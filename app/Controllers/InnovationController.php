<?php
/**
 * InnovationController — Handles all innovation feature pages.
 *
 * Features:
 *  - AI Resume Score (rule-based)
 *  - Skill Gap Analysis
 *  - Leaderboard
 *  - Achievement Badges
 *  - Career Roadmap
 *  - Certificates + QR Verification
 *  - Events
 *  - Discussion Forum
 *  - Mentorship
 */

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\CSRF;
use App\Helpers\Session;
use App\Helpers\URL;
use App\Helpers\AiScorer;
use App\Helpers\Component;
use App\Models\BaseModel;
use App\Models\StudentModel;
use App\Models\BadgeModel;
use App\Models\LeaderboardModel;
use App\Models\CertificateModel;
use App\Models\EventModel;
use App\Models\ForumModel;
use App\Models\MentorshipModel;

class InnovationController extends BaseController
{
    private StudentModel $studentModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        // Public routes: /verify and /verify/{code} — don't require auth.
        // All other innovation routes require login + student role (M-1 fix).
        $current = \App\Helpers\URL::current();
        $isVerifyRoute = (strpos($current, '/verify') === 0);
        if (!$isVerifyRoute) {
            if (!Session::isLoggedIn()) {
                \App\Helpers\URL::redirect('login');
                exit;
            }
            // Enforce student role on /student/* innovation routes
            if (strpos($current, '/student/') === 0 && !Session::isStudent()) {
                http_response_code(403);
                die('Access Denied: This area is for students only.');
            }
        }
    }

    /* ---------------- AI RESUME SCORE ---------------- */
    public function aiScore(): void
    {
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) {
            Flash::error('Student profile not found.');
            $this->redirect('student/dashboard');
            return;
        }
        $score = AiScorer::resumeScore($student['id']);
        $this->view('student/ai-score', [
            'student' => $student,
            'score'   => $score,
        ]);
    }

    /* ---------------- SKILL GAP ANALYSIS ---------------- */
    public function skillGap(): void
    {
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) {
            Flash::error('Student profile not found.');
            $this->redirect('student/dashboard');
            return;
        }
        $targetRole = $_GET['role'] ?? '';
        $analysis = AiScorer::skillGap($student['id'], $targetRole ?: null);
        $this->view('student/skill-gap', [
            'student'  => $student,
            'analysis' => $analysis,
            'targetRole' => $targetRole,
        ]);
    }

    /* ---------------- LEADERBOARD ---------------- */
    public function leaderboard(): void
    {
        $leaderModel = new LeaderboardModel();
        $top = $leaderModel->topStudents(50);
        $student = $this->studentModel->findByUserId(Session::userId());
        $myRank = $student ? $leaderModel->getStudentRank($student['id']) : ['rank' => 0, 'score' => 0, 'total' => 0];
        $this->view('student/leaderboard', [
            'top'     => $top,
            'myRank'  => $myRank,
            'student' => $student,
        ]);
    }

    /* ---------------- BADGES ---------------- */
    public function badges(): void
    {
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) {
            Flash::error('Student profile not found.');
            $this->redirect('student/dashboard');
            return;
        }
        $badgeModel = new BadgeModel();
        $badgeModel->autoAward($student['id']);
        $earned = $badgeModel->getForStudent($student['id']);
        $all = $badgeModel->getAll();
        $earnedIds = array_column($earned, 'id');
        $this->view('student/badges', [
            'student'   => $student,
            'earned'    => $earned,
            'all'       => $all,
            'earnedIds' => $earnedIds,
        ]);
    }

    /* ---------------- CAREER ROADMAP ---------------- */
    public function roadmap(): void
    {
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) {
            Flash::error('Student profile not found.');
            $this->redirect('student/dashboard');
            return;
        }
        $milestones = AiScorer::suggestRoadmap($student['id']);
        $this->view('student/roadmap', [
            'student'    => $student,
            'milestones' => $milestones,
        ]);
    }

    /* ---------------- CERTIFICATES ---------------- */
    public function certificates(): void
    {
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) {
            Flash::error('Student profile not found.');
            $this->redirect('student/dashboard');
            return;
        }
        $certModel = new CertificateModel();
        $certs = $certModel->getForStudent($student['id']);
        $this->view('student/certificates', [
            'student'      => $student,
            'certificates' => $certs,
        ]);
    }

    public function addCertificate(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('student/certificates');
            return;
        }
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) {
            Flash::error('Student profile not found.');
            $this->redirect('student/certificates');
            return;
        }
        $certModel = new CertificateModel();
        $code = $certModel->generateVerificationCode();
        $certModel->execute(
            "INSERT INTO certificates (student_id, title, issuing_organization, certificate_number, issued_date, verification_code, verified) VALUES (?, ?, ?, ?, ?, ?, 0)",
            [
                $student['id'],
                $this->post('title'),
                $this->post('issuing_organization'),
                $this->post('certificate_number') ?: ('CERT-' . time()),
                $this->post('issued_date') ?: date('Y-m-d'),
                $code,
            ]
        );
        $this->logActivity('certificate', 'Added certificate: ' . $this->post('title'));
        Flash::success('Certificate added. Verification code: ' . $code);
        $this->redirect('student/certificates');
    }

    /* ---------------- EVENTS ---------------- */
    public function events(): void
    {
        $eventModel = new EventModel();
        $events = $eventModel->upcoming(20);
        $this->view('student/events', [
            'events' => $events,
        ]);
    }

    public function registerEvent(int $id): void
    {
        if (!CSRF::check()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }
        $eventModel = new EventModel();
        if ($eventModel->isRegistered($id, Session::userId())) {
            $this->json(['success' => false, 'message' => 'You are already registered.'], 400);
            return;
        }
        $ok = $eventModel->register($id, Session::userId());
        $this->json(['success' => $ok, 'message' => $ok ? 'Registered successfully!' : 'Could not register.']);
    }

    /* ---------------- FORUM ---------------- */
    public function forum(): void
    {
        $forumModel = new ForumModel();
        $topics = $forumModel->recent(20);
        $categories = $forumModel->categories();
        $this->view('student/forum', [
            'topics'     => $topics,
            'categories' => $categories,
        ]);
    }

    public function forumTopic(int $id): void
    {
        $forumModel = new ForumModel();
        $topic = $forumModel->find($id);
        if (!$topic) {
            Flash::error('Topic not found.');
            $this->redirect('student/forum');
            return;
        }
        $forumModel->incrementViews($id);
        $comments = $forumModel->comments($id);
        $this->view('student/forum-topic', [
            'topic'    => $topic,
            'comments' => $comments,
        ]);
    }

    public function createForumTopic(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('student/forum');
            return;
        }
        $forumModel = new ForumModel();
        $forumModel->execute(
            "INSERT INTO discussions (user_id, title, body, category, tags) VALUES (?, ?, ?, ?, ?)",
            [
                Session::userId(),
                $this->post('title'),
                $this->post('body'),
                $this->post('category') ?: 'General',
                $this->post('tags'),
            ]
        );
        $id = $forumModel->lastId();
        $this->logActivity('forum', 'Created discussion: ' . $this->post('title'));
        Flash::success('Discussion posted!');
        $this->redirect('student/forum/' . $id);
    }

    public function addForumComment(int $id): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('student/forum/' . $id);
            return;
        }
        $forumModel = new ForumModel();
        $forumModel->addComment($id, Session::userId(), $this->post('body'));
        Flash::success('Comment added.');
        $this->redirect('student/forum/' . $id);
    }

    /* ---------------- MENTORSHIP ---------------- */
    public function mentors(): void
    {
        $mentorshipModel = new MentorshipModel();
        $mentors = $mentorshipModel->allMentors(20);
        $this->view('student/mentors', [
            'mentors' => $mentors,
        ]);
    }

    public function bookMentor(int $id): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('student/mentors');
            return;
        }
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) {
            Flash::error('Student profile required to book a session.');
            $this->redirect('student/mentors');
            return;
        }
        $mentorshipModel = new MentorshipModel();
        $mentorshipModel->execute(
            "INSERT INTO mentorship_sessions (mentor_id, student_id, topic, description, scheduled_at, duration_minutes, status) VALUES (?, ?, ?, ?, ?, ?, 'requested')",
            [
                $id,
                $student['id'],
                $this->post('topic'),
                $this->post('description'),
                $this->post('scheduled_at'),
                (int)$this->post('duration_minutes') ?: 60,
            ]
        );
        $this->logActivity('mentorship', 'Booked a session with mentor #' . $id);
        Flash::success('Session requested! The mentor will confirm shortly.');
        $this->redirect('student/mentors');
    }

    /* ---------------- QR / VERIFICATION ---------------- */
    public function verifyLanding(): void
    {
        $this->view('verify/landing', []);
    }

    public function verifyByCode(string $code): void
    {
        $certModel = new CertificateModel();
        $cert = $certModel->findByVerificationCode($code);
        $this->view('verify/result', [
            'code' => $code,
            'certificate' => $cert,
        ]);
    }

    public function deleteCertificate(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('student/certificates'); return; }
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) { $this->redirect('student/certificates'); return; }
        $base = new BaseModel();
        $base->execute("DELETE FROM certificates WHERE id = ? AND student_id = ?", [$id, $student['id']]);
        Flash::success('Certificate deleted.');
        $this->redirect('student/certificates');
    }

    public function deleteForumTopic(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('student/forum'); return; }
        $base = new BaseModel();
        $topic = $base->queryOne("SELECT * FROM discussions WHERE id = ? AND user_id = ?", [$id, Session::userId()]);
        if (!$topic) { Flash::error('Topic not found or you do not have permission.'); $this->redirect('student/forum'); return; }
        $base->execute("DELETE FROM discussions WHERE id = ?", [$id]);
        Flash::success('Discussion deleted.');
        $this->redirect('student/forum');
    }

    public function deleteComment(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('student/forum'); return; }
        $base = new BaseModel();
        $comment = $base->queryOne("SELECT * FROM comments WHERE id = ? AND user_id = ?", [$id, Session::userId()]);
        if (!$comment) { Flash::error('Comment not found.'); $this->redirect('student/forum'); return; }
        $discussionId = $comment['discussion_id'];
        $base->execute("DELETE FROM comments WHERE id = ?", [$id]);
        Flash::success('Comment deleted.');
        $this->redirect('student/forum/' . $discussionId);
    }

    public function rateMentor(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('student/mentors'); return; }
        $rating = (int)($this->post('rating') ?: 5);
        $review = $this->post('review');
        if ($rating < 1 || $rating > 5) $rating = 5;
        $student = $this->studentModel->findByUserId(Session::userId());
        if (!$student) { $this->redirect('student/mentors'); return; }
        $base = new BaseModel();
        $base->execute("INSERT INTO ratings (user_id, target_id, target_type, rating, review) VALUES (?, ?, 'mentor', ?, ?)",
            [Session::userId(), $id, $rating, $review]);
        // Update mentor's average rating
        $base->execute("UPDATE mentors SET rating = (SELECT AVG(rating) FROM ratings WHERE target_id = ? AND target_type = 'mentor') WHERE id = ?", [$id, $id]);
        Flash::success('Thank you for your rating!');
        $this->redirect('student/mentors');
    }
}
