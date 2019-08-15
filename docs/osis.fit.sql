
-- ------------------------------------------------------------------------------------
-- ACCOUNT

DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
    id                  VARCHAR(40) NOT NULL,
    mail                VARCHAR(89) NOT NULL,

    UNIQUE INDEX unique_mail (mail),

    PRIMARY KEY (id)
);



-- ------------------------------------------------------------------------------------
-- AUTH

DROP TABLE IF EXISTS `auth`;
CREATE TABLE `auth` (
    account_id          VARCHAR(40) NOT NULL,

    id                  INT NOT NULL AUTO_INCREMENT,
    level               ENUM('user', 'moderator', 'admin') NOT NULL DEFAULT 'user',
    status              ENUM('unverified', 'verified', 'locked', 'deleted') NOT NULL DEFAULT 'unverified',
    subscription        VARCHAR(255),

    login_stamp         TIMESTAMP,
    login_total         INT DEFAULT 0,
    
    PRIMARY KEY (id),
    FOREIGN KEY (account_id) REFERENCES account(id)

);

DROP TABLE IF EXISTS `auth_pass`;
CREATE TABLE `auth_pass` (
    auth_id             INT NOT NULL,

    password            VARCHAR(255) NOT NULL,

    update_stamp        TIMESTAMP,
    reset_code          VARCHAR(255),
    reset_code_stamp    VARCHAR(255),
    
    FOREIGN KEY (auth_id) REFERENCES auth(id)
);

DROP TABLE IF EXISTS `auth_verify`;
CREATE TABLE `auth_verify` (
    auth_id             INT NOT NULL,

    stamp               TIMESTAMP,
    code                VARCHAR(255),
    
    FOREIGN KEY (auth_id) REFERENCES auth(id)
);

DROP TABLE IF EXISTS `auth_refresh`;
CREATE TABLE `auth_refresh` (
    auth_id             INT NOT NULL,

    jti                 VARCHAR(255) NOT NULL,
    phrase              VARCHAR(255) NOT NULL,

    update_stamp        TIMESTAMP,
    update_total        INT DEFAULT 0,

    PRIMARY KEY (auth_id, jti),
    FOREIGN KEY (auth_id) REFERENCES auth(id)
);



-- ------------------------------------------------------------------------------------
-- GENERAL

DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
    id                  INT NOT NULL AUTO_INCREMENT,
    level               ENUM('trace','debug','info', 'warn', 'error', 'fatal') NOT NULL DEFAULT 'trace',

    account_id          VARCHAR(255),
    identity            VARCHAR(255),
    process             VARCHAR(50) NOT NULL,
    information         TEXT,
    stamp               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trace               TEXT,

    PRIMARY KEY (id)
);

-- ------------------------------------------------------------------------------------
-- IMAGE

DROP TABLE IF EXISTS `image`;
CREATE TABLE `image` (
    id                  INT NOT NULL AUTO_INCREMENT,
    account_id          VARCHAR(40) NOT NULL,

    name                VARCHAR(255),
    mime                VARCHAR(20),

    access_stamp        TIMESTAMP,
    upload_stamp        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, account_id),
    FOREIGN KEY (account_id) REFERENCES account(id)
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

-- ------------------------------------------------------------------------------------
-- USER

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
    account_id          VARCHAR(40) NOT NULL,
    
    firstname           VARCHAR(150) NOT NULL,
    lastname            VARCHAR(150) NOT NULL,
    gender              ENUM('male','female'),
    height              DOUBLE,
    birthdate           DATE,

    aim_weight          DOUBLE,
    aim_date            DATE,

    PRIMARY KEY (account_id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

DROP TABLE IF EXISTS `user_food`;
CREATE TABLE `user_food` (
    account_id          VARCHAR(40) NOT NULL,
    image_id            INT,

    id                  INT NOT NULL AUTO_INCREMENT,

    title               VARCHAR(150) NOT NULL,
    amount              DOUBLE,
    calories_per_100    DOUBLE,

    PRIMARY KEY (id, account_id),
    FOREIGN KEY (image_id) REFERENCES image(id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

DROP TABLE IF EXISTS `user_food_favorite`;
CREATE TABLE `user_food_favorite` (
    account_id          VARCHAR(40) NOT NULL,

    id                  VARCHAR(255),
    image               VARCHAR(255),
    title               VARCHAR(255) NOT NULL,

    amount              DOUBLE,
    calories_per_100    DOUBLE,
    total               DOUBLE,

    PRIMARY KEY (id, account_id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

DROP TABLE IF EXISTS `ulog_weight`;
CREATE TABLE `ulog_weight` (
    account_id          VARCHAR(40) NOT NULL,

    id                  INT NOT NULL AUTO_INCREMENT,

    weight              DOUBLE NOT NULL,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, account_id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

DROP TABLE IF EXISTS `ulog_calories`;
CREATE TABLE `ulog_calories` (
    account_id          VARCHAR(40) NOT NULL,

    id                  INT NOT NULL AUTO_INCREMENT,

    title               VARCHAR(150),
    calories            DOUBLE NOT NULL,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, account_id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

DROP TABLE IF EXISTS `ulog_activity`;
CREATE TABLE `ulog_activity` (
    account_id          VARCHAR(40) NOT NULL,

    id                  INT NOT NULL AUTO_INCREMENT,

    title               VARCHAR(150),
    duration            TIME,
    calories            DOUBLE,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, account_id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

-- ------------------------------------------------------------------------------------
-- VIEWS

DROP VIEW IF EXISTS `v_auth_check`;
CREATE VIEW `v_auth_check` AS

    SELECT

        acc.mail AS 'account_mail',
        acc.id AS 'account_id',

        auth.id AS 'auth_id',
        auth.status AS 'auth_status',
        auth.subscription AS 'auth_subscription',
        auth.level AS 'auth_level',

        pass.update_stamp AS 'pass_update_stamp'

    FROM account AS acc
    LEFT JOIN auth AS auth ON auth.account_id = acc.id
    LEFT JOIN auth_pass AS pass ON pass.auth_id = auth.id;


DROP VIEW IF EXISTS `v_user`;
CREATE VIEW `v_user` AS

    SELECT

        us.firstname AS 'firstname',
        us.lastname AS 'lastname',
        us.birthdate AS 'birthdate',
        us.height AS 'height',
        us.gender AS 'gender',
        us.aim_weight AS 'aim_weight',
        us.aim_date AS 'aim_date'

    FROM account AS acc
    LEFT JOIN user AS us ON us.account_id = acc.id;


DROP VIEW IF EXISTS `v_image`;
CREATE VIEW `v_image` AS

    SELECT

        img.id AS 'id',
        img.account_id AS 'account_id',
        img.name AS 'name',
        img.mime AS 'mime',
        sz.full AS 'full',
        sz.large AS 'large',
        sz.medium AS 'medium',
        sz.small AS 'small',
        sz.lazy AS 'lazy'

    FROM image AS img
    LEFT JOIN image_sizes AS sz ON sz.image_id = img.id;