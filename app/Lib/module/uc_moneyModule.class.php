<?php
require APP_ROOT_PATH.'app/Lib/uc.php';
require APP_ROOT_PATH."system/utils/Depository/Require.php";
class uc_moneyModule extends SiteBaseModule
{
	private $creditsettings;
	private $allow_exchange = false;

	public function __construct()
	{
		if(in_array(ACTION_NAME,array("carry","savecarry"))){
			$is_ajax = intval($_REQUEST['is_ajax']);
			//判断是否是黑名单会员
			if($GLOBALS['user_info']['is_black']==1){
				showErr("您当前无权限提现，具体联系网站客服",$is_ajax,url("index","uc_center"));
			}
		}
		if(file_exists(APP_ROOT_PATH."public/uc_config.php"))
		{
			require_once APP_ROOT_PATH."public/uc_config.php";
		}
		if(app_conf("INTEGRATE_CODE")=='Ucenter'&&UC_CONNECT=='mysql')
		{
			if(file_exists(APP_ROOT_PATH."public/uc_data/creditsettings.php"))
			{
				require_once APP_ROOT_PATH."public/uc_data/creditsettings.php";
				$this->creditsettings = $_CACHE['creditsettings'];
				if(count($this->creditsettings)>0)
				{
					foreach($this->creditsettings as $k=>$v)
					{
						$this->creditsettings[$k]['srctitle'] = $this->credits_CFG[$v['creditsrc']]['title'];
					}
					$this->allow_exchange = true;
					$GLOBALS['tmpl']->assign("allow_exchange",$this->allow_exchange);
				}
			}
		}
		parent::__construct();
	}

	public function exchange()
	{		
		$user_info = get_user_info("*","id = ".intval($GLOBALS['user_info']['id']));		
		$GLOBALS['tmpl']->assign("user_info",$user_info);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_EXCHANGE']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_exchange.html");
		$GLOBALS['tmpl']->assign("exchange_data",$this->creditsettings);
		$GLOBALS['tmpl']->assign("exchange_json_data",json_encode($this->creditsettings));
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function doexchange()
	{		
		if($this->allow_exchange)
		{
			$user_pwd = md5(addslashes(trim($_REQUEST['password'])));
			$user_info = get_user_info("*","id = ".intval($GLOBALS['user_info']['id']));		
			
			if($user_info['user_pwd']=="")
			{
				//判断是否为初次整合
				//载入会员整合
				$integrate_code = trim(app_conf("INTEGRATE_CODE"));
				if($integrate_code!='')
				{
					$integrate_file = APP_ROOT_PATH."system/integrate/".$integrate_code."_integrate.php";
					if(file_exists($integrate_file))
					{
						require_once $integrate_file;
						$integrate_class = $integrate_code."_integrate";
						$integrate_obj = new $integrate_class;
					}	
				}
				if($integrate_obj)
				{			
					$result = $integrate_obj->login($user_info['user_name'],$user_pwd);						
					if($result['status'])
					{
						$GLOBALS['db']->query("update ".DB_PREFIX."user set user_pwd = '".$user_pwd."' where id = ".$user_info['id']);
						$user_info['user_pwd'] = $user_pwd;
					}								
				}
			}
			if($user_info['user_pwd']==$user_pwd)
			{
				$cfg = $this->creditsettings[addslashes(trim($_REQUEST['key']))];
				if($cfg)
				{	
					$amount = floor($_REQUEST['amountdesc']);
					$use_amount = floor($amount*$cfg['ratio']); //消耗的本系统积分
					$field = $this->credits_CFG[$cfg['creditsrc']]['field'];
					
					if($user_info[$field]<$use_amount)
					{
						$data = array("status"=>false,"message"=>$cfg['srctitle']."不足，不能兑换");
						ajax_return($data);
					}				    
					
					include_once(APP_ROOT_PATH . 'uc_client/client.php');	
					$res = call_user_func_array("uc_credit_exchange_request", array(
				    	$user_info['integrate_id'],  //uid(整合的UID)
				    	$cfg['creditsrc'],  //原积分ID
				    	$cfg['creditdesc'],  //目标积分ID
				    	$cfg['appiddesc'],  //toappid目标应用ID
				    	$amount,  //amount额度(计算过的目标应用的额度)
				    	));
					if($res)
					{
				    	//兑换成功
						$use_amount = 0 - $use_amount;				    	
						$credit_data = array($field=>$use_amount);
						require_once APP_ROOT_PATH."system/libs/user.php";
						modify_account($credit_data,$user_info['id'],"ucenter兑换支出",22);
						$data = array("status"=>true,"message"=>"兑换成功");
						ajax_return($data);
					}
					else
					{
						$data = array("status"=>false,"message"=>"兑换失败");
						ajax_return($data);
					}
				}
				else
				{
					$data = array("status"=>false,"message"=>"非法的兑换请求");
					ajax_return($data);
				}
			}
			else
			{
				$data = array("status"=>false,"message"=>"登录密码不正确");
				ajax_return($data);
			}
		}
		else
		{
			$data = array("status"=>false,"message"=>"未开启兑换功能");
			ajax_return($data);
		}
	}


	public function index(){




        $user_id=$GLOBALS['user_info']['id'];
        $user_info = $GLOBALS['user_info'];
        
        $fund_paging = intval($_REQUEST['fund_paging']);
        $GLOBALS['tmpl']->assign("fund_paging",$fund_paging);
        $page_args['fund_paging'] = $fund_paging;
        $page_pram = "";
        foreach($page_args as $k=>$v){
            $page_pram .="&".$k."=".$v;
        }    
       	//存管资金
       	$Pcrecharge_moneys= $GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as money FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
        $Pcrecharge_money=strval(sprintf("%.2f",$Pcrecharge_moneys));
        //普通资金
        $Pcordinary=strval(sprintf("%.2f",$user_info['money']));
        //总可用余额
        $total_Pcord= $Pcrecharge_money+$Pcordinary;

        //存管的冻结
        $cun_statics=$GLOBALS['user_info']['cunguan_lock_money'];
	  	//普通的冻结
	  	$p_statics=$GLOBALS['user_info']['lock_money'];
	  	//总冻结
	  	$Total_freeze = $cun_statics+$p_statics;

		$type_title = isset($_REQUEST['type_title']) ? intval($_REQUEST['type_title']) : 100;
		$times = intval($_REQUEST['times']);
		$time_status = intval($_REQUEST['time_status']);
		//$t = strim($_REQUEST['t']); //point 积分  为空为资金
		//$GLOBALS['tmpl']->assign("t",$t);
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");

		$voucher_deal = array(
            array(
                "name" => "存管账户",
            ),
            array(
                "name" => "普通账户",
            ),
        );
		foreach($voucher_deal as $k=>$v){
            $tmp_args = $page_args;
            $tmp_args['fund_paging']=$k;
            $voucher_deal[$k]['url'] = url("index","uc_money#index",$tmp_args);  //是从20出来的   $url =$url.$k."=".urlencode($v)."&";
        }


		if ($type_title==100)
		{
			$type= -1;
		}
		else{
			$type = $type_title;
		}
        $condition='';
		if($time_status==1){
			$time = isset($_REQUEST['time']) ? strim($_REQUEST['time']) : "";
			$time_f = to_date(to_timespan($time,"Ymd"),"Y-m-d");
			$condition.=" and create_time_ymd = '".$time_f."'";
			$GLOBALS['tmpl']->assign('time_normal',$time_f);
			$GLOBALS['tmpl']->assign('time',$time);
		}

		if($fund_paging ==1)
        	$condition .= " and cunguan_tag =0  and type in(1,2,5,8,47,27,29,58)";
       	else
       		$condition .= "and cunguan_tag =1 and type in(1,2,3,4,5,8,47,27,29,58,59,60,61,62,48,70)";

   
        $result = get_user_money_log($limit,$user_id,-1,$condition); //会员资金日志
		foreach($result['list'] as $k=>$v){
			$result['list'][$k]['title'] = mb_substr($v['memo'],strrpos($v['memo'],',')+1);
            $result['list'][$k]['brief'] = stripos($v['brief'],":")?"还本还息":$v['brief'];
        }
        // var_dump($result);exit;

		$GLOBALS['tmpl']->assign("Total_freeze",$Total_freeze);
        $GLOBALS['tmpl']->assign("cun_statics",$cun_statics);
	  	$GLOBALS['tmpl']->assign("p_statics",$p_statics);
        $GLOBALS['tmpl']->assign("Pctotal",$Pctotal);	
	    $GLOBALS['tmpl']->assign("Pcrecharge_money",$Pcrecharge_money);
	    $GLOBALS['tmpl']->assign("Pcordinary",$Pcordinary);
        $GLOBALS['tmpl']->assign('voucher_deal',$voucher_deal);
		$GLOBALS['tmpl']->assign("type_title",$type_title);
		$GLOBALS['tmpl']->assign("total_Pcord",$total_Pcord);
		$GLOBALS['tmpl']->assign("times",$times);
		/*********wap2.0资金详览**********/
		$money_type = intval($_REQUEST['money_type']);
		$GLOBALS['tmpl']->assign("money_type",$money_type);
		$page_args['money_type'] =  $money_type;
		$invest = array(
			array(
				"name" => "总账户",
			),
			array(
				"name" => "存管账户",
			),
			array(
				"name" => "普通账户",
			),
		);

		foreach($invest as $k=>$v){
			$tmp_args = $page_args;
			$tmp_args['money_type'] = $k;
			$invest[$k]['url'] = url("index","uc_money",$tmp_args);
		}
		$GLOBALS['tmpl']->assign('invest',$invest);
		if($money_type == 1){
			//存管账户
			$user_statics = get_user_money_info($user_id);
			//存管账户总资产
			$user_statics['total_money'] = sprintf('%.2f',intval($user_statics['cunguan_total_money']));//存管资产
			//存管账户可用余额
			$user_statics['balance'] = intval($GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as money FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']));
			//存管账户在投金额
			$user_statics['invest_money'] = intval(sprintf('%.2f',$user_statics["cunguan_invest_money"]));
			//存管账户提现冻结金额
			$lock_money=$GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_carry where user_id=".$GLOBALS['user_info']['id']." and (status =3 or status = 0) and cunguan_tag=1 and cunguan_pwd = 1");
			if($lock_money){
				$user_statics['lock_money']  = $lock_money;
			}else{
				$user_statics['lock_money']  = 0;
			}
            //存管现金红包
            $user_statics['cash_red_sum']=$GLOBALS['db']->getOne("select sum(rp.money) from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id = ".$GLOBALS['user_info']['id']." and rp.status=0 and rpn.red_type=3 and rp.end_time>".time());
            $user_statics['cash_red_sum'] = $user_statics['cash_red_sum'] ? $user_statics['cash_red_sum'] : '0';
            //存管出借红包
            $user_statics['deal_red_sum']=$GLOBALS['db']->getOne("select sum(rp.money) from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id = ".$GLOBALS['user_info']['id']." and rp.status=0 and rpn.red_type=1 and rp.end_time>".time());
            $user_statics['deal_red_sum'] = $user_statics['deal_red_sum'] ?$user_statics['deal_red_sum']:'0';


			//存管账户红包
			$user_statics['red_money'] = $user_statics['cunguan_red_money']; 
			//存管账户代金券金额					
			$user_statics['voucher_count'] = intval($user_statics["cunguan_ecv_money"]);
			//存管账户累计出借收益	
			$user_statics['invest_total_money'] = sprintf('%.2f',$user_statics["cunguan_invest_total_money"]);
			//存管账户已收收益总计
			$user_statics['load_repay_money'] = sprintf('%.2f',$user_statics["cunguan_load_repay_money"]);
			//存管账户待收收益总计
			$user_statics['load_wait_earnings'] = sprintf('%.2f',$user_statics["cunguan_load_wait_earnings"]);
			//存管账户体验金收益
			$user_statics['taste'] = sprintf('%.2f',$user_statics["cunguan_taste"]);
			//自动投标冻结金额
			$user_statics['lock_autoinvest_money'] = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."auto_invest_config where user_id=".$user_id." and status=1 and is_delete=0");
			$user_statics['lock_autoinvest_money'] = $user_statics['lock_autoinvest_money'] ? sprintf('%.2f',$user_statics['lock_autoinvest_money']) : 0;
		
		}else if($money_type == 2){
			//普通账户
			$user_statics = get_user_money_info($user_id);
			$user_statics['balance'] = intval($GLOBALS['db']->getOne("SELECT AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."') as money FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']));
			//普通账户总资产
			$user_statics['total_money'] = strval(sprintf("%.2f",$user_info['money']+$user_info['lock_money']));
			//普通账户可用余额
			$user_statics['balance'] = intval($user_info['money']);
			//普通账户在投金额
			$user_statics['invest_money'] = intval(sprintf('%.2f',$user_statics["invest_money"]));
			//普通账户提现冻结金额
			if($user_statics['cash_money']){
				$user_statics['lock_money']  = intval($user_statics['cash_money']);
			}else{
				$user_statics['lock_money']  = 0;
			}

            //普通现金红包
            $user_statics['cash_red_sum'] = '0';
            //普通出借红包
            $user_statics['deal_red_sum'] = '0';

			//普通账户红包
			$user_statics['red_money'] = $user_statics['red_money']; 
			//普通账户代金券金额					
			$user_statics['voucher_count'] = intval($user_statics["ecv_money"]);
			//普通账户累计出借收益	
			$user_statics['invest_total_money'] = sprintf('%.2f',$user_statics["invest_total_money"]);
			//普通账户已收收益总计
			$user_statics['load_repay_money'] = sprintf('%.2f',$user_statics["load_repay_money"]);
			//普通账户待收收益总计
			$user_statics['load_wait_earnings'] = sprintf('%.2f',$user_statics["load_wait_earnings"]);
			//普通账户体验金收益
			$user_statics['taste'] = sprintf('%.2f',$user_statics["taste"]); 
			
		}else{
			//总账户
			//账户总资产			
			$user_statics = get_user_money_info($user_id);
			$user_statics['total_money'] = sprintf('%.2f',$user_statics['cunguan_total_money']+$user_info['lock_money']+$user_info['money']);
			//可用总余额
			$user_statics['balance'] = intval($user_info['money']+$GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as money FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']));
			//在投金额
			$user_statics['invest_money'] = intval(sprintf('%.2f',$user_statics["invest_money"]+$user_statics["cunguan_invest_money"]));
			//提现冻结
			$lock_money=$GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_carry where user_id=".$GLOBALS['user_info']['id']." and (status =3 or status = 0) and cunguan_tag=1 and cunguan_pwd = 1");
			if($user_statics['cash_money']+$lock_money){
				$user_statics['lock_money']  = $user_statics['cash_money']+$lock_money;
			}else{
				$user_statics['lock_money']  = 0;
			}

            //存管现金红包
            $user_statics['cash_red_sum']=$GLOBALS['db']->getOne("select sum(rp.money) from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id = ".$GLOBALS['user_info']['id']." and rp.status=0 and rpn.red_type=3 and rp.end_time>".time());
            $user_statics['cash_red_sum'] = $user_statics['cash_red_sum'] ? $user_statics['cash_red_sum'] : '0';
            //存管出借红包
            $user_statics['deal_red_sum']=$GLOBALS['db']->getOne("select sum(rp.money) from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id = ".$GLOBALS['user_info']['id']." and rp.status=0 and rpn.red_type=1 and rp.end_time>".time());
            $user_statics['deal_red_sum'] = $user_statics['deal_red_sum'] ?$user_statics['deal_red_sum']:'0';

			//红包金额
			$user_statics['red_money'] = $user_statics['red_money']; 
			//代金券金额					
			$user_statics['voucher_count'] = $user_statics["ecv_money"]+$user_statics["cunguan_ecv_money"];
			//累计出借收益	
			$user_statics['invest_total_money'] = sprintf('%.2f',$user_statics["invest_total_money"]+$user_statics["cunguan_invest_total_money"]);
			//已收收益总计
			$user_statics['load_repay_money'] = sprintf('%.2f',$user_statics["load_repay_money"]+$user_statics["cunguan_load_repay_money"]);
			//待收收益总计
			$user_statics['load_wait_earnings'] = sprintf('%.2f',$user_statics["load_wait_earnings"]+$user_statics["cunguan_load_wait_earnings"]);
			//体验金收益
			$user_statics['taste'] = sprintf('%.2f',$user_statics["taste"]+$user_statics["cunguan_taste"]); 				
			//自动投标冻结金额
			$user_statics['lock_autoinvest_money'] = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."auto_invest_config where user_id=".$user_id." and status=1 and is_delete=0");
			$user_statics['lock_autoinvest_money'] = $user_statics['lock_autoinvest_money'] ? sprintf('%.2f',$user_statics['lock_autoinvest_money']) : 0;
		}
				
		$GLOBALS['tmpl']->assign("lock_invest_money",$lock_invest_money);
		$GLOBALS['tmpl']->assign("user_statics",$user_statics);
		/*********wap2.0资金详览**********/
		$GLOBALS['tmpl']->assign("carry_money",$GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."user_carry WHERE user_id=".$user_id." AND `status`=1"));
		$GLOBALS['tmpl']->assign("incharge_money",$GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."payment_notice WHERE user_id=".$user_id." AND `is_paid`=1"));
		$GLOBALS['tmpl']->assign('time_status',$time_status);
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("page_title","资金明细");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}

	public function money_list(){
		/*******wap2.0 资金记录********/
		$user_id = $GLOBALS['user_info']['id'];
		$invest_type = intval($_REQUEST['invest_type']);
		$GLOBALS['tmpl']->assign("invest_type",$invest_type);
		$page_args['invest_type'] =  $invest_type;
		$invest = array(
			array(
				"name" => "存管账户",
			),
			array(
				"name" => "普通账户",
			),
		);
		foreach($invest as $k=>$v){
			$tmp_args = $page_args;
			$tmp_args['invest_type'] = $k;
			$invest[$k]['url'] = url("index","uc_money#money_list",$tmp_args);
		}
		$GLOBALS['tmpl']->assign('invest',$invest);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		if($invest_type == 1){
			$condition = "and (type in(1,2,5,47,29,58) or brief= '提现成功') and cunguan_tag = 0";
		}else{
			$condition = "and (type in(1,2,3,4,5,47,29,58,59,60,61,62,48) or brief= '存管提现成功') and cunguan_tag = 1";
		}
		$result = get_user_money_log($limit,$user_id,-1,$condition);
		$details = $result['list'];
		$month_time_start = to_date(TIME_UTC,"m");
	foreach($details as $key=>$val){
		if(date('m',$val['create_time']) == $month_time_start){
			$create_time=date('本月 Y年',$val['create_time']);
		}else{
			$create_time=date('m月 Y年',$val['create_time']);//-->将查出的每个年月日，以年月分离出来，做为新数组的下标
		}
        $val['icon'] = $this->get_type_icon($val['type'],$val['money']);
        switch ($val['type']) {
        	case '1':
        		$val['memo'] ="充值成功";
        		$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
        		break;
        	case '2':
        		$val['memo'] ="出借成功";
        		break;
			 case '3':
        		$val['memo'] ="标的放款";
				$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
        		break; 
			 case '4':
        		$val['memo'] ="偿还本息";
        		break; 
    		case '5':
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
    		case '8':
    			$val['memo'] ="提现成功";
    			if($val['money'] > 0){
    				$val['money'] = "-".sprintf("%.2f", floatval($val['money']));
    			}    			
    			break;
    		case '47':
    			$val['memo'] ="领取体验金收益";
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
    		case '27':
    			$val['memo'] ="资金重复修正";
    			break;
    		/*case '57':
    			$val['memo'] ="使用代金券";
    			break;*/
    		case '29':
    			$val['memo'] ="虚拟货币转换";
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
    		case '58':
    			$val['memo'] ="募集期收益";
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
			case '59':
    			$val['memo'] ="奖励加息收益";
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
			case '60':
    			$val['memo'] ="加息卡收益";
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
			case '61':
    			$val['memo'] ="现金红包";
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
            case '62':
                $val['memo'] ="转让成功";
                $val['money'] = "+".sprintf("%.2f", floatval($val['money']));
                break;
            case '48':
                //自动投标
                $val['money'] = $val['money'] > 0 ? "+".sprintf("%.2f", floatval($val['money'])) : $val['money'];
                break;
        	default:
        		# code...
        		break;
        }
        $details[$key]['create_time']=date('Y-m-d H:i:s',$val['create_time']);
        if(date('Y-m-d', $val['create_time'])==date('Y-m-d',TIME_UTC)){
            $val['week'] = '今天';
        }elseif(date("Y-m-d",strtotime("-1 day"))==date('Y-m-d',TIME_UTC)){
            $val['week'] = '昨天';
        }else{
            $val['week']= week(date('N', $val['create_time']));
        }
	    $val['time'] = date('H:i',$val['create_time']);
	    $val['create_time'] = $details[$key]['create_time'];	      
	    $list[$create_time][]=$val; //-->将查出的每个年月日，以年月分离出来，做为新数组的下标  
	    
	}
    $GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));
    $GLOBALS['tmpl']->assign("details",$list);
        /*******wap2.0 资金记录模拟数据********/
    $GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_list.html");
    $GLOBALS['tmpl']->display("page/uc.html");
}

