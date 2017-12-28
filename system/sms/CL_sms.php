<?php
 
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['server_url'] = 'http://222.73.117.156/';
	
    $module['class_name']    = 'CL';
    /* 名称 */
    $module['name']    = "创蓝信平台";

    if(ACTION_NAME == "install" || ACTION_NAME == "edit"){
	    require_once APP_ROOT_PATH."system/sms/CL/transport.php";
		$tran = new transport();
		$install_info = $tran->request($module['server_url']."data/install.php");
		$install_info = json_decode($install_info['body'],1);
		
	    $module['lang']  = $install_info['lang'];
	    $module['config'] = $install_info['config'];	
    }

    return $module;
}

// 企信通短信平台
require_once APP_ROOT_PATH."system/libs/sms.php";  //引入接口
require_once APP_ROOT_PATH."system/sms/CL/transport.php";
class CL_sms implements sms
{
	public $sms;
	public $message = "";
	
    public function __construct($smsInfo = '')
    { 	   
		if(!empty($smsInfo))
		{
			$this->sms = $smsInfo;
		}
    }
	
	public function sendSMS($mobile_number,$content,$is_adv=0)
	{
		if(isIpAllow()){
			if(is_array($mobile_number)){
				$mobile_number=$mobile_number[0];
			}
			$post_data = array();
			$post_data['pswd'] =$this->sms['password'];
			$post_data['account'] = $this->sms['user_name'];
			$post_data['mobile'] = $mobile_number; //手机号码，多个用英文逗号隔开，推荐群发一次少于1000条
			$post_data['msg'] = $content;
			$post_data['needstatus'] = 'true';
			$post_data['product'] = ''; //定时时间 格式为2011-6-29 11:09:21
			$post_data['extno'] = ''; //默认空 如果空返回系统生成的标识串 如果传值保证值唯一 成功则返回传入的值
			$url='http://222.73.117.156/msg/HttpBatchSendSM?';
			$data = http_build_query($post_data);
			$curl = curl_init();    //启动一个curl会话
			curl_setopt($curl, CURLOPT_URL, $url);   //要访问的地址
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
			curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
			curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
			curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
			curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
			curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
			$return = curl_exec($curl);  //执行curl会话
			//print_r($return);exit;
			curl_close($curl);      //关闭curl会话
			//$result['status'] = $return['status'];
			$result['status'] = 1;
			//$result['msg'] = $return['msg'];
			$result['msg'] = "";
			return $result;
		}else{
			$result['status'] = 0;
			$result['msg'] = "系统限制IP发送";
		}
	}
	
	public function getSmsInfo()
	{	
		return "创蓝信平台";
	}
	
	public function check_fee()
	{
 	  	$post_data = array();
		$post_data['account'] = $this->sms['user_name'];
		$post_data['pswd'] =$this->sms['password'];
		$url='http://222.73.117.169/msg/QueryBalance';
		$postFields = http_build_query($post_data);
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postFields );
		$result = curl_exec ( $ch );
		curl_close ( $ch );
		return $result;
	}
}
?>