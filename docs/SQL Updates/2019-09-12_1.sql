ALTER TABLE `exercise` ADD `image_id` INT NULL DEFAULT NULL AFTER `account_id`;
ALTER TABLE `exercise` ADD CONSTRAINT image_id FOREIGN KEY (image_id) REFERENCES image(id);
ALTER TABLE `exercise` DROP `repetitions`;

ALTER TABLE `training_uses_exercise` CHANGE `repetitions` `duration` TIME NULL DEFAULT NULL;

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