public function ajaxMoney(){
	$user_id = $GLOBALS['user_info']['id'];
	$limit = "";
	$invest_type = intval($_REQUEST['invest_type']);
	if($invest_type == 1){
		$condition = "and (type in(1,2,5,47,29,58) or brief= '提现成功') and cunguan_tag = 0";
	}else{
		$condition = "and (type in(1,2,3,4,5,47,29,58,59,60,61,62,48) or brief= '提现成功') and cunguan_tag = 1";
	}
	$result = get_user_money_log($limit,$user_id,-1,$condition);
	echo $result['count'];
}

public function moneyList(){
	$user_id = $GLOBALS['user_info']['id'];
	$page = $_REQUEST['page'];
	$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
	$invest_type = intval($_REQUEST['invest_type']);
	if($invest_type == 1){
		$condition = " and (type in(1,2,5,47,29,58) or brief= '提现成功') and cunguan_tag = 0";
	}else{
		$condition = " and (type in(1,2,3,4,5,47,29,58,59,60,61,62,48) or brief= '提现成功') and cunguan_tag = 1";
	}
	$result = get_user_money_log($limit,$user_id,-1,$condition);
	$details = $result['list'];
	$month_time_start = to_date(TIME_UTC,"m");
	foreach($details as $key=>$val){
		if(date('m',$val['create_time']) == $month_time_start){
			$create_time=date('本月 Y年',$val['create_time']);
		}else{
			$create_time=date('m月 Y年',$val['create_time']);//-->将查出的每个年月日，以年月分离出来，做为新数组的下标
		}
        $val['icon'] = $this->get_type_icon($val['type'],$val['money']);
		switch ($val['type']) {
        	case '1':
        		$val['memo'] ="充值成功";
        		$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
        		break;
        	case '2':
        		$val['memo'] ="出借成功";
        		break;
				
			case '3':
				$val['memo'] ="标的放款";
				break; 
			case '4':
				$val['memo'] ="偿还本息";
				break;
    		case '5':
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
    		case '8':
    			$val['memo'] ="提现成功";
    			if($val['money'] > 0){
    				$val['money'] = "-".sprintf("%.2f", floatval($val['money']));
    			}    			
    			break;
    		case '47':
    			$val['memo'] ="领取体验金收益";
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
    		case '27':
    			$val['memo'] ="资金重复修正";
    			break;
    		case '29':
    			$val['memo'] ="虚拟货币转换";
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
    		case '58':
    			$val['memo'] ="募集期收益";
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
			case '59':
    			$val['memo'] ="奖励加息收益";
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
			case '60':
    			$val['memo'] ="加息卡收益";
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
			case '61':
    			$val['memo'] ="现金红包";
    			$val['money'] = "+".sprintf("%.2f", floatval($val['money']));
    			break;
            case '62':
                $val['memo'] ="转让成功";
                $val['money'] = "+".sprintf("%.2f", floatval($val['money']));
                break;
        	default:
        		# code...
        		break;
        }
        $details[$key]['create_time']=date('Y-m-d H:i:s',$val['create_time']);
        if(date('Y-m-d', $val['create_time'])==date('Y-m-d',TIME_UTC)){
            $val['week'] = '今天';
        }elseif(date("Y-m-d",strtotime("-1 day"))==date('Y-m-d',TIME_UTC)){
            $val['week'] = '昨天';
        }else{
            $val['week']= week(date('N', $val['create_time']));
        }
		$details[$key]['create_time']=date('Y-m-d H:i:s',$val['create_time']);
		$val['week']= week(date('N', $val['create_time']));
		$val['time'] = date('H:i',$val['create_time']);
	    $val['create_time'] = $details[$key]['create_time'];	      
	    $list[$create_time][]=$val; //-->将查出的每个年月日，以年月分离出来，做为新数组的下标  
	    
	}
	if (empty($list)) {
            echo 'false';
        }else{
            $GLOBALS['tmpl']->assign('details', $list);
            $info = $GLOBALS['tmpl']->fetch("inc/uc/moneyList.html");
            echo $info;
        }
}

    public function get_type_icon($type,$money){
        if($type==1 || $type==47 || $type==58 || $type==5 || $type==29 || $type==59 || $type==60|| $type==3 ||$type==5 || $type==62){
            $icon = "my-cash-cz"; //充值成功
        }elseif($type==2 || $type==8 || $type==27||$type==4){
            if($money>0){
                $icon = "my-cash-bb"; //提现失败
            }else{
                $icon = "my-cash-aa"; //提现成功
            }
        }elseif($type==56 ||$type==61){
            if($money>0){
                $icon = "my-cash-cc"; //红包获得
            }else{
                $icon = "my-cash-hb"; //红包使用
            }
        }elseif($type==57){
            if($money>0){
                $icon = ""; //代金券获得
            }else{
                $icon = "my-cash-dd"; //代金券使用
            }
        }elseif($type == 48){
            if($money>0){
                $icon = "my-cash-cz"; //自动投标返还
            }else{
                $icon = "my-cash-aa"; //自动投标冻结
            }
        }
        return $icon;
    }

