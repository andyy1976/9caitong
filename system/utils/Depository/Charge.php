<?php
require APP_ROOT_PATH."system/utils/Depository/Require.php";
class Charge{
            /*
             * 存管平台账户充值
            * $SeqNo:业务流水号  $type：操作类型   $money：操作金额  $card：对公户
            */
            function charges($SeqNo,$type,$money,$card){
                $Publics = new Publics();
            $map['reqHeader'] = $Publics->reqheader("KPCZ01");
            $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
            $map['inBody']['businessOrderNo'] = "";//订单流水号
            $map['inBody']['rType'] = $type;//类型---R03：营销充值  R04:代偿充值  R05：费用充值  R06：垫资充值
            $map['inBody']['entrustflag'] = "00";//委托标识-未委托
            $map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>$card,"cebitAccountNo"=>"","currency"=>"CNY","amount"=>$money,"otherAmounttype"=>"","otherAmount"=>""));//资金账务处理列表
            $map['inBody']['payType'] = "00";//支付方式--00:银行支付渠道 01：第三方支付渠道
            $map['inBody']['busiBranchNo'] = "";//支付公司代码
            $map['inBody']['bankAccountNo'] = "";//银行卡号
            $map['inBody']['secBankaccNo'] = "";//二类户账户
            $map['inBody']['note'] = "";//备注
            $dep = $Publics->sign($map);//签名
            $map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
            $map['inBody']['ownerName'] = "";//持卡人姓名
            $map['inBody']['ownerCertNo'] = "";//持卡人身份证号
            $map['inBody']['ownerMobile'] = "";//持卡人手机号
            $map['inBody']['bankId'] = "";//银行ID
            $map['inBody']['bankName'] = "";//银行名称
            $map['inBody']['cardType'] = "";//卡类型
            $map['inBody']['identifycode'] = "";//短信验证码
            $deps = $Publics->encrypt(json_encode($map));
            $DepSdk = new DepSdk();
            $result=$DepSdk->charge($deps);
                  $data['seqno'] = $SeqNo;
                  $data['form_con'] = json_encode($map);
                  $data['back_con'] = json_encode($result);
                  $data['callback_con'] = $result['respHeader']['respMsg'];
                  $data['type'] = $type;
                  $data['money'] = $money;
                  $data['add_time'] = time();
                  $data['date_time'] = date("Y-m-d H:i:s");
                  $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
                  return $result;
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
                  "ACCT_NAME"=>$corpacc['username'],
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
            $method ="post";
            $button_name ="确定";
            $sHtml = "<form id='enterpri' name='llpaysubmit' target='_blank'  action='" . $url . "' method='" . $method . "'>";
            $sHtml .= "<input type='hidden' name='OUT_ID' value='" . $para['OUT_ID'] . "'/>";
            $sHtml .= "<input type='hidden' name='TRAN_TIME' value='" . $para['TRAN_TIME'] . "'/>";
            $sHtml .= "<input type='hidden' name='GOODS_NAME' value='" . $para['GOODS_NAME'] . "'/>";
            $sHtml .= "<input type='hidden' name='COMM_NO' value='" . $para['COMM_NO'] . "'/>";
            $sHtml .= "<input type='hidden' name='ORDER_NO' value='" . $para['ORDER_NO'] . "'/>";
            $sHtml .= "<input type='hidden' name='USER_IP' value='" . $para['USER_IP'] . "'/>";
            $sHtml .= "<input type='hidden' name='ACCT_NO' value='" . $para['ACCT_NO'] . "'/>";
            $sHtml .= "<input type='hidden' name='ACCT_NAME' value='" . $para['ACCT_NAME'] . "'/>";
            $sHtml .= "<input type='hidden' name='B_ACCT_NO' value='" . $para['B_ACCT_NO'] . "'/>";
            $sHtml .= "<input type='hidden' name='B_ACCT_NAME' value='" . $para['B_ACCT_NAME'] . "'/>";
            $sHtml .= "<input type='hidden' name='SUB_SOURCE_TYPE' value='" . $para['SUB_SOURCE_TYPE'] . "'/>";
            $sHtml .= "<input type='hidden' name='PAY_AMT' value='" . $para['PAY_AMT'] . "'/>";
            $sHtml .= "<input type='hidden' name='BC_FLAG' value='" . $para['BC_FLAG'] . "'/>";
            $sHtml .= "<input type='hidden' name='FRONT_URL' value='" . $para['FRONT_URL'] . "'/>";
            $sHtml .= "<input type='hidden' name='SIGNATURE' value='" . $para['sign'] . "'/>";
            $sHtml = $sHtml . "<input type='submit' value='" . $button_name . "'></form>";
            $sHtml = $sHtml."<script>document.forms['enterpri'].submit();</script>";
            return $sHtml;
      }

      public function buildRequestPara($para_temp,$http_type,$rec_type) {
            $para_filter = $this->paraFilter($para_temp);
            $para_sort = $this->argSort($para_filter);
            $mysign = $this->buildRequestMysign($para_sort);
            $para_sort['sign'] = strtolower($mysign);
            if($rec_type == "0"){ 
                  $para_sort['BC_FLAG'] ="2";
            }else{ 
                  $para_sort['BC_FLAG'] ="1";
            }
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
            #测试
            #$key ="87FB9444028A4B14937A1905";
            #线上的
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

//end      
}