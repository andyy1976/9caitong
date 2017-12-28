-- fanwe SQL Dump Program
-- nginx/1.4.4
-- 
-- DATE : 2016-08-01 09:23:37
-- MYSQL SERVER VERSION : 5.6.29
-- PHP VERSION : fpm-fcgi
-- Vol : 3


DROP TABLE IF EXISTS `%DB_PREFIX%ips_create_new_acct`;
CREATE TABLE `%DB_PREFIX%ips_create_new_acct` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `user_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:普通用户%DB_PREFIX%user.id;1:担保用户%DB_PREFIX%deal_agency.id',
  `argMerCode` varchar(6) NOT NULL COMMENT '“平台”账号 否 由IPS颁发的商户号 ',
  `pMerBillNo` varchar(30) NOT NULL DEFAULT '0' COMMENT 'pMerBillNo商户开户流水号 否 商户系统唯一丌重复 针对用户在开户中途中断（开户未完成，但关闭了IPS开 户界面）时，必须重新以相同的商户订单号发起再次开户 ',
  `pIdentType` tinyint(2) DEFAULT '1' COMMENT '证件类型 否 1#身份证，默认：1',
  `pIdentNo` varchar(20) DEFAULT NULL COMMENT '证件号码 否 真实身份证 ',
  `pRealName` varchar(30) DEFAULT NULL COMMENT '姓名 否 真实姓名（中文） ',
  `pMobileNo` varchar(11) DEFAULT NULL COMMENT '手机号 否 用户发送短信 ',
  `pEmail` varchar(50) DEFAULT NULL COMMENT '注册邮箱 否 用于登录账号，IPS系统内唯一丌能重复',
  `pSmDate` date DEFAULT NULL COMMENT '提交日期 否 时间格式“yyyyMMdd”,商户提交日期,。如：20140323 ',
  `pMemo1` varchar(100) DEFAULT NULL COMMENT '备注 是/否',
  `pMemo2` varchar(100) DEFAULT NULL COMMENT '备注 是/否',
  `pMemo3` varchar(100) DEFAULT NULL COMMENT '备注 是/否',
  `pStatus` tinyint(2) DEFAULT NULL COMMENT '开户状态 否 状态：10#开户成功，5#注册超时，9#开户失败',
  `pBankName` varchar(64) DEFAULT NULL COMMENT '银行名称 是/否 pErrCode 返回状态为 MG00000F 时返回，用 户在IPS登记的信息 ',
  `pBkAccName` varchar(50) DEFAULT NULL COMMENT '户名 是/否 pErrCode 返回状态为 MG00000F 时返回，用 户在IPS登记的信息不姓名一致。',
  `pBkAccNo` varchar(4) DEFAULT NULL COMMENT '银行卡账号 是/否 pErrCode 返回状态为 MG00000F 时返回，用 户在IPS登记的信息。返回卡号后4位。 ',
  `pCardStatus` tinyint(1) DEFAULT NULL COMMENT '身份证状态 是/否 pErrCode 返回状态为MG00000F时返回。 是否验证成功 F 未验证，Y 验证通过 验证丌 通过 ',
  `pPhStatus` tinyint(1) DEFAULT NULL COMMENT '手机状态 是/否 pErrCode 返回状态为MG00000F时返回 是否验证成功： F未验 ，Y 验证通过，N验证 丌通过 ',
  `pIpsAcctNo` varchar(30) DEFAULT NULL COMMENT 'IPS托管平台账 户号 是/否 pErrCode 返回状态为 MG00000F 时返回，由 IPS生成颁发的资金账号。 ',
  `pIpsAcctDate` date DEFAULT NULL COMMENT 'IPS开户日期 否 pErrCode 返回状态为 MG00000F 时返回，格 式：yyyymmdd ',
  `pMerCode` varchar(6) DEFAULT NULL COMMENT '“平台”账号 否 由IPS颁发的商户号 ',
  `pErrCode` varchar(8) DEFAULT NULL COMMENT '处理返回状态 否 MG00000F 操作成功； 其他错误：参考自定义错误码 ',
  `pErrMsg` varchar(255) DEFAULT NULL COMMENT '返回信息 是/否 MG00000F 操作成功； 其他错误信息：参考自定义错误码 ',
  `is_callback` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未回调处理;1:已回调处理',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=336 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%ips_do_dp_trade`;
CREATE TABLE `%DB_PREFIX%ips_do_dp_trade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` tinyint(1) NOT NULL COMMENT '0:普通用户%DB_PREFIX%user.id;1:担保用户%DB_PREFIX%deal_agency.id',
  `user_id` int(11) NOT NULL,
  `pMerCode` varchar(6) DEFAULT NULL COMMENT '“平台”账号 否 由IPS颁发的商户号 ',
  `pMerBillNo` varchar(30) DEFAULT NULL COMMENT '商户充值订单号 否 商户系统唯一不重复',
  `pAcctType` tinyint(1) NOT NULL DEFAULT '1' COMMENT '账户类型 否 固定值为 1，表示为类型为IPS个人账户',
  `pIdentNo` varchar(20) DEFAULT NULL COMMENT '证件号码 否 真实身份证（个人）/IPS颁发的商户号（商户） 本期考虑个人，商户充值预留，下期增加 ',
  `pRealName` varchar(30) DEFAULT NULL COMMENT '姓名 否 真实姓名（中文） pIpsAcctNo 30 IPS托管账户号 否 账户类型为1时，IPS托管账户号（个人） ',
  `pIpsAcctNo` varchar(30) DEFAULT NULL COMMENT 'IPS托管账户号 账户类型为1时，IPS托管账户号（个人）',
  `pTrdDate` date DEFAULT NULL COMMENT '充值日期 否 格式：YYYYMMDD ',
  `pTrdAmt` decimal(11,2) DEFAULT '0.00' COMMENT '充值金额 否 金额单位：元，丌能为负，丌允许为0，保留2位小数； 格式：12.00 ',
  `pChannelType` tinyint(1) DEFAULT '1' COMMENT '充值渠道种类 否 1#网银充值；2#代扣充值 ',
  `pTrdBnkCode` varchar(5) DEFAULT NULL COMMENT '充值银行 是/否 网银充值的银行列表由IPS提供，对应充值银行的CODE， 具体使用见接口 <<商户端获取银行列表查询(WS)>>， 获取pBankList内容项中“银行卡编号”字段； 代扣充值这里传空； ',
  `pMerFee` decimal(11,2) DEFAULT '0.00' COMMENT '平台手续费 否 这里是平台向用户收取的费用 金额单位：元，丌能为负，允许为0，保留2位小数； 格式：12.00 ',
  `pIpsFeeType` tinyint(1) DEFAULT NULL COMMENT '谁付IPS手续费 否 这里是IPS向平台收取的费用 1：平台支付 2：用户支付 ',
  `pMemo1` varchar(100) DEFAULT NULL,
  `pMemo2` varchar(100) DEFAULT NULL,
  `pMemo3` varchar(100) DEFAULT NULL,
  `pIpsBillNo` varchar(30) DEFAULT NULL COMMENT 'IPS充值订单号 否 由IPS系统生成的唯一流水号',
  `pErrCode` varchar(8) DEFAULT NULL COMMENT '充值状态 否 MG00000F 操作成功； MG00008F IPS受理中; 其他错误信息：参考自定义错误码',
  `pErrMsg` varchar(100) DEFAULT NULL COMMENT '返回信息 是/否 MG00000F 操作成功； MG00008F IPS受理中; 其他错误信息：参考自定义错误码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%ips_do_dw_trade`;
CREATE TABLE `%DB_PREFIX%ips_do_dw_trade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:普通用户%DB_PREFIX%user.id;1:担保用户%DB_PREFIX%deal_agency.id',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `pMerCode` varchar(6) NOT NULL COMMENT '“平台”账号 否 由IPS颁发的商户号',
  `pMerBillNo` varchar(30) NOT NULL COMMENT '商户提现订单号商户系统唯一不重复',
  `pAcctType` tinyint(1) DEFAULT '1' COMMENT '账户类型 否 0#机构（暂未开放） ；1#个人',
  `pOutType` tinyint(1) DEFAULT NULL COMMENT '提现模式 否 1#普通提现；2#定向提现<暂不开放> ',
  `pBidNo` varchar(30) DEFAULT NULL COMMENT '标号 是/否 提现模式为2时，此字段生效 内容是投标时的标号',
  `pContractNo` varchar(30) DEFAULT NULL COMMENT '合同号 是/否 提现模式为2时，此字段生效 内容是投标时的合同号',
  `pDwTo` varchar(30) DEFAULT NULL COMMENT '提现去向 是/否 提现模式为2时，此字段生效 上送IPS托管账户号（个人/商户号）',
  `pIdentNo` varchar(20) DEFAULT NULL COMMENT '证件号码 否 真实身份证（个人）/由IPS颁发的商户号（商户）',
  `pRealName` varchar(30) DEFAULT NULL COMMENT '姓名 否 真实姓名（中文） ',
  `pIpsAcctNo` varchar(30) DEFAULT NULL COMMENT 'IPS账户号 否 账户类型为1时，IPS个人托管账户号 账户类型为0时，由IPS颁发的商户号',
  `pDwDate` date DEFAULT NULL COMMENT '提现日期 否 格式：YYYYMMDD ',
  `pTrdAmt` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '提现金额 否 金额单位，不能为负，不允许为0 ',
  `pMerFee` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '平台手续费 否 金额单位，不能为负，允许为0 这里是平台向用户收取的费用 ',
  `pIpsFeeType` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'IPS手续费收取方 否 这里是IPS收取的费用 1：平台支付 2：提现方支付',
  `pIpsBillNo` varchar(30) DEFAULT NULL,
  `is_callback` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未回调处理;1:已回调处理',
  `pErrCode` varchar(8) DEFAULT NULL COMMENT 'MG00000F ?操作成功',
  `pErrMsg` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%ips_guarantee_unfreeze`;