public function incharge()
{

    
    $switch=$GLOBALS['db']->getOne("SELECT status FROM ".DB_PREFIX."switch_conf  where switch_id=1 and status=1");//总开关
    $switch2=$GLOBALS['db']->getOne("SELECT status FROM ".DB_PREFIX."switch_conf  where switch_id=4 and status=1");//充值
    if(empty($switch) || empty($switch2)){
        $switch='1';
        $GLOBALS['tmpl']->assign('aaaaa',$switch);
    }
	$jumpUrl = es_cookie::get("jump_url_incharge");
	$GLOBALS['tmpl']->assign('jumpUrl',$jumpUrl);
	$user_id= $GLOBALS['user_info']['id'];

	$Consolidated=$GLOBALS['user_info']['money']+$GLOBALS['user_info']['cunguan_money'];
	$Consolidated=sprintf("%.2f",($Consolidated));
	$Consolidated=floatval($Consolidated);

	//企业用户判断是否开通存管 
    if(!$GLOBALS['user_info']['cunguan_tag'] &&$GLOBALS['user_info']['user_type']){
        $jump = url("index","user#company_steptwo");
        if(WAP==1) app_redirect(url("index","user#company_steptwo"));
        showErr("请您先开通银行存管账户",0,$jump);
    }
	
	//企业用户屏蔽
	if($GLOBALS['user_info']['user_type'] !='1')
	{
		
		$vo = $GLOBALS['db']->getRow("select real_name,idno,mobile from " . DB_PREFIX . "user where id =$user_id and cunguan_tag =1");
		if(empty($vo)){ 
			app_redirect(url("index","uc_depository_account#index"));
		}

	    $paypassword=$GLOBALS['db']->getOne("SELECT cunguan_pwd FROM ".DB_PREFIX."user WHERE id=$user_id and cunguan_tag=1");    
	    if(empty($paypassword)){
	        app_redirect(url("index","uc_depository_paypassword#pc_setpaypassword"));
	    }

	    
	    
	    $userbank=$GLOBALS['db']->getOne("SELECT bankcard FROM ".DB_PREFIX."user_bank WHERE user_id=".$GLOBALS['user_info']['id']." and status=1 and cunguan_tag=1");
	    if(empty($userbank)){
	        app_redirect(url("index","uc_depository_addbank#check_pwd"));
	    }
    
    }
	$GLOBALS['tmpl']->assign("money",$_REQUEST['money']);
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_MONEY_INCHARGE']);
	
    //输出支付方式
	$payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment where is_effect = 1 and class_name <> 'Account' and class_name <> 'Voucher' and class_name <> 'tenpayc2c' and online_pay = 1 order by sort desc");			
	foreach($payment_list as $k=>$v)
	{
		if($v['class_name']=='Alipay')
		{
			$cfg = unserialize($v['config']);
			if($cfg['alipay_service']!=2)
			{
				unset($payment_list[$k]);
				continue;
			}
		}
		$directory = APP_ROOT_PATH."system/payment/";
		$file = $directory. $v['class_name']."_payment.php";
		if(file_exists($file))
		{
			require_once($file);
			$payment_class = $v['class_name']."_payment";
			$payment_object = new $payment_class();
			$payment_list[$k]['display_code'] = $payment_object->get_display_code();						
		}
		else
		{
			unset($payment_list[$k]);
		}
	}
	$GLOBALS['tmpl']->assign("payment_list",$payment_list);

	//判断是否有线下支付
	$below_payment = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where is_effect = 1 and class_name = 'Otherpay'");
	if($below_payment){
		$directory = APP_ROOT_PATH."system/payment/";
		$file = $directory. $below_payment['class_name']."_payment.php";	
		if(file_exists($file))
		{
			require_once($file);
			$payment_class = $below_payment['class_name']."_payment";
			$payment_object = new $payment_class();
			$below_payment['display_code'] = $payment_object->get_display_code();						
		}
		$GLOBALS['tmpl']->assign("below_payment",$below_payment);
	}
    $user_id=$GLOBALS['user_info']['id'];
	$bank = $GLOBALS['db']->getRow("SELECT ub.id,ub.bankcard,ub.region_lv2,ub.region_lv3,ub.region_lv4,ub.bankzone,b.name,b.icon,b.single_quota,b.day_limit,b.ll_single_quota,b.ll_day_limit,b.cg_single_quota,b.cg_day_limit FROM ".DB_PREFIX."user_bank as ub left join ".DB_PREFIX."bank as b on ub.bank_id=b.bankid where ub.user_id=".$user_id." and ub.status=1 and ub.cunguan_tag=1 and b.is_rec=1 order by ub.redline desc limit 1");
	if($bank) {
			$bank['sub_card'] = substr($bank['bankcard'],-4,4);
			$bank['bankcard'] = substr($bank['bankcard'], 0, 4) . "**** **** ****" . substr($bank['bankcard'], -3, 3);
			$sheng = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "region_conf where id =" . $bank['region_lv2']);
			$shi = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "region_conf where id =" . $bank['region_lv3']);
			$qu = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "region_conf where id =" . $bank['region_lv4']);
			$bank['addr'] = $sheng . '-' . $shi . '-' . $qu;
		}
	$GLOBALS['tmpl']->assign("bank",$bank);
	/*移动端交互处理*/
	$jump = machineInfo();
	if(($jump['ToProductList']=='iosToProductList'||$jump['ToProductList']=='androidToProductList')&&$GLOBALS['user_info']['user_type'] =='1'){
		$GLOBALS['tmpl']->assign("recharger_explain","1");
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->display("page/activity/warning.html");exit();
		
	}
	$GLOBALS['tmpl']->assign('jump',$jump);



	/*******充值说明*******/

	$user_type=$GLOBALS['user_info']['user_type'];//1是企业 0是存管

    //银行的名字
    $bankcard = $GLOBALS['db']->getRow("SELECT bank_id,bankcard FROM  ".DB_PREFIX."user_bank WHERE user_id=$user_id and status=1");

    $curesult= floatval($GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as cunmoney FROM ".DB_PREFIX."user WHERE id=".$user_id));
 
    $bankcard_info = $GLOBALS['db']->getRow("SELECT icon,day_limit,single_quota,name FROM  " . DB_PREFIX . "bank WHERE id=" . $bankcard['bank_id']);
	$recharger_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'recharger_explain'"));
	$bankcard['last_four']=substr($bankcard['bankcard'],-4);
	$GLOBALS['tmpl']->assign("Consolidated",$Consolidated);
    $GLOBALS['tmpl']->assign("bank_info",$bankcard_info);
    $GLOBALS['tmpl']->assign("bankcard",$bankcard);
    $GLOBALS['tmpl']->assign("curesult",$curesult);
    $GLOBALS['tmpl']->assign("user_type",$user_type);
	$GLOBALS['tmpl']->assign("recharger_explain",$recharger_explain);
	$GLOBALS['tmpl']->assign("cate_title","充值");
	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_incharge.html");
	$GLOBALS['tmpl']->display("page/uc.html");
}


