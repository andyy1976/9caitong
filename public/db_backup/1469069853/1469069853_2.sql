-- fanwe SQL Dump Program
-- nginx/1.4.4
-- 
-- DATE : 2016-07-21 18:57:34
-- MYSQL SERVER VERSION : 5.6.29
-- PHP VERSION : fpm-fcgi
-- Vol : 2


DROP TABLE IF EXISTS `%DB_PREFIX%deal_agency`;
CREATE TABLE `%DB_PREFIX%deal_agency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header` text NOT NULL,
  `name` varchar(100) NOT NULL,
  `brief` text NOT NULL,
  `company_brief` text NOT NULL,
  `history` text NOT NULL,
  `content` text NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  `sort` int(11) NOT NULL,
  `short_name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `acct_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '担保方类型 否 0#机构；1#个人',
  `ips_mer_code` varchar(10) DEFAULT NULL COMMENT '由IPS颁发的商户号 acct_type = 0',
  `idno` varchar(20) DEFAULT NULL COMMENT '真实身份证 acct_type =1',
  `real_name` varchar(30) DEFAULT NULL COMMENT 'acct_type = 1真实姓名',
  `mobile` varchar(11) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `ips_acct_no` varchar(30) DEFAULT NULL COMMENT 'ips个人帐户',
  `password` varchar(32) NOT NULL COMMENT '密码',
  `code` varchar(30) DEFAULT NULL COMMENT '找回密码验证码',
  `update_time` int(11) NOT NULL COMMENT '修改时间',
  `create_time` int(11) NOT NULL COMMENT '注册时间',
  `login_ip` varchar(30) DEFAULT NULL COMMENT '登陆ip',
  `login_time` int(11) DEFAULT NULL COMMENT '登陆时间',
  `emailpassed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '邮箱验证标识符',
  `verify` varchar(10) DEFAULT NULL COMMENT '邮件验证码',
  `verify_create_time` int(11) DEFAULT NULL COMMENT '邮件验证码生成时间',
  `mobilepassed` tinyint(1) DEFAULT '0' COMMENT '手机验证标识',
  `bind_verify` varchar(10) DEFAULT NULL COMMENT '手机绑定验证码',
  `view_info` text NOT NULL COMMENT '资料展示',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%deal_cate`;
CREATE TABLE `%DB_PREFIX%deal_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `brief` text NOT NULL,
  `pid` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  `sort` int(11) NOT NULL,
  `uname` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '分类icon',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%deal_cate` VALUES ('1','信用认证标','这个有意义吗？','0','0','1','10','','./public/attachment/201209/10/11/504d5d393a3a1.jpg');
