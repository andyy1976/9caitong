<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/BAOFOOSDK/ini.php';
    $TransID = $_REQUEST["TransID"];//商户订单号
    $trans_id="TSN".get_transid().rand4();//商户订单号
    //=================================================
    $version = "4.0.0.1";//版本号
    $terminal_id = $GLOBALS["terminal_id"];//商户号
    $member_id = $GLOBALS["member_id"];	//终端号
    //======================FORM===========================
    $input_charset = "1";
    $language = "1";
    $data_type = $GLOBALS["data_type"]; //加密报文的数据类型（xml/json）
    //=====================================================
    $request_url = "http://tgw.baofoo.com/apipay/queryQuickOrder";//请求地址
    $ArrayData=array();
    $ArrayData["orig_trans_id"] = $TransID;
    $ArrayData["trans_serial_no"] = $trans_id;
    $ArrayData["terminal_id"] = $terminal_id;
    $ArrayData["member_id"] = $member_id;
    $ArrayData["additional_info"] = "附加信息";
    $ArrayData["req_reserved"] = "保留";

    
    if($data_type == "json"){
	$Encrypted_string = str_replace("\\/", "/",json_encode($ArrayData));//转JSON
    }else{
	$toxml = new SdkXML();	//实例化XML转换类
	$Encrypted_string = $toxml->toXml($ArrayData);//转XML
    }
    
    
    //Log::LogWirte("请求明文：".$Encrypted_string);
    $baofoosdk = new BaofooSdk($GLOBALS["pfxfilename"],$GLOBALS["cerfilename"],$GLOBALS["private_key_password"]); //实例化加密类。  
    $data_content = $baofoosdk->encryptedByPrivateKey($Encrypted_string);	//RSA加密
    //Log::LogWirte("请求密文：".$data_content);
    
    $PostHead = array();
    $PostHead["version"] = $version;
    $PostHead["input_charset"] = $input_charset;
    $PostHead["language"] = $language;
    $PostHead["terminal_id"] = $terminal_id;
    $PostHead["member_id"] = $member_id;
    $PostHead["data_type"] = $data_type;
    $PostHead["data_content"] = $data_content;
    
    
    $Result = HttpClient::Post($PostHead, $request_url);//发送请求并接收结果
    //Log::LogWirte("返回报文".$Result);
    $Result = $baofoosdk->decryptByPublicKey($Result);
    //Log::LogWirte("解密文明：$Result");
    $endata_content=array();
    if(!empty($Result)){//解析返回参数。
        if($data_type =="xml"){
            $endata_content = SdkXML::XTA($Result);
        }else{
            $endata_content = json_decode($Result,TRUE);
        }
    }else{
        echo "出错！";
        die();
    }
    
    if($endata_content["resp_code"] == "0000"){
        echo "订单状态：".$endata_content["resp_code"].", 返回消息：".$endata_content["resp_msg"].", 成功金额：".$endata_content["succ_amt"].", 商户订单号：".$endata_content["terminal_id"];
        //返回单位为元
    }else{
        echo "订单失败！";
        die();
    }
    
function get_transid(){//生成时间戳
    return strtotime(date('Y-m-d H:i:s',time()));
}
function rand4(){//生成四位随机数
    return rand(1000,9999);
}
function return_time(){//生成时间
    return date('YmdHis',time());
}