CREATE TABLE `%DB_PREFIX%ips_guarantee_unfreeze` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL DEFAULT '0',
  `pMerCode` varchar(6) NOT NULL COMMENT '“平台”账号 否 由IPS颁发的商户号 ',
  `pMerBillNo` varchar(30) NOT NULL DEFAULT '0' COMMENT '商户系统唯一丌重复',
  `pBidNo` varchar(30) NOT NULL DEFAULT '0' COMMENT '标的号，商户系统唯一丌重复',
  `pUnfreezeDate` date DEFAULT NULL COMMENT '解冻日期格 式：yyyymmdd ',
  `pUnfreezeAmt` decimal(11,2) DEFAULT '0.00' COMMENT '解冻金额 金额单位，丌能为负，丌允许为0 累计解冻金额  <= 当时冻结时的保证金',
  `pUnfreezenType` tinyint(1) DEFAULT '1' COMMENT '解冻类型 否 1#解冻借款方；2#解冻担保方',
  `pAcctType` tinyint(1) DEFAULT '1' COMMENT '解冻者账户类型 否 0#机构；1#个人',
  `pIdentNo` varchar(20) DEFAULT NULL COMMENT '解冻者证件号码 是/否 解冻者账户类型1时：真实身份证（个人），必填 解冻账户类型0时：为空处理',
  `pRealName` varchar(30) DEFAULT NULL COMMENT '解冻者姓名 否 账户类型为1时，真实姓名（中文） 账户类型为0时，开户时在IPS登记的商户名称 ',
  `pIpsAcctNo` varchar(30) DEFAULT NULL COMMENT '解冻者IPS账号 否 账户类型为1时，IPS个人托管账户号 账户类型为0时，由IPS颁发的商户号 ',
  `pIpsBillNo` varchar(30) DEFAULT NULL COMMENT '由IPS系统生成的唯一流水号',
  `pIpsTime` datetime DEFAULT NULL COMMENT 'IPS处理时间 否 格式为：yyyyMMddHHmmss',
  `pErrCode` varchar(8) DEFAULT NULL COMMENT '处理返回状态 否 MG00000F 操作成功； 其他错误：参考自定义错误码 ',
  `pErrMsg` varchar(255) DEFAULT NULL COMMENT '返回信息 是/否 MG00000F 操作成功； 其他错误信息：参考自定义错误码 ',
  `is_callback` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未回调处理;1:已回调处理',
  PRIMARY KEY (`id`,`pMerBillNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%ips_log`;
CREATE TABLE `%DB_PREFIX%ips_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `create_date` datetime NOT NULL,
  `strxml` text,
  `html` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=231 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%ips_register_creditor`;
CREATE TABLE `%DB_PREFIX%ips_register_creditor` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `pMerCode` int(6) NOT NULL,
  `pMerBillNo` varchar(30) NOT NULL COMMENT '商户订单号 否 商户系统唯一不重复 ',
  `pMerDate` date DEFAULT NULL COMMENT '商户日期 否 格式：YYYYMMDD ',
  `pBidNo` varchar(30) DEFAULT NULL COMMENT '标的号 否 字母和数字，如a~z,A~Z,0~9',
  `pContractNo` varchar(30) DEFAULT NULL COMMENT '合同号 否 字母和数字，如a~z,A~Z,0~9',
  `pRegType` tinyint(1) DEFAULT NULL COMMENT '登记方式 否 1：手劢投标  2：自劢投标',
  `pAuthNo` varchar(30) DEFAULT NULL COMMENT '授权号 是/否  字母和数字，如a~z,A~Z,0~9 登记方式为1时，为空 登记方式为2时，填写该投资人自劢投标签约时IPS向平 台接口返回的“pIpsAuthNo 授权号” （详见自劢投标签 约） ',
  `pAuthAmt` decimal(11,2) DEFAULT '0.00' COMMENT '债权面额 否 金额单位元，不能为负，不允许为0 ',
  `pTrdAmt` decimal(11,2) DEFAULT '0.00' COMMENT '交易金额 否 金额单位元，不能为负，不允许为0 债权面额等于交易金额 ',
  `pFee` decimal(11,2) DEFAULT '0.00' COMMENT '投资人手续费 否 金额单位元，不能为负，允许为0 ',
  `pAcctType` tinyint(1) DEFAULT '1' COMMENT '账户类型 否 0#机构（暂未开放） ；1#个人 ',
  `pIdentNo` varchar(20) DEFAULT NULL COMMENT '证件号码 否 真实身份证（个人）/由IPS颁发的商户号',
  `pRealName` varchar(30) DEFAULT NULL COMMENT '姓名 否 真实姓名（中文）',
  `pAccount` varchar(30) DEFAULT NULL COMMENT '投资人账户 否 账户类型为1时，IPS托管账户号（个人） 账户类型为0时，由IPS颁发的商户号',
  `pUse` varchar(100) DEFAULT NULL COMMENT '借款用途 否 借款用途 ',
  `pMemo1` varchar(100) DEFAULT NULL COMMENT '备注',
  `pMemo2` varchar(100) DEFAULT NULL COMMENT '备注',
  `pMemo3` varchar(100) DEFAULT NULL COMMENT '备注',
  `pAccountDealNo` varchar(20) DEFAULT NULL COMMENT '投资人编号 否 IPS返回的投资人编号 ',
  `pBidDealNo` varchar(30) DEFAULT NULL COMMENT '标的编号 否 IPS返回的标的编号',
  `pBusiType` tinyint(1) DEFAULT NULL COMMENT '业务类型 否 返回1，代表投标',
  `pTransferAmt` decimal(11,2) DEFAULT NULL COMMENT '实际冻结金额 否 实际冻结金额',
  `pStatus` tinyint(2) DEFAULT '0' COMMENT '债权人状态 否 0：新增 1：?行中 10：结束',
  `pP2PBillNo` varchar(30) DEFAULT NULL COMMENT 'IPS P2P订单号 否 由IPS系统生成的唯一流水号',
  `pIpsTime` datetime DEFAULT NULL COMMENT 'IPS处理时间 否 格式为：yyyyMMddHHmmss',
  `is_callback` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未回调处理;1:已回调处理',
  `pErrCode` varchar(8) DEFAULT NULL,
  `pErrMsg` varchar(100) DEFAULT NULL,
  `create_time` int(11) NOT NULL,
  `ecv_id` int(11) NOT NULL,
  PRIMARY KEY (`id`,`pMerBillNo`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%ips_register_cretansfer`;
CREATE TABLE `%DB_PREFIX%ips_register_cretansfer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `is_callback` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未回调处理;1:已回调处理',
  `t_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '受让用户ID',
  `transfer_id` int(11) NOT NULL DEFAULT '0',
  `pMerCode` varchar(30) NOT NULL COMMENT '“平台”账 号 由IPS颁发的商户号',
  `pMerBillNo` varchar(30) NOT NULL DEFAULT '' COMMENT '商户订单号 否 商户系统唯一不重复',
  `pMerDate` date DEFAULT NULL COMMENT '商户日期 否 格式：YYYYMMDD ',
  `pBidNo` varchar(30) DEFAULT NULL COMMENT '标的号 否 原投资交易的标的号，字母和数字，如a~z,A~Z,0~9 ',
  `pContractNo` varchar(30) DEFAULT NULL COMMENT '合同号 否 原投资交易的合同号， 字母和数字，如a~z,A~Z,0~9 ',
  `pFromAccountType` tinyint(1) DEFAULT NULL COMMENT '出让方账户类型 否 0：机构（暂不支持） 1：个人 ',
  `pFromName` varchar(30) DEFAULT NULL COMMENT '出让方账户姓名 否 出让方账户真实姓名',
  `pFromAccount` varchar(50) DEFAULT NULL COMMENT '出让方账户 否 出让方账户类型为1时，IPS托管账户号（个人） 出让方账户类型为0时，由IPS颁发的商户号 ',
  `pFromIdentType` tinyint(2) DEFAULT '1' COMMENT '出让方证件类型 否 1#身份证，默认：1 ',
  `pFromIdentNo` varchar(20) DEFAULT NULL COMMENT '出让方证件号码 否 真实身份证（个人）/由IPS颁发的商户号（机构）',
  `pToAccountType` tinyint(1) DEFAULT NULL COMMENT '受让方账户类型 否 1：个人  0：机构（暂不支持）',
  `pToAccountName` varchar(30) DEFAULT NULL COMMENT '受让方账户姓名 否 受让方账户真实姓名 ',
  `pToAccount` varchar(30) DEFAULT NULL COMMENT '受让方账户 否 受让方账户类型为1时，IPS托管账户号（个人）',
  `pToIdentType` tinyint(2) DEFAULT '1' COMMENT '受让方证件类型 否 1#身份证，默讣：1 ',
  `pToIdentNo` varchar(20) DEFAULT NULL COMMENT '受让方证件号码 否 真实身份证（个人）/由IPS颁发的商户号（机构）',
  `pCreMerBillNo` varchar(30) DEFAULT NULL COMMENT '登记债权人时提 交的订单号 否 字母和数字，如a~z,A~Z,0~9 登记债权人时提交的订单号，见<登记债权人接口>请求 参数中的“pMerBillNo” ',
  `pCretAmt` decimal(11,2) DEFAULT '0.00' COMMENT '债权面额 否 金额单位元，不能为负，不允许为0 ',
  `pPayAmt` decimal(11,2) DEFAULT '0.00' COMMENT '支付金额 否 金额单位元，不能为负，不允许为0 债权面额（1-30%）<=支付金额<= 债权面额（1+30%） ',
  `pFromFee` decimal(11,2) DEFAULT '0.00' COMMENT '出让方手续费 否 金额单位元，不能为负，允许为0 ',
  `pToFee` decimal(11,2) DEFAULT '0.00' COMMENT '受让方手续费 否 金额单位元，不能为负，允许为0 ',
  `pCretType` tinyint(1) DEFAULT '1' COMMENT '转让类型 否 1：全部转让 2：部分转让',
  `pMemo1` varchar(100) DEFAULT NULL COMMENT '备注',
  `pMemo2` varchar(100) DEFAULT NULL COMMENT '备注',
  `pMemo3` varchar(100) DEFAULT NULL COMMENT '备注',
  `pErrCode` varchar(8) DEFAULT NULL COMMENT '处理返回状态 否 MG00000F 操作成功； 其他错误信息：参考自定义错误码',
  `pErrMsg` varchar(100) DEFAULT NULL COMMENT '返回信息 是/否 MG00000F 操作成功； 其他错误信息：参考自定义错误码',
  `pP2PBillNo` varchar(30) DEFAULT NULL COMMENT '债权转让编号 否 IPS返回的债权转让编号',
  `pIpsTime` datetime DEFAULT NULL COMMENT 'IPS处理时间 否 格式为：yyyyMMddHHmmss ',
  `pBussType` tinyint(1) DEFAULT '1' COMMENT '业务类型 否 1：债权转让',
  `pStatus` tinyint(1) DEFAULT '1' COMMENT '转让状态 否 0：新建 1：?行中 10：成功  9： 失败 ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%ips_register_guarantor`;
CREATE TABLE `%DB_PREFIX%ips_register_guarantor` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `pMerCode` varchar(6) DEFAULT NULL COMMENT '“平台”账号 否 由IPS颁发的商户号',
  `pMerBillNo` varchar(30) NOT NULL DEFAULT '' COMMENT '商户订单号 否 商户系统唯一不重复',
  `pMerDate` date DEFAULT NULL COMMENT '商户日期 否 格式：yyyyMMdd ',
  `pBidNo` varchar(30) DEFAULT NULL COMMENT '标的号 否 字母和数字，如a~z,A~Z,0~9',
  `pAmount` decimal(11,2) DEFAULT '0.00' COMMENT '担保金额 否 金额单位元，不能为负，不允许为0 担保人针对该合同标的承诺的最高赔付金额 ',
  `pMarginAmt` decimal(11,2) DEFAULT '0.00' COMMENT '担保保证金 否 金额单位元，不能为负，允许为0 担保人针对该合同标的被冻结的金额',
  `pProFitAmt` decimal(11,2) DEFAULT '0.00' COMMENT '担保收益 否 金额单位元，不能为负，允许为0 ',
  `pAcctType` tinyint(1) DEFAULT '0' COMMENT '担保方类型 否 0#机构；1#个人 ',
  `pFromIdentNo` varchar(20) DEFAULT NULL COMMENT '担保方证件号码 否 针对担保方类型为1时：真实身份证（个人） 针对担保方类型为0时：由IPS颁发的商户号 ',
  `pAccountName` varchar(30) DEFAULT NULL COMMENT '担保方账户姓名 否 针对担保方类型为1时：担保方账户真实姓名 针对担保方类型为0时：在IPS开户时登记的商户名称',
  `pAccount` varchar(30) DEFAULT NULL COMMENT '担保方账户 否 担保方类型为1时，IPS托管账户号（个人） 担保方类型为0时，由IPS颁发的商户号 ',
  `pMemo1` varchar(100) DEFAULT NULL,
  `pMemo2` varchar(100) DEFAULT NULL,
  `pMemo3` varchar(100) DEFAULT NULL,
  `pP2PBillNo` varchar(30) DEFAULT NULL COMMENT '担保方编号 否 IPS返回的担保人编号 ',
  `pRealFreezeAmt` decimal(11,2) DEFAULT '0.00' COMMENT '实际冻结金额  IPS返回的担保保证金 ',
  `pCompenAmt` decimal(11,2) DEFAULT '0.00' COMMENT '已代偿金额  IPS返回的担保金额 ',
  `pIpsTime` datetime DEFAULT NULL COMMENT 'IPS处理时间  格式为：yyyyMMddHHmmss ',
  `pStatus` tinyint(2) DEFAULT NULL COMMENT '担保状态 否 0：新增  1：?行中  10：结束  9：失败',
  `pErrCode` varchar(8) DEFAULT NULL COMMENT '处理返回状态 否 MG00000F 操作成功； 其他错误信息：参考自定义错误码 ',
  `pErrMsg` varchar(100) DEFAULT NULL COMMENT '返回信息 是/否 MG00000F 操作成功； 其他错误信',
  `is_callback` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未回调处理;1:已回调处理',
  PRIMARY KEY (`id`,`pMerBillNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%ips_register_subject`;
CREATE TABLE `%DB_PREFIX%ips_register_subject` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pMerCode` int(6) NOT NULL DEFAULT '0' COMMENT '“平台”账号 否 由IPS颁发的商户号 ',
  `deal_id` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:新增; 1:标的正常结束; 2:流标结束',
  `pMerBillNo` varchar(30) NOT NULL COMMENT '商户订单号 否 商户系统唯一不重复',
  `pBidNo` varchar(30) NOT NULL COMMENT '标的号，商户系统唯一不重复 ',
  `pRegDate` date DEFAULT NULL COMMENT '商户日期 否 格式：YYYYMMDD ',
  `pLendAmt` decimal(11,2) DEFAULT '0.00' COMMENT '借款金额 否 金额单位，丌能为负，丌允许为0； 借款金额  <= 10000.00万 关于N(9,2)见4.1补充说明 ',
  `pGuaranteesAmt` decimal(11,2) DEFAULT '0.00' COMMENT '借款保证金，允许冻结的金额，金额单位，丌能为负，允 许为0； 借款保证金  <= 10000.00万 ',
  `pTrdLendRate` decimal(11,2) DEFAULT '0.00' COMMENT '借款利率 否 金额单位，丌能为负，允许为0； 借款利率  < 48%，例如：45.12%传入 45.12 ',
  `pTrdCycleType` tinyint(2) DEFAULT NULL COMMENT '借款周期类型 否 借款周期类型，1：天；3：月； 借款周期 <= 5年',
  `pTrdCycleValue` int(5) DEFAULT '0' COMMENT '借款周期值 否 借款周期值 借款周期 <= 5年。 如果借款周期类型为天，则借款周期值<= 1800(360 * 5)；如果借款周期类型为月，则借款周期值<= 60(12 * 5) ',
  `pLendPurpose` varchar(100) DEFAULT NULL COMMENT '借款用途',
  `pRepayMode` tinyint(2) DEFAULT NULL COMMENT '还款方式，1：等额本息，2：按月还息到期还本；3：等 额本金；99：其他； ',
  `pOperationType` tinyint(2) DEFAULT NULL COMMENT '标的操作类型，1：新增，2：结束 “新增”代表新增标的，“结束”代表标的正常还清、丌 需要再还款戒者标的流标等情况。标的“结束”后，投资 人投标冻结金额、担保方保证金、借款人保证金均自劢解 冻。 ',
  `pLendFee` decimal(11,2) DEFAULT '0.00' COMMENT '借款人手续费 否 金额单位，丌能为负，允许为0 这里是平台向借款人收取的费用 ',
  `pAcctType` tinyint(1) DEFAULT '1' COMMENT '账户类型 否 0#机构（暂未开放） ；1#个人 ',
  `pIdentNo` varchar(20) DEFAULT NULL COMMENT '证件号码 否 真实身份证（个人）/由IPS颁发的商户号 ',
  `pRealName` varchar(30) DEFAULT NULL COMMENT '姓名 否 真实姓名（中文）',
  `pIpsAcctNo` varchar(30) DEFAULT NULL COMMENT 'IPS账户号 否 账户类型为1时，IPS托管账户号（个人） 账户类型为0时，由IPS颁发的商户号 ',
  `pMemo1` varchar(100) DEFAULT NULL COMMENT '备注 是/否  ',
  `pMemo2` varchar(100) DEFAULT NULL,
  `pMemo3` varchar(100) DEFAULT NULL,
  `pIpsBillNo` varchar(30) DEFAULT NULL,
  `pIpsTime` datetime DEFAULT NULL COMMENT 'IPS处理时间 否 格式为：yyyyMMddHHmmss ',
  `pBidStatus` tinyint(2) DEFAULT NULL COMMENT '标的状态，1：新增；2：募集中；3：? 行中；8：结束处理中；9：失败；10：结 束；',
  `pRealFreezenAmt` decimal(11,2) DEFAULT '0.00' COMMENT '实际冻结金额，金额单位，不能为负，不允许为0； 实际冻结金额 = 保证金+手续费',
  `pErrCode` varchar(255) DEFAULT NULL COMMENT 'MG02500F标的新增；（登记标的时同步返回） ? MG02501F标的募集中；（登记标的成功后异步返回） ? MG02503F 标的结束处理中；（登记结束标的时同步返 回） ? MG02504F标的失败； ? MG02505F标的结束(登记结束标的成功后异步返回)',
  `pErrMsg` varchar(100) DEFAULT NULL COMMENT '返回信息 是/否 MG00000F 操作成功； MG02500F标的新增； MG02501F标的募集中； MG02503F标的结束处理中； MG02504F标的失败； MG02505F标的结束 其他错误信息：参考自定义错误码',
  `is_callback` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未回调处理;1:已回调处理',
  `status_msg` varchar(255) DEFAULT NULL COMMENT '主要是status_msg=2时记录的，流标原因',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%ips_repayment_new_trade`;
CREATE TABLE `%DB_PREFIX%ips_repayment_new_trade` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) NOT NULL,
  `deal_repay_id` int(11) NOT NULL DEFAULT '0' COMMENT '还款列表ID',
  `pMerCode` varchar(30) NOT NULL,
  `pMerBillNo` varchar(30) NOT NULL,
  `pBidNo` varchar(30) NOT NULL COMMENT '标号  ? 字母和数字，如a~z,A~Z,0~9',
  `pRepaymentDate` date NOT NULL COMMENT '还款日期 ? 格式：YYYYMMDD ',
  `pRepayType` tinyint(1) NOT NULL DEFAULT '1' COMMENT '还款类型，1#手动还款，2#自动还款',
  `pIpsAuthNo` varchar(30) NOT NULL COMMENT '授权号 ? 是/否 ? 当还款类型为自动还款时不为空，为手动还款时为空',
  `pOutAcctNo` varchar(30) NOT NULL COMMENT '转出方IPS账号 ? 否 ? 借款人在IPS注册的资金托管账号',
  `pOutAmt` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '转出金额 ? 否 ? 表示此次还款总金额。 ? 转出金额=Sum(pInAmt) ? Sum(pInAmt)代表转入金额的合计，一个或多个 投资人时的还款金额的累加。 ? 金额单位：元，不能为负，不允许为 0，保留 2 位小 数； ? 格式：12.00 ',
  `pOutFee` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '转出方总手续费 ? 否 ? 表示此次借款人或担保人所承担的还款手续费，此手 续费由商户平台向用户收取。 ? 金额单位：元，不能为负，允许为0，保留 2位小数； ? 格式：12.00 ?pOutFee ?= ?Sum(pOutInfoFee) ? Sum(pOutInfoFee)代表转出方手续费的合计 ? ',
  `pMessage` varchar(100) DEFAULT NULL COMMENT '转入结果说明 成功与失败的说明',
  `pMemo1` varchar(100) DEFAULT NULL,
  `pMemo2` varchar(100) DEFAULT NULL,
  `pMemo3` varchar(100) DEFAULT NULL,
  `pIpsBillNo` varchar(30) DEFAULT NULL COMMENT 'IPS还款订单号  否  由 IPS 系统生成的唯一流水号， 此次还款的批次号',
  `pOutIpsFee` decimal(11,2) DEFAULT '0.00' COMMENT '收取转出方手 续费  此手续费由平台商户垫付给 IPS 的手续费',
  `pIpsDate` date DEFAULT NULL COMMENT 'IPS受理日期  否  yyyyMMdd',
  `pErrCode` varchar(8) DEFAULT NULL COMMENT '返回状态  否 MG00000F操作成功 MG00008F IPS受理中；  待处理状态。（并非此次还款成功，还款成功返回详见 4.11.4）  除此之外：参考自定义错误码',
  `pErrMsg` varchar(100) DEFAULT NULL COMMENT '接口返回信息  否  状态非 MG00000F时，反馈实际原因',
  `is_callback` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%ips_repayment_new_trade_detail`;
CREATE TABLE `%DB_PREFIX%ips_repayment_new_trade_detail` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `pCreMerBillNo` varchar(30) NOT NULL COMMENT '登记债权人时提 交的订单号 ? 否 ? 登记债权人时提交的订单号，见<登记债权人接口>请求 参数中的“pMerBillNo” ',
  `pInAcctNo` varchar(30) DEFAULT NULL COMMENT '转入方 IPS 托管 账户号 ? 否 ? 债权人在IPS注册的资金托管账号',
  `pInFee` decimal(11,2) DEFAULT '0.00' COMMENT '转入方手续费 ? 否 ? 表示此次还款债权人所承担的还款手续费，此手续费由商 户平台向用户收取。金额单位：元，不能为负，允许为0，保留2位小数； ? 格式：12.00 ?',
  `pOutInfoFee` decimal(11,2) DEFAULT '0.00' COMMENT '转出方手续费 ? 否 ? 表示此次借款人或担保人所承担的还款明细手续费，此手 续费由商户平台收取。',
  `pInAmt` decimal(11,2) DEFAULT '0.00' COMMENT '转入金额 ? 否 ? 格式：0.00 ? ?必须大于0 ?且大于转入方手续费',
  `pStatus` varchar(2) NOT NULL DEFAULT '0' COMMENT '转入状态 ? 否 ? Y#还款成功；N#还款失败',
  `pMessage` varchar(100) DEFAULT NULL COMMENT '转入结果说明 成功与失败的说明',
  `deal_load_repay_id` int(10) NOT NULL DEFAULT '0' COMMENT '对应的还款列表ID',
  `impose_money` decimal(11,2) DEFAULT '0.00',
  `repay_manage_impose_money` decimal(11,2) DEFAULT '0.00',
  `self_money` decimal(20,2) NOT NULL,
  `repay_status` tinyint(1) NOT NULL,
  `true_repay_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%ips_transfer`;
CREATE TABLE `%DB_PREFIX%ips_transfer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `deal_id` int(10) NOT NULL,
  `ref_data` varchar(200) DEFAULT NULL,
  `pMerCode` varchar(8) NOT NULL,
  `pMerBillNo` varchar(30) DEFAULT NULL COMMENT '商户订单号  否  商户系统唯一不重复',
  `pBidNo` varchar(30) DEFAULT NULL COMMENT '标的号  否  标的号，商户系统唯一不重复 ',
  `pDate` date DEFAULT NULL COMMENT '商户日期  否  格式：YYYYMMDD  ',
  `pTransferType` tinyint(2) DEFAULT NULL COMMENT '转账类型  否  转账类型  1：投资（报文提交关系，转出方：转入方=N：1），  2：代偿（报文提交关系，转出方：转入方=1：N），  3：代偿还款（报文提交关系，转出方：转入方=1：1），  4：债权转让（报文提交关系，转出方：转入方=1：1），  5：结算担保收益（报文提交关系，转出方：转入方=1： 1） ',
  `pTransferMode` tinyint(2) DEFAULT NULL COMMENT '转账方式  是  转账方式，1：逐笔入账；2：批量入账  逐笔入账：不将转账款项汇总，而是按明细交易一笔一 笔计入账户  批量入帐：针对投资，将明细交易按 1 笔汇总本金和 1 笔汇总手续费记入借款人帐户  当转账类型为“1：投资”时，可选择 1 或 2。其余交 易只能选1',
  `pErrCode` varchar(8) DEFAULT NULL COMMENT '返回状态 ? 否 ? 一、转账类型为“代偿”，“投 资”时同步返回 MG00008F ?IPS 受理中；异步再返回 MG00000F ? 操作成功； ? 二、其他转账类型 MG00000F ?操作成功； ? 其他错误信息：参考自定义错误码 ? ',
  `pErrMsg` varchar(100) DEFAULT NULL COMMENT '接口返回信息 ? 否 ? MG00000F ?操作成功； ? 其他错误信息：参考自定义错误码',
  `pIpsBillNo` varchar(30) DEFAULT NULL COMMENT 'IPS订单号  否  由 IPS系统生成的唯一流水号',
  `pIpsTime` datetime DEFAULT NULL COMMENT 'IPS处理时间  否  格式为：yyyyMMddHHmmss',
  `is_callback` tinyint(1) NOT NULL DEFAULT '0',
  `pMemo1` varchar(100) DEFAULT NULL,
  `pMemo2` varchar(100) DEFAULT NULL,
  `pMemo3` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%ips_transfer_detail`;
CREATE TABLE `%DB_PREFIX%ips_transfer_detail` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `pOriMerBillNo` varchar(30) DEFAULT NULL COMMENT '原商户订单号  否  商户系统唯一不重复  当转账类型为投资时，为登记债权人时提交的商户订单号  当转账类型为代偿时，为登记债权人时提交的商户订单号  当转账类型为代偿还款时，为代偿时提交的商户订单号  当转账类型为债权转让时，为登记债权转让时提交的商户 订单号  当转账类型为结算担保收益时，为登记担保人时提交的商 户订单号  ',
  `pTrdAmt` decimal(11,2) DEFAULT '0.00' COMMENT '转账金额  否  金额单位：元，不能为负，不允许为0，保留2位小数；  格式：12.00  转账类型，1：投资，转账金额=债权面额；  转账类型，2：代偿，转账金额=代偿金额；  转账类型，3：代偿还款，转账金额=代偿还款金额；  转账类型，4：债权转让，转账金额=登记债权转让时的 支付金额； 转账类型，5：结算担保收益，累计转账金额<=登记担保 方时的担保收益；  ',
  `pFAcctType` tinyint(1) DEFAULT '1' COMMENT '转出方账户类型  否  0#机构；1#个人',
  `pFIpsAcctNo` varchar(30) DEFAULT NULL COMMENT '转出方 IPS 托管 账户号  否  账户类型为1时，IPS个人托管账户号  账户类型为0时，由 IPS颁发的商户号  转账类型，1：投资，此为转出方（投资人）；  转账类型，2：代偿，此为转出方（担保方）；  转账类型，3：代偿还款，此为转出方（借款人）；  转账类型，4：债权转让，此为转出方（受让方）；  转账类型，5：结算担保收益，此为转出方（借款人）；  ',
  `pFTrdFee` decimal(11,2) DEFAULT NULL COMMENT '转出方明细手续 费  否  金额单位：元，不能为负，允许为0，保留2位小数；  格式：12.00  转账类型，1：投资，此为转出方（投资人）手续费；  转账类型，2：代偿，此为转出方（担保方）手续费；  转账类型，3：代偿还款，此为转出方（借款人）手续费；  转账类型，4：债权转让，此为转出方（受让方）手续费；  转账类型，5：结算担保收益，此为转出方（借款人）手 续费；  ',
  `pTAcctType` tinyint(1) DEFAULT '1' COMMENT '转入方账户类型  否  0#机构；1#个人',
  `pTIpsAcctNo` varchar(30) DEFAULT NULL COMMENT '转入方 IPS 托管 账户号  否  账户类型为1时，IPS个人托管账户号  账户类型为0时，由 IPS颁发的商户号  转账类型，1：投资，此为转入方（借款人）；  转账类型，2：代偿，此为转入方（投资人）；  转账类型，3：代偿还款，此为转入方（担保方）；  转账类型，4：债权转让，此为转入方（出让方）；  转账类型，5：结算担保收益，此为转入方（担保方）；  ',
  `pTTrdFee` decimal(11,2) DEFAULT NULL COMMENT '转入方明细手续 费  否  金额单位：元，不能为负，允许为0，保留2位小数；  格式：12.00  转账类型，1：投资，此为转入方（借款人）手续费；  转账类型，2：代偿，此为转入方（投资人）手续费；  转账类型，3：代偿还款，此为转入方（担保方）手续费；  转账类型，4：债权转让，此为转入方（出让方）手续费；  转账类型，5：结算担保收益，此为转入方（担保方）手 续费； ',
  `pIpsDetailBillNo` varchar(255) DEFAULT NULL COMMENT 'IPS明细订单号  否  IPS明细订单号',
  `pIpsDetailTime` datetime DEFAULT NULL COMMENT 'IPS明细处理时间  否  格式为：yyyyMMddHHmmss ',
  `pIpsFee` decimal(11,2) DEFAULT '0.00' COMMENT 'IPS手续费  否  IPS手续费',
  `pStatus` varchar(1) DEFAULT NULL,
  `pMessage` varchar(100) DEFAULT NULL COMMENT '转账备注  否  转账失败的原因 ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%learn`;
CREATE TABLE `%DB_PREFIX%learn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '体验金理财名称',
  `rate` varchar(10) NOT NULL COMMENT '年利率',
  `begin_time` date NOT NULL COMMENT '开始时间',
  `end_time` date NOT NULL COMMENT '结束时间',
  `time_limit` int(11) NOT NULL COMMENT '产品期限',
  `time_expire_limit` int(11) NOT NULL COMMENT '收益结算有效期限',
  `is_effect` tinyint(4) NOT NULL COMMENT '是否有效',
  `description` text NOT NULL COMMENT '描述',
  `buy_count` int(11) NOT NULL COMMENT '购买人数',
  `load_money` decimal(11,2) NOT NULL COMMENT '总投资',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='体验理财产品表';
DROP TABLE IF EXISTS `%DB_PREFIX%learn_load`;
CREATE TABLE `%DB_PREFIX%learn_load` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `learn_id` int(11) NOT NULL,
  `money` decimal(10,2) NOT NULL COMMENT '投资金额',
  `rate` varchar(10) NOT NULL COMMENT '年利率',
  `time_limit` int(11) NOT NULL COMMENT '产品期限',
  `time_expire_limit` int(11) NOT NULL COMMENT '收益结算有效期限',
  `interest` decimal(20,2) NOT NULL COMMENT '预计得到的利息   money*rate*0.01*time_limit',
  `create_time` datetime NOT NULL COMMENT '投标时间',
  `create_date` date NOT NULL COMMENT '投标时间 Ymd',
  `is_send` tinyint(11) NOT NULL COMMENT '是否已发放',
  `send_time` datetime NOT NULL COMMENT '发放时间',
  `send_date` date NOT NULL COMMENT '发放时间 Ymd',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='体验理财产品投资列表';
DROP TABLE IF EXISTS `%DB_PREFIX%learn_send_list`;
CREATE TABLE `%DB_PREFIX%learn_send_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL COMMENT '类型ID',
  `user_id` int(11) NOT NULL,
  `money` decimal(10,2) NOT NULL,
  `begin_time` date NOT NULL COMMENT '开时时间  一般是发放之日',
  `end_time` date NOT NULL COMMENT '结束时间  begin_time + time_limit',
  `is_effect` tinyint(1) NOT NULL COMMENT '是否有效',
  `is_use` tinyint(1) NOT NULL COMMENT '是否已经使用',
  `use_time` datetime NOT NULL COMMENT '使用日期',
  `use_date` date NOT NULL COMMENT '使用时间Ymd',
  `is_recycle` tinyint(1) NOT NULL COMMENT '是否收回',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='体验金发放列表';
DROP TABLE IF EXISTS `%DB_PREFIX%learn_type`;
CREATE TABLE `%DB_PREFIX%learn_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '类型名称',
  `money` decimal(10,2) NOT NULL COMMENT '体验金额度',
  `max_money` decimal(10,2) NOT NULL COMMENT '用户最高可以获得多少金额',
  `brief` text NOT NULL COMMENT '简介',
  `type` tinyint(4) NOT NULL COMMENT '类型 0注册送 1邀请送 2管理员发放',
  `is_effect` tinyint(4) NOT NULL COMMENT '是否有效',
  `begin_time` date NOT NULL COMMENT '发放开始时间',
  `end_time` date NOT NULL COMMENT '发放结束时间',
  `time_limit` int(11) NOT NULL COMMENT '过期时长  天  即发放后多久过期',
  `send_count` int(11) NOT NULL COMMENT '已发放多少张',
  `invest_type` tinyint(4) NOT NULL COMMENT '可用于投资类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='体验发放金类型';
DROP TABLE IF EXISTS `%DB_PREFIX%licai`;
CREATE TABLE `%DB_PREFIX%licai` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '产品名称',
  `licai_sn` varchar(50) NOT NULL COMMENT '编号',
  `user_id` int(10) DEFAULT '0' COMMENT '发起人【发起机构】',
  `img` varchar(255) DEFAULT NULL COMMENT '项目图片',
  `is_recommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐',
  `re_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0元;1新品上架;2当月畅销;3:本周畅销;4:限时抢购;',
  `begin_buy_date` date NOT NULL COMMENT '购买开始时间',
  `end_buy_date` date NOT NULL COMMENT '购买结束时间',
  `end_date` date NOT NULL COMMENT '项目结束时间',
  `min_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '起购金额',
  `max_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单笔最大购买限额',
  `begin_interest_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '【0:当日生效，1:次日生效，2:下个工作日生效,3下二个工作日】',
  `product_size` varchar(255) DEFAULT NULL COMMENT '产品规模',
  `risk_rank` tinyint(1) NOT NULL DEFAULT '0' COMMENT '风险等级（2高、1中、0低）',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1有效、0无效',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '理财类型（0余额宝、1固定定存、2浮动定存;3票据、4基金）',
  `description` text NOT NULL COMMENT '理财详情',
  `purchasing_time` varchar(255) DEFAULT NULL COMMENT '赎回到账时间描述',
  `rule_info` text COMMENT '规则',
  `is_trusteeship` tinyint(1) DEFAULT NULL COMMENT '是否托管 0是 1否',
  `average_income_rate` decimal(8,4) NOT NULL DEFAULT '0.0000' COMMENT 'type=0七日平均(年)收益率;type=1近三个月收益率【动态计算】',
  `per_million_revenue` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '每万元收益【动态计算】',
  `subscribing_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '累计成交总额',
  `redeming_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '累计被赎回',
  `is_deposit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否托管;1:托管;0:非托管',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `brief` varchar(255) DEFAULT NULL COMMENT '简介',
  `net_value` decimal(10,2) DEFAULT '0.00' COMMENT '最新净值',
  `fund_key` varchar(50) DEFAULT NULL COMMENT '关连的基金编号',
  `fund_type_id` int(10) NOT NULL DEFAULT '0' COMMENT '基金种类',
  `fund_brand_id` int(10) NOT NULL DEFAULT '0' COMMENT '基金品牌',
  `bank_id` int(10) NOT NULL DEFAULT '0' COMMENT '银行',
  `begin_interest_date` date DEFAULT NULL COMMENT '起息时间',
  `time_limit` int(10) DEFAULT NULL COMMENT '理财期限',
  `review_type` tinyint(1) DEFAULT NULL COMMENT '赎回到账方式: 0,发起人审核   1,网站和发起人审核 2，自动审核',
  `total_people` int(10) DEFAULT NULL COMMENT '参与人数',
  `service_fee_rate` decimal(10,4) DEFAULT NULL COMMENT '成交服务费',
  `licai_status` tinyint(1) DEFAULT NULL COMMENT '理财状态 0：预热期 1：理财期 2：提前结束 3已到期',
  `send_type` tinyint(1) DEFAULT NULL COMMENT '发放款项类型  0：自动  1：手动',
  `is_send` tinyint(1) DEFAULT NULL COMMENT '是否发放 0：否 1：是',
  `profit_way` varchar(255) DEFAULT NULL COMMENT '获取收益方式',
  `scope` varchar(255) DEFAULT NULL COMMENT '利率范围',
  `platform_rate` decimal(10,4) DEFAULT NULL COMMENT '平台收益(余额宝)',
  `site_buy_fee_rate` decimal(10,4) DEFAULT NULL COMMENT '购买手续费(余额宝)',
  `redemption_fee_rate` decimal(10,4) DEFAULT NULL COMMENT '赎回手续费(余额宝)',
  `verify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态 0：未审核  1：通过 2：不通过',
  `contract_id` int(11) DEFAULT NULL COMMENT '理财合同范本',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%licai_advance`;
CREATE TABLE `%DB_PREFIX%licai_advance` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `redempte_id` int(11) NOT NULL COMMENT '赎回ID',
  `user_id` int(11) NOT NULL COMMENT '申请人ID',
  `user_name` varchar(255) NOT NULL COMMENT '申请用户名',
  `money` decimal(10,2) NOT NULL DEFAULT '1.00' COMMENT '赎回本金',
  `earn_money` decimal(10,2) NOT NULL COMMENT '收益金额',
  `fee` decimal(10,2) NOT NULL COMMENT '赎回手续费',
  `organiser_fee` decimal(10,2) NOT NULL,
  `advance_money` decimal(10,2) NOT NULL COMMENT '垫付金额',
  `real_money` decimal(10,2) NOT NULL COMMENT '发起人账户金额和冻结资金被扣的金额',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0表示未处理 1表示通过',
  `type` tinyint(1) NOT NULL COMMENT '0 预热期赎回 1.起息时间违约赎回 2.正常到期赎回',
  `create_date` date NOT NULL COMMENT '申请时间',
  `update_date` date NOT NULL COMMENT '处理时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%licai_bank`;
CREATE TABLE `%DB_PREFIX%licai_bank` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '产品名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态1:有效;0无效',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='基金种类：\r\n全部 货币型 股票型 债券型 混合型 理财型 指数型 QDII 其他型';
DROP TABLE IF EXISTS `%DB_PREFIX%licai_dealshow`;
CREATE TABLE `%DB_PREFIX%licai_dealshow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `licai_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `sort` int(11) NOT NULL,
  `create_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%licai_fund_brand`;
CREATE TABLE `%DB_PREFIX%licai_fund_brand` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '产品名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态1:有效;0无效',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='基金品牌：\r\n全部 嘉实 鹏华 易方达 国泰 南方 建信 招商 工银瑞信 海富通 华商 中邮创业 长盛 东方\r\n';
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('1','嘉实','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('2','鹏华','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('3','易方达','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('4','国泰','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('5','南方','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('6','建信','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('7','招商','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('8','工银瑞信','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('9','海富通','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('10','华商','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('11','中邮创业','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('12','长盛','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('13','东方','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_brand` VALUES ('14','汲侵资本','1','0');
DROP TABLE IF EXISTS `%DB_PREFIX%licai_fund_type`;
CREATE TABLE `%DB_PREFIX%licai_fund_type` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '产品名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态1:有效;0无效',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='基金种类：\r\n全部 货币型 股票型 债券型 混合型 理财型 指数型 QDII 其他型';
INSERT INTO `%DB_PREFIX%licai_fund_type` VALUES ('1','货币型','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_type` VALUES ('2','股票型','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_type` VALUES ('3','债券型','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_type` VALUES ('4','混合型','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_type` VALUES ('5','理财型','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_type` VALUES ('6','指数型','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_type` VALUES ('7','QDII','1','0');
INSERT INTO `%DB_PREFIX%licai_fund_type` VALUES ('8','其他型','1','0');
DROP TABLE IF EXISTS `%DB_PREFIX%licai_history`;
CREATE TABLE `%DB_PREFIX%licai_history` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `licai_id` varchar(50) NOT NULL COMMENT '编号',
  `history_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '购买金额起',
  `net_value` decimal(10,2) NOT NULL COMMENT '当日净利',
  `rate` decimal(7,4) NOT NULL COMMENT '利率',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='基金净值列表';
DROP TABLE IF EXISTS `%DB_PREFIX%licai_holiday`;
CREATE TABLE `%DB_PREFIX%licai_holiday` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL COMMENT '年',
  `holiday` date NOT NULL COMMENT '假日',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%licai_interest`;
CREATE TABLE `%DB_PREFIX%licai_interest` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `licai_id` varchar(50) NOT NULL COMMENT '编号',
  `min_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '购买金额起',
  `max_money` decimal(10,2) NOT NULL COMMENT '购买金额起',
  `interest_rate` decimal(7,4) NOT NULL COMMENT '利息率',
  `buy_fee_rate` decimal(10,4) DEFAULT NULL COMMENT '原购买手续费',
  `site_buy_fee_rate` decimal(10,4) DEFAULT NULL COMMENT '网站购买手续费',
  `redemption_fee_rate` decimal(10,4) DEFAULT NULL COMMENT '赎回手续费',
  `before_rate` decimal(10,4) DEFAULT NULL COMMENT '预热期利率',
  `before_breach_rate` decimal(10,4) DEFAULT NULL COMMENT '预热期违约利率',
  `breach_rate` decimal(10,4) DEFAULT NULL COMMENT '正常利息 违约收益率',
  `platform_rate` decimal(10,4) DEFAULT NULL COMMENT '平台收益率',
  `freeze_bond_rate` decimal(10,4) DEFAULT NULL,
  `platform_breach_rate` decimal(10,4) DEFAULT NULL COMMENT '用户违约网站收益',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='利率列表【不同投资金额，可以获得不同的利率】';
DROP TABLE IF EXISTS `%DB_PREFIX%licai_order`;
CREATE TABLE `%DB_PREFIX%licai_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `licai_id` int(11) NOT NULL COMMENT '理财产品ID',
  `user_id` int(11) NOT NULL COMMENT '购买用户的id',
  `user_name` varchar(50) NOT NULL,
  `money` decimal(10,2) NOT NULL COMMENT '购买金额',
  `status` tinyint(1) NOT NULL COMMENT '0：未支付 1：已支付 2、部分赎回 3、已完结',
  `freeze_bond_rate` decimal(10,4) NOT NULL COMMENT '冻结保证金费率',
  `freeze_bond` decimal(10,2) NOT NULL COMMENT '冻结保证金',
  `pay_money` decimal(10,2) NOT NULL COMMENT '发放金额',
  `status_time` datetime NOT NULL COMMENT '处理时间',
  `create_time` datetime NOT NULL COMMENT '购买时间',
  `create_date` date NOT NULL COMMENT '购买年月日',
  `site_buy_fee_rate` decimal(10,4) NOT NULL COMMENT '实际申购费率',
  `site_buy_fee` decimal(10,2) NOT NULL COMMENT '实际申购费',
  `redemption_fee_rate` decimal(10,4) NOT NULL COMMENT '赎回手续费',
  `before_interest_date` date NOT NULL COMMENT '预热开始时间',
  `before_interest_enddate` date NOT NULL COMMENT '预热结束时间',
  `before_rate` decimal(10,4) NOT NULL COMMENT '预热利率',
  `before_interest` decimal(10,2) NOT NULL COMMENT '预热利息',
  `is_before_pay` tinyint(1) NOT NULL COMMENT '是否已经支付预热期手续费',
  `before_breach_rate` decimal(10,4) NOT NULL COMMENT '预热期违约利率',
  `begin_interest_type` tinyint(1) NOT NULL COMMENT '【0:当日生效，1:次日生效，2:下个工作日生效,3下二个工作日】',
  `begin_interest_date` date NOT NULL COMMENT '起息时间YMD',
  `interest_rate` decimal(10,4) NOT NULL COMMENT '利息率',
  `breach_rate` decimal(10,4) NOT NULL COMMENT '正常利息 违约收益率',
  `end_interest_date` date NOT NULL COMMENT '结束时间YMD',
  `service_fee_rate` decimal(10,4) NOT NULL COMMENT '成交服务费率',
  `service_fee` decimal(10,2) NOT NULL COMMENT '成交服务费',
  `redempte_money` decimal(10,2) DEFAULT '0.00' COMMENT '赎回金额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='理财订单表';
DROP TABLE IF EXISTS `%DB_PREFIX%licai_recommend`;
CREATE TABLE `%DB_PREFIX%licai_recommend` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `licai_id` varchar(50) NOT NULL COMMENT '编号',
  `name` varchar(255) NOT NULL COMMENT '产品名称',
  `img` varchar(255) NOT NULL COMMENT '项目图片',
  `brief` varchar(255) DEFAULT NULL COMMENT '简介',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态1:有效;0无效',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='个性推荐';
DROP TABLE IF EXISTS `%DB_PREFIX%licai_redempte`;
CREATE TABLE `%DB_PREFIX%licai_redempte` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL COMMENT '订单ID',
  `user_id` int(11) NOT NULL COMMENT '申请人ID',
  `user_name` varchar(255) NOT NULL COMMENT '申请用户名',
  `money` decimal(10,2) NOT NULL DEFAULT '1.00' COMMENT '赎回本金',
  `earn_money` decimal(10,2) NOT NULL COMMENT '收益金额',
  `fee` decimal(10,2) NOT NULL COMMENT '赎回手续费',
  `organiser_fee` decimal(10,2) NOT NULL COMMENT '平台收益',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0表示未赎回 1表示已赎回 2表示拒绝 3表示取消赎回',
  `type` tinyint(1) NOT NULL COMMENT '0 预热期赎回 1.起息时间违约赎回 2.正常到期赎回',
  `create_date` date NOT NULL COMMENT '申请时间',
  `update_date` date NOT NULL COMMENT '处理时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='赎回列表';
DROP TABLE IF EXISTS `%DB_PREFIX%link`;
CREATE TABLE `%DB_PREFIX%link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `group_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  `sort` int(11) NOT NULL,
  `img` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `count` int(11) NOT NULL,
  `show_index` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%link` VALUES ('14','百度','7','http://baidu.com','1','1','','','0','1');
DROP TABLE IF EXISTS `%DB_PREFIX%link_group`;
CREATE TABLE `%DB_PREFIX%link_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '友情链接分组名称',
  `sort` tinyint(1) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%link_group` VALUES ('7','友情链接','3','1');
INSERT INTO `%DB_PREFIX%link_group` VALUES ('8','合作伙伴','2','1');
DROP TABLE IF EXISTS `%DB_PREFIX%log`;
CREATE TABLE `%DB_PREFIX%log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_info` text NOT NULL,
  `log_time` int(11) NOT NULL,
  `log_admin` int(11) NOT NULL,
  `log_ip` varchar(255) NOT NULL,
  `log_status` tinyint(1) NOT NULL,
  `module` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14630 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%log` VALUES ('14489','admin登录成功','1468971506','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14490','更新系统配置','1468971920','1','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14491','更新系统配置','1468971940','1','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14492','admin登录成功','1468972854','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14493','首页小广告图彻底删除成功','1468974103','1','111.198.16.9','1','Adv','foreverdelete');
INSERT INTO `%DB_PREFIX%log` VALUES ('14494','理财广告位彻底删除成功','1468974149','1','111.198.16.9','1','Adv','foreverdelete');
INSERT INTO `%DB_PREFIX%log` VALUES ('14495','首页广告更新成功','1468974209','1','111.198.16.9','1','Adv','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14496','首页广告更新成功','1468974240','1','111.198.16.9','1','Adv','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14497','首页广告更新成功','1468974287','1','111.198.16.9','1','Adv','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14498','更新系统配置','1468974961','1','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14499','更新系统配置','1468975067','1','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14500','更新系统配置','1468975116','1','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14501','更新系统配置','1468975124','1','111.198.16.9','1','Conf','update_qq');
INSERT INTO `%DB_PREFIX%log` VALUES ('14502','方维短信平台安装成功','1468979729','1','111.198.16.9','1','Sms','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14503','方维短信平台启用成功','1468979738','1','111.198.16.9','1','Sms','set_effect');
INSERT INTO `%DB_PREFIX%log` VALUES ('14504','4719彻底删除成功','1468979839','1','111.198.16.9','1','DealMsgList','foreverdelete');
INSERT INTO `%DB_PREFIX%log` VALUES ('14505','清除半年前的记录','1468980914','1','111.198.16.9','1','Log','foreverdelete');
INSERT INTO `%DB_PREFIX%log` VALUES ('14506','环讯资金托管更新成功','1468980957','1','111.198.16.9','1','Collocation','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14507','环讯资金托管更新成功','1468980963','1','111.198.16.9','1','Collocation','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14508','支付宝快捷登录安装成功','1468980994','1','111.198.16.9','1','ApiLogin','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14509','支付宝通用登录安装成功','1468980998','1','111.198.16.9','1','ApiLogin','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14510','微信登录安装成功','1468981002','1','111.198.16.9','1','ApiLogin','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14511','腾讯微博登录插件安装成功','1468981006','1','111.198.16.9','1','ApiLogin','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14512','QQv2登录插件安装成功','1468981011','1','111.198.16.9','1','ApiLogin','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14513','新浪微博api登录接口安装成功','1468981015','1','111.198.16.9','1','ApiLogin','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14514','Taobao卸载成功','1468981047','1','111.198.16.9','1','ApiLogin','uninstall');
INSERT INTO `%DB_PREFIX%log` VALUES ('14515','Alipay卸载成功','1468981050','1','111.198.16.9','1','ApiLogin','uninstall');
INSERT INTO `%DB_PREFIX%log` VALUES ('14516','Weixin卸载成功','1468981053','1','111.198.16.9','1','ApiLogin','uninstall');
INSERT INTO `%DB_PREFIX%log` VALUES ('14517','Tencent卸载成功','1468981056','1','111.198.16.9','1','ApiLogin','uninstall');
INSERT INTO `%DB_PREFIX%log` VALUES ('14518','Qqv2卸载成功','1468981059','1','111.198.16.9','1','ApiLogin','uninstall');
INSERT INTO `%DB_PREFIX%log` VALUES ('14519','Sina卸载成功','1468981063','1','111.198.16.9','1','ApiLogin','uninstall');
INSERT INTO `%DB_PREFIX%log` VALUES ('14520','admin登录成功','1469052495','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14521','彻底删除成功','1469052686','1','111.198.16.9','1','MAdv','foreverdelete');
INSERT INTO `%DB_PREFIX%log` VALUES ('14522','utdm添加成功','1469052797','1','111.198.16.9','1','MAdv','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14523','utdms添加成功','1469052849','1','111.198.16.9','1','MAdv','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14524','utdms添加成功','1469052876','1','111.198.16.9','1','MAdv','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14525','彻底删除成功','1469052887','1','111.198.16.9','1','MAdv','foreverdelete');
INSERT INTO `%DB_PREFIX%log` VALUES ('14526','utdm更新成功','1469052894','1','111.198.16.9','1','MAdv','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14527','utdms更新成功','1469052908','1','111.198.16.9','1','MAdv','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14528','更新系统配置','1469053004','1','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14529','更新系统配置','1469053034','1','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14530','FW卸载成功','1469053277','1','111.198.16.9','1','Sms','uninstall');
INSERT INTO `%DB_PREFIX%log` VALUES ('14531','短信平台安装成功','1469053285','1','111.198.16.9','1','Sms','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14532','短信平台启用成功','1469053288','1','111.198.16.9','1','Sms','set_effect');
INSERT INTO `%DB_PREFIX%log` VALUES ('14533','来来1号店更新成功','1469053629','1','111.198.16.9','1','Link','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14534','更新系统配置','1469053652','1','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14535','admin登录成功','1469055764','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14536','admin登录成功','1469055783','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14537','admin登录成功','1469055963','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14538','更新系统配置','1469055989','0','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14539','root管理员帐号错误','1469056003','0','111.198.16.9','0','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14540','admin登录成功','1469056017','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14541','admin登录成功','1469059040','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14542','admin登录成功','1469063468','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14543','编号：Test贷款名称添加成功','1469067139','1','111.198.16.9','1','Deal','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14544','编号：2，Test贷款名称更新成功','1469067248','1','111.198.16.9','1','Deal','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14545','2_is_recommend启用成功','1469067260','1','111.198.16.9','1','Deal','toogle_status');
INSERT INTO `%DB_PREFIX%log` VALUES ('14546','2_is_recommend禁用成功','1469067261','1','111.198.16.9','1','Deal','toogle_status');
INSERT INTO `%DB_PREFIX%log` VALUES ('14547','2_is_recommend启用成功','1469067261','1','111.198.16.9','1','Deal','toogle_status');
INSERT INTO `%DB_PREFIX%log` VALUES ('14548','2_is_recommend禁用成功','1469067262','1','111.198.16.9','1','Deal','toogle_status');
INSERT INTO `%DB_PREFIX%log` VALUES ('14549','2_is_recommend启用成功','1469067263','1','111.198.16.9','1','Deal','toogle_status');
INSERT INTO `%DB_PREFIX%log` VALUES ('14550','2_is_recommend禁用成功','1469067264','1','111.198.16.9','1','Deal','toogle_status');
INSERT INTO `%DB_PREFIX%log` VALUES ('14551','Test贷款名称启用成功','1469067276','1','111.198.16.9','1','Deal','set_advance');
INSERT INTO `%DB_PREFIX%log` VALUES ('14552','Test贷款名称禁用成功','1469067276','1','111.198.16.9','1','Deal','set_advance');
INSERT INTO `%DB_PREFIX%log` VALUES ('14553','Test贷款名称启用成功','1469067278','1','111.198.16.9','1','Deal','set_new');
INSERT INTO `%DB_PREFIX%log` VALUES ('14554','Test贷款名称禁用成功','1469067278','1','111.198.16.9','1','Deal','set_new');
INSERT INTO `%DB_PREFIX%log` VALUES ('14555','Test贷款名称启用成功','1469067279','1','111.198.16.9','1','Deal','set_effect');
INSERT INTO `%DB_PREFIX%log` VALUES ('14556','Test贷款名称禁用成功','1469067279','1','111.198.16.9','1','Deal','set_effect');
INSERT INTO `%DB_PREFIX%log` VALUES ('14557','Test贷款名称启用成功','1469067280','1','111.198.16.9','1','Deal','set_hidden');
INSERT INTO `%DB_PREFIX%log` VALUES ('14558','Test贷款名称禁用成功','1469067281','1','111.198.16.9','1','Deal','set_hidden');
INSERT INTO `%DB_PREFIX%log` VALUES ('14559','Test贷款名称排序修改成功','1469067283','1','111.198.16.9','1','Deal','set_sort');
INSERT INTO `%DB_PREFIX%log` VALUES ('14560','Test贷款名称启用成功','1469067854','1','111.198.16.9','1','Deal','set_effect');
INSERT INTO `%DB_PREFIX%log` VALUES ('14561','Test贷款名称禁用成功','1469068543','1','111.198.16.9','1','Deal','set_effect');
INSERT INTO `%DB_PREFIX%log` VALUES ('14562','Test贷款名称启用成功','1469068544','1','111.198.16.9','1','Deal','set_effect');
INSERT INTO `%DB_PREFIX%log` VALUES ('14563','Test贷款名称启用成功','1469068545','1','111.198.16.9','1','Deal','set_hidden');
INSERT INTO `%DB_PREFIX%log` VALUES ('14564','Test贷款名称禁用成功','1469068545','1','111.198.16.9','1','Deal','set_hidden');
INSERT INTO `%DB_PREFIX%log` VALUES ('14565','Test贷款名称启用成功','1469068546','1','111.198.16.9','1','Deal','set_new');
INSERT INTO `%DB_PREFIX%log` VALUES ('14566','Test贷款名称禁用成功','1469068547','1','111.198.16.9','1','Deal','set_new');
INSERT INTO `%DB_PREFIX%log` VALUES ('14567','Test贷款名称启用成功','1469068547','1','111.198.16.9','1','Deal','set_advance');
INSERT INTO `%DB_PREFIX%log` VALUES ('14568','Test贷款名称禁用成功','1469068548','1','111.198.16.9','1','Deal','set_advance');
INSERT INTO `%DB_PREFIX%log` VALUES ('14569','admin登录成功','1469069571','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14570','联系我们更新成功','1469069598','1','111.198.16.9','1','Article','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14571','2014-5-9   3月份运营数据：成交金额1530万更新成功','1469069650','1','111.198.16.9','1','Article','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14572','关于p2p信贷更新成功','1469069682','1','111.198.16.9','1','Article','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14573','账户安全更新成功','1469069714','1','111.198.16.9','1','Article','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14574','2014-5-9     p2p信贷即将上线更新成功','1469069764','1','111.198.16.9','1','Article','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14575','公司简介更新成功','1469069800','1','111.198.16.9','1','Article','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14576','1468972951删除成功','1469069850','1','111.198.16.9','1','Database','delete');
INSERT INTO `%DB_PREFIX%log` VALUES ('14577','加入我们更新成功','1469069896','1','111.198.16.9','1','Article','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14578','授权服务机构删除成功','1469069954','1','111.198.16.9','1','Article','delete');
INSERT INTO `%DB_PREFIX%log` VALUES ('14579','admin登录成功','1469077833','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14580','更新系统配置','1469078353','0','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14581','更新系统配置','1469078466','1','111.198.16.9','1','Conf','update_loan');
INSERT INTO `%DB_PREFIX%log` VALUES ('14582','教育培训启用成功','1469078530','1','111.198.16.9','1','DealLoanType','set_effect');
INSERT INTO `%DB_PREFIX%log` VALUES ('14583','汽车消费启用成功','1469078531','1','111.198.16.9','1','DealLoanType','set_effect');
INSERT INTO `%DB_PREFIX%log` VALUES ('14584','个人消费启用成功','1469078533','1','111.198.16.9','1','DealLoanType','set_effect');
INSERT INTO `%DB_PREFIX%log` VALUES ('14585','admin登录成功','1469121040','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14586','admin登录成功','1469121050','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14587','清除半年前的记录','1469121843','1','111.198.16.9','1','Log','foreverdelete');
INSERT INTO `%DB_PREFIX%log` VALUES ('14588','admin登录成功','1469121863','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14589','admin登录成功','1469125628','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14590','admin登录成功','1469128195','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14591','admin登录成功','1469138129','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14592','admin登录成功','1469162677','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14593','更新系统配置','1469162689','0','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14594','admin登录成功','1469162787','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14595','更新系统配置','1469162798','1','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14596','admin登录成功','1469175758','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14597','admin登录成功','1469470981','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14598','由管理员对收款单20160726101708339收款','1469471041','1','111.198.16.9','1','PaymentNotice','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14599','管理员编辑帐户','1469471087','1','111.198.16.9','1','User','update_hand_recharge');
INSERT INTO `%DB_PREFIX%log` VALUES ('14600','admin登录成功','1469484429','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14601','admin登录成功','1469489916','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14602','admin登录成功','1469573126','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14603','编号：车贷-C120727h添加成功','1469573962','1','111.198.16.9','1','Deal','insert');
INSERT INTO `%DB_PREFIX%log` VALUES ('14604','编号：3，车贷-C120727h更新成功','1469577062','1','111.198.16.9','1','Deal','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14605','编号：3，车贷-C120727h初审更新成功','1469577095','1','111.198.16.9','1','Deal','publish_update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14606','编号：3，车贷-C120727h复审更新成功','1469577108','1','111.198.16.9','1','Deal','true_publish_update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14607','admin登录成功','1469640786','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14608','admin登录成功','1469641308','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14609','更新系统配置','1469641446','1','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14610','更新系统配置','1469641464','1','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14611','admin登录成功','1469657925','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14612','admin登录成功','1469657939','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14613','admin登录成功','1469667501','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14614','admin管理员密码错误','1469730508','0','111.198.16.9','0','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14615','admin管理员密码错误','1469730632','0','111.198.16.9','0','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14616','admin登录成功','1469731222','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14617','admin登录成功','1469731281','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14618','admin登录成功','1469731571','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14619','admin登录成功','1469731653','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14620','admin登录成功','1469734405','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14621','admin登录成功','1469734637','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14622','admin登录成功','1469735664','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14623','更新系统配置','1469735678','0','111.198.16.9','1','Conf','update');
INSERT INTO `%DB_PREFIX%log` VALUES ('14624','admin登录成功','1469735694','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14625','admin登录成功','1469736407','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14626','admin登录成功','1469740879','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14627','admin登录成功','1469740902','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14628','admin登录成功','1469746485','1','111.198.16.9','1','Public','do_login');
INSERT INTO `%DB_PREFIX%log` VALUES ('14629','admin登录成功','1469985110','1','111.198.16.9','1','Public','do_login');
DROP TABLE IF EXISTS `%DB_PREFIX%m_adv`;
CREATE TABLE `%DB_PREFIX%m_adv` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT '',
  `img` varchar(255) DEFAULT '',
  `page` varchar(20) DEFAULT '',
  `type` tinyint(1) DEFAULT '0' COMMENT '1.标签集,2.url地址,3.分类排行,4.最亮达人,5.搜索发现,6.一起拍,7.热门单品排行,8.直接显示某个分享',
  `data` text,
  `sort` smallint(5) DEFAULT '10',
  `status` tinyint(1) DEFAULT '1',
  `open_url_type` int(11) DEFAULT '0' COMMENT '0:使用内置浏览器打开url;1:使用外置浏览器打开',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%m_adv` VALUES ('31','utdm','./public/attachment/201607/21/14/579067fb8d7a5.png','top','2','#','1','1','0');
INSERT INTO `%DB_PREFIX%m_adv` VALUES ('32','utdms','./public/attachment/201607/21/14/5790682ec7782.png','top','2','#','2','1','0');
DROP TABLE IF EXISTS `%DB_PREFIX%m_config`;
CREATE TABLE `%DB_PREFIX%m_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `val` text,
  `type` tinyint(1) NOT NULL,
  `sort` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%m_config` VALUES ('10','kf_phone','客服电话','400-000-0000','0','1');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('11','kf_email','客服邮箱','qq@qq.com','0','2');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('16','page_size','分页大小','10','0','10');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('17','about_info','关于我们(填文章ID)','66','0','3');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('18','program_title','程序标题名称','P2P','0','0');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('22','android_version','android版本号','2014070802','0','4');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('23','android_filename','android下载包名','p2p.apk','0','5');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('24','ios_version','ios版本号','0','0','7');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('25','ios_down_url','ios下载地址','','0','8');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('28','android_upgrade','android版本升级内容','更新版本号0，更新内容：','3','6');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('29','ios_upgrade','ios版本升级内容','','3','9');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('30','article_cate_id','文章分类ID','15','0','11');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('31','wx_appid','微信APPID','wxe4390206746d9367','0','12');
INSERT INTO `%DB_PREFIX%m_config` VALUES ('32','wx_secrit','微信SECRIT','0f1f8a34faf467bf8acc3729b789905e','0','13');
DROP TABLE IF EXISTS `%DB_PREFIX%mail_list`;
CREATE TABLE `%DB_PREFIX%mail_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_address` varchar(255) NOT NULL,
  `city_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_address_idx` (`mail_address`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%mail_server`;
CREATE TABLE `%DB_PREFIX%mail_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `smtp_server` varchar(255) NOT NULL,
  `smtp_name` varchar(255) NOT NULL,
  `smtp_pwd` varchar(255) NOT NULL,
  `is_ssl` tinyint(1) NOT NULL,
  `smtp_port` varchar(255) NOT NULL,
  `use_limit` int(11) NOT NULL,
  `is_reset` tinyint(1) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  `total_use` int(11) NOT NULL,
  `is_verify` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%medal`;
CREATE TABLE `%DB_PREFIX%medal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  `config` text NOT NULL,
  `icon` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `route` text NOT NULL,
  `allow_check` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%message`;
CREATE TABLE `%DB_PREFIX%message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `admin_reply` text NOT NULL,
  `admin_id` int(11) NOT NULL,
  `rel_table` varchar(255) NOT NULL,
  `rel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_0` (`user_id`,`is_effect`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=226 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%message_type`;
CREATE TABLE `%DB_PREFIX%message_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL,
  `is_fix` tinyint(1) NOT NULL,
  `show_name` varchar(255) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%message_type` VALUES ('1','deal','1','普通贷款','1','0');
INSERT INTO `%DB_PREFIX%message_type` VALUES ('2','transfer','1','债券转让','1','0');
INSERT INTO `%DB_PREFIX%message_type` VALUES ('3','transfer','1','债券转让','1','0');
DROP TABLE IF EXISTS `%DB_PREFIX%mobile_list`;
CREATE TABLE `%DB_PREFIX%mobile_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(255) NOT NULL,
  `city_id` int(11) NOT NULL,
  `verify_code` varchar(255) NOT NULL,
  `is_effect` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mobile_idx` (`mobile`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
DROP TABLE IF EXISTS `%DB_PREFIX%mobile_verify_code`;
CREATE TABLE `%DB_PREFIX%mobile_verify_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `verify_code` varchar(10) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `create_time` int(11) NOT NULL,
  `client_ip` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
INSERT INTO `%DB_PREFIX%mobile_verify_code` VALUES ('1','550048','13788888888','1455434659','127.0.0.1');
INSERT INTO `%DB_PREFIX%mobile_verify_code` VALUES ('2','971526','15810877391','1469573705','111.198.16.9');
