<?php
//存管提现
class Withdraw{
    /*
     * 存管平台账户提现
     * $SeqNo:业务流水号  $type：操作类型   $money：操作金额   $card：对公户
    */
    function withdraws($SeqNo,$type,$money,$card){
        $Publics = new Publics();
        $map['reqHeader'] = $Publics->reqheader("KPCZ01");
        $map['inBody']['businessSeqNo'] = $SeqNo;//业务流水号
        $map['inBody']['businessOrderNo'] = "";//订单流水号
        $map['inBody']['rType'] = $type;//类型---W02：营销提现  W03：代偿提现  W04:费用提现  W05：垫资提现
        $map['inBody']['entrustflag'] = "00";//委托标识-未委托
        $map['inBody']['accountList'] = array(array("oderNo"=>"1","debitAccountNo"=>"","cebitAccountNo"=>$card,"currency"=>"CNY","amount"=>$money,"otherAmounttype"=>"","otherAmount"=>""));//资金账务处理列表
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
        $result=$DepSdk->withdraw($deps);
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
    
    
}

?>