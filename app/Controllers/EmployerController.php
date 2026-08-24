<?php

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\CSRF;
use App\Helpers\Upload;
use App\Helpers\Validator;
use App\Models\EmployerModel;
use App\Models\JobModel;
use App\Models\InternshipModel;
use App\Models\FreelanceModel;
use App\Models\ApplicationModel;
use App\Models\MessageModel;
use App\Models\NotificationModel;
use App\Models\BaseModel;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

class EmployerController extends BaseController
{
    private EmployerModel $employerModel;
    private JobModel $jobModel;
    private InternshipModel $internModel;
    private FreelanceModel $freelanceModel;
    private ApplicationModel $appModel;
    private NotificationModel $notifModel;

    public function __construct()
    {
        // CRITICAL: Enforce authentication + employer role on employer routes
        AuthMiddleware::handle();
        RoleMiddleware::handle(['employer']);

        $this->employerModel = new EmployerModel();
        $this->jobModel = new JobModel();
        $this->internModel = new InternshipModel();
        $this->freelanceModel = new FreelanceModel();
        $this->appModel = new ApplicationModel();
        $this->notifModel = new NotificationModel();
    }

    public function dashboard(): void
    {
        $employer = $this->employerModel->findByUserId(Session::userId());
        $employerId = (int)($employer['id'] ?? 0);
        $jobs = $this->jobModel->getByEmployer($employerId, 1, 5);
        $internships = $this->internModel->getByEmployer($employerId);
        $freelance = $this->freelanceModel->getByEmployer($employerId);

        // Use parameterized queries instead of string concatenation (H-5 fix)
        $totalJobs = $this->jobModel->count('employer_id = ?', [$employerId]);
        $totalApps = $this->appModel->count('job_id IN (SELECT id FROM jobs WHERE employer_id = ?)', [$employerId]);

        // FIX: Use employer-specific stats instead of global getStats()
        // Previously this showed system-wide pipeline numbers, not this employer's own.
        $appStats = $this->appModel->getStatsForEmployer($employerId);

        $this->view('employer/dashboard', [
            'employer' => $employer,
            'jobs' => $jobs,
            'internships' => $internships,
            'freelance' => $freelance,
            'totalJobs' => $totalJobs,
            'totalApps' => $totalApps,
            'appStats' => $appStats
        ]);
    }

    public function company(): void
    {
        $employer = $this->employerModel->findByUserId(Session::userId());
        $this->view('employer/company', ['employer' => $employer]);
    }

    public function updateCompany(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('employer/company');
            return;
        }

        $employer = $this->employerModel->findByUserId(Session::userId());

