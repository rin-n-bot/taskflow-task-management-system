# AGENTS.md

Coding standards for this project. Load every turn. **What to build is in
`project_scope.md`.** Rubric is in `final_project_guide.md`. If this file and
the scope conflict, the scope wins.

## Files in this repo
- `AGENTS.md` — this file. Rules. Load every turn.
- `project_scope.md` — app spec, phases, tests. Load when building.
- `final_project_guide.md` — professor's rubric. Load for audit / presentation.

## Context
**Task Flow** — team task manager for WS101 (BSIT, HCDC). Plain PHP + MySQL
(PDO) + basic HTML/CSS on XAMPP. Admin manages projects/tasks/users; members
work on assigned tasks. Two roles, four related tables.

---

## PHP standards
- Procedural only. No OOP, classes, namespaces, traits, interfaces, `match`,
  arrow functions, strict types.
- No frameworks, no Composer for app logic, no JS/CSS libraries. Minimal plain
  JavaScript is allowed for row action dialogs and delete confirmation.
- Code must be simple enough for a student to explain line by line.
- 4-space indent. One blank line between functions and top-level blocks.
- Comment: validation rules, access checks, prepared statements, non-obvious
  logic. Don't comment the obvious.
- No duplicated logic. Shared code → `includes/functions.php` or an include.
- Use `BASE_PATH` for `require_once`, `BASE_URL` for links/redirects/CSS.
  Never relative paths.

## Naming
- DB columns + PHP variables: `snake_case`.
- Functions: `camelCase`.
- Files + folders: lowercase.
- Constants: `UPPER_SNAKE_CASE`.

## Database standards
- PDO only. One connection file: `config/database.php`.
- Options: `ERRMODE_EXCEPTION`, `ATTR_EMULATE_PREPARES => false`,
  charset `utf8mb4`.
- Prepared statements on every query with user input. No concatenation.
- Cast IDs to `int` and check existence where relevant.

## Security standards
- `e()` (i.e. `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`) on every output of
  user data. Escape on output only, never on input.
- `password_hash()` / `password_verify()` only. Never store or display plain
  passwords.
- `session_regenerate_id(true)` on login. Clear session + cookie + destroy on
  logout.
- `requireLogin()` on every page except login and register.
- `requireRole('admin')` on every admin page.
- Ownership check on member-facing task pages (`assigned_to === $_SESSION['user_id']`).
- Deletes use **POST**, not GET.
- Uploads: check extension + MIME (`finfo` + `getimagesize`) + size, generate
  unique filename, save to `uploads/` (with `.htaccess`: `php_flag engine off`).
- Credentials only in `config/database.php`. Never echo them.

## Error handling
- Every DB call inside `try/catch (PDOException $e)`.
- On catch: `logError($e->getMessage())` + show a friendly message
  (*"Something went wrong. Please try again."*).
- Never display raw errors, SQL, or stack traces. `display_errors` off.
- Handle edge cases: record not found, duplicate email, empty results, failed
  upload, FK `SQLSTATE 23000` on user delete.

## Validation (server-side, every form)
- Required fields non-empty after trimming.
- Lengths match column sizes.
- Email: `filter_var(..., FILTER_VALIDATE_EMAIL)`.
- Password: min 8 chars.
- Dates: `DateTime::createFromFormat('Y-m-d', ...)` and re-format must match
  input (rejects `2026-02-30`).
- Enums: check against explicit allowed array.
- On error: show messages near the field + keep entered values (sticky).

## UI standards
- Use only classes in `assets/css/style.css`: `container`, `row`, `stack`,
  `layout`, `sidebar`, `content`, `btn`, `btn-primary`, `btn-danger`, `field`,
  `field.has-error`, `error`, `table`, `badge-*`, `alert-*`, `empty`,
  `is-overdue`.
- Shared navigation and the theme toggle use the left sidebar; it moves above
  the main content on small screens. Login and registration screens use a split
  layout without the sidebar and keep the theme toggle and link to the other auth
  action beside the form.
- Pages do not display a shared footer. `includes/layout_end.php` closes the
  shared page layout.
- No inline styles. No new classes per page. If a pattern is missing, ask.
- Theme: `body.theme-light` / `body.theme-dark` + CSS variables. Cookie
  accepts `light` or `dark` only.

## Required functions (do not rename)
- `includes/functions.php`: `sanitize`, `e`, `logError`, `validateTask`,
  `validateUser`.
- `includes/auth.php`: `isLoggedIn`, `requireLogin`, `requireRole`.

## Workflow
- **One phase per prompt.** Never build the whole app at once.
- One-shot is fine only for pure boilerplate: scaffold, SQL seed, docs.
- Every other feature = vertical slice (form + handler + query in the same
  file), tested before starting the next phase.
- After each phase: read every file, run the relevant test cases from
  `project_scope.md` §7, `git commit`, next phase.
- If you can't explain a line, ask before continuing.
- Never skip the audit phase.

## Do NOT
- OOP, classes, namespaces, `match`, arrow functions.
- Frameworks, Composer, JS/CSS libraries, MySQLi, relative paths.
- Echo without `e()`. SQL concatenation with input. Raw errors to user.
- Build anything outside `project_scope.md`.
- Inline styles or new CSS classes.
- Skip the audit phase.

## When unsure
1. Check `project_scope.md`.
2. Out of scope? Don't build. Ask.
3. Scope wins over this file on conflicts.
4. Fix root causes, not symptoms.
5. Don't assume what exists.
