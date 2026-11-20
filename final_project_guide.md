WS101 Final Project Guide: PHP and MySQL
Web Application
Web Systems and Technologies (WS101) | BSIT | Holy Cross of Davao College Prepared by:
Instructor John Mari R. Giducos

1. Project Overview
For your final project, you will design and build a database-driven web application using
PHP as the server-side language, MySQL as the database, and basic HTML and CSS for
the frontend. The project is where you demonstrate everything covered in the course:
PHP programming, form processing, MySQL, CRUD operations, validation, error handling,
authentication, security, and a complete working backend.
The topic is your choice, as long as it meets every requirement in this guide. Some ideas:
Inventory management system
Point of Sale (POS) system
Appointment or reservation scheduling
Library, clinic, school, or enrollment record system
Student grade or attendance tracker
Online ordering or food menu system
Task or project tracker
A PHP-based game with accounts, saved progress, and a leaderboard (for example a
text-based RPG, quiz game, or card game)
Important for game projects: the game must still be a real PHP and MySQL application. It
needs user accounts, data stored in the database (players, scores, items, saves), and full
CRUD. A game that only runs in the browser with no PHP and database does not qualify.
Technology rules
Backend: PHP (plain PHP, no frameworks such as Laravel or CodeIgniter)
Database: MySQL (use phpMyAdmin or similar for management)
Frontend: basic HTML and CSS. Keep the design clean and usable; focus your effort
on the backend.
Use only code you understand and can explain. You will be asked to explain your code
during checks and your final presentation.
WS101 Final Project Guide: PHP and MySQL Web Application

Page 1 of 12

2. Learning Outcomes Covered
This project supports the course outcomes in the syllabus:
CO1: Understand web systems, technologies, and protocols to design and develop
secure web applications.
CO2: Use web development tools, manage web content, and apply best practices in
web design, user experience, and security to create high-quality, user-friendly web
systems.

3. Minimum Technical Requirements
Your project is complete only if it contains all of the following. Each item maps to a topic in
the syllabus.
A. PHP Programming (Weeks 3 to 5)
Use of variables, data types, conditions, and loops
Use of arrays (for example, arrays of records, associative arrays for form data or
settings)
Use of custom functions with parameters and return values (at least 5 meaningful
functions, placed in a separate functions file)
Sessions (to keep users logged in) and cookies (for example, remember username or
theme preference)
Basic file handling (for example, profile or product image upload, writing an activity
log file, or exporting a CSV report)
B. Forms and Input Handling
HTML forms processed by PHP
Correct use of GET (for example, search, filter, view a record by ID) and POST (for
example, create, update, login)
Handling and sanitizing user input
C. Form Validation (Server-Side)
Validate all inputs on the server (required fields, correct formats, length limits, valid
numbers and dates, email format)
Show clear error messages next to or above the form
WS101 Final Project Guide: PHP and MySQL Web Application

Page 2 of 12

Keep valid values in the form after an error so users do not retype everything
D. MySQL Database and Relationships (Weeks 6 to 10)
A properly designed database with at least 3 related tables (more is fine)
Appropriate data types, primary keys, and foreign keys
At least one table relationship (one-to-many at minimum) that is actually used in the
app, for example orders and order items, patients and appointments, or players and
scores
Use of JOIN queries to display related data
Submit your database as an exported .sql file
E. CRUD Operations (Weeks 6 to 14)
Create, Read, Update, and Delete working on at least two main tables
A list view with search or filter
Confirmation before deleting a record
PHP connects to MySQL using PDO or MySQLi with a central connection file
F. Error Handling and Debugging
Use try/catch (or equivalent) around database operations
Show friendly error messages to users, never raw database errors or stack traces
Write technical errors to a log file instead of displaying them
Handle edge cases: empty results, record not found, duplicate entries, failed uploads
G. Authentication and Authorization (Weeks 15 to 18)
User registration, login, and logout
Passwords stored using password_hash() and checked with password_verify().
Never store plain-text passwords.
Session-based authentication; protected pages redirect unauthenticated users to
login
At least two user roles (for example admin and staff, or admin and player) with
different access. Admin-only pages must be blocked for other roles.
WS101 Final Project Guide: PHP and MySQL Web Application

Page 3 of 12

H. Backend Security Practices
Prepared statements for all queries that use user input (prevents SQL injection)
Output escaping with htmlspecialchars() wherever user data is displayed (prevents
XSS)
Input validation on every form
Session handling basics (regenerate session ID on login, destroy session on logout)
Database credentials kept in one config file, not scattered through your code
I. Code and File Structure
Organize your project cleanly. Here is a suggested structure (you may adjust, but it must
be organized and consistent):
project-name/
├── index.php (landing or login page)
├── config/
│ └── database.php (DB connection)
├── includes/
│ ├── header.php
│ ├── footer.php
│ ├── functions.php (reusable functions)
│ └── auth.php (login checks, role checks)
├── pages/
│ ├── register.php
│ ├── login.php
│ ├── logout.php
│ ├── dashboard.php
│ └── (module folders, e.g. products/, orders/)
├── assets/
│ ├── css/style.css
│ └── images/
├── uploads/
├── logs/
│ └── error.log
├── database/
│ └── project.sql (exported database)
└── README.md
Coding standard expectations (these are graded):
Consistent and meaningful naming for variables, functions, files, tables, and columns
WS101 Final Project Guide: PHP and MySQL Web Application

