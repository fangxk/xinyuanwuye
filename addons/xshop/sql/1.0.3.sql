ALTER TABLE `__PREFIX__xshop_product` 
ADD COLUMN `delivery_tpl_id` INT(5) DEFAULT 1 COMMENT '运费模板';

ALTER TABLE `__PREFIX__xshop_jshook`
ADD COLUMN `addon_name` VARCHAR(255) DEFAULT '' COMMENT '插件';

ALTER TABLE `__PREFIX__xshop_jshook`
ADD COLUMN `state` INT(1) DEFAULT 1 COMMENT '状态';

ALTER TABLE `__PREFIX__xshop_product_sku` 
ADD COLUMN `image` VARCHAR(255) NULL DEFAULT '' COMMENT '图片';

COMMIT;