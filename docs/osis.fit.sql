
CREATE DATABASE IF NOT EXISTS `osis.fit` CHARACTER SET `utf8`;
USE `osis.fit`;

-- ------------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `user` (
    id                  INT NOT NULL AUTO_INCREMENT,
    mail                VARCHAR(89) NOT NULL,
    password            VARCHAR(255) NOT NULL,
    level               ENUM('user', 'moderator', 'admin') NOT NULL DEFAULT 'user',

    UNIQUE INDEX uniqueMail (mail),
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS `log` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT,

    level               ENUM('trace','debug','info', 'warn', 'error', 'fatal') NOT NULL DEFAULT 'trace',
    process             VARCHAR(50) NOT NULL,
    information         TEXT,
    identity            VARCHAR(255),
    trace               TEXT,
    stamp               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);


CREATE TABLE IF NOT EXISTS `article` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    url                 VARCHAR(60) NOT NULL,
    title               VARCHAR(60) NOT NULL,
    keywords            VARCHAR(255),
    content             MEDIUMTEXT,
    language            ENUM('de', 'en') NOT NULL DEFAULT 'en',

    creation_stamp      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_stamp        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    publication_date    DATE,

    UNIQUE INDEX uniqueUrl (url),
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

CREATE TABLE IF NOT EXISTS `article_preview` (
    article_id          INT NOT NULL,

    color               VARCHAR(60),
    dark                ENUM('true', 'false') DEFAULT 'false',
    description         VARCHAR(280),

    img_url             VARCHAR(255),
    img_lazy            VARCHAR(255),
    img_phrase          VARCHAR(255),

    PRIMARY KEY (article_id),
    FOREIGN KEY (article_id) REFERENCES article(id)
);

-- ------------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `user_verification` (
    user_id             INT NOT NULL,

    state               ENUM('unverified', 'verified', 'locked') NOT NULL DEFAULT 'unverified',
    code                VARCHAR(255),
    stamp               TIMESTAMP,

    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

CREATE TABLE IF NOT EXISTS `user_detail` (
    user_id             INT NOT NULL,
    
    firstname           VARCHAR(150) NOT NULL,
    lastname            VARCHAR(150) NOT NULL,
    gender              ENUM('male','female'),
    height              DOUBLE,
    birth               DATE,

    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

CREATE TABLE IF NOT EXISTS `user_aim` (
    user_id             INT NOT NULL,

    weight              DOUBLE,
    bmi                 DOUBLE,
    date                DATE,

    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

CREATE TABLE IF NOT EXISTS `user_food` (
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

CREATE TABLE IF NOT EXISTS `user_food_favorite` (
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

CREATE TABLE IF NOT EXISTS `user_log_calorie` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    title               VARCHAR(60),
    calories            DOUBLE NOT NULL,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

CREATE TABLE IF NOT EXISTS `user_log_weight` (
    id                  INT NOT NULL AUTO_INCREMENT,
    user_id             INT NOT NULL,

    weight              DOUBLE NOT NULL,
    stamp               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id, user_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

CREATE TABLE IF NOT EXISTS `user_log_activity` (
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

        us.id AS 'id',
        us.mail AS 'mail',
        ve.state AS 'state',
        ve.stamp AS 'stamp'

    FROM user AS us
    LEFT JOIN user_verification AS ve ON ve.user_id = us.id;


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


CREATE VIEW `v_article_edit` AS

    SELECT

        ar.url AS 'url',
        ar.title AS 'title',
        ar.keywords AS 'keywords',
        ar.language AS 'language',
        ar.content AS 'content',
        ar.publication_date AS 'publication_date',

        pr.color AS 'color',
        pr.dark AS 'dark',
        pr.description AS 'description',
        pr.img_url AS 'img_url',
        pr.img_lazy AS 'img_lazy',
        pr.img_phrase AS 'img_phrase'

    FROM article AS ar
    LEFT JOIN article_preview AS pr ON pr.article_id = ar.id;
    

CREATE VIEW `v_article_preview` AS

    SELECT

        ar.id AS 'id',
        ar.url AS 'url',
        ar.title AS 'title',
        ar.keywords AS 'keywords',
        ar.language AS 'language',
        ar.publication_date AS 'publication_date',

        pr.color AS 'color',
        pr.dark AS 'dark',
        pr.description AS 'description',
        pr.img_url AS 'img_url',
        pr.img_lazy AS 'img_lazy',
        pr.img_phrase AS 'img_phrase'

    FROM article AS ar
    LEFT JOIN article_preview AS pr ON pr.article_id = ar.id;