Page 4 of 12

Proper indentation and readable code
Comments on important or complex parts
No duplicated code that should be a function or an include
Reuse header, footer, and connection files through include or require

4. Requirements Checklist
Use this as your self-check. Bring it to every Saturday progress check.

# Requirement Done
1 PHP basics: variables, conditions, loops ☐
2 Arrays used meaningfully ☐
3 At least 5 custom functions with parameters ☐
4 Forms with GET and POST used correctly ☐
5 Server-side validation on all forms ☐
6 Sessions and cookies ☐
7 File handling (upload, log, or export) ☐
8 MySQL database with 3 or more related tables ☐
9 Primary keys, foreign keys, appropriate data types ☐
10 Full CRUD on at least 2 tables ☐
11 JOIN query displaying related data ☐
12 Search or filter feature ☐
13 try/catch and friendly error messages ☐
14 Error log file ☐
15 Registration, login, logout ☐
16 password_hash and password_verify ☐
17 Two or more user roles with access control ☐
18 Prepared statements everywhere ☐
WS101 Final Project Guide: PHP and MySQL Web Application

Page 5 of 12

5. Weekly Schedule and Saturday Progress Checks
Every Saturday there will be a progress check. You must show working progress, not just
ideas or files. The expected milestone for each week is below, aligned with our course
outline. Falling behind will be visible quickly, so keep pace.
# Requirement Done
19 Output escaping (htmlspecialchars) ☐
20 Organized folder and file structure ☐
21 Tested and debugged (test log or test cases) ☐
22 Database .sql file exported ☐
23 README and manuscript completed ☐

Week Topics in Class Project Milestone to Show on Saturday
1 Orientation, introduction to backend
development

Form your team (if allowed) and
brainstorm 2 to 3 project ideas

2 Overview of backend, intro to PHP and
server-side programming

Project proposal submitted (title,
problem, users, main features, list of
tables). Set up XAMPP (or similar) and a
project folder.

3 PHP arrays, functions and parameters Folder structure created; working
functions file; a simple PHP page that
uses arrays and functions

4 HTML forms, PHP form processing, GET
and POST, handling user input

Main forms designed in HTML/CSS and
processed by PHP (data displayed after
submit)

5 Server-side validation, sessions and
cookies, basic file handling

Validation working on forms; session or
cookie demo; file handling feature
(upload or log) started

6 Intro to MySQL and databases, creating
databases and tables

Database design finalized (ERD or
table list with keys). Tables created in
phpMyAdmin.

WS101 Final Project Guide: PHP and MySQL Web Application

Page 6 of 12

Week Topics in Class Project Milestone to Show on Saturday
7 Data types and keys, basic SQL queries Tables populated with sample data; key
SQL queries tested; connection file
created

8 CRUD operations in MySQL SQL for create, read, update, delete
written and tested in phpMyAdmin
9 Table relationships Foreign keys set; JOIN query tested;
relationships shown in the ERD

10 Connecting PHP to MySQL, database
operations using PHP

First working CRUD module in PHP
(create and read at minimum, ideally all
four) for one table

11 PHP and MySQL CRUD operations Full CRUD on first table working in the
browser; second table CRUD started
12 Form validation in database-driven apps CRUD on two tables complete, with
validation, search or filter, and delete
confirmation

13 Error handling try/catch added, friendly messages
shown, error log file working
14 Database-driven application integration Prefinal check: all main modules
working together with consistent
navigation and layout

15 User registration and login, sessions and
authentication

Registration, login, logout working with
password hashing; protected pages

16 User roles and authorization, input
validation and security

Roles and access control working;
prepared statements and escaping
applied everywhere

17 Testing and debugging, developing the
complete application

Feature-complete app; test log
completed; bugs fixed; manuscript
draft and README submitted
18 Project presentation Final presentation and demo (see

Section 8)

WS101 Final Project Guide: PHP and MySQL Web Application

Page 7 of 12

Note: Final dates for presentations and submissions will be announced in class and may be
adjusted according to the academic calendar.
What to bring to every Saturday check
1. Your project running on your laptop (or hosted locally)
2. Your updated requirements checklist
3. Your project files, ready to open and explain
4. Be ready to answer questions about the code you wrote that week
How Saturday checks are scored
Progress checks follow the Machine Problem rubric from our syllabus:

Scoring per criterion: 3 (Excellent), 2 (Good), 1 (Fair), 0 (Poor), following the syllabus
rubric.
If you miss a Saturday check, the progress for that week is scored as zero in accordance
with the course policy on missed assessments, so coordinate with me ahead of time if you
have a valid reason.

6. Deliverables
1. Project proposal (Week 2)
Project title and short description
Problem or need it addresses
Target users and user roles
List of main features and modules
Criteria Weight What I look for
Functionality 80% The week's milestone works accurately in the

