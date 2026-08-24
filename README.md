# SkillSystem — Premium Student Skills & Career Management Platform

A commercial-grade SaaS platform connecting student talent with real-world opportunities. Built with PHP MVC, MySQL, Bootstrap 5, Chart.js, and a custom design system with light/dark mode.

> Redesigned & upgraded while preserving the existing architecture, routes, controllers, models, authentication, and database schema.

---

## ✨ Features

### Core Platform
- **5 role-based dashboards** (Student, Employer, University, Mentor, Admin) with stat cards, charts, and activity feeds
- **Premium homepage** with hero, search, featured jobs/internships, top companies/universities, featured students, testimonials, FAQ, newsletter
- **Auth system** with login, register, forgot-password, role selection, password strength meter
- **Profile management** with cover photo, avatar, skills, education, experience, portfolio, certificates, resume, social links, completion %

### Innovation Features
- 🤖 **AI Resume Score** — rule-based 0-100 score across 7 dimensions (profile, skills, education, experience, portfolio, certificates, resume file)
- 🎯 **Skill Gap Analysis** — compares your skills to in-demand skills from real job postings
- 🏆 **Student Leaderboard** — computed ranking with podium for top 3
- 🏅 **Achievement Badges** — auto-awarded based on criteria (profile completion, skills, portfolio, applications, certificates)
- 🗺️ **Career Roadmap** — personalized milestone tracker
- 📜 **Certificates + QR Verification** — every certificate gets a unique code + public verification page at `/verify/{code}`
- 📅 **Events** — workshops, job fairs, webinars with registration
- 💬 **Discussion Forum** — categorized topics with comments
- 🎓 **Mentorship** — browse mentors and book 1-on-1 sessions

### UI/UX
- Light & dark mode (toggle in navbar/topbar, persisted via cookie + localStorage)
- Glassmorphism, gradients, soft shadows, rounded cards (16-20px)
- Animated counters, skeleton loaders, scroll reveals, page loader
- Collapsible sidebar with role-based menu
- Sticky glassmorphism topbar with global search, notifications bell (AJAX), messages, profile dropdown
- Advanced tables: search, sort, filter, CSV/print export, bulk actions, responsive
- Floating-label forms with client-side validation, file upload preview, password strength meter
- Real-time-style chat with typing indicator, message bubbles, conversation list
- Chart.js dashboards: line, bar, doughnut, area charts with theme-aware colors

### Security
- CSRF protection on every form (token rotation after validation)
- XSS protection via `htmlspecialchars()` on all output
- SQL injection protection via PDO prepared statements
- Password hashing with bcrypt
- Server-side input validation (`Validator` helper)
- Audit logs for admin actions
- Session-based auth with role checks

---

## 🚀 Installation (XAMPP)

### 1. Clone/copy project
Copy the `skullsystem/` folder into your XAMPP `htdocs/` directory.

### 2. Create the database
- Open phpMyAdmin (`http://localhost/phpmyadmin`)
- Create a new database named `skillsystem`
- Select the database → **Import** tab → choose `database/skillsystem.sql` → **Go**
- Then import `database/migration_innovation.sql` (adds badges, mentorship_sessions, event_registrations, career_roadmaps, qr_verifications, ai_analyses tables + verification_code column on certificates)

### 3. Configure environment
Copy `.env.example` to `.env` and edit:
```
DB_HOST=localhost
DB_NAME=skillsystem
DB_USER=root
DB_PASS=
APP_URL=http://localhost/skullsystem
APP_NAME=SkillSystem
```

### 4. Visit the setup page
Open `http://localhost/skullsystem/setup` to verify everything is configured correctly.

### 5. Log in
Use any of the demo accounts below (password: `password`):

| Role | Email |
|---|---|
| Admin | ethiennemugisha35@gmail.com |
| Student | jean.pierre@ur.ac.rw |
| Employer | admin@rwandatech.rw |
| University | admin@universityofrwanda.rw |
| Mentor | marie.claire@mentor.rw |

---

## 📂 Project Structure

