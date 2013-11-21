ALTER TABLE `questionnaire`
ADD `environment` longtext NULL,
COMMENT=''; -- 0.016 s

ALTER TABLE `questionnaire_log`
ADD `environment` longtext NULL,
COMMENT=''; -- 0.016 s