INSERT INTO `%DB_PREFIX%deal_cate` VALUES ('2','实地认证标','','0','0','1','9','','./public/attachment/201407/30/14/53d88b6a8c0d9.png');
INSERT INTO `%DB_PREFIX%deal_cate` VALUES ('3','机构担保标','','0','0','1','3','','./public/attachment/201407/30/14/53d88c14b18c2.png');
INSERT INTO `%DB_PREFIX%deal_cate` VALUES ('4','智能理财标','','0','0','1','4','','./public/attachment/201212/25/10/50d90ba803a9d.jpg');
INSERT INTO `%DB_PREFIX%deal_cate` VALUES ('6','旅游考察标','','0','0','0','10','','./public/attachment/201407/30/14/53d88c658ffbf.png');
DROP TABLE IF EXISTS `%DB_PREFIX%deal_city`;
CREATE TABLE `%DB_PREFIX%deal_city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `uname` varchar(255) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  `is_delete` tinyint(1) NOT NULL,
  `pid` int(11) NOT NULL,
  `is_default` tinyint(1) NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keyword` text NOT NULL,
  `seo_description` text NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%deal_city` VALUES ('1','全国','quanguo','1','0','0','1','','','','0');
DROP TABLE IF EXISTS `%DB_PREFIX%deal_city_link`;
CREATE TABLE `%DB_PREFIX%deal_city_link` (
  `deal_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  KEY `idx0` (`deal_id`,`city_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%deal_city_link` VALUES ('2','1');
DROP TABLE IF EXISTS `%DB_PREFIX%deal_collect`;
CREATE TABLE `%DB_PREFIX%deal_collect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `deal_id` (`deal_id`),
  KEY `user_id` (`user_id`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=265 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%deal_collect` VALUES ('264','2','1','1469068211');
DROP TABLE IF EXISTS `%DB_PREFIX%deal_inrepay_repay`;
CREATE TABLE `%DB_PREFIX%deal_inrepay_repay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `repay_money` decimal(20,2) NOT NULL COMMENT '提前还款多少',
  `manage_money` decimal(20,2) NOT NULL COMMENT '提前还款管理费',
  `mortgage_fee` decimal(20,2) NOT NULL COMMENT '代换多少抵押物管理费',
  `impose` decimal(20,2) NOT NULL COMMENT '提前还款罚息',
  `repay_time` int(11) NOT NULL,
  `true_repay_time` int(11) NOT NULL,
  `self_money` decimal(20,2) NOT NULL COMMENT '提前还款本金',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%deal_load`;
CREATE TABLE `%DB_PREFIX%deal_load` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL COMMENT '借款ID',
  `user_id` int(11) NOT NULL COMMENT '投标人ID',
  `user_name` varchar(50) NOT NULL COMMENT '用户名',
  `money` decimal(20,2) NOT NULL COMMENT '投标金额',
  `create_time` int(11) NOT NULL COMMENT '投标时间',
  `is_repay` tinyint(1) NOT NULL COMMENT '流标是否已返还',
  `is_rebate` tinyint(1) NOT NULL COMMENT '是否已返利',
  `is_auto` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为自动投标 0:收到 1:自动',
  `pP2PBillNo` varchar(30) DEFAULT NULL COMMENT 'IPS P2P订单号 否 由IPS系统生成的唯一流水号',
  `pContractNo` varchar(30) DEFAULT NULL COMMENT '合同号',
  `pMerBillNo` varchar(30) DEFAULT NULL COMMENT '登记债权人时提 交的订单号',
  `is_has_loans` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已经放款给招标人',
  `msg` varchar(100) DEFAULT NULL COMMENT '转账备注  转账失败的原因',
  `is_old_loan` tinyint(1) NOT NULL COMMENT '历史投标 0 不是  1 是 ',
  `create_date` date NOT NULL COMMENT '记录投资日期,方便统计使用',
  `rebate_money` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '返利金额',
  `bid_score` int(11) NOT NULL COMMENT 'Ͷ���õĻ���',
  `ecv_id` int(11) NOT NULL COMMENT '使用的红包的ID',
  `learn_id` int(11) NOT NULL COMMENT '体验金id',
  `learn_money` decimal(20,2) NOT NULL COMMENT '体验金金额',
  `back_learn_money` decimal(20,2) NOT NULL COMMENT '已回收金额',
  `is_winning` tinyint(1) NOT NULL COMMENT '�Ƿ��н� 0δ�н� 1�н�',
  `income_type` tinyint(1) NOT NULL COMMENT '�������� 1��� 2������ 3���� 4��Ʒ',
  `income_value` varchar(100) DEFAULT NULL COMMENT '����ֵ',
  `interestrate_id` int(11) NOT NULL DEFAULT '0' COMMENT '加息券编号',
  `interestrate_money` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '加息券加的利息',
  PRIMARY KEY (`id`),
  KEY `deal_id` (`deal_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_dl_001` (`create_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1045 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%deal_load_repay`;
CREATE TABLE `%DB_PREFIX%deal_load_repay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `self_money` decimal(20,2) NOT NULL COMMENT '本金',
  `repay_money` decimal(20,2) NOT NULL COMMENT '还款金额',
  `manage_money` decimal(20,2) NOT NULL COMMENT '管理费',
  `impose_money` decimal(20,2) NOT NULL COMMENT '罚息',
  `repay_time` int(11) NOT NULL COMMENT '预计回款时间',
  `repay_date` date NOT NULL COMMENT '预计回款时间,方便统计',
  `true_repay_time` int(11) NOT NULL COMMENT '实际回款时间',
  `true_repay_date` date NOT NULL COMMENT '实际回款时间,方便统计使用',
  `true_repay_money` decimal(20,2) NOT NULL COMMENT '真实还款本息',
  `true_self_money` decimal(20,2) NOT NULL COMMENT '真实还款本金',
  `interest_money` decimal(20,2) NOT NULL COMMENT '利息   repay_money - self_money',
  `true_interest_money` decimal(20,2) NOT NULL COMMENT '实际利息',
  `true_manage_money` decimal(20,2) NOT NULL COMMENT '实际管理费',
  `true_repay_manage_money` decimal(20,2) NOT NULL,
  `status` int(11) NOT NULL COMMENT '0提前，1准时，2逾期，3严重逾期',
  `is_site_repay` tinyint(1) NOT NULL COMMENT '0自付，1网站垫付 2担保机构垫付',
  `l_key` int(11) NOT NULL DEFAULT '0',
  `u_key` int(11) NOT NULL DEFAULT '0',
  `repay_id` int(11) NOT NULL COMMENT '还款计划ID',
  `load_id` int(11) NOT NULL COMMENT '投标记录ID',
  `has_repay` tinyint(11) NOT NULL DEFAULT '0' COMMENT '0未收到还款，1已收到还款',
  `t_user_id` int(11) NOT NULL COMMENT '承接着会员ID',
  `repay_manage_money` decimal(20,2) NOT NULL COMMENT '从借款者均摊下来的管理费',
  `repay_manage_impose_money` decimal(20,2) NOT NULL COMMENT '借款者均摊下来的逾期管理费',
  `reward_money` decimal(20,2) NOT NULL COMMENT 'Ԥ�ƽ�������',
  `true_reward_money` decimal(20,2) NOT NULL COMMENT 'ʵ�ʽ�������',
  `loantype` int(11) NOT NULL COMMENT '还款方式',
  `manage_interest_money` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT 'Ԥ�����յ�����Ϣ�����,��������ſ�ʱ����',
  `true_manage_interest_money` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT 'ʵ���յ�����Ϣ�����,���ڻ���ʱ����',
  `manage_interest_money_rebate` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT 'Ԥ�Ʒ�Ӷ���(������Ȩ����)',
  `true_manage_interest_money_rebate` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT 'ʵ�ʷ�Ӷ���(������Ȩ����)',
  `t_pMerBillNo` varchar(255) DEFAULT NULL COMMENT 'ips债权转让后新的ips流水号',
  `interestrate_money` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '加息券加的利息',
  `true_total_interest_money` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '真实发放的利息（包含加息券产生的）',
  `mortgage_fee` decimal(20,2) NOT NULL COMMENT '抵押物管理费',
  `true_mortgage_fee` decimal(20,2) NOT NULL COMMENT '抵押物管理费',
  PRIMARY KEY (`id`),
  KEY `idx_1` (`user_id`,`status`),
  KEY `idx_0` (`deal_id`,`user_id`,`l_key`,`u_key`),
  KEY `idx_2` (`deal_id`,`user_id`,`repay_time`,`l_key`,`u_key`),
  KEY `idx_dl_001` (`repay_date`),
  KEY `idx_dl_002` (`true_repay_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1853 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%deal_load_transfer`;
CREATE TABLE `%DB_PREFIX%deal_load_transfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL COMMENT '所投的标',
  `load_id` int(11) NOT NULL COMMENT '债权ID',
  `user_id` int(11) NOT NULL COMMENT '债权人ID',
  `transfer_amount` decimal(20,2) NOT NULL COMMENT '转让价格',
  `load_money` decimal(20,2) NOT NULL COMMENT '投标金额',
  `last_repay_time` int(11) NOT NULL COMMENT '最后还款日期',
  `near_repay_time` int(11) NOT NULL COMMENT '下次还款日',
  `transfer_number` int(11) NOT NULL COMMENT '转让期数',
  `t_user_id` int(11) NOT NULL COMMENT '承接人',
  `transfer_time` int(11) NOT NULL COMMENT '承接时间',
  `create_time` int(11) NOT NULL COMMENT '发布时间',
  `status` tinyint(1) NOT NULL COMMENT '转让状态，0取消 1开始',
  `callback_count` int(11) NOT NULL COMMENT '撤回次数',
  `lock_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '锁定用户id,给用户支付时间,主要用于资金托管',
  `lock_time` int(11) NOT NULL DEFAULT '0' COMMENT '锁定时间,10分钟后,自动解锁;给用户支付时间,主要用于资金托管',
  `ips_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'ips处理状态;0:未处理;1:已登记债权转让;2:已转让',
  `ips_bill_no` varchar(30) DEFAULT NULL COMMENT 'IPS P2P订单号 否 由IPS系统生成的唯一流水号',
  `pMerBillNo` varchar(30) DEFAULT NULL COMMENT '商户订单号 商户系统唯一不重复',
  `create_date` date NOT NULL COMMENT '发布时间,日期格式,方便统计',
  `transfer_date` date NOT NULL COMMENT '承接时间,日期格式,方便统计',
  PRIMARY KEY (`id`),
  KEY `idx_0` (`deal_id`,`user_id`),
  KEY `idx_1` (`deal_id`,`user_id`,`status`),
  KEY `idx_2` (`id`,`transfer_amount`,`create_time`),
  KEY `idx_3` (`deal_id`,`status`,`t_user_id`),
  KEY `idx_dlt_001` (`create_date`),
  KEY `idx_dlt_002` (`transfer_date`)
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='债权转让';
DROP TABLE IF EXISTS `%DB_PREFIX%deal_loan_type`;
CREATE TABLE `%DB_PREFIX%deal_loan_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `brief` text NOT NULL,
  `pid` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  `sort` int(11) NOT NULL,
  `uname` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '分类icon',
  `applyto` varchar(255) NOT NULL COMMENT '适用人群',
  `condition` text NOT NULL COMMENT '申请条件',
  `credits` text NOT NULL COMMENT '必要申请资料',
  `is_quota` tinyint(1) NOT NULL COMMENT '�������  0�� 1��',
  `costsetting` text NOT NULL COMMENT '费用设置（VIP等级）',
  `levelsetting` text NOT NULL COMMENT '费用设置2（用户等级）',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%deal_loan_type` VALUES ('1','短期周转','','0','0','1','10','','./public/images/dealtype/dqzz.png','','','a:1:{i:0;s:12:\"credit_house\";}','1','2|2.5|0.2|0.09|8|1.1|1.1\n3|2|0.15|0.08|6|1.2|1.2\n4|1|0.1|0.07|4|1.3|1.3\n5|0|0.05|0.06|2|1.4|1.4','1|5|0.3|0.6|15|1|1\n2|3|0.29|0.5|14|2|1.5\n3|2.5|0.28|0.4|13|3|2\n4|2|0.27|0.3|12|4|2.5\n5|1.5|0.26|0.2|11|5|3\n6|1|0.25|0.1|10|6|3.5\n7|0|0.24|0|9|7|4');
INSERT INTO `%DB_PREFIX%deal_loan_type` VALUES ('2','购房借款','','0','0','1','9','','./public/images/dealtype/gf.png','','22-55周岁的中国公民\r\n在现单位工作满3个月\r\n月收入2000以上','N;','1','2|10|0.5|0.8|0.8|1.5|1.1\n3|5|0.45|0.6|0.6|2|1.2\n4|2.5|0.4|0.4|0.4|2.5|1.3\n5|1|0.35|0.2|0.2|3|1.4','1|4|0.5|0.7|15|1|1\n2|3|0.29|0.5|14|2|1.5\n3|2.5|0.28|0.4|13|3|2\n4|2|0.27|0.3|12|4|2.5\n5|1.5|0.26|0.2|11|5|3\n6|1|0.25|0.1|10|6|3.5\n7|0|0.24|0|9|7|4');
INSERT INTO `%DB_PREFIX%deal_loan_type` VALUES ('3','装修借款','','0','0','1','8','','./public/images/dealtype/zx.png','','','a:1:{i:0;s:18:\"credit_graducation\";}','1','2|6|2|0.1|5|0.5|0.5\n3|5|1.5|0.05|4|1|1\n4|3|1|0.04|3|1.5|1.5\n5|2|0.5|0.02|2|2|2','1|6|0.4|0.7|15|1|1\n2|3|0.29|0.5|14|2|1.5\n3|2.5|0.28|0.4|13|3|2\n4|2|0.27|0.3|12|4|2.5\n5|1.5|0.26|0.2|11|5|3\n6|1|0.25|0.1|10|6|3.5\n7|0|0.24|0|9|7|4');
INSERT INTO `%DB_PREFIX%deal_loan_type` VALUES ('4','个人消费','','0','0','0','7','','./public/images/dealtype/grxf.png','','','N;','1','2|2.55|0.22|0.33|11|1.1|1.1\n3|2.3|0.21|0.28|10|1.2|1.2\n4|1.2|0.2|0.17|9|1.3|1.3\n5|0|0.19|0.06|8|1.4|1.4','1|5|0.3|0.6|15|1|1\n2|3|0.29|0.5|14|2|1.5\n3|2.5|0.28|0.4|13|3|2\n4|2|0.27|0.3|12|4|2.5\n5|1.5|0.26|0.2|11|5|3\n6|1|0.25|0.1|10|6|3.5\n7|0|0.24|0|9|7|4');
INSERT INTO `%DB_PREFIX%deal_loan_type` VALUES ('5','婚礼筹备','','0','0','1','6','','./public/images/dealtype/hlcb.png','','1111111111','N;','1','2|5.5|0.5|0.15|9|1.3|1.3\n3|4.5|0.4|0.1|8|1.4|1.4\n4|3.5|0.3|0.05|7|1.5|1.5\n5|1.5|0.2|0.01|6|1.6|1.6','1|10|3|0.6|15|1|1\n2|3|0.29|0.5|14|2|1.5\n3|2.5|0.28|0.4|13|3|2\n4|2|0.27|0.3|12|4|2.5\n5|1.5|0.26|0.2|11|5|3\n6|1|0.25|0.1|10|6|3.5\n7|0|0.24|0|9|7|4');
INSERT INTO `%DB_PREFIX%deal_loan_type` VALUES ('6','教育培训','','0','0','0','5','','./public/images/dealtype/jypx.png','','','N;','1','2|2.5|0.39|0.29|5|1.21|1.21\n3|2|0.38|0.28|4|1.22|1.22\n4|1.5|0.37|0.27|3|1.23|1.23\n5|1|0.35|0.26|2|1.24|1.24','1|5|0.3|0.6|15|1|1\n2|3|0.29|0.5|14|2|1.5\n3|2.5|0.28|0.4|13|3|2\n4|2|0.27|0.3|12|4|2.5\n5|1.5|0.26|0.2|11|5|3\n6|1|0.25|0.1|10|6|3.5\n7|0|0.24|0|9|7|4');
INSERT INTO `%DB_PREFIX%deal_loan_type` VALUES ('7','汽车消费','','0','0','0','4','','./public/images/dealtype/qcxf.png','','','N;','1','2|3|0.28|0.1|8|1|1.5\n3|2.5|0.27|0.09|7|1.1|1.6\n4|2|0.26|0.08|6|1.2|1.7\n5|1|0.25|0.07|5|1.3|1.8','1|5|0.3|0.6|15|1|1\n2|3|0.29|0.5|14|2|1.5\n3|2.5|0.28|0.4|13|3|2\n4|2|0.27|0.3|12|4|2.5\n5|1.5|0.26|0.2|11|5|3\n6|1|0.25|0.1|10|6|3.5\n7|0|0.24|0|9|7|4');
INSERT INTO `%DB_PREFIX%deal_loan_type` VALUES ('8','投资创业','','0','0','1','3','','./public/images/dealtype/tzcy.png','','','N;','1','2|3.5|0.9|0.05|12|1.1|1.1\n3|3|0.8|0.04|10|1.2|1.2\n4|2.5|0.7|0.03|8|1.3|1.3\n5|2|0.6|0.02|6|1.4|1.4','1|15|1.3|1.6|15|1|1\n2|3|0.29|0.5|14|2|1.5\n3|2.5|0.28|0.4|13|3|2\n4|2|0.27|0.3|12|4|2.5\n5|1.5|0.26|0.2|11|5|3\n6|1|0.25|0.1|10|6|3.5\n7|0|0.24|0|9|7|4');
INSERT INTO `%DB_PREFIX%deal_loan_type` VALUES ('9','医疗支出','44','0','0','1','2','','./public/images/dealtype/ylzc.png','','','N;','1','2|5|0.3|0.9|10|1.4|1.4\n3|4|0.28|0.8|9|1.5|1.5\n4|3|0.25|0.7|5|1.6|1.6\n5|2|0.2|0.6|3|1.7|1.7','1|8|0.35|0.6|15|1|1\n2|3|0.29|0.5|14|2|1.5\n3|2.5|0.28|0.4|13|3|2\n4|2|0.27|0.3|12|4|2.5\n5|1.5|0.26|0.2|11|5|3\n6|1|0.25|0.1|10|6|3.5\n7|0|0.24|0|9|7|4');
INSERT INTO `%DB_PREFIX%deal_loan_type` VALUES ('10','其他借款','','0','1','1','1','','./public/images/dealtype/other.png','','','','1','\r\n2|2.5|0.29|0.09|8|1.1|1.1\r\n3|2|0.28|0.08|6|1.2|1.2\r\n4|1|0.27|0.07|4|1.3|1.3\r\n5|0|0.25|0.06|2|1.4|1.4','1|5|0.3|0.1|15|1|1\r\n2|3|0.3|0.1|14|1|1\r\n3|2.5|0.29|0.1|13|1|1\r\n4|2|0.29|0.1|12|1|1\r\n5|1.5|0.28|0.1|11|1|1\r\n6|1|0.28|0.1|10|1|1\r\n7|0|0.27|0.1|9|1|1');
DROP TABLE IF EXISTS `%DB_PREFIX%deal_msg_list`;
CREATE TABLE `%DB_PREFIX%deal_msg_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dest` varchar(255) NOT NULL,
  `send_type` tinyint(1) NOT NULL,
  `content` text NOT NULL,
  `send_time` int(11) NOT NULL,
  `is_send` tinyint(1) NOT NULL,
  `create_time` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `result` text NOT NULL,
  `is_success` tinyint(1) NOT NULL,
  `is_html` tinyint(1) NOT NULL,
  `title` text NOT NULL,
  `is_youhui` tinyint(1) NOT NULL,
  `youhui_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx0` (`id`,`is_send`,`send_type`)
) ENGINE=InnoDB AUTO_INCREMENT=4720 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%deal_msg_list` VALUES ('4718','13788888888','0','你的手机号为13788888888,验证码为179307','0','0','1455434524','0','','0','0','你的手机号为13788888888,验证码为179307','0','0');
DROP TABLE IF EXISTS `%DB_PREFIX%deal_msgboard`;
CREATE TABLE `%DB_PREFIX%deal_msgboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL COMMENT '�û���',
  `ID_NO` varchar(255) NOT NULL COMMENT '���֤��',
  `mobile` varchar(20) NOT NULL COMMENT '�绰',
  `money` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '�����',
  `time_limit` int(11) NOT NULL COMMENT '�������',
  `usefulness` varchar(255) NOT NULL COMMENT '�����;',
  `create_time` datetime NOT NULL COMMENT '����ʱ��',
  `status` tinyint(2) unsigned zerofill NOT NULL COMMENT '״̬:0δ���� 1�Ѵ��� 2�Ѿܾ�',
  `unit` tinyint(2) NOT NULL COMMENT '��λ:0:�� 1:��',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `%DB_PREFIX%deal_order`;
CREATE TABLE `%DB_PREFIX%deal_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `user_id` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `pay_status` tinyint(1) NOT NULL,
  `total_price` decimal(20,2) NOT NULL,
  `pay_amount` decimal(20,2) NOT NULL,
  `delivery_status` tinyint(1) NOT NULL,
  `order_status` tinyint(1) NOT NULL,
  `is_delete` tinyint(1) NOT NULL,
  `return_total_score` int(11) NOT NULL,
  `refund_amount` decimal(20,2) NOT NULL COMMENT '已退款总额',
  `admin_memo` text NOT NULL,
  `memo` text NOT NULL,
  `region_lv1` int(11) NOT NULL,
  `region_lv2` int(11) NOT NULL,
  `region_lv3` int(11) NOT NULL,
  `region_lv4` int(11) NOT NULL,
  `address` text NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `consignee` varchar(255) NOT NULL,
  `deal_total_price` decimal(20,2) NOT NULL,
  `discount_price` decimal(20,2) NOT NULL,
  `delivery_fee` decimal(20,2) NOT NULL,
  `ecv_money` decimal(20,2) NOT NULL,
  `account_money` decimal(20,2) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `payment_fee` decimal(20,2) NOT NULL,
  `return_total_money` decimal(20,2) NOT NULL,
  `extra_status` tinyint(1) NOT NULL,
  `after_sale` tinyint(1) NOT NULL,
  `refund_money` decimal(20,2) NOT NULL COMMENT '弃用',
  `bank_id` varchar(255) NOT NULL,
  `referer` varchar(255) NOT NULL,
  `deal_ids` varchar(255) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `refund_status` tinyint(1) NOT NULL COMMENT '0:不需退款 1:有退款申请 2:已处理',
  `retake_status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_sn` (`order_sn`),
  FULLTEXT KEY `deal_ids` (`deal_ids`)
) ENGINE=InnoDB AUTO_INCREMENT=958 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%deal_order_item`;
CREATE TABLE `%DB_PREFIX%deal_order_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `unit_price` decimal(20,4) NOT NULL COMMENT '单价',
  `total_price` decimal(20,4) NOT NULL COMMENT '总价',
  `delivery_status` tinyint(1) NOT NULL,
  `name` text NOT NULL,
  `return_score` int(11) NOT NULL,
  `return_total_score` int(11) NOT NULL,
  `attr` varchar(255) NOT NULL,
  `verify_code` varchar(255) NOT NULL,
  `order_id` int(11) NOT NULL,
  `return_money` decimal(20,4) NOT NULL COMMENT '返现的单价',
  `return_total_money` decimal(20,4) NOT NULL,
  `buy_type` tinyint(1) NOT NULL,
  `sub_name` varchar(255) NOT NULL,
  `attr_str` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%deal_order_log`;
CREATE TABLE `%DB_PREFIX%deal_order_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_info` text NOT NULL,
  `log_time` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%deal_payment`;
CREATE TABLE `%DB_PREFIX%deal_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%deal_quota_submit`;
CREATE TABLE `%DB_PREFIX%deal_quota_submit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `titlecolor` varchar(10) NOT NULL COMMENT '标题颜色',
  `name` varchar(255) NOT NULL COMMENT '贷款标题',
  `sub_name` varchar(255) NOT NULL COMMENT '短名称',
  `view_info` text NOT NULL COMMENT '资料展示',
  `citys` text NOT NULL COMMENT '城市（序列化）',
  `cate_id` int(11) NOT NULL COMMENT '贷款分类',
  `agency_id` int(11) NOT NULL COMMENT '担保机构',
  `warrant` tinyint(4) NOT NULL,
  `guarantor_margin_amt` decimal(20,2) NOT NULL COMMENT '担保保证金',
  `guarantor_amt` decimal(20,2) NOT NULL COMMENT '担保金额',
  `guarantor_pro_fit_amt` decimal(20,2) NOT NULL COMMENT '担保收益',
  `icon` varchar(255) NOT NULL COMMENT '图标',
  `type_id` int(11) NOT NULL COMMENT '借款用途',
  `borrow_amount` decimal(20,2) NOT NULL COMMENT '申请额度',
  `guarantees_amt` decimal(20,2) NOT NULL COMMENT '借款保证金',
  `rate` decimal(10,2) NOT NULL COMMENT '借款利率',
  `services_fee` varchar(20) NOT NULL COMMENT '成交服务费',
  `manage_fee` varchar(20) NOT NULL COMMENT '借款者管理费',
  `user_loan_manage_fee` varchar(20) NOT NULL COMMENT '投资者管理费',
  `manage_impose_fee_day1` varchar(20) NOT NULL COMMENT '普通逾期管理费',
  `manage_impose_fee_day2` varchar(20) NOT NULL COMMENT '逾期管理费总额',
  `impose_fee_day1` varchar(20) NOT NULL COMMENT '普通逾期罚息',
  `impose_fee_day2` varchar(20) NOT NULL COMMENT '严重逾期罚息',
  `user_load_transfer_fee` varchar(20) NOT NULL COMMENT '债权转让管理费',
  `user_bid_rebate` varchar(20) NOT NULL COMMENT '投资人返利',
  `compensate_fee` varchar(20) NOT NULL COMMENT '提前还款补偿',
  `generation_position` varchar(20) NOT NULL COMMENT '申请延期比率',
  `description` text NOT NULL COMMENT '借款内容',
  `risk_rank` tinyint(4) NOT NULL COMMENT '风险等级',
  `risk_security` text NOT NULL COMMENT '风险控制',
  `is_effect` tinyint(1) NOT NULL COMMENT '默认是否有效',
  `status` tinyint(4) NOT NULL COMMENT '0待处理，1审核通过，2审核失败',
  `op_memo` text NOT NULL COMMENT '操作备注',
  `bad_msg` text NOT NULL COMMENT '失败原因',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  `update_time` int(11) NOT NULL COMMENT '处理时间',
  `attachment` text NOT NULL COMMENT '合同附件',
  `tattachment` text NOT NULL COMMENT '转让合同附件',
  `use_ecv` tinyint(4) NOT NULL COMMENT '是否使用红包',
  PRIMARY KEY (`id`),
  KEY `idx0` (`user_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='授信额度申请';
DROP TABLE IF EXISTS `%DB_PREFIX%deal_repay`;
CREATE TABLE `%DB_PREFIX%deal_repay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `repay_money` decimal(20,2) NOT NULL COMMENT '还款金额',
  `manage_money` decimal(20,2) NOT NULL COMMENT '管理费',
  `impose_money` decimal(20,2) NOT NULL COMMENT '罚息',
  `repay_time` int(11) NOT NULL,
  `true_repay_time` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0提前,1准时还款，2逾期还款 3严重逾期  前台在这基础上+1',
  `l_key` int(11) NOT NULL DEFAULT '0' COMMENT '还款顺序 0 开始',
  `has_repay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未还,1已还 2部分还款',
  `manage_impose_money` decimal(20,2) NOT NULL COMMENT '逾期管理费',
  `is_site_bad` tinyint(1) NOT NULL COMMENT '是否坏账  0不是，1坏账 管理员看到的',
  `repay_date` date NOT NULL COMMENT '预期还款日期,日期格式方便统计',
  `true_repay_date` date NOT NULL COMMENT '实际还款日期,日期格式方便统计',
  `true_repay_money` double(20,2) NOT NULL DEFAULT '0.00' COMMENT '实还金额',
  `true_self_money` decimal(20,2) NOT NULL COMMENT '实际还款本金',
  `interest_money` decimal(20,2) NOT NULL COMMENT '待还利息   repay_money - self_money',
  `true_interest_money` decimal(20,2) NOT NULL COMMENT '实际还利息',
  `true_manage_money` decimal(20,2) NOT NULL COMMENT '实际管理费',
  `self_money` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '需还本金',
  `loantype` int(11) NOT NULL COMMENT '还款方式',
  `manage_money_rebate` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'Ԥ���յ��ģ�����ѷ�Ӷ,����ſ�ʱ����',
  `true_manage_money_rebate` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'ʵ���յ��ģ�����ѷ�Ӷ,ÿ�ڻ���ʱ����',
  `get_manage` tinyint(1) NOT NULL COMMENT '是否已收取管理费',
  `mortgage_fee` decimal(20,2) NOT NULL COMMENT '抵押物管理费',
  `true_mortgage_fee` decimal(20,2) NOT NULL COMMENT '抵押物管理费',
  PRIMARY KEY (`id`),
  KEY `idx_0` (`user_id`,`status`),
  KEY `l_key` (`l_key`),
  KEY `idx_1` (`deal_id`,`l_key`),
  KEY `idx_dr_001` (`true_repay_date`),
  KEY `idx_dr_002` (`repay_date`)
) ENGINE=InnoDB AUTO_INCREMENT=733 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%deal_repay_log`;
CREATE TABLE `%DB_PREFIX%deal_repay_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `repay_id` int(11) NOT NULL COMMENT '账单ID',
  `log` text NOT NULL COMMENT '日志',
  `adm_id` int(11) NOT NULL COMMENT '操作管理员',
  `user_id` int(11) NOT NULL COMMENT '操作用户',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=293 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%debit_conf`;
CREATE TABLE `%DB_PREFIX%debit_conf` (
  `borrow_amount_cfg` text NOT NULL COMMENT '借款金额 序列化',
  `loantype` int(11) NOT NULL COMMENT '还款类型',
  `services_fee` varchar(20) NOT NULL COMMENT '服务费率',
  `manage_fee` varchar(20) NOT NULL COMMENT '借款者管理费',
  `manage_impose_fee_day1` varchar(20) NOT NULL COMMENT '普通逾期管理费',
  `manage_impose_fee_day2` varchar(20) NOT NULL COMMENT '严重逾期管理费',
  `impose_fee_day1` varchar(20) NOT NULL COMMENT '普通逾期费率',
  `impose_fee_day2` varchar(20) NOT NULL COMMENT '严重逾期费率',
  `rate_cfg` tinyint(1) NOT NULL COMMENT '利率 0取最低 1取中间值 2取最高',
  `enddate` int(11) NOT NULL COMMENT '筹标日期'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%debit_conf` VALUES ('a:1:{i:0;s:4:\"1000\";}','0','10','10','10','10','0.1','0.1','0','20');
DROP TABLE IF EXISTS `%DB_PREFIX%demotion_record`;
CREATE TABLE `%DB_PREFIX%demotion_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '会员ID',
  `original_vip_id` int(11) NOT NULL COMMENT 'VIP原等级ID',
  `now_vip_id` int(11) NOT NULL COMMENT '降级后等级ID',
  `causes` varchar(255) NOT NULL COMMENT '降级原因',
  `start_date` date NOT NULL COMMENT '降级开始日期',
  `eresume_date` date NOT NULL COMMENT '预计恢复日期',
  `arecovery_date` date NOT NULL COMMENT '实际恢复日期',
  `status` tinyint(1) NOT NULL COMMENT '状态(0降级、1已恢复等级)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='VIP降级记录表';
DROP TABLE IF EXISTS `%DB_PREFIX%ecv`;
CREATE TABLE `%DB_PREFIX%ecv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sn` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `use_limit` int(11) NOT NULL,
  `use_count` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `begin_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `money` decimal(20,2) NOT NULL,
  `ecv_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unk_sn` (`sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='优惠券列表';
DROP TABLE IF EXISTS `%DB_PREFIX%ecv_type`;
CREATE TABLE `%DB_PREFIX%ecv_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `money` decimal(20,2) NOT NULL,
  `use_limit` int(11) NOT NULL,
  `begin_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `gen_count` int(11) NOT NULL,
  `send_type` tinyint(1) NOT NULL COMMENT '发放方式 0:管理员手动发放 1:会员积分兑换 2:序列号兑换',
  `exchange_score` int(11) NOT NULL,
  `exchange_limit` int(11) NOT NULL,
  `exchange_sn` varchar(20) DEFAULT NULL COMMENT '红包兑换的序列号',
  `use_type` tinyint(1) NOT NULL COMMENT '使用限制 0不限 1 pc端 2 手机端',
  PRIMARY KEY (`id`),
  UNIQUE KEY `exchange_sn` (`exchange_sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='优惠券类型';
DROP TABLE IF EXISTS `%DB_PREFIX%email_verify_code`;
CREATE TABLE `%DB_PREFIX%email_verify_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `verify_code` varchar(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `create_time` int(11) NOT NULL,
  `client_ip` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('1','542292','re788@qq.com','1425509649','27.151.63.196');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('2','773913','lym99@qq.com','1425682562','27.151.64.158');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('3','319088','1248745@qq.com','1426010304','27.151.61.54');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('4','168726','fdfd56@qq.com','1426010368','110.80.172.34');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('5','617683','12fd3456@qq.com','1426010599','110.80.172.34');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('6','584122','fdfds715@qq.com','1426025433','110.80.172.34');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('7','863661','adb@a.com','1426026172','27.151.61.54');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('8','379341','bad@d.com','1426026489','27.151.61.54');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('9','641646','12fdfdas45@qq.com','1426026699','110.80.172.34');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('10','871135','fder4356@qq.com','1426028098','27.151.61.54');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('11','900796','fdgr456@qq.com','1426032409','110.80.172.34');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('12','299924','382272420@qq.com','1426099003','110.87.47.136');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('13','418481','aa@aa.com','1426131144','110.87.47.136');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('14','805638','98878@qq.com','1426532606','27.151.60.169');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('15','687221','bb@bb.com','1426727761','27.151.100.218');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('16','590544','re88898@qq.com','1426786145','27.151.61.236');
INSERT INTO `%DB_PREFIX%email_verify_code` VALUES ('17','417170','','1453172855','66.249.66.132');
DROP TABLE IF EXISTS `%DB_PREFIX%expression`;
CREATE TABLE `%DB_PREFIX%expression` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'tusiji',
  `emotion` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%expression` VALUES ('19','傲慢','qq','[傲慢]','aoman.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('20','白眼','qq','[白眼]','baiyan.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('21','鄙视','qq','[鄙视]','bishi.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('22','闭嘴','qq','[闭嘴]','bizui.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('23','擦汗','qq','[擦汗]','cahan.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('24','菜刀','qq','[菜刀]','caidao.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('25','差劲','qq','[差劲]','chajin.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('26','欢庆','qq','[欢庆]','cheer.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('27','虫子','qq','[虫子]','chong.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('28','呲牙','qq','[呲牙]','ciya.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('29','捶打','qq','[捶打]','da.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('30','大便','qq','[大便]','dabian.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('31','大兵','qq','[大兵]','dabing.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('32','大叫','qq','[大叫]','dajiao.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('33','大哭','qq','[大哭]','daku.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('34','蛋糕','qq','[蛋糕]','dangao.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('35','发怒','qq','[发怒]','fanu.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('36','刀','qq','[刀]','dao.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('37','得意','qq','[得意]','deyi.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('38','凋谢','qq','[凋谢]','diaoxie.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('39','饿','qq','[饿]','er.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('40','发呆','qq','[发呆]','fadai.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('41','发抖','qq','[发抖]','fadou.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('42','饭','qq','[饭]','fan.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('43','飞吻','qq','[飞吻]','feiwen.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('44','奋斗','qq','[奋斗]','fendou.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('45','尴尬','qq','[尴尬]','gangga.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('46','给力','qq','[给力]','geili.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('47','勾引','qq','[勾引]','gouyin.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('48','鼓掌','qq','[鼓掌]','guzhang.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('49','哈哈','qq','[哈哈]','haha.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('50','害羞','qq','[害羞]','haixiu.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('51','哈欠','qq','[哈欠]','haqian.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('52','花','qq','[花]','hua.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('53','坏笑','qq','[坏笑]','huaixiao.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('54','挥手','qq','[挥手]','huishou.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('55','回头','qq','[回头]','huitou.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('56','激动','qq','[激动]','jidong.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('57','惊恐','qq','[惊恐]','jingkong.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('58','惊讶','qq','[惊讶]','jingya.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('59','咖啡','qq','[咖啡]','kafei.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('60','可爱','qq','[可爱]','keai.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('61','可怜','qq','[可怜]','kelian.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('62','磕头','qq','[磕头]','ketou.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('63','示爱','qq','[示爱]','kiss.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('64','酷','qq','[酷]','ku.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('65','难过','qq','[难过]','kuaikule.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('66','骷髅','qq','[骷髅]','kulou.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('67','困','qq','[困]','kun.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('68','篮球','qq','[篮球]','lanqiu.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('69','冷汗','qq','[冷汗]','lenghan.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('70','流汗','qq','[流汗]','liuhan.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('71','流泪','qq','[流泪]','liulei.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('72','礼物','qq','[礼物]','liwu.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('73','爱心','qq','[爱心]','love.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('74','骂人','qq','[骂人]','ma.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('75','不开心','qq','[不开心]','nanguo.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('76','不好','qq','[不好]','no.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('77','很好','qq','[很好]','ok.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('78','佩服','qq','[佩服]','peifu.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('79','啤酒','qq','[啤酒]','pijiu.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('80','乒乓','qq','[乒乓]','pingpang.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('81','撇嘴','qq','[撇嘴]','pizui.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('82','强','qq','[强]','qiang.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('83','亲亲','qq','[亲亲]','qinqin.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('84','出丑','qq','[出丑]','qioudale.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('85','足球','qq','[足球]','qiu.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('86','拳头','qq','[拳头]','quantou.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('87','弱','qq','[弱]','ruo.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('88','色','qq','[色]','se.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('89','闪电','qq','[闪电]','shandian.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('90','胜利','qq','[胜利]','shengli.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('91','衰','qq','[衰]','shuai.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('92','睡觉','qq','[睡觉]','shuijiao.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('93','太阳','qq','[太阳]','taiyang.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('96','啊','tusiji','[啊]','aa.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('97','暗爽','tusiji','[暗爽]','anshuang.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('98','byebye','tusiji','[byebye]','baibai.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('99','不行','tusiji','[不行]','buxing.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('100','戳眼','tusiji','[戳眼]','chuoyan.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('101','很得意','tusiji','[很得意]','deyi.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('102','顶','tusiji','[顶]','ding.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('103','抖抖','tusiji','[抖抖]','douxiong.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('104','哼','tusiji','[哼]','heng.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('105','挥汗','tusiji','[挥汗]','huihan.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('106','昏迷','tusiji','[昏迷]','hunmi.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('107','互拍','tusiji','[互拍]','hupai.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('108','瞌睡','tusiji','[瞌睡]','keshui.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('109','笼子','tusiji','[笼子]','longzi.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('110','听歌','tusiji','[听歌]','music.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('111','奶瓶','tusiji','[奶瓶]','naiping.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('112','扭背','tusiji','[扭背]','niubei.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('113','拍砖','tusiji','[拍砖]','paizhuan.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('114','飘过','tusiji','[飘过]','piaoguo.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('115','揉脸','tusiji','[揉脸]','roulian.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('116','闪闪','tusiji','[闪闪]','shanshan.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('117','生日','tusiji','[生日]','shengri.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('118','摊手','tusiji','[摊手]','tanshou.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('119','躺坐','tusiji','[躺坐]','tanzuo.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('120','歪头','tusiji','[歪头]','waitou.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('121','我踢','tusiji','[我踢]','woti.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('122','无聊','tusiji','[无聊]','wuliao.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('123','醒醒','tusiji','[醒醒]','xingxing.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('124','睡了','tusiji','[睡了]','xixishui.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('125','旋转','tusiji','[旋转]','xuanzhuan.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('126','摇晃','tusiji','[摇晃]','yaohuang.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('127','耶','tusiji','[耶]','yeah.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('128','郁闷','tusiji','[郁闷]','yumen.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('129','晕厥','tusiji','[晕厥]','yunjue.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('130','砸','tusiji','[砸]','za.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('131','震荡','tusiji','[震荡]','zhendang.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('132','撞墙','tusiji','[撞墙]','zhuangqiang.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('133','转头','tusiji','[转头]','zhuantou.gif');
INSERT INTO `%DB_PREFIX%expression` VALUES ('134','抓墙','tusiji','[抓墙]','zhuaqiang.gif');
DROP TABLE IF EXISTS `%DB_PREFIX%generation_repay`;
CREATE TABLE `%DB_PREFIX%generation_repay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL,
  `repay_id` int(11) NOT NULL COMMENT '第几期',
  `admin_id` int(11) NOT NULL COMMENT '管理员ID',
  `agency_id` int(11) NOT NULL COMMENT '担保机构ID',
  `self_money` decimal(20,2) NOT NULL,
  `interest_money` decimal(20,2) NOT NULL,
  `repay_money` decimal(20,2) NOT NULL COMMENT '代还多少本息',
  `manage_money` decimal(20,2) NOT NULL COMMENT '代换多少管理费',
  `mortgage_fee` decimal(20,2) NOT NULL COMMENT '代换多少抵押物管理费',
  `impose_money` decimal(20,2) NOT NULL COMMENT '代还多少罚息',
  `manage_impose_money` decimal(20,2) NOT NULL COMMENT '代换多少逾期管理费',
  `total_money_fee` decimal(20,2) NOT NULL COMMENT '�渶��Ϣ',
  `fee_day` int(11) NOT NULL COMMENT '�渶����',
  `create_time` int(11) NOT NULL COMMENT '代还时间',
  `create_date` date NOT NULL,
  `status` tinyint(4) NOT NULL COMMENT '0待收款 1已收款 ',
  `memo` text NOT NULL COMMENT '操作备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='代还款记录';
DROP TABLE IF EXISTS `%DB_PREFIX%generation_repay_submit`;
CREATE TABLE `%DB_PREFIX%generation_repay_submit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '代还多少本息',
  `money` decimal(20,2) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0 未处理 1续约成功 2续约失败',
  `memo` text NOT NULL,
  `op_memo` text NOT NULL COMMENT '操作备注',
  `create_time` int(11) NOT NULL COMMENT '代还时间',
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='代还款申请';
DROP TABLE IF EXISTS `%DB_PREFIX%gift_record`;
CREATE TABLE `%DB_PREFIX%gift_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL COMMENT '借款ID',
  `load_id` int(11) NOT NULL COMMENT '投标记录ID',
  `reward_name` varchar(100) NOT NULL COMMENT '奖励名称',
  `user_id` int(11) NOT NULL COMMENT '所属人ID',
  `gift_type` tinyint(1) NOT NULL COMMENT '收益类型1.红包、2.收益率、3.积分、4.礼品',
  `gift_value` varchar(100) NOT NULL COMMENT '收益值 红包为金额、收益率为百分比、积分为数值、礼品为礼品ID ',
  `status` tinyint(1) NOT NULL COMMENT '发放状态 0未发放，1已发放',
  `generation_date` date NOT NULL COMMENT '生成时间',
  `release_date` date NOT NULL COMMENT '发放时间',
  `reward_money` decimal(20,2) NOT NULL COMMENT '实际获得金额',
  PRIMARY KEY (`id`),
  KEY `idx_gr_001` (`user_id`,`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='投资奖励记录表';
DROP TABLE IF EXISTS `%DB_PREFIX%given_record`;
CREATE TABLE `%DB_PREFIX%given_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '会员ID',
  `vip_id` int(11) NOT NULL COMMENT 'VIP等级ID',
  `given_name_type` tinyint(1) NOT NULL COMMENT '礼品名称类型 1.生日礼品 2.节日礼品',
  `given_type` tinyint(1) NOT NULL COMMENT '礼品类型 1.红包 2.积分',
  `given_value` int(11) NOT NULL COMMENT '礼品值',
  `given_num` int(11) NOT NULL COMMENT '数量',
  `gift_id` int(11) NOT NULL COMMENT '节日礼品ID',
  `brief` text NOT NULL COMMENT '备注',
  `send_date` date NOT NULL COMMENT '发送日期',
  `send_state` tinyint(1) NOT NULL COMMENT '发送状态 0未发送 1已发送',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=229 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='礼金记录列表';
DROP TABLE IF EXISTS `%DB_PREFIX%goods`;
CREATE TABLE `%DB_PREFIX%goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '商品名称',
  `sub_name` varchar(255) NOT NULL COMMENT '商品简称',
  `cate_id` int(11) NOT NULL COMMENT '分类ID',
  `img` text NOT NULL COMMENT '商品主图',
  `brief` text NOT NULL COMMENT '商品简介',
  `description` text NOT NULL COMMENT '商品描述',
  `sort` int(11) NOT NULL COMMENT '排序',
  `max_bought` int(11) NOT NULL COMMENT '库存数',
  `user_max_bought` int(11) NOT NULL COMMENT '会员最大购买量按件',
  `score` int(11) NOT NULL COMMENT '购买所需积分',
  `is_delivery` tinyint(1) NOT NULL COMMENT '	是否需要配送；0：否; 1：是',
  `is_hot` tinyint(1) NOT NULL COMMENT '热卖',
  `is_new` tinyint(1) NOT NULL COMMENT '最新',
  `is_recommend` tinyint(1) NOT NULL COMMENT '是否推荐',
  `seo_title` text NOT NULL COMMENT 'SEO自定义标题',
  `seo_keyword` text NOT NULL COMMENT 'SEO自定义关键词',
  `seo_description` text NOT NULL COMMENT 'SEO自定义描述',
  `goods_type_id` int(11) NOT NULL COMMENT '商品属性',
  `invented_number` int(11) NOT NULL COMMENT '虚拟购买数',
  `buy_number` int(11) NOT NULL COMMENT '购买人数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%goods_attr`;
CREATE TABLE `%DB_PREFIX%goods_attr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `goods_type_attr_id` int(11) NOT NULL,
  `score` decimal(20,4) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `is_checked` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `goods_type_attr_id` (`goods_type_attr_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%goods_attr_stock`;
CREATE TABLE `%DB_PREFIX%goods_attr_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `attr_cfg` text NOT NULL,
  `stock_cfg` int(11) NOT NULL,
  `attr_str` text NOT NULL,
  `buy_count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%goods_cate`;
CREATE TABLE `%DB_PREFIX%goods_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '商品分类名称',
  `is_effect` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%goods_cate` VALUES ('1','差旅休闲','1','0','0','3');
INSERT INTO `%DB_PREFIX%goods_cate` VALUES ('2','质尚家居','1','0','0','4');
INSERT INTO `%DB_PREFIX%goods_cate` VALUES ('3','时尚电器','1','0','0','5');
INSERT INTO `%DB_PREFIX%goods_cate` VALUES ('4','精选卡券','1','0','0','1');
INSERT INTO `%DB_PREFIX%goods_cate` VALUES ('5','幸福童年','1','0','0','2');
DROP TABLE IF EXISTS `%DB_PREFIX%goods_order`;
CREATE TABLE `%DB_PREFIX%goods_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(255) NOT NULL COMMENT '订单号',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `goods_name` varchar(255) NOT NULL COMMENT '商品名称',
  `score` int(11) NOT NULL COMMENT ' 所需积分',
  `total_score` int(11) NOT NULL COMMENT ' 所需积分',
  `number` int(11) NOT NULL DEFAULT '1' COMMENT '数量',
  `delivery_sn` varchar(255) NOT NULL COMMENT '快递单号',
  `order_status` tinyint(1) NOT NULL COMMENT '订单状态',
  `user_id` int(11) NOT NULL COMMENT ' 会员ID',
  `ex_time` int(11) NOT NULL COMMENT '兑换时间',
  `delivery_time` int(11) NOT NULL COMMENT '发货时间',
  `delivery_addr` varchar(255) NOT NULL COMMENT '收货地址',
  `delivery_tel` varchar(255) NOT NULL COMMENT '收货电话',
  `delivery_name` varchar(255) NOT NULL COMMENT '收货名称',
  `is_delivery` tinyint(1) NOT NULL,
  `ex_date` date NOT NULL COMMENT '兑换时间YMD',
  `delivery_date` date NOT NULL COMMENT '发货时间Ymd',
  `attr_stock_id` int(11) NOT NULL COMMENT '订单库存属性id',
  `attr` text NOT NULL,
  `delivery_express` varchar(255) NOT NULL COMMENT '快递公司',
  `memo` text NOT NULL COMMENT '用户留言',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=220 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%goods_type`;
CREATE TABLE `%DB_PREFIX%goods_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '商品类型名',
  `is_effect` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%goods_type` VALUES ('1','手机','1');
INSERT INTO `%DB_PREFIX%goods_type` VALUES ('2','服装','1');
INSERT INTO `%DB_PREFIX%goods_type` VALUES ('3','摆件','1');
INSERT INTO `%DB_PREFIX%goods_type` VALUES ('4','儿童车','1');
DROP TABLE IF EXISTS `%DB_PREFIX%goods_type_attr`;
CREATE TABLE `%DB_PREFIX%goods_type_attr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '商品属性名',
  `input_type` tinyint(1) NOT NULL,
  `preset_value` text NOT NULL,
  `goods_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%goods_type_attr` VALUES ('1','尺码','0','','2');
INSERT INTO `%DB_PREFIX%goods_type_attr` VALUES ('2','颜色','0','','2');
INSERT INTO `%DB_PREFIX%goods_type_attr` VALUES ('3','型号','0','','1');
INSERT INTO `%DB_PREFIX%goods_type_attr` VALUES ('4','颜色','0','','1');
INSERT INTO `%DB_PREFIX%goods_type_attr` VALUES ('5','配件','0','','1');
INSERT INTO `%DB_PREFIX%goods_type_attr` VALUES ('9','颜色','0','','4');
DROP TABLE IF EXISTS `%DB_PREFIX%idcard`;
CREATE TABLE `%DB_PREFIX%idcard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) NOT NULL COMMENT '支付接口类名',
  `is_effect` tinyint(1) NOT NULL COMMENT '有效性标识',
  `name` varchar(255) NOT NULL COMMENT '接口名称',
  `description` text NOT NULL COMMENT '描述',
  `config` text NOT NULL COMMENT '序列号后的配置信息',
  `logo` varchar(255) NOT NULL COMMENT '显示的图标',
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='支付接口表';
