-- fanwe SQL Dump Program
-- nginx/1.4.4
-- 
-- DATE : 2016-08-01 09:23:38
-- MYSQL SERVER VERSION : 5.6.29
-- PHP VERSION : fpm-fcgi
-- Vol : 5


DROP TABLE IF EXISTS `%DB_PREFIX%score_exchange_record`;
CREATE TABLE `%DB_PREFIX%score_exchange_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '会员ID',
  `user_id` int(11) NOT NULL,
  `integral` int(11) NOT NULL COMMENT '兑换积分',
  `cash` decimal(20,2) NOT NULL COMMENT '兑现金额',
  `vip_id` int(11) NOT NULL COMMENT 'VIP等级ID',
  `exchange_date` date NOT NULL COMMENT '兑换日期',
  `number` int(11) DEFAULT NULL COMMENT '笔数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='积分兑换记录表';
INSERT INTO `%DB_PREFIX%score_exchange_record` VALUES ('2','440090','1000','7.00','5','2015-07-10','0');
INSERT INTO `%DB_PREFIX%score_exchange_record` VALUES ('3','440094','1000','6.50','4','2015-07-10','0');
INSERT INTO `%DB_PREFIX%score_exchange_record` VALUES ('4','440093','1000','6.00','3','2015-07-10','0');
INSERT INTO `%DB_PREFIX%score_exchange_record` VALUES ('5','440121','1000','5.50','2','2015-07-13','0');
INSERT INTO `%DB_PREFIX%score_exchange_record` VALUES ('6','440090','1000','7.00','5','2015-07-28','0');
INSERT INTO `%DB_PREFIX%score_exchange_record` VALUES ('7','440093','1000','6.50','4','2015-07-28','0');
INSERT INTO `%DB_PREFIX%score_exchange_record` VALUES ('8','440098','1000','6.00','3','2015-07-28','0');
INSERT INTO `%DB_PREFIX%score_exchange_record` VALUES ('9','440121','1000','5.50','2','2015-07-28','0');
INSERT INTO `%DB_PREFIX%score_exchange_record` VALUES ('10','440020','10000','55.00','2','2015-09-22','0');
INSERT INTO `%DB_PREFIX%score_exchange_record` VALUES ('11','440035','5000','27.50','2','2015-10-20','0');
INSERT INTO `%DB_PREFIX%score_exchange_record` VALUES ('12','440035','50000','275.00','2','2015-10-20','0');
INSERT INTO `%DB_PREFIX%score_exchange_record` VALUES ('13','440035','450000','2475.00','2','2015-10-20','0');
DROP TABLE IF EXISTS `%DB_PREFIX%security`;
CREATE TABLE `%DB_PREFIX%security` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL COMMENT '操作员',
  `amount` decimal(20,4) NOT NULL COMMENT '金额',
  `money` decimal(20,4) NOT NULL DEFAULT '0.0000' COMMENT '余额',
  `optime` int(11) NOT NULL COMMENT '操作时间',
  `memo` text NOT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%security` VALUES ('1','3','4354545.0000','4354545.0000','1430962400','');
