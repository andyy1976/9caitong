<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
require_once APP_ROOT_PATH.'app/Lib/uc_func.php';
class plan_deal_ok
{
	public function index(){

        $switch = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."switch_conf where switch_id = 1 or switch_id = 7");
        foreach ($switch as $k=>$v){
            if($v['status']!=1){
                $root['response_code'] = 0;
                $root['show_err'] = '系统正在升级，请稍后再试';
                output($root);
            }
        }
		
		$root = array();
		/*
		id:贷款单ID
		bid_money:总金额 //出借金额+红包金额+代金券金额
		red_money：红包金额
		red_id：红包id 多个代金券用6,7格式传入
		interest_card_id: 加息卡  id
		*/
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		if ($user_id >0){
			if($user['user_type']=='1'){
				$root['response_code'] = 0;
				$root['show_err'] = '企业用户暂不可使用';
				output($root);
			}
			$root['user_login_status'] = 1;			
			$id = intval(base64_decode($GLOBALS['request']['id']));
			$interest_card_id = intval(base64_decode($GLOBALS['request']['interest_card_id']));
//			$red = intval(base64_decode($GLOBALS['request']['red']));   // 老版红包   已弃用
			$red_id = strval(base64_decode($GLOBALS['request']['red_id']));
            $red_money = intval($GLOBALS['db']->getOne("select sum(money)  from ".DB_PREFIX."red_packet where user_id=".$user_id." and id in(".$red_id.")" ));//红包金额
            $bid_money = intval(base64_decode($GLOBALS['request']['bid_money']));    //本金
            $cunguan_tag= 1;

//            if($cunguan_tag==0){
//                $paypwd = strim(base64_decode($GLOBALS['request']['pay_pwd']));
//                $paypassword = $GLOBALS['db']->getOne("SELECT paypassword FROM ".DB_PREFIX."user WHERE id=".$user_id);
//                if($paypwd != $paypassword){
//                    $root['response_code'] = 0;
//                    $root['tip'] = "交易密码不正确";
//                    output($root);
//                }
//            }

			//$status = wapdobid($id,$bid_money,$red,1,$ecv_id,0,$interestrate_id,$use_interestrate);
			$map['plan_id'] = $id;
            $map['bid_money'] = $bid_money;
//            $map['bid_paypassword'] = $paypwd;
            $map['red_id'] = $red_id;
			$map['is_pc'] = 1;
            $map['learn_id'] = 0;  //是否使用体验金
			$map['red_money'] =$red_money;
			$map['interestrate_id'] =$interest_card_id;
            if($map['interestrate_id']){
                $map['interestrate_money'] = get_interestrate_money($map['interestrate_id'],$map['bid_money'],1,$map['plan_id']);    //加息收益
            }
            if(isset($map['plan_id']) && !empty($map['plan_id'])){
            	//生成理财计划数据
				$BidListSerial = $GLOBALS['db']->getRow("select deal_id, load_money, borrow_amount,rate from " . DB_PREFIX . "plan  where id= ".$map["plan_id"]); //理财计划所包含标的
            	$map['BidListArray'] = unserialize($BidListSerial['deal_id']);
            	// 出借数据入临时表
            	$publics = new Publics();
				$load_seqno=$publics->seqno();
				$deal_load_data['load_seqno']=$load_seqno;
				// $deal_load_data['deal_id']=json_encode($map['BidListArray']);
				$deal_load_data['user_id']=$GLOBALS['user_info']['id'];
				$deal_load_data['money']=$map['bid_money'];
				$deal_load_data['total_money']=$map['bid_money']+$map['red_money'];
				$deal_load_data['red'] = $map['red_money'];
				$deal_load_data['red_id'] = $map['red_id'];
				$deal_load_data['interestrate_id'] = $map["interestrate_id"];
				$deal_load_data['cunguan_tag'] = 1;
				$deal_load_data['create_date'] = date('Y-m-d',time());
				$deal_load_data['create_time'] = time();
				$deal_load_data['plan_id'] = $map['plan_id'];
				//print_r($deal_load_data);die;
				$res=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_temp",$deal_load_data,"INSERT");
				if($res){
					$status=check_pwd_url($load_seqno);
				}else{
					$root['response_code'] = 0;
	                $root['show_err'] = '出借失败！';
	                output($root);
				}
            }
			if($status['status'] == 1){

			// 给用户发送短信通知
            if(app_conf("SMS_ON")==1)
            {
                $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$user_id);
                $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_INVESTMENT_SUCCESS'");
                // $notice['user_name'] = $user_info['user_name'];
                // $notice['release_date'] = to_date(TIME_UTC,"Y-m-d");
                // $notice['site_name'] = app_conf("SHOP_TITLE");
                // // $notice['recharge_money'] = round($storage['money'],2);
                // $GLOBALS['tmpl']->assign("notice",$notice);
                $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

                $msg_data['dest'] = $user_info['mobile'];
                $msg_data['send_type'] = 0;
                $msg_data['title'] = "出借成功短信通知";
                $msg_data['content'] = addslashes($msg);
                $msg_data['send_time'] = time();
                $msg_data['is_send'] = 0;
                $msg_data['create_time'] = TIME_UTC;
                $msg_data['user_id'] = $user_id;
                $msg_data['is_html'] = 0;
                send_sms_email($msg_data);
                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
            }

//				$root['cg_pass_url'] = WAP_SITE_DOMAIN."/index.php?ctl=deal&act=cg_pass&load_seqno=".$map['load_seqno'];
				$root['cg_pass_url'] = $status['url'];
				$root['response_code'] = 1;
				$root['status'] = 1;
				$root['show_err'] = "出借成功";
			}elseif($status['status'] == 2){
                $root['cg_pass_url'] = $status['url'];
                $root['response_code'] = 1;
                $root['status'] = 1;
                $root['show_err'] = "请输入第三方交易密码！";
            }else{
				$root['status'] = 0;
				$root['response_code'] = 0;
				if($status['show_err']){
                    $root['show_err']=$status['show_err'];
				}else{
                    $root['show_err'] = "出借失败";
				}
			}

		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$activity = $GLOBALS['db']->getRow("select id,app_page,img,name,type,url from ".DB_PREFIX."app_popup where is_effect =1 and position =2 and UNIX_TIMESTAMP(add_time) < ".TIME_UTC." and UNIX_TIMESTAMP(end_time) > ".TIME_UTC." order by sort desc");
		//活动不存在时
		if($activity){
			$act_list = $activity;
			$act_list['is_code'] = 1;
			$act_list['img'] =  get_abs_img_root(get_spec_image( $activity['img'],0,0,1));
            if($activity['type']==2){
                $act_list['url']=$GLOBALS['db']->getOne("select url from ".DB_PREFIX."app_internal where is_effect=1 and id=".$activity['app_page']);
            }
		}else{
			$act_list['is_code'] = 0;
		}
        $root['cunguan_tag']=$cunguan_tag;
		$root['activity'] = $act_list; //活动推广图片
		output($root);		
	}
}
?>
