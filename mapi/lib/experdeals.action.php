<?php

require APP_ROOT_PATH.'app/Lib/deal.php';
class experdeals
{
	public function index(){
		$root = get_baseroot();
		$id = intval(base64_decode($GLOBALS['request']['id']));	
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);

		//$info = get_deal($id);
		$root['response_code'] = 1;

		//借款详情		
		$deal = $GLOBALS['db']->getRow("select id,`name`,ips_bill_no,user_id,borrow_amount,(borrow_amount-load_money) as need_money,(load_money/borrow_amount*100) as progress_point,rate,min_loan_money,description,mortgage_desc,mortgage_infos,start_time,enddate,repay_time_type,repay_time,start_date,load_money,risk_security,deal_status,is_new,mortgage_brand,mortgage_color,mortgage_year,mortgage_info,publish_wait,mortgage_insurance,invest_notice from ".DB_PREFIX."experience_deal where id = ".intval($id)."  and is_delete <> 1  and is_effect = 1 and cunguan_tag =1 and publish_wait=1");
		
		$root['deal'] = $deal;
		
		/******************添加代码三步走判断开始**************************/
		$usinfos = $GLOBALS['db']->getRow("select u.cunguan_pwd,AES_DECRYPT(u.real_name_encrypt,'".AES_DECRYPT_KEY."') as realname,AES_DECRYPT(u.idno_encrypt,'".AES_DECRYPT_KEY."') as idno,u.paypassword,b.bankcard from ".DB_PREFIX."user u left join ".DB_PREFIX."user_bank b on u.id = b.user_id where u.id= ".$user_id);
        $root['idno'] = $usinfos['idno']?$usinfos['idno']:"";
        $root['real_name'] = $usinfos['realname']?$usinfos['realname']:"";
//        if($info['cunguan_tag']==0){
//            if(empty($usinfos['realname']) || empty($usinfos['idno'])||empty($usinfos['bankcard'])){
//                $root['three_go_code'] = 1;
//                $root['three_go_msg']='您有信息尚未填写完整，是否前去填写？';
//                $root['three_go_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_account&act=index';
//            }else{
//                if(empty($usinfos['paypassword'])){
//                    $root['three_go_code'] = 2;
//                    $root['three_go_msg']='您有信息尚未填写完整，是否前去填写？';
//                    $root['three_go_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_account&act=paypassword';
//                }else{
//
//                    $root['three_go_code'] = 3;
//                    $root['three_go_msg']='';
//                    $root['three_go_url'] ='';
//                }
//            }
//        }

