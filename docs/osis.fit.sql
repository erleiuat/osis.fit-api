
DROP DATABASE IF EXISTS `osis.fit`;
CREATE DATABASE `osis.fit` CHARACTER SET `utf8`;
USE `osis.fit`;

-- ------------------------------------------------------------------------------------

CREATE TABLE `user` (
    id                  INT NOT NULL AUTO_INCREMENT,
    mail                VARCHAR(89) NOT NULL,
    level               ENUM('user', 'moderator', 'admin') NOT NULL DEFAULT 'user',
    password            VARCHAR(255) NOT NULL,

    UNIQUE INDEX uniqueMail (mail),
    PRIMARY KEY (id)
);


CREATE TABLE `log` (
    id                  INT NOT NULL AUTO_INCREMENT,
    level               ENUM('trace','debug','info', 'warn', 'error', 'fatal') NOT NULL DEFAULT 'trace',

    user_id             INT,
    identity            VARCHAR(255),
    process             VARCHAR(50) NOT NULL,
    information         TEXT,
    stamp               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trace               TEXT,

    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);


-- ------------------------------------------------------------------------------------


CREATE TABLE `user_status` (
    user_id             INT NOT NULL,

    state               ENUM('unverified', 'verified', 'locked', 'deleted') NOT NULL DEFAULT 'unverified',
    deleted             ENUM('true', 'false') NOT NULL DEFAULT 'false',
    auth_total          INT DEFAULT 0,
    auth_stamp          TIMESTAMP,

    pw_stamp            TIMESTAMP,
    verify_stamp        TIMESTAMP,
    pw_code_stamp       VARCHAR(255),

    pw_code             VARCHAR(255),
    verify_code         VARCHAR(255),

    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);


CREATE TABLE `user_refresh_jti` (
    user_id             INT NOT NULL,

    updated_total       INT DEFAULT 0,
    updated_stamp       TIMESTAMP,

    refresh_jti         VARCHAR(255) NOT NULL,
    created_stamp       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    refresh_phrase      VARCHAR(255) NOT NULL,

    PRIMARY KEY (user_id, refresh_jti),
    FOREIGN KEY (user_id) REFERENCES user(id)
);


CREATE TABLE `user_detail` (
    user_id             INT NOT NULL,
    
    firstname           VARCHAR(150) NOT NULL,
    lastname            VARCHAR(150) NOT NULL,
    gender              ENUM('male','female'),
    height              DOUBLE,
    birth               DATE,

    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);


CREATE TABLE `user_aim` (
    user_id             INT NOT NULL,

    weight              DOUBLE,
    bmi                 DOUBLE,
    date                DATE,

    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);


CREATE TABLE `user_food` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    title               VARCHAR(60) NOT NULL,
    amount              DOUBLE,
    calories_per_100    DOUBLE,

    img_url             VARCHAR(255),
    img_lazy            VARCHAR(255),
    img_phrase          VARCHAR(255),

    PRIMARY KEY (id, user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);


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


CREATE TABLE `user_log_calorie` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    title               VARCHAR(60),
    calories            DOUBLE NOT NULL,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);


CREATE TABLE `user_log_weight` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    weight              DOUBLE NOT NULL,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);


CREATE TABLE `user_log_activity` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    title               VARCHAR(60),
    duration            TIME,
    calories            DOUBLE,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

-- ------------------------------------------------------------------------------------

CREATE VIEW `v_user_state` AS

    SELECT

        us.id AS 'user_id',
        us.mail AS 'mail',
        st.state AS 'state',
        de.firstname AS 'firstname',
        de.lastname AS 'lastname'

    FROM user AS us
    LEFT JOIN user_status AS st ON st.user_id = us.id 
    LEFT JOIN user_detail AS de ON de.user_id = us.id;

CREATE VIEW `v_user_token` AS

    SELECT

        us.id AS 'user_id',
        us.mail AS 'mail',
        us.level AS 'level',
        st.state AS 'state',
        st.pw_stamp AS 'pw_stamp',
        st.deleted AS 'deleted'

    FROM user AS us
    LEFT JOIN user_status AS st ON st.user_id = us.id;


CREATE VIEW `v_user_info` AS

    SELECT

        us.id AS 'id',
        de.firstname AS 'firstname',
        de.lastname AS 'lastname',
        de.birth AS 'birth',
        de.height AS 'height',
        de.gender AS 'gender',
        ai.weight AS 'aim_weight',
        ai.bmi AS 'aim_bmi',
        ai.date AS 'aim_date'

    FROM user AS us
    LEFT JOIN user_detail AS de ON de.user_id = us.id
    LEFT JOIN user_aim AS ai ON ai.user_id = us.id;