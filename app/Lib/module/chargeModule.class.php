<?php
define(MODULE_NAME,"index");
require APP_ROOT_PATH."system/utils/Depository/Require.php";
class chargeModule extends SiteBaseModule
{
        /*
        * $SeqNo:业务流水号    代扣
        */
        public function charge_pay(){            
            $Publics = new Publics();
            $map['reqHeader'] = $Publics->reqheader("T00001");
            $map['inBody']['businessSeqNo'] = $_GET['businessSeqNo'];//业务流水号      

            $Query = $GLOBALS['db']->getOne("SELECT seqno FROM ".DB_PREFIX."decository where seqno='".$map['inBody']['businessSeqNo']."' and type='R01'");   
            if($Query){ 
                exit;
            }
            $Query = $GLOBALS['db']->getRow("SELECT id,user_id,order_id,money,bank_id,is_paid FROM ".DB_PREFIX."payment_notice where seqno='".$map['inBody']['businessSeqNo']."' and cunguan_tag=1 and is_paid=0");   
            $trans_progress = array('is_paid' =>2,);
            //$user_query = $GLOBALS['db']->getRow("SELECT mobile,idno,bank_realname,bankcard,dep_account,user_id,bank_id FROM ".DB_PREFIX."dep_account where user_id='".$Query['user_id']."'");                        
            $user_query = $GLOBALS['db']->getRow("SELECT idno,real_name,mobile,accno FROM ".DB_PREFIX."user where id='".$Query['user_id']."' and cunguan_tag=1");             
            $bank_query = $GLOBALS['db']->getRow("SELECT bankcard,bank_id FROM ".DB_PREFIX."user_bank  where user_id='".$Query['user_id']."' and cunguan_tag=1 and status=1");             
            $bank_name = $GLOBALS['db']->getOne("SELECT cunguan_name FROM ".DB_PREFIX."bank  where bankid='".$bank_query['bank_id']."'");
            $map['inBody']['businessOrderNo'] = "";//订单流水号 $Query['order_id']
            $map['inBody']['rType'] = "R01";//充值类型---R01：客户代扣充值  R02：客户网银充值  R03：营销充值  R04:代偿充值  R05：费用充值  R06：垫资充值  R07:线下充值
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托 
            $map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>"","cebitAccountNo"=>$user_query['accno'],"currency"=>"CNY","amount"=>$Query['money'],"otherAmounttype"=>"","otherAmount"=>""));//资金账务处理列表
            $map['inBody']['payType'] = "00";//支付方式--00:银行支付渠道 01：第三方支付渠道
            $map['inBody']['busiBranchNo'] = "";//支付公司代码  hyzf  
            $data['bankcard']=str_replace(" ","",$bank_query['bankcard']);
            $map['inBody']['bankAccountNo']=$data['bankcard'];//银行卡号
            $map['inBody']['secBankaccNo'] = "";//二类户账户
            $map['inBody']['note'] = "";//备注
			$map['inBody']['platformAccountNo'] = "";//手续费收取平台台帐帐号
            $map['inBody']['deductType'] = "01";//内扣外扣类型 
            $dep = $Publics->sign($map);
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
            //$map['inBody']['ownerName'] = $user_query['real_name'];//持卡人姓名
            //$map['inBody']['ownerCertNo'] = $user_query['idno'];//持卡人身份证号
            //$map['inBody']['ownerMobile'] = $user_query['mobile'];//持卡人手机号
            //$map['inBody']['bankId']  = $bank_query['bank_id'];
            //$map['inBody']['bankName']  = $bank_name;
            //$map['inBody']['cardType'] = "SAVING";//卡类型
           // $map['inBody']['identifycode'] = "";//短信验证码
            $map['inBody']['accountList'][0]['cebitAccountNo'] =  $Publics->encrypt( $map['inBody']['accountList'][0]['cebitAccountNo']);
			foreach($map as $key=>$value){
				if($value["bankAccountNo"]){
					$map[$key]['bankAccountNo'] = $Publics->encrypt($value["bankAccountNo"]);
				}
				if($value["secBankaccNo"]){
					$map[$key]['secBankaccNo'] = $Publics->encrypt($value["secBankaccNo"]);
				}
				if($value["platformAccountNo"]){
					$map[$key]['platformAccountNo'] = $Publics->encrypt($value["platformAccountNo"]);
				}
				if($value["deductType"]){
					$map[$key]['deductType'] = $Publics->encrypt($value["deductType"]);
				}
			}

            $DepSdk = new DepSdk();
            $result=$DepSdk->charge(json_encode($map));

            //var_dump($result);
            $data['seqno'] = $_GET['businessSeqNo'];
            $data['user_id'] = $Query['user_id'];
            $data['accNo'] = $user_query['accno'];
            $data['fee_type'] ='01';
            $data['fee_money'] = '';
            $data['form_con'] = json_encode($map);
            $data['back_con'] = json_encode($result);//$result['respHeader']['respMsg'];
            $data['callback_con'] = $result['respHeader']['respMsg'];
            $data['type'] = "R01";
            $data['money'] = $Query['money'];
            $data['bankcard'] = $bank_query['bankcard'];
            $data['add_time'] = time();
            $data['date_time'] = date("Y-m-d H:i:s");
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
			
            if($result['respHeader']['respCode'] =="P2PS000"){
                $recharge = $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_progress, "UPDATE", "id=" . $Query['id'] . ""); 
                if(WAP==1){
                    app_redirect(url("index", "payment#cunguan_return_url&odl=".$map['inBody']['businessSeqNo'].""));    
                }else{
                    app_redirect(url("index", "uc_money#incharge_log"));
                }
            }else{
                if(WAP==1){ 
                     app_redirect(url("index", "payment#cunguan_return_url&odl=".$map['inBody']['businessSeqNo'].""));    
                }else{ 
                    app_redirect(url("index", "uc_money#incharge_log"));                  
                }               
            }
            
        }

        //第二步:验证成功去充值--第三方支付
        public function chongzhi_ceshi(){
            $Publics = new Publics();
            $map['reqHeader'] = $Publics->reqheader("KPCZ01");
            $map['inBody']['businessSeqNo'] = $_GET['businessSeqNo'];//业务流水号

            $Query = $GLOBALS['db']->getOne("SELECT seqno FROM ".DB_PREFIX."decository where seqno='".$map['inBody']['businessSeqNo']."' and type='R02'");   
            if($Query){ 
                app_redirect(url("index", "uc_money#incharge_log"));
            }

            $Query = $GLOBALS['db']->getRow("SELECT id,user_id,order_id,money,bank_id,is_paid FROM ".DB_PREFIX."payment_notice where seqno='".$map['inBody']['businessSeqNo']."'");   
            //$user_query = $GLOBALS['db']->getRow("SELECT mobile,idno,bank_realname,bankcard,dep_account,user_id,bank_id FROM ".DB_PREFIX."dep_account where user_id='".$Query['user_id']."'");                        
            $user_query = $GLOBALS['db']->getRow("SELECT idno,real_name,mobile,accno FROM ".DB_PREFIX."user where id='".$Query['user_id']."' and cunguan_tag=1");             
            $bank_query = $GLOBALS['db']->getRow("SELECT bankcard,bank_id FROM ".DB_PREFIX."user_bank  where user_id='".$Query['user_id']."' and cunguan_tag=1 and status=1");             
            $bank_name = $GLOBALS['db']->getOne("SELECT cunguan_name FROM ".DB_PREFIX."bank  where bankid='".$bank_query['bank_id']."'");
            $map['inBody']['businessOrderNo'] =$Query['order_id'] ;//订单流水号
            $map['inBody']['rType'] = "R02";//充值类型---R01：客户代扣充值  R02：客户网银充值  R03：营销充值  R04:代偿充值  R05：费用充值  R06：垫资充值  R07:线下充值
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托
            $map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>"","cebitAccountNo"=>$user_query['accno'],"currency"=>"CNY","amount"=>$Query['money'],"otherAmounttype"=>"","otherAmount"=>""));//资金账务处理列表
            $map['inBody']['payType'] = "01";//支付方式--00:银行支付渠道 01：第三方支付渠道
            $map['inBody']['busiBranchNo'] = "hyzf";//支付公司代码
            $map['inBody']['ownerName'] = $user_query['real_name'];//持卡人姓名
            $map['inBody']['ownerCertNo'] = $user_query['idno'];//持卡人身份证号
            $map['inBody']['ownerMobile'] = $user_query['mobile'];//持卡人手机号
            $data['bankcard']=str_replace(" ","",$bank_query['bankcard']);
            $map['inBody']['bankAccountNo'] = $data['bankcard'];//银行卡号
            $map['inBody']['secBankaccNo'] = "";//二类户账户
            $map['inBody']['bankId']  = $bank_query['bank_id'];
            $map['inBody']['bankName']  = $bank_name;
            $map['inBody']['cardType'] = "SAVING";//卡类型
            $map['inBody']['identifycode'] = "";//短信验证码
            $map['inBody']['note'] = "";//备注
            $dep = $Publics->encrypt(json_encode($map));
            $data['seqno'] = $_GET['businessSeqNo'];
            $data['user_id'] = $Query['user_id'];
            $data['accNo'] = $user_query['accno'];
            $data['money'] =$Query['money'];
            $data['form_con'] = json_encode($map);
            $data['back_con'] = "";
            $data['type'] = "R02";
            $data['add_time'] = time();
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");         
            $url = "https://test-p2.heepay.com/customer/charge";
            
            $curl = curl_init();    //启动一个curl会话
            curl_setopt($curl, CURLOPT_URL, $url);   //要访问的地址
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $dep); // Post提交的数据包
            curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
            $output = curl_exec($curl);  //执行curl会话
            curl_close($curl);
            echo $output;   

            /*
            $html = "<form action='https://test-p2.heepay.com/customer/charge' method='post' id='form1' accept-charset='utf-8' enctype='text/plain' >
                        <input type='hidden' name='body' value='".$dep."'>
                     </form>
                     <script>
                        document.getElementById('form1').submit();
                     </script>";
            echo $html;die;
            */
        }



        /*订单流水号*/
         public function Numbers(){
            $yCode = array('H', 'W', 'E', 'I', 'T', 'Y', 'C','A','M', 'B', 'S','Q','X','U','L','Z');
            $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
            return $orderSn;
        }

        //第二步:验证成功去提汇元认证支付充值申请
        public function chongzhi_kj_one_ceshi(){
            $Publics = new Publics();
            $map['reqHeader'] = $Publics->reqheader("KPCZ01");
            $map['inBody']['businessSeqNo'] = $_GET['businessSeqNo'];//业务流水号
            $Query = $GLOBALS['db']->getRow("SELECT id,user_id,order_id,money,bank_id,is_paid FROM ".DB_PREFIX."payment_notice where seqno='".$map['inBody']['businessSeqNo']."'");

            $user_query = $GLOBALS['db']->getRow("SELECT idno,real_name,mobile,accno FROM ".DB_PREFIX."user where id='".$Query['user_id']."' and cunguan_tag=1");
            
            $bank_query = $GLOBALS['db']->getRow("SELECT bankcard,bank_id FROM ".DB_PREFIX."user_bank  where user_id='".$Query['user_id']."' and cunguan_tag=1 and status=1");
           
            $bank_name = $GLOBALS['db']->getOne("SELECT cunguan_name FROM ".DB_PREFIX."bank  where bankid='".$bank_query['bank_id']."'");            
           
            $map['inBody']['businessOrderNo'] =$this->Numbers();//订单流水号
            $map['inBody']['rType'] = "R08";//充值类型---R01：客户代扣充值  R02：客户网银充值  R03：营销充值  R04:代偿充值  R05：费用充值  R06：垫资充值  R07:线下充值  R08:快捷充值申请(汇元认证)    R09快捷确认
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托
            $map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>"","cebitAccountNo"=>$user_query['accno'],"currency"=>"CNY","amount"=>$Query['money'],"otherAmounttype"=>"01","otherAmount"=>""));//资金账务处理列表
            $map['inBody']['payType'] = "01";//支付方式--00:银行支付渠道 01：第三方支付渠道
            $map['inBody']['busiBranchNo'] = "hyzf";//支付公司代码
            $map['inBody']['ownerName'] = $user_query['real_name'];//持卡人姓名
            $map['inBody']['ownerCertNo'] = $user_query['idno'];//持卡人身份证号
            $map['inBody']['ownerMobile'] = $user_query['mobile'];//持卡人手机号
            $data['bankcard']=str_replace(" ","",$bank_query['bankcard']);
            $map['inBody']['bankAccountNo'] = $data['bankcard'];//银行卡号
            $map['inBody']['secBankaccNo'] = "";//二类户账户
            $map['inBody']['bankId']  = $bank_query['bank_id'];
            $map['inBody']['bankName']  = $bank_name;
            $map['inBody']['cardType'] = "SAVING";//卡类型
            $map['inBody']['identifycode'] = "";//短信验证码
            $map['inBody']['note'] = "";//备注
            
            $dep = $Publics->encrypt(json_encode($map));
      
            $DepSdk = new DepSdk();
            $result=$DepSdk->charge($dep);
          
            $data['seqno'] = $_GET['businessSeqNo'];
            $data['user_id'] = $Query['user_id'];
            $data['accNo'] = $user_query['accno'];
            $data['money'] =$Query['money'];
            $data['form_con'] = json_encode($map);
            $data['callback_con'] = json_encode($result);
            $data['back_con'] = $result['outBody']['token'];
            $data['information'] = $result['respHeader']['respMsg'];
            $data['type'] = "R08";
            $data['add_time'] = time();
            $data['date_time'] = date("Y-m-d H:i:s");
            $data['businessOrderNo'] = $map['inBody']['businessOrderNo'];
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT"); 
            
            if($result['respHeader']['respCode'] =="P2P0000"){
                if(WAP==1){
                    app_redirect(url("index", "payment#certificate_return_payment&odl=".$map['inBody']['businessSeqNo'].""));    
                }else{
                    $mobile = $user_query['mobile'];
                    $GLOBALS['tmpl']->assign("seqno",$data['seqno']);
                    $GLOBALS['tmpl']->assign("money",$data['money']);
                    $GLOBALS['tmpl']->assign("mobile",$mobile);
                    //$GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_money_ceshi.html");
                    $GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_incharge_V_phoneCode.html");
                    $GLOBALS['tmpl']->display("page/uc.html");     
                }
            }else{
                if(WAP==1){ 
                    app_redirect(url("index", "payment#certificate_return_payment&odl=".$map['inBody']['businessSeqNo'].""));     
                }else{ 
                    showErr($result['respHeader']['respMsg'],0,url("index","uc_center"));            
                }

                
            }
        //end 
        }


        //第三步:验证成功去确认汇元认证支付充值
        public function chongzhi_kj_two_ceshi(){
            $SMS_identifying =$_REQUEST['phoneCode'];//短信验证码
        
            $user_type = $_REQUEST['Bill_Number'];//业务流水号
   
            $token = $GLOBALS['db']->getRow("SELECT back_con,user_id,form_con FROM ".DB_PREFIX."decository where seqno='".$user_type."' and type ='R08' order by id desc limit 1");         

            $form_con=json_decode($token['form_con'],true);  //获取用户其他信息    
   
            $user_query = $GLOBALS['db']->getRow("SELECT idno,real_name,mobile,accno FROM ".DB_PREFIX."user where id='".$token['user_id']."' and cunguan_tag=1");

            $bank_query = $GLOBALS['db']->getRow("SELECT bankcard,bank_id,user_id FROM ".DB_PREFIX."user_bank  where user_id='".$token['user_id']."' and cunguan_tag=1 and status=1");

            $bank_name = $GLOBALS['db']->getOne("SELECT cunguan_name FROM ".DB_PREFIX."bank  where bankid='".$bank_query['bank_id']."'");            

            $Publics = new Publics();
            $map['reqHeader'] = $Publics->reqheader("KPCZ01");
            $map['inBody']['businessSeqNo'] = $form_con['inBody']['businessSeqNo'];
            $map['inBody']['businessOrderNo'] =$form_con['inBody']['businessOrderNo'];//订单流水号
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托
            $map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>"","cebitAccountNo"=>$form_con['inBody']['accountList'][0]['cebitAccountNo'],"currency"=>"CNY","amount"=>$form_con['inBody']['accountList'][0]['amount'],"otherAmounttype"=>"01","otherAmount"=>""));//资金账务处理列表
            $map['inBody']['payType'] = "01";//支付方式--00:银行支付渠道 01：第三方支付渠道
            $map['inBody']['busiBranchNo'] = "hyzf";//支付公司代码
            $map['inBody']['ownerName'] = $user_query['real_name'];//持卡人姓名
            $map['inBody']['ownerCertNo'] = $user_query['idno'];//持卡人身份证号
            $map['inBody']['ownerMobile'] = $user_query['mobile'];//持卡人手机号
            $data['bankcard']=str_replace(" ","",$bank_query['bankcard']);
            $map['inBody']['bankAccountNo'] = $data['bankcard'];//银行卡号
            $map['inBody']['secBankaccNo'] = "";//二类户账户
            $map['inBody']['bankId']  = $bank_query['bank_id'];
            $map['inBody']['bankName']  = $bank_name;
            $map['inBody']['cardType'] = "SAVING";//卡类型
            $map['inBody']['rType'] = "R09";//充值类型---R01：客户代扣充值  R02：客户网银充值  R03：营销充值  R04:代偿充值  R05：费用充值  R06：垫资充值  R07:线下充值  R08:快捷充值申请    R09快捷确认
            $map['inBody']['identifycode'] = $SMS_identifying;//短信验证码
            $map['inBody']['note'] = $token['back_con'];//备注
     
            $dep = $Publics->encrypt(json_encode($map));
 
            $DepSdk = new DepSdk();
            $result=$DepSdk->charge($dep);
            $data['seqno'] = $form_con['inBody']['businessSeqNo'];            
            $data['user_id'] = $bank_query['user_id'];
            $data['accNo'] = $result['respHeader']['cebitAccountNo'];
            $data['form_con'] = json_encode($map);
            $data['back_con'] = json_encode($result);
            $data['type'] = "R09";
            $data['add_time'] = time();
            $data['date_time'] = date("Y-m-d H:i:s");
            $data['businessOrderNo'] = $map['inBody']['businessOrderNo'];
            $data['information'] = $result['respHeader']['respMsg'];
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");

            if($result['respHeader']['respCode'] =="P2P0000"){ 
                if(WAP==1){ 
                    app_redirect(url("index", "payment#certificate_payment&odl=".$data['seqno']."")); 
                }else{ 
                    $das['status'] = 1; 
                    $das["info"] = $result['respHeader']['respMsg'];            
                    $das['jump'] =url("index","uc_money#incharge_log");
                    ajax_return($das);
                }
                              
            }else{
                if(WAP==1){ 
                    app_redirect(url("index", "payment#certificate_payment&odl=".$data['seqno'].""));                    
                }else{ 
                    $das['status'] = 0;    
                    $das["info"] = $result['respHeader']['respMsg'];
                    $das['jump'] =url("index","uc_money#incharge");
                    ajax_return($das);        
                }                        
            }
                          
        }


        public function incharge_V_phoneCode(){

            $GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_incharge_V_phoneCode.html");
			$GLOBALS['tmpl']->display("page/uc.html");
                          
        }      

   //end

        #宜宾第二种充值
        public function charge_wang(){ 
              $Publics = new Publics();           
              $map['reqHeader'] = $Publics->reqheader("T00001");
              $map['inBody']['businessSeqNo'] =$_GET['businessSeqNo'];//业务流水号      
              $Query = $GLOBALS['db']->getRow("SELECT id,user_id,order_id,money,bank_id,is_paid,seqno,user_type FROM ".DB_PREFIX."payment_notice where seqno='".$map['inBody']['businessSeqNo']."' and cunguan_tag=1 and is_paid=0");      
              $trans_progress = array('is_paid' =>2,);  
              $map['inBody']['businessOrderNo'] = $Query['order_id'];//订单流水号 $Query['order_id']
              $map['inBody']['rType'] = "R02";//充值类型---R01：客户代扣充值  R02：客户网银充值  R03：营销充值  R04:代偿充值  R05：费用充值  R06：垫资充值  R07:线下充值
              $map['inBody']['entrustflag'] = "00";//委托标识-未委托 
              $map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>"","cebitAccountNo"=>$Query['user_id'],"currency"=>"CNY","amount"=>$Query['money'],"otherAmounttype"=>"","otherAmount"=>""));//资金账务处理列表
              $map['inBody']['payType'] = "00";//支付方式--00:银行支付渠道 01：第三方支付渠道
              $map['inBody']['busiBranchNo'] = "";//支付公司代码  hyzf  
              $map['inBody']['bankAccountNo']="";//银行卡号
              $map['inBody']['secBankaccNo'] = "";//二类户账户
              $map['inBody']['note'] = "";//备注
              $dep = $Publics->sign($map);
              $map['reqHeader']['signTime'] = $dep['signTime'];
              $map['reqHeader']['signature'] = $dep['signature'];
              $map['inBody']['accountList'][0]['cebitAccountNo'] =  $Publics->encrypt( $map['inBody']['accountList'][0]['cebitAccountNo']);
              foreach($map as $key=>$value){
                    if($value["bankAccountNo"]){
                          $map[$key]['bankAccountNo'] = $Publics->encrypt($value["bankAccountNo"]);
                    }
                    if($value["secBankaccNo"]){
                          $map[$key]['secBankaccNo'] = $Publics->encrypt($value["secBankaccNo"]);
                    }
                    if($value["platformAccountNo"]){
                          $map[$key]['platformAccountNo'] = $Publics->encrypt($value["platformAccountNo"]);
                    }
                    if($value["deductType"]){
                          $map[$key]['deductType'] = $Publics->encrypt($value["deductType"]);
                    }
              }

              $DepSdk = new DepSdk();
              $result=$DepSdk->charge(json_encode($map));            
              $data['seqno'] = $Query['seqno'];
              $data['user_id'] = $Query['user_id'];
              $data['accNo'] = $Query['user_id'];
              $data['form_con'] = json_encode($map);
              $data['back_con'] = json_encode($result);//$result['respHeader']['respMsg']; json_encode($result);
              $data['callback_con'] = "";              
              $data['type'] = "R02";             
              $data['money'] = $Query['money'];
              $data['add_time'] = time();
              $data['date_time'] = date("Y-m-d H:i:s");
              $data['information'] = $result['respHeader']['respMsg'];
              $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");

              $now=time();
              $user_add_time = date("ymdhis", $now);
              $record=array(
                    'pTrdAmt'=>$Query['money'],
                    'user_id'=>$Query['user_id'],
                    'now'=>$now,
                    'user_add_time'=>$user_add_time,
              );

              $corpacc = $GLOBALS['db']->getRow("SELECT real_name FROM ".DB_PREFIX."user where id='".$Query['user_id']."'"); 
  

              if($result['respHeader']['respCode'] =="P2PS000"){
                    $recharge = $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_progress, "UPDATE", "id=" . $Query['id'] . ""); 
                    $html=$this->Enterprise_value($Query['seqno'],$record,$Query['order_id'],$corpacc,$Query['user_type']);
                    echo $html;  
                    exit;  
              }else{
                    app_redirect(url("index", "uc_money#incharge_log"));                                                    
              }                 
        }




            /*
             * 存管企业充值
            */
            public function enterprise($trader_id,$record,$orderId,$corpacc){ 
                  $Publics = new Publics();
                  //$map['reqHeader'] = $Publics->reqheader("KPCZ01");
                  $map['reqHeader'] = $Publics->reqheader("T00001");
                  $map['inBody']['businessSeqNo'] = $trader_id;//业务流水号      
                  $Query = $GLOBALS['db']->getRow("SELECT id,user_id,order_id,money,bank_id,is_paid FROM ".DB_PREFIX."payment_notice where seqno='".$map['inBody']['businessSeqNo']."' and cunguan_tag=1 and is_paid=0");      
                  $trans_progress = array('is_paid' =>2,);               
                  $map['inBody']['businessOrderNo'] = $orderId;//订单流水号 $Query['order_id']
                  $map['inBody']['rType'] = "R02";//充值类型---R01：客户代扣充值  R02：客户网银充值  R03：营销充值  R04:代偿充值  R05：费用充值  R06：垫资充值  R07:线下充值
                  $map['inBody']['entrustflag'] = "00";//委托标识-未委托 
                  $map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>"","cebitAccountNo"=>$Query['user_id'],"currency"=>"CNY","amount"=>$record['pTrdAmt'],"otherAmounttype"=>"","otherAmount"=>""));//资金账务处理列表
                  $map['inBody']['payType'] = "00";//支付方式--00:银行支付渠道 01：第三方支付渠道
                  $map['inBody']['busiBranchNo'] = "";//支付公司代码  hyzf  
                  $map['inBody']['bankAccountNo']="";//银行卡号
                  $map['inBody']['secBankaccNo'] = "";//二类户账户
                  $map['inBody']['note'] = "";//备注
                  $dep = $Publics->sign($map);
                  $map['reqHeader']['signTime'] = $dep['signTime'];
                  $map['reqHeader']['signature'] = $dep['signature'];
                  // $map['inBody']['ownerName'] = "";//持卡人姓名
                  // $map['inBody']['ownerCertNo'] ="";//持卡人身份证号
                  // $map['inBody']['ownerMobile'] = "";//持卡人手机号
                  // $map['inBody']['bankId']  = "";
                  // $map['inBody']['bankName']  ="";
                  // $map['inBody']['cardType'] = "SAVING";//卡类型
                  // $map['inBody']['identifycode'] = "";//短信验证码

                  $map['inBody']['accountList'][0]['cebitAccountNo'] =  $Publics->encrypt( $map['inBody']['accountList'][0]['cebitAccountNo']);
                  foreach($map as $key=>$value){
                        if($value["bankAccountNo"]){
                              $map[$key]['bankAccountNo'] = $Publics->encrypt($value["bankAccountNo"]);
                        }
                        if($value["secBankaccNo"]){
                              $map[$key]['secBankaccNo'] = $Publics->encrypt($value["secBankaccNo"]);
                        }
                        if($value["platformAccountNo"]){
                              $map[$key]['platformAccountNo'] = $Publics->encrypt($value["platformAccountNo"]);
                        }
                        if($value["deductType"]){
                              $map[$key]['deductType'] = $Publics->encrypt($value["deductType"]);
                        }
                  }


                  $DepSdk = new DepSdk();
                  $result=$DepSdk->charge(json_encode($map));

                  // $deps = $Publics->encrypt(json_encode($map));

                  // $DepSdk = new DepSdk();
                  // $result=$DepSdk->charge($deps);

                  $data['seqno'] = $trader_id;
                  $data['user_id'] = $Query['user_id'];
                  $data['accNo'] = $Query['user_id'];
                  $data['form_con'] = json_encode($map);
                  $data['back_con'] = json_encode($result);//$result['respHeader']['respMsg']; json_encode($result);
                  $data['callback_con'] = "";              
                  $data['type'] = "RR02";             
                  $data['money'] = $Query['money'];
                  $data['add_time'] = time();
                  $data['date_time'] = date("Y-m-d H:i:s");
                  $data['information'] = $result['respHeader']['respMsg'];
                  $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");

                  if($result['respHeader']['respCode'] =="P2PS000"){
                        $recharge = $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_progress, "UPDATE", "id=" . $Query['id'] . ""); 
                        $html=$this->Enterprise_value($trader_id,$record,$orderId,$corpacc);
                        return $html;       
                   
                  }else{
                        app_redirect(url("index", "uc_money#incharge_log"));                                                    
                  }
            }





       public function Enterprise_value($trader_id,$record,$orderId,$corpacc,$rec_type){ 
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            
            if($rec_type =="0"){ 
                  $ellipsis = '个人充值';
            }else{ 
                  $ellipsis ='企业充值';
            }

            $enterprise=array(
                  "OUT_ID"=>$trader_id,
                  "TRAN_TIME"=>date('YmdHis',time()),
                  "GOODS_NAME" =>$ellipsis,
                  "COMM_NO" =>"002543",
                  "ORDER_NO"=>$orderId,
                  "USER_IP"=>str_replace(".", "_", $_SERVER['REMOTE_ADDR']),
                  "ACCT_NO" => $record['user_id'],
                  "ACCT_NAME"=>$corpacc['real_name'],
                  "B_ACCT_NO"=>"01573201000000528",
                  "B_ACCT_NAME"=> "天风天财（武汉）金融信息服务有限公司（资金存管户）",
                  "SUB_SOURCE_TYPE" =>"JCT",
                  "PAY_AMT"=>$record['pTrdAmt'],
            );

            
            
            $para = $this->buildRequestPara($enterprise,$http_type,$rec_type);
            #测试
            $url = "https://tppsgw.dccbj.cn:5188/devportal/ws/cpcnGatePay/rechargerByCpcn";
            #线上的 
            //$url = "https://tppsgw.ybccb.com:5188/devportal/ws/cpcnGatePay/rechargerByCpcn";
            $html = "<form action='" . $url . "'  method='post' id='form1'>       
                        <input type='hidden' name='OUT_ID' value='" . $para['OUT_ID'] . "'/>
                        <input type='hidden' name='TRAN_TIME' value='" . $para['TRAN_TIME'] . "'/>
                        <input type='hidden' name='GOODS_NAME' value='" . $para['GOODS_NAME'] . "'/>
                        <input type='hidden' name='COMM_NO' value='" . $para['COMM_NO'] . "'/>
                        <input type='hidden' name='ORDER_NO' value='" . $para['ORDER_NO'] . "'/>
                        <input type='hidden' name='USER_IP' value='" . $para['USER_IP'] . "'/>
                        <input type='hidden' name='ACCT_NO' value='" . $para['ACCT_NO'] . "'/>
                        <input type='hidden' name='ACCT_NAME' value='" . $para['ACCT_NAME'] . "'/>
                        <input type='hidden' name='B_ACCT_NO' value='" . $para['B_ACCT_NO'] . "'/>
                        <input type='hidden' name='B_ACCT_NAME' value='" . $para['B_ACCT_NAME'] . "'/>
                        <input type='hidden' name='SUB_SOURCE_TYPE' value='" . $para['SUB_SOURCE_TYPE'] . "'/>
                        <input type='hidden' name='PAY_AMT' value='" . $para['PAY_AMT'] . "'/>
                        <input type='hidden' name='BC_FLAG' value='" . $para['BC_FLAG'] . "'/>
                        <input type='hidden' name='FRONT_URL' value='" . $para['FRONT_URL'] . "'/>
                        <input type='hidden' name='SIGNATURE' value='" . $para['sign'] . "'/>
                     <script>
                        document.getElementById('form1').submit();
                     </script>";
            return $html;
      }

      public function buildRequestPara($para_temp,$http_type,$rec_type) {
            $para_filter = $this->paraFilter($para_temp);
            $para_sort = $this->argSort($para_filter);
            $mysign = $this->buildRequestMysign($para_sort);
            $para_sort['sign'] = strtolower($mysign);
            //if($rec_type == "0"){ 
                    $para_sort['BC_FLAG'] ="2";
            //}else{ 
                  //$para_sort['BC_FLAG'] ="1";
            //}
            $para_sort['FRONT_URL'] =$http_type . $_SERVER['HTTP_HOST'] . "/member.php?ctl=uc_money&act=incharge_log";//页面跳转地址（改成商户自已的地址）
            foreach ($para_sort as $key => $value) {
                  $para_sort[$key] = $value;
            }
            return $para_sort;
      }

      public function paraFilter($para) {
            $para_filter = array();
            while (list ($key, $val) = each ($para)) {
                  if($key == "sign" || $val == "")continue;
                  else  $para_filter[$key] = $para[$key];
            }
            return $para_filter;
      }

       function argSort($para) {
            ksort($para);
            reset($para);
            return $para;
      }

      public function buildRequestMysign($para_sort) {
            $mysign = "";     
            $prestr = $this->createLinkstring($para_sort);
            #测试和线上一样的
            $key = "87FB9444028A4B14937A1905";
            $prestr = $prestr ."&key=". $key;
            return md5($prestr);
      }

      public function createLinkstring($para) {
            $arg  = "";
            while (list ($key, $val) = each ($para)) {
                  $arg.=$key."=".$val."&";
            }
            $arg = substr($arg,0,count($arg)-2);
            if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
            return $arg;
      }  
}

?>


