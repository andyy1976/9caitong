<?php
class Publics{
    //公共报文头
    function reqheader($txnType){
        $depo['version'] = "1.0";//版本号ID
        $depo['merchantCode'] = "JCT";//接入系统号
        $depo['txnType'] = $txnType;//交易代码
        $depo['clientSn'] = "jct".TIME_UTC;//客户端请求流水号
        $depo['clientDate'] = date('Ymd');//客户端日期
        $depo['clientTime'] = date('Hms');//客户端时间戳
        $depo['fileName'] = "";//文件名称
        $depo['signTime'] = "";//加签时间戳
        $depo['signature'] = "";//签名
        return $depo;
    }

    /*
    * 交易密码
    *$type 1:设置    2：修改    3：重置    4：验证
    */
    /*function verify_trans_password($module,$action,$userId,$type,$SeqNo,$targ='_blank'){
        $is_mobile = isMobile();
        switch($type){
            case 1:
                $url = 'https://test-p2.heepay.com/passWord/PASSWORDSETTING';
                $yb_url = 'https://36.110.98.254:19001/p2ph5/pc/enterPassword.html';
                $data['type'] = 'J01';
                break;
            case 2:
                $url = 'https://test-p2.heepay.com/passWord/PASSWORDMODIFY';
                $yb_url = 'https://36.110.98.254:19001/p2ph5/pc/changePassword.html';
                $data['type'] = 'J02';
                break;
            case 3:
                $url = 'https://test-p2.heepay.com/passWord/passWordReSetting';
                $yb_url = 'https://36.110.98.254:19001/p2ph5/pc/forgetPassword.html';
                $data['type'] = 'J03';
                break;
            case 4:
                $url = 'https://test-p2.heepay.com/passWord/PASSWORDVERIFY';
                $yb_url = 'https://36.110.98.254:19001/p2ph5/pc/checkPassword.html';
                $data['type'] = 'J04';
                break;
        }
        $data['user_id'] = intval($userId);
        $data['seqno'] = $SeqNo;
        $data['add_time'] = TIME_UTC;
        $data['date_time'] = date("Y-m-d H:i:s");
        //$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
        $backurl = urlencode("https://" . $_SERVER['HTTP_HOST'] . "/".$module."/".$action); 
        list($msec, $sec) = explode(' ', microtime());
        $signtime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $signature = $signtime."|".$data['user_id'];
        $data_content = $this->rsa_encrypt($signature); //RSA加密
        //echo $data_content.'-------------';
        $url = $url."?systemCode=JCT&userId=".$data['user_id']."&backURL=".$backurl."&signTime=".$signtime."&signature=".$data_content."&businessSeqNo=".$SeqNo;    
        $curl = curl_init();    //启动一个curl会话
        curl_setopt($curl, CURLOPT_URL, $url);   //要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
       // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
       // curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
       // curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 1); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $output = curl_exec($curl);  //执行curl会话 
        curl_close($curl);
        echo $output;die;

        $arr = explode("Location:", $output);
        $real_arr = explode("\n", $arr[1]);
        $con = trim($real_arr[0]);
    
        
        $yb_con_arr = explode("?", $con);
     

        $yb_con = str_replace($yb_con_arr[0], "", $con);
     
        if($is_mobile){
            $y_url = $yb_con_arr[0];
        }else{  
            $y_url = $yb_url;
        }
        if($type==4){
            $html = "<form action='".$y_url."' method='get' enctype='text/plain' target='".$targ."' id='form1'>";
            $cd_con = str_replace("?", "", $yb_con);
            $arr_con = explode("&", $cd_con);
            foreach($arr_con as $k=>$v){
                $abc = explode("=", $v);
                if($abc[0] == "signature"){
                    $html .="<input type='hidden' name='".$abc[0]."' value='".$data_content."'>";
                }elseif($abc[0] == "backURL"){
                    $html .="<input type='hidden' name='".$abc[0]."' value='"."https://" . $_SERVER['HTTP_HOST'] . "/dep/pwd_call_back'>";
                }else{
                    $html .="<input type='hidden' name='".$abc[0]."' value='".$abc[1]."'>";
                }           
            }
            $html .= "         
                 </form>
                <script>
                    document.getElementById('form1').submit();
                </script>";
            $data['form_con'] = $html;
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT"); 
            return $html;
        }else{
            $y_url .= "?";
            $cd_con = str_replace("?", "", $yb_con);
            $arr_con = explode("&", $cd_con);
            foreach($arr_con as $k=>$v){
                //unset($abc);
                $abc = explode("=", $v);
                if($abc[0] == "signature"){
                    $y_url .=$abc[0]."=".$data_content."&";
                }elseif($abc[0] == "backURL"){
                    $y_url .=$abc[0]."="."https://" . $_SERVER['HTTP_HOST'] . "/dep/pwd_call_back&";
                }else{
                    $y_url .= $abc[0]."=".$abc[1]."&";
                }
            }
            $y_url = rtrim($y_url,'&');
            echo "<script>window.location.href='".$y_url."'</script>";
        }
    }*/
	function verify_trans_password($module,$action,$userId,$type,$SeqNo,$targ='_blank'){
        $is_mobile = isMobile();
        switch($type){
            case 1:
				if($is_mobile){
					$url = 'https://36.110.98.254:19001/p2ph5/standard/enterPassword.html';
				}else{  
					$url = 'https://36.110.98.254:19001/p2ph5/pc/enterPassword.html';
				}
                
                $data['type'] = 'J01';
                break;
            case 2:
				if($is_mobile){
					$url = 'https://36.110.98.254:19001/p2ph5/standard/changePassword.html';
				}else{  
					 $url = 'https://36.110.98.254:19001/p2ph5/pc/changePassword.html';
				}
                
                $data['type'] = 'J02';
                break;
            case 3:
				if($is_mobile){
					$url = 'https://36.110.98.254:19001/p2ph5/standard/forgetPassword.html';
				}else{  
					 $url = 'https://36.110.98.254:19001/p2ph5/pc/forgetPassword.html';
				}
                
                $data['type'] = 'J03';
                break;
            case 4:
				if($is_mobile){
					$url = 'https://36.110.98.254:19001/p2ph5/standard/checkPassword.html';
				}else{  
					 $url = 'https://36.110.98.254:19001/p2ph5/pc/checkPassword.html';
				}
                
                $data['type'] = 'J04';
                break;
        }
        $data['user_id'] = intval($userId);
        $data['seqno'] = $SeqNo;
        $data['add_time'] = TIME_UTC;
        $data['date_time'] = date("Y-m-d H:i:s");
        //$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
        $backurl = urlencode("https://" . $_SERVER['HTTP_HOST'] . "/".$module."/".$action); 
        list($msec, $sec) = explode(' ', microtime());
        $signtime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $signature = $signtime."|".$data['user_id'];
        $data_content = $this->rsa_encrypt($signature); //RSA加密
        //echo $data_content.'-------------';
		$urls = "?systemCode=JCT&userId=".$data['user_id']."&backURL=".$backurl."&signTime=".$signtime."&signature=".$data_content."&businessSeqNo=".$SeqNo;
        $url = $url.$urls;  
		if($type==4){
            $html = "<form action='".$url."' method='get' enctype='text/plain' target='".$targ."' id='form1'>";
            $cd_con = str_replace("?", "", $urls);
            $arr_con = explode("&", $cd_con);
            foreach($arr_con as $k=>$v){
                $abc = explode("=", $v);
                if($abc[0] == "signature"){
                    $html .="<input type='hidden' name='".$abc[0]."' value='".$data_content."'>";
                }elseif($abc[0] == "backURL"){
                    $html .="<input type='hidden' name='".$abc[0]."' value='"."https://" . $_SERVER['HTTP_HOST'] . "/".$module."/".$action."'>";
                }else{
                    $html .="<input type='hidden' name='".$abc[0]."' value='".$abc[1]."'>";
                }           
            }
            $html .= "         
                 </form>
                <script>
                    document.getElementById('form1').submit();
                </script>";
            $data['form_con'] = $html;
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT"); 
            return $html;
        }else{
			$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
            echo "<script>window.location.href='".$url."'</script>";
        }
        
    }

