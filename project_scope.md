# Task Flow: Project Scope

App spec for `AGENTS.md`. Rules live there. Professor's rubric lives in `final_project_guide.md`. Everything below aligns to that guide.

## About Task Flow

**Task Flow** is a team task manager. An admin (manager) creates projects and assigns tasks. Members work on the tasks assigned to them. Two roles, four related tables, full CRUD on projects and tasks.

---

## 1. Roles & Permissions

| Action | Admin | Member |
|---|---|---|
| Register / login / logout | Yes | Yes |
| Edit own profile, upload avatar | Yes | Yes |
| Create / edit / delete projects | Yes | No |
| Create / edit / delete tasks | Yes | No |
| Assign tasks to members | Yes | No |
| View all tasks | Yes | Only own assigned |
| Update task status | Yes | Only own |
| Add comments | Yes | Only on own tasks |
| Delete comments | Any | Own only |
| Manage users (role, delete) | Yes | No |

New registrations always get role `member`. The admin account is seeded in SQL.

---

## 2. Database

Database: `taskflow`. `database/taskflow.sql` creates the tables and seeds only the admin account. Do not include pre-made member, project, task, or comment records. Create demo records through the website, then include those manually entered records in the final database export for the professor.

**users**: id (PK), name VARCHAR(100), email VARCHAR(150) UNIQUE, password_hash VARCHAR(255), role ENUM('admin','member') default 'member', avatar VARCHAR(255) NULL, created_at TIMESTAMP.

**projects**: id (PK), created_by INT FK→users.id, title VARCHAR(150), description TEXT, created_at TIMESTAMP.

**tasks**: id (PK), project_id INT FK→projects.id ON DELETE CASCADE, assigned_to INT FK→users.id, title VARCHAR(150), description TEXT, priority ENUM('low','medium','high') default 'medium', status ENUM('todo','in_progress','done') default 'todo', due_date DATE, created_at TIMESTAMP.

**comments**: id (PK), task_id INT FK→tasks.id ON DELETE CASCADE, user_id INT FK→users.id ON DELETE CASCADE, body TEXT, created_at TIMESTAMP.

Required JOIN for the tasks list (reference structure only, not the complete query):

```sql
-- Reference JOIN only. The actual query in pages/tasks/index.php:
--   - adds WHERE filters (status, priority, assignee_id, project_id, title LIKE keyword)
--   - adds AND t.assigned_to = :uid for members (always, no exceptions)
--   - adds ORDER BY t.due_date ASC, t.created_at DESC
--   - uses prepared statements for every value
SELECT t.*, p.title AS project_title, u.name AS assignee_name
FROM tasks t
JOIN projects p ON t.project_id = p.id
JOIN users u ON t.assigned_to = u.id
```

The single admin seed uses a real `password_hash()` value. Plain password goes in README only. Importing this SQL file drops and recreates the tables, so only import it when setting up a clean database; re-importing deletes existing website data.

---

## 3. Folder Structure

```text
taskflow/
├── index.php
├── config/database.php          (PDO connection, central)
├── includes/{header.php, layout_end.php, functions.php, auth.php}
├── pages/
│   ├── login.php, register.php, logout.php, dashboard.php, profile.php
│   ├── projects/{index,create,edit,delete}.php
│   ├── tasks/{index,create,edit,delete,view,comment_delete}.php
│   └── admin/users.php
├── assets/css/style.css
├── assets/js/action-dialog.js
├── uploads/  (+ .htaccess)
├── logs/error.log
├── database/taskflow.sql
└── README.md
```

README contents: project name, team members, XAMPP setup + SQL import steps, default admin login, feature list, and instructions to create demo records through the website. Warn that importing the SQL file resets the database.

---

## 4. Features

### 4.0 General rules (all pages)
- All forms: validate on the server, show errors above the form, keep submitted values after an error.
- All DB operations in `try/catch`: log technical errors to `logs/error.log`, show only friendly messages.
- Invalid or missing ID (e.g. in `edit.php`, `view.php`) → friendly message + redirect.
- Login and registration pages use a split two-panel layout without the shared
  sidebar. Keep a link to the other account action beside the form.
- Other pages use the shared left sidebar for navigation.
  On small screens, the sidebar moves above the main content.
- Task, project, and admin user list actions open in a modal from a three-dot
  button. Give the modal clear spacing, a close button, Escape-key support, and
  backdrop dismissal. Keep existing role and self-account restrictions.
- Pages do not display a footer. The shared layout closes through
  `includes/layout_end.php`.

### 4.1 Auth
- Register: name, email, password, confirm. Unique email check, `password_hash()`.
- Login: `password_verify()`. On success: `session_regenerate_id(true)`, store `user_id`, `name`, `role`.
- Remember-username checkbox → cookie pre-fills email.
- Logout: clear session + cookie, destroy, redirect to login.

### 4.2 Dashboard
- Admin: task counts by status, overdue count, total projects, total members.
- Member: own task counts by status, own overdue count.
- Overdue = `due_date < CURDATE() AND status != 'done'`.
- The app uses the light theme only; there is no theme toggle.

### 4.3 Projects (admin CRUD)
- List with search-by-title (GET).
- Create/edit: title required (max 150), description max 1000.
- Delete with confirm (warn: tasks + comments also deleted).