public function incharge_log()
{
	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_MONEY_INCHARGE_LOG']);


		$custody_paging = intval($_REQUEST['custody_paging']);
	    $GLOBALS['tmpl']->assign("custody_paging",$custody_paging);
        $page_args['custody_paging'] = $custody_paging;


        $rec_type=$GLOBALS['user_info']['user_type'];//1是企业 0是存管

        $time=time();
        $condition='';

        if ($custody_paging == 1){
            $condition .= " AND pn.cunguan_tag=0";
        }else{          

        	if($rec_type=="0"){ 
        		$condition .= " AND pn.cunguan_tag = 1";
        	}else{ 
        		$condition .= " AND pn.cunguan_tag = 1 and pn.user_type=1";
        	}
    	}

        $page_pram = "";
        foreach($page_args as $k=>$v){
            $page_pram .="&".$k."=".$v;
        }

        //输出充值订单
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*10).",10";


		$result = get_user_incharge_log($limit,$GLOBALS['user_info']['id'],$condition);

		if(empty($result)){
            $result['list']='';
        }

		//充值的分类
        $voucher_deal = array(
            array(
                "name" => "存管账户",
            ),
            array(
                "name" => "	普通账户",
            ),
        );
 

        foreach($voucher_deal as $k=>$v){
            $tmp_args = $page_args;
            $tmp_args['custody_paging']=$k;
            $voucher_deal[$k]['url'] = url("index","uc_money#incharge_log",$tmp_args);  //是从20出来的   $url =$url.$k."=".urlencode($v)."&";
        }   

    /*
    $condition = "";
	$is_paid = isset($_REQUEST['is_paid']) ? intval($_REQUEST['is_paid']) : 0;
	if($is_paid == 0 )
	{
		$condition.=" and pn.is_paid = 0";
	}else{
		$condition.=" and pn.is_paid = 1";
	}
	$GLOBALS['tmpl']->assign('is_paid',$is_paid);
    */

		$page = new Page($result['count'],10,$page_pram);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('voucher_deal',$voucher_deal);
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_incharge_log.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}


	
	public function incharge_done()
	{
		/*
		$payment_id = intval($_REQUEST['payment']);
		$money = floatval($_REQUEST['money']);
		$bank_id = addslashes(htmlspecialchars(trim($_REQUEST['bank_id'])));
		$memo = addslashes(htmlspecialchars(trim($_REQUEST['memo'])));
		
		
		if($money<=0)
		{
			showErr($GLOBALS['lang']['PLEASE_INPUT_CORRECT_INCHARGE']);
		}
		
		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$payment_id);
		if(!$payment_info)
		{
			showErr($GLOBALS['lang']['PLEASE_SELECT_PAYMENT']);
		}
		//开始生成订单
		$now = TIME_UTC;
		$order['type'] = 1; //充值单
		$order['user_id'] = $GLOBALS['user_info']['id'];
		$order['create_time'] = $now;
		if($payment_info['fee_type'] == 0)
			$order['total_price'] = $money + $payment_info['fee_amount'];
		else
			$order['total_price'] = $money + $payment_info['fee_amount']*$money;
			
		$order['deal_total_price'] = $money;
		$order['pay_amount'] = 0;  
		$order['pay_status'] = 0;  
		$order['delivery_status'] = 5;  
		$order['order_status'] = 0; 
		$order['payment_id'] = $payment_id;
		if($payment_info['fee_type'] == 0)
			$order['payment_fee'] = $payment_info['fee_amount'];
		else
			$order['payment_fee'] = $payment_info['fee_amount']*$money;
			
		$order['bank_id'] = $bank_id;
		$order['memo'] = $bank_id;
		if($payment_info['class_name']=='Otherpay' && $order['memo']!=""){
			
			$payment_info['config'] = unserialize($payment_info['config']);
			$order['memo'] = "银行流水单号:".$order['memo'];
			$order['memo'] .= "<br>开户行：".$payment_info['config']['pay_bank'][$order['bank_id']];
			$order['memo'] .= "<br>充值银行：".$payment_info['config']['pay_name'][$order['bank_id']];
			$order['memo'] .= "<br>帐号：".$payment_info['config']['pay_account'][$order['bank_id']];
			$order['memo'] .= "<br>用户：".$payment_info['config']['pay_account_name'][$order['bank_id']];
		}
		do
		{
			$order['order_sn'] = to_date(TIME_UTC,"Ymdhis").rand(100,999);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order,'INSERT','','SILENT'); 
			$order_id = intval($GLOBALS['db']->insert_id());
		}while($order_id==0);
		
		require_once APP_ROOT_PATH."system/libs/cart.php";
		$payment_notice_id = make_payment_notice($order['total_price'],$order_id,$payment_info['id'],$order['memo']);
		//创建支付接口的付款单
	*/
		//$payment_id = intval($_REQUEST['payment']);
		//$money = floatval($_REQUEST['money']);
		//$bank_id = addslashes(htmlspecialchars(trim($_REQUEST['bank_id'])));
		//$memo = addslashes(htmlspecialchars(trim($_REQUEST['memo'])));
		//$pingzheng = replace_public(trim($_REQUEST['pingzheng']));
		$payment_id = 5;
/*		return $payment;die;*/
		$money = floatval($_REQUEST['money']);
		$bank_id = 1;
		$memo = addslashes(htmlspecialchars(trim($_REQUEST['memo'])));
		$pingzheng = replace_public(trim($_REQUEST['pingzheng']));
		
		$status = getInchargeDone($payment_id,$money,$bank_id,$memo,$pingzheng);
		if($status['status'] == 0){			
			showErr($status['show_err']);
		}
		else{
			if($status['pay_status'])
			{
				app_redirect(url("index","payment#incharge_done",array("id"=>$status['order_id']))); //充值支付成功
			}
			else
			{
				app_redirect(url("index","payment#pay",array("id"=>$status['payment_notice_id'])));
			}
		}		
		
	}
	

	
	public function bank(){

		//判断是否是企业用户
        if($GLOBALS['user_info']['user_type'] &&!$GLOBALS['user_info']['cunguan_tag']){
            $jump = url("index","user#company_steptwo");
            if(WAP==1) app_redirect(url("index","user#company_steptwo"));
            showErr("请您先开通银行存管账户",0,$jump);
        }

        //企业用户绑卡信息 cy
        $company_bank = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."company_reginfo  where user_id=".intval($GLOBALS['user_info']['id']));
        
        $GLOBALS['tmpl']->assign("company_bank",$company_bank);

		jumpUrl("jump_carry_bank");
        $user_id=$GLOBALS['user_info']['id'];
        /*
		$bank_list = $GLOBALS['db']->getAll("SELECT ub.*,b.icon FROM ".DB_PREFIX."user_bank ub left join ".DB_PREFIX."bank b on ub.bank_id=b.id  where user_id=".intval($GLOBALS['user_info']['id'])." ORDER BY id ASC");
		foreach($bank_list as $k=>$v){
			$bank_list[$k]['bankcode'] = str_replace(" ","",$v['bankcard']);
		}
		$GLOBALS['tmpl']->assign("bank_list",$bank_list);

		if(app_conf("OPEN_IPS") > 0){
			if(strtolower(getCollName()) == "yeepay")
			{
				$yee_bank = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."yeepay_bind_bank_card where is_callback =1 and code =1 and platformUserNo = ".intval($GLOBALS['user_info']['id']));
				$GLOBALS['tmpl']->assign("yee_bank",$yee_bank);
				$GLOBALS['tmpl']->assign("is_yee",1);
			}
			//手续费
			$fee_config = load_auto_cache("user_carry_config");
			$json_fee = array();
			foreach($fee_config as $k=>$v){
				$json_fee[] = $v;
				if($v['fee_type']==1)
					$fee_config[$k]['fee_format'] = $v['fee']."%";
				else
					$fee_config[$k]['fee_format'] = format_price($v['fee']);
			}
			$GLOBALS['tmpl']->assign("fee_config",$fee_config);
			$GLOBALS['tmpl']->assign("json_fee",json_encode($json_fee));
		}
		*/
		//提现说明
		$withdraw_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'withdraw_explain'"));
		$GLOBALS['tmpl']->assign("withdraw_explain",$withdraw_explain);
        //银行的名字
        // $bank_name = $GLOBALS['db']->getAll("SELECT name FROM ".DB_PREFIX."bank ");
// 		$bank = $GLOBALS['db']->getRow("SELECT ub.id,ub.bankcard,ub.region_lv2,ub.region_lv3,ub.region_lv4,ub.bankzone,b.name,b.icon,b.single_quota,b.day_limit FROM ".DB_PREFIX."user_bank as ub join ".DB_PREFIX."bank as b on ub.bank_id=b.bankid where ub.status=1 and ub.user_id=".$user_id." and b.is_rec=1 and ub.cunguan_tag=1 order by ub.redline desc limit 1 ");
		$bank = $GLOBALS['db']->getRow("SELECT ub.id,ub.bankcard,ub.region_lv2,ub.region_lv3,ub.region_lv4,ub.bankzone,b.name,b.icon,b.single_quota,b.day_limit FROM ".DB_PREFIX."user_bank as ub join ".DB_PREFIX."bank as b on ub.bank_id=b.id where ub.user_id=".$user_id." and ub.status=1 and b.is_rec=1 order by ub.redline desc limit 1");
        if($bank) {
            $bank['sub_card'] = substr($bank['bankcard'],-4,4);
            $bank['bankcard'] = substr($bank['bankcard'], 0, 4) . "**** **** ****" . substr($bank['bankcard'], -3, 3);
            $sheng = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "region_conf where id =" . $bank['region_lv2']);
            $shi = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "region_conf where id =" . $bank['region_lv3']);
            $qu = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "region_conf where id =" . $bank['region_lv4']);
            $bank['addr'] = $sheng . '-' . $shi . '-' . $qu;
            $bank['procity'] = $sheng .'-'.$shi;
            $bank['qu'] =$qu;
            //$bank['bankz']=substr(strrchr($bank['bankzone'],"-"),1);
            $bank['heng'] = '-';
        }
        if($bank['bankzone']){
			$ajax_code = 1;
		}else{
			$ajax_code = 0;
		}
		
		//存管银行信息
		$cg_bank = $GLOBALS['db']->getRow("select ub.id,ub.user_id,ub.bank_id,ub.real_name,ub.bank_mobile,ub.bankcard,ub.status,b.cg_day_limit,b.cg_single_quota,b.name,b.icon from " . DB_PREFIX . "user_bank as ub join " .DB_PREFIX."bank as b on ub.bank_id=b.bankid where ub.user_id=".$user_id." and ub.status=1 and ub.cunguan_tag=1");
		if($cg_bank){
            $cg_bank['sub_card'] = substr($cg_bank['bankcard'],-4);
            $cg_bank['bankcard'] = substr($cg_bank['bankcard'], 0, 4) . "**** **** ****" . substr($cg_bank['bankcard'], -3, 3);
            $cg_real_name_len = mb_strlen($cg_bank['real_name'],'utf-8')+1;
            $cg_bank['real_name'] = '*'.(mb_substr($cg_bank['real_name'],1,$cg_real_name_len,'utf-8'));
            $cg_bank['bank_mobile'] = substr($cg_bank['bank_mobile'], 0, 3) . "****" . substr($cg_bank['bank_mobile'], -4, 4);
        }

        //2.0余额
		$GLOBALS['tmpl']->assign('money',number_format($GLOBALS['user_info']['money'],2));
		$GLOBALS['tmpl']->assign('user_money',floatval($GLOBALS['user_info']['money']));
		//存管余额
		$GLOBALS['tmpl']->assign('cg_money',number_format($GLOBALS['user_info']['cunguan_money'],2));
		$GLOBALS['tmpl']->assign('cg_user_money',floatval($GLOBALS['user_info']['cunguan_money']));
        //存管可提现金额
