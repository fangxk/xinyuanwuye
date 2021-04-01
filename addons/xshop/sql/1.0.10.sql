ALTER TABLE `__PREFIX__xshop_app_update` 
ADD COLUMN `version_type` INT(11) NULL DEFAULT '0' COMMENT '版本类型',
ADD COLUMN `silent` INT(11) NULL DEFAULT '0' COMMENT '静默更新';

COMMIT;