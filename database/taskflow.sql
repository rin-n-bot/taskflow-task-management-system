-- Task Flow database schema and sample data.
-- Import with: mysql -u root < database/taskflow.sql

CREATE DATABASE IF NOT EXISTS taskflow
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE taskflow;

SET NAMES utf8mb4;

-- Re-importing this file rebuilds the sample data from scratch.
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    avatar VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_by INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_projects_created_by
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    assigned_to INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    status ENUM('todo', 'in_progress', 'done') NOT NULL DEFAULT 'todo',
    due_date DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tasks_project
        FOREIGN KEY (project_id) REFERENCES projects (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_tasks_assigned_to
        FOREIGN KEY (assigned_to) REFERENCES users (id)
        ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comments_task
        FOREIGN KEY (task_id) REFERENCES tasks (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_comments_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE = InnoDB;

-- Password hashes below correspond to the demo accounts listed in README.md.
INSERT INTO users (id, name, email, password_hash, role, avatar) VALUES
(1, 'Alex Cruz', 'admin@taskflow.test',
 '$2y$10$.LG9hTYRVK4ZHy1n5xXaMetl6JVXv14ZWPmqrqtIEFVQqg93kCadq', 'admin', NULL),
(2, 'Maria Santos', 'maria@taskflow.test',
 '$2y$10$8yynuF7Bak71twG/ESrcyOpB3vYhlhEj8Rv6MR1PUIn1.S.vPN.zm', 'member', NULL),
(3, 'Jose Reyes', 'jose@taskflow.test',
 '$2y$10$vPUJ2zOoR1ZIAimd6mcbB.HE9Eo/2eivHLFFv.S5Qv.zjtgTXvLH.', 'member', NULL),
(4, 'Ana Dela Cruz', 'ana@taskflow.test',
 '$2y$10$0KjrInhjz1tjlJHWJQ953OAc83gNOrhNoAFl/zlyxZ791hYBVb6EC', 'member', NULL);

INSERT INTO projects (id, created_by, title, description) VALUES
(1, 1, 'Website Redesign',
 'Rebuild the company website with a responsive layout and updated content.'),
(2, 1, 'Mobile App Launch',
 'Prepare the Android and iOS release of the Task Flow companion app.'),
(3, 1, 'Q4 Marketing Campaign',
 'Plan and run the fourth-quarter campaign across email and social media.');

-- Due dates are relative to the import date so overdue examples always exist.
INSERT INTO tasks (id, project_id, assigned_to, title, description, priority, status, due_date) VALUES
(1, 1, 2, 'Set up project repository',
 'Create the Git repository, folder structure, and first commit.',
 'high', 'done', CURDATE() - INTERVAL 20 DAY),
(2, 1, 2, 'Design homepage wireframe',
 'Sketch desktop and mobile layouts for the new homepage.',
 'high', 'in_progress', CURDATE() - INTERVAL 2 DAY),
(3, 1, 3, 'Implement responsive navigation',
 'Build the header and mobile menu with plain HTML and CSS.',
 'medium', 'todo', CURDATE() + INTERVAL 5 DAY),
(4, 1, 4, 'Write content for About page',
 'Draft the company story, mission, and team section.',
 'low', 'done', CURDATE() - INTERVAL 8 DAY),
(5, 1, 3, 'Test on mobile devices',
 'Check the layout on Android and iOS browsers.',
 'high', 'todo', CURDATE() - INTERVAL 1 DAY),
(6, 2, 4, 'Finalize app icon',
 'Deliver the final icon sizes required by both app stores.',
 'medium', 'in_progress', CURDATE() + INTERVAL 3 DAY),
(7, 2, 2, 'Prepare app store listing',
 'Write the description, keywords, and screenshots for the listing.',
 'high', 'todo', CURDATE() + INTERVAL 10 DAY),
(8, 2, 3, 'Record demo video',
 'Record a two-minute walkthrough of the main app features.',
 'medium', 'done', CURDATE() - INTERVAL 6 DAY),
(9, 2, 4, 'Fix login crash on Android',
 'Investigate the crash reported on Android 12 devices.',
 'high', 'in_progress', CURDATE() - INTERVAL 3 DAY),
(10, 3, 3, 'Draft social media calendar',
 'Plan two weeks of posts for Facebook and Instagram.',
 'medium', 'todo', CURDATE() + INTERVAL 7 DAY),
(11, 3, 2, 'Design email newsletter',
 'Create the template and hero image for the November newsletter.',
 'low', 'done', CURDATE() - INTERVAL 12 DAY),
(12, 3, 4, 'Review campaign budget',
 'Compare planned spending against the approved budget.',
 'high', 'in_progress', CURDATE() + INTERVAL 2 DAY),
(13, 3, 3, 'Schedule webinar',
 'Book the platform, date, and speaker for the campaign webinar.',
 'medium', 'todo', CURDATE() + INTERVAL 14 DAY);

INSERT INTO comments (id, task_id, user_id, body) VALUES
(1, 1, 2, 'Repository is online and the first commit is pushed.'),
(2, 2, 1, 'Please add a mobile view before the next review.'),
(3, 2, 2, 'Working on it now; desktop version is almost done.'),
(4, 3, 3, 'Started today. The mobile menu needs one more test.'),
(5, 7, 1, 'Use the new brand colors and keep the text short.'),
(6, 9, 4, 'The crash only happens on Android 12 so far.');