// 		$cg_user_info = get_cg_user_info($user_id);
// 		$GLOBALS['tmpl']->assign('withdrawalamount',$cg_user_info['withdrawalamount']);
		
        // $GLOBALS['tmpl']->assign('bank_name',$bank_name);
		$GLOBALS['tmpl']->assign('ajax_code',$ajax_code);
		$GLOBALS['tmpl']->assign("cate_title","提现");
		$GLOBALS['tmpl']->assign("bank_list",$bank);
		$GLOBALS['tmpl']->assign("cg_bank_list",$cg_bank);
		$GLOBALS['tmpl']->assign("page_title","银行卡管理");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_carry_bank.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	//支持银行卡及限额 充值页跳转
	public function inbank(){
		/*移动端交互处理*/
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
		$bankInfo = $GLOBALS['db']->getAll("select icon,name,cg_day_limit,cg_single_quota from ".DB_PREFIX."bank where bankid!=''");
		/*$llBankInfo = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."bank where is_rec = 1 and ll_single_quota != ''");*/
		$GLOBALS['tmpl']->assign("bankInfo",$bankInfo);
		$GLOBALS['tmpl']->assign("llBankInfo",$llBankInfo);
		$GLOBALS['tmpl']->assign("cate_title","支持银行及限额");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_incharge_inbank.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	//支持银行卡及限额 我的银行卡页跳转
	public function account_bank(){
		/*移动端交互处理*/
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign('jump',$jump);
		$bankInfo = $GLOBALS['db']->getAll("select icon,name,cg_day_limit,cg_single_quota from ".DB_PREFIX."bank where is_rec = 1 and cunguan_tag=1");
		/*$llBankInfo = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."bank where is_rec = 1 and ll_single_quota != ''");*/
		$GLOBALS['tmpl']->assign("bankInfo",$bankInfo);
		$GLOBALS['tmpl']->assign("llBankInfo",$llBankInfo);
		$GLOBALS['tmpl']->assign("cate_title","支持银行及限额");
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_inbank.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function addbank(){
		//判断是否验证过身份证
		/*
		if($GLOBALS['user_info']['real_name']==""){
			showErr("<div>您的实名信息尚未填写！</div>为保护您的账户安全，请先填写实名信息。",1,url("index","uc_account#security"));
			die();
		}
		*/
        $user_id=$GLOBALS['user_info']['id'];
		$bank_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."bank where is_rec=1 and id not in(45,46,47,48,49,50,51) ORDER BY is_rec DESC,sort DESC,id ASC");

		$GLOBALS['tmpl']->assign("bank_list",$bank_list);

        //银行卡
        $bankcard = $GLOBALS['db']->getOne("SELECT bankcard FROM ".DB_PREFIX."user_bank WHERE user_id=$user_id and status=1");
        if(!empty($bankcard)){
            $bankcard=$bankcard;
        }else{
            $bankcard="";
        }
        $GLOBALS['tmpl']->assign('bankcard',$bankcard);

		//地区列表
		$region_lv1 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 1");  //二级地址
		$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);

        //实名
		$real_name = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
        $GLOBALS['tmpl']->assign('real_name',$real_name);

        //身份证号
        $idno = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
        if(!empty($idno)){
            $idno = substr($idno,0,10)."****".substr($idno,-1,1);
        }else{
            $idno ="";
        }
        $GLOBALS['tmpl']->assign('idno',$idno);

		$info =  $GLOBALS["tmpl"]->fetch("inc/uc/uc_money_carry_addbank.html");
		showSuccess($info,1);
	}

	public function editbank(){
		//银行卡信息
        $user_id = $GLOBALS['user_info']['id'];
		$bank = $GLOBALS['db']->getRow("SELECT ub.bankcard,ub.region_lv2,ub.region_lv3,ub.region_lv4,ub.bankzone,b.name,b.icon FROM ".DB_PREFIX."user_bank as ub join ".DB_PREFIX."bank as b on ub.bank_id=b.id where ub.user_id=$user_id and ub.status=1 and b.is_rec=1 order by ub.redline desc limit 1");
        $bank['bankcard'] = substr($bank['bankcard'],0,4)."****".substr($bank['bankcard'],-3,3);
		$GLOBALS['tmpl']->assign("bank",$bank);
		//省市县
		$region_lv1 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 1");  //二级地址
		$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);
		$info =  $GLOBALS["tmpl"]->fetch("inc/uc/uc_money_carry_editbank.html");
		showSuccess($info,1);
	}

    public function editbank_save(){
        if(!$_POST)
        {
            app_redirect("404.html");
            exit();
        }
        foreach($_POST as $k=>$v)
        {
            $_POST[$k] = htmlspecialchars(addslashes($v));
        }

        $data['region_lv1'] = intval($_POST['region_lv1']);
		$data['region_lv2'] = intval($_POST['region_lv2']);
		$data['region_lv3'] = intval($_POST['region_lv3']);
		$data['region_lv4'] = intval($_POST['region_lv4']);

        if($data['region_lv1'] == 0){
            showErr("请选择开户行所在地",1);
        }

        if($data['region_lv2'] == 0){
            showErr("请选择开户行所在地",1);
        }

		if($data['region_lv3'] == 0){
			showErr("请选择开户行所在地",1);
		}

        if($data['region_lv4'] == 0){
            showErr("请选择开户行所在地",1);
        }
        $user_id=$GLOBALS['user_info']['id'];
        $province = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "region_conf where id =" . $data['region_lv2']);
        $city = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "region_conf where id =" . $data['region_lv3']);
        $area= $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "region_conf where id =" . $data['region_lv4']);
        $data['bankzone'] = trim($_POST['bankzone']);
        if($data['bankzone'] == ""){
			showErr("请输入开户行网点",1);
		}
        //$data['bankzone'] = $province . " " . $city . " " . $area .'- '.$bankzone;
        $result=$GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$data,"UPDATE","user_id=$user_id");
        if($result){
            showSuccess("保存成功",1);
        }else{
            showSuccess("失败",1);
        }
        //end
    }

    /*更换银行卡*/
    public function changebank(){
        $carry_status = $GLOBALS['db']->getAll("SELECT count(*) as user_carry FROM ".DB_PREFIX."user_carry where user_id=".intval($GLOBALS['user_info']['id'])." AND status IN(0,3) and cunguan_tag=1");
        //$carry_list = $GLOBALS['db']->getAll("SELECT sum(money) FROM ".DB_PREFIX."user_carry where user_id=".intval($GLOBALS['user_info']['id'])." AND status IN(0,3)");
        //$payment_lists = $GLOBALS['db']->getAll("SELECT sum(money) FROM ".DB_PREFIX."payment_notice where user_id=".intval($GLOBALS['user_info']['id'])." AND is_paid=1");
        $user_bank = $GLOBALS['db']->getAll("SELECT count(*) as bank FROM ".DB_PREFIX."deal ub LEFT JOIN ".DB_PREFIX."deal_load b on ub.id=b.deal_id where b.user_id=".intval($GLOBALS['user_info']['id'])." AND ub.deal_status IN(1,2,4) and ub.cunguan_tag=1");
        $money=$GLOBALS['user_info']['cunguan_money'];


        if($money !=0.00) {
        	$info =  $GLOBALS["tmpl"]->fetch("inc/uc/uc_money_carry_editbank_no.html");
            showErr($info,1);
        }

        if($user_bank[0]['bank']>0){
            $info =  $GLOBALS["tmpl"]->fetch("inc/uc/uc_money_carry_editbank_no.html");
            showErr($info,1);
        }

        if($carry_status[0]['user_carry']>0){
            $info =  $GLOBALS["tmpl"]->fetch("inc/uc/uc_money_carry_editbank_no.html");
            showErr($info,1);
        }



        $bank_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."bank ORDER BY is_rec DESC,sort DESC,id ASC");
        $GLOBALS['tmpl']->assign("bank_list",$bank_list);

        //地区列表
        $region_lv1 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."region_conf where region_level = 1");  //二级地址
        $GLOBALS['tmpl']->assign("region_lv1",$region_lv1);

        //实名
        $real_name = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
        $GLOBALS['tmpl']->assign('real_name',$real_name);

        //身份证号
        $idno = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
        if(!empty($idno)){
            $idno = substr($idno,0,10)."****".substr($idno,-1,1);
        }else{
            $idno ="";
        }
        $GLOBALS['tmpl']->assign('idno',$idno);

        $info =  $GLOBALS["tmpl"]->fetch("inc/uc/uc_money_carry_addbank.html");
        showSuccess($info,1);
    }

	public function delbank(){
		$id = intval($_REQUEST['id']);
		if($id==0){
			showErr("数据不存在",1);
		}
		$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."user_bank where user_id=".intval($GLOBALS['user_info']['id'])." and id=".$id);
		if($GLOBALS['db']->affected_rows()){
			showSuccess("删除成功",1);
		}
		else{
			showErr("删除失败",1);
		}
	}

    /*
    *   添加银行卡
    *  	第三方银行卡验证
    *	$name  			真实姓名
    *	$idnum 			身份证号
    *	$phone 			银行卡绑定手机号
    *	$bankCard 		银行卡号
    *	$sid        	企业账号
    *	$md5key     	MD5密码
    *	$despwd   		3DES密码
    *	$cpserialnum	订单号
    ****/
	public function savebank(){
        require APP_ROOT_PATH."system/utils/Verify.php";
        require APP_ROOT_PATH."system/utils/BinkCard/Imagebase64.php";
        require APP_ROOT_PATH."system/utils/bankList.php";

        if(!$_POST)
        {
            app_redirect("404.html");
            exit();
        }
        foreach($_POST as $k=>$v)
        {
            $_POST[$k] = htmlspecialchars(addslashes($v));
        }

        $data['uc_IDcard']=$_POST['uc_IDcard'];//身份证

            if(strpos($data['uc_IDcard'],"****") ){
                $data['uc_IDcard'] = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
            }else{
                $data['uc_IDcard']=$data['uc_IDcard'];
            }
       if($data['uc_IDcard']==''){
       		showErr("请输入身份证号！",1);
       }
        $data['bank_mobile']=$_POST['bankphone'];//手机号
        $data['validateCode']=$_POST['validateCode'];//验证码

        $verify_code=$GLOBALS['db']->getOne("SELECT verify_code  FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$data['bank_mobile']."' AND verify_code='".$data['validateCode']."'  AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC."");
        if($data['validateCode'] !=$verify_code){
            showErr("验证码错误或已过期",1);
        }

        $data['bank_id'] = intval($_POST['bank_id']);

		if($data['bank_id'] == 0)
		{
			$data['bank_id'] = intval($_POST['otherbank']);
		}
		
		if($data['bank_id'] == 0)
		{
			showErr($GLOBALS['lang']['PLASE_ENTER_CARRY_BANK'],1);
		}
		
		$data['real_name'] = trim($_POST['real_name']);
        if($data['real_name'] == ""){
            showErr("请输入开户名",1);
        }

		$data['bankcard'] = trim($_POST['bankcard']);
        $data['bankcard']=str_replace(" ","",$data['bankcard']);
        #$data['bankcard'] = preg_replace("/\s/","",$data['bankcard']);

		if(str_replace(" ","",$data['bankcard']) == ""){
			showErr($GLOBALS['lang']['PLASE_ENTER_CARRY_BANK_CODE'],1);
		}
		
		if(strlen($data['bankcard']) < 10){
			showErr("最少输入10位账号信息！",1);
		}
		
		$data['user_id'] = $GLOBALS['user_info']['id'];

        if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_bank WHERE bankcard='".$data['bankcard']."'  AND cunguan_tag=0 AND user_id=".$GLOBALS['user_info']['id']) > 0){
			showErr("该银行卡已存在",1);
		}
		$isset_id=$GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."user WHERE idno='". $data['uc_IDcard']."'");
        if($isset_id>0&&$isset_id!=$GLOBALS['user_info']['id']){
        	showErr("认证失败,同一身份证不能绑定多个账号!",1);
        }
       //  include "ajaxModule.class.php";
       //  $ajax=new ajaxModule;
       //  $bank_msg=$ajax->ajax_bank_card();
       // // print_r($bank_msg);die;
       // $bank_list=$GLOBALS['db']->getOne("SELECT Banklist FROM ".DB_PREFIX."bank WHERE id=".$data['bank_id']);
       // if($bank_list!=$bank_msg['bank_code']){
       // 		showErr("认证失败,银行卡号与所选银行不一致！",1);
       // }
       // if($bank_msg['card_type']==3){
       // 		showErr("认证失败,暂不支持绑定信用卡！",1);
       // }
        $bank_no = $GLOBALS['db']->getOne("SELECT bankcard FROM  ".DB_PREFIX."user_bank WHERE user_id=".$GLOBALS['user_info']['id']." and status=1");
        $url = "http://verifyapi.huiyuenet.com/zxbank/verifyApi.do";
        $name=$data['real_name'];
        $phone =$data['bank_mobile'];
        #$bankCard = str_replace(" ","",$data['bankcard']);
        $bankCard =  $data['bankcard'];
        $idnum=$data['uc_IDcard'];
        $sid = "jxdbc";
        $cpserialnum = $this->orderId();
        $md5key = "l46g6i";
        $despwd = "9cwcweunozhw15ul6elezl5y";
        $vtype = "03";
        $verifyFun = new VerifyFun($url);
        $result=$verifyFun->zXBank($sid, $name, $idnum, $vtype, $phone, $bankCard, $cpserialnum, $despwd,$md5key);
        $array = json_decode($result,1);
        switch ($array['result']) {
            case 'BANKCONSISTENT':
                if(!$bank_no){
                    $res = $this->addCard($data['uc_IDcard'],$data['bank_id'],$data['real_name'],$data['bankcard'],$data['bank_mobile']);
//                	if(is_get_rewards($GLOBALS['user_info']['id'])&&$res){
//                		// 邀请发放站内信
//		        		$notices['site_name'] = app_conf("SHOP_TITLE");
//						$notices['friend_name'] = utf_substr($name);
//						$notices['user_name'] = get_user_info("real_name","id=".$GLOBALS['user_info']['pid'],"ONE");
//						$to_user_id=$GLOBALS['user_info']['pid'];
//						$time=TIME_UTC;
//						$tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_INS_INVITE_REWARDS'",false);
//						$GLOBALS['tmpl']->assign("notice",$notices);
//						$content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);
//						send_user_msg("您成功邀请好友".$notices['friend_name']."，获得一张20元代金券",$content,0,$to_user_id,$time,0,true,22);
//		        		//////////////////
//                		$order_data['begin_time'] = TIME_UTC;
//						$order_data['end_time'] = to_timespan(to_date($order_data['begin_time'])." ".app_conf("INTERESTRATE_TIME")." month -1 day");
//						$order_data['money'] = 20;
//						$order_data['ecv_type_id'] = 5;
//						$sn = unpack('H12',str_shuffle(md5(uniqid())));
//						$order_data['sn'] = $sn[1];
//						$order_data['password'] = rand(10000000,99999999);
//						$order_data['user_id']=$GLOBALS['user_info']['pid'];
//						$order_data['child_id']=$GLOBALS['user_info']['id'];
//						$check=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."ecv where user_id=".$GLOBALS['user_info']['pid']." and child_id=".$GLOBALS['user_info']['id']);
//							if(empty($check)){
//								$result=$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$order_data,"INSERT");
//				        		if($result==false){
//				        			showErr("代金券发放失败",1);
//				        		}
//							}
//                	}
                }else{
                    $res = $this->saveCard($data['bank_id'],$data['real_name'],$data['bankcard'],$data['bank_mobile']);
                }

                if($res){
                    showSuccess("绑定成功",1);
                }else{
                    showErr("保存失败",1);
                }
                break;

            case 'BANKNOLIB':
                showErr("没有此银行卡信息",1);
                break;

            case 'BANKINCONSISTENT':
                showErr("身份信息与银行卡信息不匹配",1);
                break;

            case 'BANKUNKNOWN':
                showErr("银行卡信息未知",1);
                break;

            case 'FAIL':
                $info = $this->fail($array['errmsg']);
                showErr("$info",1);
                break;
            default:
                showErr("银行卡信息未知",1);
                break;
        }
      //end
	}

    public function fail($err){
        switch ($err) {
            case 'ERR2011':
                $info = "不存在该银行账户";
                break;
            case 'ERR2012':
                $info = "数据传输错误，请稍后再试";
                break;
            case 'ERR2013':
                $info = "银行账户已停用";
                break;
            case 'ERR2014':
                $info = "数据传输错误，请稍后再试";
                break;
            case 'ERR2015':
                $info = "身份证号无效";
                break;
            case 'ERR2016':
                $info = "不支持该卡验证";
                break;
            case 'ERR2017':
                $info = "其他错误（银行返回）";
                break;
            case 'ERR2020':
                $info= "卡号异常,请稍后再试";
                break;
            case 'ERR2021':
                $info="银行处理失败,请稍后重试";
                break;
            case 'ERR2022':
                $info="姓名，身份证号，手机号不匹配";
                break;
            case 'ERR9999':
                $info = "服务器错误，请稍后再试";
                break;
            default:
                $info ="未知错误,请联系客服";
                break;
        }
        return $info;
    }
	
    //添加银行卡
    public function addCard($uc_IDcard,$bank_id,$real_name,$bankcard,$bank_mobile){
        $user_id=$GLOBALS['user_info']['id'];
        $user_info['user_id'] = $GLOBALS['user_info']['id'];
        $user_info['real_name'] = $real_name;
        #$user_info['bankcard'] = str_replace(" ","",$bankcard);
        $user_info['bankcard'] =$bankcard;
        $user_info['bank_id'] = $bank_id;
        $user_info['bank_mobile'] = $bank_mobile;
        $user_info['create_time'] = TIME_UTC;
        $user_info['status'] =1;
        $res = $GLOBALS['db']->getOne("SELECT * FROM  ".DB_PREFIX."user_bank WHERE user_id=$user_id and status=1" );
        if(!$res){
            $result=$GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_info,"INSERT");
        }
        if($result){
			//绑卡成功处理渠道记录
			/* $source_id = es_session::get("source_id");
			$device = es_session::get("device");
			addsource($source_id,$device,$user_id,2); */
            $data['real_name'] = $real_name;
            #$data['idno'] = str_replace(" ","",$bankcard);
            $data['idno'] = $uc_IDcard;
            $data['idcardpassed']=1;
            $data['idcardpassed_time'] = TIME_UTC;
            $data["real_name_encrypt"] = " AES_ENCRYPT('".$real_name."','".AES_DECRYPT_KEY."') ";
            $data["idno_encrypt"] = " AES_ENCRYPT('".$uc_IDcard."','".AES_DECRYPT_KEY."') ";
            $addlt= $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);
            return $addlt;
        }
    }

    //更换银行卡
    public function saveCard($bank_id,$real_name,$bankcard,$bank_mobile){
        $user_id=$GLOBALS['user_info']['id'];
        $user_info['real_name'] = $real_name;
        #$user_info['bankcard'] = str_replace(" ","",$bankcard);
        $user_info['bankcard']  = $bankcard;
        $user_info['bank_id'] = $bank_id;
        $user_info['bank_mobile'] = $bank_mobile;
        $user_info['create_time'] = TIME_UTC;
        $user_info['region_lv1'] = 0;
        $user_info['region_lv2'] = 0;
        $user_info['region_lv3'] = 0;
        $user_info['region_lv4'] = 0;
        $user_info['bankzone'] = '';
        //$user_info['status'] =1;
        $res = $GLOBALS['db']->getOne("SELECT * FROM  ".DB_PREFIX."user_bank WHERE user_id=$user_id and status=1 order by redline desc limit 1");
        if($res){
        	$result=$GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_info,"UPDATE","user_id=$user_id");
        }
        if(isset($result)){
        	return $result;
        }
        // if(isset($result)){
        //    $data['real_name'] = $real_name;
        //     #$data['idno'] = str_replace(" ","",$bankcard);
        //     $data['idno'] = $uc_IDcard;
        //     $data['idcardpassed']=1;
        //     $data["real_name_encrypt"] = " AES_ENCRYPT('".$real_name."','".AES_DECRYPT_KEY."') ";
        //     $data["idno_encrypt"] = " AES_ENCRYPT('".$uc_IDcard."','".AES_DECRYPT_KEY."') ";
        //     $lt= $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);
        //     return $lt;
        // }
    }

    public function orderId(){
        $yCode = array('Q', 'W', 'E', 'R', 'T', 'Y', 'N','G','M', 'C', 'O');
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
        return $orderSn;
    }

	public function carry()
	{
		//判断是否是企业用户
      /*  if($GLOBALS['user_info']['user_type']){
            $jump = url("index","uc_center#index");
            if(WAP==1) app_redirect(url("index","uc_center#index"));
            showErr("企业用户暂不可使用",0,$jump);
        }*/
	    
        $switch=$GLOBALS['db']->getOne("SELECT status FROM ".DB_PREFIX."switch_conf  where switch_id=1 and status=1");//总开关
        $switch2=$GLOBALS['db']->getOne("SELECT status FROM ".DB_PREFIX."switch_conf  where switch_id=5 and status=1");//提现
        if(empty($switch) || empty($switch2)){
            $switch='1';
            $GLOBALS['tmpl']->assign('aaaaa',$switch);
        }

        $user_id = $GLOBALS ['user_info']['id'];

        if($GLOBALS['user_info']['user_type'] =="1"){

        	if(empty($user_id)){
	            app_redirect(url("index","user#login"));
	        }

            $cg_bank = $GLOBALS['db']->getRow("SELECT username,corpacc FROM ".DB_PREFIX."company_reginfo where user_id='".$user_id."'");
            if($cg_bank['corpacc']){
               $cg_bank['last_four']=substr($cg_bank['corpacc'],-4);
            }

        }else{
	        
	        if(empty($user_id)){
	            app_redirect(url("index","user#login"));
	        }
	        //判断是否开通存管 GJQ
	        if(!$GLOBALS['user_info']['cunguan_tag']){
	            $jump = url("index","uc_depository_account#index");
	            showErr("请您先开通银行存管账户",0,$jump);
	        }
	        //判断是否设置存管交易密码
	        if(!$GLOBALS['user_info']['cunguan_pwd']){
	            $jump = url("index","uc_account#security");
	            showErr("请您先设置存管系统交易密码",0,$jump);
	        }
	        //判断存管是否绑卡
	        $cg_bank = $GLOBALS['db']->getRow("select ub.id,ub.user_id,ub.bank_id,ub.bankcard,ub.status,b.name,b.icon from " . DB_PREFIX . "user_bank as ub join " .DB_PREFIX."bank as b on ub.bank_id=b.bankid where ub.user_id=".$user_id." and ub.status=1 and ub.cunguan_tag=1");
	        if(empty($cg_bank)){
	            $jump = url("index","uc_money#bank");
	            showErr("请您先绑定存管系统的银行卡",0,$jump);
	        }else{
	            $cg_bank['last_four'] = substr($cg_bank['bankcard'],-4);
	        }
	        
	        $bank = $GLOBALS['db']->getRow("SELECT ub.id,ub.bankcard,ub.region_lv2,ub.region_lv3,ub.region_lv4,ub.bankzone,b.name,b.icon,b.single_quota,b.day_limit FROM ".DB_PREFIX."user_bank as ub join ".DB_PREFIX."bank as b on ub.bank_id=b.id where ub.user_id=$user_id and ub.status=1 and b.is_rec=1 order by ub.redline desc limit 1");
	        if($bank['bankcard']){
	            $bank['last_four']=substr($bank['bankcard'],-4);
	        }

	    }
        
        $GLOBALS['tmpl']->assign("bank_list",$bank);
        $GLOBALS['tmpl']->assign("cg_bank_list",$cg_bank);
        
// 		$red_envelope = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_money_log where user_id = ".intval($GLOBALS['user_info']['id'])." and type = 28 or type = 29  ");
// 		$exchange = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_money_log where user_id = ".intval($GLOBALS['user_info']['id'])." and type = 22 ");
// 		$red_envelope = format_price($red_envelope);
// 		$exchange = format_price($exchange);
// 		$GLOBALS['tmpl']->assign("red_envelope",$red_envelope);
// 		$GLOBALS['tmpl']->assign("exchange",$exchange);
// 		$carry_total_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."user_carry WHERE user_id=".intval($GLOBALS['user_info']['id'])." AND status=1");
//         $GLOBALS['tmpl']->assign("carry_total_money",$carry_total_money);
		
// 		$tmoney=$GLOBALS['user_info']['money']-$GLOBALS['user_info']['nmc_amount'];
// 		$vip_id = 0;
// 		if($GLOBALS['user_info']['vip_id'] > 0 && $GLOBALS['user_info']['vip_state'] == 1){
// 			$vip_id = $GLOBALS['user_info']['vip_id'];
// 		}
		//存管余额
		$recharge_money = floatval($GLOBALS['user_info']['cunguan_money']);
		//2.0普通账户余额
        $old_money = floatval($GLOBALS['user_info']['money']);
        //总余额
        $sum_money = floatval($GLOBALS['user_info']['cunguan_money'] + $GLOBALS['user_info']['money']);
        $GLOBALS['tmpl']->assign("sum_money",$sum_money);
        $GLOBALS['tmpl']->assign("recharge_money",$recharge_money);
        $GLOBALS['tmpl']->assign("old_money",$old_money);
		//手续费
// 		$fee_config = load_auto_cache("user_carry_config",array("vip_id"=>$vip_id));
// 		$json_fee = array();
// 		foreach($fee_config as $k=>$v){
// 			$json_fee[] = $v;
// 			if($v['fee_type']==1)
// 				$fee_config[$k]['fee_format'] = $v['fee']."%";
// 			else
// 				$fee_config[$k]['fee_format'] = format_price($v['fee']);
// 		}
// 		$GLOBALS['tmpl']->assign("fee_config",$fee_config);
// 		$GLOBALS['tmpl']->assign("json_fee",json_encode($json_fee));
// 		unset($fee_config);
// 		unset($json_fee);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CARRY']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_carry.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}


	public function savecarry(){
		$switch = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "switch_conf  where switch_id = 1 and status = 1"); //总开关
		$switch2 = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "switch_conf  where switch_id = 5 and status = 1");  //出借开关
		if(empty($switch)||empty($switch2)){
			$return["status"] = 0;
			$return["info"] = '系统正在升级，请稍后再试';
			$return["show_err"] = '系统正在升级，请稍后再试';
			ajax_return($return);
		}
		//判断用户是否登录
		if($GLOBALS['user_info']['id'] > 0){
			require_once APP_ROOT_PATH.'app/Lib/uc_func.php';
            if(!$_POST)
            {
                app_redirect("404.html");
                exit();
            }
            foreach($_POST as $k=>$v)
            {
                $_POST[$k] = htmlspecialchars(addslashes($v));
            }
            $withdraw_acc = intval($_POST['withdraw_acc']);//提现账户 普通账户or存管账户
            $paypassword = strim(FW_DESPWD($_POST['paypassword']));
			$amount = floatval($_POST['amount']);
			$bid = intval($_POST['bid']);

			$status = getUcSaveCarry($amount,$paypassword,$bid,$withdraw_acc);

            if($status['status'] == 0){
                $status['status']=0;
                $status['info'] = $status['show_err'];
                ajax_return($status);
			}elseif($status['status'] == 2){
			    ajax_return($status);
			}else{
                if(WAP == 1){
                	$status['url'] = url("index", "withdraw#pay", array("id" => $status['id']));
                	ajax_return($status);
                }else{
                	$status['status'] = 1;
                    $status['info'] = "提现申请成功";
                    $status['jump'] = url("index","uc_money");
                    ajax_return($status);
                }                 
			}
		}else{
			app_redirect(url("index","user#login"));
		}		
	}	

	public function carry_log(){
	    $GLOBALS['tmpl']->assign("page_title","提现记录");
		//输出提现订单
		$invest_type = intval($_REQUEST['invest_type']);
		$GLOBALS['tmpl']->assign("invest_type",$invest_type);
		$page_args['invest_type'] =  $invest_type;
		$invest = array(
			array(
				"name" => "存管账户",
			),
			array(
				"name" => "普通账户",
			),
		);
		foreach($invest as $k=>$v){
			$tmp_args = $page_args;
			$tmp_args['invest_type'] = $k;
			$invest[$k]['url'] = url("index","uc_money#carry_log",$tmp_args);
		}
		$GLOBALS['tmpl']->assign('invest',$invest);
		$page = intval($_REQUEST['p']);
		$cg_tag = intval($_REQUEST['cg']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		if ($invest_type == 1){
			$cg_tag = 1;
		}else{
			$cg_tag = 2;
		}	
		$result = get_user_carry($limit,$GLOBALS['user_info']['id'],$cg_tag);
		$pc_list = $result['list'];
		foreach($pc_list as $k=>$v){
			$pc_list[$k]['corpacc']= $v ?substr(str_replace(' ','',$v['corpacc']),-4) :"";
			$pc_list[$k]['bank_card']= substr(str_replace(' ','',$v['bankcard']),-4);
			$pc_list[$k]['create_time']= date('Y-m-d H:i',$v['create_time']);
		}
		$GLOBALS['tmpl']->assign("pc_list",$pc_list);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		/*******wap2.0 提现记录数据********/
		$details = $result['list'];
		$month_time_start = to_date(TIME_UTC,"m");
        foreach($details as $key=>$val){
            if(date('m',$val['create_time']) == $month_time_start){
                $create_time=date('本月 Y年',$val['create_time']);
            }else{
                $create_time=date('m月 Y年',$val['create_time']);//-->将查出的每个年月日，以年月分离出来，做为新数组的下标
            }
            $details[$key]['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $val['week']= week(date('N', $val['create_time']));
            $val['time'] = date('H:i',$val['create_time']);
            $val['create_time'] = $details[$key]['create_time'];
            $list[$create_time][]=$val; //-->将查出的每个年月日，以年月分离出来，做为新数组的下标
        }
		/*******wap2.0 提现记录模拟数据********/
		$GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_money_carry_log.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}

	//校验存管交易密码
	public function check_cg_password(){
	    $userid = $GLOBALS['user_info']['id'];
	    $id = intval($_GET['id']);
	    if($userid < 0) app_redirect(url("index","user#login"));
		if(empty($id) || $id < 0) showErr("参数错误");
		
        $Publics = new Publics();
   		$SeqNo = $Publics->seqno();
   		//记录流水号
   		$rs = $GLOBALS['db']->query("UPDATE ".DB_PREFIX."user_carry SET seqno="."'$SeqNo'"." where id=".$id);
		if($rs){
		    $html = $Publics->verify_trans_password('uc_money',"confirm_withdraw",$userid,'4',$SeqNo,"_self");
		    echo $html;
		} else{
		    showErr("系统繁忙请稍后再试");
		}
	}
	//客户校验密码成功后回调 
	public function confirm_withdraw(){
	    $result = $_REQUEST;
	    $seqno = $result['businessSeqNo'];
	    if($result['flag'] == 1){
	        //更改校验密码状态
	        $rs = $GLOBALS['db']->query("UPDATE ".DB_PREFIX."user_carry SET cunguan_pwd=1 where seqno="."'$seqno'"." and user_id=".$result['userId']);
	        $rs = $GLOBALS['db']->affected_rows();
	        $jumpurl = machineInfo();
			$GLOBALS['tmpl']->assign('jumpurl',$jumpurl);
	        if($rs){
	            require_once APP_ROOT_PATH."/system/libs/user.php";
	            $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_carry where seqno="."'$seqno'"." and user_id=".$result['userId']);
	            //更新账户资金
	            modify_account(array('cunguan_money'=>-$data['money'],'cunguan_lock_money'=>$data['money']),$data['user_id'],"存管申请提现",8,"存管申请提现",1);
// 	            modify_account(array('cunguan_money'=>-$data['fee'],'cunguan_lock_money'=>$data['fee']),$data['user_id'],"存管提现手续费",9,"存管提现手续费",1);
	            
	            //$content = "您于".to_date($data['create_time'],"Y年m月d日 H:i:s")."提交的".format_price($data['money'])."提现申请我们正在处理，如您填写的账户信息正确无误，您的资金将会于3个工作日内到达您的银行账户.";
	            $notices['site_name'] = app_conf("SHOP_TITLE");
	            $notices['user_name'] = $GLOBALS['user_info']['real_name'];
	            $notice['time']=to_date($data['create_time']);
	            $notice['money']=format_price($data['money']);
	            
	            $tmpl_content = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_WITHDRAWS_CASH'",false);
	            $GLOBALS['tmpl']->assign("notice",$notice);
	            $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content['content']);
	            
	            send_user_msg("我们正在处理您的存管提现申请，请耐心等待!",$content,0,$data['user_id'],TIME_UTC,0,true,5);
	            
	            if(WAP == 1 ){
	                $GLOBALS['tmpl']->display("page/withdraw_sucess.html");
	            }else{
	                $jump = url("index","uc_money#carry_log&invest_type=2");
	                showSuccess("存管申请提现成功，请等待审核",0,$jump);
	            }
	            
	        }else{
	            $jump = url("index","uc_money#carry");
	            showErr("系统繁忙，请稍后再试",0,$jump);
	        }
	    }
	}

	/*
	*  企业提现
	*/
	public function Enterprise_withdrawal(){ 
		$userid = $GLOBALS['user_info']['id'];
	    $id = intval($_GET['id']);
	    if($userid < 0) app_redirect(url("index","user#login"));
		if(empty($id) || $id < 0) showErr("参数错误");
		
        $Publics = new Publics();
   		$SeqNo = $Publics->seqno();
   		//记录流水号
   		$rs = $GLOBALS['db']->query("UPDATE ".DB_PREFIX."user_carry SET seqno="."'$SeqNo'"." where id=".$id);
   		$jumpurl = machineInfo();
		$GLOBALS['tmpl']->assign('jumpurl',$jumpurl);
   		if($rs){
            $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_carry where seqno="."'$SeqNo'"." and user_id=".$userid);
            //更新账户资金
            require_once APP_ROOT_PATH."/system/libs/user.php";
            modify_account(array('cunguan_money'=>-$data['money'],'cunguan_lock_money'=>$data['money']),$data['user_id'],"存管申请提现",8,"存管申请提现",1);

            $notices['site_name'] = app_conf("SHOP_TITLE");
            $notices['user_name'] = $GLOBALS['user_info']['real_name'];
            $notice['time']=to_date($data['create_time']);
            $notice['money']=format_price($data['money']);
            
            $tmpl_content = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_WITHDRAWS_CASH'",false);
            $GLOBALS['tmpl']->assign("notice",$notice);
            $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content['content']);
            
            send_user_msg("我们正在处理您的存管提现申请，请耐心等待!",$content,0,$data['user_id'],TIME_UTC,0,true,5);

            if(WAP == "1" ){
                $GLOBALS['tmpl']->display("page/withdraw_sucess.html");
            
            }else{

                $jump = url("index","uc_money#carry_log&invest_type=2");
                showSuccess("存管企业申请提现成功，请等待审核",0,$jump);
            }       
   		}else{
   		 	 $jump = url("index","uc_money#carry");
	         showErr("系统繁忙，请稍后再试",0,$jump);
   		}
	}
	
	//提现一进来就调用它
	public function ajaxWithdrawalamount(){
	    $user_id = $GLOBALS['user_info']['id'];
	    $user_type=$GLOBALS['user_info']['user_type'];
	    if(empty($user_id)){
	        ajax_return(array("status"=>0,"msg"=>"未登录"));
	    }
	    $list = get_cg_user_info($user_id,$user_type);

	    if($user_type =="0"){ 
		    $in_money = $GLOBALS['db']->getOne("SELECT sum(money) from ".DB_PREFIX."user_carry where user_id=".$user_id." and cunguan_pwd=1 and status in(0,3)");
	    	$data['withdrawalamount'] = $list['withdrawalamount'] - $in_money;
	    }else{ 
	    	$in_money = $GLOBALS['db']->getOne("SELECT sum(money) from ".DB_PREFIX."user_carry where user_id=".$user_id." and user_type=2 and status in(0,3)");
	    	$data['withdrawalamount'] = $list['withdrawalamount']-$in_money;
	    } 
	    ajax_return($data);
	}
	
    public function ajaxCarry(){
        $user_id = $GLOBALS['user_info']['id'];
        $limit = "";
        $invest_type = intval($_REQUEST['invest_type']);
		if ($invest_type == 1){
			$cg_tag = "1";
		}else{
			$cg_tag = "2";
		}	
		$result = get_user_carry($limit,$GLOBALS['user_info']['id'],$cg_tag);
        echo $result['count'];
    }

    public function carryList(){
        $user_id = $GLOBALS['user_info']['id'];
        $page = $_REQUEST['page'];
        $limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
        $invest_type = intval($_REQUEST['invest_type']);
		if ($invest_type == 1){
			$cg_tag = "1";
		}else{
			$cg_tag = "2";
		}	
		$result = get_user_carry($limit,$GLOBALS['user_info']['id'],$cg_tag);
        $details = $result['list'];
        $month_time_start = to_date(TIME_UTC,"m");
        foreach($details as $key=>$val){
            if(date('m',$val['create_time']) == $month_time_start){
                $create_time=date('本月 Y年',$val['create_time']);
            }else{
                $create_time=date('m月 Y年',$val['create_time']);//-->将查出的每个年月日，以年月分离出来，做为新数组的下标
            }
            $details[$key]['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $val['week']= week(date('N', $val['create_time']));
            $val['time'] = date('H:i',$val['create_time']);
            $val['create_time'] = $details[$key]['create_time'];
            $list[$create_time][]=$val; //-->将查出的每个年月日，以年月分离出来，做为新数组的下标
        }
        if (empty($list)) {
                echo 'false';
            }else{
                $GLOBALS['tmpl']->assign('details', $list);
                $info = $GLOBALS['tmpl']->fetch("inc/uc/moneyCarryList.html");
                echo $info;
            }
    }
	/**
	 * 撤销提现
	 */
	public function do_reback(){
		$dltid = intval($_REQUEST['dltid']);
		$GLOBALS['db']->query("UPDATE ".DB_PREFIX."user_carry SET status=4 where id=".$dltid." and status=0  and user_id = ".intval($GLOBALS['user_info']['id']));
		if($GLOBALS['db']->affected_rows()){
			require_once APP_ROOT_PATH."system/libs/user.php";
			$data = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_carry where id=".$dltid." and status=4 and user_id = ".intval($GLOBALS['user_info']['id']));
			modify_account(array('money'=>$data['money'],'lock_money'=>-$data['money']),$data['user_id'],"撤销提现,提现金额",8);
			modify_account(array('money'=>$data['fee'],'lock_money'=>-$data['fee']),$data['user_id'],"撤销提现，提现手续费",9);
			showSuccess("撤销操作成功",1);
		}
		else{
			showErr("撤销操作失败",1);
		}
	}
	/**
	 * 继续申请提现
	 */
	public function do_apply(){
		$dltid = intval($_REQUEST['dltid']);
		$data = $GLOBALS['db']->getRow("SELECT user_id,money,fee FROM ".DB_PREFIX."user_carry where id=".$dltid." and status=4 and user_id = ".intval($GLOBALS['user_info']['id']));
		if(((float)$data['money'] + (float)$data['fee'] + (float)$GLOBALS['user_info']['nmc_amount']) > (float)$GLOBALS['user_info']['money']){
			showErr("继续申请提现失败,金额不足",1);
		}
		
		$GLOBALS['db']->query("UPDATE ".DB_PREFIX."user_carry SET status=0 where id=".$dltid." and (money + fee + ".$GLOBALS['user_info']['nmc_amount'].") <= ".(float)$GLOBALS['user_info']['money']." and status=4 and user_id = ".intval($GLOBALS['user_info']['id']) );
		if($GLOBALS['db']->affected_rows()){
			require_once APP_ROOT_PATH."system/libs/user.php";
			modify_account(array('money'=>-$data['money'],'lock_money'=>$data['money']),$data['user_id'],"提现申请",8);
			modify_account(array('money'=>-$data['fee'],'lock_money'=>$data['fee']),$data['user_id'],"提现手续费",9);
			showSuccess("继续申请提现成功",1);
		}
		else{
			showErr("继续申请提现失败",1);
		}
	}

    //--------------------------------------------------------充值-------------------------------------------
    /**
     * 用户充值
     * @param int $user_id
     * @param int 普通用户user.id;
     * @param float $pTrdAmt 充值金额
     * @param string $pTrdBnkCode 选择渠道
     * @param string $user_type 快捷还是网银
     */
    public function DoDpTrade()
    {
        //$now = TIME_UTC;
        date_default_timezone_set('PRC');
        $now=time();
        $user_add_time = date("ymdhis", $now);
        if(!$_POST)
        {
            app_redirect("404.html");
            exit();
        }
        foreach($_POST as $k=>$v)
        {
            $_POST[$k] = htmlspecialchars(addslashes($v));
        }
        $user_type =intval(strim($_POST['incharge_mode'])); //快捷还是网银
        $pTrdAmt = floatval(strim($_POST['pTrdAmt']));//金钱
        $pTrdBnkCode = strim($_POST['incharge_channel']);//选择渠道
        $user_id = $GLOBALS['user_info']['id'];
        if($pTrdAmt<1){
            exit;
        }

        $rec_type=$GLOBALS['user_info']['user_type'];//1是企业 0是存管

        //$datas = get_user_info("*", "id = " . $user_id);
        //银行卡
        $bankcard = $GLOBALS['db']->getRow("SELECT bankcard FROM ".DB_PREFIX."user_bank where user_id=$user_id and cunguan_tag=1 and status=1");

        $record=array(
            'pTrdAmt'=>$pTrdAmt,
            'user_id'=>$user_id,
            'now'=>$now,
            'user_add_time'=>$user_add_time,
        );
       	
    	if($user_type){
        	$this->Yibin($user_type,$record,$bankcard,$rec_type);
        }
    //end   
    }


    public function orderIds(){
            $yCode = array('P', 'W', 'E', 'V', 'T', 'Y', 'N','A','M', 'C', 'O','Q','X','G','H','I');
            $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
            return $orderSn;
    }


   	public function Yibin($pTrdBnkCode,$record,$bankcard,$rec_type){ 

   		$Publics = new Publics();
   		$trader_id=$Publics->seqno();  //交易流水号
   		$orderId = $this->orderIds(); //订单号   		
   		$value= new Charge();//企业充值

   		if(!in_array($rec_type, array(0,1))){ 
   			$status['status'] = 1;
            $status['info'] = "充值失败请稍等";
            $status['jump'] =  url("index","uc_center#index");
            ajax_return($status);  		
        }

   		if($rec_type =="0"){

   			if(in_array($pTrdBnkCode,array(37))){ 
   				$status['status'] = 1;
                $status['info'] = "企业用户暂不可使用";
                $status['jump'] = url("index","uc_center#index");
                ajax_return($status);
   			}

   			switch($pTrdBnkCode){   
	   			case '34':
	   					$custody="银行充值";
	   					$this->rechar_notice($pTrdBnkCode,$custody,$orderId,$record,$bankcard,$trader_id);
	   					$html=$Publics->verify_trans_password('charge',"charge_pay",$record['user_id'],'4',$trader_id);					
	   					showSuccess($html,1);
	         			die;
	   				break;
	   			
	   		// 	case '35':
						// $custody="第三方支付";
	   		// 			$this->rechar_notice($pTrdBnkCode,$custody,$orderId,$record,$bankcard,$trader_id);
	   		// 			$html=$Publics->verify_trans_password('charge',"chongzhi_ceshi",$record['user_id'],'4',$trader_id); 
	   		// 			showSuccess($html,1);
	     //     			die;
	   		// 		break;

	   		// 	case '36':
	   		// 			$custody="汇元认证";
	   		// 			$this->rechar_notice($pTrdBnkCode,$custody,$orderId,$record,$bankcard,$trader_id);
	   		// 			$html=$Publics->verify_trans_password('charge',"chongzhi_kj_one_ceshi",$record['user_id'],'4',$trader_id);
	   		// 			showSuccess($html,1);
	     //     			die;
	     //     		break;

	   			case '36':
	   		// 			$corpacc = $GLOBALS['db']->getRow("SELECT username,corpacc FROM ".DB_PREFIX."company_reginfo where user_id='".$record['user_id']."'"); 
						// $bankcard=array(
						// 	'bankcard'=>$corpacc['corpacc'],
						// ); 
	   			
						$custody="宜宾网关充值";
	   					$this->rechar_notice($pTrdBnkCode,$custody,$orderId,$record,$bankcard,$trader_id,$rec_type);
						$html=$Publics->verify_trans_password('charge',"charge_wang",$record['user_id'],'4',$trader_id);
						showSuccess($html,1);
						exit;
	         		break;


	   			default:
	   				# code...
	   				break;
	   		}

   		}else{ 

   			if(in_array($pTrdBnkCode,array(34,35,36))){ 
   				$status['status'] = 1;
                $status['info'] = "存管用户暂不可使用";
                $status['jump'] = url("index","uc_center#index");
                ajax_return($status，1);
            }

   			switch ($pTrdBnkCode) {
   				case '37':					
   						$corpacc = $GLOBALS['db']->getRow("SELECT username,corpacc FROM ".DB_PREFIX."company_reginfo where user_id='".$record['user_id']."'"); 
						$bankcard=array(
							'bankcard'=>$corpacc['corpacc'],
						); 
						$custody="企业充值";
	   					$this->rechar_notice($pTrdBnkCode,$custody,$orderId,$record,$bankcard,$trader_id,$rec_type);
						$html=$value->enterprise($trader_id,$record,$orderId,$corpacc,$rec_type);
						showSuccess($html,1);
						exit;
   					break;
   				
   				default:
   					# code...
   					break;
   			}
   		}  		
   	//end	
   	}

   	public function rechar_notice($pTrdBnkCode,$custody,$orderId,$record,$bankcard,$trader_id,$rec_type){
		 $trans_data=array(
            'is_paid'=>0,
            'create_time'=>time(),
            'money'=>$record['pTrdAmt'],
            'order_id'=>$orderId,
            'seqno'=>$trader_id,
            'user_id'=>$record['user_id'],
            'outer_notice_sn'=>$custody,
            'payment_id'=>$pTrdBnkCode,
            'create_date'=>date("Y-m-d"),
            'cunguan_tag'=>1,
            'bank_id' =>$bankcard['bankcard'],
            'user_type'=>$rec_type?1:0,
        );
		$GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$trans_data,'INSERT','','SILENT');
	}


    public function test111(){
        $OldSeqNo = $_REQUEST['seqno'];
        if(empty($OldSeqNo)){
            die;
        }
        $user_id = $GLOBALS ['user_info']['id'];
        if($user_id != 502579){
            echo "非法请求";die;
        }
        $data = $GLOBALS['db']->getRow("select seqno,user_id,jctaccNo,money from ".DB_PREFIX."decository where seqno="."'$OldSeqNo'");
        
        $deal = new Deal();
        $a = $deal->rush_positive($OldSeqNo,$data['user_id'],$data['money'],$data['jctaccNo']);
        echo "<pre>";
        print_r($a);die;
    }




//end	
}
?>