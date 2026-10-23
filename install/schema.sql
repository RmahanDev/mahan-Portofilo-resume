-- Portfolio database schema (MySQL / MariaDB, utf8mb4)

CREATE TABLE IF NOT EXISTS admins (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(60)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    last_login_at DATETIME     NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
    ip         VARCHAR(45) NOT NULL PRIMARY KEY,
    attempts   INT UNSIGNED NOT NULL DEFAULT 0,
    last_at    DATETIME    NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    k VARCHAR(100) NOT NULL PRIMARY KEY,
    v MEDIUMTEXT   NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS socials (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    icon VARCHAR(60)  NOT NULL,
    url  VARCHAR(500) NOT NULL,
    sort INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    description TEXT         NOT NULL,
    icon        VARCHAR(500) NOT NULL DEFAULT '',
    icon_alt    VARCHAR(200) NOT NULL DEFAULT '',
    sort        INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS skills (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    text VARCHAR(300) NOT NULL,
    sort INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS experiences (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(200) NOT NULL,
    avatar    VARCHAR(500) NOT NULL DEFAULT '',
    caption   TEXT         NOT NULL,
    role      VARCHAR(200) NOT NULL DEFAULT '',
    body_html MEDIUMTEXT   NOT NULL,
    sort      INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resume_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section     ENUM('education','experience') NOT NULL,
    title       VARCHAR(300) NOT NULL,
    period      VARCHAR(200) NOT NULL DEFAULT '',
    description TEXT         NOT NULL,
    sort        INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS skill_groups (
    id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    sort  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS skill_bars (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id INT UNSIGNED NOT NULL,
    label    VARCHAR(200) NOT NULL,
    percent  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    sort     INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_bar_group FOREIGN KEY (group_id) REFERENCES skill_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    sort INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug           VARCHAR(150) NOT NULL UNIQUE,
    title          VARCHAR(300) NOT NULL,
    card_category  VARCHAR(200) NOT NULL DEFAULT '',
    cover          VARCHAR(500) NOT NULL DEFAULT '',
    heading_html   TEXT         NOT NULL,
    header_image   VARCHAR(500) NOT NULL DEFAULT '',
    client         VARCHAR(300) NOT NULL DEFAULT '',
    category_label VARCHAR(200) NOT NULL DEFAULT '',
    date_text      VARCHAR(100) NOT NULL DEFAULT '',
    body_html      MEDIUMTEXT   NOT NULL,
    meta_title     VARCHAR(300) NOT NULL DEFAULT '',
    meta_desc      VARCHAR(500) NOT NULL DEFAULT '',
    is_published   TINYINT(1)   NOT NULL DEFAULT 1,
    sort           INT NOT NULL DEFAULT 0,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_categories (
    project_id  INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (project_id, category_id),
    CONSTRAINT fk_pc_project  FOREIGN KEY (project_id)  REFERENCES projects(id)   ON DELETE CASCADE,
    CONSTRAINT fk_pc_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_images (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    path       VARCHAR(500) NOT NULL,
    width      INT UNSIGNED NOT NULL DEFAULT 1920,
    height     INT UNSIGNED NOT NULL DEFAULT 1080,
    sort       INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_img_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS posts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(150) NOT NULL UNIQUE,
    title        VARCHAR(300) NOT NULL,
    category     VARCHAR(100) NOT NULL DEFAULT '',
    date_text    VARCHAR(100) NOT NULL DEFAULT '',
    cover        VARCHAR(500) NOT NULL DEFAULT '',
    excerpt      TEXT         NOT NULL,
    intro_html   MEDIUMTEXT   NOT NULL,
    header_image VARCHAR(500) NOT NULL DEFAULT '',
    body_html    MEDIUMTEXT   NOT NULL,
    meta_desc    VARCHAR(500) NOT NULL DEFAULT '',
    is_published TINYINT(1)   NOT NULL DEFAULT 1,
    sort         INT NOT NULL DEFAULT 0,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(200) NOT NULL,
    email      VARCHAR(200) NOT NULL,
    phone      VARCHAR(50)  NOT NULL DEFAULT '',
    subject    VARCHAR(300) NOT NULL DEFAULT '',
    message    TEXT         NOT NULL,
    ip         VARCHAR(45)  NOT NULL DEFAULT '',
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_messages_ip_time (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
