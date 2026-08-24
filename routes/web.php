<?php
/**
 * SkillSystem Web Routes
 *
 * Format:  'url-pattern' => 'Controller@action'
 * With method: ['handler' => 'Controller@action', 'method' => 'POST']
 *
 * URL parameters: use {paramName} in the pattern
 * Example: '/student/jobs/{id}' matches '/student/jobs/42' with $params['id'] = '42'
 */

return [

    // ============================================
    // PUBLIC PAGES
    // ============================================
    '/'                       => 'HomeController@index',
    '/setup'                  => 'HomeController@setup',
    '/login'                  => 'AuthController@login',
    '/register'               => 'AuthController@register',
    '/forgot-password'        => 'AuthController@forgotPassword',
    '/reset-password/{token}' => 'AuthController@resetPassword',
    '/logout'                 => 'AuthController@logout',

    // ============================================
    // AUTH FORM SUBMISSIONS (POST)
    // ============================================
    '/auth/login'             => ['handler' => 'AuthController@handleLogin',      'method' => 'POST'],
    '/auth/register'          => ['handler' => 'AuthController@handleRegister',   'method' => 'POST'],
    '/auth/forgot-password'   => ['handler' => 'AuthController@handleForgotPassword', 'method' => 'POST'],
    '/auth/reset-password'    => ['handler' => 'AuthController@handleResetPassword', 'method' => 'POST'],

    // ============================================
    // STUDENT ROUTES
    // ============================================
    '/student/dashboard'        => 'StudentController@dashboard',
    '/student/profile'          => 'StudentController@profile',
    '/student/profile/update'   => ['handler' => 'StudentController@updateProfile', 'method' => 'POST'],
    '/student/jobs'             => 'StudentController@jobs',
    '/student/jobs/{id}'        => 'StudentController@viewJob',
    '/student/jobs/{id}/apply'  => ['handler' => 'StudentController@applyJob', 'method' => 'POST'],
    '/student/applications'     => 'StudentController@applications',
    '/student/applications/{id}/withdraw' => ['handler' => 'StudentController@withdrawApplication', 'method' => 'POST'],
    '/student/portfolio'        => 'StudentController@portfolio',
    '/student/portfolio/add'    => ['handler' => 'StudentController@addPortfolio', 'method' => 'POST'],
    '/student/portfolio/{id}/delete' => ['handler' => 'StudentController@deletePortfolio', 'method' => 'POST'],
    '/student/resume'           => 'StudentController@resume',
    '/student/resume/upload'    => ['handler' => 'StudentController@uploadResume', 'method' => 'POST'],
    '/student/resume/{id}/download' => 'StudentController@downloadResume',
    '/student/resume/{id}/delete' => ['handler' => 'StudentController@deleteResume', 'method' => 'POST'],
    '/student/education/add'    => ['handler' => 'StudentController@addEducation', 'method' => 'POST'],
    '/student/education/{id}/delete' => ['handler' => 'StudentController@deleteEducation', 'method' => 'POST'],
    '/student/experience/add'   => ['handler' => 'StudentController@addExperience', 'method' => 'POST'],
    '/student/experience/{id}/delete' => ['handler' => 'StudentController@deleteExperience', 'method' => 'POST'],
    '/student/messages'         => 'MessageController@inbox',
    '/student/settings'         => 'StudentController@settings',
    '/student/settings/update'  => ['handler' => 'StudentController@updateSettings', 'method' => 'POST'],

    // ============================================
    // STUDENT INNOVATION FEATURES
    // ============================================
    '/student/ai-score'         => 'InnovationController@aiScore',
    '/student/skill-gap'        => 'InnovationController@skillGap',
    '/student/leaderboard'      => 'InnovationController@leaderboard',
    '/student/badges'           => 'InnovationController@badges',
    '/student/roadmap'          => 'InnovationController@roadmap',
    '/student/certificates'     => 'InnovationController@certificates',
    '/student/certificates/add' => ['handler' => 'InnovationController@addCertificate', 'method' => 'POST'],
    '/student/certificates/{id}/delete' => ['handler' => 'InnovationController@deleteCertificate', 'method' => 'POST'],
    '/student/events'           => 'InnovationController@events',
    '/student/events/{id}/register' => ['handler' => 'InnovationController@registerEvent', 'method' => 'POST'],
    '/student/forum'            => 'InnovationController@forum',
    '/student/forum/{id}'       => 'InnovationController@forumTopic',
    '/student/forum/create'     => ['handler' => 'InnovationController@createForumTopic', 'method' => 'POST'],
    '/student/forum/{id}/comment' => ['handler' => 'InnovationController@addForumComment', 'method' => 'POST'],
    '/student/forum/{id}/delete' => ['handler' => 'InnovationController@deleteForumTopic', 'method' => 'POST'],
    '/student/forum/comment/{id}/delete' => ['handler' => 'InnovationController@deleteComment', 'method' => 'POST'],
    '/student/mentors'          => 'InnovationController@mentors',
    '/student/mentors/{id}/book' => ['handler' => 'InnovationController@bookMentor', 'method' => 'POST'],
    '/student/mentors/{id}/rate' => ['handler' => 'InnovationController@rateMentor', 'method' => 'POST'],

    // ============================================
    // PUBLIC VERIFICATION (QR code / certificate)
    // ============================================
    '/verify'                   => 'InnovationController@verifyLanding',
    '/verify/{code}'            => 'InnovationController@verifyByCode',

    // ============================================
    // EMPLOYER ROUTES
    // ============================================
    '/employer/dashboard'           => 'EmployerController@dashboard',
    '/employer/company'             => 'EmployerController@company',
    '/employer/company/update'      => ['handler' => 'EmployerController@updateCompany', 'method' => 'POST'],
    '/employer/post-job'            => 'EmployerController@postJob',
    '/employer/jobs/store'          => ['handler' => 'EmployerController@storeJob', 'method' => 'POST'],
    '/employer/jobs'                => 'EmployerController@jobs',
    '/employer/jobs/{id}/applicants'=> 'EmployerController@applicants',
    '/employer/jobs/{id}/update'    => ['handler' => 'EmployerController@updateJob', 'method' => 'POST'],
    '/employer/jobs/{id}/delete'    => ['handler' => 'EmployerController@deleteJob', 'method' => 'POST'],
    '/employer/applications/{id}/status' => ['handler' => 'EmployerController@updateApplicationStatus', 'method' => 'POST'],
    '/employer/internships'         => 'EmployerController@internships',
    '/employer/internships/store'   => ['handler' => 'EmployerController@storeInternship', 'method' => 'POST'],
    '/employer/internships/{id}/update' => ['handler' => 'EmployerController@updateInternship', 'method' => 'POST'],
    '/employer/internships/{id}/delete' => ['handler' => 'EmployerController@deleteInternship', 'method' => 'POST'],
    '/employer/freelance'           => 'EmployerController@freelance',
    '/employer/freelance/store'     => ['handler' => 'EmployerController@storeFreelance', 'method' => 'POST'],
    '/employer/freelance/{id}/update' => ['handler' => 'EmployerController@updateFreelance', 'method' => 'POST'],
    '/employer/freelance/{id}/delete' => ['handler' => 'EmployerController@deleteFreelance', 'method' => 'POST'],
    '/employer/messages'            => 'MessageController@inbox',
    '/employer/settings'            => 'EmployerController@settings',

    // ============================================
    // UNIVERSITY ROUTES
    // ============================================
    '/university/dashboard'  => 'UniversityController@dashboard',
    '/university/students'   => 'UniversityController@students',
    '/university/students/add' => ['handler' => 'UniversityController@addStudent', 'method' => 'POST'],
    '/university/students/{id}/update' => ['handler' => 'UniversityController@updateStudent', 'method' => 'POST'],
    '/university/students/{id}/remove' => ['handler' => 'UniversityController@removeStudent', 'method' => 'POST'],
    '/university/reports'    => 'UniversityController@reports',
    '/university/messages'   => 'MessageController@inbox',

    // ============================================
    // MENTOR ROUTES
    // ============================================
    '/mentor/dashboard'          => 'MentorController@dashboard',
    '/mentor/sessions'           => 'MentorController@sessions',
    '/mentor/sessions/{id}/confirm'  => ['handler' => 'MentorController@confirmSession', 'method' => 'POST'],
    '/mentor/sessions/{id}/cancel'   => ['handler' => 'MentorController@cancelSession', 'method' => 'POST'],
    '/mentor/sessions/{id}/complete' => ['handler' => 'MentorController@completeSession', 'method' => 'POST'],
    '/mentor/messages'           => 'MessageController@inbox',
    '/mentor/profile/update'     => ['handler' => 'MentorController@updateProfile', 'method' => 'POST'],

    // ============================================
    // ADMIN ROUTES
    // ============================================
    '/admin/dashboard'           => 'AdminController@dashboard',
    '/admin/users'               => 'AdminController@users',
    '/admin/users/{id}/status'   => ['handler' => 'AdminController@updateUserStatus', 'method' => 'POST'],
    '/admin/users/create'        => ['handler' => 'AdminController@createUser', 'method' => 'POST'],
    '/admin/users/{id}/update'   => ['handler' => 'AdminController@updateUser', 'method' => 'POST'],
    '/admin/users/{id}/delete'   => ['handler' => 'AdminController@deleteUser', 'method' => 'POST'],
    '/admin/jobs'                => 'AdminController@manageJobs',
    '/admin/jobs/{id}/status'    => ['handler' => 'AdminController@updateJobStatus', 'method' => 'POST'],
    '/admin/jobs/{id}/update'    => ['handler' => 'AdminController@updateJob', 'method' => 'POST'],
    '/admin/jobs/{id}/delete'    => ['handler' => 'AdminController@deleteJob', 'method' => 'POST'],
    '/admin/internships'         => 'AdminController@manageInternships',
    '/admin/internships/{id}/status' => ['handler' => 'AdminController@updateInternshipStatus', 'method' => 'POST'],
    '/admin/internships/{id}/update' => ['handler' => 'AdminController@updateInternship', 'method' => 'POST'],
    '/admin/internships/{id}/delete' => ['handler' => 'AdminController@deleteInternship', 'method' => 'POST'],
    '/admin/applications'        => 'AdminController@applications',
    '/admin/certificates'        => 'AdminController@certificates',
    '/admin/certificates/{id}/verify' => ['handler' => 'AdminController@verifyCertificate', 'method' => 'POST'],
    '/admin/certificates/{id}/delete' => ['handler' => 'AdminController@deleteCertificate', 'method' => 'POST'],
    '/admin/payments'            => 'AdminController@payments',
    '/admin/payments/{id}/refund' => ['handler' => 'AdminController@refundPayment', 'method' => 'POST'],
    '/admin/analytics'           => 'AdminController@analytics',
    '/admin/security'            => 'AdminController@security',
    '/admin/system-health'       => 'AdminController@systemHealth',
    '/admin/backup'              => 'AdminController@backup',
    '/admin/reports'             => 'AdminController@reports',
    '/admin/messages'            => 'AdminController@messages',
    '/admin/notifications'       => 'AdminController@notifications',
    '/admin/notifications/{id}/delete' => ['handler' => 'AdminController@deleteNotification', 'method' => 'POST'],
    '/admin/email-sms'           => 'AdminController@emailSms',
    '/admin/maintenance'         => 'AdminController@maintenance',
    '/admin/settings'            => 'AdminController@settings',
    '/admin/settings/update'     => ['handler' => 'AdminController@updateSettings', 'method' => 'POST'],
    '/admin/audit-logs'          => 'AdminController@auditLogs',
    '/admin/homepage'            => 'AdminController@homepageManager',
    '/admin/homepage/add'        => ['handler' => 'AdminController@addHomepageContent', 'method' => 'POST'],
    '/admin/homepage/{id}/update' => ['handler' => 'AdminController@updateHomepageContent', 'method' => 'POST'],
    '/admin/homepage/{id}/delete' => ['handler' => 'AdminController@deleteHomepageContent', 'method' => 'POST'],

    // ============================================
    // AJAX / API-LIKE ROUTES
    // ============================================
    '/api/notifications'         => ['handler' => 'StudentController@getNotifications', 'method' => 'GET'],
    '/api/notifications/read'    => ['handler' => 'StudentController@markNotificationsRead', 'method' => 'POST'],
    '/api/notifications/delete/{id}' => ['handler' => 'StudentController@deleteNotification', 'method' => 'POST'],
    '/api/notifications/clear'   => ['handler' => 'StudentController@clearAllNotifications', 'method' => 'POST'],
    '/api/messages/unread'       => ['handler' => 'StudentController@getUnreadCount', 'method' => 'GET'],
    '/api/messages/conversation/{userId}' => ['handler' => 'MessageController@getConversation', 'method' => 'GET'],
    '/api/messages/send'         => ['handler' => 'MessageController@send', 'method' => 'POST'],
    '/api/messages/mark-read/{userId}' => ['handler' => 'MessageController@markConversationRead', 'method' => 'POST'],
    '/api/language/set'          => ['handler' => 'AuthController@setLanguage', 'method' => 'POST'],

    // ============================================
    // UNIVERSAL ACCOUNT UPDATE (all roles)
    // ============================================
    '/account/update'            => ['handler' => 'AuthController@updateAccount', 'method' => 'POST'],

];
