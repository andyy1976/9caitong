<?php
header("Content-type: text/html; charset=UTF-8");
class DepSdk{
    //开户、绑卡
    function CustomerInfoSync($data){
        $post_data = $data;
        //$url = "https://test-p2.heepay.com/customer/CustomerInfoSync";
		$url = "https://36.110.98.254:19002/p2pwg/JCT";  
        return $this->curl($post_data,$url);
    }
    //标的信息同步
    function  bidinfosync($data){
        $post_data = $data;
        //$url = "https://test-p2.heepay.com/customer/bidinfosync";
		$url = "https://36.110.98.254:19002/p2pwg/JCT";  
        return $this->curl($post_data,$url);
    }
    //充值
    function  charge($data){
        $post_data = $data;
        //$url = "https://test-p2.heepay.com/customer/charge";
		$url = "https://36.110.98.254:19002/p2pwg/JCT";  
        return $this->curl($post_data,$url);
    }
    //提现
    function  withdraw($data){
        $post_data = $data;
        //$url = "https://test-p2.heepay.com/customer/withdraw";
		$url = "https://36.110.98.254:19002/p2pwg/JCT";  
        return $this->curl($post_data,$url);
    }
    //客户资金交易同步
    function fundTrans($data){
        $post_data = $data;
        //$url = "https://test-p2.heepay.com/fundTrans/fundTrans";
		$url = "https://36.110.98.254:19002/p2pwg/JCT";  
        return $this->curl($post_data,$url);
    }
	//客户资金交易同步
    function newfundTrans($data){
        $post_data = $data;
        //$url = "https://test-p2.heepay.com/newFundTrans/newFundTrans";
		$url = "https://36.110.98.254:19002/p2pwg/JCT";  
        return $this->curl($post_data,$url);
    }
	//组合资金交易同步
    function coFundTrans($data){
        $post_data = $data;
        //$url = "https://test-p2.heepay.com/coFundTrans/coFundTrans";
		$url = "https://36.110.98.254:19002/p2pwg/JCT";  
        return $this->curl($post_data,$url);
    }
    //客户交易状态查询
    function transStatusQuery($data){
        $post_data = $data;
        //$url = "https://test-p2.heepay.com/transStatusQuery/transStatusQuery";
		$url = "https://36.110.98.254:19002/p2pwg/JCT";  
        return $this->curl($post_data,$url);
    }
    //客户数据信息查询
    function dataQuery($data){
        $post_data = $data;
       // $url = "https://test-p2.heepay.com/dataQuery/dataQuery";
	   $url = "https://36.110.98.254:19002/p2pwg/JCT";  
        return $this->curl($post_data,$url);
    }
    //客户委托协议
    function entrustAgreement($data){
        $post_data = $data;
        //$url = "https://test-p2.heepay.com/entrustAgreement/entrustAgreement";
		$url = "https://36.110.98.254:19002/p2pwg/JCT";  
        return $this->curl($post_data,$url);
    }
	//异步处理结果返回
    function customerwithdraw($data){
        $post_data = $data;
        //$url = "https://test-p2.heepay.com/asynNotice/customerwithdraw";
		$url = "https://36.110.98.254:19002/p2pwg/JCT";  
        return $this->curl($post_data,$url);
    }
    //对账文件
    function log($data){
        $post_data = $data;
        //$url = "https://test-p2.heepay.com";
		$url = "https://36.110.98.254:19002/p2pwg/JCT";  
        return $this->curl($post_data,$url);
    }
    function  curl($post_data,$url){
        $curl = curl_init();    //启动一个curl会话
        curl_setopt($curl, CURLOPT_URL, $url);   //要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $output = curl_exec($curl);  //执行curl会话
        curl_close($curl);
        $output_array = json_decode($output,true);
        return $output_array;
    }
}
?>