```
skullsystem/
├── app/
│   ├── Config/
│   │   ├── App.php              # App constants (APP_URL, APP_NAME, paths)
│   │   └── Database.php         # PDO singleton
│   ├── Controllers/
│   │   ├── BaseController.php   # View renderer, shared data
│   │   ├── HomeController.php   # Public homepage + setup
│   │   ├── AuthController.php   # Login/register/logout
│   │   ├── StudentController.php
│   │   ├── EmployerController.php
│   │   ├── UniversityController.php
│   │   ├── MentorController.php
│   │   ├── AdminController.php
│   │   └── InnovationController.php   # ⭐ NEW: AI score, badges, etc.
│   ├── Helpers/
│   │   ├── URL.php              # URL builder, redirect, current
│   │   ├── Session.php          # Session wrapper, role checks
│   │   ├── CSRF.php             # CSRF token gen/validate
│   │   ├── Flash.php            # Flash messages (success/error/warning/info)
│   │   ├── Upload.php           # File upload handler
│   │   ├── Validator.php        # Rule-based validation
│   │   ├── Theme.php            # ⭐ NEW: theme detection + chart colors
│   │   ├── Component.php        # ⭐ NEW: reusable UI (statCard, emptyState, etc.)
│   │   └── AiScorer.php         # ⭐ NEW: rule-based AI (resume score, skill gap, roadmap)
│   ├── Libraries/
│   │   └── Router.php           # Simple regex router
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   └── RoleMiddleware.php
│   ├── Models/                  # All extend BaseModel
│   │   ├── BaseModel.php        # find, all, where, create, update, delete, paginate, query
│   │   ├── UserModel.php
│   │   ├── StudentModel.php
│   │   ├── EmployerModel.php
│   │   ├── JobModel.php
│   │   ├── InternshipModel.php
│   │   ├── ApplicationModel.php
│   │   ├── FreelanceModel.php
│   │   ├── MessageModel.php
│   │   ├── NotificationModel.php
│   │   ├── AdminModel.php
│   │   ├── BadgeModel.php           # ⭐ NEW
│   │   ├── LeaderboardModel.php     # ⭐ NEW
│   │   ├── CertificateModel.php     # ⭐ NEW
│   │   ├── EventModel.php           # ⭐ NEW
│   │   ├── ForumModel.php           # ⭐ NEW
│   │   └── MentorshipModel.php      # ⭐ NEW
│   └── Views/
│       ├── layouts/             # app.php (sidebar+topbar), landing.php, auth.php
│       ├── home/                # index.php, 404.php
│       ├── auth/                # login.php, register.php, forgot-password.php
│       ├── student/             # 22 view files (8 original + 12 innovation + dashboard + 1)
│       ├── employer/            # 8 view files
│       ├── admin/               # 6 view files
│       ├── university/          # 3 view files
│       ├── mentor/              # 2 view files
│       └── verify/              # landing.php, result.php (public QR verification)
├── database/
│   ├── skillsystem.sql          # Original schema + seed data
│   └── migration_innovation.sql # ⭐ NEW: badges, mentorship, events_reg, roadmaps, qr, ai_analyses
├── public/
│   └── assets/
│       ├── css/app.css          # ⭐ Premium design system (1,500+ lines)
│       ├── js/app.js            # ⭐ JS engine (500+ lines)
│       ├── icons/
│       ├── images/
│       └── uploads/             # User uploads (avatars, portfolios, resumes)
├── routes/
│   ├── web.php                  # All routes
│   └── api.php
├── .env.example
├── .env                         # Your config (not in repo)
├── composer.json
└── index.php                    # Front controller
```

---

## 🎨 Design System

### Color palette (Indigo + Slate — LinkedIn-inspired)
- **Primary:** `#4F46E5` (light) / `#6366F1` (dark)
- **Text:** `#0F172A` (light) / `#F1F5F9` (dark)
- **Background:** `#F8FAFC` (light) / `#0B1120` (dark)
- Status colors: success `#10B981`, warning `#F59E0B`, danger `#EF4444`, info `#3B82F6`

### Key CSS classes
| Class | Purpose |
|---|---|
| `.ss-card`, `.ss-card-glass`, `.ss-card-gradient` | Card containers |
| `.ss-stat-card` | Stat card with icon + value + trend |
| `.ss-table-wrap`, `.ss-table`, `.ss-table-toolbar` | Advanced table |
| `.ss-float` | Floating-label form input |
| `.ss-badge`, `.ss-chip`, `.ss-avatar`, `.ss-progress` | UI primitives |
| `.ss-timeline`, `.ss-roadmap`, `.ss-leaderboard-item` | Specialized layouts |
| `.ss-chat`, `.ss-chat-list`, `.ss-chat-window` | Messaging UI |
| `.ss-calendar-grid`, `.ss-job-card`, `.ss-logo-card` | Domain components |
| `.ss-skeleton`, `.ss-page-loader` | Loading states |
| `.ss-animate-fade-up`, `.ss-reveal`, `.ss-delay-1..6` | Animations |

