<?php
/**
* wap手机版本发现栏目
*/
require_once APP_ROOT_PATH."system/libs/user.php";
require APP_ROOT_PATH.'app/Lib/phpqrcode.php';
class inviteModule extends SiteBaseModule
{
	public function index(){

		//判断是否是企业用户
        if($GLOBALS['user_info']['user_type']){
            $jump = url("index","find#warning");
            if(WAP==1) app_redirect(url("index","find#warning"));
            showErr("企业用户暂不可使用",0,$jump);
        }
		$id=$GLOBALS['user_info']['id'];
		$mobile = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile FROM ".DB_PREFIX."user WHERE id=".$id);
		/******二维码生成********/
// 		$value = "http://".$_SERVER['HTTP_HOST']."/index.php?ctl=user&act=register&code=".$mobile;//二维码数据
		//$value = "http://".$_SERVER['HTTP_HOST']."/index.php?ctl=user%26act=register%26code=".$mobile;//二维码数据
		$value = "http://jctwapcg.9caitong.com/index.php?ctl=user&act=wapRegister&code=".$mobile;//二维码数据
		
// 		$qcode_url = "http://qr.topscan.com/api.php?text=".$value."&logo=".$_SERVER['HTTP_HOST']."/logo.png";
		/*
		$errorCorrectionLevel = 'L';//纠错级别：L、M、Q、H
		$matrixPointSize = 10;//二维码点的大小：1到10
		QRcode::png ( $value, 'ewm.png', $errorCorrectionLevel, $matrixPointSize, 2 );//不带Logo二维码的文件名
		$logo = 'logo.jpg';//需要显示在二维码中的Logo图像
		$QR = 'ewm.png';
		if ($logo !== FALSE) {
		    $QR = imagecreatefromstring ( file_get_contents ( $QR ) );
		    $logo = imagecreatefromstring ( file_get_contents ( $logo ) );
		    $QR_width = imagesx ( $QR );
		    $QR_height = imagesy ( $QR );
		    $logo_width = imagesx ( $logo );
		    $logo_height = imagesy ( $logo );
		    $logo_qr_width = $QR_width / 5;
		    $scale = $logo_width / $logo_qr_width;
		    $logo_qr_height = $logo_height / $scale;
		    $from_width = ($QR_width - $logo_qr_width) / 2;
		    imagecopyresampled ( $QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height );
		}
		imagepng ( $QR, 'ewmlogo.png' );//带Logo二维码的文件名
		*/
		/*******输出邀请规则说明*******/
		$invite_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'invite_explain'"));
		$GLOBALS['tmpl']->assign("invite_explain",$invite_explain);
		/*****判断移动端设备类型*********/
		$MachineInfo = explode("|||",es_session::get('MachineInfo'));
		switch ($MachineInfo[0]) {
			case 'iOS':
				$jump['wx'] = "WxScript()";
				$jump['pyq'] = "WxqScript()";
				$jump['qq'] = "QqScript()";
				$jump['login'] = "iosLogin";
				break;
			case 'Android':
				$jump['wx'] = "WxAndroid()";
				$jump['pyq'] = "WxqAndroid()";
				$jump['qq'] = "QqAndroid()";
				$jump['login'] = "AndroidLogin";
				break;
			default:
				$jump['wx'] = "popBox('popBox_invite')";
				$jump['pyq'] = "popBox('popBox_invite')";
				$jump['qq'] = "popBox('popBox_invite')";
				$jump['login'] = "login";
				break;
		}
		$jumpUrl = es_cookie::get("jump_url_invite");
		$GLOBALS['tmpl']->assign('jumpUrl',$jumpUrl);
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("url",WAP_SITE_DOMAIN);
		$GLOBALS['tmpl']->assign("wximg",$value);
		$GLOBALS['tmpl']->assign("user_id",$id);
		$GLOBALS['tmpl']->assign("mobile",$mobile);
		$GLOBALS['tmpl']->assign('cate_title',"邀请好友");
		//$GLOBALS['tmpl']->display("page/invite.html");
		$GLOBALS['tmpl']->display("page/fi_invite_new.html");
	}
	public function invite_record(){;
		require APP_ROOT_PATH.'app/Lib/uc.php';
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$friend_list = $GLOBALS['db']->getAll("SELECT u.id,u.cunguan_tag,u.cunguan_pwd,u.cunguan_register,u.mobile,u.real_name,u.idcardpassed,sum(d.money) as money,u.create_date FROM ".DB_PREFIX."user u left join ".DB_PREFIX."deal_load d on u.id = d.user_id WHERE u.pid=".$GLOBALS['user_info']['id']." group by u.id limit $limit");
		foreach ($friend_list as $k => $v) {
			$v['type'] = "注册";
			$v['invite_type'] = "未完成";
			if(!$v['real_name']){
				$v['real_name'] = hideMobile($v['mobile']);
			}else{
				$v['real_name'] = cut_str($v['real_name'], 0, 0).'**'.cut_str($v['real_name'], 1, -1);
			}
			if($v['cunguan_register'] == 0){
				if($v['money']){
					$v['type'] = "出借";
					if($v['money'] >= 1000){
						$v['invite_type'] = "成功";
					}
				}else if($v['idcardpassed'] == 1){
					$v['type'] = "认证";
				}

			}else{
				if($v['money']){
					$v['type'] = "出借";
					if($v['money'] >= 1000){
						$v['invite_type'] = "成功";
					}
				}else if($v['cunguan_tag'] == 1){
					$v['type'] = "认证";
				}
			}
			$list[] = $v;
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));
		$GLOBALS['tmpl']->assign('cate_title',"邀请记录");
		$GLOBALS['tmpl']->display("page/invite_record.html");
	}
	public function invete_repair(){
		$referer = $GLOBALS['db']->getOne("SELECT referer FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign('referer',$referer);	
		$GLOBALS['tmpl']->assign('cate_title',"补填邀请码");
		if($referer){
			$GLOBALS['tmpl']->display("page/invete_repair_success.html");
		}else{
			$GLOBALS['tmpl']->display("page/invete_repair.html");
		}	
		
	}
	public function invite_change(){
		$GLOBALS['tmpl']->assign('cate_title',"更新邀请码");
		$GLOBALS['tmpl']->display("page/invite_change.html");
	}
	public function ajaxInvite(){        
        echo $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user WHERE pid=".$GLOBALS['user_info']['id']);
    }
    public function inviteList(){
    	require APP_ROOT_PATH.'app/Lib/uc.php';
    	$page = $_REQUEST['page'];
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$friend_list = $GLOBALS['db']->getAll("SELECT u.id,u.cunguan_tag,u.cunguan_pwd,u.cunguan_register,u.mobile,u.real_name,u.idcardpassed,sum(d.money) as money,u.create_date FROM ".DB_PREFIX."user u left join ".DB_PREFIX."deal_load d on u.id = d.user_id WHERE u.pid=".$GLOBALS['user_info']['id']." group by u.id limit $limit");
		foreach ($friend_list as $k => $v) {
			$v['type'] = "注册";
			$v['invite_type'] = "未完成";
			if(!$v['real_name']){
				$v['real_name'] = hideMobile($v['mobile']);
			}
			if($v['cunguan_register'] == 0){
				if($v['money']){
					$v['type'] = "出借";
					if($v['money'] >= 1000){
						$v['invite_type'] = "成功";
					}
				}else if($v['idcardpassed'] == 1){
					$v['type'] = "认证";
				}

			}else{
				if($v['money']){
					$v['type'] = "出借";
					if($v['money'] >= 1000){
						$v['invite_type'] = "成功";
					}
				}else if($v['cunguan_tag'] == 1){
					$v['type'] = "认证";
				}
			}
			$list[] = $v;
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));
		if (empty($list)) {
                echo 'false';
        }else{
           	$GLOBALS['tmpl']->assign("list",$list);
            $info = $GLOBALS['tmpl']->fetch("page/inviteCarryList.html");
            echo $info;
        }
		
    }
	public function newindex(){
		//判断是否是企业用户
        if($GLOBALS['user_info']['user_type']){
            $jump = url("index","find#warning");
            if(WAP==1) app_redirect(url("index","find#warning"));
            showErr("企业用户暂不可使用",0,$jump);
        }
		$id=$GLOBALS['user_info']['id'];
		if(!$id){
			app_redirect(url("index","user#login"));
		}
		$mobile = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile FROM ".DB_PREFIX."user WHERE id=".$id);
		$value = "http://jctwapcg.9caitong.com/index.php?ctl=user&act=wapRegister&code=".$mobile;//二维码数据
		/*******输出邀请规则说明*******/
		$invite_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'invite_explain'"));
		$GLOBALS['tmpl']->assign("invite_explain",$invite_explain);
		/*****判断移动端设备类型*********/
		$MachineInfo = explode("|||",es_session::get('MachineInfo'));
		switch ($MachineInfo[0]) {
			case 'iOS':
				$jump['wx'] = "WxScript()";
				$jump['pyq'] = "WxqScript()";
				$jump['qq'] = "QqScript()";
				$jump['login'] = "iosLogin";
				break;
			case 'Android':
				$jump['wx'] = "WxAndroid()";
				$jump['pyq'] = "WxqAndroid()";
				$jump['qq'] = "QqAndroid()";
				$jump['login'] = "AndroidLogin";
				break;
			default:
				$jump['wx'] = "popBox('popBox_invite')";
				$jump['pyq'] = "popBox('popBox_invite')";
				$jump['qq'] = "popBox('popBox_invite')";
				$jump['login'] = "login";
				break;
		}
		$jumpUrl = es_cookie::get("jump_url_invite");
		$GLOBALS['tmpl']->assign('jumpUrl',$jumpUrl);
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("url",WAP_SITE_DOMAIN);
		$GLOBALS['tmpl']->assign("wximg",$value);
		$GLOBALS['tmpl']->assign("user_id",$id);
		$GLOBALS['tmpl']->assign("mobile",$mobile);
		$GLOBALS['tmpl']->assign('cate_title',"邀请好友");
		$GLOBALS['tmpl']->display("page/fi_invite_new.html");
	}
	 public function newinvite_record(){
		 require APP_ROOT_PATH.'app/Lib/uc.php';
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$friend_list = $GLOBALS['db']->getAll("SELECT u.id,u.cunguan_tag,u.cunguan_pwd,u.cunguan_register,u.mobile,u.real_name,u.idcardpassed,sum(d.money) as money,u.create_date FROM ".DB_PREFIX."user u left join ".DB_PREFIX."deal_load d on u.id = d.user_id WHERE u.pid=".$GLOBALS['user_info']['id']." group by u.id limit $limit");
		foreach ($friend_list as $k => $v) {
			$v['type'] = "注册";
			$v['invite_type'] = "未完成";
			if(!$v['real_name']){
				$v['real_name'] = hideMobile($v['mobile']);
			}else{
				$v['real_name'] = cut_str($v['real_name'], 0, 0).'**'.cut_str($v['real_name'], 1, -1);
			}
			if($v['cunguan_register'] == 0){
				if($v['money']){
					$v['type'] = "出借";
					if($v['money'] >= 1000){
						$v['invite_type'] = "成功";
					}
				}else if($v['idcardpassed'] == 1){
					$v['type'] = "认证";
				}

			}else{
				if($v['money']){
					$v['type'] = "出借";
					if($v['money'] >= 1000){
						$v['invite_type'] = "成功";
					}
				}else if($v['cunguan_tag'] == 1){
					$v['type'] = "认证";
				}
			}
			$list[] = $v;
		}
		$GLOBALS['tmpl']->assign('list',$list);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));
		$GLOBALS['tmpl']->assign('cate_title',"邀请记录");
		$GLOBALS['tmpl']->display("page/fi_invite_record_new.html");
	 }
	 public function newinvite_rule(){
		 $GLOBALS['tmpl']->assign('cate_title',"邀请好友");
		$GLOBALS['tmpl']->display("page/fi_invite_rule_new.html");
	 }
}
?>