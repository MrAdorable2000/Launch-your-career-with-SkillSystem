<?php

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\CSRF;
use App\Models\StudentModel;
use App\Models\BaseModel;
use App\Models\NotificationModel;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

class UniversityController extends BaseController
{
    private BaseModel $uniModel;
    private StudentModel $studentModel;

    public function __construct()
    {
        // CRITICAL: Enforce authentication + university role on university routes
        AuthMiddleware::handle();
        RoleMiddleware::handle(['university']);

        $this->uniModel = new BaseModel();
        $this->uniModel->setTable('universities');
        $this->studentModel = new StudentModel();
    }

    public function dashboard(): void
    {
        $uni = $this->uniModel->where('user_id', Session::userId())[0] ?? null;
        if (!$uni) {
            Flash::error('University profile not found.');
            $this->redirect('/');
            return;
        }

        $totalStudents = $this->studentModel->count('university_id = ?', [$uni['id']]);
        $activeStudents = $this->studentModel->count('university_id = ?', [$uni['id']]);
        $students = $this->studentModel->getAllWithUsers(1, 10, '', $uni['id']);

        // Application stats for this university's students
        $appStats = $this->studentModel->queryOne("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN a.status = 'offered' THEN 1 ELSE 0 END) as offered,
                   SUM(CASE WHEN a.status = 'interview' THEN 1 ELSE 0 END) as interviewing
            FROM applications a
            JOIN students s ON a.user_id = s.user_id
            WHERE s.university_id = ?
        ", [$uni['id']]);

        // Fetch user account data for the Account Information edit form
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find(Session::userId());

        $this->view('university/dashboard', [
            'university' => $uni,
            'totalStudents' => $totalStudents,
            'students' => $students,
            'appStats' => $appStats,
            'user' => $user,
        ]);
    }

    public function students(): void
    {
        $uni = $this->uniModel->where('user_id', Session::userId())[0] ?? null;
        $page = $this->getInt('page', 1);
        $search = $this->post('search') ?: ($_GET['search'] ?? '');
        // FIX: Pass the university ID as the 4th argument so the students listing
        // only shows students belonging to THIS university, not all students system-wide.
        $students = $this->studentModel->getAllWithUsers($page, 15, $search, $uni['id'] ?? 0);

        $this->view('university/students', [
            'university' => $uni,
            'students' => $students,
            'search' => $search
        ]);
    }

    /**
     * Create a new student account pre-registered under this university.
     */
    public function addStudent(): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('university/students');
            return;
        }

        $uni = $this->uniModel->where('user_id', Session::userId())[0] ?? null;
        if (!$uni) {
            Flash::error('University profile not found.');
            $this->redirect('university/students');
            return;
        }

        $firstName = $this->post('first_name');
        $lastName = $this->post('last_name');
        $email = $this->post('email');
        $department = $this->post('department');
        $yearOfStudy = $this->getInt('year_of_study') ?: 1;
        $studentIdNumber = $this->post('student_id_number');

        if (empty($firstName) || empty($lastName) || empty($email)) {
            Flash::error('First name, last name, and email are required.');
            $this->redirect('university/students');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Please enter a valid email address.');
            $this->redirect('university/students');
            return;
        }

        $userModel = new \App\Models\UserModel();
        if ($userModel->findByEmail($email)) {
            Flash::error('A user with this email already exists.');
            $this->redirect('university/students');
            return;
        }

        $tempPassword = 'password';
        $hashed = password_hash($tempPassword, PASSWORD_BCRYPT);

        $userId = $userModel->create([
            'role_id' => 2, // student
            'email' => $email,
            'password' => $hashed,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'status' => 'active',
        ]);

        $this->studentModel->create([
            'user_id' => $userId,
            'university_id' => $uni['id'],
            'student_id_number' => $studentIdNumber ?: null,
            'department' => $department ?: null,
            'year_of_study' => $yearOfStudy,
            'profile_completion' => 10,
        ]);

        Flash::success("Student added successfully! Default password: {$tempPassword}");
        $this->redirect('university/students');
    }

    /**
     * Update an existing student's academic record. Restricted to students
     * belonging to the logged-in university.
     */
    public function updateStudent(int $id): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('university/students');
            return;
        }

        $uni = $this->uniModel->where('user_id', Session::userId())[0] ?? null;
        if (!$uni) { $this->redirect('university/students'); return; }

        $student = $this->studentModel->queryOne("SELECT * FROM students WHERE id = ? AND university_id = ?", [$id, $uni['id']]);
        if (!$student) {
            Flash::error('Student not found in your university.');
            $this->redirect('university/students');
            return;
        }

        $this->studentModel->update($id, [
            'student_id_number' => $this->post('student_id_number') ?: null,
            'department' => $this->post('department') ?: null,
            'year_of_study' => $this->getInt('year_of_study') ?: $student['year_of_study'],
            'gpa' => $this->post('gpa') !== '' ? (float) $this->post('gpa') : null,
        ]);

        Flash::success('Student record updated successfully.');
        $this->redirect('university/students');
    }

    /**
     * Remove a student from this university's roster (unlinks the record —
     * the student's account itself is preserved, since it belongs to them).
     */
    public function removeStudent(int $id): void
    {
        if (!CSRF::check()) {
            Flash::error('Invalid request.');
            $this->redirect('university/students');
            return;
        }

        $uni = $this->uniModel->where('user_id', Session::userId())[0] ?? null;
        if (!$uni) { $this->redirect('university/students'); return; }

        $student = $this->studentModel->queryOne("SELECT * FROM students WHERE id = ? AND university_id = ?", [$id, $uni['id']]);
        if (!$student) {
            Flash::error('Student not found in your university.');
            $this->redirect('university/students');
            return;
        }

        $this->studentModel->execute("UPDATE students SET university_id = NULL WHERE id = ?", [$id]);

        Flash::success('Student removed from your university roster.');
        $this->redirect('university/students');
    }

    public function reports(): void
    {
        $uni = $this->uniModel->where('user_id', Session::userId())[0] ?? null;

        // Department distribution
        $deptData = $this->studentModel->query("
            SELECT department, COUNT(*) as count
            FROM students WHERE university_id = ? AND department IS NOT NULL
            GROUP BY department ORDER BY count DESC
        ", [$uni['id'] ?? 0]);

        // Year of study distribution
        $yearData = $this->studentModel->query("
            SELECT year_of_study, COUNT(*) as count
            FROM students WHERE university_id = ? AND year_of_study IS NOT NULL
            GROUP BY year_of_study ORDER BY year_of_study
        ", [$uni['id'] ?? 0]);

        // Employment outcomes
        $outcomeData = $this->studentModel->queryOne("
            SELECT COUNT(DISTINCT a.user_id) as total_applied,
                   SUM(CASE WHEN a.status = 'offered' THEN 1 ELSE 0 END) as offered,
                   SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM applications a
            JOIN students s ON a.user_id = s.user_id
            WHERE s.university_id = ?
        ", [$uni['id'] ?? 0]);

        $this->view('university/reports', [
            'university' => $uni,
            'deptData' => $deptData,
            'yearData' => $yearData,
            'outcomeData' => $outcomeData
        ]);
    }
}