DROP TABLE IF EXISTS `%DB_PREFIX%session`;
CREATE TABLE `%DB_PREFIX%session` (
  `session_id` varchar(255) NOT NULL,
  `session_data` text NOT NULL,
  `session_time` int(11) NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%site_money_log`;
CREATE TABLE `%DB_PREFIX%site_money_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '关联用户',
  `money` decimal(20,2) NOT NULL COMMENT '操作金额',
  `memo` text NOT NULL COMMENT '操作备注',
  `type` tinyint(2) NOT NULL COMMENT '7提前回收，9提现手续费，10借款管理费，12逾期管理费，13人工充值，14借款服务费，17债权转让管理费，18开户奖励，20投标管理费，22兑换，23邀请返利，24投标返利，25签到成功',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
  `create_time_ymd` date NOT NULL COMMENT '操作时间 ymd',
  `create_time_ym` int(6) NOT NULL COMMENT '操作时间 ym',
  `create_time_y` int(4) NOT NULL COMMENT '操作时间 y',
  PRIMARY KEY (`id`),
  KEY `idx0` (`user_id`,`type`,`create_time`),
  KEY `idx1` (`user_id`,`type`,`create_time_ymd`),
  KEY `idx2` (`user_id`,`type`),
  KEY `create_time_ymd` (`create_time_ymd`),
  KEY `idx3` (`type`,`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2205 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='网站收益日志表';
INSERT INTO `%DB_PREFIX%site_money_log` VALUES ('2203','1','-1000.00','在2016-02-14 23:22:29注册成功','18','1455434549','2016-02-14','201602','2016');
INSERT INTO `%DB_PREFIX%site_money_log` VALUES ('2204','1','-20000.00','输入正数金额','13','1469471087','2016-07-26','201607','2016');
DROP TABLE IF EXISTS `%DB_PREFIX%sms`;
CREATE TABLE `%DB_PREFIX%sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `server_url` text NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `config` text NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%sms` VALUES ('8','企信通短信平台','','QXT','http://221.179.180.158:9000/QxtSms/QxtFirewall','','','a:1:{s:11:\"ContentType\";s:1:\"8\";}','0');
INSERT INTO `%DB_PREFIX%sms` VALUES ('15','短信平台','','FW','/','root','ababd88c8e','N;','1');
DROP TABLE IF EXISTS `%DB_PREFIX%topic`;
CREATE TABLE `%DB_PREFIX%topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fav_id` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'focus关注，1',
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `l_user_id` int(11) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `is_effect` (`is_effect`) USING BTREE,
  KEY `ordery_sort` (`create_time`) USING BTREE,
  KEY `multi_key` (`is_effect`,`create_time`) USING BTREE,
  KEY `index_01` (`fav_id`,`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=401 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%topic` VALUES ('400','2','deal_collect','1','test','0','1','1469068211');
DROP TABLE IF EXISTS `%DB_PREFIX%urls`;
CREATE TABLE `%DB_PREFIX%urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user`;
CREATE TABLE `%DB_PREFIX%user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL,
  `short_name` varchar(255) DEFAULT NULL COMMENT '缩略名',
  `user_pwd` varchar(255) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `login_ip` varchar(255) NOT NULL,
  `group_id` int(11) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  `is_delete` tinyint(1) NOT NULL,
  `email` varchar(255) NOT NULL,
  `idno` varchar(20) NOT NULL,
  `idcardpassed` tinyint(1) NOT NULL,
  `idcardpassed_time` int(11) NOT NULL,
  `real_name` varchar(50) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `mobilepassed` tinyint(1) NOT NULL,
  `score` int(11) NOT NULL,
  `money` decimal(20,2) NOT NULL DEFAULT '0.00',
  `quota` decimal(20,0) NOT NULL DEFAULT '0',
  `lock_money` decimal(20,2) NOT NULL COMMENT '冻结资金',
  `verify` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL COMMENT '登录用的标识码',
  `pid` int(11) NOT NULL,
  `referer_memo` varchar(255) NOT NULL COMMENT '邀请备注',
  `login_time` int(11) NOT NULL,
  `referral_count` int(11) NOT NULL,
  `password_verify` varchar(255) NOT NULL,
  `integrate_id` int(11) NOT NULL,
  `sina_id` varchar(255) NOT NULL,
  `renren_id` int(11) NOT NULL,
  `kaixin_id` int(11) NOT NULL,
  `sohu_id` int(11) NOT NULL,
  `bind_verify` varchar(255) NOT NULL,
  `verify_create_time` int(11) NOT NULL,
  `tencent_id` varchar(255) NOT NULL,
  `referer` varchar(255) NOT NULL,
  `login_pay_time` int(11) NOT NULL,
  `focus_count` int(11) NOT NULL COMMENT '关注别人的数量',
  `focused_count` int(11) NOT NULL COMMENT '粉丝数',
  `n_province_id` int(11) NOT NULL,
  `n_city_id` int(11) NOT NULL,
  `province_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `sex` tinyint(1) NOT NULL DEFAULT '-1',
  `step` tinyint(1) NOT NULL,
  `byear` int(4) NOT NULL,
  `bmonth` int(2) NOT NULL,
  `bday` int(2) NOT NULL,
  `graduation` varchar(15) NOT NULL,
  `graduatedyear` int(5) NOT NULL,
  `university` varchar(100) NOT NULL,
  `edu_validcode` varchar(20) NOT NULL,
  `has_send_video` tinyint(1) NOT NULL,
  `marriage` varchar(15) NOT NULL,
  `haschild` tinyint(1) NOT NULL,
  `hashouse` tinyint(1) NOT NULL,
  `houseloan` tinyint(1) NOT NULL,
  `hascar` tinyint(1) NOT NULL,
  `carloan` tinyint(4) NOT NULL,
  `car_brand` varchar(50) NOT NULL,
  `car_year` int(4) NOT NULL,
  `car_number` varchar(50) NOT NULL,
  `address` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `postcode` varchar(20) NOT NULL,
  `locate_time` int(11) NOT NULL DEFAULT '0' COMMENT '用户最后登陆时间',
  `xpoint` float(10,6) NOT NULL DEFAULT '0.000000' COMMENT '用户最后登陆x座标',
  `ypoint` float(10,6) NOT NULL DEFAULT '0.000000' COMMENT '用户最后登陆y座标',
  `topic_count` int(11) NOT NULL,
  `fav_count` int(11) NOT NULL,
  `faved_count` int(11) NOT NULL,
  `insite_count` int(11) NOT NULL,
  `outsite_count` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `point` int(11) NOT NULL,
  `sina_app_key` varchar(255) NOT NULL,
  `sina_app_secret` varchar(255) NOT NULL,
  `is_syn_sina` varchar(255) NOT NULL,
  `tencent_app_key` varchar(255) NOT NULL,
  `tencent_app_secret` varchar(255) NOT NULL,
  `is_syn_tencent` tinyint(1) NOT NULL,
  `t_access_token` varchar(250) NOT NULL,
  `t_openkey` varchar(250) NOT NULL,
  `t_openid` varchar(250) NOT NULL,
  `sina_token` varchar(255) NOT NULL,
  `is_borrow_out` tinyint(1) NOT NULL,
  `is_borrow_int` tinyint(1) NOT NULL,
  `creditpassed` tinyint(1) NOT NULL,
  `creditpassed_time` int(11) NOT NULL,
  `workpassed` tinyint(1) NOT NULL,
  `workpassed_time` int(11) NOT NULL,
  `incomepassed` tinyint(1) NOT NULL,
  `incomepassed_time` int(11) NOT NULL,
  `housepassed` tinyint(1) NOT NULL,
  `housepassed_time` int(11) NOT NULL,
  `carpassed` tinyint(1) NOT NULL,
  `carpassed_time` int(11) NOT NULL,
  `marrypassed` tinyint(1) NOT NULL,
  `marrypassed_time` int(11) NOT NULL,
  `edupassed` tinyint(1) NOT NULL,
  `edupassed_time` int(11) NOT NULL,
  `skillpassed` tinyint(1) NOT NULL,
  `skillpassed_time` int(11) NOT NULL,
  `videopassed` tinyint(1) NOT NULL,
  `videopassed_time` int(11) NOT NULL,
  `mobiletruepassed` tinyint(1) NOT NULL,
  `mobiletruepassed_time` int(11) NOT NULL,
  `residencepassed` tinyint(1) NOT NULL,
  `residencepassed_time` int(11) NOT NULL,
  `alipay_id` varchar(255) NOT NULL,
  `qq_id` varchar(255) NOT NULL,
  `taobao_id` varchar(255) NOT NULL,
  `info_down` varchar(255) NOT NULL,
  `sealpassed` tinyint(1) NOT NULL,
  `paypassword` varchar(50) NOT NULL DEFAULT '' COMMENT '支付密码',
  `apns_code` varchar(255) DEFAULT NULL COMMENT '推送设备号',
  `ips_acct_no` varchar(30) DEFAULT NULL COMMENT 'pIpsAcctNo 30 IPS托管平台账 户号',
  `emailpassed` tinyint(1) NOT NULL,
  `tmp_email` varchar(255) NOT NULL,
  `view_info` text NOT NULL,
  `referral_rate` decimal(10,4) NOT NULL COMMENT '返利抽成比',
  `user_type` tinyint(4) NOT NULL COMMENT '用户类型 0普通用户 1 企业用户',
  `create_date` date NOT NULL COMMENT '记录注册日期，方便统计使用',
  `vip_id` int(11) NOT NULL COMMENT 'VIP等级id',
  `vip_state` tinyint(1) NOT NULL COMMENT 'VIP状态 0关闭 1开启',
  `nmc_amount` decimal(20,2) NOT NULL COMMENT '不可提现金额',
  `register_ip` varchar(50) NOT NULL COMMENT '注册IP',
  `admin_id` int(11) NOT NULL COMMENT '所属管理员',
  `customer_id` int(11) NOT NULL COMMENT '所属客服',
  `is_black` tinyint(1) NOT NULL COMMENT '是否黑名单',
  `brief` text COMMENT '担保方介绍',
  `header` text COMMENT '头部',
  `company_brief` text,
  `history` text COMMENT '发展史',
  `content` text COMMENT '内容',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `acct_type` int(11) DEFAULT NULL COMMENT '担保账户类型(0:机构，1:个人)',
  `ips_mer_code` varchar(10) DEFAULT NULL COMMENT '由IPS颁发的商户号 acct_type = 0',
  `u_year` varchar(255) DEFAULT NULL COMMENT '入学年份',
  `u_special` varchar(255) DEFAULT NULL COMMENT '专业',
  `u_school` varchar(255) DEFAULT NULL COMMENT '学校',
  `u_alipay` varchar(255) DEFAULT NULL COMMENT '支付宝账号',
  `enterpriseName` varchar(50) NOT NULL COMMENT '企业名称',
  `bankLicense` varchar(50) NOT NULL COMMENT '开户银行许可证',
  `orgNo` varchar(50) NOT NULL COMMENT '组织机构代码',
  `businessLicense` varchar(50) NOT NULL COMMENT '营业执照编号',
  `taxNo` varchar(20) NOT NULL COMMENT '税务登记号',
  `email_encrypt` varbinary(255) NOT NULL COMMENT '邮箱',
  `real_name_encrypt` varbinary(255) NOT NULL COMMENT '真实姓名',
  `idno_encrypt` varbinary(255) NOT NULL COMMENT '身份证号',
  `mobile_encrypt` varbinary(255) NOT NULL COMMENT '手机号',
  `money_encrypt` varbinary(255) NOT NULL COMMENT '账户余额',
  `wx_openid` varchar(255) NOT NULL COMMENT '微信openid',
  `total_invite_borrow_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计被邀请人员的借款金额',
  `total_invite_invest_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计被邀请人员的投资金额',
  `vip_end_time` int(11) NOT NULL COMMENT 'VIP结束时间',
  `build_count` int(11) NOT NULL COMMENT '发起的项目数',
  `support_count` int(11) NOT NULL COMMENT '支持的项目数',
  `mortgage_money` decimal(20,2) NOT NULL COMMENT '理财冻结资金',
  `cust_key` varchar(255) NOT NULL,
  `cust_id` varchar(255) NOT NULL,
  `access_tokens` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unk_user_name` (`user_name`) USING BTREE,
  KEY `idx_u_001` (`create_date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%user` VALUES ('1','test','','e10adc3949ba59abbe56e057f20f883e','1455434549','1455434563','111.198.16.9','0','1','0','','441702198710250000','0','0','测试','13788888888','1','500','0.00','0','0.00','','','0','','1469578653','0','','0','','0','0','0','','0','','','0','0','0','0','0','0','0','0','0','1987','10','25','','0','','','0','','0','0','0','0','0','','0','','','','','1469576785','0.000000','0.000000','0','0','0','0','0','1','20','','','','','','0','','','','','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','','','','','0','164b0f16bbf71c80aa6f18df75975b5e','','','0','','','0.0000','0','2016-02-14','0','0','0.00','127.0.0.1','0','0','0','','','','','','0','0','','','','','','','','','','','','�K�3M[���|%ɢ��','�^\"G�х]�3�Q�6���ֱ��½��_sD','��� 9x���r��\'��','���MiqC�m)Q�l��','','0.00','0.00','0','0','0','0.00','','','');
DROP TABLE IF EXISTS `%DB_PREFIX%user_active_log`;
CREATE TABLE `%DB_PREFIX%user_active_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `point` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `money` decimal(11,4) NOT NULL COMMENT '钱',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_address`;
CREATE TABLE `%DB_PREFIX%user_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `name` varchar(50) NOT NULL COMMENT '收货人姓名',
  `address` varchar(255) NOT NULL COMMENT '用户地址',
  `phone` varchar(20) NOT NULL COMMENT '用户电话',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认地址',
  `provinces_cities` varchar(100) NOT NULL COMMENT '省市',
  `zip_code` varchar(20) NOT NULL COMMENT '邮编',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_auth`;
CREATE TABLE `%DB_PREFIX%user_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `m_name` varchar(255) NOT NULL,
  `a_name` varchar(255) NOT NULL,
  `rel_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_autobid`;
CREATE TABLE `%DB_PREFIX%user_autobid` (
  `user_id` int(11) NOT NULL,
  `fixed_amount` decimal(20,0) NOT NULL,
  `min_rate` decimal(20,0) NOT NULL,
  `max_rate` decimal(20,0) NOT NULL,
  `min_period` int(11) NOT NULL,
  `max_period` int(11) NOT NULL,
  `min_level` int(11) NOT NULL,
  `max_level` int(11) NOT NULL,
  `retain_amount` decimal(20,0) NOT NULL,
  `is_effect` tinyint(4) NOT NULL,
  `last_bid_time` int(11) NOT NULL COMMENT '最后一次投标时间',
  `deal_cates` text NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_bank`;
CREATE TABLE `%DB_PREFIX%user_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `bankcard` varchar(30) NOT NULL,
  `real_name` varchar(20) NOT NULL,
  `region_lv1` int(11) NOT NULL,
  `region_lv2` int(11) NOT NULL,
  `region_lv3` int(11) NOT NULL,
  `region_lv4` int(11) NOT NULL,
  `bankzone` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `bank_id` (`bank_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_carry`;
CREATE TABLE `%DB_PREFIX%user_carry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `money` decimal(20,2) NOT NULL COMMENT '提现金额',
  `fee` decimal(20,2) NOT NULL COMMENT '手续费',
  `bank_id` int(11) NOT NULL,
  `bankcard` varchar(30) NOT NULL,
  `create_time` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0待审核，1已付款，2未通过，3待付款',
  `update_time` int(11) NOT NULL,
  `msg` text NOT NULL,
  `desc` text NOT NULL,
  `real_name` varchar(30) NOT NULL,
  `bankzone` varchar(120) NOT NULL,
  `region_lv1` int(11) NOT NULL,
  `region_lv2` int(11) NOT NULL,
  `region_lv3` int(11) NOT NULL,
  `region_lv4` int(11) NOT NULL,
  `create_date` date NOT NULL COMMENT '记录提现提交日期，方便统计使用',
  `pingzheng` varchar(255) NOT NULL COMMENT '打款凭证',
  PRIMARY KEY (`id`),
  KEY `idx_uc_001` (`create_date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_carry_config`;
CREATE TABLE `%DB_PREFIX%user_carry_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '简称',
  `min_price` decimal(20,0) NOT NULL COMMENT '最低额度',
  `max_price` decimal(20,0) NOT NULL COMMENT '最高额度',
  `fee` decimal(20,2) NOT NULL COMMENT '费率',
  `fee_type` tinyint(1) NOT NULL COMMENT '费率类型 0 是固定值 1是百分比',
  `vip_id` int(11) NOT NULL COMMENT 'VIP等级     0默认配置  否则就是对应VIP等级设置',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%user_carry_config` VALUES ('31','1万以内','0','10000','10.00','0','0');
INSERT INTO `%DB_PREFIX%user_carry_config` VALUES ('32','5万以内','10001','50000','20.00','0','0');
DROP TABLE IF EXISTS `%DB_PREFIX%user_company`;
CREATE TABLE `%DB_PREFIX%user_company` (
  `user_id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL COMMENT '公司名称',
  `contact` varchar(50) NOT NULL DEFAULT '' COMMENT '法人代表',
  `officetype` varchar(50) NOT NULL COMMENT '公司类别',
  `officedomain` varchar(50) NOT NULL COMMENT '公司行业',
  `officecale` varchar(50) NOT NULL COMMENT '公司规模',
  `register_capital` varchar(50) NOT NULL COMMENT '注册资金',
  `asset_value` varchar(100) NOT NULL COMMENT '资产净值',
  `officeaddress` varchar(255) NOT NULL COMMENT '公司地址',
  `description` text NOT NULL COMMENT '公司简介',
  `bankLicense` varchar(50) NOT NULL COMMENT '开户银行许可证',
  `orgNo` varchar(50) NOT NULL COMMENT '组织机构代码',
  `businessLicense` varchar(50) NOT NULL COMMENT '营业执照编号',
  `taxNo` varchar(20) NOT NULL COMMENT '税务登记号'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='公司信息标';
DROP TABLE IF EXISTS `%DB_PREFIX%user_consignee`;
CREATE TABLE `%DB_PREFIX%user_consignee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `province` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `consignee` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为默认地址',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='//收件人';
INSERT INTO `%DB_PREFIX%user_consignee` VALUES ('22','440035','福建','福州','台江区八一七中路','13524161613','350600','小小','0');
INSERT INTO `%DB_PREFIX%user_consignee` VALUES ('23','440090','广西','崇左','海景区','15026164622','256120','教教','0');
INSERT INTO `%DB_PREFIX%user_consignee` VALUES ('24','440093','福建','宁德','焦阳区','13562561620','350000','嗨嗨','0');
INSERT INTO `%DB_PREFIX%user_consignee` VALUES ('25','440115','安徽','安庆','换新','13565420120','250001','哈哈','0');
INSERT INTO `%DB_PREFIX%user_consignee` VALUES ('26','440119','广东','佛山','接到','15016125113','251010','嘻嘻','0');
INSERT INTO `%DB_PREFIX%user_consignee` VALUES ('27','440121','福建','南平','建瓯','1352156613','350000','下线','0');
INSERT INTO `%DB_PREFIX%user_consignee` VALUES ('28','440123','福建','南平','建瓯','1502315','350000','多多','0');
INSERT INTO `%DB_PREFIX%user_consignee` VALUES ('29','440119','福建','宁德','郊区','135000','350000','换换','0');
DROP TABLE IF EXISTS `%DB_PREFIX%user_credit_file`;
CREATE TABLE `%DB_PREFIX%user_credit_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `file` text NOT NULL,
  `create_time` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0未处理，1已处理',
  `passed` tinyint(1) NOT NULL COMMENT '是否认证通过',
  `passed_time` int(1) NOT NULL COMMENT '认证日期',
  `msg` text NOT NULL COMMENT '失败原因',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=390 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%user_credit_file` VALUES ('389','1','credit_identificationscanning','a:1:{i:0;s:49:\"/public/attachment/201602/14/23/56c09c0598038.png\";}','1455434630','0','0','0','');
DROP TABLE IF EXISTS `%DB_PREFIX%user_credit_type`;
CREATE TABLE `%DB_PREFIX%user_credit_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL COMMENT '类型名称',
  `type` varchar(100) NOT NULL COMMENT '审核类型',
  `icon` varchar(255) NOT NULL COMMENT '图标',
  `brief` text NOT NULL COMMENT '简介',
  `description` text NOT NULL COMMENT '认证说明',
  `role` varchar(255) NOT NULL COMMENT '认证条件',
  `file_tip` varchar(255) NOT NULL COMMENT '上传框说明',
  `file_count` int(11) NOT NULL,
  `expire` int(11) NOT NULL COMMENT '过期时间',
  `status` tinyint(1) NOT NULL COMMENT '0系统，1管理员新加',
  `is_effect` tinyint(1) NOT NULL COMMENT '0无效，1有效',
  `sort` int(11) NOT NULL COMMENT '排序',
  `point` int(11) NOT NULL COMMENT '信用积分',
  `must` tinyint(1) NOT NULL COMMENT '是否必须',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员认证审核资料';
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('1','实名认证','credit_identificationscanning','./public/credit/1.jpg','您上传的身份证扫描件需和您绑定的身份证一致，否则将无法通过认证。','<div class=\"lh22\">\r\n	1、请您上传您<span class=\"f_red\">本人身份证原件</span>的照片。如果您持有第二代身份证，请上传正、反两面照片。\r\n</div>\r\n<div class=\"lh22\">\r\n	如果您持有第一代身份证，仅需上传正面照片。\r\n</div>\r\n<div class=\"lh22\">\r\n	2、请确认您上传的资料是清晰的、未经修改的数码照片（不可以是扫描图片）。\r\n</div>\r\n<div class=\"lh22\">\r\n	每张图片的尺寸<span class=\"f_red\">不大于1.5M</span>。\r\n</div>','','','2','0','1','1','1','10','1');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('2','工作认证','credit_contact','./public/credit/2.jpg','您的工作状况是评估您信用状况的主要依据。请您填写真实可靠的工作信息。','上传资料说明：<br />\r\n如果您满足以下 1种以上的身份，例如：您有稳定工作，且兼职开淘宝店。<br />\r\n我们建议您同时上传两份资料，这将有助于提高您的借款额度和信用等级 <br />\r\n<br />\r\n<table class=\"f12\" cellspacing=\"1\" style=\"background:#ccc;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"pl10 pr10 wb\">\r\n				<h4>\r\n					工薪阶层：\r\n				</h4>\r\n请上传以下<span class=\"f_red\">至少两项</span>资料的照片或扫描件：\r\n			</td>\r\n			<td class=\"pl10 pr10 wb\">\r\n				<div class=\"lh22\">\r\n					a) 劳动合同。\r\n				</div>\r\n				<div class=\"lh22\">\r\n					b) 加盖单位公章的在职证明。\r\n				</div>\r\n				<div class=\"lh22\">\r\n					c) 带有姓名及照片的工作证。\r\n				</div>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td class=\"pl10 pr10 wb\">\r\n				<h4>\r\n					私营企业主:\r\n				</h4>\r\n请上传以下<span class=\"f_red\">全部三项</span>资料的照片或扫描件：\r\n			</td>\r\n			<td class=\"pl10 pr10 wb\">\r\n				<div class=\"lh22\">\r\n					a) 企业的营业执照。\r\n				</div>\r\n				<div class=\"lh22\">\r\n					b) 企业的税务登记证。\r\n				</div>\r\n				<div class=\"lh22\">\r\n					c) 店面照片（照片内需能看见营业执照）。\r\n				</div>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td class=\"pl10 pr10 wb\">\r\n				<h4>\r\n					网商：\r\n				</h4>\r\n请上传以下资料的照片或扫描件：\r\n			</td>\r\n			<td class=\"pl10 pr10 wb\">\r\n				<div class=\"lh22\">\r\n					a) 请上传网店主页和后台的截屏(需要看清网址）。\r\n				</div>\r\n				<div class=\"lh22\">\r\n					b) 支付宝（或其他第三方支付工具）的至少3张最近3个月的商户版成功交易记录的截屏图片。\r\n				</div>\r\n				<div class=\"lh22\">\r\n					c) 营业执照（如果有的话提供，不是必须的）。\r\n				</div>\r\n				<div class=\"lh22\">\r\n					d) 备注：如果是淘宝专职卖家，店铺等级必须为3钻以上（含3钻）。\r\n				</div>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>','工薪阶层需入职满6个月，私营企业主和淘宝商家需经营满一年','','4','6','1','1','2','10','1');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('3','信用报告','credit_credit','./public/credit/3.jpg','个人信用报告是由中国人民银行出具，全面记录个人信用活动，反映个人信用基本状况的文件。本报告是p2p信贷了解您信用状况的一个重要参考资料。 您信用报告内体现的信用记录，和信用卡额度等数据，将在您发布借款时经网站工作人员整理，在充分保护您隐私的前提下披露给借出者，作为借出者投标的依据。','<div>\r\n	<div class=\"lh22\">\r\n		1、个人信用报告需<span class=\"f_red\">15日内</span>开具。\r\n	</div>\r\n	<div class=\"lh22\">\r\n		2、上传您的<span class=\"f_red\">个人信用报告原件</span>的照片，每页信用报告需独立照相，并将整份信用报告按页码先后顺序完整上传。 <br />\r\n<a href=\"#creditDiv\" id=\"creditGuy\" class=\"f_blue\">如何办理个人信用报告？</a> <br />\r\n<a href=\"http://www.pbccrc.org.cn/zxzx/lxfs/lxfs.shtml\" target=\"_blank\" class=\"f_blue\">全国各地征信中心联系方式查询</a> \r\n	</div>\r\n	<div class=\"lh22\">\r\n		3、请确认您上传的资料是清晰的、未经修改的数码照片或扫描图片。\r\n	</div>\r\n</div>','','上传央行信用报告','2','6','1','1','3','10','1');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('4','收入认证','credit_incomeduty','./public/credit/4.jpg','您的银行流水单以及完税证明，是证明您收入情况的主要文件，也是评估您还款能力的主要依据之一。','上传资料说明：<br />\r\n如果您满意以下 1种以上的身份，例如：您有稳定工作，且兼职开淘宝店。 <br />\r\n我们建议您同时上传两份资料，这将有助于提高您的借款额度和信用等级。 <br />\r\n<table class=\"f12\" cellspacing=\"1\" style=\"background:#ccc;\">\r\n	<tbody>\r\n		<tr>\r\n			<td class=\"pl10 pr10 wb\">\r\n				<h4>\r\n					工薪阶层：\r\n				</h4>\r\n请上传右侧<span class=\"f_red\">一项或多项</span>资料：\r\n			</td>\r\n			<td class=\"wb\">\r\n				<div class=\"lh22\" style=\"padding:0 10px;\">\r\n					a) 最近连续六个月工资卡银行流水单的照片或扫描件，须有银行盖章，或工资卡网银的电脑截屏。<br />\r\n<a href=\"#bankDiv\" id=\"bankGuy\" class=\"f_blue\">如何办理银行流水单？</a> \r\n				</div>\r\n				<div class=\"lh22\" style=\"padding:0 10px;\">\r\n					b) 最近连续六个月的个人所得税完税凭证。<br />\r\n<a href=\"#dutyDiv\" id=\"dutyGuy\" class=\"f_blue\">如何办理个人所得税完税证明？</a> \r\n				</div>\r\n				<div class=\"lh22\" style=\"padding:0 10px;\">\r\n					c) 社保卡正反面原件的照片以及最近连续六个月缴费记录。\r\n				</div>\r\n				<div class=\"lh22\" style=\"padding:0 10px;\">\r\n					d) 如果工资用现金形式发放，请提供近半年的常用银行储蓄账户流水单，须有银行盖章，或工资卡网银的电脑截屏。。\r\n				</div>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td class=\"pl10 pr10 wb\">\r\n				<h4>\r\n					私营企业主:\r\n				</h4>\r\n请上传右侧<span class=\"f_red\">一项或多项</span>资料的照片或扫描件：\r\n			</td>\r\n			<td class=\"wb\">\r\n				<div class=\"lh22\" style=\"padding:0 10px;\">\r\n					a) 最近连续六个月个人银行卡流水单，须有银行盖章，或网银的电脑截屏。<br />\r\n<a href=\"#bankDiv\" id=\"bankGuy2\" class=\"f_blue\">如何办理银行流水单？</a> \r\n				</div>\r\n				<div class=\"lh22\" style=\"padding:0 10px;\">\r\n					b) 最近连续六个月企业银行流水单，须有银行盖章；或近半年企业的纳税证明。\r\n				</div>\r\n			</td>\r\n		</tr>\r\n		<tr>\r\n			<td class=\"pl10 pr10 wb\">\r\n				<h4>\r\n					网商：\r\n				</h4>\r\n请上传右侧<span class=\"f_red\">全部两项</span>资料的照片或扫描件：\r\n			</td>\r\n			<td class=\"wb\">\r\n				<div class=\"lh22\" style=\"padding:0 10px;\">\r\n					a) 最近连续六个月个人银行卡流水单，须有银行盖章，或网银的电脑截屏。<br />\r\n<a href=\"#bankDiv\" id=\"bankGuy2\" class=\"f_blue\">如何办理银行流水单？</a> \r\n				</div>\r\n				<div class=\"lh22\" style=\"padding:0 10px;\">\r\n					b) 如果是淘宝商家请上传近半年淘宝店支付宝账户明细的网银截图。\r\n				</div>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>','收入需较稳定，私营企业主及淘宝商家月均流水需在20000以上','上传完税证明','6','6','1','1','4','10','1');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('5','电子印章','credit_seal','./public/credit/6.jpg','电子印章将会在借款协议那边使用。','<div class=\"lh22\">\r\n                        	电子印章认证必须为<span class=\"f_red\">GIF</span>或者<span class=\"f_red\">PNG</span>的<span class=\"f_red\">背景透明</span>图片。\r\n                        </div>','','电子印章','1','0','1','1','5','2','0');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('6','房产认证','credit_house','./public/credit/15.jpg','房产证明是证明借入者资产及还款能力的重要凭证,根据借款者提供的房产证明给与借入者一定的信用加分。','1、 请上传以下任意一项或多项资料。\r\n<div class=\"pl15\">\r\n	<div class=\"lh22\">\r\n		a) <span class=\"f_red\">购房合同以及发票。</span> \r\n	</div>\r\n	<div class=\"lh22\">\r\n		b) <span class=\"f_red\">银行按揭贷款合同。</span> \r\n	</div>\r\n	<div class=\"lh22\">\r\n		c) <span class=\"f_red\">房产局产调单及收据。</span> \r\n	</div>\r\n</div>\r\n2、 请确认您上传的资料是清晰的、未经修改的数码照片或<span class=\"f_red\">彩色扫描</span>图片。 每张图片的尺寸<span class=\"f_red\">不大于3M</span>。','必须是商品房','上传房产证明','4','0','1','1','6','3','0');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('7','购车认证','credit_car','./public/credit/12.jpg','购车证明是证明借入者资产及还款能力的重要凭证之一，根据借入者提供的购车证明给与借入者一定的信用加分。','<div class=\"lh22\">\r\n	1、请上传您所购买<span class=\"f_red\">车辆行驶证</span>原件的照片。\r\n</div>\r\n<div class=\"lh22\">\r\n	2、请上传您和您购买车辆的<span class=\"f_red\">合影（照片须露出车牌号码）</span>。\r\n</div>\r\n<div class=\"lh22\">\r\n	3、请确认您上传的资料是清晰的、未经修改的数码照片或扫描图片。\r\n</div>','','上传汽车证明','4','0','1','1','7','3','0');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('8','学历认证','credit_graducation','./public/credit/10.jpg','出者在选择借款列表投标时，借入者的学历也是一个重要的参考因素。为了让借出者更好、更快地相信您的学历是真实的，强烈建议您对学历进行在线验证。','<div class=\"f14 f_red\">一、2001年至今获得学历，需学历证书编号</div>\r\n<div class=\"pl15\">\r\n<div class=\"lh22\">\r\n	1、点击 <a href=\"http://www.chsi.com.cn/xlcx/\" target=\"_blank\" class=\"f_blue\">网上学历查询</a>。\r\n</div>\r\n<div class=\"lh22\">\r\n	2、选择“零散查询”。\r\n</div>\r\n<div class=\"lh22\">\r\n	3、输入证书编号、查询码（通过手机短信获得，为12位学历查询码）、姓名、以及验证码进行查询。\r\n</div>\r\n<div class=\"lh22\">\r\n	4、查询成功后，您将查获得《教育部学历证书电子注册备案表》。\r\n</div>\r\n<div class=\"lh22\">\r\n	5、将该表<span class=\"f_red\">右下角的12位在线验证码</span><a href=\"./public/images/xueli_1.jpg\" target=\"_blank\" class=\"f_blue\">（见样本图01）</a>，输入下面的文本框。\r\n</div>\r\n<div class=\"lh22\">\r\n	6、点击提交审核。\r\n</div>\r\n</div>\r\n<div class=\"f14 f_red\">\r\n	二、1991年至今获得学历，无需学历证书编号\r\n</div>\r\n<div class=\"pl15\">\r\n<div class=\"lh22\">\r\n	1、点击 <a href=\"http://www.chsi.com.cn/xlcx/\" target=\"_blank\" class=\"f_blue\">网上学历查询</a>。\r\n</div>\r\n<div class=\"lh22\">\r\n	2、选择“本人查询”。\r\n</div>\r\n<div class=\"lh22\">\r\n	3、注册学信网账号。\r\n</div>\r\n<div class=\"lh22\">\r\n	4、登录学信网，点击“学历信息”。\r\n</div>\r\n<div class=\"lh22\">\r\n	5、选择您的最高学历，并点击“申请验证报告”（申请过程中，您需通过手机短信获得12位学历查询码，此查询码与{function name=\"app_conf\" v=\"SHOP_TITLE\"}所需验证码不同）。\r\n</div>\r\n<div class=\"lh22\">\r\n	6、申请成功后，您将获得<span class=\"f_red\">12位在线验证码</span><a href=\"./public/images/xueli_2.jpg\" target=\"_blank\" class=\"f_blue\">（见样本图02）</a> \r\n</div>\r\n<div class=\"lh22\">\r\n	7、将12位在线验证码输入下面的文本框\r\n</div>\r\n<div class=\"lh22\">\r\n	8、点击提交审核\r\n</div>\r\n</div>','大专或以上学历（普通全日制）','','0','0','1','1','8','10','0');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('9','技术职称认证','credit_titles','./public/credit/9.jpg','技术职称是经专家评审、反映一个人专业技术水平并作为聘任专业技术职务依据的一种资格，不与工资挂钩，是考核借款人信用的评估因素之一，通过技术职称认证证明，您将获得一定的信用加分。','<div class=\"lh22\">\r\n	1、请上传您的技术职称证书原件照片。\r\n</div>\r\n<div class=\"lh22\">\r\n	2、 请确认您上传的资料是清晰的、未经修改的数码照片或扫描图片。 每张图片的尺寸<span class=\"f_red\">不大于1.5M</span>。\r\n</div>','国家承认的二级及以上等级证书。例如律师证、会计证、工程师证等','上传技术职称认证','2','0','1','1','9','2','0');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('10','视频认证','credit_videoauth','./public/credit/8.jpg','什么是视频认证？只有通过视频认证您才能获得贷款，您只需要在视频认证页面上传您本人的视频，并提交，即可申请视频认证。您也可以选择与p2p信贷客服在线上进行视频认证。','<div class=\"lh22\">\r\n	1、视频录制要求：\r\n	<div>\r\n		（1）视频认证文件大小<span class=\"f_red\">不得超过50M</span><br />\r\n（2）	请上传真实有效的本人的视频<br />\r\n（3）	视频文件格式可以是RMVB、WMV、mp4 、 AVI等类型的文件<br />\r\n（4）	视频认证必须图像清晰，声音清楚<br />\r\n（5）	视频认证必须衣冠整洁，禁止出现抽烟，赤裸等形象\r\n	</div>\r\n</div>\r\n<div class=\"lh22\">\r\n	2、视频录制内容。请针对本次借款录制视频，视频中需包括以下内容：\r\n	<div>\r\n		<span class=\"b\">（1）：首先，请朗读以下文字：</span>我是 ***，我在{function name=\"app_conf\" v=\"SHOP_TITLE\"}的用户名是***，我的身份证号是 ***********************，现在我正在做{function name=\"app_conf\" v=\"SHOP_TITLE\"}的视频确认。我在此做出以下承诺：我愿意接受{function name=\"app_conf\" v=\"SHOP_TITLE\"}的使用条款和借款协议；我提供给{function name=\"app_conf\" v=\"SHOP_TITLE\"}的信息及资料均是真实有效的；我愿意对我在{function name=\"app_conf\" v=\"SHOP_TITLE\"}上的行为承担全部法律责任；在我未能按时归还借款时，我同意{function name=\"app_conf\" v=\"SHOP_TITLE\"}采取法律诉讼、资料曝光等一切必要措施。\r\n	</div>\r\n	<div>\r\n		<span class=\"b\">（2）：读完声明后，请您将身份证正面(有身份证号)对准摄像头，并保持5秒，需要保证画面中能同时看到您和您的身份证，并且身份证内容清晰可见。</span>\r\n	</div>\r\n</div>\r\n<div class=\"lh22\">\r\n	3、视频提交办法：您可以选择下列方法之一进行视频认证的提交：\r\n	<div>\r\n		（1）您可以联系右侧的在线QQ客服进行视频文件的提交。\r\n	</div>\r\n	<div>\r\n		（2）您可以将视频文件发送至<a name=\"app_conf\"></a>{function name=\"app_conf\" v=\"REPLY_ADDRESS\"}，请在邮件中注明您的{function name=\"app_conf\" v=\"SHOP_TITLE\"}用户名及真实姓名。\r\n	</div>\r\n	<div>\r\n		（3）当您通过上述两种方式之一提交过视频认证文件之后，请选择下面的选项并点击“提交审核”。\r\n	</div>\r\n</div>','','','0','0','1','1','10','2','0');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('11','手机实名认证','credit_mobilereceipt','./public/credit/7.jpg','手机流水单是最近一段时间内的详细通话记录，是验证借入者真实性的重要凭证之一。您的手机详单不会以任何形式被泄露。','<div class=\"div22\">\r\n	1、请您上传您绑定的手机号码<span class=\"f_red\">最近3个月的手机详单</span>原件的照片。如详单数量较多可分月打印并上传\r\n</div>\r\n<div class=\"lh22\">\r\n	<span class=\"f_red\">每月前5日部分</span>（每月详单均需清晰显示机主手机号码）。\r\n</div>\r\n<div class=\"lh22\">\r\n	2、请确认您上传的资料是清晰的、未经修改的数码照片（不可以是扫描图片）。\r\n</div>\r\n<div class=\"lh22\">\r\n	每张图片的尺寸<span class=\"f_red\">不大于1.5M</span>。\r\n</div>','','上传手机流水单','4','0','1','1','11','10','0');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('12','居住地认证','credit_residence','./public/credit/5.jpg','居住地的稳定性，是考核借款人的主要评估因素之一，通过居住地证明，您将获得一定的信用加分。','<div class=\"lh22\">\r\n	1、请上传以下任何一项可证明<span class=\"f_red\">现居住地址</span>的证明文件原件的照片。\r\n</div>\r\n<div class=\"lh22\">\r\n	1) 用您姓名登记的水、电、气最近三期缴费单；\r\n</div>\r\n<div class=\"lh22\">\r\n	2) 用您姓名登记固定电话最近三期缴费单；\r\n</div>\r\n<div class=\"lh22\">\r\n	3) 您的信用卡最近两期的月结单；\r\n</div>\r\n<div class=\"lh22\">\r\n	4) 您的自有房产证明；\r\n</div>\r\n<div class=\"lh22\">\r\n	5) 您父母的房产证明，及证明您和父母关系的证明材料。\r\n</div>\r\n<div class=\"lh22\">\r\n	2、请确认您上传的资料是清晰的、未经修改的数码照片（不可以是扫描图片）。\r\n</div>\r\n<div class=\"lh22\">\r\n	每张图片的尺寸<span class=\"f_red\">不大于1.5M</span>。\r\n</div>','','上传居住地证明','4','6','1','1','12','2','0');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('13','结婚认证','credit_marriage','./public/credit/11.jpg','借入者的婚姻状况的稳定性，是考核借款人信用的评估因素之一，通过结婚认证，您将获得一定的信用加分。','<div class=\"lh22\">\r\n	1、请您上传以下资料\r\n</div>\r\n<div class=\"lh22\">\r\n	1) 您<span class=\"f_red\">结婚证书</span>原件的照片\r\n</div>\r\n<div class=\"lh22\">\r\n	2) 您配偶的身份证原件的照片。如果持有第二代身份证，请上传正反两面\r\n</div>\r\n<div class=\"lh22\">\r\n	照片。如果持有第一代身份证，仅需上传正面照片。\r\n</div>\r\n<div class=\"lh22\">\r\n	3) 您和配偶的<span class=\"f_red\">近照合影</span>一张\r\n</div>\r\n<div class=\"lh22\">\r\n	2、请确认您上传的资料是清晰的、未经修改的数码照片或扫描图片。\r\n</div>','您的配偶同意您将其个人资料提供给本站','上传结婚证书','4','0','1','1','13','2','0');
INSERT INTO `%DB_PREFIX%user_credit_type` VALUES ('14','工龄认证','credit_seniority','','','请上传工龄证明复印件','带有劳动局盖章','工龄证明复印件','1','12','0','1','1','5','1');
DROP TABLE IF EXISTS `%DB_PREFIX%user_extend`;
CREATE TABLE `%DB_PREFIX%user_extend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1661 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_field`;
CREATE TABLE `%DB_PREFIX%user_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_name` varchar(255) NOT NULL,
  `field_show_name` varchar(255) NOT NULL,
  `input_type` tinyint(1) NOT NULL,
  `value_scope` text NOT NULL,
  `is_must` tinyint(1) NOT NULL,
  `is_show` tinyint(1) NOT NULL COMMENT '是否注册页面显示',
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unk_field_name` (`field_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_focus`;
CREATE TABLE `%DB_PREFIX%user_focus` (
  `focus_user_id` int(11) NOT NULL COMMENT '关注人ID',
  `focused_user_id` int(11) NOT NULL COMMENT '被关注人ID',
  `focus_user_name` varchar(255) NOT NULL,
  `focused_user_name` varchar(255) NOT NULL,
  PRIMARY KEY (`focus_user_id`,`focused_user_id`),
  KEY `focus_user_id` (`focus_user_id`) USING BTREE,
  KEY `focused_user_id` (`focused_user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_frequented`;
CREATE TABLE `%DB_PREFIX%user_frequented` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0' COMMENT '员会ID',
  `title` varchar(50) DEFAULT NULL,
  `addr` varchar(255) DEFAULT NULL,
  `xpoint` float(12,6) DEFAULT '0.000000' COMMENT 'longitude',
  `ypoint` float(12,6) DEFAULT '0.000000' COMMENT 'latitude',
  `latitude_top` float(12,6) DEFAULT NULL,
  `latitude_bottom` float(12,6) DEFAULT NULL,
  `longitude_left` float(12,6) DEFAULT NULL,
  `longitude_right` float(12,6) DEFAULT NULL,
  `zoom_level` int(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_group`;
CREATE TABLE `%DB_PREFIX%user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `score` int(11) NOT NULL,
  `discount` decimal(20,4) NOT NULL COMMENT '折扣',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_level`;
CREATE TABLE `%DB_PREFIX%user_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '等级名称',
  `point` int(11) NOT NULL COMMENT '所需经验',
  `services_fee` varchar(20) NOT NULL COMMENT '服务费率',
  `enddate` varchar(255) NOT NULL COMMENT '贷款时间',
  `repaytime` text NOT NULL COMMENT '借款期限和借款利率【一行一配置】',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unk` (`point`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员等级表';
INSERT INTO `%DB_PREFIX%user_level` VALUES ('1','HR','0','5','20','3|1|10|24\n6|1|11|24\n9|1|12|24\n12|1|15|24\n18|1|15|24\n24|1|15|24\n28|0|15|24');
INSERT INTO `%DB_PREFIX%user_level` VALUES ('2','E','100','3','7','3|1|10|24\n6|1|11|24\n9|1|12|24\n12|1|15|24\n18|1|15|24\n24|1|15|24');
INSERT INTO `%DB_PREFIX%user_level` VALUES ('3','D','110','2.5','7','3|1|10|24\n6|1|11|24\n9|1|12|24\n12|1|15|24\n18|1|15|24\n24|1|15|24');
INSERT INTO `%DB_PREFIX%user_level` VALUES ('4','C','120','2','7','3|1|10|24\r\n6|1|11|24\r\n9|1|12|24\r\n12|1|15|24\r\n18|1|15|24\r\n24|1|15|24');
INSERT INTO `%DB_PREFIX%user_level` VALUES ('5','B','130','1.5','7','3|1|10|24\n6|1|11|24\n9|1|12|24\n12|1|15|24\n18|1|15|24\n24|1|15|24');
INSERT INTO `%DB_PREFIX%user_level` VALUES ('6','A','145','1','7','3|1|10|24\r\n6|1|11|24\r\n9|1|12|24\r\n12|1|15|24\r\n18|1|15|24\r\n24|1|15|24');
INSERT INTO `%DB_PREFIX%user_level` VALUES ('7','AA','160','0','7','3|0|10|15\n6|0|16|24\n3|1|10|24\n6|1|11|24\n9|1|12|24\n12|1|15|24\n18|1|15|24\n24|1|15|25');
DROP TABLE IF EXISTS `%DB_PREFIX%user_lock_money_log`;
CREATE TABLE `%DB_PREFIX%user_lock_money_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '关联用户',
  `lock_money` decimal(20,2) NOT NULL COMMENT '操作金额',
  `account_lock_money` decimal(20,2) NOT NULL COMMENT '当前账户余额',
  `memo` text NOT NULL COMMENT '操作备注',
  `type` tinyint(2) NOT NULL COMMENT '0结存，1充值，2投标成功，8申请提现，9提现手续费，10借款管理费，18开户奖励，19流标还返',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
  `create_time_ymd` date NOT NULL COMMENT '操作时间 ymd',
  `create_time_ym` int(6) NOT NULL COMMENT '操作时间 ym',
  `create_time_y` int(4) NOT NULL COMMENT '操作时间 y',
  PRIMARY KEY (`id`),
  KEY `idx0` (`user_id`,`type`,`create_time`),
  KEY `idx1` (`user_id`,`type`,`create_time_ymd`),
  KEY `idx2` (`user_id`,`type`),
  KEY `create_time_ymd` (`create_time_ymd`),
  KEY `idx3` (`type`,`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3165 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员资金日志表';
INSERT INTO `%DB_PREFIX%user_lock_money_log` VALUES ('3164','1','0.00','0.00','在2016-02-14 23:22:29注册成功','18','1455434549','2016-02-14','201602','2016');
DROP TABLE IF EXISTS `%DB_PREFIX%user_log`;
CREATE TABLE `%DB_PREFIX%user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_info` text NOT NULL,
  `log_time` int(11) NOT NULL,
  `log_admin_id` int(11) NOT NULL,
  `log_user_id` int(11) NOT NULL,
  `money` decimal(20,2) NOT NULL COMMENT '相关的钱',
  `score` int(11) NOT NULL,
  `point` int(11) NOT NULL,
  `quota` decimal(20,2) NOT NULL COMMENT '相关的额度',
  `lock_money` decimal(20,2) NOT NULL COMMENT '相关的冻结资金',
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%user_log` VALUES ('1','您在2016-07-26 09:26:01签到成功','1469467561','0','1','0.00','100','0','0.00','0.00','1');
INSERT INTO `%DB_PREFIX%user_log` VALUES ('2','线下充值','1469471041','1','0','10000.00','0','0','0.00','0.00','1');
INSERT INTO `%DB_PREFIX%user_log` VALUES ('3','输入正数金额','1469471087','1','0','20000.00','0','0','0.00','0.00','1');
INSERT INTO `%DB_PREFIX%user_log` VALUES ('4','您在2016-07-27 15:19:12签到成功','1469575152','1','0','0.00','100','0','0.00','0.00','1');
DROP TABLE IF EXISTS `%DB_PREFIX%user_medal`;
CREATE TABLE `%DB_PREFIX%user_medal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `medal_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `create_time` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL,
  `icon` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_money_log`;
CREATE TABLE `%DB_PREFIX%user_money_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '关联用户',
  `money` decimal(20,2) NOT NULL COMMENT '操作金额',
  `account_money` decimal(20,2) NOT NULL COMMENT '当前账户余额',
  `memo` text NOT NULL COMMENT '操作备注',
  `type` tinyint(2) NOT NULL COMMENT '0结存，1充值，2投标成功，3招标成功，4偿还本息，5回收本息，6提前还款，7提前回收，8申请提现，9提现手续费，10借款管理费，11逾期罚息，12逾期管理费，13人工充值，14借款服务费，15出售债权，16购买债权，17债权转让管理费，18开户奖励，19流标还返，20投标管理费，21投标逾期收入，22兑换，23邀请返利，24投标返利，25签到成功',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
  `create_time_ymd` date NOT NULL COMMENT '操作时间 ymd',
  `create_time_ym` int(6) NOT NULL COMMENT '操作时间 ym',
  `create_time_y` int(4) NOT NULL COMMENT '操作时间 y',
  `from_user_id` int(4) DEFAULT NULL COMMENT '来源ID(返佣)',
  `from_deal_id` int(4) DEFAULT NULL COMMENT '关联标(借款者)',
  `from_load_repay_id` int(4) DEFAULT NULL COMMENT '关联还款计划(投资者)',
  PRIMARY KEY (`id`),
  KEY `idx0` (`user_id`,`type`,`create_time`),
  KEY `idx1` (`user_id`,`type`,`create_time_ymd`),
  KEY `idx2` (`user_id`,`type`),
  KEY `create_time_ymd` (`create_time_ymd`),
  KEY `idx3` (`type`,`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=9298 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员资金日志表';
INSERT INTO `%DB_PREFIX%user_money_log` VALUES ('9295','1','1000.00','1000.00','在2016-02-14 23:22:29注册成功','18','1455434549','2016-02-14','201602','2016','0','0','0');
INSERT INTO `%DB_PREFIX%user_money_log` VALUES ('9296','1','10000.00','10000.00','线下充值','1','1469471041','2016-07-26','201607','2016','','','');
INSERT INTO `%DB_PREFIX%user_money_log` VALUES ('9297','1','20000.00','30000.00','输入正数金额','13','1469471087','2016-07-26','201607','2016','','','');
DROP TABLE IF EXISTS `%DB_PREFIX%user_nmc_money_log`;
CREATE TABLE `%DB_PREFIX%user_nmc_money_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '关联用户',
  `money` decimal(20,2) NOT NULL COMMENT '操作金额',
  `account_money` decimal(20,2) NOT NULL COMMENT '当前账户余额',
  `memo` varchar(255) NOT NULL COMMENT '操作备注',
  `type` tinyint(2) NOT NULL COMMENT '18开户奖励，28投资奖励，29红包奖励',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
  `create_time_ymd` date NOT NULL COMMENT '操作时间 ymd',
  `create_time_ym` int(11) NOT NULL COMMENT '操作时间 ym',
  `create_time_y` int(11) NOT NULL COMMENT '操作时间 y',
  PRIMARY KEY (`id`),
  KEY `idx0` (`user_id`,`type`,`create_time`),
  KEY `idx1` (`user_id`,`type`,`create_time_ymd`),
  KEY `idx2` (`user_id`,`type`),
  KEY `create_time_ymd` (`create_time_ymd`),
  KEY `idx3` (`type`,`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_point_log`;
CREATE TABLE `%DB_PREFIX%user_point_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '关联用户',
  `point` decimal(20,2) NOT NULL COMMENT '操作信用积分',
  `account_point` decimal(20,2) NOT NULL COMMENT '当前账户信用积分',
  `memo` text NOT NULL COMMENT '操作备注',
  `type` tinyint(2) NOT NULL COMMENT '0结存，4偿还本息，5回收本息，6提前还款，7提前回收，8申请认证，11逾期还款，13人工充值，14借款服务费，18开户奖励，22兑换，23邀请返利，24投标返利，25签到成功',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
  `create_time_ymd` date NOT NULL COMMENT '操作时间 ymd',
  `create_time_ym` int(6) NOT NULL COMMENT '操作时间 ym',
  `create_time_y` int(4) NOT NULL COMMENT '操作时间 y',
  PRIMARY KEY (`id`),
  KEY `idx0` (`user_id`,`type`,`create_time`),
  KEY `idx1` (`user_id`,`type`,`create_time_ymd`),
  KEY `idx2` (`user_id`,`type`),
  KEY `create_time_ymd` (`create_time_ymd`),
  KEY `idx3` (`type`,`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1852 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员信用积分日志表';
INSERT INTO `%DB_PREFIX%user_point_log` VALUES ('1851','1','20.00','20.00','在2016-02-14 23:22:29注册成功','18','1455434549','2016-02-14','201602','2016');
DROP TABLE IF EXISTS `%DB_PREFIX%user_recharge_config`;
CREATE TABLE `%DB_PREFIX%user_recharge_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '简称',
  `interface_class` varchar(50) NOT NULL COMMENT '接口类名',
  `fee` decimal(20,4) NOT NULL COMMENT '费率',
  `fee_type` tinyint(1) NOT NULL COMMENT '费率类型 0 是固定值 1是百分比',
  `vip_id` int(11) NOT NULL COMMENT 'VIP等级     0默认配置  否则就是对应VIP等级设置',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_score_log`;
CREATE TABLE `%DB_PREFIX%user_score_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '关联用户',
  `score` decimal(20,2) NOT NULL COMMENT '操作积分',
  `account_score` decimal(20,2) NOT NULL COMMENT '当前账户剩余积分',
  `memo` varchar(255) NOT NULL COMMENT '操作备注',
  `type` tinyint(2) NOT NULL COMMENT '0结存，1充值，2投标成功，3招标成功，4偿还本息，5回收本息，6提前还款，13人工充值，14借款服务费，15出售债权，16购买债权，17债权转让管理费，18开户奖励，19流标还返，20投标管理费，21投标逾期收入，22兑换，23邀请返利，24投标返利，25签到成功 ',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
  `create_time_ymd` date NOT NULL COMMENT '����ʱ�� ymd',
  `create_time_ym` int(11) NOT NULL COMMENT 'ym',
  `create_time_y` int(11) NOT NULL COMMENT 'y',
  PRIMARY KEY (`id`),
  KEY `idx0` (`user_id`,`type`,`create_time`),
  KEY `idx1` (`user_id`,`type`,`create_time_ymd`),
  KEY `idx2` (`user_id`,`type`),
  KEY `create_time_ymd` (`create_time_ymd`),
  KEY `idx3` (`type`,`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1405 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%user_score_log` VALUES ('1399','1','100.00','100.00','在2016-02-14 23:22:29注册成功','18','1455434549','2016-02-14','201602','2016');
INSERT INTO `%DB_PREFIX%user_score_log` VALUES ('1400','1','100.00','200.00','您在2016-07-21 18:57:17签到成功','25','1469069837','2016-07-21','201607','2016');
INSERT INTO `%DB_PREFIX%user_score_log` VALUES ('1401','1','100.00','300.00','您在2016-07-22 20:50:02签到成功','25','1469163002','2016-07-22','201607','2016');
INSERT INTO `%DB_PREFIX%user_score_log` VALUES ('1402','1','100.00','400.00','您在2016-07-26 09:26:01签到成功','25','1469467561','2016-07-26','201607','2016');
INSERT INTO `%DB_PREFIX%user_score_log` VALUES ('1403','1','0.00','400.00','线下充值','1','1469471041','2016-07-26','201607','2016');
INSERT INTO `%DB_PREFIX%user_score_log` VALUES ('1404','1','100.00','500.00','您在2016-07-27 15:19:12签到成功','25','1469575152','2016-07-27','201607','2016');
