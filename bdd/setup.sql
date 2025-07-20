CREATE DATABASE IF NOT EXISTS roomies;
USE roomies;

-- Table: users
INSERT INTO users (id, username, email, password, created_at, avatar, roles) VALUES
(1, 'admin', 'admin@admin.com','$2y$13$2/E8uChDMnWBd61Uuh3ooOJZv77XVVm/LvjNX9qquKIxOEPqP7jTy', NOW(), '/img/avatar/6.png', '["ROLE_ADMIN"]');