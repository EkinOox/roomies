CREATE DATABASE IF NOT EXISTS roomies;
USE roomies;

-- Table: users
insert into users (id, username, email, password, created_at, avatar, roles) values
(1, 'admin', 'admin@admin.com','$2y$13$2/E8uChDMnWBd61Uuh3ooOJZv77XVVm/LvjNX9qquKIxOEPqP7jTy', '/img/avatar/6.png', '["ROLE_ADMIN"]'