    function call_back($data){
        unset($data['ctl']);
        unset($data['act']);
        $str = "";
        foreach($data as $k=>$v){
            if($k == 'signature'){
                $str .= $k . "=" . str_replace(' ','+',$v) . "&";
            }else {
                $str .= $k . "=" . $v . "&";
            }
        }
        $str = rtrim($str,'&');
        $url = "https://test-p2.heepay.com/passWord/PASSWORDVERIFYBACK";
        $url = $url."?".$str;
        $curl = curl_init();    //启动一个curl会话
        curl_setopt($curl, CURLOPT_URL, $url);   //要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        // curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        // curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 1); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $output = curl_exec($curl);  //执行curl会话
        curl_close($curl);
        $arr = explode("Location:", $output);
        //$arr_one = explode("?userId", $arr[1]);
        if($data['flag']==1) {
            header('Location: ' . $arr[1]);
        }else{
            $is_mobile = isMobile();
            if($is_mobile){
                header('Location:https://jctwapcg.9caitong.com');
            }else{
                header('Location:https://jctcg.9caitong.com');
            }
        }
    }
    //流水号
    function seqno(){
        $yCode = 'JCT';
        $orderSn = $yCode.date('Y'). strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }
    
    //加签
    function sign($data=array()){
        if($data['inBody']['returnInfoList']){
            foreach ($data['inBody']['returnInfoList'] as $ke=>$ve) {
                $data['inBody']['returnNo'.$ve['oderNo']] = $ve['returnNo'];
                $data['inBody']['returnDate'.$ve['oderNo']] = $ve['returnDate'];
            }
            unset($data['inBody']['returnInfoList']);
        }
        if($data['inBody']['accountList']){
            foreach ($data['inBody']['accountList'] as $kd=>$vd) {
                foreach($vd as $ks=>$vs){
                    if($ks!='oderNo') {
                        $data['inBody'][$ks . $vd['oderNo']] = $vs;
                    }
                }
            }
            unset($data['inBody']['accountList']);
        }
        if($data['inBody']['contractList']){
            foreach ($data['inBody']['contractList'] as $kd=>$vd) {
                foreach($vd as $ks=>$vs){
                    if($ks!='oderNo') {
                        $data['inBody'][$ks . $vd['oderNo']] = $vs;
                    }
                }
            }
            unset($data['inBody']['contractList']);
        }
            unset($data['inBody']['contractList']);
        list($msec, $sec) = explode(' ', microtime());
        $signtime = (string)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $signature = $signtime;
        ksort($data['inBody']);
        foreach($data['inBody'] as $k=>$v){
                $signature = $signature."|".$v;
        }
        $data_content = $this->rsa_encrypt($signature); //RSA加密
        $map['signTime'] = $signtime;
        $map['signature'] = $data_content;
        $map['aa'] = $signature;
        return $map;
    }
    //验签
    function out_sign($data=array()){
        $data_content = $this->rsa_decrypt($data['reqHeader']['signature']);
        if($data_content){
            $map = explode("|",$data_content);
            if($map){

            }
        }else{
            $res['rescode'] = 0;
            $res['resmsg'] = "验签失败";
            return $res;
        }
    }
    