### 4.4 Tasks
- Admin: list all, filter by status/priority/assignee/project/keyword (GET), full CRUD. Delete with confirm.
- Member: list own only, same filters except assignee. Can open `view.php` and update status only.
- Fields: project, assigned member, title, description, priority, status, due_date.
- Assignee dropdown lists members only.
- Past due dates allowed (needed for overdue).
- Default sort: `due_date ASC, created_at DESC`.
- Empty result → "No tasks found."

### 4.5 Comments (on `view.php`)
- List comments (JOIN users for commenter name).
- Add comment (POST). Admin or assigned member only.
- Delete: admin any, member own only.

### 4.6 User Management (admin)
- List users, change role, delete.
- Admin cannot delete or demote own account.
- Catch SQLSTATE 23000 on delete → "Reassign or delete this user's tasks and projects first."

### 4.7 Profile
- Edit name.
- Upload avatar: JPG/PNG only, ≤ 2 MB, unique filename in `uploads/`, save filename in `users.avatar`. Clear message for wrong type / too large / upload error.

### 4.8 Optional (only after core is tested)
- Board view: To Do / In Progress / Done columns, "Move to..." dropdown via POST form. No drag-and-drop, no JS.
- Overdue highlight: red when `due_date < today` and `status != done`.

### 4.9 Out of scope
Drag-and-drop, AJAX, real-time, email notifications, REST APIs, frameworks, JS libraries, multi-tenancy, task attachments, password reset, password change page.

---

## 5. Build Phases

Aligned to the professor's weekly milestones (`final_project_guide.md` §5). Auth is front-loaded so CRUD never needs hardcoded user ids.

| # | Phase | Build | Professor's week |
|---|---|---|---|
| 1 | Proposal | Title, problem, users/roles, features, table list | 2 |
| 2 | Scaffold | Folders, `config/database.php` (PDO), `includes/functions.php` and `includes/auth.php` (function names per `AGENTS.md`), shared header/layout includes, `style.css`, `index.php`, placeholders, `.htaccess`, empty log | 3 |
| 3 | Database | `taskflow.sql` with schema + only the admin seed (real `password_hash`); create demo records through the website | 6–9 |
| 4 | Auth | register, login, logout, remember-username cookie | 10 (guide: 15) |
| 5 | Projects | full CRUD + search + confirm delete | 10–11 |
| 6 | Tasks list + view | JOIN, filters, ownership check, status update | 11 |
| 7 | Tasks CRUD | create, edit, delete + validation | 11–12 |
| 8 | Comments | add + delete with role/ownership | 12 |
| 9 | Users | list, change role, delete + SQLSTATE 23000 catch | 15–16 |
| 10 | Profile | name + avatar upload | 17 |
| 11 | Dashboard | role-aware counts and overdue summary | 14 |
| 12 | Audit + docs | walk every file against `AGENTS.md`; README + 20-row test log; export manually entered demo records; manuscript | 13, 17 |
| 13 | Presentation | Demo: DB → register/login → CRUD → filters → roles → validation → errors → code walkthrough | 18 |

Functions are added when first needed: `sanitize`, `e`, `logError` at scaffold; `validateUser` in Phase 4; `validateTask` in Phase 7. Full list and signatures live in `AGENTS.md`.

---

## 6. Definition of Done (professor's checklist)

- [ ] Variables, conditions, loops
- [ ] Arrays used meaningfully
- [ ] ≥ 5 custom functions with parameters
- [ ] GET + POST used correctly
- [ ] Server-side validation on all forms
- [ ] Sessions + cookies
- [ ] File handling (avatar upload and error log)
- [ ] 3+ related tables with PKs/FKs
- [ ] Full CRUD on ≥ 2 tables
- [ ] JOIN query displaying related data
- [ ] Search or filter
- [ ] try/catch + friendly messages
- [ ] Error log file
- [ ] Registration, login, logout
- [ ] `password_hash` + `password_verify`
- [ ] Two roles with access control
- [ ] Prepared statements everywhere
- [ ] `htmlspecialchars` on output
- [ ] Organized folder structure
- [ ] Tested (test log)
- [ ] Database `.sql` exported
- [ ] README + manuscript

---

## 7. Test Cases (20)

Record in a table: `Test Case | Input | Expected | Actual | Pass/Fail`.

1. Login wrong password → error, no access.
2. Login empty fields → validation errors.
3. SQL injection `' OR '1'='1` in login → rejected, no error shown.
4. Register invalid email → validation error.
5. Register duplicate email → friendly message.
6. Register short password → validation error.
7. Create project empty title → error, values kept.
8. Create task invalid date → error.
9. Create task valid → appears in list with project + assignee.
10. XSS `<script>alert(1)</script>` in title → displayed as text.
11. Member opens admin page by URL → blocked.
12. Member opens other member's task by URL → blocked.
13. Logged-out user opens dashboard → redirected to login.
14. Delete project → cancel → record remains.
15. Delete project → confirm → record + its tasks/comments gone.
16. Search with no matches → "No results" message.
17. Upload non-image avatar → rejected with message.
18. Avatar upload larger than 2 MB → rejected with a clear message.
19. Admin deletes own account → blocked.
20. Member updates own task status → saved.
