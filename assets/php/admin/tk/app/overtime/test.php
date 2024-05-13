DELIMITER $$

USE `kajima`$$

DROP VIEW IF EXISTS `vw_overtime_application`$$

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_overtime_application` AS (
SELECT
  `a`.`id`             AS `id`,
  `a`.`docnumber`      AS `docnumber`,
  `a`.`creator`        AS `creator`,
  `a`.`idacct`         AS `idacct`,
  `c`.`empid`          AS `empid`,
  `c`.`empname`        AS `empname`,
  `c`.`idsuperior`     AS `idsuperior`,
  `c`.`idunit`         AS `idunit`,
  `c`.`business_unit`  AS `business_unit`,
  `a`.`date`           AS `date`,
  `a`.`sdate`          AS `planned_date_start`,
  `a`.`fdate`          AS `planned_date_end`,
  `a`.`stime`          AS `planned_time_start`,
  `a`.`ftime`          AS `planned_time_end`,
  (CASE WHEN ((ISNULL(`b`.`in`) OR (`b`.`in` = '')) AND (ISNULL(`b`.`out`) OR (`b`.`out` = ''))) THEN CONCAT(CONVERT(TIME_FORMAT(`a`.`stime`,'%h:%i %p') USING utf8mb4),' to ',CONVERT(TIME_FORMAT(`a`.`ftime`,'%h:%i %p') USING utf8mb4)) WHEN (ISNULL(`b`.`in`) OR (`b`.`in` = '')) THEN CONVERT(TIME_FORMAT(`a`.`stime`,'%h:%i %p') USING utf8mb4) WHEN (ISNULL(`b`.`out`) OR (`b`.`out` = '')) THEN CONVERT(TIME_FORMAT(`a`.`ftime`,'%h:%i %p') USING utf8mb4) END) AS `app_time`,
  CONCAT(`a`.`sdate`,' ',CONCAT(`a`.`stime`,':00')) AS `planned_start`,
  CONCAT(`a`.`fdate`,' ',CONCAT(`a`.`ftime`,':00')) AS `planned_end`,
  `a`.`planhrs`        AS `planned_hrs`,
  `a`.`hrs`            AS `appr_actual_hrs`,
  CONCAT(`b`.`date`,' ',`b`.`shiftout`) AS `actual_start`,
  CONCAT(`b`.`date`,' ',`b`.`out`) AS `actual_end`,
  IFNULL(ROUND(ABS(((IF((`b`.`out` IS NOT NULL),UNIX_TIMESTAMP(CONCAT(`b`.`date_out`,' ',`b`.`out`)),IFNULL(IF((`g`.`stat` = '1'),UNIX_TIMESTAMP(CONCAT(`g`.`date`,' ',`g`.`ftime`)),NULL),UNIX_TIMESTAMP(CONCAT(`b`.`date_out`,' ',`b`.`out`)))) - UNIX_TIMESTAMP(CONCAT(`a`.`sdate`,' ',`a`.`stime`))) / 3600)),2),0) AS `actual_hrs`,
  IFNULL(CONCAT(`b`.`date`,' ',`b`.`shiftout`),CONCAT(`a`.`sdate`,' ',CONCAT(`a`.`stime`,':00'))) AS `start_time`,
  IFNULL(CONCAT(`b`.`date`,' ',`b`.`out`),CONCAT(`a`.`fdate`,' ',CONCAT(`a`.`ftime`,':00'))) AS `end_time`,
  `a`.`approve_hr`     AS `approve_hr`,
  `a`.`approver1_date` AS `date_approve`,
  `a`.`id_payperiod`   AS `id_payperiod`,
  `d`.`period_start`   AS `period_start`,
  `d`.`period_end`     AS `period_end`,
  `d`.`grace_hour`     AS `grace_hour`
FROM ((((((`kajima`.`tbltimeovertime` `a`
        JOIN `kajima`.`vw_dataemployees` `c`
          ON ((`a`.`idacct` = `c`.`id`)))
       LEFT JOIN `kajima`.`vw_data_timesheet` `b`
         ON (((`a`.`date` = `b`.`work_date`)
              AND (`b`.`empID` = `a`.`idacct`))))
      LEFT JOIN `kajima`.`tblpayperiod` `d`
        ON ((`d`.`id` = `a`.`id_payperiod`)))
     LEFT JOIN `kajima`.`vw_dataemployees` `e`
       ON ((`a`.`approver1` = `e`.`id`)))
    LEFT JOIN `kajima`.`vw_dataemployees` `f`
      ON ((`a`.`approver2` = `f`.`id`)))
   LEFT JOIN `kajima`.`tbltimeadjustment` `g`
     ON (((`a`.`date` = `g`.`date`)
          AND (`g`.`idacct` = `a`.`idacct`))))
WHERE ISNULL(`a`.`cancelby`))$$

DELIMITER ;