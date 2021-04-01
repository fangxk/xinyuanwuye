[order_type]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_order' AND COLUMN_NAME='order_type';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_order` ADD order_type tinyint(2) default '0' COMMENT '订单类型';"
[groupon_status]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_order' AND COLUMN_NAME='groupon_status';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_order` ADD groupon_status tinyint(2) default '1' COMMENT '团购状态';"
[order_sn_re]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_order' AND COLUMN_NAME='order_sn_re';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_order` ADD order_sn_re varchar(45) default '' COMMENT 'out_trade_no';"
[after_buyer_remark]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_order' AND COLUMN_NAME='after_buyer_remark';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_order` ADD after_buyer_remark varchar(400) default '' COMMENT '售后买家留言';"
[after_saler_remark]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_order' AND COLUMN_NAME='after_saler_remark';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_order` ADD after_saler_remark varchar(400) default '' COMMENT '售后卖家留言';"
[after_sale_status]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_order' AND COLUMN_NAME='after_sale_status';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_order` ADD after_sale_status tinyint(2) default '0' COMMENT '售后状态';"
[refund_fee]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_order' AND COLUMN_NAME='refund_fee';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_order` ADD refund_fee decimal(10,2) default '0' COMMENT '退款金额';"

