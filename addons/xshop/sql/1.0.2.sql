[openid]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='openid';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD openid varchar(45) default '' COMMENT '';"
[nickname]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='nickname';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD nickname varchar(255) default '' COMMENT '昵称';"
[sex]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='sex';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD sex tinyint(1) default '0' COMMENT '';"
[province]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='province';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD province varchar(45) default '' COMMENT '';"
[city]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='city';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD city varchar(45) default '' COMMENT '';"
[headimgurl]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='headimgurl';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD headimgurl varchar(500) default '' COMMENT '';"
[privilege]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='privilege';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD privilege varchar(500) default '' COMMENT '';"
[unionid]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='unionid';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD unionid varchar(45) default '' COMMENT '';"
[user_id]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='user_id';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD user_id int(10) default 0 COMMENT '';"
[access_token]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='access_token';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD access_token varchar(45) default '' COMMENT '';"
[fresh_token]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='fresh_token';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD fresh_token varchar(45) default '' COMMENT '';"
[session_key]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='session_key';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD session_key varchar(45) default '' COMMENT '';"
[expires_in]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='expires_in';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD expires_in int(10) default 0 COMMENT '';"
[create_time]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='create_time';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD create_time int(10) default 0 COMMENT '';"
[login_time]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='login_time';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD login_time int(10) default 0 COMMENT '';"
[expires_time]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='expires_time';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD expires_time int(10) default 0 COMMENT '';"
[platform]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='platform';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD platform varchar(45) default '' COMMENT '';"
[vendor]
IF = "SELECT 1 FROM information_schema.columns WHERE TABLE_SCHEMA = '__DATABASE__' AND table_name='__PREFIX__xshop_vendor' AND COLUMN_NAME='vendor';length:0"
THEN = "ALTER TABLE `__PREFIX__xshop_vendor` ADD vendor varchar(45) default '' COMMENT '';"