<?php
    class Register{
		//开户-绑卡
		/*
		*$type:业务操作类型（U01：客户注册，U02：客户信息修改，B01：客户绑卡）
		*$ctype:会员类型（00：个人，01：企业）
		*$crole:会员角色（00：投资方，01：融资方，02：担保方，09：全部）
		*/
        function register1($user_msg,$type,$ctype='00',$crole='00'){
			//$user_info = $GLOBALS['db']->getRow("select real_name,idno,mobile from ".DB_PREFIX."user where id = ".$userid);
			//$bank_info = $GLOBALS['db']->getRow("select bankcard,real_name,bank_mobile from ".DB_PREFIX."user_bank where user_id = ".$userid);

            $Publics = new Publics();
            $map['reqHeader'] = $Publics->reqheader("U00001");
            $map['inBody']['customerId'] = $user_msg['user_id'];//会员编号
            if($type=='B01' || $type =='B02' || $type=='B03'){
                $map['inBody']['businessSeqNo'] =  $user_msg['businessSeqNo'];//业务流水号
            }else{
                $map['inBody']['businessSeqNo'] =  $Publics->seqno();//业务流水号
            }

            $map['inBody']['busiTradeType'] = $type;//业务操作类型
            $map['inBody']['ctype'] = $ctype;//会员类型---00：个人   01：企业
            $map['inBody']['crole'] = $crole;//会员角色---00：投资方  01：融资方  02：担保方  09：全部
            $map['inBody']['username'] = $user_msg['real_name'];//用户名
            $map['inBody']['certType'] = "00";//证件类型---身份证
            $map['inBody']['certNo'] = strtoupper($user_msg['idno']);//证件号码
            $map['inBody']['certFImage'] = "";//身份证正面影像
            $map['inBody']['certBImage'] = "";//身份证反面影像
            //$map['inBody']['certInfo'] = "110223198909060011|关博宇|F|19890906|汉族|北京市通州区新城东里4号楼443号|北京市公安局通州分局";//身份证详情
            $map['inBody']['certInfo'] = "";//身份证详情
            $map['inBody']['idvalidDate'] = '';//身份证有效起始日期
            $map['inBody']['idexpiryDate'] = '';//身份证有效截止日期
            $map['inBody']['jobType'] =  '';//职业类型---自由职业
            $map['inBody']['job'] =  '';//职业描述---自由职业
            $map['inBody']['postcode'] = '';//邮编
            $map['inBody']['address'] = '';//地址
            $map['inBody']['national'] = '';//民族
            $map['inBody']['completeFlag'] = "0";//身份证信息完整标识---完整
            $map['inBody']['phoneNo'] = $user_msg['mobile'];//手机号
            $map['inBody']['companyName'] = "";//企业名称   会员类型为企业时必填
            $map['inBody']['uniSocCreCode'] = "";//统一社会信用代码
            $map['inBody']['uniSocCreDir'] = "";//统一社会信用地址
			if($type=='U01'||$type=='U02'){
				$map['inBody']['bindFlag'] = "01";//绑卡标识--注册
                $map['inBody']['bindType'] = "";//绑定类型
                $map['inBody']['acctype'] = "";//卡帐标识-银行卡
                $map['inBody']['oldbankAccountNo'] = "";//原绑定银行卡号
                $map['inBody']['bankAccountNo'] = "";//银行卡号
                $map['inBody']['bankAccountName'] = "";//银行账户名称
                $map['inBody']['bankAccountTelNo'] = "";//银行手机号
			}
			if($type=='B01' || $type=='B02' || $type=='B03'){
				$map['inBody']['bindFlag'] = "00";//绑卡标识--绑卡
				$map['inBody']['bindType'] = "99";//绑定类型
				$map['inBody']['acctype'] = "00";//卡帐标识-银行卡
                //$map['inBody']['accNo'] = $user_msg['dep_account']; //开户账号
				//$map['inBody']['oldbankAccountNo'] = $user_msg['oldbankcard'];//原绑定银行卡号
				$map['inBody']['oldbankAccountNo'] = "";//原绑定银行卡号
				$map['inBody']['bankAccountNo'] = $user_msg['bankcard'];//银行卡号
				$map['inBody']['bankAccountName'] = $user_msg['real_name'];//银行账户名称
				$map['inBody']['bankAccountTelNo'] = $user_msg['bank_mobile'];//银行手机号
			}
            $map['inBody']['note'] = "";//备注
            $map['inBody']['bizLicDomicile'] = "";//营业执照住所
            $map['inBody']['entType'] = "";//主体类型
            $map['inBody']['dateOfEst'] = "";//成立日期
            $map['inBody']['corpacc'] = "";//对公户账号
            $map['inBody']['corpAccBankNo'] = "";//对公户开户行行号
            $map['inBody']['corpAccBankNm'] = "";//对公户开户行名称
            $dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
            /* $map['inBody']['busiLiceNo'] = "";//营业执照编号
            $map['inBody']['busiLiceDir'] = "";//营业执照存放地址
            $map['inBody']['orgCodeNo'] = "";//组织机构代码
            $map['inBody']['orgCodeDir'] = "";//组织机构存放地址
            $map['inBody']['taxRegisNo'] = "";//税务登记号
            $map['inBody']['taxRegisDir'] = "";//税务登记号地址 */

            //$deps = $Publics->encrypt(json_encode($map));
			foreach($map as $k=>$v){
				if($v["certNo"]){
					$map[$k]["certNo"] = $Publics->encrypt($v["certNo"]);
					
				}
				if($v["username"]){
					$map[$k]["username"] = $Publics->encrypt($v["username"]);
				}
				if($v["phoneNo"]){
					$map[$k]["phoneNo"] = $Publics->encrypt($v["phoneNo"]);
				}
				if($v["customerId"]){
					$map[$k]["customerId"] = $Publics->encrypt($v["customerId"]);
				}
				if($v["oldbankAccountNo"]){
					$map[$k]["oldbankAccountNo"] = $Publics->encrypt($v["oldbankAccountNo"]);
				}
				if($v["bankAccountName"]){
					$map[$k]["bankAccountName"] = $Publics->encrypt($v["bankAccountName"]);
				}
				if($v["bankAccountNo"]){
					$map[$k]["bankAccountNo"] = $Publics->encrypt($v["bankAccountNo"]);
				}
				if($v["bankAccountTelNo"]){
					$map[$k]["bankAccountTelNo"] = $Publics->encrypt($v["bankAccountTelNo"]);
				}
			}
            $DepSdk = new DepSdk();
            $res=$DepSdk->CustomerInfoSync(json_encode($map));
            if($type=='U01'||$type=='U02' ){
                if(!$res||$res['respHeader']['respCode']=='P2P0300'){
                    $Publics = new Publics();
                    $maps['reqHeader'] = $Publics->reqheader("C00002");
                    $maps['inBody']['checkType'] = '01';//用户信息查询
                    $maps['inBody']['customerId'] = strval($user_msg['user_id']);//会员编号
                    $maps['inBody']['accountNo'] = '';//台帐帐号
                    $maps['inBody']['beginDate'] = '';//开始日期
                    $maps['inBody']['endDate'] = '';//结束日期
                    $maps['inBody']['beginPage'] = "";//起始页码
                    $maps['inBody']['endPage'] = "";//截止页码
                    $maps['inBody']['showNum'] = "10";//每页显示条数
                    $maps['inBody']['note'] = "";//备注
                    $depss = $Publics->sign($maps);
                    $maps['reqHeader']['signTime'] = $depss['signTime'];
                    $maps['reqHeader']['signature'] = $depss['signature'];
                    //$depsss = $Publics->encrypt(json_encode($maps));
					foreach($maps as $k=>$v){
						if($v['customerId']){
							$maps[$k]['customerId'] = $Publics->encrypt($v['customerId']);
						}
						if($v['accountNo']){
							$maps[$k]['accountNo'] = $Publics->encrypt($v['accountNo']);
						}
					}
                    $DepSdk = new DepSdk();
                    $result11=$DepSdk->dataQuery(json_encode($maps));
                    $result['callback_request_data'] = $maps; //反查请求参数
                    $result['callback_return_data'] = $result11; //反查返回参数
                }
            }elseif($type=="B02"){//解绑银行卡
				$res['outBody']['secBankaccNo'] = $Publics->decrypt($res['outBody']['secBankaccNo']);
				$res['outBody']['accNo'] = $Publics->decrypt($res['outBody']['accNo']);
				$data['secBankaccNo'] = $res['outBody']['secBankaccNo'];
				$data['form_con'] = json_encode($map);
				$data['back_con'] = json_encode($res,JSON_UNESCAPED_UNICODE);
				$data['type'] = "B02";
				$data['add_time'] = TIME_UTC;
				$data['date_time'] = date('Y-m-d H:i:s');
				$data['seqno'] =$map['inBody']['businessSeqNo'];
				$data['bankcard'] =$user_msg['bankcard'];
				$data['user_id'] = $user_msg['user_id'];
				$data['accNo'] = $user_msg['user_id'];
				$GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "INSERT");
			}

            //$result['liushui'] = $map['inBody']['businessSeqNo'];
            $result['map'] = $map;
            $result['res'] = $res;
            $result['seqno'] = $data['seqno'];
			return $result;
        }
        //企业用户注册

        function register2($user_msg,$type,$ctype='00',$crole='00'){
            
          
            $Publics = new Publics();
            $map['reqHeader'] = $Publics->reqheader("U00001");
            $map['inBody']['customerId'] = $user_msg['customerId'];//会员编号         
            $map['inBody']['businessSeqNo'] =  $Publics->seqno();//业务流水号
            $map['inBody']['busiTradeType'] = $type;//业务操作类型
            $map['inBody']['ctype'] = $ctype;//会员类型---00：个人   01：企业
            $map['inBody']['crole'] = $crole;//会员角色---00：投资方  01：融资方  02：担保方  09：全部
            $map['inBody']['username'] = $user_msg['username'];//用户名
            $map['inBody']['certType'] = "";//证件类型---身份证
            $map['inBody']['certNo'] = "";//证件号码
            $map['inBody']['certFImage'] = "";//身份证正面影像
            $map['inBody']['certBImage'] = "";//身份证反面影像
            $map['inBody']['certInfo'] = "";//身份证详情
            $map['inBody']['idvalidDate'] = '';//身份证有效起始日期
            $map['inBody']['idexpiryDate'] = '';//身份证有效截止日期
            $map['inBody']['jobType'] =  '';//职业类型---自由职业
            $map['inBody']['job'] =  '';//职业描述---自由职业
            $map['inBody']['postcode'] = '';//邮编
            $map['inBody']['address'] = '';//地址
            $map['inBody']['national'] = '';//民族
            $map['inBody']['completeFlag'] = "";//身份证信息完整标识---完整
            $map['inBody']['phoneNo'] =$user_msg['phone'];//手机号
            $map['inBody']['companyName'] = $user_msg['companyName'];//企业名称   会员类型为企业时必填
            $map['inBody']['uniSocCreCode'] = $user_msg['uniSocCreCode'];//统一社会信用代码
            $map['inBody']['uniSocCreDir'] = $user_msg['uniSocCreDir'];//统一社会信用地址           
            $map['inBody']['bindFlag'] = "00";//绑卡标识--绑卡
            $map['inBody']['bindType'] = "";//绑定类型
            $map['inBody']['acctype'] = "";//卡帐标识-银行卡
            $map['inBody']['oldbankAccountNo'] = "";//原绑定银行卡号
            $map['inBody']['bankAccountNo'] = "";//银行卡号
            $map['inBody']['bankAccountName'] = "";//银行账户名称
            $map['inBody']['bankAccountTelNo'] = "";//银行手机号
            $map['inBody']['note'] = "";//备注
            $map['inBody']['bizLicDomicile'] = $user_msg['bizLicDomicile'];//营业执照住所           
            $map['inBody']['entType'] = $user_msg['entType'];//主体类型
            $map['inBody']['dateOfEst'] = $user_msg['dateOfEst'];//成立日期
            $map['inBody']['corpacc'] = $user_msg['corpacc']; //对公户账号
            $map['inBody']['corpAccBankNo'] = $user_msg['corpAccBankNo'];//对公户开户行行号
            $map['inBody']['corpAccBankNm'] = $user_msg['corpAccBankNm'];//对公户开户行名称
            $dep = $Publics->sign($map);//签名
			$map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
            //$deps = $Publics->encrypt(json_encode($map));
			foreach($map as $k=>$value){
				if($value["customerId"]){
					$map[$k]['customerId'] = $Publics->encrypt($value["customerId"]);
				}
				if($value["certNo"]){
					$map[$k]['certNo'] = $Publics->encrypt($value["certNo"]);
				}
				if($value["username"]){
					$map[$k]['username'] = $Publics->encrypt($value["username"]);
				}
				if($value["phoneNo"]){
					$map[$k]['phoneNo'] = $Publics->encrypt($value["phoneNo"]);
				}
				if($value["bizLicDomicile"]){
					$map[$k]['bizLicDomicile'] = $Publics->encrypt($value["bizLicDomicile"]);
				}
				if($value["corpacc"]){
					$map[$k]['corpacc'] = $Publics->encrypt($value["corpacc"]);
				}if($value["uniSocCreCode"]){
					$map[$k]['uniSocCreCode'] = $Publics->encrypt($value["uniSocCreCode"]);
				}
				if($value["uniSocCreDir"]){
					$map[$k]['uniSocCreDir'] = $Publics->encrypt($value["uniSocCreDir"]);
				}
				if($value["phoneNo"]){
					$map[$k]['phoneNo'] = $Publics->encrypt($value["phoneNo"]);
				}
			}
            // var_dump($deps);exit;
            $DepSdk = new DepSdk();
            $res=$DepSdk->CustomerInfoSync(json_encode($map));
			$res['outBody']['accNo'] = $Publics->decrypt($res['outBody']['accNo']);
            //$result['liushui'] = $map['inBody']['businessSeqNo'];
			$ress['form_con'] =json_encode($map);
			$ress['back_con'] =json_encode($res,JSON_UNESCAPED_UNICODE);
			$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$ress,"INSERT");
            $result['map'] = $map;
            $result['res'] = $res;
            return $result;
        }


		/*客户委托协议签署/撤销
		* parames user_id 用户id
		* parames type 类型：B04签署;B05撤销
		* parames protocolno 协议号
		*/
		public  function delegate($seqno,$user_id,$type,$fundTradetype,$protocolno){
			$accNo = $GLOBALS['db']->getOne('select accno from '.DB_PREFIX.'user where id='.$user_id);
			$Publics = new Publics();
            $map['reqHeader'] = $Publics->reqheader("U00004");
            $map['inBody']['customerId'] = $user_id;//会员编号
			$map['inBody']['businessSeqNo'] = $seqno;//业务流水号
			$map['inBody']['busiTradeType'] = $type;//业务操作类型
			$map['inBody']['fundTradetype'] = $fundTradetype;//资金交易类型
			$map['inBody']['protocolNo'] = $protocolno;//协议号
			$map['inBody']['note'] = '';//备注
			$dep = $Publics->sign($map);//签名
			$map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
			//$deps = $Publics->encrypt(json_encode($map));
			foreach($map as $k=>$value){
				if($value["customerId"]){
					$map[$k]['customerId'] = $Publics->encrypt($value["customerId"]);
				}
				if($value["protocolNo"]){
					$map[$k]['protocolNo'] = $Publics->encrypt($value["protocolNo"]);
				}
			}
            $DepSdk = new DepSdk();
			$result=$DepSdk->entrustAgreement(json_encode($map));
			$data['seqno'] = $map['inBody']['businessSeqNo'];
			$data['user_id'] = $user_id;
			$data['accNo'] = $accNo;
            $data['form_con'] = json_encode($map);
            $data['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
            $data['type'] = $type;
			$data['add_time'] =time();
			$data['date_time'] =date("Y-m-d H:i:s");
			if($result['respHeader']['respCode']=="P2P0000"){
				$data['status'] =1;
				$data['suc_time']=time();
			}
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
			return $result;
		}
	}