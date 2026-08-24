<?php

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\CSRF;
use App\Models\BaseModel;
use App\Models\MessageModel;
use App\Models\NotificationModel;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

class MentorController extends BaseController
{
    private BaseModel $mentorModel;
    private MessageModel $msgModel;
    private NotificationModel $notifModel;

    public function __construct()
    {
        // CRITICAL: Enforce authentication + mentor role on mentor routes
        AuthMiddleware::handle();
        RoleMiddleware::handle(['mentor']);

        $this->mentorModel = new BaseModel();
        $this->mentorModel->setTable('mentors');
        // Define fillable fields for mentor profile updates (fixes setFillable bug)
        // We use reflection to set the protected $fillable property since BaseModel
        // has no public setter — this avoids creating a whole new MentorModel class
        // while still making update() work correctly.
        try {
            $ref = new \ReflectionProperty(BaseModel::class, 'fillable');
            $ref->setAccessible(true);
            $ref->setValue($this->mentorModel, [
                'specialization', 'company', 'title', 'years_experience',
                'bio', 'hourly_rate', 'availability', 'linkedin',
            ]);
        } catch (\Throwable $e) {
            // If reflection fails, fillable stays empty — update() will return false
            // but the page won't crash. The mentor can still read their profile.
        }
        $this->msgModel = new MessageModel();
        $this->notifModel = new NotificationModel();
    }

    public function dashboard(): void
    {
        $mentor = $this->mentorModel->where('user_id', Session::userId())[0] ?? null;
        if (!$mentor) {
            Flash::error('Mentor profile not found.');
            $this->redirect('/');
            return;
        }

        // Get messages from students
        $inbox = $this->msgModel->getInbox(Session::userId(), 1, 10);

        // Get ratings
        $ratings = (new BaseModel())->query(
            "SELECT r.*, u.first_name, u.last_name, u.avatar
             FROM ratings r JOIN users u ON r.user_id = u.id
             WHERE r.target_id = ? AND r.target_type = 'mentor'
             ORDER BY r.created_at DESC LIMIT 10",
            [$mentor['id']]
        );

        // FIX: Fetch real upcoming mentorship sessions from the database
        // (previously the dashboard showed hardcoded mock sessions).
        $upcomingSessions = [];
        try {
            $mentorshipModel = new \App\Models\MentorshipModel();
            $upcomingSessions = $mentorshipModel->getMentorSessions($mentor['id']);
        } catch (\Throwable $e) {
            // mentorship_sessions table might not exist if migration wasn't run
        }

        $this->view('mentor/dashboard', [
            'mentor' => $mentor,
            'inbox' => $inbox,
            'ratings' => $ratings,
            'upcomingSessions' => $upcomingSessions,
        ]);
    }

    public function sessions(): void
    {
        $mentor = $this->mentorModel->where('user_id', Session::userId())[0] ?? null;

        // FIX: Fetch real mentorship sessions from the database
        // (previously this only showed messages, not actual sessions).
        $sessions = [];
        try {
            $mentorshipModel = new \App\Models\MentorshipModel();
            $sessions = $mentorshipModel->getMentorSessions($mentor['id'] ?? 0);
        } catch (\Throwable $e) {
            // mentorship_sessions table might not exist if migration wasn't run
        }

        // Also keep messages for context
        $messages = $this->msgModel->getInbox(Session::userId(), 1, 20);

        // Fetch user account data for the Account Information edit form
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find(Session::userId());

        $this->view('mentor/sessions', [
            'mentor' => $mentor,
            'sessions' => $sessions,
            'messages' => $messages,
            'user' => $user,
        ]);
    }

    public function updateProfile(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('mentor/dashboard');
            return;
        }

        $mentor = $this->mentorModel->where('user_id', Session::userId())[0] ?? null;
        if (!$mentor) {
            $this->redirect('mentor/dashboard');
            return;
        }

        // Fillable fields are now set in the constructor via reflection,
        // so we can call update() directly without the non-existent setFillable().
        $this->mentorModel->update($mentor['id'], [
            'specialization' => $this->post('specialization'),
            'company' => $this->post('company'),
            'title' => $this->post('title'),
            'years_experience' => $this->getInt('years_experience') ?: null,
            'bio' => $this->post('bio'),
            'hourly_rate' => $this->post('hourly_rate') ?: null,
            'availability' => $this->post('availability') ?: 'available',
            'linkedin' => $this->post('linkedin')
        ]);

        Flash::success('Profile updated successfully.');
        $this->redirect('mentor/dashboard');
    }

    public function confirmSession(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('mentor/sessions'); return; }
        $mentor = $this->mentorModel->where('user_id', Session::userId())[0] ?? null;
        if (!$mentor) { $this->redirect('mentor/sessions'); return; }
        $base = new BaseModel();
        $base->execute("UPDATE mentorship_sessions SET status = 'scheduled' WHERE id = ? AND mentor_id = ? AND status = 'requested'", [$id, $mentor['id']]);
        Flash::success('Session confirmed.');
        $this->redirect('mentor/sessions');
    }

    public function cancelSession(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('mentor/sessions'); return; }
        $mentor = $this->mentorModel->where('user_id', Session::userId())[0] ?? null;
        if (!$mentor) { $this->redirect('mentor/sessions'); return; }
        $base = new BaseModel();
        $base->execute("UPDATE mentorship_sessions SET status = 'cancelled' WHERE id = ? AND mentor_id = ?", [$id, $mentor['id']]);
        Flash::success('Session cancelled.');
        $this->redirect('mentor/sessions');
    }

    public function completeSession(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('mentor/sessions'); return; }
        $mentor = $this->mentorModel->where('user_id', Session::userId())[0] ?? null;
        if (!$mentor) { $this->redirect('mentor/sessions'); return; }
        $base = new BaseModel();
        $base->execute("UPDATE mentorship_sessions SET status = 'completed' WHERE id = ? AND mentor_id = ?", [$id, $mentor['id']]);
        // Increment mentor's total_sessions
        $base->execute("UPDATE mentors SET total_sessions = total_sessions + 1 WHERE id = ?", [$mentor['id']]);
        Flash::success('Session marked as completed.');
        $this->redirect('mentor/sessions');
    }
}