<?php
/**
* wap手机版本发现栏目
*/
class tasteModule extends SiteBaseModule
{
	public function index(){
		if(!$GLOBALS['user_info']['id']){
			app_redirect(url("index","user#login"));
		}
		/*体验金活动列表*/
		
		/*
		$taste=experience_money($GLOBALS['user_info']['id'],"wap");
		$taste_info['money_total'] = $taste['money_total'];
		$taste_info['can_use_money'] = $taste['can_use_money'];
		$taste_info['incomed'] = $taste['incomed'];
		$taste_info['incomeing'] = $taste['incomeing'];
		$taste_info['item'] = $taste['item'];
		$taste_info['user_id'] = $GLOBALS['user_info']['id'];
		*/
		
		
		$user_id = $GLOBALS['user_info']['id'];
		$result = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."taste_cash where cunguan_tag=1 and user_id=".$user_id);
			foreach ($result as $k=>$v){
                $taste_info['item'][$k]['title'] = $v['disc']; //体验金标题
                $taste_info['item'][$k]['money'] = strval($v['money']); //体验标金额
                $taste_info['item'][$k]['id'] = $v['id']; //体验标id
                $has_repay = $GLOBALS['db']->getOne("select has_repay from ".DB_PREFIX."experience_deal_load where learn_id=".$v['id']." and user_id=".$user_id );
                if($v['use_status']==1){
                    $taste_info['item'][$k]['button'] = '已使用';
                    $taste_info['item'][$k]['button_status'] = '3';
                }else if($v['use_status']==1 && $has_repay==0){
                    $taste_info['item'][$k]['button'] = '计息中';
                    $taste_info['item'][$k]['button_status'] = '2';
                }else if(time()>$v['end_time']){
                	 $taste_info['item'][$k]['button'] = '已过期';
                     $taste_info['item'][$k]['button_status'] = '4';                   
                }else{
                     $taste_info['item'][$k]['button'] = '立即使用';
                     $taste_info['item'][$k]['button_status'] = '1';  

                }
        	}
        	
            $taste_info['response_code'] = 1;
            //体验金总金额
            $taste_info['money_total'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."taste_cash where cunguan_tag = 1 and user_id=".$user_id);
            $taste_info['money_total'] = $taste_info['money_total']?$taste_info['money_total']:"0.00";
         
            //体验金可用余额
            $taste_info['can_use_money'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."taste_cash where cunguan_tag =1 and user_id=".$user_id." and use_status=0 and end_time>=".time());
            $taste_info['can_use_money']= $taste_info['can_use_money']? $taste_info['can_use_money']:"0.00";

            //体验经已收收益
            //$taste_info['incomed'] = $GLOBALS['db']->getOne("select SUM(experience_money) from ".DB_PREFIX."experience_deal_load where user_id=".$user_id." and has_repay=1 ");
			$taste_info['incomed'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."red_packet where user_id=".$user_id." and status=1 and packet_type=3 and publish_wait=1 ");
            //$root['incomed']=  $root['incomed']>0?  $root['incomed']:"0.00";
            $taste_info['incomed']= $taste_info['incomed']? $taste_info['incomed']:"0.00";
            
            //体验金待收收益
           	$taste_info['incomeing'] = $GLOBALS['db']->getOne("select SUM(experience_money) from ".DB_PREFIX."experience_deal_load where user_id=".$user_id." and has_repay=0 ");
            //$root['incomed']=  $root['incomed']>0?  $root['incomed']:"0.00";
            $taste_info['incomeing']= $taste_info['incomeing']? $taste_info['incomeing']:"0.00";
         	
            $taste_info['licai_open'] = $taste_info['licai_open'];
            $taste_info['user_name'] = $taste_info['user_name'];
            $GLOBALS['tmpl']->assign("taste_info",$taste_info);
            $taste_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'cg_explain'"));
			$GLOBALS['tmpl']->assign("taste_explain",$taste_explain);
			$GLOBALS['tmpl']->display("page/taste_money.html");
	}


	public function taste_money_share(){
		if(!$GLOBALS['user_info']['id']){
			app_redirect(url("index","user#login"));
		}
		$MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
		$taste=experience_money($GLOBALS['user_info']['id'],"wap");
		$friend = friends_numbers($GLOBALS['user_info']['id']); //分享领取体验金通过实名认证的好友
		$taste_info['item'] = $taste['item'];
		$GLOBALS['tmpl']->assign("taste_info",$taste_info);
		$GLOBALS['tmpl']->assign('mobile',$GLOBALS['user_info']['mobile']);
		$GLOBALS['tmpl']->assign("device",$MachineInfo[0]);
        $GLOBALS['tmpl']->assign('cate_title',"分享体验金");
		$GLOBALS['tmpl']->assign("friend_info",$friend);
		$GLOBALS['tmpl']->assign("url",WAP_SITE_DOMAIN);
		$GLOBALS['tmpl']->display("page/taste_money_share.html");
	}
	
	public function taste_money_use(){
		$taste_list=experience_money_detail($GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("list",$taste_list['item']);
		$GLOBALS['tmpl']->display("page/taste_money_use.html");
	}

	public function taste_use(){
		$switch_conf = $GLOBALS['db']->getAll("SELECT status FROM ".DB_PREFIX."switch_conf where switch_id=1 or switch_id=6");
		foreach ($switch_conf as $k => $v) {
			if($v['status'] != 1){
				$return['status'] = 0;
				$return['show_err'] = "系统正在升级，请稍后再试";
				ajax_return($return);
			}
		}
        if(!check_ipop_limit(CLIENT_IP,"experience_money_use",5,0))
        {
            $return['status'] = 0;
            $return['show_err'] = "提交太快了";
            ajax_return($return);
        }
		foreach($_REQUEST as $k=>$v){
			$data[$k] = htmlspecialchars(addslashes(trim($v)));
		}
		$money_use=experience_money_use($data['user_id'],$data['id'],$data['device']);
		if($money_use['response_code'] == 1){
			if($money_use['success_status'] == 2){
				$result['status'] = 2;
				$result['show_err'] = $money_use['show_err'];
				ajax_return($result);
			}else{
				$result['status'] = 1;
				$result['show_err'] = $money_use['show_err'];
				ajax_return($result);
			}			
		}else{
			if($data['status'] == 5){
				$result['status'] = 3;
				$result['show_err'] = $money_use['show_err'];
				ajax_return($result);
			}else{
				$result['status'] = 0;
				$result['show_err'] = $money_use['show_err'];
				ajax_return($result);
			}			
		}

	}
}
?>