    //RSA加密解密----开始
    //私钥加密
    function rsa_encrypt($data){
        $private_key = file_get_contents(APP_ROOT_PATH."system/utils/Depository/jct/private_key.pem");
        openssl_sign($data,$encrypted,$private_key, OPENSSL_ALGO_SHA256);
        //$pi_key =  openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        //openssl_private_encrypt($data,$encrypted,$pi_key);//私钥加密
        $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return $encrypted;
    }

    //公钥解密
    function rsa_decrypt($data){
        $public_key = file_get_contents(APP_ROOT_PATH."system/utils/Depository/yibin/YB_PublicKey.pem");
        $pu_key = openssl_pkey_get_public($public_key);//这个函数可用来判断公钥是否是可用的
        openssl_public_decrypt(base64_decode($data),$decrypted,$pu_key);//私钥加密的内容通过公钥可用解密出来
        return $decrypted;
    }
    //RSA加密解密----结束

    //AES加密解密----开始
    //加密
    function encrypt($encryptStr) {
        $localIV = '1a2b3c4c5d6f7d8e';
        $encryptKey = 'JCT123';
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);
        mcrypt_generic_init($module, $encryptKey, $localIV);
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block - (strlen($encryptStr) % $block);
        $encryptStr .= str_repeat(chr($pad), $pad);
        $encrypted = mcrypt_generic($module, $encryptStr);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        return base64_encode($encrypted);
    }

    //解密
    function decrypt($encryptStr) {
        $localIV = '1a2b3c4c5d6f7d8e';
        $encryptKey = 'JCT123';
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);
        mcrypt_generic_init($module, $encryptKey, $localIV);
        $encryptedData = base64_decode($encryptStr);
        $encryptedData = mdecrypt_generic($module, $encryptedData);
		mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
		$encryptedData = $this->trimEnd($encryptedData);
        return $encryptedData;
    }
    //AES加密解密----结束
	//将解密后多余的长度去掉(因为在加密的时候 补充长度满足block_size的长度)    
	function trimEnd($text){    
		$len = strlen($text);    
		$c = $text[$len-1];    
		if(ord($c) <$len){    
			for($i=$len-ord($c); $i<$len; $i++){    
				if($text[$i] != $c){    
					return $text;    
				}    
			}    
			return substr($text, 0, $len-ord($c));    
		}    
		return $text;    
	}
}
?>