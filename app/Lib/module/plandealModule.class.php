<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
require APP_ROOT_PATH.'app/Lib/uc_func.php';
define(ACTION_NAME,"deal");
define(MODULE_NAMEN,"index");
class plandealModule extends SiteBaseModule
{
    public function index(){

        //判断是否是企业用户
        if($GLOBALS['user_info']['user_type']){
            $jump = url("index","uc_center#index");
            if(WAP==1) app_redirect(url("index","uc_center#index"));
            showErr("企业用户暂不可使用",0,$jump);
        }
		
        $id = intval($_GET['id']);
        $deal = get_plandeal($id);
		es_session::set("deal_id",$id);
        //剩余募集天数
        if($deal['deal_status']==1) {
            $deal['surplus_enddate'] = floor($deal['remain_time']/86400)+1;
        }else{
            $deal['surplus_enddate'] = 0;
        }
        $deal['over_amount'] = $deal['borrow_amount'] - $deal['load_money'];
		if($deal['borrow_amount'] <=$deal['load_money']){
			$deal['progress_point'] ="100";
		}
		$deal['weibiao_need_money'] = $deal['borrow_amount'] - $deal['load_money'];
		$deal['weibiao_yes'] = intval(str_replace(',','',$deal['over_amount']))<intval($deal['min_loan_money'])?1:0;
        $xs = 10;
        $user_id = intval($GLOBALS['user_info']['id']);
        $GLOBALS['tmpl']->assign("user_id",$user_id);
        $deal["rate"] = sprintf("%.1f",$deal["rate"]); //统一预期年化收益格式
        $deal['ymb'] = $GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'ymb'"); 
        $deal['bank'] = $GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config where code = 'bank'");
        $ips =  $GLOBALS['db']->getRow("SELECT * FROM  ".DB_PREFIX."app_ips where code = 2" );
        if($deal['rate'] >= 8)$deal['rate_progress'] = 80;
        else $deal['rate_progress'] = $deal['rate']*$xs;
        if($deal['bank'] < 2)$deal['bank_progress'] = 20;
        else $deal['bank_progress'] = $deal['bank']*$xs;
        $deal['ymb_progress'] = $deal['ymb']*$xs;
        //==========wap端与pc端请求区分结束================
        $GLOBALS['tmpl']->assign("deal",$deal);
        $GLOBALS['tmpl']->assign("ips",$ips);

        //资产包出借列表
        //借款列表
        /*$deal_ids = $GLOBALS['db']->getAll("select id from ".DB_PREFIX."deal where plan_id=".$id);
        foreach ($deal_ids as $k => $v) {
            $deal_id.=$v['id'].",";
        }
        $load_list = $GLOBALS['db']->getAll("select d.*,u.mobile from ".DB_PREFIX."deal_load d left join ".DB_PREFIX."user u on d.user_id=u.id where deal_id in(".rtrim($deal_id,",").")");*/
        $load_list = $GLOBALS['db']->getAll("select d.*,u.mobile from ".DB_PREFIX."plan_load d left join ".DB_PREFIX."user u on d.user_id=u.id where plan_id=".$id." order by d.id desc");
        foreach ($load_list as $k => $v) {
            $load_list[$k]['create_time'] = $v['load_time'];
        }
        $GLOBALS['tmpl']->assign("load_list",$load_list);
        $GLOBALS['tmpl']->assign("money",floatval($GLOBALS['user_info']['cunguan_money']));
        //判断是否开通存管
        $userinfo = $GLOBALS['user_info'];
        $user_status = $GLOBALS['db']->getOne("select sum(status) as status from ".DB_PREFIX."user_bank  where cunguan_tag = 1 and user_id= ".$userinfo['id']);
        //存管出借 验证
        if($userinfo['cunguan_tag'] == 0){
            $ajax['code'] = 0;
            $ajax['url'] = url("index","uc_depository_account"); //判断存管是否开户
        }else if($userinfo['cunguan_tag'] == 1 && $userinfo['cunguan_pwd'] == 0){
            $ajax['code'] = 1;
            $ajax['url'] = url("index","uc_depository_paypassword#setpaypassword"); //判断存管是否设置交易密码
        }else if($userinfo['cunguan_tag'] == 1 && $userinfo['cunguan_pwd'] == 1 && $user_status < 1){
            $ajax['code'] = 1;
            $ajax['url'] = url("index","uc_depository_addbank#wap_check_pwd"); //判断存管是否设置交易密码
        }else{
            $ajax['code'] = 4;
        }
        $GLOBALS['tmpl']->assign('ajax',$ajax);
        $GLOBALS['tmpl']->display("page/plandeal.html");
    }
    public function deals(){
        $user_id = intval($GLOBALS['user_info']['id']);
        if(!$user_id)
        {
            app_redirect(url("index","user#login"));
        }
        es_session::set('deal_type',1); //1为理财计划
        /*获取session参数 输出到页面*/
        $deal_id = es_session::get('deal_id');         //出借标id
        $lend_money = es_session::get('lend_money');    //出借金额
        $GLOBALS['tmpl']->assign("deal_id",$deal_id);
        $GLOBALS['tmpl']->assign("lend_money",$lend_money);
        /*获取session参数*/
        if(es_session::get('red_id')){
            $red_id = rtrim(es_session::get('red_id'),",");
            $red_money=intval($GLOBALS['db']->getOne("select sum(money) as money from ".DB_PREFIX."red_packet where id in(".$red_id.")"));
        }
        $GLOBALS['tmpl']->assign("red_id",$red_id);
        $GLOBALS['tmpl']->assign("red_money",$red_money);
		$deal = get_plandeal($deal_id);
		$time=time();
        //获取红包张数
		$red_list=get_red_list($limit,$user_id,$plan_id,true);
        //$red_packets = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."red_packet where user_id = $user_id and packet_type < 3 and status = 0 and end_time >".time());
		$red_packets = $red_list['count'];
		if(!$red_packets){
			$red_packets =0;	
		}
        $GLOBALS['tmpl']->assign("red_packets",$red_packets);
		//获取加息卡张数
		$lc_card= get_lcinterest_card_list($limit,$user_id,$plan_id);
		$raise_interes = $lc_card['count'];
        /* $raise_interes = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."interest_card where user_id = $user_id and status = 0 and end_time >".time()); */
		if(!$raise_interes){
			$raise_interes=0;
		}
        $GLOBALS['tmpl']->assign("raise_interes",$raise_interes);
        if(es_session::get('coupon_id')){
            $coupon_id = rtrim(es_session::get('coupon_id'));
            $coupon_rate = "+".$GLOBALS['db']->getOne("select rate as money from ".DB_PREFIX."interest_card where id = $coupon_id")."%";
        }
        $GLOBALS['tmpl']->assign("coupon_id",$coupon_id);
        $GLOBALS['tmpl']->assign("coupon_rate",$coupon_rate);
        $GLOBALS['tmpl']->assign("cungaun_money",$GLOBALS['user_info']['cunguan_money']);
        $deal = get_plandeal($deal_id);//min_loan_money
        $deal['need_money'] = $deal['borrow_amount'] - $deal['load_money'];
        $GLOBALS['tmpl']->assign("deal",$deal);
        $GLOBALS['tmpl']->display("page/plan_deals.html");
    }
    public function licai_down_contract(){
        $pid = intval($_REQUEST['id']);
        if(!is_numeric($pid) || empty($pid)) {
            $this->error("参数错误");
        }
        $contract_id = 15;
        $title = "消费分期出借协议";
        $contract = $GLOBALS['tmpl']->fetch("str:".get_contract($contract_id));
        require APP_ROOT_PATH.'app/Lib/contract.php';
        $pdf = new contract();
        $file_name = $title.".pdf";
        $pdf->contractOutputByHtml($contract,$file_name,'I',$title);
    }
}
?>
