<?php
require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
class welfareModule extends SiteBaseModule{

    public function index() {
        $user_id = $GLOBALS['user_info']["id"];
        //邀请
        $code = $GLOBALS['user_info']['mobile'];		
        /*移动端交互处理*/
        $jump = machineInfo();
        if($jump['index'] == "ToHomePage"){
            jumpUrl("jump_url_invite");
            $wap_cloumn_url = "http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=28&code=".$code;
        }else{
            $wap_cloumn_url=WAP_SITE_DOMAIN."/index.php?ctl=invite";
        } 
        
        $GLOBALS['tmpl']->assign("wap_cloumn_url",$wap_cloumn_url);      
        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->assign('user_id',$user_id);
		$GLOBALS['tmpl']->assign("cate_title","新手福利");
    	$GLOBALS['tmpl']->display("page/welfare_index.html");
    }
	public function delegate(){
		$Publics = new Publics();
		$seqno = $Publics->seqno();
            $map['reqHeader'] = $Publics->reqheader("U00004");
            $map['inBody']['customerId'] = "1123896";//会员编号
			$map['inBody']['businessSeqNo'] = $seqno;//业务流水号
			$map['inBody']['busiTradeType'] = "B04";//业务操作类型
			$map['inBody']['fundTradetype'] = "T01";//资金交易类型
			$map['inBody']['protocolNo'] = "123456";//协议号
			$map['inBody']['note'] = '';//备注
			$dep = $Publics->sign($map);//签名
			$map['reqHeader']['signTime'] = $dep['signTime'];
            $map['reqHeader']['signature'] = $dep['signature'];
			foreach($map['inBody'] as $k=>$v){
				if($k=="protocolNo"){
					$map['inBody'][$k] = $Publics->encrypt($v['protocolNo']);
					
				}elseif($k=="customerId"){
					$map['inBody'][$k] = $Publics->encrypt($v['customerId']);
				}
			} 
            $url = "https://36.110.98.254:19002/p2pwg/JCT";  
			$da=json_encode($map);
            $ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode($map));
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt ( $ch, CURLOPT_TIMEOUT,40);//单位S 秒
			$datas = curl_exec ( $ch );
			curl_close ( $ch );
			$data['a']=$datas;
			$data['dat']=$da;
			var_dump($data);die;
	}
}
?>