browser

Coding Standard 10% Well-structured code, proper naming conventions,

consistent practices

Submission Time 10% Submitted or shown on time (earlier submissions

score higher)

WS101 Final Project Guide: PHP and MySQL Web Application

Page 8 of 12

Initial list of database tables
2. Working web application (source code, complete folder)
3. Database file (exported .sql with sample data)
4. README.md, which must contain:
Project name and team members
Setup instructions (how to run it on a fresh XAMPP install, import the database)
Default admin account login for testing
Short list of features
5. Project manuscript (proposal version and final version), containing:
Introduction and objectives
Description of the system and its users
Entity relationship diagram and table descriptions
Screenshots of the main pages
Explanation of how each syllabus requirement is implemented (for example,
where validation, error handling, and authentication are in your code)
Test cases and results
Challenges encountered and lessons learned
6. Final presentation and live demo

7. Testing and Debugging
Before the final presentation, test your application thoroughly. Prepare a test log table
like this:

Test Case Input Expected Result Actual
Result

Pass/Fail

Login with wrong
password

valid username,
wrong password

Error message, no
access

Submit empty
required field

blank name Validation error
shown

Delete a record click delete, confirm Record removed,
list updated

WS101 Final Project Guide: PHP and MySQL Web Application

Page 9 of 12

Include at least 15 test cases covering normal use, invalid input, and security.

8. Final Presentation
Each team or student will present and demonstrate the live application.
Presentation flow (suggested 10 to 15 minutes plus questions)
1. Introduce the system: problem, users, and goals
2. Show the database design and explain the relationships
3. Live demo: registration, login, CRUD operations, search, roles, and validation
4. Show errors being handled (for example, wrong input, duplicate entry)

5. Walk through key code: a prepared statement, a validation function, and an access-
control check

6. Share challenges encountered and lessons learned
Be ready for:
Questions about any part of your code
Live changes or small modifications requested by the instructor
Questions about your database design and security choices
Every member of a team must be able to explain any part of the project. Inability to
explain your own submitted code will affect your score.

9. Grading
The project is assessed through the following components, based on the syllabus:
Test Case Input Expected Result Actual
Result

Pass/Fail

Access admin page
as staff

direct URL Access blocked or
redirected

SQL injection
attempt

' OR '1'='1 in login Rejected, no error

shown

WS101 Final Project Guide: PHP and MySQL Web Application

Page 10 of 12

Final Project Scoring Guide (suggested)

The instructor may adjust the weights as announced in class.

10. Rules and Academic Integrity
Your project must be original work. Copying another group's project, or downloading
a full ready-made system, will result in a failing mark for the project.
You may use tutorials, documentation (php.net, PHP The Right Way, MySQLTutorial),
and our course references for learning. Understand and adapt what you use; do not
paste code you cannot explain.
Component Basis
Weekly Saturday progress checks Machine Problem rubric (Functionality 80%,
Coding Standard 10%, Submission Time 10%)

Project manuscript (proposal and
final)

Project Manuscript rubric (Grammar 20%,
Organization 40%, Completeness 40%)
Final project and presentation Requirements checklist, functionality, security,

code quality, and ability to explain

Criteria Points
Functionality: all CRUD, search, forms, and
features work correctly

30

Database design: relationships, keys, data types,
JOINs

15

Validation and error handling 15
Authentication, roles, and security practices 20
Code and file structure, naming, comments 10
User interface and usability (clean HTML and CSS) 5
Presentation, demo, and Q&A 5
Total 100
WS101 Final Project Guide: PHP and MySQL Web Application

Page 11 of 12

If you use AI tools or online code snippets for help, you must be able to explain every
line during checks and presentation.
All members of a team must contribute. Contribution may be checked through code
explanations and project history.
Follow the classroom policies in the syllabus on attendance and submission. A missed
exam, quiz, or project component is computed as zero.
Keep backups. Use a USB drive, cloud storage, or Git. Lost files are not an excuse for a
missed deadline.

11. Tips for Success
1. Start small, then grow. Get one table fully working (create, read, update, delete)
before building the rest.
2. Design the database first. A good ERD saves hours of rewriting later.
3. Reuse your code. One connection file, one header, one footer, one functions file.
4. Always validate on the server and always use prepared statements.
5. Test as you build, not only at the end.
6. Don't wait for Saturday. Ask questions in class, during consultation hours, or through
our class channels when you are stuck.
7. Keep the design simple. A clean, consistent layout beats a flashy one that does not
work.

12. References
From the syllabus:
Gerland, J. (2021). Guide to PHP and MySQL. Milne Open Textbooks.
Scott, S. (2023). Full stack web development for beginners. eCampusOntario
Pressbooks.
McFadyen, R. (2021). Relational databases and data management. Open Textbook
Library.
PHP Manual: https://www.php.net/docs.php
PHP The Right Way: https://phptherightway.com/
MySQLTutorial (PHP MySQL): https://www.mysqltutorial.org/php-mysql/