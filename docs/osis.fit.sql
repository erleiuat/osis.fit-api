
CREATE DATABASE IF NOT EXISTS `app.osis.fit` CHARACTER SET `utf8`;
USE `app.osis.fit`;


-- ------------------------------------------------------------------------------------

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
    id                  INT NOT NULL AUTO_INCREMENT,
    mail                VARCHAR(89) NOT NULL,
    level               ENUM('user', 'moderator', 'admin') NOT NULL DEFAULT 'user',
    premium             BOOLEAN NOT NULL DEFAULT 0,

    UNIQUE INDEX unique_mail (mail),
    PRIMARY KEY (id)
);

DROP TABLE IF EXISTS `auth`;
CREATE TABLE `auth` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    status              ENUM('unverified', 'verified', 'locked', 'deleted') NOT NULL DEFAULT 'unverified',
    password            VARCHAR(255) NOT NULL,
    auth_stamp          TIMESTAMP,
    auth_total          INT DEFAULT 0,

    password_stamp      TIMESTAMP,
    verify_stamp        TIMESTAMP,
    password_code_stamp VARCHAR(255),

    password_code       VARCHAR(255),
    verify_code         VARCHAR(255),

    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user(id)

);

DROP TABLE IF EXISTS `auth_refresh`;
CREATE TABLE `auth_refresh` (
    auth_id             INT NOT NULL,

    updated_total       INT DEFAULT 0,
    updated_stamp       TIMESTAMP,

    refresh_jti         VARCHAR(255) NOT NULL,
    created_stamp       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    refresh_phrase      VARCHAR(255) NOT NULL,

    PRIMARY KEY (auth_id, refresh_jti),
    FOREIGN KEY (auth_id) REFERENCES auth(id)
);

DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
    id                  INT NOT NULL AUTO_INCREMENT,
    level               ENUM('trace','debug','info', 'warn', 'error', 'fatal') NOT NULL DEFAULT 'trace',

    user_id             INT,
    identity            VARCHAR(255),
    process             VARCHAR(50) NOT NULL,
    information         TEXT,
    stamp               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trace               TEXT,

    PRIMARY KEY (id)
);

-- ------------------------------------------------------------------------------------

DROP TABLE IF EXISTS `user_detail`;
CREATE TABLE `user_detail` (
    user_id             INT NOT NULL,
    
    firstname           VARCHAR(150) NOT NULL,
    lastname            VARCHAR(150) NOT NULL,
    gender              ENUM('male','female'),
    height              DOUBLE,
    birthdate           DATE,

    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

DROP TABLE IF EXISTS `user_aim`;
CREATE TABLE `user_aim` (
    user_id             INT NOT NULL,

    weight              DOUBLE,
    bmi                 DOUBLE,
    date                DATE,

    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

DROP TABLE IF EXISTS `image`;
CREATE TABLE `image` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    name                VARCHAR(255),
    mime                VARCHAR(20),

    access_stamp        TIMESTAMP,
    upload_stamp        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

DROP TABLE IF EXISTS `image_sizes`;
CREATE TABLE `image_sizes` (
    image_id            INT NOT NULL,

    full                BOOLEAN NOT NULL DEFAULT 0,
    large               BOOLEAN NOT NULL DEFAULT 0,
    medium              BOOLEAN NOT NULL DEFAULT 0,
    small               BOOLEAN NOT NULL DEFAULT 0,
    lazy                BOOLEAN NOT NULL DEFAULT 0,
    update_stamp        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (image_id),
    FOREIGN KEY (image_id) REFERENCES image(id)
);

DROP TABLE IF EXISTS `user_food`;
CREATE TABLE `user_food` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,
    image_id            INT,

    title               VARCHAR(150) NOT NULL,
    amount              DOUBLE,
    calories_per_100    DOUBLE,

    PRIMARY KEY (id, user_id),
    FOREIGN KEY (image_id) REFERENCES image(id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

DROP TABLE IF EXISTS `user_food_favorite`;
CREATE TABLE `user_food_favorite` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    title               VARCHAR(60) NOT NULL,
    amount              DOUBLE,
    calories_per_100    DOUBLE,
    information         TEXT,
    source              VARCHAR(60),

    img_url             VARCHAR(255),
    img_lazy            VARCHAR(255),
    img_phrase          VARCHAR(255),

    PRIMARY KEY (id, user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

DROP TABLE IF EXISTS `user_weight`;
CREATE TABLE `user_weight` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    weight              DOUBLE NOT NULL,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

DROP TABLE IF EXISTS `user_calories`;
CREATE TABLE `user_calories` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    title               VARCHAR(150),
    calories            DOUBLE NOT NULL,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

DROP TABLE IF EXISTS `user_activity`;
CREATE TABLE `user_activity` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    title               VARCHAR(150),
    duration            TIME,
    calories            DOUBLE,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

-- ------------------------------------------------------------------------------------

DROP VIEW IF EXISTS `v_auth`;
CREATE VIEW `v_auth` AS

    SELECT

        au.id AS 'id',
        au.status AS 'status',
        au.password_stamp AS 'password_stamp',
        us.id AS 'user_id',
        us.mail AS 'user_mail',
        us.level AS 'user_level',
        us.premium AS 'user_premium'

    FROM user AS us
    LEFT JOIN auth AS au ON au.user_id = us.id;


DROP VIEW IF EXISTS `v_user_info`;
CREATE VIEW `v_user_info` AS

    SELECT

        us.id AS 'id',
        us.mail AS 'mail',
        us.level AS 'level',
        de.firstname AS 'firstname',
        de.lastname AS 'lastname',
        de.birthdate AS 'birthdate',
        de.height AS 'height',
        de.gender AS 'gender',
        ai.weight AS 'aim_weight',
        ai.bmi AS 'aim_bmi',
        ai.date AS 'aim_date'

    FROM user AS us
    LEFT JOIN user_detail AS de ON de.user_id = us.id
    LEFT JOIN user_aim AS ai ON ai.user_id = us.id;


DROP VIEW IF EXISTS `v_image_info`;
CREATE VIEW `v_image_info` AS

    SELECT

        img.id AS 'id',
        img.user_id AS 'user_id',
        img.name AS 'name',
        img.mime AS 'mime',
        sz.full AS 'full',
        sz.large AS 'large',
        sz.medium AS 'medium',
        sz.small AS 'small',
        sz.lazy AS 'lazy'

    FROM image AS img
    LEFT JOIN image_sizes AS sz ON sz.image_id = img.id;