### JS data attributes
| Attribute | Purpose |
|---|---|
| `data-theme-toggle` | Theme switch button |
| `data-count="1234"` | Animated counter |
| `data-validate` | Form validation |
| `data-password-strength="#id"` | Password strength meter |
| `data-file-preview="#id"` | File upload preview |
| `data-table` | Sortable/searchable/exportable table |
| `data-table-search` | Table search input |
| `data-table-export="csv|print"` | Export buttons |
| `data-live-search="#list"` | Live search filter |
| `data-tab="#pane-id"` | Tab navigation |
| `data-sidebar-toggle`, `data-sidebar-collapse` | Sidebar controls |
| `data-notif-endpoint`, `data-notif-badge`, `data-mark-all-read` | AJAX notifications |

---

## 🤖 AI Features (Rule-Based)

The `AiScorer` helper (`app/Helpers/AiScorer.php`) provides deterministic, rule-based AI:

- **`resumeScore($studentId)`** → 0-100 score with 7-dimension breakdown + personalized suggestions
- **`careerRecommendations($userId, $studentId, $limit)`** → jobs matching student skills + department, scored by keyword overlap
- **`skillGap($studentId, $targetRole)`** → matched vs missing skills with demand counts from real job postings
- **`suggestRoadmap($studentId)`** → milestone list adjusted by year of study

The architecture supports swapping in an LLM API later — just replace the method bodies with calls to OpenAI/Claude.

---

## 🛣️ Routes

### Public
- `GET /` — Homepage
- `GET /setup` — Setup diagnostic
- `GET /login`, `GET /register`, `GET /forgot-password`
- `POST /auth/login`, `POST /auth/register`, `POST /auth/forgot-password`
- `GET /verify` — Public verification landing
- `GET /verify/{code}` — Verify a certificate

### Student
- `/student/dashboard`, `/student/profile`, `/student/jobs`, `/student/jobs/{id}`
- `/student/applications`, `/student/portfolio`, `/student/resume`
- `/student/messages`, `/student/settings`
- **Innovation:** `/student/ai-score`, `/student/skill-gap`, `/student/leaderboard`
- **Innovation:** `/student/badges`, `/student/roadmap`, `/student/certificates`
- **Innovation:** `/student/events`, `/student/forum`, `/student/forum/{id}`
- **Innovation:** `/student/mentors`

### Employer
- `/employer/dashboard`, `/employer/jobs`, `/employer/post-job`
- `/employer/jobs/{id}/applicants`, `/employer/internships`, `/employer/freelance`
- `/employer/company`, `/employer/settings`

### University
- `/university/dashboard`, `/university/students`, `/university/reports`

### Mentor
- `/mentor/dashboard`, `/mentor/sessions`

### Admin
- `/admin/dashboard`, `/admin/users`, `/admin/jobs`, `/admin/internships`
- `/admin/audit-logs`, `/admin/settings`

### AJAX API
- `GET /api/notifications` — Unread count
- `POST /api/notifications/read` — Mark as read (body: `{id}` or `{all:true}`)
- `GET /api/messages/unread` — Unread message count

---

## 📊 Database Migration

If you're upgrading from the original schema, run `database/migration_innovation.sql` after importing the base `database/skillsystem.sql`. The migration:

1. Creates 6 new tables: `badges`, `student_badges`, `mentorship_sessions`, `event_registrations`, `career_roadmaps`, `qr_verifications`, `ai_analyses`
2. Adds `verification_code` column to the existing `certificates` table
3. Backfills verification codes for existing certificates
4. Seeds 6 default badges (Profile Pioneer, Skill Master, Portfolio Pro, Go-Getter, Certified Pro, Top Talent)

**The migration is additive only** — no existing columns are renamed or dropped, so your existing data is safe.

---

## 🔧 Tech Stack

- **Backend:** PHP 8.0+ (no framework, pure MVC), PDO/MySQL
- **Frontend:** Bootstrap 5.3, Font Awesome 6.5, Poppins font, Chart.js 4.4
- **CSS:** Custom design system on CSS custom properties (1,500+ lines)
- **JS:** Vanilla JS with `IntersectionObserver`, `fetch`, CSS custom properties (500+ lines)
- **DB:** MySQL 5.7+ / MariaDB 10.4+
- **Server:** Apache (XAMPP) with `.htaccess` for routing

---

## 📝 License

This project is licensed for educational and commercial use. Built with ❤️ in Kigali, Rwanda.