        // Handle logo upload
        $logoPath = $employer['company_logo'] ?? null;
        if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
            $upload = Upload::handle($_FILES['company_logo'], 'uploads/logos', ['jpg', 'jpeg', 'png']);
            if ($upload['success']) {
                $logoPath = $upload['path'];
            }
        }

        $this->employerModel->update($employer['id'], [
            'company_name' => $this->post('company_name'),
            'industry' => $this->post('industry'),
            'company_size' => $this->post('company_size'),
            'location' => $this->post('location'),
            'website' => $this->post('website'),
            'description' => $this->post('description'),
            'company_logo' => $logoPath,
            'founded_year' => $this->post('founded_year') ?: null
        ]);

        $this->logActivity('company', 'Updated company profile');
        Flash::success('Company profile updated.');
        $this->redirect('employer/company');
    }

    public function postJob(): void
    {
        $this->view('employer/post-job');
    }

    public function storeJob(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('employer/post-job');
            return;
        }

        $employer = $this->employerModel->findByUserId(Session::userId());

        $validator = new Validator();
        $validator->validate($_POST, [
            'title' => 'required|min:3',
            'description' => 'required|min:20',
            'location' => 'required',
            'type' => 'required|in:full-time,part-time,contract,freelance',
            'deadline' => 'required'
        ]);

        if ($validator->fails()) {
            Flash::error($validator->firstError('title') ?: $validator->firstError('description') ?: 'Please fix the errors.');
            $this->redirect('employer/post-job');
            return;
        }

        // Get company_id
        $company = (new BaseModel())->queryOne("SELECT id FROM companies WHERE employer_id = ?", [$employer['id']]);

        $jobId = $this->jobModel->create([
            'employer_id' => $employer['id'],
            'company_id' => $company['id'] ?? null,
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'requirements' => $this->post('requirements'),
            'responsibilities' => $this->post('responsibilities'),
            'salary_min' => $this->getInt('salary_min') ?: null,
            'salary_max' => $this->getInt('salary_max') ?: null,
            'salary_currency' => 'RWF',
            'location' => $this->post('location'),
            'type' => $this->post('type'),
            'remote' => isset($_POST['remote']) ? 1 : 0,
            'deadline' => $this->post('deadline'),
            'status' => 'published'
        ]);

        $this->logActivity('job', "Posted job: " . $this->post('title'));
        Flash::success('Job posted successfully!');
        $this->redirect('employer/jobs');
    }

    public function jobs(): void
    {
        $employer = $this->employerModel->findByUserId(Session::userId());
        $page = $this->getInt('page', 1);
        $jobs = $this->jobModel->getByEmployer($employer['id'] ?? 0, $page, 10);

        $this->view('employer/jobs', ['jobs' => $jobs]);
    }

    /**
     * Update an existing job posting owned by the logged-in employer.
     */
    public function updateJob(int $id): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('employer/jobs');
            return;
        }

        $employer = $this->employerModel->findByUserId(Session::userId());
        if (!$employer) { $this->redirect('employer/jobs'); return; }

        // Ownership check
        $job = $this->jobModel->queryOne("SELECT * FROM jobs WHERE id = ? AND employer_id = ?", [$id, $employer['id']]);
        if (!$job) {
            Flash::error('Job not found or you do not have permission to edit it.');
            $this->redirect('employer/jobs');
            return;
        }

        $validator = new Validator();
        $validator->validate($_POST, [
            'title' => 'required|min:3',
            'description' => 'required|min:20',
            'location' => 'required',
            'type' => 'required|in:full-time,part-time,contract,freelance',
        ]);

        if ($validator->fails()) {
            Flash::error($validator->firstError('title') ?: $validator->firstError('description') ?: 'Please fix the errors.');
            $this->redirect('employer/jobs');
            return;
        }

        $this->jobModel->update($id, [
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'requirements' => $this->post('requirements'),
            'responsibilities' => $this->post('responsibilities'),
            'salary_min' => $this->getInt('salary_min') ?: null,
            'salary_max' => $this->getInt('salary_max') ?: null,
            'location' => $this->post('location'),
            'type' => $this->post('type'),
            'remote' => isset($_POST['remote']) ? 1 : 0,
            'deadline' => $this->post('deadline') ?: null,
            'status' => $this->post('status') ?: $job['status'],
        ]);

        $this->logActivity('job', 'Updated job: ' . $this->post('title'));
        Flash::success('Job updated successfully!');
        $this->redirect('employer/jobs');
    }

    public function applicants(int $jobId): void
    {
        $job = $this->jobModel->find($jobId);
        $applicants = $this->appModel->getApplicantsByJob($jobId);

        $this->view('employer/applicants', [
            'job' => $job,
            'applicants' => $applicants
        ]);
    }

    public function updateApplicationStatus(int $id): void
    {
        if (!CSRF::check()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }

        $status = $this->post('status');
        $allowed = ['pending', 'reviewing', 'shortlisted', 'interview', 'offered', 'rejected'];

        if (!in_array($status, $allowed)) {
            $this->json(['success' => false, 'message' => 'Invalid status'], 400);
            return;
        }

        $this->appModel->updateStatus($id, $status);

        // Notify student
        $app = $this->appModel->find($id);
        if ($app) {
            $this->notifModel->createNotification($app['user_id'], 'application', 'Application Status Updated',
                "Your application status has been changed to: " . ucfirst($status), ['application_id' => $id]);
        }

        $this->json(['success' => true, 'message' => 'Status updated']);
    }

    public function internships(): void
    {
        $employer = $this->employerModel->findByUserId(Session::userId());
        $internships = $this->internModel->getByEmployer($employer['id'] ?? 0);
        $this->view('employer/internships', ['internships' => $internships]);
    }

    public function storeInternship(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('employer/internships');
            return;
        }

        $employer = $this->employerModel->findByUserId(Session::userId());
        $company = (new BaseModel())->queryOne("SELECT id FROM companies WHERE employer_id = ?", [$employer['id']]);

        (new InternshipModel())->create([
            'employer_id' => $employer['id'],
            'company_id' => $company['id'] ?? null,
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'requirements' => $this->post('requirements'),
            'duration' => $this->getInt('duration') ?: 3,
            'duration_unit' => $this->post('duration_unit') ?: 'months',
            'allowance' => $this->getInt('allowance') ?: 0,
            'location' => $this->post('location'),
            'deadline' => $this->post('deadline'),
            'positions_available' => $this->getInt('positions') ?: 1,
            'status' => 'published'
        ]);

        $this->logActivity('internship', "Posted internship: " . $this->post('title'));
        Flash::success('Internship posted successfully!');
        $this->redirect('employer/internships');
    }

    /**
     * Update an existing internship posting owned by the logged-in employer.
     */
    public function updateInternship(int $id): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('employer/internships');
            return;
        }

        $employer = $this->employerModel->findByUserId(Session::userId());
        if (!$employer) { $this->redirect('employer/internships'); return; }

        $internship = $this->internModel->queryOne("SELECT * FROM internships WHERE id = ? AND employer_id = ?", [$id, $employer['id']]);
        if (!$internship) {
            Flash::error('Internship not found or you do not have permission to edit it.');
            $this->redirect('employer/internships');
            return;
        }

        $validator = new Validator();
        $validator->validate($_POST, [
            'title' => 'required|min:3',
            'description' => 'required|min:20',
            'location' => 'required',
        ]);

        if ($validator->fails()) {
            Flash::error($validator->firstError('title') ?: $validator->firstError('description') ?: 'Please fix the errors.');
            $this->redirect('employer/internships');
            return;
        }

        $this->internModel->update($id, [
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'requirements' => $this->post('requirements'),
            'duration' => $this->getInt('duration') ?: 3,
            'duration_unit' => $this->post('duration_unit') ?: 'months',
            'allowance' => $this->getInt('allowance') ?: 0,
            'location' => $this->post('location'),
            'deadline' => $this->post('deadline') ?: null,
            'positions_available' => $this->getInt('positions') ?: 1,
            'status' => $this->post('status') ?: $internship['status'],
        ]);

        $this->logActivity('internship', 'Updated internship: ' . $this->post('title'));
        Flash::success('Internship updated successfully!');
        $this->redirect('employer/internships');
    }

    public function freelance(): void
    {
        $employer = $this->employerModel->findByUserId(Session::userId());
        $projects = $this->freelanceModel->getByEmployer($employer['id'] ?? 0);
        $this->view('employer/freelance', ['projects' => $projects]);
    }

    public function storeFreelance(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('employer/freelance');
            return;
        }

        $employer = $this->employerModel->findByUserId(Session::userId());

        (new FreelanceModel())->create([
            'employer_id' => $employer['id'],
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'budget_min' => $this->getInt('budget_min') ?: null,
            'budget_max' => $this->getInt('budget_max') ?: null,
            'skills_required' => $this->post('skills_required'),
            'deadline' => $this->post('deadline') ?: null,
            'status' => 'open'
        ]);

        Flash::success('Freelance project posted!');
        $this->redirect('employer/freelance');
    }

    /**
     * Update an existing freelance project owned by the logged-in employer.
     */
    public function updateFreelance(int $id): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('employer/freelance');
            return;
        }

        $employer = $this->employerModel->findByUserId(Session::userId());
        if (!$employer) { $this->redirect('employer/freelance'); return; }

        $project = $this->freelanceModel->queryOne("SELECT * FROM freelance_projects WHERE id = ? AND employer_id = ?", [$id, $employer['id']]);
        if (!$project) {
            Flash::error('Project not found or you do not have permission to edit it.');
            $this->redirect('employer/freelance');
            return;
        }

        $validator = new Validator();
        $validator->validate($_POST, [
            'title' => 'required|min:3',
            'description' => 'required|min:20',
        ]);

        if ($validator->fails()) {
            Flash::error($validator->firstError('title') ?: $validator->firstError('description') ?: 'Please fix the errors.');
            $this->redirect('employer/freelance');
            return;
        }

        $this->freelanceModel->update($id, [
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'budget_min' => $this->getInt('budget_min') ?: null,
            'budget_max' => $this->getInt('budget_max') ?: null,
            'skills_required' => $this->post('skills_required'),
            'deadline' => $this->post('deadline') ?: null,
            'status' => $this->post('status') ?: $project['status'],
        ]);

        $this->logActivity('freelance', 'Updated freelance project: ' . $this->post('title'));
        Flash::success('Freelance project updated successfully!');
        $this->redirect('employer/freelance');
    }

    public function settings(): void
    {
        // Fetch the current user's data so the Account Info form can be pre-populated
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find(Session::userId());

        $this->view('employer/settings', [
            'user' => $user,
        ]);
    }

    public function deleteJob(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('employer/jobs'); return; }
        $employer = $this->employerModel->findByUserId(Session::userId());
        if (!$employer) { $this->redirect('employer/jobs'); return; }
        $base = new \App\Models\BaseModel();
        $job = $base->queryOne("SELECT * FROM jobs WHERE id = ? AND employer_id = ?", [$id, $employer['id']]);
        if (!$job) { Flash::error('Job not found.'); $this->redirect('employer/jobs'); return; }
        $base->execute("DELETE FROM jobs WHERE id = ?", [$id]);
        $this->logActivity('job', 'Deleted job: ' . ($job['title'] ?? ''));
        Flash::success('Job deleted successfully.');
        $this->redirect('employer/jobs');
    }

    public function deleteInternship(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('employer/internships'); return; }
        $employer = $this->employerModel->findByUserId(Session::userId());
        if (!$employer) { $this->redirect('employer/internships'); return; }
        $base = new \App\Models\BaseModel();
        $internship = $base->queryOne("SELECT * FROM internships WHERE id = ? AND employer_id = ?", [$id, $employer['id']]);
        if (!$internship) { Flash::error('Internship not found.'); $this->redirect('employer/internships'); return; }
        $base->execute("DELETE FROM internships WHERE id = ?", [$id]);
        Flash::success('Internship deleted.');
        $this->redirect('employer/internships');
    }

    public function deleteFreelance(int $id): void
    {
        if (!CSRF::check()) { Flash::error('Invalid request.'); $this->redirect('employer/freelance'); return; }
        $employer = $this->employerModel->findByUserId(Session::userId());
        if (!$employer) { $this->redirect('employer/freelance'); return; }
        $base = new \App\Models\BaseModel();
        $project = $base->queryOne("SELECT * FROM freelance_projects WHERE id = ? AND employer_id = ?", [$id, $employer['id']]);
        if (!$project) { Flash::error('Project not found.'); $this->redirect('employer/freelance'); return; }
        $base->execute("DELETE FROM freelance_projects WHERE id = ?", [$id]);
        Flash::success('Freelance project deleted.');
        $this->redirect('employer/freelance');
    }
}