<?php

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_voucherModule extends SiteBaseModule
{
	public function index()
	{
        $voucher_paging = intval($_REQUEST['voucher_paging']);
        $GLOBALS['tmpl']->assign("voucher_paging",$voucher_paging);
        $page_args['voucher_paging'] = $voucher_paging;
        $order=" order by status asc";
        $time=time();
        $condition='';
        if ($voucher_paging > 0) {
            if ($voucher_paging == 1)
                $condition .= " AND status = 0 AND end_time >$time ";
            else if ($voucher_paging == 2)
                $condition .= " AND status = 1 ";
            else if ($voucher_paging == 3)
                $condition .= " AND status = 0 AND end_time <$time";
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
		
		$result =voucher_list($limit,$GLOBALS['user_info']['id'],$condition);

        foreach($result['list'] as $k=>$v){
            $result['list'][$k]['content']= mb_substr($v['content'],0,10,'utf-8')."…";
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
            $tmp_args['voucher_paging']=$k;
            $voucher_deal[$k]['url'] = url("index","uc_voucher#index",$tmp_args);  //是从20出来的   $url =$url.$k."=".urlencode($v)."&";
        }
		$page = new Page($result['count'],6,$page_pram);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('voucher_paging',$voucher_paging);
		$GLOBALS['tmpl']->assign('pages',$p);
        $GLOBALS['tmpl']->assign('voucher_deal',$voucher_deal);
        $GLOBALS['tmpl']->assign("result",$result['list']);
        $GLOBALS['tmpl']->assign("time",$time);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_BONUS']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_voucher_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function wapindex()
	{
		$user_id = $GLOBALS['user_info']['id'];
		//现金红包
		$cash_red_packets = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."red_packet where user_id =".$user_id." and status = 0  and packet_type = 3 and end_time >".TIME_UTC);
		$cash_red_packets = $cash_red_packets? $cash_red_packets : '0';

		$GLOBALS['tmpl']->assign("cash_red_packets",$cash_red_packets);
		//出借红包
		$lend_red_packets = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."red_packet where user_id =".$user_id." and status = 0 and packet_type = 1 and end_time >".TIME_UTC);
		$GLOBALS['tmpl']->assign("lend_red_packets",intval($lend_red_packets));
		//加息卡
		$interest_increase = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."interest_card where user_id =".$user_id." and status = 0 and end_time >".TIME_UTC);
		$GLOBALS['tmpl']->assign("interest_increase",intval($interest_increase));
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_voucher_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}

	public function cash_red_packets()
	{
		/*****现金红包选项卡切换******/	
    	$invest_type = intval($_REQUEST['invest_type']);
		$GLOBALS['tmpl']->assign("invest_type",$invest_type);
		$page_args['invest_type'] =  $invest_type;
    	$invest = array(
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
		foreach($invest as $k=>$v){
			$tmp_args = $page_args;
			$tmp_args['invest_type'] = $k;
			$invest[$k]['url'] = url("index","uc_voucher#cash_red_packets",$tmp_args);
		}
		$GLOBALS['tmpl']->assign('invest',$invest);
		/*****优惠券选项卡切换******/
		/*分页*/
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		//红包列表
		if($invest_type == 1){
			$condition = " and r.status = 1 and r.packet_type = 3"; //已使用
		}else if($invest_type == 2){
			$condition = " and r.status = 0 and r.packet_type = 3 and r.end_time < ".TIME_UTC; //已过期
		}else{
			$condition = " and r.status = 0 and r.packet_type = 3 and r.end_time > ".TIME_UTC; //未使用
		}			
		$result =get_red_packet_list($limit,$GLOBALS['user_info']['id'],$condition);

		if(empty($result['list'])){
			$result['list'] = 0;
		}
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));
		$red_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'red_explain'"));
		$GLOBALS['tmpl']->assign("red_explain",$red_explain);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_cash_red_packets.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}

	public function lend_red_packets()
	{
		/*****出借红包选项卡切换******/	
    	$invest_type = intval($_REQUEST['invest_type']);
		$GLOBALS['tmpl']->assign("invest_type",$invest_type);
		$page_args['invest_type'] =  $invest_type;
    	$invest = array(
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
		foreach($invest as $k=>$v){
			$tmp_args = $page_args;
			$tmp_args['invest_type'] = $k;
			$invest[$k]['url'] = url("index","uc_voucher#lend_red_packets",$tmp_args);
		}
		$GLOBALS['tmpl']->assign('invest',$invest);
		/*****优惠券选项卡切换******/
		/*分页*/
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		if($invest_type == 1){
			$condition = " and r.status = 1 and r.packet_type = 1"; //已使用
		}else if($invest_type == 2){
			$condition = " and r.status = 0 and r.packet_type = 1 and r.end_time < ".TIME_UTC; //已过期
		}else{
			$condition = " and r.status = 0 and r.packet_type = 1 and r.end_time > ".TIME_UTC; //未使用
		}			
		$result =get_red_packet_list($limit,$GLOBALS['user_info']['id'],$condition);
		foreach ($result['list'] as $k => $v) {
			$result['list'][$k]['use_condition'] = str_replace(",","、",$v['use_condition']);
		}
		
		if(empty($result['list'])){
			$result['list'] = 0;
		}
		foreach ($result['list'] as $k => $v) {
			$result['list'][$k]['money'] = intval($v['money']);
		}
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));
		$red_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'red_explain'"));
		$GLOBALS['tmpl']->assign("red_explain",$red_explain);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_lend_red_packets.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function interest_increase()
	{
		/*****出借红包选项卡切换******/	
    	$invest_type = intval($_REQUEST['invest_type']);
		$GLOBALS['tmpl']->assign("invest_type",$invest_type);
		$page_args['invest_type'] =  $invest_type;
    	$invest = array(
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
		foreach($invest as $k=>$v){
			$tmp_args = $page_args;
			$tmp_args['invest_type'] = $k;
			$invest[$k]['url'] = url("index","uc_voucher#interest_increase",$tmp_args);
		}
		$GLOBALS['tmpl']->assign('invest',$invest);
		/*****优惠券选项卡切换******/
		/*分页*/
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		if($invest_type == 1){
			$condition = " and ic.status = 1"; //已使用
		}else if($invest_type == 2){
			$condition = " and ic.status = 0 and ic.end_time < ".TIME_UTC; //已过期
		}else{
			$condition = " and ic.status = 0 and ic.end_time > ".TIME_UTC; //未使用
		}			
		$result =get_interest_increase_list($limit,$GLOBALS['user_info']['id'],$condition);
		foreach ($result['list'] as $k => $v) {
			if($v['use_time'] == 0){
				$result['list'][$k]['interest_time']= "全程加息";
			}else{
				$result['list'][$k]['interest_time']= "加息".$v['use_time']."天";
			}
			
		}
		if(empty($result['list'])){
			$result['list'] = 0;
		}
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));
		$pluscard_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'pluscard_explain'"));
		$GLOBALS['tmpl']->assign("pluscard_explain",$pluscard_explain);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_interest_increase.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function ajaxCard(){
		$invest_type = intval($_REQUEST['invest_type']);
		$user_id = $GLOBALS['user_info']['id'];
		if($invest_type ==1){
			$interest_increase = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."interest_card where user_id =".$user_id." and status = 1 ");
		}elseif($invest_type==2){
			$interest_increase = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."interest_card where user_id =".$user_id." and status = 0 and end_time< ".TIME_UTC);
		}else{
			$interest_increase = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."interest_card where user_id =".$user_id." and status = 0 and end_time> ".TIME_UTC);
		}
		echo $interest_increase;
	}
	public function cardList(){
		$page = intval($_REQUEST['page']);
		$invest_type = intval($_REQUEST['invest_type']);
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			echo 'false';
		}
		if($invest_type ==1){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."interest_card where user_id =".$user_id." and status = 1 limit ".$limit);
		}elseif($invest_type==2){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."interest_card where user_id =".$user_id." and status = 0 and end_time< ".time()." limit ".$limit);
		}else{
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."interest_card where user_id =".$user_id." and status = 0 and end_time> ".time()." limit ".$limit);
		}
		if (empty($list)) {
            echo 'false';
        }else{
            $GLOBALS['tmpl']->assign('intererate_card',$list);
            $info = $GLOBALS['tmpl']->fetch("inc/uc/cardList.html");
            echo $info;
        }
	}
	public function ajaxRed(){
		$invest_type = intval($_REQUEST['invest_type']);
		$user_id = $GLOBALS['user_info']['id'];
		if($invest_type ==1){
			$red_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."red_packet where user_id =".$user_id." and status = 1 ");
		}elseif($invest_type==2){
			$red_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."red_packet where user_id =".$user_id." and status = 0 and end_time< ".TIME_UTC);
		}else{
			$red_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."red_packet where user_id =".$user_id." and status = 0 and end_time> ".TIME_UTC);
		}
		echo $red_count;
	}
	public function redList(){
		$page = intval($_REQUEST['page']);
		$invest_type = intval($_REQUEST['invest_type']);
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			echo 'false';
		}
		if($invest_type ==1){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."red_packet where user_id =".$user_id." and status = 1 and packet_type=1 limit ".$limit);
		}elseif($invest_type==2){
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."red_packet where user_id =".$user_id." and status = 0 and packet_type=1 and end_time< ".time()." limit ".$limit);
		}else{
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."red_packet where user_id =".$user_id." and status = 0 and packet_type=1 and end_time> ".time()." limit ".$limit);
		}
		if (empty($list)) {
            echo 'false';
        }else{
            $GLOBALS['tmpl']->assign('red_packet_list',$list);
            $info = $GLOBALS['tmpl']->fetch("inc/uc/redList.html");
            echo $info;
        }
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
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/cash_red.html");
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