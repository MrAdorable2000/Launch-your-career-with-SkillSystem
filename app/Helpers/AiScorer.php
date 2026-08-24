<?php
/**
 * AiScorer — Rule-based AI helper (no external API required)
 *
 * Provides:
 *  - Resume score (0-100) based on profile completeness, skills, experience, portfolio
 *  - Career recommendations (jobs matching student skills + department)
 *  - Skill gap analysis (required skills vs student skills)
 *  - Profile completion %
 *
 * Deterministic algorithms that can later be swapped for an LLM API.
 */

namespace App\Helpers;

use App\Models\BaseModel;

class AiScorer
{
    /**
     * Calculate a resume score (0-100) for a student.
     *
     * Factors:
     *   - Profile completeness (30 pts)
     *   - Number of skills + proficiency (20 pts)
     *   - Education entries (15 pts)
     *   - Experience entries (15 pts)
     *   - Portfolio projects (10 pts)
     *   - Certificates (5 pts)
     *   - Resume file uploaded (5 pts)
     */
    public static function resumeScore(int $studentId): array
    {
        $db = \App\Config\Database::getInstance()->getConnection();

        // Profile completion
        $stu = $db->prepare("SELECT profile_completion, bio, linkedin, github, website, gpa, department FROM students WHERE id = ?");
        $stu->execute([$studentId]);
        $student = $stu->fetch(\PDO::FETCH_ASSOC) ?: [];
        $completion = (int)($student['profile_completion'] ?? 0);
        $profileScore = min(30, round($completion * 0.30));

        // Skills
        $skillsStmt = $db->prepare("
            SELECT COUNT(*) as cnt,
                   SUM(CASE WHEN ss.proficiency_level = 'expert' THEN 3 WHEN ss.proficiency_level = 'advanced' THEN 2.5 WHEN ss.proficiency_level = 'intermediate' THEN 2 ELSE 1 END) as weighted
            FROM student_skills ss WHERE ss.student_id = ?
        ");
        $skillsStmt->execute([$studentId]);
        $skillRow = $skillsStmt->fetch(\PDO::FETCH_ASSOC);
        $skillCount = (int)($skillRow['cnt'] ?? 0);
        $skillWeight = (float)($skillRow['weighted'] ?? 0);
        $skillScore = min(20, round(($skillWeight / max(1, 15)) * 20)); // 15 weighted pts = full

        // Education
        $eduStmt = $db->prepare("SELECT COUNT(*) as c FROM education WHERE student_id = ?");
        $eduStmt->execute([$studentId]);
        $eduCount = (int)$eduStmt->fetch(\PDO::FETCH_ASSOC)['c'];
        $eduScore = min(15, $eduCount * 8);

        // Experience
        $expStmt = $db->prepare("SELECT COUNT(*) as c FROM experience WHERE student_id = ?");
        $expStmt->execute([$studentId]);
        $expCount = (int)$expStmt->fetch(\PDO::FETCH_ASSOC)['c'];
        $expScore = min(15, $expCount * 8);

        // Portfolio
        $portStmt = $db->prepare("SELECT COUNT(*) as c FROM portfolios WHERE student_id = ?");
        $portStmt->execute([$studentId]);
        $portCount = (int)$portStmt->fetch(\PDO::FETCH_ASSOC)['c'];
        $portScore = min(10, $portCount * 4);

        // Certificates
        $certStmt = $db->prepare("SELECT COUNT(*) as c FROM certificates WHERE student_id = ? AND verified = 1");
        $certStmt->execute([$studentId]);
        $certCount = (int)$certStmt->fetch(\PDO::FETCH_ASSOC)['c'];
        $certScore = min(5, $certCount * 2);

        // Resume file
        $resStmt = $db->prepare("SELECT COUNT(*) as c FROM resumes WHERE student_id = ?");
        $resStmt->execute([$studentId]);
        $resCount = (int)$resStmt->fetch(\PDO::FETCH_ASSOC)['c'];
        $resScore = $resCount > 0 ? 5 : 0;

        $total = $profileScore + $skillScore + $eduScore + $expScore + $portScore + $certScore + $resScore;
        $total = min(100, $total);

        return [
            'score'        => $total,
            'grade'        => self::gradeFor($total),
            'breakdown'    => [
                'profile'    => ['points' => $profileScore, 'max' => 30, 'label' => 'Profile Completeness'],
                'skills'     => ['points' => $skillScore, 'max' => 20, 'label' => 'Skills', 'count' => $skillCount],
                'education'  => ['points' => $eduScore, 'max' => 15, 'label' => 'Education', 'count' => $eduCount],
                'experience' => ['points' => $expScore, 'max' => 15, 'label' => 'Experience', 'count' => $expCount],
                'portfolio'  => ['points' => $portScore, 'max' => 10, 'label' => 'Portfolio', 'count' => $portCount],
                'certificates' => ['points' => $certScore, 'max' => 5, 'label' => 'Certificates', 'count' => $certCount],
                'resume'     => ['points' => $resScore, 'max' => 5, 'label' => 'Resume File'],
            ],
            'suggestions'  => self::resumeSuggestions($profileScore, $skillCount, $eduCount, $expCount, $portCount, $certCount, $resCount, $student),
        ];
    }

    public static function gradeFor(int $score): string
    {
        if ($score >= 90) return 'A+';
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }

    private static function resumeSuggestions(int $p, int $sk, int $edu, int $exp, int $port, int $cert, int $res, array $student): array
    {
        $out = [];
        if ($p < 25) $out[] = ['icon' => 'fa-user', 'text' => 'Complete your profile bio, location, and contact details to boost visibility.'];
        if ($sk < 5) $out[] = ['icon' => 'fa-code', 'text' => 'Add at least 5 skills with proficiency levels to improve job matching.'];
        if ($edu === 0) $out[] = ['icon' => 'fa-graduation-cap', 'text' => 'Add your education history — employers filter by degree and field.'];
        if ($exp === 0) $out[] = ['icon' => 'fa-briefcase', 'text' => 'Add internships, part-time roles, or volunteer experience.'];
        if ($port < 3) $out[] = ['icon' => 'fa-folder-plus', 'text' => 'Showcase at least 3 portfolio projects with live demos.'];
        if ($cert === 0) $out[] = ['icon' => 'fa-certificate', 'text' => 'Add industry certifications (Coursera, AWS, Google, etc.) to stand out.'];
        if ($res === 0) $out[] = ['icon' => 'fa-file-upload', 'text' => 'Upload your resume in PDF format for one-click applications.'];
        if (empty($student['linkedin'])) $out[] = ['icon' => 'fa-linkedin', 'text' => 'Link your LinkedIn profile — recruiters check it 80% of the time.'];
        if (empty($student['github']) && in_array(strtolower($student['department'] ?? ''), ['computer science', 'software engineering', 'it', 'information technology'])) {
            $out[] = ['icon' => 'fa-github', 'text' => 'Add your GitHub username — essential for tech roles.'];
        }
        if (empty($out)) $out[] = ['icon' => 'fa-trophy', 'text' => 'Excellent! Your resume is well-optimized. Keep updating it with new achievements.'];
        return $out;
    }

    /**
     * Career recommendations — match jobs/internships to student skills + department.
     */
    public static function careerRecommendations(int $userId, int $studentId, int $limit = 5): array
    {
        $db = \App\Config\Database::getInstance()->getConnection();

        // Get student skills
        $skStmt = $db->prepare("
            SELECT s.name FROM student_skills ss
            JOIN skills s ON s.id = ss.skill_id
            WHERE ss.student_id = ?
        ");
        $skStmt->execute([$studentId]);
        $skills = array_column($skStmt->fetchAll(\PDO::FETCH_ASSOC), 'name');

        // Get student department
        $stuStmt = $db->prepare("SELECT department, year_of_study FROM students WHERE id = ?");
        $stuStmt->execute([$studentId]);
        $stu = $stuStmt->fetch(\PDO::FETCH_ASSOC);

        if (empty($skills) && empty($stu['department'])) {
            // No signal — return recent published jobs
            $recent = $db->prepare("
                SELECT j.*, c.name as company_name, c.logo as company_logo
                FROM jobs j LEFT JOIN companies c ON c.employer_id = j.employer_id
                WHERE j.status = 'published' AND j.deadline >= CURDATE()
                ORDER BY j.created_at DESC LIMIT ?
            ");
            $recent->execute([$limit]);
            return array_map(fn($r) => ['match' => 50, 'reason' => 'Newly posted'] + $r, $recent->fetchAll(\PDO::FETCH_ASSOC));
        }

        // Score each published job by skill match + department match
        $sql = "
            SELECT j.*, c.name as company_name, c.logo as company_logo,
                   e.company_name as employer_name
            FROM jobs j
            LEFT JOIN companies c ON c.employer_id = j.employer_id
            LEFT JOIN employers e ON e.id = j.employer_id
            WHERE j.status = 'published' AND j.deadline >= CURDATE()
            ORDER BY j.created_at DESC LIMIT 50
        ";
        $jobs = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $scored = [];
        foreach ($jobs as $job) {
            $score = 0;
            $reasons = [];
            $jobText = strtolower(($job['title'] ?? '') . ' ' . ($job['description'] ?? '') . ' ' . ($job['requirements'] ?? ''));
            foreach ($skills as $skill) {
                if (strpos($jobText, strtolower($skill)) !== false) {
                    $score += 20;
                    $reasons[] = 'Matches your skill: ' . $skill;
                }
            }
            if (!empty($stu['department']) && strpos($jobText, strtolower($stu['department'])) !== false) {
                $score += 15;
                $reasons[] = 'Matches your department';
            }
            // Location preference (remote gives bonus)
            if (!empty($job['remote']) && in_array(strtolower($job['remote']), ['yes', 'remote', '1', 'true'])) {
                $score += 5;
            }
            $score = min(100, $score + 30); // baseline
            if ($score >= 40) {
                $scored[] = ['match' => $score, 'reason' => implode('; ', array_slice($reasons, 0, 2))] + $job;
            }
        }
        usort($scored, fn($a, $b) => $b['match'] <=> $a['match']);
        return array_slice($scored, 0, $limit);
    }

    /**
     * Skill gap analysis — compare student's skills to their target role's required skills.
     */
    public static function skillGap(int $studentId, ?string $targetRole = null): array
    {
        $db = \App\Config\Database::getInstance()->getConnection();

        // Student's current skills
        $cur = $db->prepare("
            SELECT s.name, ss.proficiency_level, s.category
            FROM student_skills ss JOIN skills s ON s.id = ss.skill_id
            WHERE ss.student_id = ?
        ");
        $cur->execute([$studentId]);
        $current = $cur->fetchAll(\PDO::FETCH_ASSOC);
        $currentNames = array_map('strtolower', array_column($current, 'name'));

        // Required skills (from jobs matching the target role or all published jobs)
        $reqSql = "
            SELECT j.requirements, j.title, j.description
            FROM jobs j WHERE j.status = 'published'
        ";
        $params = [];
        if ($targetRole) {
            $reqSql .= " AND (j.title LIKE ? OR j.description LIKE ?)";
            $params[] = "%$targetRole%"; $params[] = "%$targetRole%";
        }
        $reqSql .= " ORDER BY j.created_at DESC LIMIT 100";
        $reqStmt = $db->prepare($reqSql);
        $reqStmt->execute($params);
        $jobs = $reqStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get all skills in DB
        $allSkills = $db->query("SELECT name, category FROM skills ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
        $allSkillNames = array_map('strtolower', array_column($allSkills, 'name'));

        // Count skill mentions across jobs
        $skillDemand = [];
        foreach ($allSkills as $s) {
            $name = strtolower($s['name']);
            $count = 0;
            foreach ($jobs as $job) {
                $text = strtolower(($job['requirements'] ?? '') . ' ' . ($job['description'] ?? '') . ' ' . ($job['title'] ?? ''));
                if (strpos($text, $name) !== false) $count++;
            }
            if ($count > 0) {
                $skillDemand[] = [
                    'name' => $s['name'],
                    'category' => $s['category'],
                    'demand' => $count,
                    'has' => in_array($name, $currentNames)
                ];
            }
        }
        usort($skillDemand, fn($a, $b) => $b['demand'] <=> $a['demand']);

        $missing = array_filter($skillDemand, fn($s) => !$s['has']);
        $matched = array_filter($skillDemand, fn($s) => $s['has']);

        return [
            'target_role' => $targetRole,
            'current_skills' => $current,
            'matched' => array_slice(array_values($matched), 0, 10),
            'missing' => array_slice(array_values($missing), 0, 10),
            'coverage_pct' => count($skillDemand) > 0 ? round(count($matched) / count($skillDemand) * 100) : 0,
        ];
    }

    /**
     * Suggested career roadmap milestones for a student.
     */
    public static function suggestRoadmap(int $studentId): array
    {
        $db = \App\Config\Database::getInstance()->getConnection();
        $stu = $db->prepare("SELECT year_of_study, department, gpa FROM students WHERE id = ?");
        $stu->execute([$studentId]);
        $s = $stu->fetch(\PDO::FETCH_ASSOC);

        $year = (int)($s['year_of_study'] ?? 1);
        $roadmap = [
            ['title' => 'Complete Profile', 'desc' => 'Add bio, photo, contact info, and social links', 'icon' => 'fa-user-circle', 'status' => 'done'],
            ['title' => 'Add Core Skills', 'desc' => 'Add 5-10 skills relevant to your field', 'icon' => 'fa-code', 'status' => 'current'],
            ['title' => 'Build First Portfolio Project', 'desc' => 'Showcase a real project with code/demo', 'icon' => 'fa-folder-plus', 'status' => 'todo'],
            ['title' => 'Apply to Internships', 'desc' => 'Target at least 5 relevant internships', 'icon' => 'fa-paper-plane', 'status' => 'todo'],
            ['title' => 'Earn a Certification', 'desc' => 'Complete one industry-recognized certification', 'icon' => 'fa-certificate', 'status' => 'todo'],
            ['title' => 'Attend Career Events', 'desc' => 'Join workshops, job fairs, networking events', 'icon' => 'fa-calendar-star', 'status' => 'todo'],
            ['title' => 'Land First Job Offer', 'desc' => 'Convert an application into an interview & offer', 'icon' => 'fa-handshake', 'status' => 'todo'],
        ];
        // Adjust based on year
        if ($year >= 2) $roadmap[1]['status'] = 'done';
        if ($year >= 3) { $roadmap[2]['status'] = 'done'; $roadmap[3]['status'] = 'current'; }
        if ($year >= 4) { $roadmap[3]['status'] = 'done'; $roadmap[4]['status'] = 'current'; $roadmap[5]['status'] = 'current'; }

        return $roadmap;
    }
}
