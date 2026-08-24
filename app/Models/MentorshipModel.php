<?php
/**
 * MentorshipModel — Mentorship sessions between mentors and students.
 */

namespace App\Models;

class MentorshipModel extends BaseModel
{
    protected string $table = 'mentorship_sessions';
    protected string $primaryKey = 'id';
    protected array $fillable = ['mentor_id', 'student_id', 'topic', 'description', 'scheduled_at', 'duration_minutes', 'status', 'meeting_link', 'feedback'];

    public function getMentorSessions(int $mentorId, int $limit = 50): array
    {
        return $this->query("
            SELECT ms.*, u.first_name as student_first, u.last_name as student_last, u.avatar as student_avatar, s.department
            FROM mentorship_sessions ms
            JOIN students s ON s.id = ms.student_id
            JOIN users u ON u.id = s.user_id
            WHERE ms.mentor_id = ?
            ORDER BY ms.scheduled_at DESC
            LIMIT ?
        ", [$mentorId, $limit]);
    }

    public function getStudentSessions(int $studentId, int $limit = 50): array
    {
        return $this->query("
            SELECT ms.*, u.first_name as mentor_first, u.last_name as mentor_last, u.avatar as mentor_avatar,
                   m.specialization, m.title as mentor_title
            FROM mentorship_sessions ms
            JOIN mentors m ON m.id = ms.mentor_id
            JOIN users u ON u.id = m.user_id
            WHERE ms.student_id = ?
            ORDER BY ms.scheduled_at DESC
            LIMIT ?
        ", [$studentId, $limit]);
    }

    public function upcomingForStudent(int $studentId, int $limit = 5): array
    {
        return $this->query("
            SELECT ms.*, u.first_name as mentor_first, u.last_name as mentor_last, m.specialization
            FROM mentorship_sessions ms
            JOIN mentors m ON m.id = ms.mentor_id
            JOIN users u ON u.id = m.user_id
            WHERE ms.student_id = ? AND ms.scheduled_at >= NOW() AND ms.status = 'scheduled'
            ORDER BY ms.scheduled_at ASC
            LIMIT ?
        ", [$studentId, $limit]);
    }

    public function allMentors(int $limit = 20): array
    {
        return $this->query("
            SELECT m.*, u.first_name, u.last_name, u.avatar, u.email
            FROM mentors m
            JOIN users u ON u.id = m.user_id
            WHERE u.status = 'active'
            ORDER BY m.rating DESC, m.total_sessions DESC
            LIMIT ?
        ", [$limit]);
    }
}
