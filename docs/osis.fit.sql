
-- ------------------------------------------------------------------------------------
-- ACCOUNT

DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
    id                  VARCHAR(40) NOT NULL,
    mail                VARCHAR(89) NOT NULL,
    username            VARCHAR(255) NOT NULL,

    UNIQUE INDEX unique_mail (mail),
    UNIQUE INDEX unique_username (username),

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
    
    image_id            INT,
    firstname           VARCHAR(150) NOT NULL,
    lastname            VARCHAR(150) NOT NULL,

    PRIMARY KEY (account_id),
    
    FOREIGN KEY (image_id) REFERENCES image(id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

DROP TABLE IF EXISTS `user_detail`;
CREATE TABLE `user_detail` (
    account_id          VARCHAR(40) NOT NULL,

    gender              ENUM('male','female'),
    height              FLOAT,
    birthdate           DATE,
    pal                 FLOAT,

    aim_weight          FLOAT,
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
    amount              FLOAT,
    calories_per_100    FLOAT,

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

    amount              FLOAT,
    calories_per_100    FLOAT,
    total               FLOAT,

    PRIMARY KEY (id, account_id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

DROP TABLE IF EXISTS `ulog_weight`;
CREATE TABLE `ulog_weight` (
    account_id          VARCHAR(40) NOT NULL,

    id                  INT NOT NULL AUTO_INCREMENT,

    weight              FLOAT NOT NULL,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, account_id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

DROP TABLE IF EXISTS `ulog_calories`;
CREATE TABLE `ulog_calories` (
    account_id          VARCHAR(40) NOT NULL,

    id                  INT NOT NULL AUTO_INCREMENT,

    title               VARCHAR(150),
    calories            FLOAT NOT NULL,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, account_id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

DROP TABLE IF EXISTS `ulog_activity`;
CREATE TABLE `ulog_activity` (
    account_id          VARCHAR(40) NOT NULL,

    id                  INT NOT NULL AUTO_INCREMENT,

    title               VARCHAR(150),
    duration            TIME, -- TODO: REMOVE THIS ATTR.
    calories            FLOAT,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, account_id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

-- ------------------------------------------------------------------------------------
-- TRAININGS

DROP TABLE IF EXISTS `exercise`;
CREATE TABLE `exercise` (
    account_id          VARCHAR(40) NOT NULL,

    id                  INT NOT NULL AUTO_INCREMENT,
    public              BOOLEAN NOT NULL DEFAULT 0,

    title               VARCHAR(150) NOT NULL,
    description         TINYTEXT,
    content             LONGTEXT,
    type                ENUM('strength','stamina','fitness','flexibility','coordination','other') NOT NULL DEFAULT 'other',
    calories            FLOAT,
    repetitions         FLOAT,

    PRIMARY KEY (id, account_id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

DROP TABLE IF EXISTS `bodypart`;
CREATE TABLE `bodypart` (
    id                  VARCHAR(10) NOT NULL,

    title               VARCHAR(150) NOT NULL,
    type                ENUM('muscle','tissue','other') NOT NULL,

    PRIMARY KEY (id)
);

DROP TABLE IF EXISTS `exercise_uses_bodypart`;
CREATE TABLE `exercise_uses_bodypart` (

    exercise_id         INT NOT NULL,
    bodypart_id         VARCHAR(10) NOT NULL,

    PRIMARY KEY (exercise_id, bodypart_id),
    FOREIGN KEY (exercise_id) REFERENCES exercise(id),
    FOREIGN KEY (bodypart_id) REFERENCES bodypart(id)
);

DROP TABLE IF EXISTS `training`;
CREATE TABLE `training` (
    account_id          VARCHAR(40) NOT NULL,

    id                  INT NOT NULL AUTO_INCREMENT,
    public              BOOLEAN NOT NULL DEFAULT 0,

    title               VARCHAR(150) NOT NULL,
    description         TEXT,

    PRIMARY KEY (id, account_id),
    FOREIGN KEY (account_id) REFERENCES account(id)
);

DROP TABLE IF EXISTS `training_uses_exercise`;
CREATE TABLE `training_uses_exercise` (
    id                  INT NOT NULL AUTO_INCREMENT,
    training_id         INT NOT NULL,
    exercise_id         INT NOT NULL,

    repetitions         FLOAT,

    PRIMARY KEY (id, training_id, exercise_id),
    FOREIGN KEY (training_id) REFERENCES training(id),
    FOREIGN KEY (exercise_id) REFERENCES exercise(id)
);

DROP TABLE IF EXISTS `training_favorite`;
CREATE TABLE `training_favorite` (
    account_id          VARCHAR(40) NOT NULL,
    training_id         INT NOT NULL,

    PRIMARY KEY (account_id, training_id),
    FOREIGN KEY (account_id) REFERENCES account(id),
    FOREIGN KEY (training_id) REFERENCES training(id)
);

-- ------------------------------------------------------------------------------------
-- VIEWS

DROP VIEW IF EXISTS `v_log_detailed`;
CREATE VIEW `v_log_detailed` AS

    SELECT

        lo.id AS 'id',
        CONCAT(us.firstname, ' ', us.lastname) AS 'user',
        lo.level AS 'level',
        lo.process AS 'process',
        lo.information AS 'information',
        acc.mail AS 'mail',
        acc.username AS 'username',
        lo.stamp AS 'stamp',
        lo.identity AS 'identity',
        lo.trace AS 'trace'

    FROM log AS lo
    LEFT JOIN account AS acc ON lo.account_id = acc.id
    LEFT JOIN user AS us ON us.account_id = acc.id;


DROP VIEW IF EXISTS `v_auth_check`;
CREATE VIEW `v_auth_check` AS

    SELECT

        acc.mail AS 'account_mail',
        acc.username AS 'account_username',
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

        acc.id AS 'account_id',
        us.image_id AS 'image_id',
        us.firstname AS 'firstname',
        us.lastname AS 'lastname',
        de.birthdate AS 'birthdate',
        de.height AS 'height',
        de.gender AS 'gender',
        de.pal AS 'pal',
        de.aim_weight AS 'aim_weight',
        de.aim_date AS 'aim_date'

    FROM account AS acc
    LEFT JOIN user AS us ON us.account_id = acc.id
    LEFT JOIN user_detail AS de ON de.account_id = acc.id;


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


DROP VIEW IF EXISTS `v_exercise_bodypart`;
CREATE VIEW `v_exercise_bodypart` AS

    SELECT

        eub.exercise_id AS 'exercise_id',
        eub.bodypart_id AS 'bodypart_id',
        bo.type AS 'type'

    FROM exercise_uses_bodypart AS eub
    LEFT JOIN bodypart AS bo ON eub.bodypart_id = bo.id;


DROP VIEW IF EXISTS `v_exercise_search`;
CREATE VIEW `v_exercise_search` AS

    SELECT

        CONCAT(ex.title, '', us.firstname, '', us.lastname) AS 'query',
        (
            SELECT GROUP_CONCAT(bodypart_id) FROM `v_exercise_bodypart`
            WHERE exercise_id = ex.id
            GROUP BY exercise_id
        ) AS 'bodyparts',
        ex.id AS 'id',
        ex.public AS 'public',
        ex.title AS 'title',
        ex.description AS 'description',
        ex.repetitions AS 'repetitions',
        ex.calories AS 'calories',
        CONCAT(us.firstname, ' ', us.lastname) AS 'user',
        us.account_id AS 'account_id',
        img.id AS 'account_image_id',
        img.name AS 'account_image_name',
        img.mime AS 'account_image_mime',
        sz.full AS 'account_image_full',
        sz.small AS 'account_image_small',
        sz.lazy AS 'account_image_lazy'

    FROM exercise AS ex
    LEFT JOIN user AS us ON ex.account_id = us.account_id
    LEFT JOIN image AS img ON img.id = us.image_id
    LEFT JOIN image_sizes AS sz ON sz.image_id = img.id;
    

DROP VIEW IF EXISTS `v_training_search`;
CREATE VIEW `v_training_search` AS

    SELECT

        CONCAT(tr.title, '', us.firstname, '', us.lastname) AS 'query',
        tr.id AS 'id',
        tr.public AS 'public',
        tr.title AS 'title',
        tr.description AS 'description',
        CONCAT(us.firstname, ' ', us.lastname) AS 'user',
        us.account_id AS 'account_id',
        img.id AS 'account_image_id',
        img.name AS 'account_image_name',
        img.mime AS 'account_image_mime',
        sz.full AS 'account_image_full',
        sz.small AS 'account_image_small',
        sz.lazy AS 'account_image_lazy'

    FROM training AS tr
    LEFT JOIN user AS us ON tr.account_id = us.account_id
    LEFT JOIN image AS img ON img.id = us.image_id
    LEFT JOIN image_sizes AS sz ON sz.image_id = img.id;


DROP VIEW IF EXISTS `v_training_favorites`;
CREATE VIEW `v_training_favorites` AS

    SELECT

        trfa.training_id AS 'id',
        trfa.account_id AS 'account_id',
        tr.title AS 'title',
        tr.public AS 'public',
        tr.description AS 'description'

    FROM training_favorite AS trfa
    LEFT JOIN training AS tr ON tr.id = trfa.training_id;