        if($user['cunguan_tag']==1){
        	if($user['user_type']=='1'){
        		$root['three_go_code'] = 4;
        		$root['three_go_msg']='';
        		$root['three_go_url'] ='';
        	}else{
        		$user_bank=$GLOBALS['db']->getOne("select bankcard from ".DB_PREFIX."user_bank where cunguan_tag=1 and user_id=".$user_id);
        		// 存管流程  判断三步走  1：只开户实名  2：交易密码  3：绑定银行卡 4：成功
        		if($user['cunguan_tag']==0 || empty($usinfos['realname']) || empty($usinfos['idno'])){
        			$root['three_go_code'] = 1;
        			$root['three_go_msg']='您尚未开通资金存管，是否前去开通？';
        			$root['three_go_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_account&act=index';
        		}elseif(empty($usinfos['cunguan_pwd'])){
        			$root['three_go_code'] = 2;
        			$root['three_go_msg']='您尚未设置存管交易密码，是否前去设置？';
        			$root['three_go_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_paypassword&act=setpaypassword';
        		}elseif(!$user_bank){
        			$root['three_go_code'] = 3;
        			$root['three_go_msg']='您尚未绑定银行卡，是否前去绑定？';
        			$root['three_go_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_addbank&act=wap_check_pwd';
        		}else{
        			$root['three_go_code'] = 4;
        			$root['three_go_msg']='';
        			$root['three_go_url'] ='';
        			
        		}
        	}
            
        }

		/********************添加代码三步走判断结束***************************/


		//格式化抵押图片资料
		$img = unserialize($deal['mortgage_infos']);
		foreach ($img as $k => $v) {
			if ($v['img'] != ''){
				$v['img'] = get_abs_img_root(get_spec_image($v['img'],0,0,1));
				$imgs[] = $v;	
			}
		}

		if($imgs == null){
			$img_list = array();
		}else{
			$img_list = $imgs;
		}

        if($deal['deal_status']==1) {
            $root['deal']['residual_time'] = ceil($deal['remain_time']/86400);
        }else{
            $root['deal']['residual_time'] = 0;
        }

		/*$residual_time = (($deal['start_time'] +  $deal['enddate']*24*3600) - TIME_UTC)/(24*60*60);
		$root['deal']['residual_time'] = strval(ceil($residual_time)); */   	//剩余时间

        $need_money = $GLOBALS['db']->getOne("select SUM(total_money) from ".DB_PREFIX."experience_deal_load where deal_id=".$id);
        $need_money_re = $deal['borrow_amount'] - $need_money;
        if($need_money_re<0){
            $need_money_re = '0';
        }
        $deal['progress_point'] = $need_money/$deal['borrow_amount']*100;
        if($deal['progress_point']>=100){
            $root['deal']['progress_point'] = '100';
        }else{
            $root['deal']['progress_point'] =substr_replace($deal['progress_point'], '', strpos($deal['progress_point'], '.') + 2);			//借款进度
        }

        $root['deal']['cunguan_tag'] = $deal['publish_wait'];    // 标的存管状态  1存管标 0普通标 
        $root['deal']['loantype_format'] ='到期一次性付息'; 		//还款类型
		$root['deal']['need_money'] = strval(intval($need_money_re));   		//剩余金额
		$root['deal']['description'] = strip_tags($deal['description'])?strip_tags($deal['description']):'暂无数据';   		//项目介绍
		$root['deal']['invest_notice'] = strip_tags($deal['invest_notice'])?strip_tags($deal['invest_notice']):'暂无数据';
		$root['deal']['mortgage_desc'] = strip_tags($deal['mortgage_desc']);   		//借款人信息
		$root['deal']['risk_security'] = strip_tags($deal['risk_security']);   		//抵押物资料
		$root['deal']['rate'] = sprintf("%.1f",$deal['rate']);	//利率+加息利率
		if($deal['interest_rate']>0){
			$root['deal']['interest_rate'] =$deal['interest_rate'];		//利率+加息利率
		}
		
		$root['deal']['ratio'] = strval(($deal['rate']/100/365)*$deal['repay_time']);//预期收益系数

		$root['deal']['begin_time'] = "出借当日起息";						//起息时间
		//$root['deal']['begin_info'] = WAP_SITE_DOMAIN.'/index.php?ctl=interest';						//起息时间

        $root['deal']['name'] =$deal['name'];

        /*************项目资料详情开关开始**************/
        $switch = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."switch_conf where switch_id = 1 or switch_id = 8");
        foreach ($switch as $k=>$v){
            if($v['status']!=1){
                //抵押物资料
                //抵押物资料
                $deal['mortgage_brand']= '暂无';
                $deal['mortgage_year'] = '暂无';
                $deal['mortgage_color'] = $deal['mortgage_color'] ?$deal['mortgage_color']:'暂无';
                $deal['mortgage_insurance'] = $deal['mortgage_insurance'] ?$deal['mortgage_insurance']:'暂无';
                $deal['mortgage_info'] = $deal['mortgage_info'] ?$deal['mortgage_info'] : '暂无';
                $root['deal']['mortgage_desc'] = "车辆品牌：暂无\n车辆年限：暂无\n车辆颜色：暂无\n保险情况：暂无\n车辆状况：暂无";
                //图片信息
                $root['deal']['mortgage_infos'] = '暂无数据';
            }else{
                //抵押物资料
                $deal['mortgage_brand']= $deal['mortgage_brand']?$deal['mortgage_brand']:'暂无';
                $deal['mortgage_year'] = $deal['mortgage_year'] ?$deal['mortgage_year']:'暂无';
                $deal['mortgage_color'] = $deal['mortgage_color'] ?$deal['mortgage_color']:'暂无';
                $deal['mortgage_insurance'] = $deal['mortgage_insurance'] ?$deal['mortgage_insurance']:'暂无';
                $deal['mortgage_info'] = $deal['mortgage_info'] ?$deal['mortgage_info'] : '暂无';
                $root['deal']['mortgage_desc'] = "车辆品牌：".$deal['mortgage_brand']."\n车辆年限：".$deal['mortgage_year']."\n车辆颜色：".$deal['mortgage_color']."\n保险情况：".$deal['mortgage_insurance']."\n车辆状况：".$deal['mortgage_info'];
                $root['deal']['mortgage_infos'] = $img_list;						//抵押图片
            }
        }

        /*************项目资料详情开关结束**************/
		//借款出借列表
        
		$list_user = $GLOBALS['db']->getAll("SELECT deal_id,user_id,user_name,money,create_time FROM ".DB_PREFIX."experience_deal_load WHERE deal_id = ".$id." order by id desc ");
		foreach ($list_user as $k => $val) {
		 	$val['create_time'] = date("m-d H:i",$val['create_time']);
		 	$val['money'] =strval(format_price_money($val['money']));
			//根据出借记录取出用户的手机号码
			//$user_mobile = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile FROM ".DB_PREFIX."user WHERE user_name='".$val['user_name']."'");
			//zhuxaing 2017513
            $user_mobile = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile FROM ".DB_PREFIX."user WHERE id='".$val['user_id']."'");
			$val['user_name'] = hideMobile($user_mobile);
		 	$users[] = $val;
		}
		
		
		
		if($users == null){
		 	$user_list = array();
		}else{
		 	$user_list = $users;
		}
		$root['deal']['user_list'] = $user_list;
		$ips =  $GLOBALS['db']->getRow("SELECT * FROM  ".DB_PREFIX."app_ips where code = 2" );
		$ips['img'] = get_abs_img_root(get_spec_image($ips['img'],0,0,1));
        if($ips['type']==2){
            $ips['url'] = $GLOBALS['db']->getOne("select url from ".DB_PREFIX."app_internal where is_effect=1 and id=".$ips['app_page']);
        }
        $ips['warningText'] = "市场有风险，出借需谨慎";
		$root['ips'] = $ips; //第三方托管机构
		$root['ymb'] = $GLOBALS['m_config']['ymb']; 	//余某宝利率
		$root['bank'] = $GLOBALS['m_config']['bank']; 	//银行年化利率
		$root['recharge_url'] = WAP_SITE_DOMAIN ."/member.php?ctl=uc_money&act=incharge";
		/*
		*	status=0; 正常状态
		*	status=1; 尾标状态
		*	wb_code=0; 新手标状态
		*	wb_code=2; 第三方托管标
		*	wb_code=3; 普通标
		 *
		*/
		if ($user_id >0){
			$root['is_new'] = strval($deal['is_new']);  //此字段用于判断是不是新手标 1.是新手标,0.不是新手标
			$root['user_login_status'] = 1;
            //  账户余额   区分存管余额和非存管余额
            if($deal['cunguan_tag']==1){
                $root['balance'] = strval(sprintf("%.2f",$user['cunguan_money'])); //存管账户余额
            }else{
                $root['balance'] = strval(sprintf("%.2f",($user['money']))); //非存管账户余额
            }
			if($deal['need_money'] < $deal['min_loan_money'] /*&& $deal['need_money'] < $user['money']*/ && $deal['need_money'] != 0){
				$root['status'] = 1; //尾标状态
			}else{
				$root['status'] = 0; //正常状态
			}
			if($deal['is_new']==1 &&  $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."experience_deal  WHERE user_id=".$user_id) > 0){
				$root['wb_code'] = 0; //新手标
				$root['msg'] = "只有新手才可以出借哦！";
				output($root);
			}elseif($deal['ips_bill_no']!="" && $GLOBALS['user_info']['ips_acct_no']==""){
				$root['wb_code'] = 2;
				$root['msg'] = "此标为第三方托管标，请先绑定第三方托管账户";
				output($root);
			}elseif($deal['user_id']==$GLOBALS['user_info']['id']){
                $root['wb_code'] = 4;
                $root['msg'] = "不能投自己发布的标的！";
            }else{

				$root['wb_code'] = 3;
				$root['msg'] = "正常状态";
			}	
		}else{
			$root['user_login_status'] = 0;
		}
			
		$root['program_title'] = "投标详情";
		output($root);	
	}
}
?>