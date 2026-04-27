-- できたすく データベース初期化
-- MySQLコンテナ初回起動時に自動実行される

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS dekitasuku
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;git add

USE dekitasuku;

CREATE TABLE IF NOT EXISTS children (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(50) NOT NULL,
    total_points INT NOT NULL DEFAULT 0,
    deleted_at   DATETIME NULL
);

CREATE TABLE IF NOT EXISTS tasks (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    child_id   INT NOT NULL,
    title      VARCHAR(100) NOT NULL,
    points     INT NOT NULL DEFAULT 10,
    deleted_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS task_logs (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    task_id        INT NOT NULL,
    completed_date DATE NOT NULL,
    deleted_at     DATETIME NULL
);

CREATE TABLE IF NOT EXISTS diaries (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    child_id   INT NOT NULL,
    content    TEXT NULL,
    body_score INT NOT NULL,
    mind_score INT NOT NULL,
    diary_date DATE NOT NULL,
    deleted_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS diary_replies (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    diary_id   INT NOT NULL,
    content    TEXT NOT NULL,
    deleted_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS rewards (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    child_id        INT NOT NULL,
    title           VARCHAR(100) NOT NULL,
    points_required INT NOT NULL,
    deleted_at      DATETIME NULL
);

CREATE TABLE IF NOT EXISTS reward_logs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    child_id   INT NOT NULL,
    reward_id  INT NOT NULL,
    used_date  DATE NOT NULL,
    deleted_at DATETIME NULL
);

ALTER TABLE tasks ADD CONSTRAINT fk_child FOREIGN KEY (child_id) REFERENCES children(id);
ALTER TABLE task_logs ADD CONSTRAINT fk_task FOREIGN KEY (task_id) REFERENCES tasks(id);
ALTER TABLE diaries ADD CONSTRAINT fk_diary_child FOREIGN KEY (child_id) REFERENCES children(id);
ALTER TABLE diary_replies ADD CONSTRAINT fk_diary FOREIGN KEY (diary_id) REFERENCES diaries(id);
ALTER TABLE rewards ADD CONSTRAINT fk_reward_child FOREIGN KEY (child_id) REFERENCES children(id);
ALTER TABLE reward_logs ADD CONSTRAINT fk_reward FOREIGN KEY (reward_id) REFERENCES rewards(id);
ALTER TABLE reward_logs ADD CONSTRAINT fk_reward_log_child FOREIGN KEY (child_id) REFERENCES children(id);