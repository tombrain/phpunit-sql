SELECT *
FROM `t1`
JOIN `t2` ON `t1`.`a` = `t2`.`b`
WHERE `t1`.`b` IS NULL
    AND `t2`.`b` IS NOT NULL;
