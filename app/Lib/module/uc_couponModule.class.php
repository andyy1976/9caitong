<?php

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_couponModule extends SiteBaseModule
{
	public function index()
	{

		//判断是否是企业用户
        if($GLOBALS['user_info']['user_type']){
            $jump = url("index","uc_center#index");
            if(WAP==1) app_redirect(url("index","uc_center#index"));
            showErr("企业用户暂不可使用",0,$jump);
        }
        $card_type = intval($_REQUEST['card_type']);
        $GLOBALS['tmpl']->assign("card_type",$card_type);
        $page_args['card_type'] = $card_type;
        $order=" order by status asc";
        $time=time();
        $condition='';
        if ($card_type > 0) {
            if ($card_type == 1)
                $condition .= " AND ic.status = 0 AND ic.end_time >$time ";
            else if ($card_type == 2)
                $condition .= " AND ic.status = 1 ";
            else if ($card_type == 3)
                $condition .= " AND ic.status = 0 AND ic.end_time <$time";
        }
        $page_pram = "";
        foreach($page_args as $k=>$v){
            $page_pram .="&".$k."=".$v;
        }
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		if(WAP == 1){
			$limit = "0,1000";
		}else{
			$limit = (($page-1)*6).",6";
		}
		
		$result =get_uc_interest_card_list($limit,$GLOBALS['user_info']['id'],$condition);
        foreach($result['list'] as $k=>$v){
//            $result['list'][$k]['content']= mb_substr($v['content'],0,10,'utf-8')."…";
            $result['list'][$k]['begin_date']=date("Y-m-d",$v['begin_time']);
            $result['list'][$k]['end_date']=date("Y-m-d",$v['end_time']);
            if($v['interest_time']==0){
                $result['list'][$k]['interest_time_info']="全程加息";
            }else{
                $result['list'][$k]['interest_time_info']="加息".$v['interest_time']."天";
            }
        }
        if(empty($result)){
            $result['list']='';
        }
        //代金券的分类
        $voucher_deal = array(
            array(
                "name" => "全部",
            ),
            array(
                "name" => "未使用",
            ),
            array(
                "name" => "已使用",
            ),
            array(
                "name" => "已过期",
            ),
        );
        foreach($voucher_deal as $k=>$v){
            $tmp_args = $page_args;
            $tmp_args['card_type']=$k;
            $voucher_deal[$k]['url'] = url("index","uc_coupon#index",$tmp_args);  //是从20出来的   $url =$url.$k."=".urlencode($v)."&";
        }
		$page = new Page($result['count'],6,$page_pram);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('card_type',$card_type);
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('time',$time);
        $GLOBALS['tmpl']->assign('voucher_deal',$voucher_deal);
        $GLOBALS['tmpl']->assign("result",$result['list']);
        $GLOBALS['tmpl']->assign("time",$time);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_BONUS']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_coupon_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function wapindex()
	{
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		if(WAP == 1){
			$limit = "0,1000";
		}else{
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		}		
		$result = get_voucher_list($limit,$GLOBALS['user_info']['id']);
		foreach ($result['list'] as $k => $v) {
			if($v['status'] != 1 && $v['end_time'] > time()){
				$v['s_money'] = $v['money']*50;
				$list[] = $v;
			}elseif($v['status'] == 1){
				$v['s_money'] = $v['money']*50;
				$list_ago[] = $v;
			}elseif( $v['end_time'] < time()){
				$v['s_money'] = $v['money']*50;
				$list_old[] = $v;
			}
		}
		if($list == null)
		$list = 1;
		if($list_ago == null)
		$list_ago = 1;
		if($list_old == null)
		$list_old = 1;
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("list_ago",$list_ago);
		$GLOBALS['tmpl']->assign("list_old",$list_old);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$red_money = $GLOBALS['db']->getOne("select red_money from ".DB_PREFIX."user  where id = ".$GLOBALS['user_info']['id']);
//         $sql = "select e.type,e.create_time,e.money,e.account_money,e.memo,et.red_money from ".DB_PREFIX."user_red_money_log as e left join ".DB_PREFIX."user as et on e.user_id = et.id where e.user_id= ".$GLOBALS['user_info']['id'];
//         $red_money_list = $GLOBALS['db']->getAll($sql);
//         foreach($red_money_list as $v){
//             $red_total+=$v['money'];
//         }
        //红包记录
		$red_money_list = get_user_red_money_log($limit,$GLOBALS['user_info']['id'],$type=56,$condition="");
	    $details = $red_money_list['list'];
		$month_time_start = to_date(TIME_UTC,"m");
		foreach($details as $key=>$val){
			if(date('m',$val['create_time']) == $month_time_start){
				$create_time=date('本月 Y年',$val['create_time']);
			}else{
				$create_time=date('m月 Y年',$val['create_time']);//-->将查出的每个年月日，以年月分离出来，做为新数组的下标
			}
			if($val['money'] > 0){
				$val['icon'] = get_domain().'/app/Tpl/wap/images/wap2/my/icon_re_state_1.png';
			}else{
				$val['icon'] = get_domain().'/app/Tpl/wap/images/wap2/my/icon_tx_state_1.png';
			}
            $val['memo']=strim(strip_tags($val['memo']));
		    $details[$key]['create_time']=date('Y-m-d H:i:s',$val['create_time']);
            $val['money'] = intval($val['money']);
		    $val['week']= week(date('N', $val['create_time']));
		    $val['time'] = date('H:i',$val['create_time']);
		    $val['create_time'] = $details[$key]['create_time']; 			       
		    $data[$create_time][]=$val; 
		}
		foreach ($data as $k => $v) {
			$bat['month'] = $k;
			$bat['weeks'] = $v;
			$list[] = $bat;
		}
        /*********优惠券 红包使用说明**********/
		$explain['voucher_explain'] = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'voucher_explain'"));
		$explain['red_explain'] = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'red_explain'"));
		$GLOBALS['tmpl']->assign("explain",$explain);
        $GLOBALS['tmpl']->assign("total",$red_total);
		$GLOBALS['tmpl']->assign("red_money",$red_money);
        $GLOBALS['tmpl']->assign("red_money_list",$red_money_list);
        $GLOBALS['tmpl']->assign("details",$data);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_VOUCHER']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_voucher_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function exchange()
	{
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$result = get_exchange_voucher_list($limit);
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_VOUCHER']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_voucher_exchange.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function do_exchange()
	{
		$id = intval($_REQUEST['id']);
		$ecv_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where id = ".$id);
		if(!$ecv_type)
		{
			showErr($GLOBALS['lang']['INVALID_VOUCHER'],1);
		}
		else
		{
			$exchange_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ecv where ecv_type_id = ".$id." and user_id = ".intval($GLOBALS['user_info']['id']));
            if($ecv_type['exchange_limit']>0&&$exchange_count>=$ecv_type['exchange_limit'])
			{
				$msg = sprintf($GLOBALS['lang']['EXCHANGE_VOUCHER_LIMIT'],$ecv_type['exchange_limit']);
				showErr($msg,1);
			}
			elseif($ecv_type['exchange_score']>intval($GLOBALS['db']->getOne("select score from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']))))
			{
				showErr($GLOBALS['lang']['INSUFFCIENT_SCORE'],1);
			}
			else
			{
				require_once APP_ROOT_PATH."system/libs/voucher.php";
				$rs = send_voucher($ecv_type['id'],$GLOBALS['user_info']['id'],1);
				if($rs)
				{
					require_once APP_ROOT_PATH."system/libs/user.php";
					$msg = sprintf($GLOBALS['lang']['EXCHANGE_VOUCHER_USE_SCORE'],$ecv_type['name'],$ecv_type['exchange_score']);
					modify_account(array('score'=>"-".$ecv_type['exchange_score']),$GLOBALS['user_info']['id'],$msg,'22');
					showSuccess($GLOBALS['lang']['EXCHANGE_SUCCESS'],1,url('index','uc_voucher'));
				}
				else
				{
					showErr($GLOBALS['lang']['EXCHANGE_FAILED'],1);
				}
			}
		}
	}
	
	public function do_snexchange()
	{
		$sn = strim($_REQUEST['sn']);
		$ecv_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where exchange_sn = '".$sn."'");
		if(!$ecv_type)
		{
			showErr($GLOBALS['lang']['INVALID_VOUCHER'],1);
		}
		else
		{
			$exchange_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ecv where ecv_type_id = ".$ecv_type['id']." and user_id = ".intval($GLOBALS['user_info']['id']));
			if($ecv_type['exchange_limit']>0&&$exchange_count>=$ecv_type['exchange_limit'])
			{
				$msg = sprintf($GLOBALS['lang']['EXCHANGE_VOUCHER_LIMIT'],$ecv_type['exchange_limit']);
				showErr($msg,1);
			}
			else
			{
				require_once APP_ROOT_PATH."system/libs/voucher.php";
				$rs = send_voucher($ecv_type['id'],$GLOBALS['user_info']['id'],1);
				if($rs)
				{
					showSuccess($GLOBALS['lang']['EXCHANGE_SUCCESS'],1,url('index','uc_voucher'));
				}
				else
				{
					showErr($GLOBALS['lang']['EXCHANGE_FAILED'],1);
				}
			}
		}
	}

    public function log(){
        $page = intval($_REQUEST['p']);
        if($page==0)
            $page = 1;
        $limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
        $condition='';
        $result =voucher_list_log($limit,$GLOBALS['user_info']['id'],$condition);

        $GLOBALS['tmpl']->assign("result",$result['list']);
        $page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象
        $p  =  $page->show();
        $time=time();
        $GLOBALS['tmpl']->assign('time',$time);
        $GLOBALS['tmpl']->assign('pages',$p);
        $GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_voucher_log.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }
}
?>