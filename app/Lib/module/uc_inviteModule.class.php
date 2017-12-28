<?php

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_inviteModule extends SiteBaseModule
{
	public function index()
	{
		//判断是否是企业用户
        if($GLOBALS['user_info']['user_type']){
            $jump = url("index","uc_center#index");
            if(WAP==1) app_redirect(url("index","uc_center#index"));
            showErr("企业用户暂不可使用",0,$jump);
        }
		
		$total_referral_money = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."referrals where rel_user_id = ".$GLOBALS['user_info']['id']." and pay_time > 0");
		
		$GLOBALS['tmpl']->assign("total_referral_money",$total_referral_money);
		
		//$referral_user = get_user_info("count(*)","pid = ".$GLOBALS['user_info']['id']." and is_effect=1 and is_delete=0 AND user_type in(0,1) ","ONE");
		//zhuxiang 2017513
        $referral_user = get_user_info("count(*)","pid = ".$GLOBALS['user_info']['id']." AND user_type in(0,1) ","ONE");
		$GLOBALS['tmpl']->assign("referral_user",$referral_user);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_INVITE']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invite_index.html");
		// 邀请注册的地址  链接到PC端注册
		if(intval(app_conf("URL_MODEL")) == 0)
			$depart="&";
		else
			$depart="?";	
		$share_url = SITE_DOMAIN.url("index","user#register");
		if($GLOBALS['user_info']){
			$share_url_mobile = $share_url.$depart."r=".str_replace('+', '%2b', base64_encode($GLOBALS['user_info']['mobile']));
			$share_url_username = $share_url.$depart."r=".str_replace('+', '%2b', base64_encode($GLOBALS['user_info']['user_name']));
		}
		
		$GLOBALS['tmpl']->assign("share_url_mobile",$share_url_mobile);
		$GLOBALS['tmpl']->assign("share_url_username",$share_url_username);
		// 邀请注册的二维码 链接到wap端注册
		$qrcode_url=WAP_SITE_DOMAIN."/index.php?ctl=user%26act=register%26code=".$GLOBALS['user_info']['mobile'];
		$logo_url=WAP_SITE_DOMAIN."/logo.jpg";
		$GLOBALS['tmpl']->assign("qrcode_url",$qrcode_url);
		$GLOBALS['tmpl']->assign("logo_url",$logo_url);
		// 邀请好友信息
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$invite_sql="SELECT u.id,e.money FROM ".DB_PREFIX."user u LEFT JOIN ".DB_PREFIX."ecv e on u.id=e.child_id where e.user_id=".$GLOBALS['user_info']['id']." and e.user_id=".$GLOBALS['user_info']['id']." and u.pid=".$GLOBALS['user_info']['id'];
		$invite_reward_list=$GLOBALS['db']->getAll($invite_sql);
		$invite_all_list=$GLOBALS['db']->getAll("SELECT AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') AS real_name,AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') AS mobile,create_date,idcardpassed,id from ".DB_PREFIX."user where pid=".$GLOBALS['user_info']['id']." limit ".$limit);
		foreach($invite_all_list as $k=>$v){
			foreach($invite_reward_list as $kk=>$vv){
				if($v['id']==$vv['id']){
					$invite_all_list[$k]['money']=$vv['money'];
				}
			}
		}
		
		$friends_info['list']=$invite_all_list;



		$friends_info['count']=$GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user where pid=".$GLOBALS['user_info']['id']);
		foreach($friends_info['list'] as $k=>$v){
			// 处理被邀请人真实姓名
			$friends_info['list'][$k]['real_name']=str_replace(substr($v['real_name'],0,-3),str_repeat('*', mb_strlen($v['real_name'],'utf-8')-1),$v['real_name']); 
			// 被邀请人id
			$friend_id=$v['id'];
			$sql="SELECT dl.create_date,dl.money,d.name from ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d on dl.deal_id=d.id where dl.user_id=$friend_id";
			$friends_info['list'][$k]['friend_deal_list']=$GLOBALS['db']->getAll($sql);
		}
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($friends_info['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);

		$GLOBALS['tmpl']->assign("friends_info",$friends_info['list']);
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	function invite(){
		$type = intval($_REQUEST['type']);
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$result = get_invite_list($limit,$GLOBALS['user_info']['id'],$type);
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign("type",$type);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invite_list.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	
	function reward(){
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$result['count'] = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."referrals WHERE rel_user_id=".$GLOBALS['user_info']['id']." ORDER BY id DESC");
		if($result['count'] > 0){
			$sql = "SELECT r.*,AES_DECRYPT(u.real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name,AES_DECRYPT(u.email_encrypt,'".AES_DECRYPT_KEY."') as email,AES_DECRYPT(u.idno_encrypt,'".AES_DECRYPT_KEY."') as idno,AES_DECRYPT(u.mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile,u.user_name as re_user_name " .
					"FROM ".DB_PREFIX."referrals r LEFT JOIN ".DB_PREFIX."user u ON u.id = r.user_id " .
					"WHERE r.rel_user_id=".$GLOBALS['user_info']['id']." ORDER BY id DESC LIMIT ".$limit;
					//echo $sql;
			$result['list'] = $GLOBALS['db']->getAll($sql);
		}
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$invite_money=$GLOBALS['db']->getAll("select r.create_time,rpn.grant_type,r.money,r.content from ".DB_PREFIX."red_packet r left join ".DB_PREFIX."red_packet_newconfig rpn on rpn.id=r.red_type_id where r.user_id=".$GLOBALS['user_info']['id']." and (rpn.grant_type = 2 or rpn.grant_type = 3)");
		foreach ($invite_money as $k => $v) {
			$v['create_date'] = date("Y-m-d",$v['create_time']);
			$invite_list[] = $v;
		}
		$GLOBALS['tmpl']->assign('invite_list',$invite_list);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invite_reward.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	/**
	 * 补填邀请码
	 */
	public function fillin(){

		//判断是否是企业用户
        if($GLOBALS['user_info']['user_type']){
            $jump = url("index","uc_center#index");
            if(WAP==1) app_redirect(url("index","uc_center#index"));
            showErr("企业用户暂不可使用",0,$jump);
        }
		$pid=$GLOBALS['user_info']['pid'];
		$vip_id=$GLOBALS['user_info']['vip_id'];
		$friend_info = get_user_info("*","id='".$pid."'");
		if($friend_info & $GLOBALS['user_info']['referer']!=''){
			$GLOBALS['tmpl']->assign("mobile",$friend_info['mobile']);
			$GLOBALS['tmpl']->assign("is_fillin","yes");
		}else{
			$GLOBALS['tmpl']->assign("is_fillin","no");
		}
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_invite_fillin.html");
		$GLOBALS['tmpl']->assign("page_title","补填邀请码");
		$GLOBALS['tmpl']->display("page/uc.html");
	}

	public function referer_deal(){
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		$mobile = strim($_REQUEST["invitecode"]);
		$friend_id = get_user_info("pid,id,real_name","mobile='".$mobile."'");
		$friend_pid = $friend_id['id'];
		$fill_data=$GLOBALS['db']->getRow("select pid,referer from ".DB_PREFIX."user where id=".$GLOBALS['user_info']['id']);
		if(empty($friend_id)){
			$return['status'] = 1;
			$return['info'] = "邀请人不存在!";
		}elseif($mobile==$GLOBALS['user_info']['mobile']){
			$return['status'] = 3;
			$return['info'] = "邀请人不能是自己!";
		}elseif($friend_id['pid'] && $friend_id['pid']==$GLOBALS['user_info']['id']){
			$return['status'] = 1;
			$return['info'] = "不能互相邀请哦!";
		}elseif($fill_data['pid']||$fill_data['referer']){
			$return['status'] = 1;
			$return['info'] = "已存在邀请人!";
		}else{
			$data['pid']=$friend_id['id'];
			$data['referer']=$mobile;
			if(isset($mobile) && !empty($mobile)){
                $data['referer_time']=time();
            }
			$res=$GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);
			if($res){
				$GLOBALS['db']->query("update ".DB_PREFIX."user set referral_count=referral_count+1 where id=".$friend_id['id']);
                //建立的邀请关系的用户，彼此添加为抢红包好友
                $this->insert_packet_friend($GLOBALS['user_info']['id'],$friend_pid);
                $this->insert_packet_friend($friend_pid,$GLOBALS['user_info']['id']);
				$return['status'] = 2;
				$return['info'] = "补填邀请码成功!";
			}else{
				showErr("邀请失败，请返回重试！",1);
			}
			// 判断是否满足给邀请人发放奖励的条件
        	if($res){
//        		// 邀请发放站内信
//        		$notices['site_name'] = app_conf("SHOP_TITLE");
//				$notices['friend_name'] = utf_substr($GLOBALS['user_info']['real_name']);
//				$notices['user_name'] = $friend_id['real_name']?$friend_id['real_name']:'w'.$mobile;
//				$to_user_id=$friend_id['id'];
//				$time=TIME_UTC;
//				$tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_INS_INVITE_REWARDS'",false);
//				$GLOBALS['tmpl']->assign("notice",$notices);
//				$content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);
//				send_user_msg("您成功邀请好友".$notices['friend_name']."，获得一张20元代金券",$content,0,$to_user_id,$time,0,true,22);
        		//////////////////
        		$order_data['begin_time'] = TIME_UTC;
				$order_data['end_time'] = to_timespan(to_date($order_data['begin_time'])." ".app_conf("INTERESTRATE_TIME")." month -1 day");
				$order_data['money'] = 20;
				$order_data['ecv_type_id'] = 5;
				$sn = unpack('H12',str_shuffle(md5(uniqid())));
				$order_data['sn'] = $sn[1];
				$order_data['password'] = rand(10000000,99999999);
				$order_data['user_id']=intval($friend_id['id']);
				$order_data['child_id']=intval($GLOBALS['user_info']['id']);
				$order_data['content']="邀请好友奖励代金券！";
				$order_data['cunguan_tag']=1;

				$check=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."ecv where user_id=".$friend_pid." and child_id=".$GLOBALS['user_info']['id']);
				// if(empty($check)){
				// 	$result=$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$order_data,"INSERT");
	   //      		if($result==false){
	   //      			showErr("代金券发放失败",1);
	   //      		}
				// }

        	}
		}
		ajax_return($return);

	}
    /**
     * 抢红包好友入库
     * @param $user_id
     * @param $friend_id
     * @author:zhuxiang
     */
    function insert_packet_friend($user_id,$friend_id){
        //注册成功创建红包好友关系
        $red_data['user_id'] = $user_id;
        $red_data['friend_id'] = $friend_id;
        $red_data['status'] = 0;
        $red_data['addtime'] = TIME_UTC;
        $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_friends",$red_data,"INSERT");
    }
}
?>