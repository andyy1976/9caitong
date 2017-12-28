-- fanwe SQL Dump Program
-- nginx/1.4.4
-- 
-- DATE : 2016-07-21 18:57:35
-- MYSQL SERVER VERSION : 5.6.29
-- PHP VERSION : fpm-fcgi
-- Vol : 6


DROP TABLE IF EXISTS `%DB_PREFIX%user_sign_log`;
CREATE TABLE `%DB_PREFIX%user_sign_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `sign_date` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%user_sign_log` VALUES ('237','1','1469069837');
DROP TABLE IF EXISTS `%DB_PREFIX%user_sta`;
CREATE TABLE `%DB_PREFIX%user_sta` (
  `user_id` int(11) NOT NULL,
  `dp_count` int(11) NOT NULL COMMENT '留言数',
  `borrow_amount` decimal(20,4) NOT NULL COMMENT '总的借款数',
  `repay_amount` decimal(20,4) NOT NULL COMMENT '已还本息',
  `need_repay_amount` decimal(20,4) NOT NULL COMMENT '待还本息',
  `need_manage_amount` decimal(20,4) NOT NULL COMMENT '待还管理费',
  `avg_rate` float(10,2) NOT NULL COMMENT '平均借款利率',
  `avg_borrow_amount` decimal(20,4) NOT NULL COMMENT '平均每笔借款金额',
  `deal_count` int(11) NOT NULL COMMENT '总借入笔数',
  `success_deal_count` int(11) NOT NULL COMMENT '成功借款',
  `repay_deal_count` int(11) NOT NULL COMMENT '还清笔数',
  `tq_repay_deal_count` int(11) NOT NULL COMMENT '提前还清',
  `zc_repay_deal_count` int(11) NOT NULL COMMENT '正常还清',
  `wh_repay_deal_count` int(11) NOT NULL COMMENT '未还清',
  `yuqi_count` int(11) NOT NULL COMMENT '逾期次数',
  `yz_yuqi_count` int(11) NOT NULL COMMENT '严重逾期次数',
  `yuqi_amount` decimal(20,4) NOT NULL COMMENT '逾期本息',
  `yuqi_impose` decimal(20,4) NOT NULL COMMENT '逾期费用',
  `load_earnings` decimal(20,4) NOT NULL COMMENT '已赚利息',
  `load_tq_impose` decimal(20,4) NOT NULL COMMENT '提前还款违约金',
  `load_yq_impose` decimal(20,4) NOT NULL COMMENT '逾期还款违约金',
  `load_avg_rate` float(10,2) NOT NULL COMMENT '借出加权平均收益率',
  `load_count` int(11) NOT NULL COMMENT '总借出笔数',
  `load_money` decimal(20,4) NOT NULL COMMENT '总的借出金额',
  `load_repay_money` decimal(20,4) NOT NULL COMMENT '已回收本息',
  `load_wait_repay_money` decimal(20,4) NOT NULL COMMENT '待回收本息',
  `reback_load_count` int(11) NOT NULL COMMENT '收回的借出笔数',
  `wait_reback_load_count` int(11) NOT NULL COMMENT '未收回的借出笔数',
  `load_wait_earnings` decimal(20,2) NOT NULL COMMENT '待回收利息',
  `bad_count` int(11) NOT NULL COMMENT '坏账数量',
  `rebate_money` int(11) NOT NULL COMMENT '�������',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%user_sta` VALUES ('1','0','0.0000','0.0000','0.0000','0.0000','0.00','0.0000','1','0','0','0','0','0','0','0','0.0000','0.0000','0.0000','0.0000','0.0000','0.00','0','0.0000','0.0000','0.0000','0','0','0.00','0','0');
DROP TABLE IF EXISTS `%DB_PREFIX%user_work`;
CREATE TABLE `%DB_PREFIX%user_work` (
  `user_id` int(11) NOT NULL,
  `office` varchar(100) NOT NULL,
  `jobtype` varchar(50) NOT NULL,
  `province_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `officetype` varchar(50) NOT NULL,
  `officedomain` varchar(50) NOT NULL,
  `officecale` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `salary` varchar(50) NOT NULL,
  `workyears` varchar(50) NOT NULL,
  `workphone` varchar(20) NOT NULL,
  `workemail` varchar(50) NOT NULL,
  `officeaddress` varchar(100) NOT NULL,
  `urgentcontact` varchar(20) NOT NULL,
  `urgentrelation` varchar(20) NOT NULL,
  `urgentmobile` varchar(20) NOT NULL,
  `urgentcontact2` varchar(20) NOT NULL,
  `urgentrelation2` varchar(20) NOT NULL,
  `urgentmobile2` varchar(20) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%user_x_y_point`;
CREATE TABLE `%DB_PREFIX%user_x_y_point` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `xpoint` float(14,6) NOT NULL DEFAULT '0.000000',
  `ypoint` float(14,6) NOT NULL DEFAULT '0.000000',
  `locate_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%vip_buy_log`;
CREATE TABLE `%DB_PREFIX%vip_buy_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vip_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vip_buytime` int(11) NOT NULL COMMENT '购买时间',
  `buy_limit` int(11) NOT NULL COMMENT '购买期限',
  `vip_end_time` int(11) NOT NULL COMMENT '到期时间',
  `buy_fee` decimal(20,2) NOT NULL COMMENT '购买价格',
  `buy_type` tinyint(1) NOT NULL COMMENT '0 VIP购买，1 管理员变更',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='VIP购买日志表';
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('1','2','440124','1436465888','1','1468001888','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('2','2','440090','1436467064','1','1468003064','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('3','2','440090','1436467079','1','1499539064','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('4','2','440090','1436467265','1','1531075064','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('5','3','440090','1436467477','1','1468003477','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('6','4','440090','1436467544','1','1468003544','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('7','2','440090','1436467678','1','1468003678','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('8','2','440090','1436468261','4','1594147678','320.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('9','5','440090','1436468402','5','1594148402','2000.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('10','2','440094','1436469098','5','1594149098','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('11','4','440094','1436469214','1','1468005214','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('12','2','440093','1436480253','2','1499552253','160.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('13','2','440098','1436485325','2','1499557325','160.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('14','3','440093','1436486800','1','1468022800','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('15','2','440115','1436493578','2','1499565578','160.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('16','2','440121','1436724350','1','1468260350','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('17','2','440123','1436724508','1','1468260508','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('18','2','440031','1436894567','5','1594574567','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('19','4','440031','1436894627','1','1468430627','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('20','5','440031','1436894641','1','1468430641','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('21','3','440031','1436912655','2','1499984655','300.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('22','3','440098','1437345938','1','1468881938','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('23','4','440093','1437348770','1','1468884770','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('24','5','440094','1437524748','1','1469060748','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('25','2','440148','1437525114','2','1500597114','160.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('26','2','440137','1437525240','1','1469061240','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('27','2','440145','1437586846','1','1469122846','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('28','3','440148','1437589050','1','1469125050','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('29','2','440157','1437672664','1','1469208664','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('30','3','440029','1437678177','1','1469214177','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('31','3','440029','1437678230','1','1500750177','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('32','3','440029','1437678232','1','1532286177','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('33','3','440029','1437678586','1','1563822177','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('34','4','440029','1437678899','1','1469214899','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('35','2','440158','1437696762','1','1469232762','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('36','3','440121','1438044043','1','1469580043','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('37','5','440093','1438044069','1','1469580069','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('38','4','440098','1438044136','1','1469580136','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('39','2','440119','1438044348','3','1532652348','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('40','3','440123','1438102305','1','1469638305','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('41','4','440121','1438125434','1','1469661434','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('42','4','440123','1438125594','1','1469661594','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('43','2','440179','1438190622','1','1469726622','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('44','3','440179','1438191627','1','1469727627','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('45','2','440129','1438194992','1','1469730992','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('46','3','440129','1438195054','1','1469731054','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('47','2','440180','1438196448','1','1469732448','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('48','2','440128','1438208210','1','1469744210','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('49','3','440180','1438211049','1','1469747049','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('50','2','440183','1438216188','1','1469752188','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('51','2','440185','1438218418','1','1469754418','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('52','2','440186','1438274135','1','1469810135','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('53','2','440190','1438277947','1','1469813947','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('54','2','440152','1438281253','1','1469817253','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('55','3','440029','1438535776','1','1470071776','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('56','3','440119','1439402353','1','1470938353','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('57','4','440017','1442879597','2','1505951597','480.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('58','2','440036','1446054483','1','1477590483','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('59','3','440020','1446059868','1','1477595868','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('60','4','440129','1446403042','1','1477939042','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('61','5','440129','1446403053','1','1477939053','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('62','2','440225','1447002125','1','1478538125','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('63','3','440225','1447367908','1','1478903908','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('64','5','440226','1447636347','1','1479172347','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('65','4','440225','1448235127','1','1479771127','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('66','5','440225','1448471254','1','1480007254','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('67','3','440228','1448471497','1','1480007497','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('68','4','440228','1448471507','1','1480007507','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('69','5','440020','1451417346','1','1482953346','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('70','5','440020','1451417396','1','1482953396','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('71','3','440230','1451417731','1','1482953731','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('72','4','440230','1451417767','1','1482953767','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('73','5','440230','1451417796','1','1482953796','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('74','2','440231','1451418275','1','1482954275','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('75','5','440231','1451418298','2','1514490298','800.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('76','4','440238','1451844233','1','1483380233','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('77','5','440238','1451844242','1','1483380242','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('78','4','440232','1451844386','1','1483380386','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('79','2','440227','1452637591','1','1484173591','80.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('80','3','440227','1452637763','1','1484173763','150.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('81','4','440227','1452637984','1','1484173984','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('82','4','440183','1452708359','1','1484244359','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('83','5','440183','1452708493','1','1484244493','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('84','4','440183','1452709082','1','1484245082','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('85','5','440183','1452709249','1','1484245249','400.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('86','4','440183','1452713287','1','1484249287','240.00','0');
INSERT INTO `%DB_PREFIX%vip_buy_log` VALUES ('87','5','440183','1452713532','1','1484249532','400.00','0');
DROP TABLE IF EXISTS `%DB_PREFIX%vip_festivals`;
CREATE TABLE `%DB_PREFIX%vip_festivals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '节日名称',
  `holiday_date` date NOT NULL COMMENT '节日时间',
  `number` int(11) NOT NULL COMMENT '数量',
  `brief` text NOT NULL COMMENT '简介',
  `is_delete` tinyint(1) NOT NULL COMMENT '删除标识',
  `is_effect` tinyint(1) NOT NULL COMMENT '有效性标识',
  `sort` int(11) NOT NULL COMMENT '节日礼品排序',
  `is_send` tinyint(1) NOT NULL COMMENT '发送标识',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='节日礼品表';
INSERT INTO `%DB_PREFIX%vip_festivals` VALUES ('10','春节福利','2015-07-10','1','','0','1','1','1');
INSERT INTO `%DB_PREFIX%vip_festivals` VALUES ('11','中秋节','2015-07-10','2','','0','1','2','1');
INSERT INTO `%DB_PREFIX%vip_festivals` VALUES ('12','生日礼品','2015-07-10','1','','0','1','3','1');
INSERT INTO `%DB_PREFIX%vip_festivals` VALUES ('13','端午节','2015-07-10','1','','0','1','4','1');
INSERT INTO `%DB_PREFIX%vip_festivals` VALUES ('14','国庆节','2015-07-13','1','','0','1','5','1');
INSERT INTO `%DB_PREFIX%vip_festivals` VALUES ('15','妇女节','2015-07-13','500','112233','0','1','6','1');
INSERT INTO `%DB_PREFIX%vip_festivals` VALUES ('16','节日','2015-07-20','1','','0','1','7','1');
INSERT INTO `%DB_PREFIX%vip_festivals` VALUES ('17','七夕','2015-07-28','1','','0','1','8','1');
INSERT INTO `%DB_PREFIX%vip_festivals` VALUES ('18','八一','2015-08-01','0','测试','0','1','9','0');
INSERT INTO `%DB_PREFIX%vip_festivals` VALUES ('19','元旦节','2016-01-01','5','元旦节 元旦节 元旦节','0','1','10','1');
DROP TABLE IF EXISTS `%DB_PREFIX%vip_gift`;
CREATE TABLE `%DB_PREFIX%vip_gift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '礼品名称',
  `brief` text NOT NULL COMMENT '礼品简介',
  `is_delete` tinyint(1) NOT NULL COMMENT '删除标识',
  `is_effect` tinyint(1) NOT NULL COMMENT '有效性标识',
  `sort` int(11) NOT NULL COMMENT '礼品排序',
  PRIMARY KEY (`id`),
  KEY `sort` (`sort`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='礼品列表';
INSERT INTO `%DB_PREFIX%vip_gift` VALUES ('1','礼品一','礼品一礼品一礼品一礼品一','0','1','1');
INSERT INTO `%DB_PREFIX%vip_gift` VALUES ('2','礼品二','礼品二礼品二礼品二礼品二','0','1','2');
INSERT INTO `%DB_PREFIX%vip_gift` VALUES ('3','礼品三','礼品三礼品三礼品三礼品三','0','1','3');
DROP TABLE IF EXISTS `%DB_PREFIX%vip_red_envelope`;
CREATE TABLE `%DB_PREFIX%vip_red_envelope` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `money` decimal(20,0) NOT NULL COMMENT '红包金额',
  `brief` text NOT NULL COMMENT '红包简介',
  `is_delete` tinyint(1) NOT NULL COMMENT '删除标识',
  `is_effect` tinyint(1) NOT NULL COMMENT '有效性标识',
  `sort` int(11) NOT NULL COMMENT '红包排序',
  PRIMARY KEY (`id`),
  KEY `sort` (`sort`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='红包列表';
INSERT INTO `%DB_PREFIX%vip_red_envelope` VALUES ('1','1','1元红包1元红包1元红包1元红包1元红包','0','1','1');
INSERT INTO `%DB_PREFIX%vip_red_envelope` VALUES ('2','2','2元红包2元红包2元红包2元红包','0','1','2');
INSERT INTO `%DB_PREFIX%vip_red_envelope` VALUES ('3','5','5元红包','0','1','3');
INSERT INTO `%DB_PREFIX%vip_red_envelope` VALUES ('4','0','','1','1','4');
DROP TABLE IF EXISTS `%DB_PREFIX%vip_setting`;
CREATE TABLE `%DB_PREFIX%vip_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vip_id` int(11) NOT NULL COMMENT 'VIP等级ID',
  `probability` int(11) NOT NULL COMMENT '收益奖励几率',
  `load_mfee` decimal(20,2) NOT NULL COMMENT '借款管理费(每月)',
  `interest` int(11) NOT NULL COMMENT '投资利息费',
  `charges` int(11) NOT NULL COMMENT '提现手续费(每笔)',
  `coefficient` decimal(20,2) NOT NULL COMMENT '积分折现系数',
  `multiple` decimal(20,1) NOT NULL COMMENT '积分获取倍数',
  `bgift` int(11) NOT NULL COMMENT '生日礼品',
  `btype` tinyint(1) NOT NULL COMMENT '生日礼品类别 1积分 2现金红包',
  `holiday_score` int(11) NOT NULL COMMENT '节日积分',
  `rate` decimal(20,2) NOT NULL COMMENT '增加收益率',
  `integral` int(11) NOT NULL COMMENT '收益积分值',
  `red_envelope` varchar(100) NOT NULL COMMENT '多种类型红包金额',
  `gift` varchar(100) NOT NULL COMMENT '多种礼品ID',
  `is_delete` tinyint(1) NOT NULL COMMENT '删除标识',
  `is_effect` tinyint(1) NOT NULL COMMENT '有效性标识',
  `sort` int(11) NOT NULL COMMENT 'VIP配置排序',
  `original_price` decimal(20,2) NOT NULL COMMENT 'vip 原价（原先购买价格）',
  `site_pirce` decimal(20,2) NOT NULL COMMENT 'vip 现价 (现有购买价格)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='VIP配置表';
INSERT INTO `%DB_PREFIX%vip_setting` VALUES ('2','2','100','0.29','8','1','0.55','1.2','50','2','1000','0.40','300','1,2,3','1,2','0','1','2','100.00','80.00');
INSERT INTO `%DB_PREFIX%vip_setting` VALUES ('3','3','15','0.28','6','0','0.60','1.3','100','2','2000','0.30','350','1,2,3','2,3','0','1','3','200.00','150.00');
INSERT INTO `%DB_PREFIX%vip_setting` VALUES ('4','4','20','0.27','4','0','0.65','1.4','200','2','5000','0.32','400','2,3','1,2,3','0','1','4','300.00','240.00');
INSERT INTO `%DB_PREFIX%vip_setting` VALUES ('5','5','25','0.25','2','0','0.70','1.5','500','2','10000','0.34','450','1,2,3','1,2,3','0','1','5','500.00','400.00');
INSERT INTO `%DB_PREFIX%vip_setting` VALUES ('7','2','50','0.00','0','0','5.00','0.0','1000','1','1000','5.00','4500','1,2,3','1,2,3','0','1','6','500.00','200.00');
DROP TABLE IF EXISTS `%DB_PREFIX%vip_type`;
CREATE TABLE `%DB_PREFIX%vip_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vip_grade` varchar(100) NOT NULL COMMENT 'VIP等级',
  `lower_limit` decimal(20,0) NOT NULL COMMENT '金额下限',
  `upper_limit` decimal(20,0) NOT NULL COMMENT '金额上限',
  `content` varchar(255) NOT NULL COMMENT 'VIP升级条件',
  `is_delete` tinyint(1) NOT NULL COMMENT '删除标识',
  `is_effect` tinyint(1) NOT NULL COMMENT '有效性标识',
  `sort` int(11) NOT NULL COMMENT 'VIP排序',
  PRIMARY KEY (`id`),
  KEY `sort` (`sort`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='VIP类别表';
INSERT INTO `%DB_PREFIX%vip_type` VALUES ('1','普通VIP会员','15000','49999','注册完成后为普通VIP会员；','1','1','1');
INSERT INTO `%DB_PREFIX%vip_type` VALUES ('2','白银VIP会员','3000','100000','总额度达到3000元，且没有迟还款和逾期还款的用户，系统自动为其升级成为白银VIP会员。','0','1','2');
INSERT INTO `%DB_PREFIX%vip_type` VALUES ('3','黄金VIP会员','100001','4999999','总额度达到500000元，且没有迟还款和逾期还款记录的用户，系统自动为其升级成为黄金VIP会员。','0','1','3');
INSERT INTO `%DB_PREFIX%vip_type` VALUES ('4','铂金VIP会员','5000000','49999999','总额度达到5000000元，且没有迟还款和逾期还款记录的用户，系统自动为其升级成为白金VIP会员。','0','1','4');
INSERT INTO `%DB_PREFIX%vip_type` VALUES ('5','钻石会员','50000000','999999999999999','总额度达到50000000元，且没有还款和逾期还款记录的用户，系统自动为其升级成为钻石VIP会员。','0','1','5');
DROP TABLE IF EXISTS `%DB_PREFIX%vip_upgrade_record`;
CREATE TABLE `%DB_PREFIX%vip_upgrade_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '会员ID',
  `original_vip_id` int(11) NOT NULL COMMENT 'VIP原等级ID',
  `now_vip_id` int(11) NOT NULL COMMENT 'VIP现有等级ID',
  `causes` varchar(255) NOT NULL COMMENT 'VIP升级原因',
  `upgrade_date` date NOT NULL COMMENT 'VIP升级日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='VIP升级记录表';
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('25','440009','0','2','借款升级','2015-03-05');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('26','440010','0','2','投标升级','2015-03-05');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('27','440009','2','1','借款升级','2015-03-06');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('28','440010','2','1','投标升级','2015-03-06');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('29','440022','0','2','投标升级','2015-03-17');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('30','440020','0','2','借款升级','2015-03-17');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('31','440026','0','2','正常还款升级','2015-03-26');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('32','440029','0','2','正常还款升级','2015-03-30');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('33','440031','0','2','借款升级','2015-04-08');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('34','440092','0','2','投标升级','2015-06-16');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('35','440090','0','2','借款升级','2015-06-16');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('36','440035','0','2','投标升级','2015-06-25');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('37','440090','3','4','VIP购买升级','2015-07-10');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('38','440090','4','2','VIP购买升级','2015-07-10');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('39','440093','3','4','VIP购买升级','2015-07-20');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('40','440094','4','5','VIP购买升级','2015-07-22');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('41','440093','4','5','VIP购买升级','2015-07-28');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('42','440098','3','4','VIP购买升级','2015-07-28');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('43','440121','3','4','VIP购买升级','2015-07-29');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('44','440127','0','2','借款升级','2015-08-17');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('45','440130','0','2','借款升级','2015-08-17');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('46','440136','0','2','借款升级','2015-08-19');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('47','440017','0','2','投标升级','2015-09-22');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('48','440036','0','2','VIP购买升级','2015-10-29');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('49','440020','2','3','VIP购买升级','2015-10-29');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('50','440129','3','4','VIP购买升级','2015-11-02');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('51','440129','4','5','VIP购买升级','2015-11-02');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('52','440225','0','2','VIP购买升级','2015-11-09');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('53','440228','0','2','投标升级','2015-11-09');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('54','440225','2','3','VIP购买升级','2015-11-13');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('55','440229','0','2','投标升级','2015-11-16');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('56','440226','0','2','投标升级','2015-11-16');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('57','440226','2','5','VIP购买升级','2015-11-16');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('58','440230','0','2','投标升级','2015-11-18');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('59','440225','3','4','VIP购买升级','2015-11-23');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('60','440225','4','5','VIP购买升级','2015-11-26');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('61','440228','2','3','VIP购买升级','2015-11-26');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('62','440228','3','4','VIP购买升级','2015-11-26');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('63','440230','2','3','VIP购买升级','2015-12-30');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('64','440230','3','4','VIP购买升级','2015-12-30');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('65','440230','4','5','VIP购买升级','2015-12-30');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('66','440231','0','2','VIP购买升级','2015-12-30');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('67','440231','2','5','VIP购买升级','2015-12-30');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('68','440081','0','2','借款升级','2015-12-31');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('69','440238','0','4','VIP购买升级','2016-01-04');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('70','440238','4','5','VIP购买升级','2016-01-04');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('71','440232','0','4','VIP购买升级','2016-01-04');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('72','440227','0','2','VIP购买升级','2016-01-13');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('73','440227','2','3','VIP购买升级','2016-01-13');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('74','440227','3','4','VIP购买升级','2016-01-13');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('75','440183','2','4','VIP购买升级','2016-01-14');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('76','440183','4','5','VIP购买升级','2016-01-14');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('77','440183','3','4','VIP购买升级','2016-01-14');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('78','440183','4','5','VIP购买升级','2016-01-14');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('79','440183','3','4','VIP购买升级','2016-01-14');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('80','440183','4','5','VIP购买升级','2016-01-14');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('81','440255','0','2','借款升级','2016-02-01');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('82','440256','0','2','借款升级','2016-02-02');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('83','440247','0','2','管理员操作VIP升级','2016-02-04');
INSERT INTO `%DB_PREFIX%vip_upgrade_record` VALUES ('84','440260','0','2','管理员操作VIP升级','2016-02-04');
DROP TABLE IF EXISTS `%DB_PREFIX%vote`;
CREATE TABLE `%DB_PREFIX%vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `begin_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  `sort` int(11) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%vote` VALUES ('9','投票测试','1410809987','1420054740','1','1','测试题目');
INSERT INTO `%DB_PREFIX%vote` VALUES ('10','投票功能是否好用','1417374030','1419966039','1','2','投票功能是否好用');
DROP TABLE IF EXISTS `%DB_PREFIX%vote_ask`;
CREATE TABLE `%DB_PREFIX%vote_ask` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `sort` int(11) NOT NULL,
  `vote_id` int(11) NOT NULL,
  `val_scope` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%vote_ask` VALUES ('30','投票1','1','1','9','是');
INSERT INTO `%DB_PREFIX%vote_ask` VALUES ('31','投票2','1','2','9','否');
INSERT INTO `%DB_PREFIX%vote_ask` VALUES ('41','1.投票功能是否好用','1','1','10','好用,不好用,一般般');
INSERT INTO `%DB_PREFIX%vote_ask` VALUES ('42','2.你的爱好?','2','2','10','旅游,看书,看电视,看电影,购物,上网,其他...');
DROP TABLE IF EXISTS `%DB_PREFIX%vote_result`;
CREATE TABLE `%DB_PREFIX%vote_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  `vote_id` int(11) NOT NULL,
  `vote_ask_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('15','报纸3','1','0','13');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('16','报纸1','1','0','13');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('17','1','2','1','16');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('18','1','2','1','15');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('19','江佳','1','0','15');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('20','20岁以下\r\n21-30岁\r\n31-40岁\r\n50岁以上','1','0','16');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('21','232','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('22','21-30岁','11','0','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('23','dwa','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('24','88952634','98','88952634','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('25','20岁以下','98','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('26','21-30岁','98','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('27','31-40岁','98','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('28','50岁以上','98','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('29','88952634\\\'`\\&quot;(','2','88952634','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('30','88952634-0','2','88952634','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('31','88952634s3','2','88952634','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('32','88952634\\\'+\\\'','2','88952634','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('33','88952634\\\'','4','88952634','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('34','88952634\\\'||\\\'','2','88952634','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('35','88952634&lt;alert(88952634)&gt;','2','88952634','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('36','20岁以下\\\'`\\&quot;(','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('37','20岁以下-0','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('38','20岁以下s3','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('39','20岁以下\\\'+\\\'','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('40','20岁以下\\\'','4','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('41','20岁以下\\\'||\\\'','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('42','20岁以下&lt;alert(88952634)&gt;','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('43','21-30岁\\\'`\\&quot;(','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('44','21-30岁-0','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('45','21-30岁s3','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('46','21-30岁\\\'+\\\'','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('47','21-30岁\\\'','4','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('48','21-30岁\\\'||\\\'','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('49','21-30岁&lt;alert(88952634)&gt;','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('50','31-40岁\\\'`\\&quot;(','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('51','31-40岁-0','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('52','31-40岁s3','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('53','31-40岁\\\'+\\\'','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('54','31-40岁\\\'','4','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('55','31-40岁\\\'||\\\'','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('56','31-40岁&lt;alert(88952634)&gt;','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('57','50岁以上\\\'`\\&quot;(','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('58','50岁以上-0','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('59','50岁以上s3','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('60','50岁以上\\\'+\\\'','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('61','50岁以上\\\'','4','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('62','50岁以上\\\'||\\\'','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('63','50岁以上&lt;alert(88952634)&gt;','2','88952634','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('64','111','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('65','姓名姓','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('66','jsbyttqy','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('67','qtcveyvm','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('68','20岁以下','5','0','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('69','kwevurxe','1','12345','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('70','20岁以下','1','12345','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('71','kwevurxe','1','1','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('72','20岁以下','1','1','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('73','rhpyvdkj','1','-1','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('74','20岁以下','1','-1','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('75','rhpyvdkj','1','46','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('76','21-30岁','1','46','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('77','31-40岁','4','0','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('78','50岁以上','2','0','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('79','fanwe','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('80','小小彬','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('81','黎明','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('82','李银华','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('83','asda','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('84','喊口号','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('85','test','95','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('86','20','176','0','22');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('87','hh','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('88','adsasdsa','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('89','ta','1','0','21');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('90','3','12','0','23');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('95','2\r\n3\r\n1','2','9','26');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('96','2\n3\n1','1','9','26');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('97','2\r\n3\r\n1','1','9','27');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('98','是','40','9','30');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('99','否','3','9','31');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('100','是\\\'`\\&quot;(','2','9','30');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('101','是-0','2','9','30');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('102','是s3','2','9','30');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('103','是\\\'+\\\'','2','9','30');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('104','是\\\'','4','9','30');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('105','是\\\'||\\\'','2','9','30');
INSERT INTO `%DB_PREFIX%vote_result` VALUES ('106','是&lt;alert(88952634)&gt;','2','9','30');
DROP TABLE IF EXISTS `%DB_PREFIX%weixin_account`;
CREATE TABLE `%DB_PREFIX%weixin_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appid` varchar(255) NOT NULL COMMENT 'AppID(应用ID)-第三方平台指 授权方appid',
  `appsecret` varchar(255) NOT NULL COMMENT 'AppSecret(应用密钥)-第三方平台无用',
  `app_url` varchar(255) NOT NULL COMMENT 'URL(服务器地址)-第三方平台无用',
  `app_token` varchar(255) NOT NULL COMMENT 'Token(令牌)-第三方平台无用',
  `app_encodingAESKey` varchar(255) NOT NULL COMMENT 'EncodingAESKey(消息加解密密钥)-第三方平台无用',
  `authorizer_appid` varchar(255) NOT NULL COMMENT '授权方appid',
  `authorizer_access_token` varchar(255) NOT NULL COMMENT '授权方令牌-第三方平台无用',
  `expires_in` int(11) NOT NULL COMMENT '授权方令牌 有效时间-第三方平台无用',
  `authorizer_refresh_token` varchar(255) NOT NULL COMMENT '刷新令牌-第三方平台',
  `func_info` text NOT NULL COMMENT '公众号授权给开发者的权限集列表',
  `verify_type_info` tinyint(1) NOT NULL COMMENT '授权方认证类型，-1代表未认证，0代表微信认证，1代表新浪微博认证，2代表腾讯微博认证，3代表已资质认证通过但还未通过名称认证，4代表已资质认证通过、还未通过名称认证，但通过了新浪微博认证，5代表已资质认证通过、还未通过名称认证，但通过了腾讯微博认证',
  `service_type_info` tinyint(1) NOT NULL COMMENT '授权方公众号类型，0代表订阅号，1代表由历史老帐号升级后的订阅号，2代表服务号',
  `nick_name` varchar(255) NOT NULL,
  `user_name` varchar(255) NOT NULL COMMENT '授权方公众号的原始ID',
  `authorizer_info` varchar(255) NOT NULL COMMENT '授权方昵称',
  `head_img` varchar(255) NOT NULL COMMENT '授权方头像',
  `alias` varchar(255) NOT NULL COMMENT '授权方公众号所设置的微信号，可能为空',
  `qrcode_url` varchar(255) NOT NULL COMMENT '二维码图片的URL，开发者最好自行也进行保存',
  `location_report` tinyint(1) NOT NULL COMMENT '地理位置上报选项 0 无上报 1 进入会话时上报 2 每5s上报',
  `voice_recognize` tinyint(1) NOT NULL COMMENT '语音识别开关选项 0 关闭语音识别 1 开启语音识别',
  `customer_service` tinyint(1) NOT NULL COMMENT '客服开关选项 0 关闭多客服 1 开启多客服',
  `is_authorized` tinyint(1) NOT NULL DEFAULT '0' COMMENT '授权方是否取消授权 0表示取消授权 1表示授权',
  `user_id` int(11) NOT NULL COMMENT '会员ID ，诺type为1，user_id 为空',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 表示前台会员 1 表示后台管理员',
  `industry_1` int(11) NOT NULL,
  `industry_1_status` tinyint(1) NOT NULL,
  `industry_2` int(11) NOT NULL,
  `industry_2_status` tinyint(1) NOT NULL,
  `test_user` varchar(255) DEFAULT NULL COMMENT '测试微信号',
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `au_app_id` (`authorizer_appid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='//微信公众号列表';
INSERT INTO `%DB_PREFIX%weixin_account` VALUES ('8','','','','','','wxe4390206746d9367','lCh3oy0ezTg8Edlefm4t6vpXAm4qQOmaxiPQ2SCHduDmjAOQqaJpSGmxzwrvKUYEqVfaannzkqqGj8SR8m4qSKIlIJyPI56TjFlp-A3VBhsRTP-eHhdYaDf0rvPdW-yLAPUjAIDKZE','1453688489','refreshtoken@@@xL9DCcKlNnUuSZx57y1_PQhnP9jlwroMxdp5HHH5hLY','a:11:{i:0;a:1:{s:18:\"funcscope_category\";a:1:{s:2:\"id\";i:2;}}i:1;a:1:{s:18:\"funcscope_category\";a:1:{s:2:\"id\";i:3;}}i:2;a:1:{s:18:\"funcscope_category\";a:1:{s:2:\"id\";i:4;}}i:3;a:1:{s:18:\"funcscope_category\";a:1:{s:2:\"id\";i:5;}}i:4;a:1:{s:18:\"funcscope_category\";a:1:{s:2:\"id\";i:6;}}i:5;a:1:{s:18:\"funcscope_category\";a:1:{s:2:\"id\";i:7;}}i:6;a:1:{s:18:\"funcscope_category\";a:1:{s:2:\"id\";i:8;}}i:7;a:1:{s:18:\"funcscope_category\";a:1:{s:2:\"id\";i:11;}}i:8;a:1:{s:18:\"funcscope_category\";a:1:{s:2:\"id\";i:12;}}i:9;a:1:{s:18:\"funcscope_category\";a:1:{s:2:\"id\";i:13;}}i:10;a:1:{s:18:\"funcscope_category\";a:1:{s:2:\"id\";i:10;}}}','0','2','方维配资','gh_32a728fda405','','http://wx.qlogo.cn/mmopen/bdJlFlia0970JjTialDhDbdBzXPbSYc23L5U1XLCdTcsHbW1bhl2CbPSdx0FKJseKqAdrTXxdMag6eodTAhj1x4A2csSzA10az/0','jctp2p_pz','http://mmbiz.qpic.cn/mmbiz/mb4KlCvCYMUUbyJfzb6eRArhFXHmDpNubkJyia4J42WY4yib9PLEic57243pqhjeIjhxWda7PcWg4BQ4FWqOxK4ug/0','0','0','0','0','0','1','2','0','1','0','oeDhSs8SQ1EBuEmxefWHegVxNf3c','0');
DROP TABLE IF EXISTS `%DB_PREFIX%weixin_api_get_record`;
CREATE TABLE `%DB_PREFIX%weixin_api_get_record` (
  `openid` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`openid`),
  KEY `account_id` (`account_id`) USING BTREE,
  KEY `idx_0` (`account_id`,`create_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='请求的用户记录';
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('','0','0','1451269826');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oeDhSs68M9wN1wRmIyebp-W89fow','0','8','1451351057');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oeDhSs8SQ1EBuEmxefWHegVxNf3c','0','8','1453680907');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oeDhSsyUUQHk8gvM5aTxr_rpQ-iU','0','8','1452131200');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oeDhSs_LlEtHCGfeZCgo0rwNK950','0','8','1453682910');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt-sOSVt8dBWsK4SeRNHbXK0','0','0','1453079828');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt0Dc_2FDdwkhxcLuWsRvlm4','0','0','1454373466');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt1kYjYnP1nHvhf6YKsaLhUo','0','0','1453365052');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt2fbtqISrpKHbtY1GVxxnTQ','0','0','1451322194');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt2lPM-4SHwK-Z-WcF4vtLDE','0','0','1452820777');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt3AINdOZpEPdwhtMT4z7zOI','0','0','1452388354');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt3BflU5bEJwrFmPRkozkz0I','0','0','1452677082');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt3mxgLH-ZX8pg4Dq22jBXLk','0','0','1454295631');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt4sjtLYPjts7w2j0NdzDsJ4','0','0','1451897415');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt5c7Z1RakZsQZcEm3BBru1o','0','0','1453308635');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt78VWWhIXblw6NxtgYhua4c','0','0','1452151295');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt8XF1SjKfTD45nD63mgrUd4','0','0','1453008240');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt9uiYI4Kh5CKeHt06KdVJJA','0','0','1452234291');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNtyCFCOkPPyh-_DkZmGVPnCU','0','0','1451925515');
INSERT INTO `%DB_PREFIX%weixin_api_get_record` VALUES ('oVYNNt_EQz34h4Xhzydl9LPCIDxY','0','0','1452365907');
DROP TABLE IF EXISTS `%DB_PREFIX%weixin_conf`;
CREATE TABLE `%DB_PREFIX%weixin_conf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `group_id` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '配置输入的类型 0:文本输入 1:下拉框输入 2:图片上传 3:编辑器',
  `value_scope` text NOT NULL COMMENT '取值范围',
  `is_require` tinyint(1) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  `is_conf` tinyint(1) NOT NULL COMMENT '是否可配置 0: 可配置  1:不可配置',
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='//微信配置选项';
INSERT INTO `%DB_PREFIX%weixin_conf` VALUES ('1','第三方平台appid','platform_appid','wx5d1a3b76567906ad','0','0','','0','1','1','1');
INSERT INTO `%DB_PREFIX%weixin_conf` VALUES ('2','第三方平台token','platform_token','YNMrc5','0','0','','0','1','1','2');
INSERT INTO `%DB_PREFIX%weixin_conf` VALUES ('3','第三方平台symmetric_key','platform_encodingAesKey','qwertyuiopasdfghjklzxcvbnm12345678901234567','0','0','','0','1','1','3');
INSERT INTO `%DB_PREFIX%weixin_conf` VALUES ('4','是否开启第三方平台','platform_status','1','0','4','0,1','0','1','1','4');
INSERT INTO `%DB_PREFIX%weixin_conf` VALUES ('5','第三方平台AppSecret','platform_appsecret','0c79e1fa963cd80cc0be99b20a18faeb','0','0','','0','1','1','1');
INSERT INTO `%DB_PREFIX%weixin_conf` VALUES ('6','component_verify_ticket','platform_component_verify_ticket','ticket@@@mmKSJRz-mBfwbEI77FeGOikZeyr0CS_WVZERQeobjeuW0vjJf4rEnjXrj_nbNE3BBklK8mOnWtuOEccIjFBKUA','0','0','','0','1','0','6');
INSERT INTO `%DB_PREFIX%weixin_conf` VALUES ('7','第三方平台access_token','platform_component_access_token','fOi5Wfn66CXMSzsDl8QD80aQr58HxcvfsX5p7f3UyQtatHP4_WIjWvASK5Tx-IrEELUsih4-xsz2CdrUQaMoLfwo5psaiwACv3G_p-vI2KEqVl5UJrkFiwQM6XiMtzjATDFiADAPZB','0','0','','0','1','0','7');
INSERT INTO `%DB_PREFIX%weixin_conf` VALUES ('8','第三方平台预授权码','platform_pre_auth_code','preauthcode@@@2x1mpD3R75qdK5E_M2qPkCehU5sVvq0xGTkM9_slCHfKc-4xDLbLS0ouJzs07bG9','0','0','','0','1','0','8');
INSERT INTO `%DB_PREFIX%weixin_conf` VALUES ('9','第三方平台access_token有效期','platform_component_access_token_expire','1454866767','0','0','','0','1','0','9');
INSERT INTO `%DB_PREFIX%weixin_conf` VALUES ('10','第三方平台预授权码有效期','platform_pre_auth_code_expire','1451269925','0','0','','0','1','0','10');
DROP TABLE IF EXISTS `%DB_PREFIX%weixin_group`;
CREATE TABLE `%DB_PREFIX%weixin_group` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `groupid` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(60) NOT NULL DEFAULT '',
  `intro` varchar(200) NOT NULL DEFAULT '',
  `account_id` varchar(30) NOT NULL DEFAULT '',
  `fanscount` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `groupid` (`groupid`,`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('38','0','未分组','','5','641');
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('39','1','黑名单','','5','1');
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('40','2','星标组','','5','8');
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('41','100','木叶组','','5','18');
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('42','105','东莞分站','','5','1');
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('43','106','南方','','5','1');
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('44','107','天涯1','天涯','5','0');
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('45','108','test','23','5','0');
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('46','109','22','','5','0');
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('47','0','未分组','','8','27');
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('48','1','黑名单','','8','0');
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('49','2','星标组','','8','1');
INSERT INTO `%DB_PREFIX%weixin_group` VALUES ('50','100','测试组','123500','8','0');
DROP TABLE IF EXISTS `%DB_PREFIX%weixin_msg_list`;
CREATE TABLE `%DB_PREFIX%weixin_msg_list` (
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
  `code` varchar(60) NOT NULL COMMENT '发送的验证码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='//微信消息列表';
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('4','oeDhSs8SQ1EBuEmxefWHegVxNf3c','1','','0','0','4','440081','','0','0','3','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('5','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','','0','0','1452206325','440081','','0','0','231','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('6','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','','1452206325','1','1452206325','440081','发送成功','1','0','231','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('7','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','','0','0','1452206731','440081','','0','0','提现成功通知','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('8','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','','1452206731','1','1452206731','440081','发送成功','1','0','提现成功通知','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('9','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','','1452206868','1','1452206868','440081','发送成功','1','0','提现成功通知','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('10','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','a:4:{s:5:\"first\";a:2:{s:5:\"value\";s:18:\"提现成功通知\";s:5:\"color\";s:7:\"#173177\";}s:6:\"remark\";a:2:{s:5:\"value\";s:36:\"有任何疑问，请致电客服。\";s:5:\"color\";s:7:\"#173177\";}s:5:\"money\";a:2:{s:5:\"value\";s:6:\"150.00\";s:5:\"color\";s:7:\"#173177\";}s:5:\"timet\";a:2:{s:5:\"value\";s:10:\"2016-01-08\";s:5:\"color\";s:7:\"#173177\";}}','1452207561','1','1452207561','440081','发送成功','1','0','提现成功通知','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('11','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','a:4:{s:5:\"first\";a:2:{s:5:\"value\";s:18:\"提现成功通知\";s:5:\"color\";s:7:\"#173177\";}s:6:\"remark\";a:2:{s:5:\"value\";s:36:\"有任何疑问，请致电客服。\";s:5:\"color\";s:7:\"#173177\";}s:5:\"money\";a:2:{s:5:\"value\";s:6:\"200.00\";s:5:\"color\";s:7:\"#173177\";}s:5:\"timet\";a:2:{s:5:\"value\";s:10:\"2016-01-08\";s:5:\"color\";s:7:\"#173177\";}}','1452207684','1','1452207684','440081','发送成功','1','0','提现成功通知','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('12','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','s:0:\"\";','1452209168','1','1452209168','440081','发送成功','1','0','提现成功通知','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('13','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','a:4:{s:5:\"first\";a:2:{s:5:\"value\";s:18:\"提现成功通知\";s:5:\"color\";s:7:\"#173177\";}s:6:\"remark\";a:2:{s:5:\"value\";s:36:\"有任何疑问，请致电客服。\";s:5:\"color\";s:7:\"#173177\";}s:5:\"money\";a:2:{s:5:\"value\";s:6:\"200.00\";s:5:\"color\";s:7:\"#173177\";}s:5:\"timet\";a:2:{s:5:\"value\";s:10:\"2016-01-08\";s:5:\"color\";s:7:\"#173177\";}}','1452209223','1','1452209223','440081','发送成功','1','0','提现成功通知','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('14','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','a:4:{s:5:\"first\";a:2:{s:5:\"value\";s:18:\"提现成功通知\";s:5:\"color\";s:7:\"#173177\";}s:6:\"remark\";a:2:{s:5:\"value\";s:36:\"有任何疑问，请致电客服。\";s:5:\"color\";s:7:\"#173177\";}s:5:\"money\";a:2:{s:5:\"value\";s:6:\"100.00\";s:5:\"color\";s:7:\"#173177\";}s:5:\"timet\";a:2:{s:5:\"value\";s:10:\"2016-01-08\";s:5:\"color\";s:7:\"#173177\";}}','1452209375','1','1452209375','440081','发送成功','1','0','提现成功通知','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('15','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','a:5:{s:6:\"touser\";s:28:\"oeDhSs8SQ1EBuEmxefWHegVxNf3c\";s:11:\"template_id\";s:43:\"gQj-c-o99MkIO1T0-js_JkbmzWfG6voVkvHmxkMZjUA\";s:3:\"url\";s:20:\"http://p2p.fanwe.net\";s:8:\"topcolor\";s:7:\"#FF0000\";s:4:\"data\";a:4:{s:5:\"first\";a:2:{s:5:\"value\";s:18:\"提现成功通知\";s:5:\"color\";s:7:\"#173177\";}s:6:\"remark\";a:2:{s:5:\"value\";s:36:\"有任何疑问，请致电客服。\";s:5:\"color\";s:7:\"#173177\";}s:5:\"money\";a:2:{s:5:\"value\";s:6:\"150.00\";s:5:\"color\";s:7:\"#173177\";}s:5:\"timet\";a:2:{s:5:\"value\";s:10:\"2016-01-08\";s:5:\"color\";s:7:\"#173177\";}}}','1452209530','1','1452209530','440081','发送成功','1','0','提现成功通知','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('16','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','a:5:{s:6:\"touser\";s:28:\"oeDhSs8SQ1EBuEmxefWHegVxNf3c\";s:11:\"template_id\";s:43:\"gQj-c-o99MkIO1T0-js_JkbmzWfG6voVkvHmxkMZjUA\";s:3:\"url\";s:20:\"http://p2p.fanwe.net\";s:8:\"topcolor\";s:7:\"#FF0000\";s:4:\"data\";a:4:{s:5:\"first\";a:2:{s:5:\"value\";s:18:\"提现成功通知\";s:5:\"color\";s:7:\"#173177\";}s:6:\"remark\";a:2:{s:5:\"value\";s:36:\"有任何疑问，请致电客服。\";s:5:\"color\";s:7:\"#173177\";}s:5:\"money\";a:2:{s:5:\"value\";s:7:\"1000.00\";s:5:\"color\";s:7:\"#173177\";}s:5:\"timet\";a:2:{s:5:\"value\";s:10:\"2016-01-11\";s:5:\"color\";s:7:\"#173177\";}}}','1452447712','1','1452447712','440081','发送成功','1','0','提现成功通知','0','0','');
INSERT INTO `%DB_PREFIX%weixin_msg_list` VALUES ('17','oeDhSs8SQ1EBuEmxefWHegVxNf3c','3','a:5:{s:6:\"touser\";s:28:\"oeDhSs8SQ1EBuEmxefWHegVxNf3c\";s:11:\"template_id\";s:43:\"gQj-c-o99MkIO1T0-js_JkbmzWfG6voVkvHmxkMZjUA\";s:3:\"url\";s:20:\"http://p2p.fanwe.net\";s:8:\"topcolor\";s:7:\"#FF0000\";s:4:\"data\";a:4:{s:5:\"first\";a:2:{s:5:\"value\";s:18:\"提现成功通知\";s:5:\"color\";s:7:\"#173177\";}s:6:\"remark\";a:2:{s:5:\"value\";s:36:\"有任何疑问，请致电客服。\";s:5:\"color\";s:7:\"#173177\";}s:5:\"money\";a:2:{s:5:\"value\";s:6:\"200.00\";s:5:\"color\";s:7:\"#173177\";}s:5:\"timet\";a:2:{s:5:\"value\";s:10:\"2016-01-11\";s:5:\"color\";s:7:\"#173177\";}}}','1452447728','1','1452447728','440081','发送成功','1','0','提现成功通知','0','0','');
DROP TABLE IF EXISTS `%DB_PREFIX%weixin_nav`;
CREATE TABLE `%DB_PREFIX%weixin_nav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT '' COMMENT '菜单名称',
  `sort` int(11) DEFAULT '0' COMMENT '菜单排序 大->小',
  `key_or_url` varchar(255) DEFAULT '' COMMENT '用于推送到微信平台的key或url(所有以http://开头的表示url，其余一率为key)',
  `event_type` enum('click') DEFAULT 'click' COMMENT '按钮的事件，目前微信只支持click',
  `account_id` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0' COMMENT '是否已推送到微信(0:未推送或失败 1:成功)，该列同一个商家全部相同，菜单为一次性推送,对菜单本地修改时，批量更新该值为0',
  `u_id` int(11) DEFAULT NULL,
  `u_module` varchar(255) DEFAULT NULL,
  `u_action` varchar(255) DEFAULT NULL,
  `u_param` varchar(255) DEFAULT NULL,
  `pid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sort` (`sort`),
  KEY `event_type` (`event_type`),
  KEY `account_id` (`account_id`,`key_or_url`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='为微信自定义的菜单设置';
INSERT INTO `%DB_PREFIX%weixin_nav` VALUES ('1','22','1','','click','5','0','0','index','','111','0');
INSERT INTO `%DB_PREFIX%weixin_nav` VALUES ('2','0011','2','22','click','8','0','0','','','','0');
INSERT INTO `%DB_PREFIX%weixin_nav` VALUES ('3','0022','3','','click','8','0','0','index','','','0');
DROP TABLE IF EXISTS `%DB_PREFIX%weixin_reply`;
CREATE TABLE `%DB_PREFIX%weixin_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `i_msg_type` enum('event','link','location','image','text') DEFAULT 'text' COMMENT '接收到的微信的推送到本系统api中的MsgType',
  `o_msg_type` enum('news','music','text') DEFAULT 'text' COMMENT '用于响应并回复给微信推送的消息类型 news:图文 music:音乐 text:纯文本',
  `keywords` varchar(300) DEFAULT NULL COMMENT '用于响应文本(i_msg_type:text或者i_event:click时对key的响应)类型的回复时进行匹配的关键词',
  `keywords_match` text COMMENT 'keywords的全文索引列',
  `keywords_match_row` text COMMENT 'keywords全文索引的未作unicode编码的原文，用于开发者查看',
  `address` text COMMENT '用于显示的地理地址',
  `api_address` text COMMENT '用于地理定位的API地址',
  `x_point` varchar(100) DEFAULT '' COMMENT '用于lbs消息,i_msg_type:location 匹配的经度',
  `y_point` varchar(100) DEFAULT '' COMMENT '用于lbs消息,i_msg_type:location 匹配的纬度',
  `scale_meter` int(11) DEFAULT '0' COMMENT '用于lbs消息,i_msg_type:location 匹配的距离范围(米)',
  `i_event` enum('subscribe','unsubscribe','click','empty') DEFAULT 'empty' COMMENT '用于响应i_msg_type为event时的对应事件',
  `reply_content` text COMMENT '回复的文本消息',
  `reply_music` varchar(255) DEFAULT '' COMMENT '回复的音乐链接',
  `reply_news_title` text COMMENT '图文回复的标题',
  `reply_news_description` text COMMENT '图文回复的描述',
  `reply_news_picurl` varchar(255) DEFAULT '' COMMENT '图文回复的图片链接',
  `reply_news_url` varchar(255) DEFAULT '' COMMENT '图文回复的跳转链接',
  `reply_news_content` text,
  `type` tinyint(1) DEFAULT '0' COMMENT '回复归类 \r\n0:普通的回复 \r\n1:默认回复(只能一条文本或图文) \r\n2:官网回复(只能有一条图文)\r\n3.业务数据(图文)\r\n4.关注时回复(只能有一条文本或图文) \r\n5.取消关注时回复(只能有一条文本或图文) ',
  `relate_data` varchar(255) DEFAULT '' COMMENT '关联的业务数据源(如youhui,vote)等',
  `relate_id` int(11) DEFAULT '0' COMMENT '所关联的relate_data的id，用于判断数据关联的删除(指定url)',
  `account_id` int(11) DEFAULT '0' COMMENT '所属的商家ID',
  `default_close` tinyint(1) DEFAULT '1' COMMENT '默认回复是否关闭 0：关闭 1：开启',
  `relate_type` tinyint(1) DEFAULT NULL COMMENT '与关联数据的关系 0:回复数据由关联数据源获取 1:只url跳转数据来源于关联数据',
  `match_type` tinyint(1) NOT NULL DEFAULT '0',
  `u_id` int(11) DEFAULT NULL,
  `u_module` varchar(255) DEFAULT NULL,
  `u_action` varchar(255) DEFAULT NULL,
  `u_param` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i_msg_type` (`i_msg_type`),
  KEY `o_msg_type` (`o_msg_type`),
  KEY `i_event` (`i_event`),
  KEY `type` (`type`),
  KEY `relate_data` (`relate_data`),
  KEY `relate_id` (`relate_id`),
  KEY `account_id` (`account_id`),
  KEY `match_type` (`account_id`,`match_type`,`keywords`(255)),
  FULLTEXT KEY `keywords_match` (`keywords_match`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='商家回复设置表';
INSERT INTO `%DB_PREFIX%weixin_reply` VALUES ('2','text','news','1','+ux49','1','','','','','0','empty','','','2','22','./public/attachment/201512/24/16/567ba68f2aa7d.gif','13','','0','','0','0','1','1','0','0','','','');
INSERT INTO `%DB_PREFIX%weixin_reply` VALUES ('3','text','text','','','','','','','','0','empty','关注回复 /::)1234567','','','','','','','4','','0','0','0','0','0','0','','','');
INSERT INTO `%DB_PREFIX%weixin_reply` VALUES ('4','text','text','','','','','','','','0','empty','这个是默认回复  /::)ASD','','','','','','','1','','0','0','0','0','0','0','','','');
INSERT INTO `%DB_PREFIX%weixin_reply` VALUES ('5','location','news','234','+ux50 +ux51 +ux52','234','1','','119.296494','26.074508','10000','empty','','','123','1111','./public/attachment/201512/28/10/56809e99adec8.gif','23','','0','','0','0','1','1','0','0','','','');
INSERT INTO `%DB_PREFIX%weixin_reply` VALUES ('6','text','text','自定义文本回复','','','','','','','0','empty','文本回复测试','','','','','','','0','','0','0','1','0','0','0','','','');
INSERT INTO `%DB_PREFIX%weixin_reply` VALUES ('7','text','text','','','','','','','','0','empty','123','','','','','','','1','','0','5','0','0','0','0','','','');
INSERT INTO `%DB_PREFIX%weixin_reply` VALUES ('8','text','text','','','','','','','','0','empty','000  /:handclap /:share','','','','','','','4','','0','8','0','0','0','0','','','');
INSERT INTO `%DB_PREFIX%weixin_reply` VALUES ('9','text','text','123','','','','','','','0','empty','23','','','','','','','0','','0','8','1','0','1','0','','','');
INSERT INTO `%DB_PREFIX%weixin_reply` VALUES ('10','text','text','','','','','','','','0','empty','/::)','','','','','','','1','','0','8','0','0','0','0','','','');
INSERT INTO `%DB_PREFIX%weixin_reply` VALUES ('11','text','news','123','','','','','','','0','empty','','','112233','111222333','./public/attachment/201512/29/14/56822af84938e.gif','http://p2p.fanwe.net/index.php?ctl=weixin&act=gz_accept&appid=/$APPID$','','0','','0','8','1','1','0','0','','','');
INSERT INTO `%DB_PREFIX%weixin_reply` VALUES ('12','location','news','00','+ux48 +ux48','00','111','','119.296494','26.074508','1000','empty','','','2222','12345','./public/attachment/201601/07/10/568dca8646b6d.gif','333','','0','','0','8','1','1','0','0','','','');
INSERT INTO `%DB_PREFIX%weixin_reply` VALUES ('14','text','text','怎么','','','','','','','0','empty','/:share','','','','','','','0','','0','8','1','0','1','0','','','');
DROP TABLE IF EXISTS `%DB_PREFIX%weixin_reply_relate`;
CREATE TABLE `%DB_PREFIX%weixin_reply_relate` (
  `main_reply_id` int(11) DEFAULT '0' COMMENT '主回复ID',
  `relate_reply_id` int(11) DEFAULT '0' COMMENT '关联的多图文用的子回复ID',
  `sort` tinyint(1) DEFAULT '0',
  KEY `main_reply_id` (`main_reply_id`),
  KEY `relate_reply_id` (`relate_reply_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='多图文回复的关联配置';
DROP TABLE IF EXISTS `%DB_PREFIX%weixin_send`;
CREATE TABLE `%DB_PREFIX%weixin_send` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '标题',
  `author` varchar(100) NOT NULL COMMENT '作者件',
  `media_file` varchar(255) NOT NULL COMMENT '多媒体文件',
  `content` text NOT NULL COMMENT '图文消息页面的内容，支持HTML标签',
  `send_type` tinyint(4) NOT NULL COMMENT '0普通群发，1高级群发',
  `user_type` tinyint(4) NOT NULL COMMENT '发送对 0所有 1会员组 2会员等级',
  `user_type_id` int(11) NOT NULL COMMENT '组ID或者等级ID',
  `msgtype` enum('news','music','video','voice','image','text') NOT NULL COMMENT '消息类型',
  `relate_type` tinyint(4) NOT NULL COMMENT '与关联数据的关系 0:回复数据由关联数据源获取 1:只url跳转数据来源于关联数据',
  `relate_data` varchar(255) NOT NULL,
  `relate_id` int(255) NOT NULL,
  `url` varchar(255) NOT NULL COMMENT '连接地址',
  `digest` text NOT NULL COMMENT '简介',
  `account_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `create_time` int(11) NOT NULL,
  `send_time` int(11) NOT NULL COMMENT '推送时间',
  `media_id` varchar(255) NOT NULL COMMENT '微信服务器的关联多媒体ID',
  `u_id` int(11) NOT NULL,
  `u_module` varchar(255) NOT NULL,
  `u_action` varchar(255) NOT NULL,
  `u_param` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('30','是是是','','','1111','0','0','0','text','0','','0','','','8','1','1451268423','1451269676','','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('31','000','','./public/attachment/201601/07/17/568e33342c92f.jpg','111','1','0','2','news','0','','0','','0000','8','1','1451945904','1452131158','CoCjWFC3gJGh-e4rBydi9NVvTUnnaZTLp75bRfbDhkVpWwdfxLg8gQ3KcvSEc14i','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('32','234','','','12345','0','0','0','text','0','','0','','','8','1','1451268574','1451945993','','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('34','111','','','123','0','0','0','text','0','','0','','','8','1','1451953230','1453682498','','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('35','1234','','./public/attachment/201601/07/17/568e36c12b469.jpg','555','0','0','752','news','0','','0','','000','8','1','1451955410','1452132333','','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('36','0000000','','./public/attachment/201601/07/17/568e3592c487d.mp3','','0','0','0','voice','0','','0','','','8','1','1452131608','1452131634','','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('37','lklklasdfasdf','','./public/attachment/201601/07/18/568e38ba13be6.jpg','lklklasdfasdf','0','0','0','news','0','','0','','lklklasdfasdf','8','1','1452132420','1452210725','','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('38','17','','./public/attachment/201601/07/18/568e3b50b6611.jpg','111','1','0','2','news','0','','0','','2222','8','1','1452133093','1452133103','0ke72v5XiLADi_ynUc6neVbMZBbyCnbMKs0vlYfZjCwMLG5YEer9GAZpY-1AkFcq','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('39','微信测试','','./public/attachment/201601/08/16/568f7404b09d7.jpg','这个可以用','1','0','2','news','0','','0','','不错','8','1','1452213159','1452213172','sV3HH6s0ruTQBaNbkD9SMCItRV79mXxDbCY01cAJwAbL1MNxP58nJ6iXbXQZdbZw','0','licai_deals','index','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('40','中奖信息通知','','./public/attachment/201601/11/10/56930d36771d2.jpg','00123','0','0','760','text','0','','0','','','8','1','1452448904','1452451225','','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('41','1.11','','./public/attachment/201601/11/10/56930d7d3b427.jpg','000','1','0','2','news','0','','0','','111','8','1','1452449029','1452449035','n4VJgFvpnFUcLKSbBoEmKUBLtHYFWEyWW6cSFzSRVwy2mdEOuNM2sMfV8g1ujJqZ','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('42','123','','','鼓楼区工业路523号福大怡山文化创意园（原福大机械厂）8#101座2层（方维）','0','0','760','text','0','','0','','','8','1','1452563605','1452563622','','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('43','test','','./public/attachment/201601/25/14/56a5c2368d189.jpg','test test test test','0','0','0','news','0','','0','','test','8','1','1453674953','1453682477','','0','show_article','index','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('44','test','','./public/attachment/201601/25/14/56a5c2ff406a9.jpg','test test test test','1','0','2','news','0','','0','','test','8','1','1453675146','1453682905','hwEwvYBb4BJobDbPQ9FCU3BrDjosKrTD7MG9EmqOQSmyEvjAVyFYD6qU7IKakYYB','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('45','test1','','./public/attachment/201601/25/16/56a5e27a95757.jpg','订单','1','0','2','news','0','','0','','订单','8','0','1453683200','0','','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('46','test2','','./public/attachment/201601/25/16/56a5e29c3dcfb.jpg','订单','1','0','2','news','0','','0','','订单','8','0','1453683232','0','','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('47','test3','','./public/attachment/201601/25/16/56a5e2ba74d81.jpg','点点滴滴','1','0','2','news','0','','0','','点点滴滴','8','0','1453683263','0','','0','','','');
INSERT INTO `%DB_PREFIX%weixin_send` VALUES ('48','test5','','./public/attachment/201601/25/16/56a5e3b51f5d8.jpg','古古怪怪','1','0','2','news','0','','0','','古古怪怪','8','0','1453683517','0','','0','','','');
DROP TABLE IF EXISTS `%DB_PREFIX%weixin_send_relate`;
CREATE TABLE `%DB_PREFIX%weixin_send_relate` (
  `relate_id` int(11) NOT NULL,
  `send_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%weixin_tmpl`;
CREATE TABLE `%DB_PREFIX%weixin_tmpl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT '' COMMENT '模板名称',
  `msg` text COMMENT '模板内容',
  `template_id` varchar(255) DEFAULT NULL COMMENT '模板ID',
  `template_id_short` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT '0' COMMENT '菜单排序 大->小',
  `account_id` int(11) DEFAULT '0' COMMENT '所属的商家ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='微信模板';
INSERT INTO `%DB_PREFIX%weixin_tmpl` VALUES ('121','回款通知','a:2:{s:5:\"first\";s:12:\"回款通知\";s:6:\"remark\";a:2:{s:5:\"value\";s:18:\"点此了解详情\";s:5:\"color\";s:7:\"#173177\";}}','sqgPFmhIE7awMipuIe15y0DL4PboRzNOHaDHAnYGjCI','OPENTM207940989','0','8');
INSERT INTO `%DB_PREFIX%weixin_tmpl` VALUES ('122','投标结果通知','a:2:{s:5:\"first\";a:2:{s:5:\"value\";s:18:\"投标结果通知\";s:5:\"color\";s:7:\"#173177\";}s:6:\"remark\";a:2:{s:5:\"value\";s:21:\"满标放款成功！\";s:5:\"color\";s:7:\"#173177\";}}','W-8056-onkxZMQUTJN9-RyzSc2uTWw9PGNMeCt62r6s','OPENTM200685723','0','8');
INSERT INTO `%DB_PREFIX%weixin_tmpl` VALUES ('123','验证通知','a:2:{s:5:\"first\";s:12:\"验证通知\";s:6:\"remark\";a:2:{s:5:\"value\";s:84:\"若非本人操作，可能您的帐号存在安全风险，请及时修改密码。\";s:5:\"color\";s:7:\"#173177\";}}','UsyV5oq0RwoSkjg8ARDzPdB9CSrln8Ph7jVi8xw7NZI','OPENTM203026900','0','8');
INSERT INTO `%DB_PREFIX%weixin_tmpl` VALUES ('124','订单支付成功通知','a:2:{s:5:\"first\";s:24:\"订单支付成功通知\";s:6:\"remark\";a:2:{s:5:\"value\";s:18:\"点此了解详情\";s:5:\"color\";s:7:\"#173177\";}}','y2xETncCEJ29tvgBg0EIN-nHNFqPygAq0SOBeQjHI-A','OPENTM207791277','0','8');
INSERT INTO `%DB_PREFIX%weixin_tmpl` VALUES ('125','贷款还款提醒','a:2:{s:5:\"first\";s:18:\"贷款还款提醒\";s:6:\"remark\";a:2:{s:5:\"value\";s:51:\"如非您本人操作，请尽快查明原因哦。\";s:5:\"color\";s:7:\"#173177\";}}','yMglfGUi2zSCHDKaClBhXDVxQvuZYj4FqV9Np6g9w9k','OPENTM400811886','0','8');
INSERT INTO `%DB_PREFIX%weixin_tmpl` VALUES ('126','成功获取额度通知','a:2:{s:5:\"first\";s:24:\"成功获取额度通知\";s:6:\"remark\";a:2:{s:5:\"value\";s:36:\"有任何疑问，请致电客服。\";s:5:\"color\";s:7:\"#173177\";}}','DE9nCzP5xWpF9zvAPJFj3NnWFE0DI9QBexrmbk-Dfu0','OPENTM207582660','0','8');
INSERT INTO `%DB_PREFIX%weixin_tmpl` VALUES ('127','提现成功通知','a:2:{s:5:\"first\";s:18:\"提现成功通知\";s:6:\"remark\";a:2:{s:5:\"value\";s:36:\"有任何疑问，请致电客服。\";s:5:\"color\";s:7:\"#173177\";}}','gQj-c-o99MkIO1T0-js_JkbmzWfG6voVkvHmxkMZjUA','TM00980','0','8');
INSERT INTO `%DB_PREFIX%weixin_tmpl` VALUES ('129','审核结果通知','a:2:{s:5:\"first\";s:18:\"审核结果通知\";s:6:\"remark\";a:2:{s:5:\"value\";s:18:\"点此了解详情\";s:5:\"color\";s:7:\"#173177\";}}','EjxIHm8H0rT-CCJT67uTdzRYBr-2oipscSZSg6RBfQ4','OPENTM204320762','0','8');
INSERT INTO `%DB_PREFIX%weixin_tmpl` VALUES ('130','还款成功通知','a:2:{s:5:\"first\";s:18:\"还款成功通知\";s:6:\"remark\";a:2:{s:5:\"value\";s:36:\"有任何疑问，请致电客服。\";s:5:\"color\";s:7:\"#173177\";}}','OseSNGAMF6LVkA3SOPrbccWtE9T4k9lltLd6BtNYPns','OPENTM207128740','0','8');
INSERT INTO `%DB_PREFIX%weixin_tmpl` VALUES ('131','还款信息通知','a:2:{s:5:\"first\";s:18:\"还款信息通知\";s:6:\"remark\";a:2:{s:5:\"value\";s:36:\"有任何疑问，请致电客服。\";s:5:\"color\";s:7:\"#173177\";}}','i8bE8psWUpyoJJetBDrw9-BkTMMc2YwIHn4QnpNnsRw','OPENTM205975633','0','8');
DROP TABLE IF EXISTS `%DB_PREFIX%weixin_user`;
CREATE TABLE `%DB_PREFIX%weixin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '会员ID',
  `account_id` int(11) NOT NULL,
  `subscribe` tinyint(1) NOT NULL COMMENT '用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。',
  `openid` varchar(255) NOT NULL COMMENT '用户的标识，对当前公众号唯一',
  `nickname` varchar(255) NOT NULL,
  `sex` tinyint(1) NOT NULL COMMENT '用户的性别，值为1时是男性，值为2时是女性，值为0时是未知',
  `city` varchar(255) NOT NULL,
  `country` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `language` varchar(20) DEFAULT NULL,
  `headimgurl` varchar(255) DEFAULT NULL,
  `subscribe_time` varchar(255) DEFAULT NULL COMMENT '用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间',
  `unionid` varchar(255) DEFAULT NULL COMMENT '只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。',
  `remark` varchar(255) DEFAULT NULL COMMENT '公众号运营者对粉丝的备注，公众号运营者可在微信公众平台用户管理界面对粉丝添加备注',
  `groupid` int(11) DEFAULT NULL COMMENT '用户所在的分组ID',
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='//微信公众号会员列表';
DROP TABLE IF EXISTS `%DB_PREFIX%yeepay_bind_bank_card`;
CREATE TABLE `%DB_PREFIX%yeepay_bind_bank_card` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `requestNo` int(10) NOT NULL DEFAULT '0' COMMENT 'yeepay_log.id',
  `platformUserNo` int(11) NOT NULL DEFAULT '0' COMMENT '%DB_PREFIX%user.id',
  `platformNo` varchar(20) NOT NULL,
  `bankCardNo` varchar(50) NOT NULL DEFAULT '' COMMENT '绑定的卡号',
  `bank` varchar(20) NOT NULL DEFAULT '' COMMENT '卡的开户行',
  `cardStatus` varchar(20) NOT NULL COMMENT '卡的状态VERIFYING 认证中 VERIFIED 已认证',
  `is_callback` tinyint(1) NOT NULL DEFAULT '0',
  `bizType` varchar(50) DEFAULT NULL COMMENT '业务名称',
  `code` varchar(50) DEFAULT NULL COMMENT '返回码;1 成功 0 失败 2 xml参数格式错误 3 签名验证失败 101 引用了不存在的对象（例如错误的订单号） 102 业务状态不正确 103 由于业务限制导致业务不能执行 104 实名认证失败',
  `message` varchar(255) DEFAULT NULL COMMENT '描述异常信息',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`,`requestNo`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%yeepay_cp_transaction`;
CREATE TABLE `%DB_PREFIX%yeepay_cp_transaction` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `requestNo` int(10) NOT NULL DEFAULT '0' COMMENT 'yeepay_log.id',
  `platformNo` varchar(20) NOT NULL,
  `platformUserNo` int(11) NOT NULL DEFAULT '0' COMMENT '%DB_PREFIX%user.id',
  `userType` varchar(20) NOT NULL DEFAULT 'MEMBER' COMMENT '出款人用户类型，目前只支持传入 MEMBER\r\nMEMBER 个人会员 MERCHANT 商户 ',
  `bizType` varchar(50) NOT NULL COMMENT 'TENDER 投标 REPAYMENT 还款 CREDIT_ASSIGNMENT 债权转让 TRANSFER 转账 COMMISSION 分润，仅在资金转账明细中使用',
  `expired` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '超过此时间即不允许提交订单',
  `tenderOrderNo` int(11) DEFAULT '0' COMMENT '项目编号',
  `tenderName` varchar(255) DEFAULT NULL COMMENT '项目名称 ',
  `tenderAmount` decimal(20,2) DEFAULT NULL COMMENT '项目金额',
  `tenderDescription` varchar(255) DEFAULT NULL COMMENT '项目描述信息',
  `borrowerPlatformUserNo` int(11) DEFAULT NULL COMMENT '项目的借款人平台用户编号',
  `originalRequestNo` int(11) DEFAULT NULL COMMENT '需要转让的投资记录流水号',
  `paymentAmount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '投标金额',
  `details` text COMMENT '资金明细记录',
  `extend` text COMMENT '业务扩展属性，根据业务类型的不同，需要传入不同的参数。',
  `transfer_id` int(11) NOT NULL DEFAULT '0' COMMENT '债权转让id %DB_PREFIX%deal_load_transfer.id',
  `is_callback` tinyint(1) NOT NULL DEFAULT '0',
  `is_complete_transaction` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is_callback=1时，才生效;判断是否已经完成转帐',
  `code` varchar(50) DEFAULT NULL COMMENT '返回码;1 成功 0 失败 2 xml参数格式错误 3 签名验证失败 101 引用了不存在的对象（例如错误的订单号） 102 业务状态不正确 103 由于业务限制导致业务不能执行 104 实名认证失败',
  `message` varchar(255) DEFAULT NULL COMMENT '描述异常信息',
  `description` varchar(255) DEFAULT NULL,
  `deal_repay_id` int(11) DEFAULT NULL COMMENT '还款计划ID',
  `fee` decimal(20,2) DEFAULT NULL,
  `repay_start_time` varchar(50) DEFAULT NULL COMMENT '记录还款时间',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL COMMENT '易宝处理时间',
  `ecv_id` int(11) NOT NULL,
  PRIMARY KEY (`id`,`requestNo`)
) ENGINE=InnoDB AUTO_INCREMENT=1300094 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
