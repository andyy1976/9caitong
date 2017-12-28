<?php
require_once APP_ROOT_PATH."system/utils/Depository/Public.php";
require_once APP_ROOT_PATH."system/user_level/Level.php";


/**
 * 获取指定的投标
 */
function get_deal($id=0,$is_effect=1)
{
    $time = TIME_UTC;
    if($is_effect == 1)
    {
        $ext = " and is_effect = 1 ";
    }
    if($id==0)  //有ID时不自动获取
    {
        return false;
        /*$sql = "select id from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0  ";
            if($cate_id>0)
            {

        $ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));

        $sql .= " and cate_id in (".implode(",",$ids).")";
        }

        $sql.=" order by sort desc";
        $deal = $GLOBALS['db']->getRow($sql);
        */

    }
    else{
        $deal = $GLOBALS['db']->getRow("select *,(start_time + enddate*24*3600 - ".$time.") as remain_time from ".DB_PREFIX."deal where id = ".intval($id)."  and is_delete <> 1  $ext limit 1");
    }

    if($deal)
    {
        if($deal['deal_status']!=3 && $deal['deal_status']!=5)
        {
            $temp_data =syn_deal_status($deal['id']);
            $deal = array_merge($deal,$temp_data);
        }
        format_deal_item($deal);
    }
    return $deal;

}
/**
 * 获取理财计划信息
 */
function get_plandeal($id=0,$is_effect=1)
{
    $time = TIME_UTC;
    if($is_effect == 1)
    {
        $ext = " and is_effect = 1 ";
    }
    if($id==0)  //有ID时不自动获取
    {
        return false;
    }
    else{
        $plan = $GLOBALS['db']->getRow("select *,(start_time + enddate*24*3600 - ".$time.") as remain_time from ".DB_PREFIX."plan where id = ".intval($id)."  and is_delete <> 1  $ext  order by id desc");
    }
    if($plan)
    {
       /* if($plan['deal_status']!=3 && $plan['deal_status']!=5)
        {
            $temp_data =syn_deal_status($plan['id']);
            $plan = array_merge($plan,$temp_data);
            var_dump($plan);exit;
        }*/
        format_deal_item($plan);
    }
    return $plan;

}

/**
 * 获取正在进行的投标列表
 */
function get_deal_list($limit="",$cate_id=0, $where='',$orderby = '',$user_name='',$user_pwd='',$is_all=false)
{
    // $debts_id = $GLOBALS['db']->getAll("select d2.id from ".DB_PREFIX."deal d1 left join ".DB_PREFIX."deal d2 on d1.id = d2.old_deal_id");
    // $debts_ids = '';
    // foreach($debts_id as $kk=>$vv){
    //     $debts_ids .= $vv['id'].",";
    // }
    // $debts_ids = trim($debts_ids,',');
    // if(!isset($debts_ids) || empty($debts_ids)){
    //     $debts_ids = 0;
    // }
    $time = TIME_UTC;
    $count_sql = "select count(*) from ".DB_PREFIX."deal where type_id <>12 ";
    if($is_all==false)
        $count_sql.=" and is_effect = 1 and is_delete = 0 ";

    if(es_cookie::get("shop_sort_field")=="ulevel"){
        $extfield = ",(SELECT u.level_id FROM jctp2p_user u WHERE u.id=user_id ) as ulevel";
    }


    $sql = "select id,name,type_id,old_deal_id,debts,is_advance,publish_wait,sub_name,is_new,is_hot,is_recommend,user_id,repay_time,user_bid_rebate,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,interest_rate,deal_status,borrow_amount,start_time as last_time,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - ".$time.") as remain_time $extfield from ".DB_PREFIX."deal where  type_id <>12 ";

    if($is_all==false)
        $sql.=" and is_effect = 1 and is_delete = 0 ";

    if($cate_id>0)
    {
        $ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));
        $sql .= " and cate_id in (".implode(",",$ids).")";
        $count_sql .= " and cate_id in (".implode(",",$ids).")";
    }

    if($where != '')
    {
        $sql.=" and ".$where;
        $count_sql.=" and ".$where;
    }

    if($orderby=='')
        $sql.=" order by sort desc ";
    else
        $sql.=" order by ".$orderby;

    if($limit!=""){
        $sql .=" limit ".$limit;
    }

    $deals_count = $GLOBALS['db']->getOne($count_sql);

    if($deals_count > 0){

        if($_REQUEST['p']=="0"||$_REQUEST['p']=="1"){
            //新手标显示第一个
            $new =$GLOBALS['db']->getRow("select id,name,sub_name,is_new,is_hot,is_recommend,user_id,repay_time,user_bid_rebate,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,deal_status,borrow_amount,start_time as last_time,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - ".$time.") as remain_time from ".DB_PREFIX."deal where is_new =1 and deal_status=1 ");
            $deals = $GLOBALS['db']->getAll($sql);
            if($new){
                array_push($deals,$new); 
                $deals=array_reverse($deals);

            }

        }else{
            $deals = $GLOBALS['db']->getAll($sql);
        }

        $deals = $GLOBALS['db']->getAll($sql);
        if($deals)
        {
            foreach($deals as $k=>$deal)
            {
                //format_deal_item($deal,$user_name,$user_pwd);
                //$deals[$k] = $deal;
                if($user_name && $user_pwd){
                    $deals[$k]['url'] = url("index","deal_limit",array("id"=>$deal['id']));
                }else{
                    $deals[$k]['url'] = url("index","deal",array("id"=>$deal['id']));
                }

                $deals[$k]['now_time'] = $time;
            }
        }
    }
    else{
        $deals = array();
    }
    $server_time=$time;
    return array('list'=>$deals,'count'=>$deals_count,'server_time'=>$server_time);
}


/**
 * 获取房贷宝可以投资的标的
 */
function get_deal_listhouse($limit="",$cate_id=0, $where='',$orderby = '',$user_name='',$user_pwd='',$is_all=false)
{
    $time = TIME_UTC;
    $count_sql = "select count(*) from ".DB_PREFIX."deal where 1=1  ";
    if($is_all==false)
        $count_sql.=" and is_effect = 1 and is_delete = 0 ";

    if(es_cookie::get("shop_sort_field")=="ulevel"){
        $extfield = ",(SELECT u.level_id FROM jctp2p_user u WHERE u.id=user_id ) as ulevel";
    }


    $sql = "select id,name,type_id,old_deal_id,debts,is_advance,publish_wait,sub_name,is_new,is_hot,is_recommend,user_id,repay_time,user_bid_rebate,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,interest_rate,deal_status,borrow_amount,start_time as last_time,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - ".$time.") as remain_time $extfield from ".DB_PREFIX."deal where 1 = 1 and type_id =14 ";


    if($is_all==false)
        $sql.=" and is_effect = 1 and is_delete = 0 ";

    if($cate_id>0)
    {
        $ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));
        $sql .= " and cate_id in (".implode(",",$ids).")";
        $count_sql .= " and cate_id in (".implode(",",$ids).")";
    }

    if($where != '')
    {
        $sql.=" and ".$where;
        $count_sql.=" and ".$where;
    }

    if($orderby=='')
        $sql.=" order by sort desc ";
    else
        $sql.=" order by ".$orderby;

    if($limit!=""){
        $sql .=" limit ".$limit;
    }

    $deals_count = $GLOBALS['db']->getOne($count_sql);

    if($deals_count > 0){

        if($_REQUEST['p']=="0"||$_REQUEST['p']=="1"){
            //新手标显示第一个
            $new =$GLOBALS['db']->getRow("select id,name,sub_name,is_new,is_hot,is_recommend,user_id,repay_time,user_bid_rebate,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,deal_status,borrow_amount,start_time as last_time,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - ".$time.") as remain_time from ".DB_PREFIX."deal where is_new =1 and deal_status=1" );
            $deals = $GLOBALS['db']->getAll($sql);
            if($new){
                array_push($deals,$new);
                $deals=array_reverse($deals);

            }

        }else{
            $deals = $GLOBALS['db']->getAll($sql);
        }

        $deals = $GLOBALS['db']->getAll($sql);
        if($deals)
        {
            foreach($deals as $k=>$deal)
            {
                //format_deal_item($deal,$user_name,$user_pwd);
                //$deals[$k] = $deal;
                if($user_name && $user_pwd){
                    $deals[$k]['url'] = url("index","deal_limit",array("id"=>$deal['id']));
                }else{
                    $deals[$k]['url'] = url("index","deal",array("id"=>$deal['id']));
                }

                $deals[$k]['now_time'] = $time;
            }
        }
    }
    else{
        $deals = array();
    }
    $server_time=$time;
    return array('list'=>$deals,'count'=>$deals_count,'server_time'=>$server_time);
}



/**
 * 获取理财计划可以投资的标的
 */
function get_plandeal_list($limit="",$cate_id=0, $where='',$orderby = '',$user_name='',$user_pwd='',$is_all=false)
{
    $time = TIME_UTC;
    $count_sql = "select count(*) from ".DB_PREFIX."plan where 1=1 ";
    if($is_all==false)
        $count_sql.=" and is_effect = 1 and is_delete = 0 ";

    if(es_cookie::get("shop_sort_field")=="ulevel"){
        $extfield = ",(SELECT u.level_id FROM jctp2p_user u WHERE u.id=user_id ) as ulevel";
    }


    $sql = "select id,name,publish_wait,repay_time,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,interest_rate,deal_status,borrow_amount,start_time as last_time,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - ".$time.") as remain_time $extfield from ".DB_PREFIX."plan where 1 = 1 ";


    if($is_all==false)
        $sql.=" and is_effect = 1 and is_delete = 0 ";

    if($cate_id>0)
    {
        $ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));
        $sql .= " and cate_id in (".implode(",",$ids).")";
        $count_sql .= " and cate_id in (".implode(",",$ids).")";
    }

    if($where != '')
    {
        $sql.=" and ".$where;
        $count_sql.=" and ".$where;
    }

    if($orderby=='')
        $sql.=" order by id desc,sort desc ";
    else
        $sql.=" order by ".$orderby;

    if($limit!=""){
        $sql .=" limit ".$limit;
    }

    $deals_count = $GLOBALS['db']->getOne($count_sql);

    if($deals_count > 0){

        if($_REQUEST['p']=="0"||$_REQUEST['p']=="1"){
           $deals = $GLOBALS['db']->getAll($sql);
        }

        $deals = $GLOBALS['db']->getAll($sql);
        if($deals)
        {
            foreach($deals as $k=>$deal)
            {
                //format_deal_item($deal,$user_name,$user_pwd);
                //$deals[$k] = $deal;
                if($user_name && $user_pwd){
                    $deals[$k]['url'] = url("index","deal_limit",array("id"=>$deal['id']));
                }else{
                    $deals[$k]['url'] = url("index","plandeal",array("id"=>$deal['id']));
                }

                $deals[$k]['now_time'] = $time;
            }
        }
    }
    else{
        $deals = array();
    }
    $server_time=$time;
    return array('list'=>$deals,'count'=>$deals_count,'server_time'=>$server_time);
}

/**
 * 获取餐饮企业可以投资的标的
 */
function get_deal_listfood($limit="",$cate_id=0, $where='',$orderby = '',$user_name='',$user_pwd='',$is_all=false)
{
    $time = TIME_UTC;
    $count_sql = "select count(*) from ".DB_PREFIX."deal where 1=1  ";
    if($is_all==false)
        $count_sql.=" and is_effect = 1 and is_delete = 0 ";

    if(es_cookie::get("shop_sort_field")=="ulevel"){
        $extfield = ",(SELECT u.level_id FROM jctp2p_user u WHERE u.id=user_id ) as ulevel";
    }


    $sql = "select id,name,type_id,old_deal_id,debts,is_advance,publish_wait,sub_name,is_new,is_hot,is_recommend,user_id,repay_time,user_bid_rebate,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,interest_rate,deal_status,borrow_amount,start_time as last_time,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - ".$time.") as remain_time $extfield from ".DB_PREFIX."deal where 1 = 1 and type_id =15 ";


    if($is_all==false)
        $sql.=" and is_effect = 1 and is_delete = 0 ";

    if($cate_id>0)
    {
        $ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));
        $sql .= " and cate_id in (".implode(",",$ids).")";
        $count_sql .= " and cate_id in (".implode(",",$ids).")";
    }

    if($where != '')
    {
        $sql.=" and ".$where;
        $count_sql.=" and ".$where;
    }

    if($orderby=='')
        $sql.=" order by sort desc ";
    else
        $sql.=" order by ".$orderby;

    if($limit!=""){
        $sql .=" limit ".$limit;
    }

    $deals_count = $GLOBALS['db']->getOne($count_sql);

    if($deals_count > 0){

        if($_REQUEST['p']=="0"||$_REQUEST['p']=="1"){
			
            $deals = $GLOBALS['db']->getAll($sql);
        }

        $deals = $GLOBALS['db']->getAll($sql);
        if($deals)
        {
            foreach($deals as $k=>$deal)
            {
                //format_deal_item($deal,$user_name,$user_pwd);
                //$deals[$k] = $deal;
                if($user_name && $user_pwd){
                    $deals[$k]['url'] = url("index","deal_limit",array("id"=>$deal['id']));
                }else{
                    $deals[$k]['url'] = url("index","deal",array("id"=>$deal['id']));
                }

                $deals[$k]['now_time'] = $time;
            }
        }
    }
    else{
        $deals = array();
    }
    $server_time=$time;
    return array('list'=>$deals,'count'=>$deals_count,'server_time'=>$server_time);
}



function format_deal_item(&$deal,$user_name="",$user_pwd=""){

    //判断是否已经开始
    $deal['is_wait'] = 0;
    if($deal['start_time'] > TIME_UTC){
        $deal['is_wait'] = 1;
        $deal['remain_time'] = $deal['start_time'] - TIME_UTC;
    }
    else{
        $deal['remain_time'] = $deal['start_time'] + $deal['enddate']*24*3600 - TIME_UTC;
    }

    $deal['loantype_format'] = loantypename($deal['loantype'],1);

    //当为天的时候
    if($deal['repay_time_type'] == 0){
        $true_repay_time = 1;
    }
    else{
        $true_repay_time = $deal['repay_time'];
    }

    if(trim($deal['titlecolor']) != ''){
        $deal['color_name'] = "<span style='color:#".$deal['titlecolor']."'>".$deal['name']."</span>";
    }
    else{
        $deal['color_name'] = $deal['name'];
    }
    //格式化数据
    if($deal['apart_borrow_amount'])
        $deal['borrow_amount_format'] = format_price($deal['apart_borrow_amount']/10000)."万";
    else
        $deal['borrow_amount_format'] = format_price($deal['borrow_amount']/10000)."万";

    $deal['load_money'] = getCollWaitMoney($deal) + $deal['load_money'];
    $deal['load_money_format'] = format_price($deal['load_money']/10000)."万";

    $deal['rate_foramt'] = number_format($deal['rate'],2);

    $deal['create_time_format'] = to_date($deal['create_time'],'Y-m-d');

    //$deal['borrow_amount_format_w'] = format_price($deal['borrow_amount']/10000)."万";
    $deal['rate_foramt_w'] = number_format($deal['rate'],2)."%";

    $deal_repay_rs = deal_repay_money($deal);

    //本息还款金额
    $deal['month_repay_money'] = round($deal_repay_rs['month_repay_money'],2);

    //最后一期还款
    $deal['last_month_repay_money'] = $deal_repay_rs['last_month_repay_money'];

    $deal['month_repay_money_format'] = format_price($deal['month_repay_money']);

    //到期还本息管理费
    if($deal['repay_time_type']==1) //月标
    {
        $deal['month_manage_money'] = $deal['borrow_amount']*(float)$deal['manage_fee']/100;
        //总的多少管理费
        $deal['all_manage_money'] = $deal['month_manage_money'] * $deal["repay_time"];
    }else{ //天标
        $deal['month_manage_money'] = $deal['borrow_amount']*(float)$deal['manage_fee']*$deal["repay_time"]/100/30;
        //总的多少管理费
        $deal['all_manage_money'] = $deal['month_manage_money'];
    }


    $deal['month_manage_money_format'] = format_price($deal['month_manage_money']);


    if(is_last_repay($deal['loantype'])==1){
        $deal['true_month_repay_money'] = $deal['month_repay_money'] + $deal['all_manage_money'];
        $deal['true_last_month_repay_money'] = $deal['last_month_repay_money'] + $deal['all_manage_money'];
    }
    elseif(is_last_repay($deal['loantype'])==2){
        $deal['month_manage_money'] = $deal['month_manage_money'] *3;
        $deal['true_month_repay_money'] = $deal['month_repay_money'] + $deal['month_manage_money'];
        $deal['true_last_month_repay_money'] = $deal['last_month_repay_money'] + $deal['month_manage_money'];
    }
    else{
        $deal['true_month_repay_money'] = $deal['month_repay_money'] + $deal['month_manage_money'];
        $deal['true_last_month_repay_money'] = $deal['last_month_repay_money'] + $deal['month_manage_money'];
    }

    $deal['true_month_repay_money_format'] = format_price($deal['true_month_repay_money']);

    //还需多少钱
    $deal['need_money'] = format_price($deal['borrow_amount'] - $deal['load_money']);//计算锁定了的钱
    //百分比
    $deal['progress_point'] = $deal['load_money']/$deal['borrow_amount']*100;

    $deal['user'] = get_user("*",$deal['user_id']);

    if($deal['cate_id'] > 0){
        $deal['cate_info'] = $GLOBALS['db']->getRow("select id,name,brief,uname,icon from ".DB_PREFIX."deal_cate where id = ".$deal['cate_id']." and is_effect = 1 and is_delete = 0",false);
    }
    if($deal['type_id'] > 0){
        $deal['type_info'] = get_loantype_info($deal['type_id']);
        if($deal['deal_status'] == 0){
            $deal['need_credit'] = 0; //0不需要材料，1需要材料
            $credits = unserialize($deal['type_info']['credits']);
            $user_credit = get_user_credit_file($deal['user_id']);
            foreach($credits as $kk=>$vv){
                if($user_credit[$vv]['passed']==0){
                    $deal['need_credit'] += 1;
                }
            }

        }
    }

    if($deal['agency_id'] > 0){
        $deal['agency_info'] = get_user("*",$deal['agency_id']);
        if($deal['agency_info']['view_info']!=""){
            $deal['agency_info']['view_info_list'] = unserialize($deal['agency_info']['view_info']);
        }
    }

    if($deal['is_mortgage'] == 1){
        if($deal['mortgage_infos']!=""){
            $deal['mortgage_infos_list'] = unserialize($deal['mortgage_infos']);
        }

        if($deal['repay_time_type']==1)
            $deal['all_mortgage_fee'] = $deal['mortgage_fee'] * $deal["repay_time"];
        else
            $deal['all_mortgage_fee'] = $deal['mortgage_fee'] ;
    }
    else{
        $deal['mortgage_fee'] = 0;
        $deal['all_mortgage_fee'] = 0;
    }

    if($deal['mortgage_contract']!=""){
        $deal['mortgage_contract_list'] = unserialize($deal['mortgage_contract']);
    }

    if($deal['deal_status'] <> 1 || $deal['remain_time'] <= 0){
        $deal['remain_time_format'] = "0".$GLOBALS['lang']['DAY']."0".$GLOBALS['lang']['HOUR']."0".$GLOBALS['lang']['MIN'];
    }
    else{
        $deal['remain_time_format'] = remain_time($deal['remain_time']);
    }

    $deal['min_loan_money_format'] = format_price($deal['min_loan_money']);

    if($deal['uloadtype'] == 1){
        if($deal['buy_count'] == 0)
            $deal['buy_portion'] = 0;
        else
            $deal['buy_portion'] = intval($deal['load_money']/$deal['min_loan_money']);

        $deal['need_portion'] = intval(($deal['borrow_amount'] - $deal['load_money'] ) / $deal['min_loan_money']);
    }

    if($deal['deal_status']>=4){

        //总的必须还多少本息
        $deal['remain_repay_money'] = $deal_repay_rs['remain_repay_money'];

        //还有多少需要还
        $deal['need_remain_repay_money'] = floatval($deal['remain_repay_money']) - floatval($deal['repay_money']);

        //还款进度条
        if($deal['remain_repay_money'] > 0)
        {
            $deal['repay_progress_point'] =  $deal['repay_money']/$deal['remain_repay_money']*100;
            if($deal['deal_status'] == 5){
                $deal['progress_point'] = 100;
            }else{
                $deal['progress_point'] = $deal['repay_progress_point'];
            }
        }else
        {
            $deal['repay_progress_point'] =  0;
        }




        //最后的还款日期
        if($deal['repay_time_type'] == 0)
            $deal["end_repay_time"] =  $deal['repay_start_time'] + $deal['repay_time']*24*3600;
        else
            $deal["end_repay_time"] =  next_replay_month($deal['repay_start_time'],$true_repay_time);

        if($deal['deal_status']==4){

            $has_repay_money = $GLOBALS['db']->getOne("SELECT sum(true_repay_money + impose_money + true_repay_manage_money + repay_manage_impose_money) FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$deal['id']." AND repay_time=".$deal['next_repay_time']);

            $deal['true_month_repay_money'] = $deal['true_month_repay_money'] - floatval($has_repay_money);
            $deal['true_last_month_repay_money'] = $deal['true_last_month_repay_money'] - floatval($has_repay_money);

            $deal["next_repay_time_format"] = to_date($deal['next_repay_time'],'Y-m-d');

            if(to_date($deal["end_repay_time"],"Ymd") < to_date(TIME_UTC,"Ymd")){
                $deal['exceed_the_time'] = true;
            }

            //罚息
            $is_check_impose = true;
            //到期还本息 只有最后一个月后才算罚息
            if($deal_repay_rs['is_check_impose'] == true){
                //算出到期还本息的最后一个月是否小于今天
                if($deal['exceed_the_time']){
                    $is_check_impose = true;
                }
                else{
                    $is_check_impose = false;
                }
            }
            if($deal["next_repay_time"] - TIME_UTC < 0 && $is_check_impose){
                //晚多少天
                $time_span = to_timespan(to_date(TIME_UTC,"Y-m-d"),"Y-m-d");
                $next_time_span = to_timespan(to_date($deal['next_repay_time'],"Y-m-d"),"Y-m-d");
                $day  = ceil(($time_span-$next_time_span)/24/3600);

                $impose_fee = trim($deal['impose_fee_day1']);
                $manage_impose_fee = trim($deal['manage_impose_fee_day1']);
                //判断是否严重逾期
                if($day >= app_conf('YZ_IMPSE_DAY')){
                    $impose_fee = trim($deal['impose_fee_day2']);
                    $manage_impose_fee = trim($deal['manage_impose_fee_day2']);
                }

                $impose_fee = floatval($impose_fee);
                $manage_impose_fee = floatval($manage_impose_fee);

                //罚息
                if((int)$deal['next_repay_time'] == (int)$deal['end_repay_time']){
                    $deal['impose_money'] = $deal['last_month_repay_money']*$impose_fee*$day/100;
                    $deal['manage_impose_money'] = $deal['last_month_repay_money']*$manage_impose_fee*$day/100;
                }
                else{
                    $deal['impose_money'] = $deal['month_repay_money']*$impose_fee*$day/100;
                    //罚管理费
                    $deal['manage_impose_money'] = $deal['month_repay_money']*$manage_impose_fee*$day/100;
                }
                $deal['impose_money'] += $deal['manage_impose_money'];
            }
        }
    }

    if($deal['publish_wait'] == 1 || $deal['publish_wait'] == 0){
        $deal['publish_time_format'] = to_date($deal['create_time'],'Y-m-d H:i');
    }else{
        $deal['publish_time_format'] = to_date($deal['start_time'],'Y-m-d H:i');
    }

    if($GLOBALS['request']['from'] == "wap")
    {
        $durl = url_wap("index","deal",array("id"=>$deal['id']));
    }
    else
    {
        $durl = url("index","deal",array("id"=>$deal['id']));
    }
    $deal['share_url'] = SITE_DOMAIN.APP_ROOT.$durl;
    if($GLOBALS['user_info'])
    {
        if(app_conf("URL_MODEL")==0)
        {
            $deal['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
        }
        else
        {
            $deal['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
        }
    }

    $deal['url'] = $durl;
    if (!empty($user_name) && !empty($user_pwd)){
        $durl = "/index.php?ctl=uc_deal&act=mrefdetail&is_sj=1&id=".$deal['id']."&user_name=".$user_name."&user_pwd=".$user_pwd;
    }else{
        $durl =wap_url("index","deal_mobile",array("id"=>$deal['id']));
    }

    $deal['app_url'] = str_replace("/mapi", "", SITE_DOMAIN.APP_ROOT.$durl);
}

/**
 * 还款列表
 */
function get_deal_load_list($deal){

    //当为天的时候
    if($deal['repay_time_type'] == 0){
        $true_repay_time = 1;
    }
    else{
        $true_repay_time = $deal['repay_time'];
    }


    $deal_repay_list = $GLOBALS['db']->getAll("SELECT *,l_key+1 as l_key_index FROM ".DB_PREFIX."deal_repay where deal_id=".$deal['id']." order by l_key ASC ");

    $tmp_has_repay_money = $GLOBALS['db']->getAll("SELECT repay_id,sum(true_repay_money + impose_money + true_repay_manage_money + repay_manage_impose_money) as has_repay_money FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$deal['id']." GROUP BY repay_id");
    $has_repay_money = array();
    foreach($tmp_has_repay_money as $kk=>$vv){
        $has_repay_money[$vv['repay_id']] = $vv['has_repay_money'];
    }
    unset($tmp_has_repay_money);

    foreach($deal_repay_list as $k=>$v){

        $i = $v['l_key'];
        $loan_list[$i]['get_manage'] = $v['get_manage'];
        $loan_list[$i]['l_key'] = $v['l_key'];
        $loan_list[$i]['l_key_index'] = $v['l_key_index'];
        $loan_list[$i]['repay_id'] = $v['id'];
        $loan_list[$i]['impose_day'] = 0;
        /**
         * status 1提前,2准时还款，3逾期还款 4严重逾期 5部分还款 6还款中
         */
        if($v['has_repay'] == 2){
            $loan_list[$i]['status'] = 5;
        }
        elseif($v['has_repay'] == 3){
            $loan_list[$i]['status'] = 6;
        }

        $loan_list[$i]['repay_day'] = $v['repay_time'];

        //月还本息
        $loan_list[$i]['month_repay_money'] = $v['repay_money'];
        //判断是否已经还完
        $loan_list[$i]['true_repay_time'] = $v['true_repay_time'];
        //管理费
        $loan_list[$i]['month_manage_money'] = $v['manage_money'] - $v['true_manage_money'];
        //抵押物管理费
        $loan_list[$i]['mortgage_fee'] = $v['mortgage_fee'] - $v['true_mortgage_fee'];

        //返佣
        $loan_list[$i]['manage_money_rebate'] = (float)$v['manage_money_rebate'];

        //has_repay：1：已还款;0:未还款
        $loan_list[$i]['has_repay'] = $v['has_repay'];

        //已还多少
        $loan_list[$i]['month_has_repay_money'] = 0;

        //总罚息 =  罚息管理费 + 逾期管理费
        $loan_list[$i]['impose_all_money'] = 0;
        if($v['has_repay'] == 1){
            $loan_list[$i]['month_has_repay_money'] = $v['true_repay_money'];
            $loan_list[$i]['month_manage_money'] = $v['true_manage_money'];
            //返佣
            $loan_list[$i]['manage_money_rebate'] = (float)$v['true_manage_money_rebate'];

            $loan_list[$i]['status'] = $v['status']+1;

            $loan_list[$i]['month_repay_money'] =0;

            $loan_list[$i]['mortgage_fee'] = $v['true_mortgage_fee'];

            //逾期罚息
            $loan_list[$i]['impose_money'] = $v['impose_money'];

            //逾期管理费
            $loan_list[$i]['manage_impose_money'] = $v['manage_impose_money'];

            //真实还多少
            $loan_list[$i]['month_has_repay_money_all'] = $loan_list[$i]['month_has_repay_money'] + $loan_list[$i]['month_manage_money']+$loan_list[$i]['impose_money']+$loan_list[$i]['manage_impose_money'] + $loan_list[$i]['mortgage_fee'];

            //总的必须还多少
            $loan_list[$i]['month_need_all_repay_money'] = 0;

            $loan_list[$i]['impose_all_money'] = $loan_list[$i]['impose_money'] + $loan_list[$i]['manage_impose_money'];

        }
        elseif($v['has_repay'] == 0){
            //判断是否罚息
            if(TIME_UTC > ($v['repay_time']+ 24*3600 -1)&& $loan_list[$i]['month_repay_money'] > 0){
                //晚多少天
                $loan_list[$i]['status'] = 3;
                $time_span = to_timespan(to_date(TIME_UTC,"Y-m-d"),"Y-m-d");
                $next_time_span = $v['repay_time'];
                $day  = ceil(($time_span-$next_time_span)/24/3600);

                $loan_list[$i]['impose_day'] = $day;

                $impose_fee = trim($deal['impose_fee_day1']);
                $manage_impose_fee = trim($deal['manage_impose_fee_day1']);
                //严重逾期费率
                if($day >= app_conf('YZ_IMPSE_DAY')){
                    $loan_list[$i]['status'] = 4;
                    $impose_fee = trim($deal['impose_fee_day2']);
                    $manage_impose_fee = trim($deal['manage_impose_fee_day2']);
                }

                $impose_fee = floatval($impose_fee);
                $manage_impose_fee = floatval($manage_impose_fee);

                //罚息
                $loan_list[$i]['impose_money'] = $loan_list[$i]['month_repay_money']*$impose_fee*$day/100;

                //罚管理费
                $loan_list[$i]['manage_impose_money'] = $loan_list[$i]['month_repay_money']*$manage_impose_fee*$day/100;
                $loan_list[$i]['impose_all_money'] = $loan_list[$i]['impose_money'] + $loan_list[$i]['manage_impose_money'];
            }
            /*elseif(to_date(TIME_UTC,"Y-m-d") == to_date($v['repay_time'],"Y-m-d") || (((int)$v['repay_time'] - TIME_UTC)/24/3600 <=3 && ((int)$v['repay_time'] - TIME_UTC)/24/3600 >=0)){
                $loan_list[$i]['status'] =  2;
            }
            else{
                $loan_list[$i]['status'] =  1;
            }*/
            else{
                //判断是否罚息
                if(TIME_UTC > ($v['repay_time']+ 24*3600 -1)&& $loan_list[$i]['month_repay_money'] > 0){
                    //晚多少天
                    $loan_list[$i]['status'] = 3;
                    $time_span = to_timespan(to_date(TIME_UTC,"Y-m-d"),"Y-m-d");
                    $next_time_span = $v['repay_time'];
                    $day  = ceil(($time_span-$next_time_span)/24/3600);

                    $loan_list[$i]['impose_day'] = $day;

                    $impose_fee = trim($deal['impose_fee_day1']);
                    $manage_impose_fee = trim($deal['manage_impose_fee_day1']);
                    //严重逾期费率
                    if($day >= app_conf('YZ_IMPSE_DAY')){
                        $loan_list[$i]['status'] = 4;
                        $impose_fee = trim($deal['impose_fee_day2']);
                        $manage_impose_fee = trim($deal['manage_impose_fee_day2']);
                    }

                    $impose_fee = floatval($impose_fee);
                    $manage_impose_fee = floatval($manage_impose_fee);

                    //罚息
                    $loan_list[$i]['impose_money'] = $loan_list[$i]['month_repay_money']*$impose_fee*$day/100;

                    //罚管理费
                    $loan_list[$i]['manage_impose_money'] = $loan_list[$i]['month_repay_money']*$manage_impose_fee*$day/100;
                    $loan_list[$i]['impose_all_money'] = $loan_list[$i]['impose_money'] + $loan_list[$i]['manage_impose_money'];
                }
                $loan_list[$i]['status'] =  2;
            }

            //真实还多少
            $loan_list[$i]['month_has_repay_money_all'] = $has_repay_money[$v['id']];

            //总的必须还多少
            $loan_list[$i]['month_need_all_repay_money'] =  $loan_list[$i]['month_repay_money'] + $loan_list[$i]['month_manage_money'] + $loan_list[$i]['impose_money'] + $loan_list[$i]['manage_impose_money'] + $loan_list[$i]['mortgage_fee'];
        }
        elseif($v['has_repay'] == 2){
            //判断是否罚息
            $ss_repay_info = $GLOBALS['db']->getRow("SELECT sum(repay_money) as month_repay_money,sum(repay_manage_money) as month_manage_money FROM ".DB_PREFIX."deal_load_repay WHERE l_key =".$i." and deal_id=".$deal['id']." and has_repay=0 ");

            $tmp_month_repay_money = $loan_list[$i]['month_repay_money'];
            $loan_list[$i]['month_repay_money'] = $ss_repay_info['month_repay_money'];
            $loan_list[$i]['month_manage_money']= $ss_repay_info['month_manage_money'];
            if(TIME_UTC > ($v['repay_time']+ 24*3600 -1)&& $loan_list[$i]['month_repay_money'] > 0){
                $loan_list[$i]['status'] = 3;
                //晚多少天
                $time_span = to_timespan(to_date(TIME_UTC,"Y-m-d"),"Y-m-d");
                $next_time_span = $v['repay_time'];
                $day  = ceil(($time_span-$next_time_span)/24/3600);

                $loan_list[$i]['impose_day'] = $day;

                $impose_fee = trim($deal['impose_fee_day1']);
                $manage_impose_fee = trim($deal['manage_impose_fee_day1']);
                //严重逾期费率
                if($day >= app_conf('YZ_IMPSE_DAY')){
                    $loan_list[$i]['status'] = 4;
                    $impose_fee = trim($deal['impose_fee_day2']);
                    $manage_impose_fee = trim($deal['manage_impose_fee_day2']);
                }

                $impose_fee = floatval($impose_fee);
                $manage_impose_fee = floatval($manage_impose_fee);

                //罚息
                $loan_list[$i]['impose_money'] = $loan_list[$i]['month_repay_money']*$impose_fee*$day/100;


                //罚管理费
                $loan_list[$i]['manage_impose_money'] = $loan_list[$i]['month_repay_money']*$manage_impose_fee*$day/100;
                $loan_list[$i]['impose_all_money'] = $loan_list[$i]['impose_money'] + $loan_list[$i]['manage_impose_money'];
            }
            /*elseif(to_date(TIME_UTC,"Y-m-d") == to_date($v['repay_time'],"Y-m-d") || (((int)$v['repay_time'] - TIME_UTC)/24/3600 <=3 && ((int)$v['repay_time'] - TIME_UTC)/24/3600 >=0)){

                $loan_list[$i]['status'] =  2;
            }
            elseif(round($tmp_month_repay_money) <= $loan_list[$i]['month_repay_money']){
                $loan_list[$i]['has_repay'] =  0;
            }
            else{
                $loan_list[$i]['status'] =  1;
            }*/
            else{
                $loan_list[$i]['status'] =  2;
            }
            $loan_list[$i]['has_repay'] =  0;

            //真实还多少
            $loan_list[$i]['month_has_repay_money_all'] = $has_repay_money[$v['id']];

            //总的必须还多少
            $loan_list[$i]['month_need_all_repay_money'] =  $loan_list[$i]['month_repay_money'] + $loan_list[$i]['month_manage_money'] + $loan_list[$i]['impose_money'] + $loan_list[$i]['manage_impose_money'] + $loan_list[$i]['mortgage_fee'];
        }

        //还款日
        $loan_list[$i]['repay_day_format'] = to_date($loan_list[$i]['repay_day'],'Y-m-d');
        //已还金额
        $loan_list[$i]['month_has_repay_money_all_format'] = format_price($loan_list[$i]['month_has_repay_money_all']);
        //待还金额
        $loan_list[$i]['month_need_all_repay_money_format'] = format_price($loan_list[$i]['month_need_all_repay_money']);

        //待还本息
        $loan_list[$i]['month_repay_money_format'] = format_price($loan_list[$i]['month_repay_money']);
        //借款管理费
        $loan_list[$i]['month_manage_money_format'] = format_price($loan_list[$i]['month_manage_money']);
        //抵押物管理费
        $loan_list[$i]['mortgage_fee_format'] = format_price($loan_list[$i]['mortgage_fee']);
        //返佣
        $loan_list[$i]['manage_money_rebate_format'] = format_price($loan_list[$i]['manage_money_rebate']);

        //借款管理费
        $loan_list[$i]['manage_money_impose_format'] = format_price($loan_list[$i]['manage_impose_money']);

        //逾期费用
        $loan_list[$i]['impose_money_format'] = format_price($loan_list[$i]['impose_money']);

        //逾期、违约金
        $loan_list[$i]['impose_all_money_format'] = format_price($loan_list[$i]['impose_all_money']);

        //状态
        if($loan_list[$i]['has_repay'] == 0){
            $loan_list[$i]['status_format'] = '待还';
        }
        elseif($loan_list[$i]['status'] == 1){
            $loan_list[$i]['status_format'] = '提前还款';
        }elseif($loan_list[$i]['status'] == 2){
            $loan_list[$i]['status_format'] = '正常还款';
        }elseif($loan_list[$i]['status'] == 3){
            $loan_list[$i]['status_format'] = '逾期还款';
        }elseif($loan_list[$i]['status'] == 4){
            $loan_list[$i]['status_format'] = '严重逾期';
        }elseif($loan_list[$i]['status'] == 5){
            $loan_list[$i]['status_format'] = '部分还款';
        }
        elseif($loan_list[$i]['status'] == 6){
            $loan_list[$i]['status_format'] = '还款中';
        }


    }


    return $loan_list;
}


/**
 * 获取某一期的用户还款列表
 * array $deal_info 借款信息
 * int $user_id 用户ID 为0代表全部
 * int $lkey  第几期 -1 全部
 * int $ukey 第几个投标人 -1 全部
 * int $true_time  真实还款时间
 * int $get_type  0 全部 1代表未还的  2 代表已还的
 * int $r_type = 0; 返回类型; 1:只返回一个数组; $result['item']
 * string $limit; 查询限制数量; 0,20  $result['count']
 *
 *
 * 还款 时（has_repay = 0)
 *
 * “平台”收取的费用
 * 	：借款者 的管理费 + 管理逾期罚息  $v['repay_manage_money'] + $v['repay_manage_impose_money'];
 * 	：出借人 的(本金)管理费 + 利息管理费  $v['manage_money'] + $v['manage_interest_money'];
 *
 * “出借人” 收取的费用
 * 	：本金 + 利息 + 罚息 - (本金)管理费 - 利息管理费
 * 		$v['self_money'] + $v['interest_money'] + $v['impose_money'] - $v['manage_money'] - $v['manage_interest_money']
 * 	：奖励 （奖励费用，由平台支出）
 * 		$v['reward_money']
 * 	注：$item['month_repay_money'] = $v['repay_money'] = $v['self_money'] + $v['interest_money']
 *
 *
 * “借款者” 需要支出的费用 = 借款者 的管理费 + 管理逾期罚息 + (本金 + 利息 + 罚息)
 * 	 $v['repay_manage_money'] + $v['repay_manage_impose_money'] + $v['self_money'] + $v['interest_money'] + $v['impose_money']
 *
 *
 * “平台” 需要支付
 * 	支付给“出借人”的奖励 $v['reward_money']
 * 	支付给“推荐人”的  [“出借人”利息管理费返利 ] $v['manage_interest_money_rebate']
 *
 *  支付给“推荐人”的 [“借款者”管理费返利 ] deal_repay.manage_money_rebate 需要本期所有的还完后，才计算
 */

function get_deal_user_load_list($deal_info, $user_id = 0 ,$lkey = -1 , $ukey = -1,$true_time=0,$get_type = 0, $r_type = 0, $limit = ""){
    if(!$deal_info){
        return false;
    }

    $result = array();

    if($get_type > 0){
        if($get_type==1)
            $extW = " AND dlr.has_repay = 0 ";
        else
            $extW = " AND dlr.has_repay = 1 ";
    }

    if($user_id > 0){
        $extW .= " AND ((dlr.user_id =  ".$user_id." and dlr.t_user_id = 0 ) or dlr.t_user_id = ".$user_id.")";
    }

    if($lkey >= 0){
        $extW .= " AND dlr.l_key =  ".$lkey;
    }

    if (!empty($limit)){
        $limit = " limit ".$limit;

        $sql = "SELECT count(*) FROM ".DB_PREFIX."deal_load_repay dlr ".
            " WHERE dlr.deal_id=".$deal_info['id']." $extW";

        $count = $GLOBALS['db']->getOne($sql);
        $result['count'] = $count;
    }


    $sql = "SELECT dlr.*,dl.create_time,dl.interestrate_id,dl.pMerBillNo,dl.money,dl.is_winning,dl.income_type,income_value,u.ips_acct_no,AES_DECRYPT(u.mobile_encrypt,'".AES_DECRYPT_KEY."') AS mobile,AES_DECRYPT(u.email_encrypt,'".AES_DECRYPT_KEY."') AS email,u.user_name,tu.ips_acct_no as t_ips_acct_no,tu.id as t_user_id,tu.user_name as t_user_name,AES_DECRYPT(tu.mobile_encrypt ,'".AES_DECRYPT_KEY."') AS t_mobile,AES_DECRYPT(tu.email_encrypt ,'".AES_DECRYPT_KEY."') AS t_email,dl.learn_id,dl.learn_money,dl.back_learn_money  FROM ".DB_PREFIX."deal_load_repay dlr ".

        " LEFT JOIN ".DB_PREFIX."deal_load dl ON dl.id =dlr.load_id  ".
        " LEFT OUTER JOIN ".DB_PREFIX."user u ON u.id = dlr.user_id ".
        " LEFT OUTER JOIN ".DB_PREFIX."deal_load_transfer dlt ON dlt.load_id = dl.id and dlt.near_repay_time <=dlr.repay_time ".
        " LEFT OUTER JOIN ".DB_PREFIX."user tu ON tu.id = dlt.t_user_id ".
        " WHERE dlr.deal_id=".$deal_info['id']." $extW ORDER BY dlr.l_key ASC,dlr.u_key ASC ".$limit;

    $load_users = $GLOBALS['db']->getAll($sql);

    if($true_time == 0)
        $true_time = TIME_UTC;



    $loan_list = array();
    foreach($load_users as $k=>$v){

        $item = array();

        //deal_load_repay 编号
        $item['id'] = $v['id'];

        $item['learn_id'] = $v['learn_id'];
        $item['load_id'] = $v['load_id'];
        $item['learn_money'] = $v['learn_money'];
        $item['back_learn_money'] = $v['back_learn_money'];
        $item['create_time'] = $v['create_time'];

        //status 1提前,2准时还款，3逾期还款 4严重逾期 数据库里的参数 + 1
        if($v['has_repay'] == 1){
            $item['status'] = $v['status'] +1;
        }

        //实际投标金额
        $item['money'] = $v['money'];

        //还款日
        $item['repay_day'] = $v['repay_time'];

        //实际还款日
        $item['true_repay_time'] = $v['true_repay_time'];

        //月还本息
        $item['month_repay_money']= $v['true_repay_money'];

        //募集期利息
        $item['raise_money']= $v['raise_money'];

        //当前期本金
        $item['self_money'] = $v['true_self_money'];

        //罚息
        $item['impose_money'] =$v['impose_money'];


        $item['interest_money'] = $v['true_interest_money'];

        //加息券
        $item['interestrate_money'] = $v['interestrate_money'];
        $item['interestrate_id'] = $v['interestrate_id'];

        //投标者信息
        $item['user_id'] =$v['user_id'];
        $item['user_name'] =$v['user_name'];
        $item['email'] =$v['email'];
        $item['mobile'] =$v['mobile'];
        $item['ips_acct_no'] =$v['ips_acct_no'];

        //承接者信息
        $item['t_user_id'] =$v['t_user_id'];
        $item['t_user_name'] =$v['t_user_name'];
        $item['t_ips_acct_no'] =$v['t_ips_acct_no'];
        $item['t_email'] =$v['t_email'];
        $item['t_mobile'] =$v['t_mobile'];

        //管理费
        $item['manage_money'] =$v['true_manage_money'];

        //利息管理费
        $item['manage_interest_money'] =$v['true_manage_interest_money'];

        //借款者均摊下来的管理费
        $item['repay_manage_money'] =$v['repay_manage_money'];
        //借款者均摊下来的抵押物管理费
        $item['mortgage_fee'] =$v['mortgage_fee'];

        //是否还款 0未还 1已还
        $item['has_repay'] =$v['has_repay'];

        //对应deal_repay的编号
        $item['repay_id'] =$v['repay_id'];
        //投标编号 对应 deal_load 的编号
        $item['load_id'] =$v['load_id'];
        //第几期
        $item['l_key'] =$v['l_key'];
        $item['l_key_index'] =$v['l_key']+1;


        //对应借款的第几个投标人
        $item['u_key'] =$v['u_key'];
        //登记债权人时提 交的订单号
        $item['pMerBillNo'] =$v['pMerBillNo'];
        //逾期借入者管理费罚息
        $item['repay_manage_impose_money'] = $v['repay_manage_impose_money'];
        //返佣
        $item['manage_interest_money_rebate'] = $v['true_manage_interest_money_rebate'];

        $item['true_manage_interest_money_rebate'] = $v['true_manage_interest_money_rebate'];

        $item['t_pMerBillNo'] = $v['t_pMerBillNo'];

        if($v['has_repay'] == 0){
            //月还本息
            $item['month_repay_money']= $v['repay_money'];
            //管理费
            $item['manage_money'] =$v['manage_money'];
            //利息管理费
            $item['manage_interest_money'] =$v['manage_interest_money'];
            $item['repay_manage_money'] = $v['repay_manage_money'];
            $item['self_money'] = $v['self_money'];
            $item['interest_money'] = $v['interest_money'];
            $item['repay_manage_impose_money'] = $v['repay_manage_impose_money'];
            //返佣
            $item['manage_interest_money_rebate'] = $v['manage_interest_money_rebate'];

            $item['month_has_repay_money'] = 0;
            if($true_time > ($v['repay_time'] + 24*3600 -1 ) && $item['month_repay_money'] > 0){
                $time_span = to_timespan(to_date($true_time,"Y-m-d"),"Y-m-d");
                $next_time_span = $v['repay_time'];
                $item['impose_day'] = $day  = ceil(($time_span-$next_time_span)/24/3600);


                if($day >0){
                    //普通逾期
                    $item['status'] = 3;
                    $impose_fee = trim($deal_info['impose_fee_day1']);
                    $manage_impose_fee = trim($deal_info['manage_impose_fee_day1']);
                    if($day >= app_conf('YZ_IMPSE_DAY')){//严重逾期
                        $impose_fee = trim($deal_info['impose_fee_day2']);
                        $manage_impose_fee = trim($deal_info['manage_impose_fee_day2']);
                        $item['status'] = 4;
                    }

                    $impose_fee = floatval($impose_fee);

                    //罚息
                    $item['impose_money'] = $item['month_repay_money'] *$impose_fee*$day/100;

                    $item['repay_manage_impose_money'] = $item['month_repay_money']*$manage_impose_fee*$day/100;
                }

            }
            /*elseif(to_date($true_time,"Y-m-d") == to_date($v['repay_time'],"Y-m-d")  || (((int)$v['repay_time'] - $true_time)/24/3600 <=3 && ((int)$v['repay_time'] - $true_time)/24/3600 >=0)){
                $item['status'] = 2;
            }
            else{
                $item['status'] = 1;
            }*/
            else{
                $item['status'] = 2;
            }
            $item['month_has_repay_money'] = 0;
            $item['month_has_repay_money_all'] = 0;
        }
        elseif($v['has_repay'] == 2){
            //月还本息
            $item['month_repay_money']= $v['repay_money'];
            //管理费
            $item['manage_money'] =$v['manage_money'];
            //利息管理费
            $item['manage_interest_money'] =$v['manage_interest_money'];
            $item['repay_manage_money'] =$v['repay_manage_money'];
            $item['self_money'] = $v['self_money'];
            $item['interest_money'] = $v['interest_money'];
            $item['repay_manage_impose_money'] = $v['repay_manage_impose_money'];
            //返佣
            $item['manage_interest_money_rebate'] = $v['manage_interest_money_rebate'];
            $item['month_has_repay_money'] = 0;
            $item['month_has_repay_money_all'] = 0;
        }
        else{
            $item['month_has_repay_money'] = $item['month_repay_money'];
            $item['month_has_repay_money_all'] = $item['month_repay_money'] + $item['month_manage_money']+$item['impose_money'];
        }

        $item['expect_earnings'] = $v['interest_money'] - $v['manage_money'] - $v['manage_interest_money'] + $v['reward_money'];
        if($item['has_repay']==1){
            $item['true_earnings'] = $v['true_interest_money'] + $item['impose_money'] - $v['true_manage_money'] - $v['true_manage_interest_money'] + $v['true_reward_money'];
        }
        else{
            $item['expect_earnings'] += $item['impose_money'];
            $item['true_earnings'] = 0;
        }

        $item['repay_day_format'] = to_date($item['repay_day'],"Y-m-d");
        $item['true_repay_time_format'] = to_date($item['true_repay_time']);
        $item['true_repay_day_format'] = to_date($item['true_repay_time'],"Y-m-d");
        $item['manage_money_format'] = format_price($item['manage_money']);
        $item['manage_interest_money_format'] = format_price($item['manage_interest_money']);
        $item['impose_money_format'] = format_price($item['impose_money']);
        $item['repay_manage_impose_money_format'] = format_price($item['repay_manage_impose_money']);
        $item['manage_interest_money_rebate_format'] = format_price($item['manage_interest_money_rebate']);
        $item['month_repay_money_format'] = format_price($item['month_repay_money']);
        $item['month_has_repay_money_format'] = format_price($item['month_has_repay_money']);
        $item['month_has_repay_money_all_format'] = format_price($item['month_has_repay_money_all']);
        //状态
        if($item['has_repay'] == 0){
            $item['status_format'] = '待还';
        }elseif($item['status'] == 1){
            $item['status_format'] = '提前还款';
        }elseif($item['status'] == 2){
            $item['status_format'] = '正常还款';
        }elseif($item['status'] == 3){
            $item['status_format'] = '逾期还款';
        }elseif($item['status'] == 4){
            $item['status_format'] = '严重逾期';
        }

        $item['site_repay_format'] = "";
        if($v['has_repay']==1){
            if($v['is_site_repay'] == 0){
                $item['site_repay_format'] = "会员";
            }
            elseif($v['is_site_repay'] == 1){
                $item['site_repay_format'] = "网站";
            }
            elseif($v['is_site_repay'] == 2){
                $item['site_repay_format'] = "机构";
            }
        }


        if ($r_type == 0){
            if($lkey >= 0){
                if($lkey == $item['l_key']){
                    $loan_list[$item['u_key']][$item['l_key']] = $item;
                }
            }
            else
                $loan_list[$item['u_key']][$item['l_key']] = $item;
        }else{
            $loan_list[] = $item;
        }
    }

    if ($r_type == 0){
        if($ukey >= 0)
            return $loan_list[$ukey];
        else{
            return $loan_list;
        }
    }else{
        $result['item'] = $loan_list;
        return $result;
    }
}

/*
 * $deal_id:标ID               $bid_money:出借金额                        $bid_paypassword:支付密码            $is_pc:          $red_id:红包ID            $learn_id
 *$red_money:红包金额          // $ecv_money:代金券金额                      $interestrate_id:加息券                    $use_interestrate:原系统红包（已改或弃用）
 */
// 参数中  去掉交易密码   zjb 2017年6月13日11:52:00
function check_dobid2($deal_id,$bid_money,$is_pc = 0,$red_id='',$learn_id = 0,$red_money=0,$ecv_money=0,$interestrate_id=0,$use_interestrate=0){
    $root = array();
    $root["status"] = 0;//0:出错;1:正确;
    $bid_money = floatval($bid_money);
//		$bid_paypassword = strim($bid_paypassword);  // 存管版不验证平台交易密码
    if(!check_ipop_limit(CLIENT_IP,"deal_dobid",intval(app_conf("SUBMIT_DELAY")))) {
        $root["show_err"] = $GLOBALS['lang']['SUBMIT_TOO_FAST'];
        return $root;
    }
    if(!$GLOBALS['user_info']){
        $root["show_err"] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
        return $root;
    }
    if(!$GLOBALS['user_info']['cunguan_tag']){
        $root["show_err"] = '请先开通存管用户！';
        return $root;
    }
    if(!$GLOBALS['user_info']['cunguan_pwd']){
        $root["show_err"] = '请先设置存管交易密码！';
        return $root;
    }
    $cg_bank=$GLOBALS['db']->getOne("select bankcard from ".DB_PREFIX."user_bank where user_id=".$GLOBALS['user_info']['id']." and cunguan_tag=1");
    if(!$cg_bank){
        $root["show_err"] = '请先绑定存管银行卡！';
        return $root;
    }
//        $cunguan_tag=$GLOBALS['db']->getOne("select cunguan_tag from ".DB_PREFIX."deal where id=".$deal_id);
//        if($cunguan_tag!=1){
//            if(!$GLOBALS['user_info']['paypassword']){
//                $root["show_err"] = '请先设置交易密码';
//                return $root;
//            }
//            if($bid_paypassword==""){
//                $root["show_err"] = $GLOBALS['lang']['PAYPASSWORD_EMPTY'];
//                return $root;
//            }
//            if($bid_paypassword!=$GLOBALS['user_info']['paypassword']){
//                $root["show_err"] = $GLOBALS['lang']['PAYPASSWORD_ERROR'];
//                return $root;
//            }
//        }


    if(intval($use_interestrate) > 0 )
    {
        if(intval($interestrate_id) > 0){
            //加息券
//				$i_sql = "select *,e.id as i_id from ".DB_PREFIX."interestrate as e left join ".DB_PREFIX."interestrate_type as et on e.ecv_type_id = et.id where ((e.user_id = ".intval($GLOBALS['user_info']['id'])." and e.to_user_id = 0) or e.to_user_id = ".intval($GLOBALS['user_info']['id']).") AND e.id=".$interestrate_id;
            $i_sql="select id,status from ".DB_PREFIX."interest_card where id=".$interestrate_id;
            //use_type = 0 PC端使用
//				if($is_pc == 0)
//				{
//					$i_sql .= " and (et.use_type = 1 or et.use_type = 2)";
//				}
//				else
//				{
//					$i_sql .= " and (et.use_type = 0 or et.use_type = 2)";
//				}

            $interestrate = $GLOBALS['db']->getRow($i_sql);

            if(!$interestrate){
                $root["show_err"] = "加息券不存在";
                return $root;
            }
//				if($interestrate['use_limit'] > 0 && $interestrate['use_limit'] - $interestrate['use_count'] <=0 ){
//					$root["show_err"] = "此加息券已被使用过了";
//					return $root;
//				}
            if($interestrate['status'] != 0){
                $root["show_err"] = "此加息券已被使用过了";
                return $root;
            }
            if($interestrate['begin_time'] > 0 && $interestrate['begin_time'] > TIME_UTC){
                $root["show_err"] = "此加息券还不能用";
                return $root;
            }
//				if($interestrate['end_time'] > 0 && ($interestrate['end_time'] +24*3600 - 1) < TIME_UTC){
//					$root["show_err"] = "此加息券已过期";
//					return $root;
//				}
            if($interestrate['end_time'] > 0 && $interestrate['end_time']){
                $root["show_err"] = "此加息券已过期";
                return $root;
            }
            $root['interestrate_id'] = $interestrate['i_id'];
        }
    }
    // 获取已选择红包
//        if($red_id){
//            $condition=" and rp.status=0 and rp.id in(".$red_id.")";
//            $choose_red_list=get_uc_red_list("0,1000",$GLOBALS['user_info']['id'],$condition);     //这个方法没调起来
////            $choose_red_money=0;
////            $max_money=0;
//            foreach($choose_red_list['list'] as $k=>$v){
//                $choose_red_list['list'][$k]['max_use_money']=$v['ratio'];
//                $choose_red_list['list'][$k]['begin_date']=date("Y-m-d",$v['begin_time']);
//                $choose_red_list['list'][$k]['end_date']=date("Y-m-d",$v['end_time']);
////                $max_money+=$choose_red_list['list'][$k]['max_use_money'];
////                $choose_red_money+=$v['money'];
//            }
//
////            echo $max_money;die;
////            if($max_money!=$red_money){
////                $root["show_err"] = "红包金额与已选红包金额不匹配！";
////                return $root;
////            }
//            if(!$choose_red_list){
//                $root["show_err"] = "红包不可用或已过期，请重新选择！";
//                return $root;
//            }
////            if($max_money>$bid_money){
////                $root["show_err"] = "红包超限，请重新选择！";
////                return $root;
////            }
//        }
    /*
    if(intval($learn_id) > 0){
        //体验金抵用
        $today=to_date(TIME_UTC,"Y-m-d");
        $sql = "select lsl.* from ".DB_PREFIX."learn_send_list lsl left join ".DB_PREFIX."learn_type lt on lsl.type_id = lt.id where lt.invest_type = 1 and lt.is_effect = 1 AND lsl.is_recycle=0 AND lsl.user_id = ".intval($GLOBALS['user_info']['id'])." AND lsl.id=".$learn_id;
        $learn = $GLOBALS['db']->getRow($sql);

        if(!$learn){
            $root['show_err'] = "体验金不存在";
            return $root;
        }
        if($learn['is_use'] > 0){
            $root['show_err'] = "此体验金已被使用过了";
            return $root;
        }
        if($learn['begin_time'] > $today){
            $root['show_err'] = "此体验金还不能用";
            return $root;
        }
        if($learn['end_time'] < $today){
            $root['show_err'] = "此体验金已过期";
            return $root;
        }

        $root['learn_money'] = $learn['money'];
        $root['learn_id'] = $learn_id;

    }

    */
    if($bid_money<=0){
        $root["show_err"] = "请先填写出借金额";
        return $root;
    }
    if($red_money<0){
        $root["show_err"] = "红包金额不能为负数";
        return $root;
    }
//		if($ecv_money<0){
//			$root["show_err"] = "代金券金额不能为负数";
//			return $root;
//		}
    if($GLOBALS['user_info']['cunguan_money'] < $bid_money){
        $root["show_err"] = "存管账户余额不足，请充值";
        return $root;
    }
    /*
     * 此判断为存管上线后用于区别1.0资金，上线存管后开启，然后把上面判断账户余额的注释掉
     *
    if($GLOBALS['user_info']['money'] > $GLOBALS['user_info']['recharge_money']) {
        if ($bid_money > $GLOBALS['user_info']['recharge_money']) {
            $root["show_err"] = "2.0可用余额为" . $GLOBALS['user_info']['recharge_money'] . "元,1.0转入金额只限提现";
            return $root;
        }
    }elseif($GLOBALS['user_info']['money'] = $GLOBALS['user_info']['recharge_money']){
        if($GLOBALS['user_info']['money'] < $bid_money){
            $root["show_err"] = "账户余额不足，请充值";
            return $root;
        }
    }else{
        $root["show_err"] = "资金账户出错，请联系客服处理";
        return $root;
    }
    */
    if($GLOBALS['user_info']['debts']==1){
        $deal = get_deal($deal_id,0);
    }else{
        $deal = get_deal($deal_id);
    }
    if(!$deal){
        $root["show_err"] = $GLOBALS['lang']['PLEASE_SPEC_DEAL'];
        return $root;
    }
    if($deal['user_id'] == $GLOBALS['user_info']['id']){
        $root["show_err"] = $GLOBALS['lang']['CANT_BID_BY_YOURSELF'];//不能投自己发放的标
        return $root;
    }
    if($deal['ips_bill_no']!="" && $GLOBALS['user_info']['ips_acct_no']==""){
        $root["show_err"] = "此标为第三方托管标，请先绑定第三方托管账户,<a href=\"".url("index","uc_center")."\" target='_blank'>点这里设置</a>";
        return $root;
    }
    //判断是否是新手专享
    $deal_load_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_load  WHERE user_id=".$GLOBALS['user_info']['id']." ");
    if($deal['is_new']==1 && $deal_load_count > 0){
        $root["show_err"] = "此标为新手专享，只有新手才可以出借哦";
        return $root;
    }
    if($deal['is_wait'] == 1){
        $root["show_err"] = $GLOBALS['lang']['DEAL_IS_WAIT'];
        return $root;
    }
    if(floatval($deal['borrow_amount']) <= floatval($deal['load_money'])){
        $root["show_err"] = $GLOBALS['lang']['DEAL_BID_FULL'];//已满标
        return $root;
    }
    if(floatval($deal['deal_status']) != 1 && floatval($deal['deal_status']) != 0){
        $root["show_err"] = $GLOBALS['lang']['DEAL_FAILD_OPEN'];
        return $root;
    }
    $create_time = $GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."deal_load where deal_id = ".intval($deal_id)."  and user_id=".intval($GLOBALS['user_info']['id'])." order by create_time desc limit 1");
    if((TIME_UTC-$create_time) <= 10 ){
        $root["show_err"] = "出借失败";
        return $root;
    }
    $deal['need_money'] = $deal['borrow_amount'] - $deal['load_money'];
    $weibiao_yes = intval($deal['need_money'])<intval($deal['min_loan_money'])?1:0;
    if($weibiao_yes){
        if($red_money||$interestrate_id){
            $root["show_err"] =  "尾标不能使用加息券和红包";
            return $root;
        }
        if($bid_money<$deal['need_money']){
            $root["show_err"] =  "尾标金额不可变更";
            return $root;
        }
    }else{
        if($bid_money< $deal['min_loan_money'] ){
            $root["show_err"] = "起投金额为".$deal['min_loan_money']."元";
            return $root;
        }
        if($deal['max_loan_money'] > 0 && $bid_money>$deal['max_loan_money']){
            $root["show_err"] = "最大出借金额为".$deal['max_loan_money']."元";
            return $root;
        }
    }
    if($deal['need_money']<($bid_money+$red_money)){
        $root["show_err"] = "出借总额大于可投金额";
        return $root;
    }
    if($deal['use_ecv'] !=1 && $red_money){
        $root["show_err"] = "此标不能使用红包";
        return $root;
    }
    $time=time();
//        $cg_red_money=$GLOBALS['db']->getOne("select sum(rp.money) from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id = ".$GLOBALS['user_info']['id']." and rp.status=0 and rpn.red_type=3 and rp.end_time>$time");
//		if($red_money>$cg_red_money){
//			$root["show_err"] = "红包可用余额".$cg_red_money."元，请重新输入";
//			return $root;
//		}
//		if($deal['use_interestrate'] !=1){
//			if($ecv_id || $ecv_money) {
//				$root["show_err"] = "此标不能使用代金券";
//				return $root;
//			}
//		}else {
//			if ($ecv_id) {
//				$ecv_count = 0;
//				$che_ecv_id = explode(',', $ecv_id);
////				foreach ($che_ecv_id as $k => $v) {
////					$ecv_count += $GLOBALS['db']->getOne("select count(id) from " . DB_PREFIX . "ecv  where cunguan_tag=1 and id =" . $v . " and status = 0  and end_time >" . time() . " and user_id = " . intval($GLOBALS['user_info']['id']));
////				}
//                $ecv_count = $GLOBALS['db']->getOne("select count(id) from " . DB_PREFIX . "ecv  where cunguan_tag=1 and status = 0  and end_time >" . time() . " and user_id = " . intval($GLOBALS['user_info']['id'])." and id in(".$ecv_id.")");
//				if (count($che_ecv_id) != $ecv_count) {
//					$root["show_err"] = "选用代金券已过期，请重新选择";
//					return $root;
//				}
//			}
//			if ($red_money > 0) {
////				foreach ($che_ecv_id as $k => $v) {
////					$cash_money += $GLOBALS['db']->getOne("select money from " . DB_PREFIX . "ecv  where cunguan_tag=1 and id =" . $v . " and user_id = " . intval($GLOBALS['user_info']['id']));
////				}
//                $cash_money = $GLOBALS['db']->getOne("select sum(money) from " . DB_PREFIX . "red_packet  where user_id = " . intval($GLOBALS['user_info']['id'])." and id in(".$red_id.")");
//				if ($cash_money != $red_money) {
//					$root["show_err"] = "红包金额不匹配，请重新选择";
//					return $root;
//				}
//			}

//			if (($bid_money / 50) < $ecv_money) {
//				$root["show_err"] = "代金券超出使用限额";
//				return $root;
//			}


    //@file_put_contents("/Public/sqlog.txt",print_r($_REQUEST,1));
    //手机端或者 按份数 默认跑到这里
    /*
    if ($deal['uloadtype'] == 0 || $is_pc == 0){
        if($bid_money <=0 || $bid_money < $deal['min_loan_money'] || ($bid_money * 100)%100!=0){
            $root["show_err"] = $GLOBALS['lang']['BID_MONEY_NOT_TRUE'];
            //print_r($deal);
            return $root;
        }
        if($deal['uloadtype'] == 1 && $is_pc == 0 && ($bid_money % $deal['min_loan_money'])!=0){
            $root["show_err"] = "必须为".$deal['min_loan_money']."的倍数";
            //print_r($deal);
            return $root;
        }
        if(floatval($deal['max_loan_money']) >0){
            if($bid_money > floatval($deal['max_loan_money'])){
                $root["show_err"] = $GLOBALS['lang']['BID_MONEY_NOT_TRUE'];
                //print_r($deal);
                /*
                 $root["bid_money"] = $bid_money;
                $root["max_loan_money"] = floatval($deal['max_loan_money']);
                $root["show_err"] = 'ddd2';
                print_r($root);
                die();

                return $root;
            }
        }

        if((int)strim(app_conf('DEAL_BID_MULTIPLE')) > 0){
            if($bid_money%(int)strim(app_conf('DEAL_BID_MULTIPLE'))!=0){
                $root["show_err"] = $GLOBALS['lang']['BID_MONEY_NOT_TRUE'];
                return $root;
            }
        }


//		//判断所投的钱是否超过了剩余投标额度
//		if($bid_money > (round($deal['borrow_amount'],2) - round($deal['load_money'],2))){
//			$root["show_err"] = sprintf($GLOBALS['lang']['DEAL_LOAN_NOT_ENOUGHT'],format_price($deal['borrow_amount'] - $deal['load_money'] ));
//			return $root;
//		}
        //判断所投的钱是否超过了剩余投标额度
        if($bid_money > (round($deal['borrow_amount'],2) - round($deal['load_money'],2) - getCollWaitMoney($deal))){
            $root["show_err"] = sprintf($GLOBALS['lang']['DEAL_LOAN_NOT_ENOUGHT'],format_price($deal['borrow_amount'] - $deal['load_money'] - getCollWaitMoney($deal)));
            return $root;
        }


        //判断所投的全部金额是否超过了所限制的金额
        if(floatval($deal['max_loan_money']) > 0){
            $has_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."deal_load WHERE deal_id=".$deal_id." AND user_id=".$GLOBALS['user_info']['id']);
            if($has_bid_money > 0){
                if($has_bid_money > floatval($deal['max_loan_money'])){
                    $root["show_err"] = "您已经投满该借款所限制的额度：".format_price($deal['max_loan_money']);
                    return $root;
                }

                if($has_bid_money + $bid_money > floatval($deal['max_loan_money'])){
                    $root["show_err"] = "您已经投了".format_price($has_bid_money);
                    if(floatval($deal['max_loan_money']) - $has_bid_money > 0){
                        $root["show_err"] .= ",只能再投".format_price(floatval($deal['max_loan_money']) - $has_bid_money);
                    }
                    else{
                        $root["show_err"] .= ",不能再投了";
                    }
                    return $root;
                }
            }
        }

        $root["bid_money"] = $bid_money;
    }
    else{
        if(intval($bid_money) <=0 || ($bid_money * 100)%100!=0){
            $root["show_err"] = $GLOBALS['lang']['BID_MONEY_NOT_TRUE'];
            //print_r($deal);
            return $root;
        }

        //判断所投的钱是否超过了剩余投标额度
        $has_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."deal_load WHERE deal_id=".$deal_id." AND user_id=".$GLOBALS['user_info']['id']);
        $has_bid_portion = $has_bid_money/($deal['borrow_amount'] / $deal['portion']);
        if(intval($deal['max_portion']) > 0  && intval($bid_money) > (intval($deal['max_portion'] - intval($has_bid_portion)))){
            $root["show_err"] = "您已经购买了$has_bid_portion份，还能购买".intval($deal['max_portion'] - intval($has_bid_portion))."份";
            return $root;
        }
        elseif(intval($bid_money) > intval($deal['need_portion'])){
            $root["show_err"] = "您已经购买了$has_bid_portion份，还能购买".intval($deal['need_portion'])."份";
            return $root;
        }

        $root["bid_money"] = $bid_money * ($deal['borrow_amount'] / $deal['portion']);
    }
    */

    // $redis = new Redis();
    // $need_key = "need".$deal_id;
    // if ($redis->connect(REDIS_HOST, REDIS_PORT) == false) {
    //     die($redis->getLastError());
    // }
    // if ($redis->auth(REDIS_PWD) == false) {
    //     die($redis->getLastError());
    // }
    // if(!$redis->get($need_key)){
    // 	$redis->set($need_key,$deal['need_money']);
    // }
    //       $bd_money = "bid_money".$deal_id;
    // $redis->lpush($bd_money,$bid_money+$red_money+$ecv_money);
    // $need = $redis->get($need_key) - $redis->rpop($bd_money);
    //  	if($need  < 0){
    //  		$root["show_err"] = "出借失败";
    // 	return $root;
    //  	}else{
    //  		$redis->set($need_key,$need);
    //  	}




    $root["deal"] = $deal;
    if($deal['ips_bill_no']==""){
        $root["status"] = 1;//0:出错;1:正确;
        return $root;
    }else{
        $root["status"] = 2;//第三方托管标 正确
        return $root;
    }
}

function dobid2_ok($deal_id,$user_id){
    $deal = get_deal($deal_id);
    sys_user_status($user_id);

    //超过一半的时候

    if($deal['deal_status']==1 && $deal['progress_point'] >= 50 && $deal['progress_point']<=60 && $deal['is_send_half_msg'] == 0)
    {
        $msg_conf = get_user_msg_conf($deal['user_id']);
        //邮件
        if(app_conf("MAIL_ON")){
            if(!$msg_conf || intval($msg_conf['mail_half'])==1){
                $load_tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_HALF_EMAIL'",false);
                $user_info = get_user_info("AES_DECRYPT(email_encrypt,'".AES_DECRYPT_KEY."') as email,user_name","id = ".$deal['user_id']);
                $tmpl_content = $load_tmpl['content'];
                $notice['user_name'] = $user_info['user_name'];
                $notice['deal_name'] = $deal['name'];
                $notice['deal_url'] = SITE_DOMAIN.$deal['url'];
                $notice['site_name'] = app_conf("SHOP_TITLE");
                $notice['site_url'] = SITE_DOMAIN.APP_ROOT;
                $notice['help_url'] = SITE_DOMAIN.url("index","helpcenter");
                $notice['msg_cof_setting_url'] = SITE_DOMAIN.url("index","uc_msg#setting");


                $GLOBALS['tmpl']->assign("notice",$notice);

                $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                $msg_data['dest'] = $user_info['email'];
                $msg_data['send_type'] = 1;
                $msg_data['title'] = "您的借款列表“".$deal['name']."”招标过半！";
                $msg_data['content'] = addslashes($msg);
                $msg_data['send_time'] = 0;
                $msg_data['is_send'] = 0;
                $msg_data['create_time'] = TIME_UTC;
                $msg_data['user_id'] =  $deal['user_id'];
                $msg_data['is_html'] = $load_tmpl['is_html'];
                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
            }
        }

        //站内信
        if(intval($msg_conf['sms_half'])==1){

            $notices['shop_title'] = app_conf("SHOP_TITLE");
            $notices['url'] =  "“<a href=\"".$deal['url']."\">".$deal['name']."</a>”";

            $tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_OVER_FIVE'",false);
            $GLOBALS['tmpl']->assign("notice",$notices);
            $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);
            send_user_msg("",$content,0,$deal['user_id'],TIME_UTC,0,true,15);
        }
        //更新
        $GLOBALS['db']->autoExecute(DB_PREFIX."deal",array("is_send_half_msg"=>1),"UPDATE","id=".$deal_id);
    }
}

// 理财计划跳转验密
function check_pwd_url($load_seqno){
    $root['url'] = WAP_SITE_DOMAIN."/index.php?ctl=deal&act=plan_check_pwd&load_seqno=".$load_seqno;
    $root['status'] = 1;
    return $root;
}

/*
 * $deal_id:标ID               $bid_money:出借金额                   $bid_paypassword:支付密码            $is_pc:          $ecv_id:代金券ID            $learn_id
 *$red_money:红包金额          $ecv_money:代金券金额                 $interestrate_id:加息券               $use_interestrate:原系统红包（已改或弃用）
 */
//function dobid2($deal_id,$bid_money,$bid_paypassword,$is_pc=0,$ecv_id='',$learn_id=0,$red_money=0,$ecv_money=0,$interestrate_id=0,$use_interestrate=0){
function dobid2($map){
    $level=new Level();
    $deal_id = $map['deal_id'];
    $bid_money = $map['bid_money'];
    $is_pc = $map['is_pc'];
    $interestrate_id = $map['interestrate_id'];
    $interestrate_money = $map['interestrate_money'];
    $red_id = $map['red_id'];
    $learn_id = $map['learn_id'];
    $red_money = $map['red_money']?$map['red_money']:0;
    $total_money=$red_money+$bid_money;
    // 平台加息
    $deal_info=$GLOBALS['db']->getRow("select id,user_id,name,repay_time,repay_start_time,interest_rate,repay_time_type,loantype,borrow_amount,load_money,buy_count,rate,debts,old_deal_id,old_load_id,create_time,is_new,plan_id,is_advance   from ".DB_PREFIX."deal where id=".$deal_id);
    if($deal_info['interest_rate']){
        // 平台加息收益
        $increase_interest=get_jct_interest_money($deal_info,$total_money);
    }
    $cunguan_tag=$GLOBALS['db']->getOne("select cunguan_tag from ".DB_PREFIX."deal where id=".$deal_id);
    if(!$cunguan_tag){
        $root["show_err"] = "该标的不存在！";
        return $root;
    }
    // 债转标 此标的不允许原始发标人和自己进行出借
    if($deal_info['debts']==1){
        $old_deal_user=$GLOBALS['db']->getOne("select user_id from ".DB_PREFIX."deal where id=".$deal_info['old_deal_id']);
        if($GLOBALS['user_info']['id']==$old_deal_user || $GLOBALS['user_info']['id']==$deal_info['user_id']){
            $root["show_err"] = "此标的不允许原始发标人和自己进行出借！";
            return $root;
        }
    }
    if(empty($map['load_seqno'])&&empty($map['cunguan_tag'])){
        // $root = check_dobid2($deal_id,$bid_money,$bid_paypassword,$is_pc,$ecv_id,$learn_id,$red_money,$ecv_money,$interestrate_id=0,$use_interestrate=0);
        $root = check_dobid2($deal_id,$bid_money,$is_pc,$red_id,$learn_id,$red_money,$ecv_money=0,$interestrate_id,$use_interestrate=0);
        //   区别存管和非存管
        if($root["status"] == 1&&$cunguan_tag==1){
            $publics = new Publics();
            if($red_money>0){
                $xuni_seqno=$publics->seqno();
            }
            $load_seqno=$publics->seqno();
            $deal_load_data['xuni_seqno']=$xuni_seqno;
            $deal_load_data['load_seqno']=$load_seqno;
            $deal_load_data['deal_id']=$deal_id;
            $deal_load_data['user_id']=$GLOBALS['user_info']['id'];
            $deal_load_data['money']=$bid_money;
            $deal_load_data['total_money']=$bid_money+$red_money;
            $deal_load_data['red'] = $red_money;
            $deal_load_data['red_id'] = $red_id;
            $deal_load_data['interestrate_id'] = $interestrate_id;
            $deal_load_data['interestrate_money'] = $interestrate_money;
            $deal_load_data['add_ip'] = $_SERVER['REMOTE_ADDR'];
            $deal_load_data['cunguan_tag'] = $cunguan_tag;
            $deal_load_data['increase_interest'] = $increase_interest;
            $deal_load_data['create_date'] = date('Y-m-d',time());
            $deal_load_data['create_time'] = time();
            $deal_load_data['old_deal_id'] = $deal_info['old_deal_id']?$deal_info['old_deal_id']:'';
            $deal_load_data['debts'] = $deal_info['debts']?2:0;
            $deal_load_data['create_time'] = time();
            $deal_load_data['plan_id'] = $plan_id;
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_temp",$deal_load_data,"INSERT");
            if(!$res){
                $root["show_err"] = "请稍后重试！";
                return $root;
            }
        }
        if ($root["status"] == 0){
            return $root;
        }
        elseif($root["status"] == 2){
            $root['jump'] = APP_ROOT."/index.php?ctl=collocation&act=RegisterCreditor&deal_id=$deal_id&user_id=".$GLOBALS['user_info']['id']."&bid_money=".$root['bid_money']."&bid_paypassword=$bid_paypassword"."&from=".$GLOBALS['request']['from']."&learn_id=".$learn_id."&ecv_id=".$ecv_id;
            $root['jump'] = str_replace("/mapi", "", SITE_DOMAIN.$root['jump']);
            return $root;
        }
    }
    // 跳转第三方交易密码页面区分wap和pc端
    if($cunguan_tag==1&&WAP==0&&empty($map['load_seqno'])){

        $html =  $publics ->verify_trans_password('deal','cg_dobid',$GLOBALS['user_info']['id'],4,$load_seqno,"_self");
        $data['msg']=$html;
        $data['status']=4;
        return $data;

    }elseif($cunguan_tag==1&&WAP==1&&empty($map['load_seqno'])){
        $html =  $publics ->verify_trans_password('deal','cg_dobid',$GLOBALS['user_info']['id'],4,$load_seqno,"_self");
        $data['msg']=$html;
        $data['status']=4;
        return $data;
    }
    $root["status"] = 0;
    if($bid_money > $GLOBALS['user_info']['cunguan_money']){
        $root["show_err"] = $GLOBALS['lang']['MONEY_NOT_ENOUGHT'];//余额不足，无法投标
        return $root;
    }
//    print_r($deal_info);die;
    $data['user_id'] = $GLOBALS['user_info']['id'];
    $data['user_name'] = $GLOBALS['user_info']['user_name'];
    $data['deal_id'] = $deal_id;
    $data['money'] = $bid_money;
    $data['red'] = $red_money;
    $data['red_id'] = $red_id;
    $data['interestrate_id'] = $interestrate_id;
    $data['create_time']=time();
    $data['interestrate_money']=$interestrate_money;
    $data['total_money'] = $bid_money+$red_money;
    $data['add_ip'] = $_SERVER['REMOTE_ADDR'];
    $insertdata = return_deal_load_data($data,$GLOBALS['user_info'],$root['deal']);
    // 存管入库的字段判断
    if($map['load_seqno']&&$map['cunguan_tag']==1){
        $insertdata['cunguan_tag']=$map['cunguan_tag'];
        $insertdata['xuni_seqno']=$map['xuni_seqno'];
        $insertdata['load_seqno']=$map['load_seqno'];
        $insertdata['interestrate_money']=$map['interestrate_money'];
        $insertdata['increase_interest']=$map['increase_interest'];
        $insertdata['old_deal_id'] = $deal_info['old_deal_id']?$deal_info['old_deal_id']:'';
        $insertdata['debts'] = $deal_info['debts']?2:0;
    }


    $GLOBALS['db']->startTrans();   //开始事务
    $deal_con=$GLOBALS['db']->getRow("SELECT borrow_amount,load_money,buy_count FROM ".DB_PREFIX."deal where id=".$deal_id." FOR UPDATE");
    if(($deal_con['borrow_amount']-$deal_con['load_money'])<$data['total_money']){
        $GLOBALS['db']->rollback();
        $root["show_err"] = "出借总额大于可出借金额";
        return $root;
    }
    //判断封标奖励
    if($deal_info['is_advance'] != 1 && $deal_info['is_new'] != 1){
        if(($deal_con['borrow_amount']-$deal_con['load_money'])==$data['total_money']){
            SendSealedReward($data['user_id'],$bid_money);
        }
    }
    // $user_cg_info=get_cg_user_info($GLOBALS['user_info']['id']);
    // if($user_cg_info['assetamount']<$bid_money){
    //     $GLOBALS['db']->rollback();
    //     $root["show_err"] = "存管账户余额不足";
    //     return $root;
    // }
    //  出借金额折标后入库
    if($deal_info['debts'] == 1){
        $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$deal_info['old_deal_id']." order by repay_time desc limit 1");
        $debts_repay_time = ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24)+1;
        $data_arr['total_invite_invest_money']= round($debts_repay_time*$data['total_money']/365);
    }else{
        $data_arr['total_invite_invest_money']=round($data['total_money']*$deal_info['repay_time']/12);
    }
    if($GLOBALS['user_info']['pid']>0){
        $res3 = $GLOBALS['db']->query("update ".DB_PREFIX."user set total_invite_invest_money=total_invite_invest_money+".$data_arr['total_invite_invest_money']." where id=".$GLOBALS['user_info']['id']);
    }else{
        $res3 = true;
    }
    $is_get_reward = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_grow_point where user_id=".$GLOBALS['user_info']['pid']." and task_type=10");
     if(is_invite_load() && !$is_get_reward>0){
        // 邀请好友出借奖励成长值
        $result=$level->get_grow_point(10,$data_arr['total_invite_invest_money'],$GLOBALS['user_info']['pid']);
        if(!$result){
            $GLOBALS['db']->rollback();
            $root["show_err"] = "出借失败，请重试";
            return $root;
        }
    } 
    // 出借满10次奖励成长值
    $is_get_reward = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_grow_point where user_id=".$GLOBALS['user_info']['id']." and task_type=15");
    $load_count=$GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal_load where user_id=".$GLOBALS['user_info']['id']);
    if(!$is_get_reward){

        $level->get_grow_point(15,$load_count+1);
    }
    // 是否当天注册
    if($deal_info['debts'] == 1){
        $param=round($data['total_money']*$debts_repay_time/365,2);
    }else{
        $param=round($data['total_money']*$deal_info['repay_time']/12,2);
    }
    if(date('Y-m-d',$GLOBALS['user_info']['create_time']) == date("Y-m-d",time())){
        $level->get_grow_point(17,$param);
    }else{
        $level->get_grow_point(16,$param);
    }
    // 是否首次出借
    if($load_count==0){
        $level->get_grow_point(22);
    }
    $new_load_money = $deal_con['load_money']+$data['total_money'];
    $buy_count = $deal_con['buy_count']+1;
    $res1 = $GLOBALS['db']->query("update ".DB_PREFIX."deal set load_money = ".intval($new_load_money).",buy_count = ".$buy_count." where id =".$deal_id);
    $res2 = $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$insertdata,"INSERT");
    $load_id = $GLOBALS['db']->insert_id();

    if($res1 && $res2 && $load_id && $res3){
        if($deal_info['borrow_amount']<$deal_info['load_money']){
            $GLOBALS['db']->rollback();
            $root["show_err"] = "出借总额大于可出借金额";
            return $root;
        }elseif(intval($deal_info['borrow_amount'])==intval($deal_info['load_money'])+$data['total_money']){
            if($deal_info['debts']==1){
                $GLOBALS['db']->query("update ".DB_PREFIX."deal set success_time = ".$insertdata['create_time'].",deal_status = 4 where id =".$deal_id);
            }else{
                $GLOBALS['db']->query("update ".DB_PREFIX."deal set success_time = ".$insertdata['create_time'].",deal_status = 2 where id =".$deal_id);
            }
        }
    }else{
        $GLOBALS['db']->rollback();
        $root["show_err"] = "出借失败,请重试！";
        return $root;
    }
    // 如果投的是债转标，投资成功时生成还款计划
    if(!empty($map['load_seqno'])&&$map['cunguan_tag']==1&&$deal_info['debts']==1){
        if($deal_info['loantype']==1){      //按月付息 到期还本
            $res=make_repay_plan_loantype1($data,$deal_info,$load_id);
        }elseif($deal_info['loantype']==0){     //等额本息
            $res=make_repay_plan_loantype0($data,$deal_info,$load_id);
        }
        if(!$res){
            $GLOBALS['db']->rollback();
            $root["show_err"] = "请稍后重试！";
            return $root;
        }
        
        //处理虚拟货币
        if(($red_money>0)&&$cunguan_tag==1&&!empty($map['xuni_seqno'])){
            require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
            $ideal_money=$red_money;
            $deal=new Deal;
            $status=$deal->deal($map["xuni_seqno"],'T10',$ideal_money,$deal_id,$GLOBALS['user_info']['id']);
            // print_r($status);die;
            if($status['respHeader']['respCode']!='P2P0000'){
                $GLOBALS['db']->rollback();
                $root["show_err"] = $status['respHeader']['respMsg'];
                return $root;
            }
        }
        // 投资成功后将资金打入转让人账户
        require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
        $deal=new Deal;
        $status=$deal->deal($map['load_seqno'],'T07',$total_money,$deal_id,$GLOBALS['user_info']['id']);
        $status['load_seqno']=$map['load_seqno'];
        if($status['respHeader']['respCode']!='P2P0000'){
            $GLOBALS['db']->rollback();
            $root["show_err"] = $status['respHeader']['respMsg'];
            return $root;
        }
        // 转让方资金增加
        require_once APP_ROOT_PATH."system/libs/user.php";
        $brief="转让成功";
        $deal_name=$GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."deal where id=".$deal_id);
        $msg="[<a href='/index.php?ctl=deal&id=".$deal_id."' target='_blank'>".$deal_name."</a>]的转让";
        $debts['cunguan_money']=$total_money;
        $debts['cunguan_lock_money']=-$total_money;
        modify_account($debts,$deal_info['user_id'],$msg,62,$brief,$cunguan_tag);

    }else{
        /// 如果不是债转标的
        // 处理虚拟货币，先充值
        if(($red_money>0)&&$cunguan_tag==1&&!empty($map['xuni_seqno'])){
            require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
            $ideal_money=$red_money;
            $deal=new Deal;
            $status=$deal->deal($map["xuni_seqno"],'T10',$ideal_money,$deal_id,$GLOBALS['user_info']['id']);
            // print_r($status);die;
            if($status['respHeader']['respCode']!='P2P0000'){
                $GLOBALS['db']->rollback();
                $root["show_err"] = $status['respHeader']['respMsg'];
                return $root;
            }
        }

        // 存管资金投资
        if($load_id > 0&&!empty($map['load_seqno'])){
            require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
            $deal=new Deal;
            $status=$deal->deal($map['load_seqno'],'T01',$total_money,$deal_id,$GLOBALS['user_info']['id']);
            if($status['respHeader']['respCode']!='P2P0000'){
                $GLOBALS['db']->rollback();
                $root["show_err"] = $status['respHeader']['respMsg'];
                return $root;
            }
        }
    }

	if($load_id > 0){
		$data['load_id'] = $load_id;
		//更改红包状态
		if($red_id){
            $GLOBALS['db']->query("UPDATE ".DB_PREFIX."red_packet SET status=1,deal_id =".$deal_id.",deal_load_id =".$load_id." WHERE user_id=".$GLOBALS['user_info']['id']." and id in(".$red_id.")");
		}

		//更改加息卡状态
		if($interestrate_id > 0){
			$GLOBALS['db']->query("UPDATE ".DB_PREFIX."interest_card SET status=1,deal_id =".$deal_id.",deal_load_id =".$load_id." WHERE user_id=".$GLOBALS['user_info']['id']." and id=".$interestrate_id);
		}
        /*
                //更改体验金状态
                if($learn_id > 0){
                    $now_time = to_date(TIME_UTC,"Y-m-d H:i:s");
                    $today = to_date(TIME_UTC,"Y-m-d");
                    $GLOBALS['db']->query("update ".DB_PREFIX."learn_send_list set is_use = '1',use_time ='".$now_time."',use_date='".$today."' where is_use = '0' and id ='".$learn_id."' and  user_id = ".intval($GLOBALS['user_info']['id'])." ");
                }
                */
		if($bid_money > 0){
            $deal_name=$GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."deal where id=".$deal_id);
//                $msg = '[<a href="'.$root['deal']['url'].'" target="_blank">'.$root['deal']['name'].'</a>]的出借';
                $msg="[<a href='/index.php?ctl=deal&id=".$deal_id."' target='_blank'>".$deal_name."</a>]的出借";
                $brief = '出借成功';
                require_once APP_ROOT_PATH."system/libs/user.php";
                $data['cunguan_money'] = -($data['total_money']);

                $data['cunguan_lock_money'] = $data['total_money'];
                if($red_money){
                    $data['red_money'] = -$red_money;
                    red_modify_account($data,$GLOBALS['user_info']['id'],$msg.',使用红包',$cunguan_tag);
                }
                unset($data['money']);
                modify_account($data,$GLOBALS['user_info']['id'],$msg,2,$brief,$cunguan_tag);
                $decository['status']=1;
                $result=$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$decository,"UPDATE","seqno='".$map['load_seqno']."'");
            if($map['xuni_seqno']){
                $res=$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$decository,"UPDATE","seqno='".$map['xuni_seqno']."'");
            }


		}
        //   存管标满标后后台放款时生成
//        if(empty($map['seqno'])&&$map['cunguan_tag']!=1){
//            //生成用户回款计划
//            //月还利息
//            $month_repay_money_f = av_it_formula($data ['total_money'],$deal_info['rate']/12/100);
//            //月还利息--精确到小数点后两位
//            $month_repay_money = round($month_repay_money_f,2);
//            for($i=0;$i<$deal_info['repay_time'];$i++){
//                $repay_data['u_key'] = $deal_info['buy_count']-1;
//                $repay_data['l_key'] = $i;
//                $repay_data['deal_id'] = $deal_id;
//                $repay_data['load_id'] = $load_id;
//                $repay_data['repay_id'] = 0;
//                $repay_data['t_user_id'] = 0;
//                $repay_data['user_id'] = $GLOBALS['user_info']['id'];
//                $repay_data['repay_time'] = strtotime("+" . $i+1 . " months", $insertdata['create_time']);
//                $repay_data['repay_date'] = to_date($repay_data['repay_time']);
//                if($i+1 == $deal_info['repay_time']){
//                    $repay_data['repay_money'] = ($data['total_money'] + round($month_repay_money_f*$deal_info['repay_time'],2)) - $month_repay_money*($deal_info['repay_time']-1);
//                    $repay_data['self_money'] = $data['total_money'];
//                }
//                else{
//                    $repay_data['repay_money'] = $month_repay_money;
//                    $repay_data['self_money'] = 0;
//                }
//                $repay_data['raise_money'] = 0;
//                $repay_data['interest_money'] = $repay_data['repay_money']-$repay_data['self_money'];
//                $repay_data['repay_manage_money'] = 0;
//                $repay_data['loantype'] = $deal_info['loantype'];
//                $repay_data['has_repay'] = 0;
//                $repay_data['manage_money'] = 0;
//                $repay_data['reward_money'] = 0;
//                $repay_data['interestrate_money'] = 0; //加息券
//                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$repay_data,"INSERT");
//            }
//        }

		//dobid2_ok($deal_id,$GLOBALS['user_info']['id']);
		//替换上面注释掉的dobid2_ok()---只记录本人投资，不包含债券转让
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_sta WHERE user_id=".$GLOBALS['user_info']['id']) > 0) {
			$u_load = $GLOBALS['db']->getRow("SELECT count(*) as load_count,sum(total_money) as load_money FROM ".DB_PREFIX."deal_load WHERE user_id=".$GLOBALS['user_info']['id']." and is_repay= 0 ");
			$data_arr['load_count'] = $u_load['load_count'];//总借出笔数
			$data_arr['load_money'] = $u_load['load_money'];//总借出金额
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_sta",$data_arr,"UPDATE","user_id=".$GLOBALS['user_info']['id']);
		}else{
			$data_arr['user_id'] = $GLOBALS['user_info']['id'];
			$data_arr['load_count'] = 1;
			$data_arr['load_money'] = $data['total_money'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_sta",$data_arr,"INSERT");
		}
        /************出借成功后微信模板消息开始*********************/
        $wx_openid = $GLOBALS['db']->getOne("select wx_openid from ".DB_PREFIX."user where id=".$GLOBALS['user_info']['id']);
        if($wx_openid){
            if(app_conf('WEIXIN_TMPL')){
                $tmpl_url =app_conf('WEIXIN_TMPL_URL');
                $tmpl_datas = array();
                $tmpl_datas['first'] = '尊敬的用户，您已成功购买以下标的。';
                $tmpl_datas['keyword1'] = $deal_info['name'];
                $tmpl_datas['keyword2'] = $deal_info['rate'].'%';
                $tmpl_datas['keyword3'] = $deal_info['repay_time'].'个月';
                $tmpl_datas['keyword4'] = $bid_money.'元';
                $tmpl_datas['keyword5'] = sprintf("%.2f",($data['total_money'] * $root['deal']['repay_time'] * $root['deal']['rate'])/12/100);
                $tmpl_data = create_request_data('2',$wx_openid,app_conf('WEIXIN_JUMP_URL'),$tmpl_datas);
                $resl = request_curl($tmpl_url,$tmpl_data);

                $tmpl_msg['dest'] = $wx_openid;
                $tmpl_msg['send_type'] = 2;
                $tmpl_msg['content'] = serialize($tmpl_datas);
                $tmpl_msg['send_time'] = time();
                $tmpl_msg['create_time'] = time();
                $tmpl_msg['user_id'] = $GLOBALS['user_info']['id'];
                $tmpl_msg['title'] = '出借成功';
                if($resl=='true'){
                    $GLOBALS['db']->query("update ".DB_PREFIX."user set wx_openid_status=1 where id=".$GLOBALS['user_info']['id']);
                    $tmpl_msg['is_send'] = 1;
                    $tmpl_msg['result'] = '发送成功';
                    $tmpl_msg['is_success'] = 1;
                }else{
                    $tmpl_msg['is_send'] = 0;
                    $tmpl_msg['result'] = $resl['message'];
                    $tmpl_msg['is_success'] = 0;
                }
                $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_msg_list",$tmpl_msg,'INSERT','','SILENT');
            }
        }

        /************出借成功后微信模板消息结束*********************/

		if(app_conf('SMS_ON')==1 && app_conf('SMS_DEAL_LOAD')==1){
			//发送投标短信
			$load_tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_DEAL_LOAD'",false);
			$tmpl_content = $load_tmpl['content'];
			$notice['user_name'] = $GLOBALS['user_info']['user_name'];
			$notice['deal_name'] = $root['deal']['name'];
			$notice['money'] = number_format($bid_money);
			$notice['time'] = to_date(TIME_UTC,"Y年m月d日 H:i");
			$notice['site_name'] = app_conf("SHOP_TITLE");

			$GLOBALS['tmpl']->assign("notice",$notice);

			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $GLOBALS['user_info']['mobile'];
			$msg_data['send_type'] = 0;
			$msg_data['title'] = $root['deal']['name']."投标短信通知";
			$msg_data['content'] = addslashes($msg);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = TIME_UTC;
			$msg_data['user_id'] =  $GLOBALS['user_info']['id'];
			$msg_data['is_html'] = $load_tmpl['is_html'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
		}

		if($GLOBALS['user_info']['cust_key']!=""){
			//推送到人人厉害
			require_once APP_ROOT_PATH."/api/rrl/Rrleeapi.php";

			//通过用户出借后，接着时时把数据推送给人人利，数据格式如下
			$data_push[] = array(
				"Cust_key"=>$GLOBALS['user_info']['cust_key'],
				"User_name"=>$GLOBALS['user_info']['user_name'],
				"Order_no"=>$load_id,
				"Pro_name"=>$root['deal']['name'],
				"Pro_id"=>$root['deal']['id'],
				"Invest_money"=>$bid_money,
				"Invest_start_date"=>TIME_UTC."000",
				"Invest_end_date"=>0,
				"Back_money"=>0,
				"Rate"=>$root['deal']['rate'],
				"Back_last_date"=>0
				);

			$newapi = new Rrleeapi();
			$newapi->data_push($data_push);
		}
		$GLOBALS['db']->commit();
		/*$repay_time = $GLOBALS['db']->getOne("select repay_time from ".DB_PREFIX."deal where id = $deal_id");*/
		$uid = $GLOBALS['user_info']['id'];
		$pid = $GLOBALS['user_info']['pid'];
		if($pid){
			/*$res = set_invite_cash_red_packet($uid,$pid,$bid_money,$repay_time); //奖励一 发放*/
			$timer = check_register_time($uid); //注册时间小于15天
			if($timer){
				$firstLend = check_first_lend($uid); //验证是否首次投资
				if($firstLend){
					$res1 = set_invite_cash_red_packet_two($uid,$pid,$bid_money); //奖励二 发放
				}				
			}
			//验证出借人是否在当前月注册并投资  满足条件总数
			$count = check_register_lend_count($uid,$pid);
			if($count == 2){
				$is_grant=is_grant($pid,30); //是否已经发放
				if($is_grant){
					$res2 = set_invite_lend_red_packet($uid,$pid,30);
				}
			}else if($count == 4){
				$is_grant=is_grant($pid,70); //是否已经发放
				if($is_grant){
					$res2 = set_invite_lend_red_packet($uid,$pid,70);
				}
			}
		}
		//添加渠道来源
	
         $user_id = $GLOBALS['user_info']['id'];
		 addsource(0,0,$user_id,3,1); 

		$root["status"] = 1;//0:出错;1:正确;
		return $root;
	}else{
		$GLOBALS['db']->rollback();
		$root["show_err"] = $GLOBALS['lang']['ERROR_TITLE'];
		return $root;
	}
}

function dobid_app($map){
    $level=new Level();
    $deal_id = $map['deal_id'];
    $bid_money = $map['bid_money'];
    $is_pc = $map['is_pc'];
    $interestrate_id = $map['interestrate_id'];
    $interestrate_money = $map['interestrate_money'];
    $red_id = $map['red_id'];
    $learn_id = $map['learn_id'];
    $red_money = $map['red_money']?$map['red_money']:0;
    $total_money=$red_money+$bid_money;
    // 平台加息
    $deal_info=$GLOBALS['db']->getRow("select id,user_id,name,repay_time,interest_rate,repay_time_type,loantype,borrow_amount,load_money,buy_count,rate,debts,old_deal_id,old_load_id,repay_start_time,is_advance,is_new from ".DB_PREFIX."deal where id=".$deal_id);
    if($deal_info['interest_rate']){
        // 平台加息收益
        $increase_interest=get_jct_interest_money($deal_info,$total_money);
    }
    $cunguan_tag=$GLOBALS['db']->getOne("select cunguan_tag from ".DB_PREFIX."deal where id=".$deal_id);
    if(!$cunguan_tag){
        $root["show_err"] = "该标的不存在！";
        return $root;
    }
    // 债转标 此标的不允许原始发标人和自己进行出借
    if($deal_info['debts']==1){
        $old_deal_user=$GLOBALS['db']->getOne("select user_id from ".DB_PREFIX."deal where id=".$deal_info['old_deal_id']);
        if($GLOBALS['user_info']['id']==$old_deal_user || $GLOBALS['user_info']['id']==$deal_info['user_id']){
            $root["show_err"] = "此标的不允许原始发标人和自己进行出借！";
            return $root;
        }
    }

    if(empty($map['load_seqno'])&&empty($map['cunguan_tag'])){
        $root = check_dobid2($deal_id,$bid_money,$is_pc,$red_id,$learn_id,$red_money,$ecv_money=0,$interestrate_id,$use_interestrate=0);
        //   区别存管和非存管
        if($root["status"] == 1&&$cunguan_tag==1){
            $publics = new Publics();
            if($red_money>0){
                $xuni_seqno=$publics->seqno();
            }
            $load_seqno=$publics->seqno();
            $deal_load_data['xuni_seqno']=$xuni_seqno;
            $deal_load_data['load_seqno']=$load_seqno;
            $deal_load_data['deal_id']=$deal_id;
            $deal_load_data['user_id']=$GLOBALS['user_info']['id'];
            $deal_load_data['money']=$bid_money;
            $deal_load_data['total_money']=$bid_money+$red_money;
            $deal_load_data['red'] = $red_money;
            $deal_load_data['red_id'] = $red_id;
            $deal_load_data['interestrate_id'] = $interestrate_id;
            $deal_load_data['interestrate_money'] = $interestrate_money;
            $deal_load_data['add_ip'] = $_SERVER['REMOTE_ADDR'];
            $deal_load_data['cunguan_tag'] = $cunguan_tag;
            $deal_load_data['increase_interest'] = $increase_interest;
            $deal_load_data['create_date'] = date('Y-m-d',time());
            $deal_load_data['create_time'] = time();
            $deal_load_data['old_deal_id'] = $deal_info['old_deal_id']?$deal_info['old_deal_id']:'';
            $deal_load_data['debts'] = $deal_info['debts']?2:0;
            $res=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_temp",$deal_load_data,"INSERT");
            if(!$res){
                $root["show_err"] = "请稍后重试！";
                return $root;
            }
        }
        if ($root["status"] == 0){
            return $root;
        }
        elseif($root["status"] == 2){
            $root['jump'] = APP_ROOT."/index.php?ctl=collocation&act=RegisterCreditor&deal_id=$deal_id&user_id=".$GLOBALS['user_info']['id']."&bid_money=".$root['bid_money']."&bid_paypassword=$bid_paypassword"."&from=".$GLOBALS['request']['from']."&learn_id=".$learn_id."&ecv_id=".$ecv_id;
            $root['jump'] = str_replace("/mapi", "", SITE_DOMAIN.$root['jump']);
            return $root;
        }
    }
    // app 跳转第三方交易密码页面
    if($cunguan_tag==1 && empty($map['load_seqno'])){
        $root['url']=WAP_SITE_DOMAIN."/index.php?ctl=deal&act=cg_pass&load_seqno=".$load_seqno;
        return $root;
    }
    $root["status"] = 0;
    if($bid_money > $GLOBALS['user_info']['cunguan_money']){
        $root["show_err"] = $GLOBALS['lang']['MONEY_NOT_ENOUGHT'];//余额不足，无法投标
        return $root;
    }
    $data['user_id'] = $GLOBALS['user_info']['id'];
    $data['user_name'] = $GLOBALS['user_info']['user_name'];
    $data['deal_id'] = $deal_id;
    $data['money'] = $bid_money;
    $data['red'] = $red_money;
    $data['red_id'] = $red_id;
    $data['interestrate_id'] = $interestrate_id;
    $data['create_time']=time();
    $data['interestrate_money']=$interestrate_money;
    $data['total_money'] = $bid_money+$red_money;
    $data['add_ip'] = $_SERVER['REMOTE_ADDR'];
    $insertdata = return_deal_load_data($data,$GLOBALS['user_info'],$root['deal']);
    // 存管入库的字段判断
    if($map['load_seqno']&&$map['cunguan_tag']==1){
        $insertdata['cunguan_tag']=$map['cunguan_tag'];
        $insertdata['xuni_seqno']=$map['xuni_seqno'];
        $insertdata['load_seqno']=$map['load_seqno'];
        $insertdata['interestrate_money']=$map['interestrate_money'];
        $insertdata['increase_interest']=$map['increase_interest'];
        $insertdata['old_deal_id'] = $deal_info['old_deal_id']?$deal_info['old_deal_id']:'';
        $insertdata['debts'] = $deal_info['debts']?2:0;
    }
    $GLOBALS['db']->startTrans();   //开始事务
    $deal_con=$GLOBALS['db']->getRow("SELECT borrow_amount,load_money,buy_count FROM ".DB_PREFIX."deal where id=".$deal_id." FOR UPDATE");
    if(($deal_con['borrow_amount']-$deal_con['load_money'])<$data['total_money']){
        $GLOBALS['db']->rollback();
        $root["show_err"] = "出借总额大于可出借金额";
        return $root;
    }
    //判断封标奖励
    if($deal_info['is_advance'] != 1 && $deal_info['is_new'] != 1){
        if(($deal_con['borrow_amount']-$deal_con['load_money'])==$data['total_money']){
            SendSealedReward($data['user_id'],$bid_money);
        }
    }
    
    // $user_cg_info=get_cg_user_info($GLOBALS['user_info']['id']);
    // if($user_cg_info['assetamount']<$bid_money){
    //     $GLOBALS['db']->rollback();
    //     $root["show_err"] = "存管账户余额不足";
    //     return $root;
    // }
    //  出借金额折标后入库
    if($deal_info['debts'] == 1){
        $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$deal_info['old_deal_id']." order by repay_time desc limit 1");
        $debts_repay_time = ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24)+1;
        $data_arr['total_invite_invest_money']= round($debts_repay_time*$data['total_money']/365);
    }else{
        $data_arr['total_invite_invest_money']=round($data['total_money']*$deal_info['repay_time']/12);
    }
    if($GLOBALS['user_info']['pid']>0){
        $res3 = $GLOBALS['db']->query("update ".DB_PREFIX."user set total_invite_invest_money=total_invite_invest_money+".$data_arr['total_invite_invest_money']." where id=".$GLOBALS['user_info']['id']);
    }else{
        $res3 = true;
    }
    $is_get_reward = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_grow_point where user_id=".$GLOBALS['user_info']['pid']." and task_type=10");
    if(is_invite_load() && !$is_get_reward>0){
        // 邀请好友出借奖励成长值
        $result=$level->get_grow_point(10,$data_arr['total_invite_invest_money'],$GLOBALS['user_info']['pid']);
        if(!$result){
            $GLOBALS['db']->rollback();
            $root["show_err"] = "出借失败，请重试";
            return $root;
        }
    }
    // 出借满10次奖励成长值
    $is_get_reward = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_grow_point where user_id=".$GLOBALS['user_info']['id']." and task_type=15");
    $load_count=$GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal_load where user_id=".$GLOBALS['user_info']['id']);
    if(!$is_get_reward){

        $level->get_grow_point(15,$load_count+1);
    }
    // 是否当天注册
    if($deal_info['debts'] == 1){
        $param=round($data['total_money']*$debts_repay_time/365,2);
    }else{
        $param=round($data['total_money']*$deal_info['repay_time']/12,2);
    }
    if(date('Y-m-d',$GLOBALS['user_info']['create_time']) == date("Y-m-d",time())){
        $level->get_grow_point(17,$param);
    }else{
        $level->get_grow_point(16,$param);
    }
    // 是否首次出借
    if($load_count==0){
        $level->get_grow_point(22);
    }
    $new_load_money = $deal_con['load_money']+$data['total_money'];
    $buy_count = $deal_con['buy_count']+1;
    $res1 = $GLOBALS['db']->query("update ".DB_PREFIX."deal set load_money = ".intval($new_load_money).",buy_count = ".$buy_count." where id =".$deal_id);
    $res2 = $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$insertdata,"INSERT");
    $load_id = $GLOBALS['db']->insert_id();

    if($res1 && $res2 && $load_id && $res3){
        if($deal_info['borrow_amount']<$deal_info['load_money']){
            $GLOBALS['db']->rollback();
            $root["show_err"] = "出借总额大于可出借金额";
            return $root;
        }elseif(intval($deal_info['borrow_amount'])==intval($deal_info['load_money'])+$data['total_money']){
            if($deal_info['debts']==1){
                $GLOBALS['db']->query("update ".DB_PREFIX."deal set success_time = ".$insertdata['create_time'].",deal_status = 4 where id =".$deal_id);
            }else{
                $GLOBALS['db']->query("update ".DB_PREFIX."deal set success_time = ".$insertdata['create_time'].",deal_status = 2 where id =".$deal_id);
            }
        }
    }else{
        $GLOBALS['db']->rollback();
        $root["show_err"] = "出借失败,请重试！";
        return $root;
    }
    // 如果投的是债转标，投资成功时生成还款计划
    if(!empty($map['load_seqno'])&&$map['cunguan_tag']==1&&$deal_info['debts']==1){
        if($deal_info['loantype']==1){      //按月付息 到期还本
            $res=make_repay_plan_loantype1($data,$deal_info,$load_id);
        }elseif($deal_info['loantype']==0){     //等额本息
            $res=make_repay_plan_loantype0($data,$deal_info,$load_id);
        }
        if(!$res){
            $GLOBALS['db']->rollback();
            $root["show_err"] = "请稍后重试！";
            return $root;
        }
        //处理虚拟货币
        if(($red_money>0)&&$cunguan_tag==1&&!empty($map['xuni_seqno'])){
            require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
            $ideal_money=$red_money;
            $deal=new Deal;
            $status=$deal->deal($map["xuni_seqno"],'T10',$ideal_money,$deal_id,$GLOBALS['user_info']['id']);
            // print_r($status);die;
            if($status['respHeader']['respCode']!='P2P0000'){
                $GLOBALS['db']->rollback();
                $root["show_err"] = $status['respHeader']['respMsg'];
                return $root;
            }
        }
        // 投资成功后将资金打入转让人账户
        require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
        $deal=new Deal;
        $status=$deal->deal($map['load_seqno'],'T07',$total_money,$deal_id,$GLOBALS['user_info']['id']);
        $status['load_seqno']=$map['load_seqno'];
        if($status['respHeader']['respCode']!='P2P0000'){
            $GLOBALS['db']->rollback();
            $root["show_err"] = $status['respHeader']['respMsg'];
            return $root;
        }
        // 转让方资金增加
        require_once APP_ROOT_PATH."system/libs/user.php";
        $brief="转让成功";
        $deal_name=$GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."deal where id=".$deal_id);
        $msg="[<a href='/index.php?ctl=deal&id=".$deal_id."' target='_blank'>".$deal_name."</a>]的转让";
        $debts['cunguan_money']=$total_money;
        $debts['cunguan_lock_money']=-$total_money;
        modify_account($debts,$deal_info['user_id'],$msg,62,$brief,$cunguan_tag);

    }else{
        /// 如果不是债转标的
        // 处理虚拟货币，先充值
        if(($red_money>0)&&$cunguan_tag==1&&!empty($map['xuni_seqno'])){
            require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
            $ideal_money=$red_money;
            $deal=new Deal;
            $status=$deal->deal($map["xuni_seqno"],'T10',$ideal_money,$deal_id,$GLOBALS['user_info']['id']);
            // print_r($status);die;
            if($status['respHeader']['respCode']!='P2P0000'){
                $GLOBALS['db']->rollback();
                $root["show_err"] = $status['respHeader']['respMsg'];
                return $root;
            }
        }

        // 存管资金投资
        if($load_id > 0&&!empty($map['load_seqno'])){
            require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
            $deal=new Deal;
            $status=$deal->deal($map['load_seqno'],'T01',$total_money,$deal_id,$GLOBALS['user_info']['id']);
            if($status['respHeader']['respCode']!='P2P0000'){
                $GLOBALS['db']->rollback();
                $root["show_err"] = $status['respHeader']['respMsg'];
                return $root;
            }
        }
    }

    if($load_id > 0){
        $data['load_id'] = $load_id;
        //更改红包状态
        if($red_id){
            $GLOBALS['db']->query("UPDATE ".DB_PREFIX."red_packet SET status=1,deal_id =".$deal_id.",deal_load_id =".$load_id." WHERE user_id=".$GLOBALS['user_info']['id']." and id in(".$red_id.")");
        }

        //更改加息卡状态
        if($interestrate_id > 0){
            $GLOBALS['db']->query("UPDATE ".DB_PREFIX."interest_card SET status=1,deal_id =".$deal_id.",deal_load_id =".$load_id." WHERE user_id=".$GLOBALS['user_info']['id']." and id=".$interestrate_id);
        }
        /*
                //更改体验金状态
                if($learn_id > 0){
                    $now_time = to_date(TIME_UTC,"Y-m-d H:i:s");
                    $today = to_date(TIME_UTC,"Y-m-d");
                    $GLOBALS['db']->query("update ".DB_PREFIX."learn_send_list set is_use = '1',use_time ='".$now_time."',use_date='".$today."' where is_use = '0' and id ='".$learn_id."' and  user_id = ".intval($GLOBALS['user_info']['id'])." ");
                }
                */
        if($bid_money > 0){
            $deal_name=$GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."deal where id=".$deal_id);
//                $msg = '[<a href="'.$root['deal']['url'].'" target="_blank">'.$root['deal']['name'].'</a>]的出借';
            $msg="[<a href='/index.php?ctl=deal&id=".$deal_id."' target='_blank'>".$deal_name."</a>]的出借";
            $brief = '出借成功';
            require_once APP_ROOT_PATH."system/libs/user.php";
            $data['cunguan_money'] = -($data['total_money']);

            $data['cunguan_lock_money'] = $data['total_money'];
            if($red_money){
                $data['red_money'] = -$red_money;
                red_modify_account($data,$GLOBALS['user_info']['id'],$msg.',使用红包',$cunguan_tag);
            }
            unset($data['money']);
            modify_account($data,$GLOBALS['user_info']['id'],$msg,2,$brief,$cunguan_tag);
            $decository['status']=1;
            $result=$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$decository,"UPDATE","seqno='".$map['load_seqno']."'");
            if($map['xuni_seqno']){
                $res=$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$decository,"UPDATE","seqno='".$map['xuni_seqno']."'");
            }


        }
        //   存管标满标后后台放款时生成
//        if(empty($map['seqno'])&&$map['cunguan_tag']!=1){
//            //生成用户回款计划
//            //月还利息
//            $month_repay_money_f = av_it_formula($data ['total_money'],$deal_info['rate']/12/100);
//            //月还利息--精确到小数点后两位
//            $month_repay_money = round($month_repay_money_f,2);
//            for($i=0;$i<$deal_info['repay_time'];$i++){
//                $repay_data['u_key'] = $deal_info['buy_count']-1;
//                $repay_data['l_key'] = $i;
//                $repay_data['deal_id'] = $deal_id;
//                $repay_data['load_id'] = $load_id;
//                $repay_data['repay_id'] = 0;
//                $repay_data['t_user_id'] = 0;
//                $repay_data['user_id'] = $GLOBALS['user_info']['id'];
//                $repay_data['repay_time'] = strtotime("+" . $i+1 . " months", $insertdata['create_time']);
//                $repay_data['repay_date'] = to_date($repay_data['repay_time']);
//                if($i+1 == $deal_info['repay_time']){
//                    $repay_data['repay_money'] = ($data['total_money'] + round($month_repay_money_f*$deal_info['repay_time'],2)) - $month_repay_money*($deal_info['repay_time']-1);
//                    $repay_data['self_money'] = $data['total_money'];
//                }
//                else{
//                    $repay_data['repay_money'] = $month_repay_money;
//                    $repay_data['self_money'] = 0;
//                }
//                $repay_data['raise_money'] = 0;
//                $repay_data['interest_money'] = $repay_data['repay_money']-$repay_data['self_money'];
//                $repay_data['repay_manage_money'] = 0;
//                $repay_data['loantype'] = $deal_info['loantype'];
//                $repay_data['has_repay'] = 0;
//                $repay_data['manage_money'] = 0;
//                $repay_data['reward_money'] = 0;
//                $repay_data['interestrate_money'] = 0; //加息券
//                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$repay_data,"INSERT");
//            }
//        }

        //dobid2_ok($deal_id,$GLOBALS['user_info']['id']);
        //替换上面注释掉的dobid2_ok()---只记录本人投资，不包含债券转让
        if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_sta WHERE user_id=".$GLOBALS['user_info']['id']) > 0) {
            $u_load = $GLOBALS['db']->getRow("SELECT count(*) as load_count,sum(total_money) as load_money FROM ".DB_PREFIX."deal_load WHERE user_id=".$GLOBALS['user_info']['id']." and is_repay= 0 ");
            $data_arr['load_count'] = $u_load['load_count'];//总借出笔数
            $data_arr['load_money'] = $u_load['load_money'];//总借出金额
            $GLOBALS['db']->autoExecute(DB_PREFIX."user_sta",$data_arr,"UPDATE","user_id=".$GLOBALS['user_info']['id']);
        }else{
            $data_arr['user_id'] = $GLOBALS['user_info']['id'];
            $data_arr['load_count'] = 1;
            $data_arr['load_money'] = $data['total_money'];
            $GLOBALS['db']->autoExecute(DB_PREFIX."user_sta",$data_arr,"INSERT");
        }
        /************出借成功后微信模板消息开始*********************/
        $wx_openid = $GLOBALS['db']->getOne("select wx_openid from ".DB_PREFIX."user where id=".$GLOBALS['user_info']['id']);
        if($wx_openid){
            if(app_conf('WEIXIN_TMPL')){
                $tmpl_url =app_conf('WEIXIN_TMPL_URL');
                $tmpl_datas = array();
                $tmpl_datas['first'] = '尊敬的用户，您已成功购买以下标的。';
                $tmpl_datas['keyword1'] = $deal_info['name'];
                $tmpl_datas['keyword2'] = $deal_info['rate'].'%';
                $tmpl_datas['keyword3'] = $deal_info['repay_time'].'个月';
                $tmpl_datas['keyword4'] = $bid_money.'元';
                $tmpl_datas['keyword5'] = sprintf("%.2f",($data['total_money'] * $deal_info['repay_time'] * $deal_info['rate'])/12/100);
                $tmpl_data = create_request_data('2',$wx_openid,app_conf('WEIXIN_JUMP_URL'),$tmpl_datas);
                $resl = request_curl($tmpl_url,$tmpl_data);

                $tmpl_msg['dest'] = $wx_openid;
                $tmpl_msg['send_type'] = 2;
                $tmpl_msg['content'] = serialize($tmpl_datas);
                $tmpl_msg['send_time'] = time();
                $tmpl_msg['create_time'] = time();
                $tmpl_msg['user_id'] = $GLOBALS['user_info']['id'];
                $tmpl_msg['title'] = '出借成功';
                if($resl=='true'){
                    $GLOBALS['db']->query("update ".DB_PREFIX."user set wx_openid_status=1 where id=".$GLOBALS['user_info']['id']);
                    $tmpl_msg['is_send'] = 1;
                    $tmpl_msg['result'] = '发送成功';
                    $tmpl_msg['is_success'] = 1;
                }else{
                    $tmpl_msg['is_send'] = 0;
                    $tmpl_msg['result'] = $resl['message'];
                    $tmpl_msg['is_success'] = 0;
                }
                $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_msg_list",$tmpl_msg,'INSERT','','SILENT');
            }
        }

        /************出借成功后微信模板消息结束*********************/

        if(app_conf('SMS_ON')==1 && app_conf('SMS_DEAL_LOAD')==1){
            //发送投标短信
            $load_tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_DEAL_LOAD'",false);
            $tmpl_content = $load_tmpl['content'];
            $notice['user_name'] = $GLOBALS['user_info']['user_name'];
            $notice['deal_name'] = $root['deal']['name'];
            $notice['money'] = number_format($bid_money);
            $notice['time'] = to_date(TIME_UTC,"Y年m月d日 H:i");
            $notice['site_name'] = app_conf("SHOP_TITLE");

            $GLOBALS['tmpl']->assign("notice",$notice);

            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
            $msg_data['dest'] = $GLOBALS['user_info']['mobile'];
            $msg_data['send_type'] = 0;
            $msg_data['title'] = $root['deal']['name']."投标短信通知";
            $msg_data['content'] = addslashes($msg);
            $msg_data['send_time'] = 0;
            $msg_data['is_send'] = 0;
            $msg_data['create_time'] = TIME_UTC;
            $msg_data['user_id'] =  $GLOBALS['user_info']['id'];
            $msg_data['is_html'] = $load_tmpl['is_html'];
            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
        }

        if($GLOBALS['user_info']['cust_key']!=""){
            //推送到人人厉害
            require_once APP_ROOT_PATH."/api/rrl/Rrleeapi.php";

            //通过用户出借后，接着时时把数据推送给人人利，数据格式如下
            $data_push[] = array(
                "Cust_key"=>$GLOBALS['user_info']['cust_key'],
                "User_name"=>$GLOBALS['user_info']['user_name'],
                "Order_no"=>$load_id,
                "Pro_name"=>$root['deal']['name'],
                "Pro_id"=>$root['deal']['id'],
                "Invest_money"=>$bid_money,
                "Invest_start_date"=>TIME_UTC."000",
                "Invest_end_date"=>0,
                "Back_money"=>0,
                "Rate"=>$root['deal']['rate'],
                "Back_last_date"=>0
            );

            $newapi = new Rrleeapi();
            $newapi->data_push($data_push);
        }
        $GLOBALS['db']->commit();
        /*$repay_time = $GLOBALS['db']->getOne("select repay_time from ".DB_PREFIX."deal where id = $deal_id");*/
        $uid = $GLOBALS['user_info']['id'];
        $pid = $GLOBALS['user_info']['pid'];
        if($pid){
            /*$res = set_invite_cash_red_packet($uid,$pid,$bid_money,$repay_time); //奖励一 发放*/
            $timer = check_register_time($uid); //注册时间小于15天
            if($timer){
                $firstLend = check_first_lend($uid); //验证是否首次投资
                if($firstLend){
                    $res1 = set_invite_cash_red_packet_two($uid,$pid,$bid_money); //奖励二 发放
                }
            }
            //验证出借人是否在当前月注册并投资  满足条件总数
            $count = check_register_lend_count($uid,$pid);
            if($count == 2){
                $is_grant=is_grant($pid,30); //是否已经发放
                if($is_grant){
                    $res2 = set_invite_lend_red_packet($uid,$pid,30);
                }
            }else if($count == 4){
                $is_grant=is_grant($pid,70); //是否已经发放
                if($is_grant){
                    $res2 = set_invite_lend_red_packet($uid,$pid,70);
                }
            }
        }
        //添加渠道来源

        $user_id = $GLOBALS['user_info']['id'];
        addsource(0,0,$user_id,3,1);
        $root["status"] = 1;//0:出错;1:正确;
        return $root;
    }else{
        $GLOBALS['db']->rollback();
        $root["show_err"] = $GLOBALS['lang']['ERROR_TITLE'];
        return $root;
    }
}
/*
 * 自动投标
 */
function auto_dobid2(){
    $level=new Level();
    $switch = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "switch_conf  where switch_id = 1 and status = 1"); //总开关
    $switch2 = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "switch_conf  where switch_id = 7 and status = 1");  //出借开关
    if(empty($switch)||empty($switch2)){
        die; //系统正在升级，请稍后再试
    }
    $deal_id = intval($_REQUEST['deal_id']);
    // 标的信息
    $deal_info=$GLOBALS['db']->getRow("select id,loantype,interest_rate,objectaccno,user_id,is_new,max_loan_money,borrow_amount,min_loan_money,repay_time,rate,load_money,deal_status,is_advance,debts,old_deal_id,cunguan_tag from ".DB_PREFIX."deal where id=".$deal_id." and cunguan_tag=1");
    if(!$deal_info){
         // 标的信息不存在
    }

    if($deal_info['debts']==1){
        $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$deal_info['old_deal_id']." order by repay_time desc limit 1");
        $deal_info['repay_time_day']= ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24)+1;
        $deal_info['repay_time']= round($deal_info['repay_time_day']/30,2);
    }
    // 判断是否为普通标
    $condition = '';
    if($deal_info['is_new']==0 && $deal_info['is_advance']==0 && $deal_info['debts']==0){
        $condition .= " and is_ordinary = 1 ";
    }else{
        $is_ordinary=0;
    }
    if($deal_info['is_new'] == 1){
        $condition .= " and is_new = 1 ";
    }
    if($deal_info['is_advance'] == 1){
        $condition .= " and is_advance = 1 ";
    }
    if($deal_info['debts'] == 1){
        $condition .= " and is_debts = 1 ";
    }
    // 查询符合此标的自动投标配置
    $auto_deal_list=$GLOBALS['db']->getAll("select id,is_part_load,user_id,money,deadline_start,deadline_end from ".DB_PREFIX."auto_invest_config where is_delete=0 and status = 1 ".$condition."and end_time>".time()." and deadline_start<=".$deal_info['repay_time']." and deadline_end >=".$deal_info['repay_time']." order by update_time asc");
    foreach($auto_deal_list as $k=>$v){

        if($v['user_id']){
            $update_time['update_time']=time();
            $GLOBALS['db']->autoExecute(DB_PREFIX."auto_invest_config",$update_time,"UPDATE","id=".$v['id']);
        }
        $deal_load_count = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_load  WHERE user_id=".$v['user_id']);
        if($deal_info['is_new']==1 && $deal_load_count > 0){
            auto_load_log($v['user_id'],$deal_id,$v['id'],"此标为新手专享，只有新手才可以出借哦");
            continue; // 配置金额不能为空
        }
        // 该用户的账户余额
        $user_info=$GLOBALS['db']->getRow("select pid,id,user_name,cunguan_pwd,cunguan_tag,AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as cunguan_money from ".DB_PREFIX."user where id=".$v['user_id']);

        if(!$user_info){
            auto_load_log($v['user_id'],$deal_id,$v['id'],"用户不存在");
            continue; // 该用户不存在
        }
        if(empty($v['money']) && $v['money']==0){
            auto_load_log($v['user_id'],$deal_id,$v['id'],"配置金额不能为空");
            continue; // 配置金额不能为空
        }
        if($user_info['cunguan_tag']!=1){
            auto_load_log($v['user_id'],$deal_id,$v['id'],"用户为非存管用户");
            continue;// 该用户为非存管用户
        }
        if($user_info['cunguan_pwd']!=1){
            auto_load_log($v['user_id'],$deal_id,$v['id'],"用户未设置存管交易密码");
            continue; // 该用户未开通存管交易密码
        }
        $cg_bank=$GLOBALS['db']->getOne("select bankcard from ".DB_PREFIX."user_bank where user_id=".$v['user_id']." and cunguan_tag=1");
        if(!$cg_bank){
            auto_load_log($v['user_id'],$deal_id,$v['id'],"该用户未绑定存管银行卡");
            continue;// 该用户未绑定存管银行卡
        }
        if($user_info['cunguan_money']<$deal_info['min_loan_money']){
            auto_load_log($v['user_id'],$deal_id,$v['id'],"账户余额不足以投标");
            continue;// 账户余额不足以投标
        }
        if($v['money'] < $deal_info['min_loan_money']){
            auto_load_log($v['user_id'],$deal_id,$v['id'],"自动投标金额小于该标的最低限制");
            continue;// 账户余额不足以投标
        }
        if($deal_info['user_id'] == $v['user_id']){
            auto_load_log($v['user_id'],$deal_id,$v['id'],"不能投自己发放的标的");
            continue;// 不能投自己发放的标的
        }
        // 剩余可投金额
        $deal_load=$GLOBALS['db']->getRow("select borrow_amount,load_money from ".DB_PREFIX."deal where id=".$deal_id);
        $residue_load_money=$deal_load['borrow_amount']-$deal_load['load_money'];
        if($residue_load_money == 0){
            auto_load_log($v['user_id'],$deal_id,$v['id'],"此标的已满");
            continue;// 此标的已满
        }
        //投资金额的判断
        if($v['is_part_load'] == 0){    // 未开启部分中标
            // 可用余额>=最低出借限额  && 可用余额>=剩余可投金额 && 自动投标金额>=剩余可投金额 && (最大出借限额 >= 剩余可投金额 || 无上限)   取剩余可投
            if($user_info['cunguan_money']>=$deal_info['min_loan_money'] && $user_info['cunguan_money']>=$residue_load_money && $v['money']>=$residue_load_money && ($deal_info['max_loan_money'] >= $residue_load_money || $deal_info['max_loan_money'] == 0)){
                $bid_money=intval($residue_load_money);
            }
            // 可用余额>=最低出借限额  && 剩余可投金额>=可用余额 && 自动投标金额>=可用余额 && (最大出借限额 >= 可用余额 || 无上限)    取可用余额
            if($user_info['cunguan_money']>=$deal_info['min_loan_money'] && $residue_load_money>=$user_info['cunguan_money'] && $v['money']>=$user_info['cunguan_money'] && ($deal_info['max_loan_money']>=$user_info['cunguan_money'] || $user_info['cunguan_money']==0)){
                $bid_money=floor($user_info['cunguan_money']);
            }
            // 可用余额>=自动投标金额 && 剩余可投金额>=自动投标金额 && 自动投标金额>=最低出借限额 && (最大出借限额 >= 自动投标金额 || 无上限)    取自动投标金额
            if($user_info['cunguan_money']>=$v['money'] && $residue_load_money>=$v['money'] && $v['money'] >= $deal_info['min_loan_money'] && ($deal_info['max_loan_money']>=$v['money'] || $deal_info['max_loan_money'] == 0)){
                $bid_money=intval($v['money']);
            }
            // 可用余额>=最大出借限额 && 自动投标金额>=最大出借限额 && 自动投标金额>=最大出借限额 && 最大出借限额存在   去标的的最大出借限额
            if($user_info['cunguan_money']>=$deal_info['max_loan_money'] && $residue_load_money>=$deal_info['max_loan_money'] && $v['money']>=$deal_info['max_loan_money'] && $deal_info['max_loan_money']>$deal_info['min_loan_money']){
                $bid_money=intval($deal_info['max_loan_money']);
            }
        }else{    // 开启部分中标
            if($v['money']>$user_info['cunguan_money'] || ($v['money']>$deal_info['max_loan_money'] && $deal_info['max_loan_money']>0) || $v['money']>$residue_load_money){
                auto_load_log($v['user_id'],$deal_id,$v['id'],"该标的与自动投标配置金额不匹配");
                continue;// 该标的与自动投标配置金额不匹配
            }else{
                $bid_money=intval($v['money']);
            }
        }

        // 平台加息收益
        if($deal_info['interest_rate']){
            $increase_interest=get_jct_interest_money($deal_info,$bid_money);
        }
        $publics = new Publics();
        $data['money'] = $bid_money;
        $red_money=0;
        $data['user_id'] = $user_info['id'];
        $data['user_name'] = $user_info['user_name'];
        $data['deal_id'] = $deal_id;
        $data['create_time']=time();
        $data['total_money'] = $bid_money+$red_money;
        $data['add_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['cunguan_tag']=1;
        $data['load_seqno']=$publics->seqno();
        if(isset($red_money) && $red_money>0){
            $data['xuni_seqno']=$publics->seqno();
        }
        $data['increase_interest']=$increase_interest;   //平台加息
        $data['old_deal_id'] = $deal_info['old_deal_id']?$deal_info['old_deal_id']:'';
        $data['debts'] = $deal_info['debts']?2:0;
        $data['is_auto'] = 1;
        $data['is_part_load'] = $v['is_part_load'];

        if($residue_load_money<$data['total_money']){
            auto_load_log($v['user_id'],$deal_id,$v['id'],"出借总额大于可出借金额");
            continue;// 出借总额大于可出借金额
        }
        $GLOBALS['db']->startTrans();   //开始事务
        $deal_info=$GLOBALS['db']->getRow("select id,loantype,old_load_id,interest_rate,objectaccno,user_id,is_new,borrow_amount,min_loan_money,max_loan_money,repay_time,rate,load_money,deal_status,is_advance,debts,old_deal_id,cunguan_tag from ".DB_PREFIX."deal where id=".$deal_id." and cunguan_tag=1 FOR UPDATE");
        //  出借金额折标后入库
        if($deal_info['debts'] == 1){
            $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$deal_info['old_deal_id']." order by repay_time desc limit 1");
            $debts_repay_time = ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24)+1;
            $data_arr['total_invite_invest_money']= round($debts_repay_time*$data['total_money']/365);
        }else{
            $data_arr['total_invite_invest_money']=round($data['total_money']*$deal_info['repay_time']/12);
        }
        if($user_info['pid']>0){
            $res3 = $GLOBALS['db']->query("update ".DB_PREFIX."user set total_invite_invest_money=total_invite_invest_money+".$data_arr['total_invite_invest_money']." where id=".$v['user_id']);
        }else{
            $res3 = true;
        }
        $invite_grow_start=strtotime(date('Y-m',time()));
        if(date('m-d',time())=='02-28' || date('m-d',time())=='02-29' || date('m-d',time())=='03-31' || date('m-d',time())=='05-31' || date('m-d',time())=='08-31' || date('m-d',time())=='10-31'){
            $invite_grow_end=strtotime(date('Y-m',strtotime(date('Y-m',time())." +5 days")));
        }else{
            $invite_grow_end=strtotime(date('Y-m',strtotime(date('Y-m',time())." +1 month")));
        }

        $is_get_reward = $GLOBALS['db']->getOne("select count(grow_point) from ".DB_PREFIX."user_grow_point where user_id=".$user_info['pid']." and task_type=10 and create_time>".$invite_grow_start." and create_time<".$invite_grow_end);
        if(is_invite_load() && $is_get_reward<200){
            // 邀请好友出借奖励成长值  每月上限200
            $result=$level->get_grow_point(10,$data_arr['total_invite_invest_money'],$user_info['pid']);
            if(!$result){
                $GLOBALS['db']->rollback();
                continue;
            }
        }
        // 出借满10次奖励成长值
        $is_get_reward = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_grow_point where user_id=".$user_info['id']." and task_type=15");
        $load_count=$GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal_load where user_id=".$user_info['id']);
        if(!$is_get_reward){
            $level->get_grow_point(15,$load_count+1);
        }
        // 是否当天注册
        if($deal_info['debts'] == 1){
            $param=round($data['total_money']*$debts_repay_time/365,2);
        }else{
            $param=round($data['total_money']*$deal_info['repay_time']/12,2);
        }
        if(date('Y-m-d',$GLOBALS['user_info']['create_time']) == date("Y-m-d",time())){
            $level->get_grow_point(17,$param);
        }else{
            $level->get_grow_point(16,$param);
        }
        // 是否首次出借
        if($load_count==0){
            $level->get_grow_point(22);
        }
        $new_load_money = $deal_info['load_money']+$data['total_money'];
        $buy_count = $deal_info['buy_count']+1;
        $res1 = $GLOBALS['db']->query("update ".DB_PREFIX."deal set load_money = ".intval($new_load_money).",buy_count = ".$buy_count." where id =".$deal_id);
        $res2 = $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$data,"INSERT");
        $load_id = $GLOBALS['db']->insert_id();
        if($res1 && $res2 && $load_id && $res3){
            if($deal_info['borrow_amount']<$deal_info['load_money']){
                $GLOBALS['db']->rollback();  // 出借总额大于可出借金额
                continue;
            }elseif(intval($deal_info['borrow_amount'])==intval($new_load_money)){
                if($deal_info['debts']==1){
                    $GLOBALS['db']->query("update ".DB_PREFIX."deal set success_time = ".$data['create_time'].",deal_status = 4 where id =".$deal_id);
                }else{
                    $GLOBALS['db']->query("update ".DB_PREFIX."deal set success_time = ".$data['create_time'].",deal_status = 2 where id =".$deal_id);
                }
            }
        }else{
            $GLOBALS['db']->rollback();   // 出借失败
            continue;
        }
        // 如果投的是债转标，投资成功时生成还款计划
        if($deal_info['cunguan_tag']==1&&$deal_info['debts']==1){
            if($deal_info['loantype']==1){      //按月付息 到期还本
                $res=make_repay_plan_loantype1($data,$deal_info,$load_id);
            }elseif($deal_info['loantype']==0){     //等额本息
                $res=make_repay_plan_loantype0($data,$deal_info,$load_id);
            }
            if(!$res){
                $GLOBALS['db']->rollback();//生成还款计划失败
                continue;
            }
            // 投资成功后将资金打入转让人账户
//            require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
//            $deal=new Deal;
//            $status=$deal->deal($data['load_seqno'],'T07',$data['total_money'],$deal_id,$v['user_id']);
//            $status['load_seqno']=$data['load_seqno'];
//            if($status['respHeader']['respCode']!='P2P0000'){
//                $GLOBALS['db']->rollback();
//                $root["show_err"] = $status['respHeader']['respMsg'];
//                auto_load_log($v['user_id'],$deal_id,$v['id'],$root["show_err"]);
//                continue;
//            }
            // 转让方资金增加
            require_once APP_ROOT_PATH."system/libs/user.php";
            $brief="转让成功";
            $deal_name=$GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."deal where id=".$deal_id);
            $msg="[<a href='/index.php?ctl=deal&id=".$deal_id."' target='_blank'>".$deal_name."</a>]的转让";
            $debts['cunguan_money']=$data['total_money'];
            $debts['cunguan_lock_money']=-$data['total_money'];
            modify_account($debts,$deal_info['user_id'],$msg,62,$brief,1);

        }
        $GLOBALS['db']->commit();
//        else{
//            /// 如果不是债转标的
//            // 处理虚拟货币，先充值
//            if(($red_money>0)&&$deal_info['cunguan_tag']==1&&!empty($data['xuni_seqno'])){
//                require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
//                $ideal_money=$red_money;
//                $deal=new Deal;
//                $status=$deal->deal($data["xuni_seqno"],'T10',$ideal_money,$deal_id,$v['user_id']);
//                // print_r($status);die;
//                if($status['respHeader']['respCode']!='P2P0000'){
//                    $GLOBALS['db']->rollback();
//                    auto_load_log($v['user_id'],$deal_id,$v['id'],$root["show_err"]);
//                }
//            }
//
//            // 存管资金投资
//            if($load_id > 0&&!empty($data['load_seqno'])){
//                require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
//                $deal=new Deal;
//                $status=$deal->deal($data['load_seqno'],'T01',$data['total_money'],$deal_id,$v['user_id']);
//                if($status['respHeader']['respCode']!='P2P0000'){
//                    $GLOBALS['db']->rollback();
//                    auto_load_log($v['user_id'],$deal_id,$v['id'],$root["show_err"]);
//                }
//            }
//        }
        if($bid_money > 0){

           

            require_once APP_ROOT_PATH."system/libs/user.php";
            $deal_name=$GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."deal where id=".$deal_id);
            $msg="[<a href='/index.php?ctl=deal&id=".$deal_id."' target='_blank'>".$deal_name."</a>]的出借";
            $brief = '出借成功';
            if($red_money){
                $data['red_money'] = -$red_money;
                red_modify_account($data,$v['user_id'],$msg.',使用红包',1);
            }
            unset($data['money']);
            $data['cunguan_money']=-$bid_money;
            modify_account($data,$v['user_id'],$msg,2,$brief,1);
            $decository['status']=1;
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$decository,"UPDATE","seqno='".$data['load_seqno']."'");
            if($data['xuni_seqno']){
                $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$decository,"UPDATE","seqno='".$data['xuni_seqno']."'");
            }

             // 给用户发送短信通知
            if(app_conf("SMS_ON")==1)
            {
                $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$data['user_id']);
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

        }
    }

}
// 自动投标日志
function auto_load_log($user_id,$deal_id,$auto_id,$msg,$true_load_money=0){
    $data['user_id']=$user_id;
    $data['deal_id']=$deal_id;
    $data['auto_id']=$auto_id;
    $data['msg']=$msg;
    $GLOBALS['db']->autoExecute(DB_PREFIX."auto_load_log",$data,"INSERT");
}
// 是否满足复投    每月1号跑一次脚本
function is_repeat_load(){
    $level=new level();
	$repeat_start_time=strtotime(date('Y-m',strtotime(date('Y-m',time())." -2 days")));
	$repeat_end_time=strtotime(date('Y-m',time()));
    $repeat_load_user=$GLOBALS['db']->getAll("select user_id,sum(repay_money) as sum_repay_money,min(true_repay_time) as min_repay_time,sum(increase_interest) as increase_interest,sum(interestrate_money) as interestrate_money from ".DB_PREFIX."deal_load_repay where true_repay_time>$repeat_start_time and true_repay_time<$repeat_end_time group by user_id");
    foreach($repeat_load_user as $k=>$v){
        $GLOBALS['db']->startTrans();
        $is_has_get=$GLOBALS['db']->getOne("select grow_point from ".DB_PREFIX."user_grow_point where task_type=18 and user_id=".$v['user_id']." and create_time>$repeat_end_time and create_time<".time()." FOR UPDATE");
        if(isset($is_has_get) && $is_has_get>0){
            $GLOBALS['db']->rollback();
            continue;
        }
        $user_load_money=$GLOBALS['db']->getOne("select sum(dl.total_money*d.repay_time/12) from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal d on dl.deal_id=d.id where user_id=".$v['user_id']." and create_time>".$v['min_repay_time']." and create_time<".$repeat_end_time);
        if($user_load_money>$v['sum_repay_money']){
            $level=new level();
            $level->get_grow_point(18,$user_load_money);
        }
        $GLOBALS['db']->commit();
    }

	// 是否有本月还本息的标的
}
// 判断 是否满足邀请好友 出借1000以上（折标后）
function is_invite_load(){
    if(!isset($GLOBALS['user_info']['referer']) || empty($GLOBALS['user_info']['referer'])){
        return false;  // 邀请人不存在
    }
    //  上线时间  1504234552（测试）
    $uptime=1504234552;
    if(empty($GLOBALS['user_info']['referer_time']) || $GLOBALS['user_info']['referer_time']< $uptime){
        return false;  // 上线之后邀请生效
    }
    $is_get_reward=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_grow_point where user_id=".$GLOBALS['user_info']['referer']." and task_type=10");
    if(isset($is_get_reward) && $is_get_reward>0){
        return false;   //已经发放过奖励
    }
    return true;
//    $total_invite_invest_money=$GLOBALS['user_info']['total_invite_invest_money'];
//    if($total_invite_invest_money<1000){
//        return false;
//    }else{
//        return true;
//    }




}
function get_transfer($union_sql,$condition){

    $sql = 'SELECT dlt.id,dlt.transfer_amount,dlt.near_repay_time,dlt.user_id,d.loantype,d.next_repay_time,d.last_repay_time,d.rate,d.repay_start_time,d.repay_time,dlt.load_money,d.name as deal_name,dlt.load_money,dlt.id as dltid,dlt.status as tras_status,dlt.t_user_id,dlt.transfer_amount,dlt.create_time as tras_create_time,dlt.transfer_time,dlt.load_id,d.tcontract_id,d.user_load_transfer_fee,d.user_id as duser_id FROM '.DB_PREFIX.'deal_load dl LEFT JOIN '.DB_PREFIX.'deal d ON d.id = dl.deal_id '.$union_sql.' WHERE 1=1 '.$condition;

    $transfer = $GLOBALS['db']->getRow($sql);

    if($transfer){
        //下个还款日
        $transfer['next_repay_time_format'] = to_date($transfer['next_repay_time'],"Y-m-d");
        $transfer['near_repay_time_format'] = to_date(next_replay_month($transfer['near_repay_time']," -1 "),"Y 年 m 月 d 日");

        //什么时候开始借
        $transfer['repay_start_time_format']  = to_date($transfer['repay_start_time'],"Y 年 m 月 d 日");

        //还款日
        $transfer['final_repay_time'] = next_replay_month($transfer['repay_start_time'],$transfer['repay_time']);
        $transfer['final_repay_time_format'] = to_date($transfer['final_repay_time'],"Y-m-d");
        //剩余期数
        if(is_last_repay($transfer['loantype'])==1)
            $transfer['how_much_month'] = $transfer['repay_time'];
        else
            $transfer['how_much_month'] = how_much_month($transfer['near_repay_time'],$transfer['final_repay_time']);

        if(is_last_repay($transfer['loantype'])==2){
            $transfer['how_much_month'] =  $transfer['how_much_month'] / 3 ;
        }

        $transfer_rs = deal_transfer($transfer);

        $transfer['month_repay_money'] = $transfer_rs['month_repay_money'];
        $transfer['all_must_repay_money'] = $transfer_rs['all_must_repay_money'];
        $transfer['left_benjin'] = $transfer_rs['left_benjin'];

        $transfer['month_repay_money_format'] = format_price($transfer['month_repay_money']);

        $transfer['all_must_repay_money_format'] = format_price($transfer['all_must_repay_money']);

        $transfer['left_benjin_format'] = format_price($transfer['left_benjin']);
        //剩多少利息
        $transfer['left_lixi'] = $transfer['all_must_repay_money'] - $transfer['left_benjin'];
        $transfer['left_lixi_format'] = format_price($transfer['left_lixi']);

        //转让价格
        $transfer['transfer_amount_format'] =  format_price($transfer['transfer_amount']);

        //投标价格
        $transfer['load_money_format'] =  format_price($transfer['load_money']);

        //转让管理费
        $transfer['transfer_fee_format'] = format_price($transfer['transfer_amount']*(float)$transfer["user_load_transfer_fee"]*0.01);

        //转让收益
        $transfer['transfer_income_format'] =  format_price($transfer['all_must_repay_money']-$transfer['transfer_amount']);

        if($transfer['tras_create_time'] !=""){
            $transfer['tras_create_time_format'] =  to_date($transfer['tras_create_time'],"Y-m-d");
        }

        if(intval($transfer['transfer_time'])>0){
            $transfer['transfer_time_format'] =  to_date($transfer['transfer_time'],"Y-m-d");
        }

        if($transfer['tras_create_time'] !=""){
            $transfer['tras_create_time_format'] =  to_date($transfer['tras_create_time'],"Y 年 m 月 d 日");
        }

        $transfer['transfer_time_format'] =  to_date($transfer['transfer_time'],"Y 年 m 月 d 日");

        $transfer['user'] = get_user("*",$transfer['user_id']);
        if($transfer['t_user_id'] > 0)
            $transfer['tuser'] = get_user("*",$transfer['t_user_id']);

        $transfer['duser'] = get_user("*",$transfer['duser_id']);

        $transfer['remain_time'] =next_replay_month($transfer['near_repay_time']) - TIME_UTC + 24*3600 - 1;
        $transfer['remain_time_format'] = remain_time($transfer['remain_time']);

        if(is_last_repay($transfer['loantype'])==2){
            $transfer['repay_time'] =  $transfer['repay_time'] / 3 ;
        }

        $transfer['url'] = url("index","transfer#detail",array("id"=>$transfer['id']));
    }

    return $transfer;

}

function get_transfer_list($limit,$condition='',$extfield,$union_sql,$orderby = ''){
    //获取转让列表
    $count_sql = 'SELECT count(dlt.id) FROM '.DB_PREFIX.'deal_load_transfer dlt LEFT JOIN '.DB_PREFIX.'deal d ON d.id =dlt.deal_id WHERE  d.is_effect=1 AND d.is_delete = 0 '.$condition;

    $rs_count = $GLOBALS['db']->getOne($count_sql);

    if($rs_count > 0){
        $list_sql = 'SELECT dlt.*,d.loantype,d.name,d.icon,d.cate_id,d.user_id as duser_id,d.rate,d.last_repay_time,d.repay_start_time,d.repay_time,d.repay_time_type '.$extfield.'  FROM '.DB_PREFIX.'deal_load_transfer dlt LEFT JOIN '.DB_PREFIX.'deal d ON d.id =dlt.deal_id '.$union_sql.' WHERE d.is_effect=1 AND d.is_delete = 0 '.$condition;
        $list_sql .= ' ORDER BY '.$orderby;
        $list_sql .=' LIMIT '.$limit;

        $list = $GLOBALS['db']->getAll($list_sql);
        foreach($list as $k=>$v){
            $list[$k]['duser'] = get_user("*",$v['duser_id']);
            $list[$k]['user'] = get_user("*",$v['user_id']);
            if($v['t_user_id'] > 0)
                $list[$k]['tuser'] = get_user("*",$v['t_user_id']);
            else
                $list[$k]['tuser'] = null;


            if($list[$k]['tuser'] === false){
                $list[$k]['tuser'] = null;
            }

            if($list[$k]['duser'] === false){
                $list[$k]['duser'] = null;
            }

            if($list[$k]['user'] === false){
                $list[$k]['user'] = null;//new ArrayObject(); {}
            }


            $list[$k]['url'] = url("index","transfer#detail",array("id"=>$v['id']));
            //$deal['url'] = $durl;
            //x	$durl = "/index.php?ctl=deal&act=mobile&id=".$v['deal_id'];
            $durl = APP_ROOT."/wap/index.php?ctl=transfer_mobile&is_sj=1&id=".$v['deal_id']."&transfer_id=".$v['id'];
            $list[$k]['app_url'] = str_replace("/mapi", "", SITE_DOMAIN.$durl);

            //剩余期数
            $list[$k]['final_repay_time'] = $v['final_repay_time'] = next_replay_month($v['repay_start_time'],$v['repay_time']);

            $list[$k]['final_repay_time_format'] = to_date($v['final_repay_time'],"Y-m-d");

            if(is_last_repay($v['loantype'])==1)
                $list[$k]['how_much_month'] = $v['repay_time'];
            else
                $list[$k]['how_much_month'] = how_much_month($v['near_repay_time'],$v['final_repay_time']);

            if(is_last_repay($v['loantype'])==2) {
                $list[$k]['how_much_month'] = $list[$k]['how_much_month']/3;
            }

            if($v['cate_id'] > 0){
                $list[$k]['cate_info'] = $GLOBALS['db']->getRow("select id,name,brief,uname,icon from ".DB_PREFIX."deal_cate where id = ".$v['cate_id']." and is_effect = 1 and is_delete = 0",false);
            }

            $transfer_rs = deal_transfer($list[$k]);
            $list[$k]['month_repay_money'] = $transfer_rs['month_repay_money'];
            $list[$k]['all_must_repay_money'] = $transfer_rs['all_must_repay_money'];
            $list[$k]['left_benjin'] = $transfer_rs['left_benjin'];


            if($list[$k]['left_benjin'] < 100)
                $list[$k]['left_benjin_format'] = format_price($list[$k]['left_benjin']);
            else
                $list[$k]['left_benjin_format'] = format_price($list[$k]['left_benjin']/10000)."万";

            //剩多少利息
            $list[$k]['left_lixi'] = $list[$k]['all_must_repay_money'] - $list[$k]['left_benjin'];

            if($list[$k]['left_lixi'] < 100)
                $list[$k]['left_lixi_format'] = format_price($list[$k]['left_lixi']);
            else
                $list[$k]['left_lixi_format'] = format_price($list[$k]['left_lixi']/10000)."万";

            $list[$k]['remain_time'] = next_replay_month($v['near_repay_time']) - TIME_UTC + 24*3600 - 1;
            $list[$k]['remain_time_format'] = remain_time($list[$k]['remain_time']);


            $list[$k]['near_repay_time_format'] = to_date($v['near_repay_time'],"Y-m-d");
            if($v['transfer_amount'] < 100)
                $list[$k]['transfer_amount_format'] = format_price($v['transfer_amount']);
            else
                $list[$k]['transfer_amount_format'] = format_price($v['transfer_amount']/10000)."万";

            //转让收益
            $list[$k]['transfer_income'] =  $list[$k]['all_must_repay_money']-$list[$k]['transfer_amount'];
            $list[$k]['transfer_income_format'] =  format_price($list[$k]['transfer_income']);

            //
            $list[$k]['transfer_time_format'] = to_date($v['transfer_time'],"Y-m-d");
            if(is_last_repay($v['loantype'])==2) {
                $list[$k]['repay_time'] = $list[$k]['repay_time']/3;
            }
        }

        $result["list"] =  $list;
    }
    $result["rs_count"] =  $rs_count;
    return $result;
}


//正常还款执行界面
function getUcRepayBorrowMoney($id,$ids,$user_id=0){
    $id = intval($id);
    $root = array();
    $root["status"] = 0;//0:出错;1:正确;

    if($id == 0){
        $root["show_err"] = "操作失败！";
        return $root;
    }

    $deal = get_deal($id);
    if(!$deal)
    {
        $root["show_err"] = "借款不存在！";
        return $root;
    }
    if($deal['ips_bill_no']!=""){
        $root["status"] = 2;
        $root["jump"] = APP_ROOT.'/index.php?ctl=collocation&act=RepaymentNewTrade&deal_id='.$deal['id'].'&l_key='.$ids."&from=".$GLOBALS['request']['from'];
        $root['jump'] = str_replace("/mapi", "", SITE_DOMAIN.$root['jump']);
        return $root;
    }

    if($user_id > 0){
        $GLOBALS['user_info'] = get_user("*",$user_id);
    }

    if($deal['user_id']!=$GLOBALS['user_info']['id']){
        $root["show_err"] = "不属于你的借款！";
        return $root;
    }
    if($deal['deal_status']!=4){
        $root["show_err"] = "借款不是还款状态！";
        return $root;
    }

    $ids = explode(",",$ids);

    //当前用户余额
    $user_total_money = (float)$GLOBALS['user_info']['money'];

    if($user_total_money<= 0){
        $root["show_err"] = "余额不足";
        return $root;
    }

    $last_repay_key = -1;
    require_once APP_ROOT_PATH.'system/libs/user.php';

    foreach($ids as $lkey){
        //还了多少人
        $repay_user_count = 0;
        //多少人未还
        $no_repay_user_count =0;
        //还了多少本息
        $repay_money = 0;
        //还了多少逾期罚息
        $repay_impose_money = 0;
        //还了多少管理费
        $repay_manage_money = 0;
        //还了多少抵押管理费
        $mortgage_fee = 0;
        //还了多少逾期管理费
        $repay_manage_impose_money = 0;

        //用户回款 get_deal_user_load_list($deal_info, $user_id = 0 ,$lkey = -1 , $ukey = -1,$true_time=0,$get_type = 0, $r_type = 0, $limit = "")
        $user_loan_list = get_deal_user_load_list($deal, 0 , $lkey , -1 , 0 , 1);
        //如果已收取管理费
        $get_manage = $GLOBALS['db']->getOne("SELECT get_manage FROM ".DB_PREFIX."deal_repay WHERE deal_id = ".$deal['id']." and l_key=".$lkey."  ");

        //===============还款================
        foreach($user_loan_list as $lllk=>$lllv){
            foreach($lllv as $kk=>$vv){
                if($vv['has_repay']==0 ){//借入者已还款，但是没打款到借出用户中心
                    $user_load_data = array();

                    $user_load_data['true_repay_time'] = TIME_UTC;
                    $user_load_data['true_repay_date'] = to_date(TIME_UTC);
                    $user_load_data['is_site_repay'] = 0;
                    $user_load_data['status'] = 0;

                    $user_load_data['true_repay_money'] = (float)$vv['month_repay_money'];
                    $user_load_data['true_self_money'] = (float)$vv['self_money'];
                    $user_load_data['true_interest_money'] = (float)$vv['interest_money'];
                    $user_load_data['true_manage_money'] = (float)$vv['manage_money'];
                    $user_load_data['true_manage_interest_money'] = (float)$vv['manage_interest_money'];
                    $user_load_data['true_repay_manage_money'] = (float)$vv['repay_manage_money'];
                    $user_load_data['true_manage_interest_money_rebate'] = (float)$vv['manage_interest_money_rebate'];
                    $user_load_data['impose_money'] = (float)$vv['impose_money'];
                    $user_load_data['repay_manage_impose_money'] = (float)$vv['repay_manage_impose_money'];
                    $user_load_data['true_reward_money'] = (float)$vv['reward_money'];
                    $user_load_data['true_mortgage_fee'] = (float)$vv['mortgage_fee'];

                    $need_repay_money = 0;
                    if($get_manage==0)
                        $need_repay_money += $user_load_data['true_repay_money']  + $user_load_data['impose_money'] + $user_load_data['true_repay_manage_money'] + $user_load_data['repay_manage_impose_money'] + $user_load_data['mortgage_fee'];
                    else
                        $need_repay_money += $user_load_data['true_repay_money']  + $user_load_data['impose_money'] + $user_load_data['repay_manage_impose_money'] + $user_load_data['mortgage_fee'];
                    //=============余额足够才进行还款=================
                    if((float)$need_repay_money <= $user_total_money){
                        $last_repay_key = $lkey;
                        $repay_user_count +=1;
                        $repay_money +=$user_load_data['true_repay_money'];
                        $repay_impose_money += $user_load_data['impose_money'];
                        $repay_manage_money += $user_load_data['true_repay_manage_money'];
                        $repay_manage_impose_money += $user_load_data['repay_manage_impose_money'];
                        $user_total_money = $user_total_money - $need_repay_money;
                        $mortgage_fee+= $user_load_data['true_mortgage_fee'];

                        if($vv['status']>0)
                            $user_load_data['status'] = $vv['status'] - 1;

                        $user_load_data['has_repay'] = 1;
                        if($get_manage == 1){
                            unset($user_load_data['true_repay_manage_money']);
                        }
                        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$user_load_data,"UPDATE","id=".$vv['id']."  AND has_repay = 0  ","SILENT");

                        if($GLOBALS['db']->affected_rows() > 0){

                            $unext_loan = $user_loan_list[$vv['u_key']][$kk+1];


                            $load_repay_rs = $GLOBALS['db']->getRow("SELECT (sum(true_interest_money) + sum(impose_money)) as shouyi,sum(impose_money) as total_impose_money FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$deal['id']." AND user_id=".$vv['user_id']);
                            $all_shouyi_money= number_format($load_repay_rs['shouyi'],2);
                            $all_impose_money = number_format($load_repay_rs['total_impose_money'],2);
                            //$notices['content'] = "本次投标共获得收益:".$all_shouyi_money."元,其中违约金为:".$all_impose_money."元,本次投标已回款完毕！";


                            if($user_load_data['impose_money'] !=0 || $user_load_data['true_manage_money'] !=0 || $user_load_data['true_repay_money']!=0){
                                $in_user_id  = $vv['user_id'];
                                //如果是转让债权那么将回款打入转让者的账户
                                if((int)$vv['t_user_id']== 0){
                                    $loan_user_info['user_name'] = $vv['user_name'];
                                    $loan_user_info['email'] = $vv['email'];
                                    $loan_user_info['mobile'] = $vv['mobile'];
                                }
                                else{
                                    $in_user_id = $vv['t_user_id'];
                                    $loan_user_info['user_name'] = $vv['t_user_name'];
                                    $loan_user_info['email'] = $vv['t_email'];
                                    $loan_user_info['mobile'] = $vv['t_mobile'];
                                }

                                //更新用户账户资金记录
                                modify_account(array("money"=>$user_load_data['true_repay_money']),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,回报本息",5);

                                if($user_load_data['true_manage_money'] > 0)
                                    modify_account(array("money"=>-$user_load_data['true_manage_money']),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,投标管理费",20);

                                //利息管理费
                                modify_account(array("money"=>-$user_load_data['true_manage_interest_money']),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,投标利息管理费",20);

                                if($user_load_data['impose_money'] != 0)
                                    modify_account(array("money"=>$user_load_data['impose_money']),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,逾期罚息",21);

                                //出借人奖励
                                if($user_load_data['true_reward_money']!=0){
                                    modify_account(array("money"=>$user_load_data['true_reward_money']),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,奖励收益",28);
                                }

                                //扣除体验金
                                if($vv['learn_id'] > 0){
                                    $load_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_load where id=".$vv['load_id']);
                                    if($load_info){
                                        //还需回收多少
                                        $back_learn_money = 0;
                                        if(floatval($load_info['learn_money']) > floatval($load_info['back_learn_money'])){
                                            $back_learn_money = floatval($load_info['learn_money']) - floatval($load_info['back_learn_money']);
                                        }
                                        if($back_learn_money > 0){
                                            if(($user_load_data['true_repay_money'] -$user_load_data['true_manage_money'] -$user_load_data['true_manage_interest_money'] + $user_load_data['impose_money'] + $user_load_data['true_reward_money']) >= $back_learn_money){
                                                modify_account(array("money"=>-$back_learn_money),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,体验金回收",54);
                                            }
                                            else{
                                                $back_learn_money = $user_load_data['true_repay_money'] -$user_load_data['true_manage_money'] -$user_load_data['true_manage_interest_money'] + $user_load_data['impose_money'] + $user_load_data['true_reward_money'];
                                                modify_account(array("money"=>-$back_learn_money),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,体验金回收",54);
                                            }

                                            $GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal_load SET  back_learn_money = back_learn_money + ".$back_learn_money." where id=".$vv['load_id']);
                                        }
                                    }
                                }

                                //普通会员邀请返利
                                get_referrals($vv['id']);

                                //出借人返佣金
                                if($user_load_data['true_manage_interest_money_rebate'] !=0){
                                    /*ok*/
                                    $reback_memo = sprintf($GLOBALS['lang']["INVEST_REBATE_LOG"],$deal["url"],$deal["name"],$loan_user_info["user_name"],intval($vv["l_key"])+1);
                                    reback_rebate_money($in_user_id,$user_load_data['true_manage_interest_money_rebate'],"invest",$reback_memo);
                                }


                                //短信通知回款
                                $loan_user_info['id'] = $in_user_id;
                                send_repay_reback_sms_mail($deal,$loan_user_info,$unext_loan,$user_load_data,$all_shouyi_money,$all_impose_money);




                            }
                        }
                    }

                    //=============余额足够才进行还款=================

                }
            }
        }
        //===============还款================

        if($repay_user_count > 0){
            //判断当前期是否还款完毕
            $true_repay_count = $GLOBALS['db']->getOne("SELECT count(*)  FROM ".DB_PREFIX."deal_load_repay WHERE deal_id = ".$deal['id']." and l_key=".$lkey." AND has_repay=1 ");

            $ext_str= "";
            if($true_repay_count<>$repay_user_count){
                $ext_str="[部分]";
            }
            //更新用户账户资金记录
            modify_account(array("money"=>-$repay_money),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,偿还本息$ext_str",4);
            if($repay_impose_money!=0)
                modify_account(array("money"=>-$repay_impose_money),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,逾期罚息$ext_str",11);

            if($repay_manage_money > 0 && $get_manage == 0)
                modify_account(array("money"=>-$repay_manage_money),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,借款管理费$ext_str",10);

            if($mortgage_fee > 0)
                modify_account(array("money"=>-$mortgage_fee),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,抵押物管理费$ext_str",27);

            if($repay_manage_impose_money!=0 )
                modify_account(array("money"=>-$repay_manage_impose_money),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,逾期管理费$ext_str",12);

            $rebate_rs = get_rebate_fee($GLOBALS['user_info']['id'],"borrow");
            $true_manage_money_rebate = $repay_manage_money* floatval($rebate_rs['rebate'])/100;
            //借款者返佣
            if($true_manage_money_rebate!=0){
                /*ok*/
                $reback_memo = sprintf($GLOBALS['lang']["BORROW_REBATE_LOG"],$deal["url"],$deal["name"],$deal["user"]["user_name"],intval($kk)+1);
                reback_rebate_money($GLOBALS['user_info']['id'],$true_manage_money_rebate,"borrow",$reback_memo);
            }
        }

        $r_msg = "会员还款$ext_str";
        if($repay_money > 0){
            $r_msg .=",本息：".format_price($repay_money);
        }
        if($repay_impose_money> 0){
            $r_msg .=",逾期费用：".format_price($repay_impose_money);
        }
        if($repay_manage_money > 0 && $get_manage == 0){
            $r_msg .=",管理费：".format_price($repay_manage_money);
        }
        if($mortgage_fee > 0){
            $r_msg .=",抵押物管理费：".format_price($mortgage_fee);
        }
        if($repay_manage_impose_money > 0){
            $r_msg .=",逾期管理费：".format_price($repay_manage_impose_money);
        }
        $repay_id = $GLOBALS['db']->getOne("SELECT id  FROM ".DB_PREFIX."deal_repay WHERE deal_id = ".$deal['id']." and l_key=".$lkey);
        repay_log($repay_id,$r_msg,$GLOBALS['user_info']['id'],0);


        //$content = "您好，您在".app_conf("SHOP_TITLE")."的借款 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”的借款第".($lkey+1)."期还款".number_format(($repay_money+$repay_impose_money+$repay_manage_money+$repay_manage_impose_money),2)."元，";
        //如果还款完毕
        $sms_ext_str = "成功";
        if($left_user_count = $GLOBALS['db']->getOne("SELECT count(*)  FROM ".DB_PREFIX."deal_load_repay WHERE deal_id = ".$deal['id']." and l_key=".$lkey." AND has_repay = 0 ") == 0){
            //$content .="本期还款完毕。";
            $notices['repay_status'] = "本期还款完毕";
            $impose_rs = $GLOBALS['db']->getRow("SELECT sum(true_self_money) as total_self_money,sum(true_interest_money) as total_interest_money,sum(true_repay_money) as total_repay_money,sum(impose_money) as total_impose_money,sum(true_repay_manage_money) as total_repay_manage_money,sum(repay_manage_impose_money) as total_repay_manage_impose_money,sum(true_mortgage_fee) as total_mortgage_fee  FROM ".DB_PREFIX."deal_load_repay WHERE deal_id = ".$deal['id']." and l_key=".$lkey." AND has_repay = 1");
            //判断是否逾期
            $repay_update_data['has_repay'] = 1;
            $repay_update_data['true_repay_time'] = TIME_UTC;
            $repay_update_data['true_repay_date'] = to_date(TIME_UTC);
            $repay_update_data['true_repay_money'] = floatval($impose_rs['total_repay_money']);
            $repay_update_data['true_self_money'] =  floatval($impose_rs['total_self_money']);
            $repay_update_data['true_interest_money'] =  floatval($impose_rs['total_interest_money']);
            $repay_update_data['impose_money'] =floatval($impose_rs['total_impose_money']);
            if($get_manage == 0){
                $repay_update_data['true_manage_money'] =floatval($impose_rs['total_repay_manage_money']);
            }

            $repay_update_data['true_mortgage_fee'] =floatval($impose_rs['total_mortgage_fee']);

            $repay_update_data['manage_impose_money']=floatval($impose_rs['total_repay_manage_impose_money']);

            //返佣金额
            $rebate_rs = get_rebate_fee($GLOBALS['user_info']['id'],"borrow");
            $repay_update_data['true_manage_money_rebate'] =floatval($impose_rs['total_repay_manage_money']) * floatval($rebate_rs['rebate'])/100;

            if($vv['impose_day'] > 0){

                //VIP降级-逾期还款
                $type = 2;
                $type_info = 5;
                $resultdate = syn_user_vip($GLOBALS['user_info']['id'],$type,$type_info);

                if($vv['impose_day'] < app_conf('YZ_IMPSE_DAY')){
                    modify_account(array("point"=>trim(app_conf('IMPOSE_POINT'))),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,逾期还款",11);
                    $repay_update_data['status'] = 2;
                }
                else{
                    modify_account(array("point"=>trim(app_conf('YZ_IMPOSE_POINT'))),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,严重逾期",11);
                    $repay_update_data['status'] = 3;
                }
            }
            elseif(TIME_UTC<=((int)$vv['repay_day'] + 24*3600-1)){
                $repay_update_data['status'] = 1;

                //VIP升级 -正常还款
                $type = 1;
                $type_info = 3;
                $resultdate = syn_user_vip($GLOBALS['user_info']['id'],$type,$type_info);
            }

            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$repay_update_data,"UPDATE","deal_id = ".$deal['id']." and l_key=".$lkey);

            $notices['has_next_loan'] = 0;
            if($next_loan =$GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal['id']." and l_key > ".$last_repay_key." ORDER BY  l_key ASC")){
                //$content .= "本笔借款的下个还款日为".to_date($next_loan['repay_day'],"Y年m月d日")."，需要本息".number_format($next_loan['repay_money'],2)."元。";
                $notices['has_next_loan'] = 1;
                $notices['next_repay_time'] = to_date($next_loan['repay_time'],"Y年m月d日");
                $notices['next_repay_money'] = number_format($next_loan['repay_money'],2);
            }
        }
        else{
            //$content .="本期部分还款，还有".$left_user_count."个出借人待还。";
            $notices['repay_status'] = "本期部分还款";
            $notices['left_user_count'] = $left_user_count;
            $sms_ext_str = "部分";
            $GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal_repay SET has_repay = 2 WHERE deal_id = ".$deal['id']." and l_key=".$lkey);
        }

        //您好，您在{$notice.shop_title}的借款{$notice.url}的借款第{$notice.key}期还款{$notice.money}元

        $notices['site_title'] = app_conf("SHOP_TITLE");
        $notices['url'] =  "“<a href=\"".$deal['url']."\">".$deal['name']."</a>”";
        $notices['index'] =  ($lkey+1);
        $notices['repay_money'] = ($repay_money+$repay_impose_money+$repay_manage_impose_money);
        if($get_manage == 0){
            $notices['repay_money'] = number_format($notices['repay_money'] + $repay_manage_money,2);
        }
        else{
            $notices['repay_money'] = number_format($notices['repay_money'],2);
        }

        $notices['repay_money'] += $mortgage_fee;

        $tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_REPAY_MONEY_MSG'",false);
        $GLOBALS['tmpl']->assign("notice",$notices);
        $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);

        send_user_msg("",$content,0,$GLOBALS['user_info']['id'],TIME_UTC,0,true,8);
        unset($notices);

        //短信通知
        if(app_conf("SMS_ON")==1&&app_conf('SMS_SEND_REPAY')==1){
            $tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_REPAY_SUCCESS_MSG'",false);
            $tmpl_content = $tmpl['content'];
            //$sms_content = "尊敬的".app_conf("SHOP_TITLE")."用户".$GLOBALS['user_info']['user_name']."，您的借款“".$deal['name']."”第".($lkey+1)."期".$sms_ext_str."还款".number_format(($repay_money+$repay_impose_money+$repay_manage_money+$repay_manage_impose_money),2)."元，感谢您的关注和支持。";
            $notice['user_name'] = $GLOBALS['user_info']['user_name'];
            $notice['deal_name'] = $deal['sub_name'];
            $notice['site_name'] = app_conf("SHOP_TITLE");
            $notice['index'] = $lkey+1;
            $notice['status'] = $sms_ext_str;
            if($get_manage ==0){
                $notice['all_money'] = number_format(($repay_money+$repay_impose_money+$repay_manage_money+$repay_manage_impose_money),2);
            }
            else{
                $notice['all_money'] = number_format(($repay_money+$repay_impose_money+$repay_manage_impose_money),2);
            }
            $notice['repay_money'] = number_format($repay_money,2);
            $notice['impose_money'] = number_format($repay_impose_money,2);
            if($get_manage == 0){
                $notice['manage_money'] = number_format($repay_manage_money,2);
            }
            else{
                $notice['manage_money'] = number_format(0,2);
            }
            $notice['mortgage_fee'] = number_format($mortgage_fee,2);
            $notice['manage_impose_money'] = number_format($repay_manage_impose_money,2);

            $GLOBALS['tmpl']->assign("notice",$notice);
            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
            $msg_data['dest'] = $GLOBALS['user_info']['mobile'];
            $msg_data['send_type'] = 0;
            $msg_data['title'] = "还款短信通知";
            $msg_data['content'] = $msg;
            $msg_data['send_time'] = 0;
            $msg_data['is_send'] = 0;
            $msg_data['create_time'] = TIME_UTC;
            $msg_data['user_id'] = $GLOBALS['user_info']['id'];
            $msg_data['is_html'] = 0;
            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
        }

        //还款信息通知
        if(app_conf("WEIXIN_MSG")==1){
            $user_info = get_user_info("*","id = ".$GLOBALS['user_info']['id']);
            if($user_info['wx_openid']!='')
            {
                $tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_tmpl where template_id_short='OPENTM205975633' ");
                $num = $lkey+1;
                if($get_manage ==0){
                    $all_money = number_format(($repay_money+$repay_impose_money+$repay_manage_money+$repay_manage_impose_money),2);
                }
                else{
                    $all_money = number_format(($repay_money+$repay_impose_money+$repay_manage_impose_money),2);
                }
                $weixin_data['first'] = array('value'=>$user_info['user_name'].'您的借款','color'=>'#173177');
                $weixin_data['keyword1']=array('value'=>$deal['sub_name'].'第'.$num.'期','color'=>'#173177');
                $weixin_data['keyword2']=array('value'=>$all_money,'color'=>'#173177');
                //up BY  20170512 1010
                //weixin_tmpl_send($tmpl['template_id'],$user_info['id'],$weixin_data);
            }
        }

    }

    //判断本借款是否还款完毕
    if($GLOBALS['db']->getOne("SELECT count(*)  FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal['id']." and l_key=".$last_repay_key." AND has_repay <> 1 ") == 0){
        //全部还完
        if($GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal['id']." and has_repay = 0 ") == 0){
            //判断获取的信用是否超过限制
            if($GLOBALS['db']->getOne("SELECT sum(point) FROM ".DB_PREFIX."user_point_log WHERE  `type`=6 AND user_id=".$GLOBALS['user_info']['id']) < (int)trim(app_conf('REPAY_SUCCESS_LIMIT'))){
                //获取上一次还款时间
                $befor_repay_time = $GLOBALS['db']->getOne("SELECT MAX(create_time) FROM ".DB_PREFIX."user_point_log WHERE  `type`=6 AND user_id=".$GLOBALS['user_info']['id']);
                $day = ceil((TIME_UTC-$befor_repay_time)/24/3600);
                //当天数大于等于间隔时间 获得信用
                if($day >= (int)trim(app_conf('REPAY_SUCCESS_DAY'))){
                    modify_account(array("point"=>trim(app_conf('REPAY_SUCCESS_POINT'))),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],还清借款",4);
                }
            }

            //用户获得额度
            modify_account(array("quota"=>trim(app_conf('USER_REPAY_QUOTA'))),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],还清借款获得额度",4);

        }
    }


    $GLOBALS['db']->query("UPDATE ".DB_PREFIX."generation_repay_submit SET `memo`='因还款失效',`status`=2 WHERE deal_id=".$deal['id']);

    sys_user_status($GLOBALS['user_info']['id'],false,true);
    syn_deal_status($id);
    syn_transfer_status(0,$id);
    $root["status"] = 1;//0:出错;1:正确;
    $root["show_err"] = "还款完毕，本次还款人数:$repay_user_count";

    return $root;
}

//提前还款操作界面
function getUcInrepayRefund($id){
    $id = intval($id);
    $root = array();
    $root["status"] = 0;//0:出错;1:正确;


    if($id == 0){
        $root["show_err"] = "操作失败！";
        return $root;
    }

    $deal = get_deal($id);
    if(!$deal)
    {
        $root["show_err"] = "借款不存在！";
        return $root;
    }
    if($deal['user_id']!=$GLOBALS['user_info']['id']){
        $root["show_err"] = "不属于你的借款！";
        return $root;
    }
    if($deal['deal_status']!=4){
        $root["show_err"] = "借款不是还款状态！";
        return $root;
    }

    $root["deal"] = $deal;

    $time = TIME_UTC;
    $impose_money = 0;
    //还了几期了
    $has_repay_count =  $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_repay WHERE has_repay = 1 and deal_id=".$id);
    //计算罚息
    $loan_list = get_deal_load_list($deal);

    foreach($loan_list as $k=>$v){
        if($v['has_repay'] == 0)
        {
            $impose_money += floatval($v['impose_money']);
        }
    }

    if($impose_money > 0){
        $root["show_err"] = "请将逾期未还的借款还完才可以进行此操作！";
        return $root;
    }

    $get_manage = $GLOBALS['db']->getOne("SELECT get_manage FROM ".DB_PREFIX."deal_repay WHERE deal_id = ".$deal['id']." and l_key=".$has_repay_count."  ");
    if($get_manage==1){
        $deal['month_manage_money'] = 0;
        $deal['all_manage_money'] = 0;
    }

    $loaninfo['deal'] = $deal;
    $loaninfo['loanlist'] = $loan_list;

    $inrepay_info = inrepay_repay($loaninfo,$has_repay_count);

    $root["true_all_manage_money"] = $inrepay_info["true_manage_money"];
    $root["true_all_manage_money_format"] = format_price($inrepay_info["true_manage_money"]);

    $root["true_all_mortgage_fee"] = $inrepay_info["true_mortgage_fee"];
    $root["true_all_mortgage_fee_format"] = format_price($inrepay_info["true_mortgage_fee"]);

    $root["status"] = 1;//0:出错;1:正确;
    $root["impose_money"] = $inrepay_info['impose_money'];
    $root["impose_money_format"] = format_price($root["impose_money"]);

    $root["total_repay_money"] = $inrepay_info['true_repay_money'];
    $root["total_repay_money_format"] = format_price($root["total_repay_money"]);

    $true_total_repay_money = $inrepay_info['true_repay_money'] + $inrepay_info['impose_money'] + $inrepay_info["true_manage_money"] + $root["true_all_mortgage_fee"];
    $root["true_total_repay_money"] = $true_total_repay_money;
    $root["true_total_repay_money_format"] = format_price($root["true_total_repay_money"]);

    return $root;
}

//提前还款执行程序
function getUCInrepayRepayBorrowMoney($id,$user_id = 0){
    $id = intval($id);

    $root = array();
    $root["status"] = 0;//0:出错;1:正确;

    if($id == 0){
        $root["show_err"] = "操作失败！";
        return $root;
    }

    $deal = get_deal($id);
    if(!$deal)
    {
        $root["show_err"] = "借款不存在！";
        return $root;
    }

    if($user_id > 0){
        $GLOBALS['user_info'] = get_user("*",$user_id);
    }

    if($deal['user_id']!=$GLOBALS['user_info']['id']){
        $root["show_err"] = "不属于你的借款！";
        return $root;
    }
    if($deal['deal_status']!=4){
        $root["show_err"] = "借款不是还款状态！";
        return $root;
    }



    $time = TIME_UTC;
    $impose_money = 0;
    //是否有部分还款的
    $repay_count_ing =  $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_repay WHERE has_repay=2 and deal_id=".$id);
    if($repay_count_ing){
        $root["show_err"] = "请将部分还款的借款还完才可以进行此操作！";
        return $root;
    }

    //计算罚息
    $loan_list = get_deal_load_list($deal);

    $k_repay_key = -1;
    $k_repay_time = 0;
    foreach($loan_list as $k=>$v){
        if($v['has_repay'] == 0)
        {
            if($k_repay_key==-1){
                $k_repay_key = $v['l_key'];
                $k_repay_time = $v['repay_day'];
            }
            $impose_money +=$v['impose_all_money'];
        }
    }

    if($impose_money > 0){
        $root["show_err"] = "请将逾期未还的借款还完才可以进行此操作！";
        return $root;
    }

    if($deal['ips_bill_no']!=""){
        $root["status"] = 2;
        $root["jump"] = APP_ROOT.'/index.php?ctl=collocation&act=RepaymentNewTrade&deal_id='.$deal['id'].'&l_key=all&from='.$GLOBALS['request']['from'];
        $root['jump'] = str_replace("/mapi", "", SITE_DOMAIN.$root['jump']);
        return $root;
    }


    //还了几期了
    $has_repay_count =  $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_repay WHERE has_repay=1 and deal_id=".$id);

    $get_manage = $GLOBALS['db']->getOne("SELECT get_manage FROM ".DB_PREFIX."deal_repay WHERE deal_id = ".$deal['id']." and l_key=".$has_repay_count."  ");
    if($get_manage==1){
        $deal['month_manage_money'] = 0;
        $deal['all_manage_money'] = 0;
    }

    $loaninfo['deal'] = $deal;
    $loaninfo['loanlist'] = $loan_list;

    //返佣
    $rebate_rs = get_rebate_fee($GLOBALS['user_info']['id'],"borrow");
    $loaninfo['deal']['rebate'] = $rebate_rs['rebate'];

    $inrepay_info = inrepay_repay($loaninfo,$has_repay_count);

    $true_repay_money = (float)$inrepay_info['true_repay_money'];
    $true_self_money = (float)$inrepay_info['true_self_money'];
    $impose_money  = (float)$inrepay_info['impose_money'];
    $true_manage_money = (float)$inrepay_info['true_manage_money'];
    $true_mortgage_fee = (float)$inrepay_info['true_mortgage_fee'];
    $true_manage_money_rebate = (float)$inrepay_info['true_manage_money_rebate'];

    $true_total_repay_money = $true_repay_money + $impose_money + $true_manage_money;

    if($true_total_repay_money > $GLOBALS['user_info']['money']){
        $root["show_err"] = "对不起，您的余额不足！";
        return $root;
    }


    //录入到提前还款列表
    $inrepay_data['deal_id'] = $id;
    $inrepay_data['user_id'] = $GLOBALS['user_info']['id'];
    $inrepay_data['repay_money'] = $true_repay_money;
    $inrepay_data['self_money'] = $true_self_money;
    $inrepay_data['impose_money'] = $impose_money;
    $inrepay_data['manage_money'] = $true_manage_money;
    $inrepay_data['mortgage_fee'] = $true_mortgage_fee;
    $inrepay_data['repay_time'] = $k_repay_time;
    $inrepay_data['true_repay_time'] = $time;

    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_inrepay_repay",$inrepay_data,"INSERT");
    $inrepay_id = $GLOBALS['db']->insert_id();
    if($inrepay_id==0){
        $root["show_err"] = "对不起，数据处理失败，请联系客服！";
        return $root;
    }

    //录入还款列表
    $wait_repay_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$id." and has_repay=0 ORDER BY l_key ASC");
    $temp_ids = array();
    foreach($wait_repay_list as $k=>$v){
        $repay_data =array();
        $repay_data['has_repay'] = 1;
        $repay_data['true_repay_time'] = $time;
        $repay_data['true_repay_date'] = to_date($time);
        $repay_data['status'] = 0;
        if($k_repay_key==$v['l_key']){
            $repay_data['true_repay_money'] = $true_repay_money;
            $repay_data['impose_money'] = $impose_money;
            $repay_data['true_manage_money'] = $true_manage_money;
            $repay_data['true_mortgage_fee'] = $true_mortgage_fee;
            $repay_data['true_self_money'] = $true_self_money;
            $repay_data['true_interest_money'] = $true_repay_money - $true_self_money;


            $repay_data['true_manage_money_rebate'] = $true_manage_money_rebate;
        }

        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$repay_data,"UPDATE","id=".$v['id']);

        //假如出错 删除掉原来的以插入的数据
        if($GLOBALS['db']->affected_rows() == 0)
        {
            if(count($temp_ids) > 0){
                $GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal_repay SET has_repay=0 WHERE id in ".implode(",",$temp_ids)."");
                make_repay_plan($deal);
            }
            $root["show_err"] = "对不起，处理数据失败请联系客服！";
            return $root;
        }
        else{
            $temp_ids[] = $v['id'];
        }

    }

    if(count($temp_ids)==0){
        $root["show_err"] = "对不起，处理数据失败请联系客服！";
        return $root;
    }

    //更新用户账户资金记录
    require_once APP_ROOT_PATH.'system/libs/user.php';

    modify_account(array("money"=>-round($impose_money,2)),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],提前还款违约金",6);
    modify_account(array("money"=>-round($true_manage_money,2)),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],提前还款管理费",10);
    modify_account(array("money"=>-round($true_mortgage_fee,2)),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],提前还款抵押物管理费",27);
    modify_account(array("money"=>-round($true_repay_money,2)),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],提前还款本息",6);
    //用户获得额度
    modify_account(array("quota"=>trim(app_conf('USER_REPAY_QUOTA'))),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],还清借款获得额度",6);


    //借款者返佣金
    if($true_manage_money_rebate!=0){
        /*ok*/
        $reback_memo = '借款“<a href="'.$deal["url"].'">'.$deal["name"].'</a>”，借款者'.$deal["user"]["user_name"];
        reback_rebate_money($GLOBALS['user_info']['id'],$true_manage_money_rebate,"borrow",$reback_memo);
    }


    //判断获取的信用是否超过限制
    if($GLOBALS['db']->getOne("SELECT sum(point) FROM ".DB_PREFIX."user_point_log WHERE `type`=6 AND user_id=".$GLOBALS['user_info']['id']) < (int)trim(app_conf('REPAY_SUCCESS_LIMIT'))){
        //获取上一次还款时间
        $befor_repay_time = $GLOBALS['db']->getOne("SELECT MAX(create_time) FROM ".DB_PREFIX."user_point_log WHERE `type`=6 AND user_id=".$GLOBALS['user_info']['id']);
        $day = ceil(($time-$befor_repay_time)/24/3600);
        //当天数大于等于间隔时间 获得信用
        if($day >= (int)trim(app_conf('REPAY_SUCCESS_DAY'))){
            modify_account(array("point"=>trim(app_conf('REPAY_SUCCESS_POINT'))),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],还清借款",6);
        }
    }



    //用户回款
    /**
     * 获取某一期的用户还款列表
     * array $deal_info 借款信息
     * int $user_id 用户ID 为0代表全部
     * int $lkey  第几期 -1 全部
     * int $ukey 第几个投标人 -1 全部
     * int $true_time  真实还款时间
     * int $get_type  0 全部 1代表未还的  2 代表已还的
     * int $r_type = 0; 返回类型; 1:只返回一个数组; $result['item']
     * string $limit; 查询限制数量; 0,20  $result['count']
     */
    $user_loan_list = get_deal_user_load_list($deal,0,-1,-1,$time,1,0,'');
    $learn_id = 0;
    $load_id = 0;

    foreach($user_loan_list as $lllk=>$lllv){//循环用户
        //本金
        $user_self_money = 0;
        //本息
        $user_repay_money = 0;
        //违约金
        $user_impose_money = 0;
        //管理费
        $user_manage_money = 0;
        //利息管理费
        $user_manage_interest_money =0;
        //返佣金
        $manage_interest_money_rebate = 0;

        //奖励
        $user_reward_money = 0;

        foreach($lllv as $kk=>$vv){//循环期数

            $in_user_id = $vv['user_id'];
            //判断是否转让了债权
            if((int)$vv['t_user_id'] == 0){
                $loan_user_info['user_name'] = $vv['user_name'];
                $loan_user_info['email'] = $vv['email'];
                $loan_user_info['mobile'] = $vv['mobile'];
            }
            else{
                $in_user_id = $vv['t_user_id'];
                $loan_user_info['user_name'] = $vv['t_user_name'];
                $loan_user_info['email'] = $vv['t_email'];
                $loan_user_info['mobile'] = $vv['t_mobile'];
            }

            $user_load_data = array();
            $user_load_data['true_repay_time'] = $time;
            $user_load_data['true_repay_date'] = to_date($time);
            $user_load_data['is_site_repay'] = 0;
            $user_load_data['status'] = 0;

            if($k_repay_key==$vv['l_key']){
                $loadinfo = array();
                $loadinfo['deal']['rate'] = $deal['rate'];
                $loadinfo['deal']['loantype'] = $deal['loantype'];
                $loadinfo['deal']['repay_time'] = $deal['repay_time'];
                $loadinfo['deal']['borrow_amount'] = $vv['money'];
                $loadinfo['deal']['repay_start_time'] = $deal['repay_start_time'];
                $loadinfo['deal']['month_manage_money'] = $vv['manage_money'];
                $loadinfo['deal']['mortgage_fee'] = $vv['mortgage_fee'];
                $loadinfo['deal']['manage_interest_money'] = $vv['manage_interest_money'];
                $deal =  get_user_load_fee($in_user_id,0,$deal);
                $loadinfo['deal']['user_loan_interest_manage_fee'] = $deal['user_loan_interest_manage_fee'];
                $rebate_rs = get_rebate_fee($in_user_id,"invest");
                $loadinfo['deal']['rebate'] = $rebate_rs['rebate'];

                $loadinfo['deal']['manage_interest_money_rebate'] = $vv['manage_interest_money_rebate'];
                $loadinfo['deal']['month_repay_money'] = $vv['month_repay_money'];
                $loadinfo['deal']['compensate_fee'] = $deal['compensate_fee'];
                if($deal['repay_time_type'] == 1)
                    $loadinfo['deal']['all_manage_money'] = $vv['manage_money'];
                else
                    $loadinfo['deal']['all_manage_money'] = $vv['manage_money'] * $deal['repay_time'];

                $loadinfo['deal']['repay_time_type'] = $deal['repay_time_type'];

                $user_load_rs = inrepay_repay($loadinfo,$has_repay_count);

                $user_load_data['true_repay_money'] = $user_load_rs['true_repay_money'];
                $user_load_data['true_self_money'] = $user_load_rs['true_self_money'];
                $user_load_data['impose_money'] = $user_load_rs['impose_money'];
                $user_load_data['true_interest_money'] = $user_load_rs['true_repay_money'] - $user_load_rs['true_self_money'];
                $user_load_data['true_manage_money'] = $user_load_rs['true_manage_money'];
                $user_load_data['true_mortgage_fee'] = $user_load_rs['true_mortgage_fee'];
                $user_load_data['true_manage_interest_money'] = $user_load_rs['true_manage_interest_money'];
                $user_load_data['true_manage_interest_money_rebate'] = $user_load_rs['true_manage_interest_money_rebate'];
                $user_load_data['true_repay_manage_money'] = $true_manage_money / count($user_loan_list);
                $user_load_data['true_reward_money'] = 0;
                if((int)$vv['is_winning']==1 && (int)$vv['income_type']==2 && (float)$vv['income_value']!=0){
                    $user_load_data['true_reward_money'] = $user_load_data['true_interest_money'] * (float)$vv['income_value']*0.01;
                }

                $user_self_money = $user_load_data['true_self_money'];
                $user_repay_money = $user_load_data['true_repay_money'];
                $user_impose_money = $user_load_data['impose_money'];
                $user_manage_money = $user_load_data['true_manage_money'];
                $user_manage_interest_money = $user_load_data['true_manage_interest_money'];
                $manage_interest_money_rebate = $user_load_data['true_manage_interest_money_rebate'];
                $user_reward_money = $user_load_data['true_reward_money'];

            }

            $user_load_data['has_repay'] = 1;
            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$user_load_data,"UPDATE","id=".$vv['id']);

            $learn_id = $vv['learn_id'];
            $load_id = $vv['load_id'];

            //普通会员邀请返利
            get_referrals($vv['id']);
        }

        if($user_repay_money >0 || $user_impose_money >0 || $user_manage_money > 0 || $user_manage_interest_money >0 || $user_reward_money > 0){
            $all_repay_money = number_format($GLOBALS['db']->getOne("SELECT (sum(repay_money)-sum(self_money) + sum(impose_money)) as shouyi FROM ".DB_PREFIX."deal_load_repay WHERE  has_repay = 1 and deal_id=".$v['deal_id']." AND user_id=".$v['user_id']),2);
            $all_impose_money = number_format($GLOBALS['db']->getOne("SELECT sum(impose_money) FROM ".DB_PREFIX."deal_load_repay WHERE has_repay = 1 and deal_id=".$v['deal_id']." AND user_id=".$v['user_id']),2);

            //$content = "您好，您在".app_conf("SHOP_TITLE")."的投标 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”提前还款,";
            //$content .= "本次投标共获得收益:".$all_repay_money."元,其中违约金为:".$all_impose_money."元,本次投标已回款完毕！";

            //更新用户账户资金记录
            modify_account(array("money"=>$user_repay_money),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],回报本息",5);

            modify_account(array("money"=>$user_impose_money),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],提前回收违约金",7);

            if($user_manage_money>0)
                modify_account(array("money"=>-$user_manage_money),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],投标管理费",20);

            if($user_reward_money>0)
                modify_account(array("money"=>-$user_reward_money),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],投标奖励",28);

            modify_account(array("money"=>-$user_manage_interest_money),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],投标利息管理费",20);

            //扣除体验金
            if($learn_id > 0){
                $load_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_load where id=".$load_id);
                if($load_info){
                    //还需回收多少
                    $back_learn_money = 0;
                    if(floatval($load_info['learn_money']) > floatval($load_info['back_learn_money'])){
                        $back_learn_money = floatval($load_info['learn_money']) - floatval($load_info['back_learn_money']);
                    }
                    if($back_learn_money > 0){
                        if(($user_repay_money + $user_impose_money -$user_manage_money - $user_reward_money -$user_manage_interest_money) >= $back_learn_money){
                            modify_account(array("money"=>-$back_learn_money),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],体验金回收",54);
                        }
                        else{
                            $back_learn_money = $user_repay_money + $user_impose_money -$user_manage_money - $user_reward_money -$user_manage_interest_money;
                            modify_account(array("money"=>-$back_learn_money),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],体验金回收",54);
                        }
                        $GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal_load SET  back_learn_money = back_learn_money + ".$back_learn_money." where id=".$load_id);
                    }
                }
            }

            //出借人返佣金
            if($manage_interest_money_rebate){
                /*ok*/
                $reback_memo = "借款“<a href'".$deal['url']."'>".$deal["name"]."</a>”,出借人".$loan_user_info["user_name"]."，第".(intval($k_repay_key)+1)."期,提前还款";
                reback_rebate_money($in_user_id,$manage_interest_money_rebate,"invest",$reback_memo);
            }

            $msg_conf = get_user_msg_conf($in_user_id);
            //短信通知
            if(app_conf("SMS_ON")==1&&app_conf('SMS_REPAY_TOUSER_ON')==1){

                $tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_SMS'",false);
                $tmpl_content = $tmpl['content'];

                $notice['user_name'] = $loan_user_info['user_name'];
                $notice['deal_name'] = $deal['sub_name'];
                $notice['deal_url'] = $deal['url'];
                $notice['site_name'] = app_conf("SHOP_TITLE");
                $notice['repay_money'] = $vv['month_repay_money']+$vv['impose_money'];

                $notice['all_repay_money'] = $all_repay_money;
                $notice['impose_money'] = $all_impose_money;

                $GLOBALS['tmpl']->assign("notice",$notice);
                $sms_content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

                $msg_data['dest'] = $loan_user_info['mobile'];
                $msg_data['send_type'] = 0;
                $msg_data['title'] = $msg_data['content'] = addslashes($sms_content);
                $msg_data['send_time'] = 0;
                $msg_data['is_send'] = 0;
                $msg_data['create_time'] = $time;
                $msg_data['user_id'] = $in_user_id;
                $msg_data['is_html'] = 0;
                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
            }

            //回款通知
            if(app_conf("WEIXIN_MSG")==1){
                $user_info = get_user_info("*","id = ".$in_user_id);
                if($user_info['wx_openid']!='')
                {
                    $tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_tmpl where template_id_short='OPENTM207940989' ");
                    $repay_money = number_format(($vv['month_repay_money']+$vv['impose_money']),2);
                    $weixin_data['first'] = array('value'=>'投标回款短信通知','color'=>'#173177');
                    $weixin_data['keyword1']=array('value'=>$deal['sub_name'],'color'=>'#173177');
                    $weixin_data['keyword2']=array('value'=>$loan_user_info['user_name'],'color'=>'#173177');
                    $weixin_data['keyword3']=array('value'=>$repay_money,'color'=>'#173177');
                    //up BY  20170512 1010
                    //weixin_tmpl_send($tmpl['template_id'],$in_user_id,$weixin_data);
                }
            }
            //站内信

            $notices['shop_title'] = app_conf("SHOP_TITLE");
            $notices['url'] = "“<a href=\"".$deal['url']."\">".$deal['name']."</a>”";
            $notices['repay_money'] = $all_repay_money;
            $notices['impose_money'] = $all_impose_money;

            $tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_REPAY_MONEY_TQTB'",false);
            $GLOBALS['tmpl']->assign("notice",$notices);
            $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);

            if($msg_conf['sms_bidrepaid']==1)
                send_user_msg("",$content,0,$in_user_id,$time,0,true,9);
            //邮件
            if($msg_conf['mail_bidrepaid']==1 && app_conf('MAIL_ON')==1){

                $tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_EMAIL'",false);
                $tmpl_content = $tmpl['content'];

                $notice['user_name'] = $loan_user_info['user_name'];
                $notice['deal_name'] = $deal['sub_name'];
                $notice['deal_url'] = $deal['url'];
                $notice['site_name'] = app_conf("SHOP_TITLE");
                $notice['site_url'] = SITE_DOMAIN.APP_ROOT;
                $notice['help_url'] = SITE_DOMAIN.url("index","helpcenter");
                $notice['msg_cof_setting_url'] = SITE_DOMAIN.url("index","uc_msg#setting");
                $notice['repay_money'] = $vv['month_repay_money']+$vv['impose_money'];

                $notice['all_repay_money'] = $all_repay_money;
                $notice['impose_money'] = $all_impose_money;

                $GLOBALS['tmpl']->assign("notice",$notice);

                $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                $msg_data['dest'] = $loan_user_info['email'];
                $msg_data['send_type'] = 1;
                $msg_data['title'] = "“".$deal['name']."”回款通知";
                $msg_data['content'] = addslashes($msg);
                $msg_data['send_time'] = 0;
                $msg_data['is_send'] = 0;
                $msg_data['create_time'] = $time;
                $msg_data['user_id'] = $in_user_id;
                $msg_data['is_html'] = $tmpl['is_html'];
                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
            }
        }

    }

    /*
    $content = "您好，您在".app_conf("SHOP_TITLE")."的借款 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”成功提前还款".number_format($true_total_repay_money,2)."元，";
    $content .= "其中违约金为:".number_format($impose_money,2)."元,本笔借款已还款完毕！";
    */
    $notices['shop_title'] = app_conf("SHOP_TITLE");
    $notices['url'] = "“<a href=\"".$deal['url']."\">".$deal['name']."</a>”";
    $notices['repay_money'] = number_format($true_total_repay_money,2);
    $notices['impose_money'] = number_format($impose_money,2);
    $tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_REPAY_MONEY_TQJK'",false);
    $GLOBALS['tmpl']->assign("notice",$notices);
    $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);
    //站内信
    send_user_msg("",$content,0,$GLOBALS['user_info']['id'],$time,0,true,8);

    //短信通知
    if(app_conf("SMS_ON")==1&&app_conf('SMS_SEND_REPAY')==1){
        //$sms_content = "尊敬的".app_conf("SHOP_TITLE")."用户".$GLOBALS['user_info']['user_name']."，您成功提前还款".number_format($true_total_repay_money,2)."元，其中违约金为:".number_format($impose_money,2)."元,感谢您的关注和支持。【".app_conf("SHOP_TITLE")."】";
        $tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_REPAY_SUCCESS_MSG'",false);
        $tmpl_content = $tmpl['content'];
        $notice['user_name'] = $GLOBALS['user_info']['user_name'];
        $notice['deal_name'] = $deal['sub_name'];
        $notice['site_name'] = app_conf("SHOP_TITLE");
        $notice['index'] = $has_repay_count+1;
        $notice['status'] = "成功提前";
        $notice['all_money'] = number_format($true_total_repay_money,2);
        $notice['repay_money'] = number_format($true_repay_money,2);
        $notice['impose_money'] = number_format($impose_money,2);
        $notice['manage_money'] = number_format($true_manage_money,2);
        $notice['manage_impose_money'] = 0;

        $GLOBALS['tmpl']->assign("notice",$notice);
        $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

        $msg_data['dest'] = $GLOBALS['user_info']['mobile'];
        $msg_data['send_type'] = 0;
        $msg_data['title'] = "提前还款短信通知";
        $msg_data['content'] = $msg;
        $msg_data['send_time'] = 0;
        $msg_data['is_send'] = 0;
        $msg_data['create_time'] = $time;
        $msg_data['user_id'] = $GLOBALS['user_info']['id'];
        $msg_data['is_html'] = 0;
        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
    }

    $GLOBALS['db']->query("UPDATE ".DB_PREFIX."generation_repay_submit SET `memo`='因还款失效',`status`=2 WHERE deal_id=".$deal['id']);


    //VIP升级 -提前还款
    $type = 1;
    $type_info = 4;
    $resultdate = syn_user_vip($GLOBALS['user_info']['id'],$type,$type_info);

    syn_deal_status($id);
    sys_user_status($GLOBALS['user_info']['id'],false,true);
    syn_transfer_status(0,$id);
    $root["status"] = 1;//0:出错;1:正确;
    $root["show_err"] = "操作成功!";
    return $root;
}


/**
 * 获取积分商品
 */
function get_goods($id=0,$is_effect=1)
{
    $goods = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."goods where id = ".intval($id));
    return $goods;
}

/**
 * 获取积分商城商品列表
 */
function get_goods_list($limit="", $where='',$orderby = '')
{
    $count_sql = "select count(*) from ".DB_PREFIX."goods where 1=1  ";
    $sql = "select * from ".DB_PREFIX."goods where 1=1 ";

    if($where != '')
    {
        $sql.=" and ".$where;
        $count_sql.=" and ".$where;
    }

    if($orderby=='')
        $sql.=" order by sort desc ";
    else
        $sql.=" order by ".$orderby;

    if($limit!=""){
        $sql .=" limit ".$limit;
    }

    $goods_count = $GLOBALS['db']->getOne($count_sql);
    if($goods_count > 0){
        $goods = $GLOBALS['db']->getAll($sql);

    }
    else{
        $deals = array();
    }
    return array('list'=>$goods,'count'=>$goods_count);
}

//债权转让常规检测;
function check_trans($id,$paypassword){
    $paypassword = strim($paypassword);
    $id = intval($id);

    $root = array();
    $root["status"] = 0;//0:出错;1:正确;

    if(!$GLOBALS['user_info']){
        $root["show_err"] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
        return $root;
    }


    if($paypassword==""){
        $root["show_err"] = $GLOBALS['lang']['PAYPASSWORD_EMPTY'];
        return $root;
    }

    if(md5($paypassword)!=$GLOBALS['user_info']['paypassword']){
        $root["show_err"] = $GLOBALS['lang']['PAYPASSWORD_ERROR'];//.$GLOBALS['user_info']['paypassword'].';'.md5($paypassword).';'.$paypassword;
        return $root;
    }



    $deal_id = $GLOBALS['db']->getOne("SELECT deal_id FROM ".DB_PREFIX."deal_load_transfer WHERE id=".$id);
    if($deal_id==0){
        $root["show_err"] = "不存在的债权";
        return $root;
    }
    else{
        syn_deal_status($deal_id);
    }

    $condition = ' AND dlt.id='.$id.' AND d.deal_status = 4 and d.is_effect=1 and d.is_delete=0 and d.repay_time_type =1 and  d.publish_wait=0 ';
    $union_sql = " LEFT JOIN ".DB_PREFIX."deal_load_transfer dlt ON dlt.deal_id = dl.deal_id ";

    $sql = 'SELECT dlt.load_id,dlt.id,dlt.t_user_id,dlt.transfer_amount,dlt.user_id,dlt.near_repay_time,d.next_repay_time,d.last_repay_time,d.rate,d.repay_start_time,d.repay_time,dlt.load_money,dlt.id as dltid,dlt.status as tras_status,dlt.t_user_id,dlt.transfer_amount,dlt.create_time as tras_create_time,d.user_id as duser_id,d.ips_bill_no FROM '.DB_PREFIX.'deal_load dl LEFT JOIN '.DB_PREFIX.'deal d ON d.id = dl.deal_id '.$union_sql.' WHERE 1=1 '.$condition;

    $transfer = $GLOBALS['db']->getRow($sql);

    if($transfer){
        if($transfer['user_id']==$GLOBALS['user_info']['id']){
            $root["show_err"] = "不能购买自己转让的债权";
            return $root;
        }

        if($transfer['duser_id']==$GLOBALS['user_info']['id']){
            $root["show_err"] = "不能购买自己的的借贷债权";
            return $root;
        }

        if($transfer['tras_status']==0){
            $root["show_err"] = "债权已撤销";
            return $root;
        }

        if(intval($transfer['t_user_id'])>0){
            $root["show_err"] = "债权已转让";
            return $root;
        }

        //下个还款日
        if(intval($transfer['next_repay_time']) == 0){
            $transfer['next_repay_time'] = next_replay_month($transfer['repay_start_time']);
        }

        if($transfer['next_repay_time'] - TIME_UTC  + 24*3600 - 1 <= 0){
            $root["show_err"] = "债权转让已过期";
            return $root;
        }

        $root["transfer"] = $transfer;
        $root["deal_id"] = $deal_id;
    }
    else{
        $root["show_err"] = "债权转让不存在";
        return $root;
    }
    if($transfer['ips_bill_no']!="")
        $root["status"] = 2;
    else
        $root["status"] = 1;//0:出错;1:正确;
    return $root;
}

//债权转让;
function dotrans($id,$paypassword){
    $paypassword = strim($paypassword);
    $id = intval($id);

    $root = array();
    $root["status"] = 0;//0:出错;1:正确;

    $result = check_trans($id,$paypassword);

    if ($result['status'] == 0){
        $root["show_err"] = $result["show_err"];
        return $root;
    }

    if ($result['status'] == 2){
        $root["status"] = 2;
        $root["jump"] = APP_ROOT."/index.php?ctl=collocation&act=RegisterCretansfer&id=$id&t_user_id=".$GLOBALS['user_info']['id']."&paypassword=".$paypassword."&from=".$GLOBALS['request']['from'];
        $root['jump'] = str_replace("/mapi", "", SITE_DOMAIN.$root['jump']);
        return $root;
    }

    $transfer = $result["transfer"];
    $deal_id = $result["deal_id"];


    if($transfer){
        if(floatval($transfer['transfer_amount']) > floatval($GLOBALS['user_info']['money'])){
            $root["show_err"] = "账户余额不足";
            return $root;
        }

        $transfer_date = to_date(TIME_UTC);
        $GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal_load_transfer set t_user_id = ".$GLOBALS['user_info']['id'].",transfer_time='".TIME_UTC."',transfer_date='".$transfer_date."' WHERE id=".$id." and t_user_id =0 AND status=1 AND near_repay_time- ".next_replay_month(to_timespan(to_date(TIME_UTC,"Y-m-d"),"Y-m-d"),-1)." + 24*3600 - 1 > 0 ");
        if($GLOBALS['db']->affected_rows()){

            //更新相应的回款计划
            $GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal_load_repay SET t_user_id='".$GLOBALS['user_info']['id']."' WHERE  user_id=".$transfer['user_id']." and load_id=".$transfer['load_id']." and repay_time > ".$transfer['near_repay_time'] );

            require APP_ROOT_PATH."/system/libs/user.php";
            //承接人扣除转让费
            modify_account(array("money"=>-floatval($transfer['transfer_amount'])),$GLOBALS['user_info']['id'],"债:Z-".$transfer['load_id'].",承接金",16);
            //转让人接受转让费
            modify_account(array("money"=>floatval($transfer['transfer_amount'])),$transfer['user_id'],"债:Z-".$transfer['load_id'].",转让金",15);

            $user_load_transfer_fee = $GLOBALS['db']->getOne("SELECT user_load_transfer_fee FROM ".DB_PREFIX."deal WHERE id=".$deal_id);
            //扣除转让人的手续费
            if(trim($user_load_transfer_fee)!=""){
                $transfer_fee = $transfer['transfer_amount']*floatval(trim($user_load_transfer_fee));
                if($transfer_fee!=0){
                    $transfer_fee = $transfer_fee / 100;
                }
                modify_account(array("money"=>-floatval($transfer_fee)),$transfer['user_id'],"债:Z-".$transfer['load_id'].",转让管理费",17);
            }


            dotrans_ok($id);


            $root["status"] = 1;//0:出错;1:正确;
            $root["show_err"] = "转让成功";
            return $root;
        }
        else{
            $root["show_err"] = "转让失败";
            return $root;
        }
    }
    else{
        $root["show_err"] = "债权转让不存在";
        return $root;
    }

}

function dotrans_ok($transfer_id){

    $transfer = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_load_transfer where id = ".$transfer_id);
    //发送消息
    $msg_conf = get_user_msg_conf($transfer['user_id']);
    //if($msg_conf['sms_transfer']==1 || $msg_conf['mail_transfer']==1){
    $transfer['tuser'] = get_user("*",$transfer['t_user_id']);
    $transfer['user'] = get_user("*",$transfer['user_id']);
    //}

    if($msg_conf['sms_transfer']==1){
        //您好，您在{$notice.shop_title}的债权{$notice.url}成功转让给：{$notice.url_name}
        //$content = "您好，您在".app_conf("SHOP_TITLE")."的债权 “<a href=\"".url("index","transfer#detail",array("id"=>$transfer['id']))."\">Z-".$transfer['load_id']."</a>” 成功转让给：<a href=\"".$transfer['tuser']['url']."\">".$transfer['tuser']['user_name']."</a>";

        $notices['shop_title']=app_conf("SHOP_TITLE");
        $notices['url']="“<a href=\"".url("index","transfer#detail",array("id"=>$transfer['id']))."\">Z-".$transfer['load_id']."</a>”";
        $notices['url_name']=	"<a href=\"".$transfer['tuser']['url']."\">".$transfer['tuser']['user_name']."</a>";

        $tmpl_contents = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_TRANSFER_REVOKE_USER'",false);
        $GLOBALS['tmpl']->assign("notice",$notices);
        $content = $GLOBALS['tmpl']->fetch("str:".$tmpl_contents['content']);
        send_user_msg("",$content,0,$transfer['user_id'],TIME_UTC,0,true,18);
    }
    //邮件
    if($msg_conf['mail_transfer']==1 && app_conf('MAIL_ON')==1){
        $tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_TRANSFER_SUCCESS'",false);
        $tmpl_content = $tmpl['content'];

        $notice['user_name'] = $transfer['user']['user_name'];
        $notice['transfer_time'] = to_date($transfer['create_time'],"Y年m月d日");
        $notice['transfer_id'] = "Z-".$transfer['load_id'];
        $notice['deal_url'] = SITE_DOMAIN.url("index","transfer#detail",array("id"=>$transfer['id']));
        $notice['site_name'] = app_conf("SHOP_TITLE");
        $notice['site_url'] = SITE_DOMAIN.APP_ROOT;
        $notice['help_url'] = SITE_DOMAIN.url("index","helpcenter");
        $notice['msg_cof_setting_url'] = SITE_DOMAIN.url("index","uc_msg#setting");



        $GLOBALS['tmpl']->assign("notice",$notice);

        $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
        $msg_data['dest'] = $transfer['user']['email'];
        $msg_data['send_type'] = 1;
        $msg_data['title'] = "“债权：Z-".$transfer['load_id']."”转让通知";
        $msg_data['content'] = addslashes($msg);
        $msg_data['send_time'] = 0;
        $msg_data['is_send'] = 0;
        $msg_data['create_time'] = TIME_UTC;
        $msg_data['user_id'] = $transfer['user_id'];
        $msg_data['is_html'] = $tmpl['is_html'];
        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
    }

    if(app_conf('SMS_ON')==1){
        $tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_TRANSFER_SUCCESS'",false);
        $tmpl_content = $tmpl['content'];

        $notice['user_name'] = $transfer['user']['user_name'];
        $notice['transfer_time'] = to_date($transfer['create_time'],"Y年m月d日");
        $notice['transfer_id'] = "Z-".$transfer['load_id'];
        $notice['site_name'] = app_conf("SHOP_TITLE");


        $GLOBALS['tmpl']->assign("notice",$notice);

        $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
        $msg_data['dest'] = $transfer['user']['mobile'];
        $msg_data['send_type'] = 0;
        $msg_data['title'] = "“债权：Z-".$transfer['load_id']."”转让通知";
        $msg_data['content'] = addslashes($msg);
        $msg_data['send_time'] = 0;
        $msg_data['is_send'] = 0;
        $msg_data['create_time'] = TIME_UTC;
        $msg_data['user_id'] = $transfer['user_id'];
        $msg_data['is_html'] = $tmpl['is_html'];
        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
    }

    //发送债权协议
    send_transfer_contract_email($transfer_id);
}


/**
 * 更新 用户回款 计划数据
 * @param unknown_type $deal_id
 * @param unknown_type $deal_repay_id
 */
function syn_deal_repay_status($deal_id,$deal_repay_id){
    //has_repay 0未收到还款，1已收到还款
    $deal_id = intval($deal_id);
    $deal_repay_id = intval($deal_repay_id);

    $deal = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal WHERE id=".$deal_id);
    $deal['url'] = url("index","deal",array("id"=>$deal['id']));
    $deal["user"] = $GLOBALS['db']->getRow("SELECT *,AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name,AES_DECRYPT(email_encrypt,'".AES_DECRYPT_KEY."') as email,AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno,AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."') as money,AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile FROM ".DB_PREFIX."user WHERE id=".$deal['user_id']);

    //未还款记录数
    $sql = "select count(*) from ".DB_PREFIX."deal_load_repay where has_repay = 0 and deal_id = ".$deal_id . " and repay_id = ".$deal_repay_id;
    $has_repay_0 = $GLOBALS['db']->getOne($sql);

    //已经还款记录数
    $sql = "select count(*) from ".DB_PREFIX."deal_load_repay where has_repay = 1 and deal_id = ".$deal_id . " and repay_id = ".$deal_repay_id;
    $has_repay_1 = $GLOBALS['db']->getOne($sql);

    //第几期
    $kk = $GLOBALS['db']->getOne("select l_key from ".DB_PREFIX."deal_load_repay where deal_id = ".$deal_id . " and repay_id = ".$deal_repay_id);



    //has_repay 0未还,1已还 2部分还款
    if (($has_repay_0 == 0 && $has_repay_1 == 0) || ($has_repay_0 == 0 && $has_repay_1 > 0)){

        $deal_rs_sql = "select sum(true_interest_money) as total_true_interest_money," .
            "sum(true_self_money) as total_true_self_money," .
            "sum(true_repay_money) as total_true_repay_money," .
            "sum(true_repay_manage_money) as total_true_repay_manage_money," .
            "sum(repay_manage_impose_money) as total_repay_manage_impose_money, " .
            "sum(true_mortgage_fee) as total_true_mortgage_fee, " .
            "sum(impose_money) as total_impose_money ".
            "from ".DB_PREFIX."deal_load_repay where deal_id = ".$deal_id . " and repay_id = ".$deal_repay_id;

        $deal_rs = $GLOBALS['db']->getRow($deal_rs_sql);
        $last_Rs = $GLOBALS['db']->getRow("SELECT `status`,`true_repay_time`,`repay_time` from ".DB_PREFIX."deal_load_repay where deal_id = ".$deal_id . " and repay_id = ".$deal_repay_id." ORDER BY `true_repay_time` DESC");

        $deal_repay_data['true_repay_money'] =  $deal_rs['total_true_repay_money'];
        $deal_repay_data['true_manage_money'] =  $deal_rs['total_true_repay_manage_money'];
        $deal_repay_data['true_mortgage_fee'] =  $deal_rs['total_true_mortgage_fee'];
        $deal_repay_data['manage_impose_money'] =  $deal_rs['total_repay_manage_impose_money'];
        $deal_repay_data['true_self_repay'] =  $deal_rs['total_true_self_money'];
        $deal_repay_data['impose_money'] =  $deal_rs['total_impose_money'];
        $deal_repay_data['true_interest_money'] =  $deal_rs['total_true_interest_money'];
        $deal_repay_data['true_repay_time'] = $last_Rs['true_repay_time'];
        $deal_repay_data['true_repay_date'] = to_date($last_Rs['true_repay_time']);
        $deal_repay_data['status'] =  $last_Rs['status'];
        $deal_repay_data['has_repay'] =  1;

        //返佣金额
        $rebate_rs = get_rebate_fee($deal['user_id'],"borrow");
        $deal_repay_data['true_manage_money_rebate'] =floatval($deal_repay_data['true_manage_money']) * floatval($rebate_rs['rebate'])/100;

        $true_manage_money_rebate = $deal_repay_data['true_manage_money']* floatval($rebate_rs['rebate'])/100;
        //借款者返佣
        if($true_manage_money_rebate!=0){
            /*ok*/
            $reback_memo = sprintf($GLOBALS['lang']["BORROW_REBATE_LOG"],$deal["url"],$deal["name"],$deal["user"]["user_name"],intval($kk)+1);
            reback_rebate_money($deal['user_id'],$true_manage_money_rebate,"borrow",$reback_memo);
        }

        require_once APP_ROOT_PATH."system/libs/user.php";

        if($last_Rs['status'] > 1){
            $impose_day = ceil(($last_Rs['true_repay_time'] - $last_Rs['repay_time'] + 24*3600 - 1)/24/3600);
            //VIP降级-逾期还款
            $type = 2;
            $type_info = 5;
            $resultdate = syn_user_vip($deal['user_id'],$type,$type_info);

            if($impose_day < app_conf('YZ_IMPSE_DAY')){
                modify_account(array("point"=>trim(app_conf('IMPOSE_POINT'))),$deal['user_id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,逾期还款",11);
                $repay_update_data['status'] = 2;
            }
            else{
                modify_account(array("point"=>trim(app_conf('YZ_IMPOSE_POINT'))),$deal['user_id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,严重逾期",11);
                $repay_update_data['status'] = 3;
            }
        }
        elseif($last_Rs['status']==1){
            //VIP升级 -正常还款
            $type = 1;
            $type_info = 3;
            $resultdate = syn_user_vip($deal['user_id'],$type,$type_info);
        }
        elseif($last_Rs['status']==0){
            //VIP升级 -提前还款
            $type = 1;
            $type_info = 4;
            $resultdate = syn_user_vip($deal['user_id'],$type,$type_info);
        }

        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$deal_repay_data,"UPDATE","id = ".$deal_repay_id);
        $last_repay_key =$kk;
        //判断本借款是否还款完毕
        if($GLOBALS['db']->getOne("SELECT count(*)  FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal['id']." and l_key=".$last_repay_key." AND has_repay <> 1 ") == 0){
            //全部还完
            if($GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal['id']." and has_repay=0 ") == 0){

                //判断获取的信用是否超过限制
                if($GLOBALS['db']->getOne("SELECT sum(point) FROM ".DB_PREFIX."user_point_log WHERE  `type`=6 AND user_id=".$deal['user_id']) < (int)trim(app_conf('REPAY_SUCCESS_LIMIT'))){
                    //获取上一次还款时间
                    $befor_repay_time = $GLOBALS['db']->getOne("SELECT MAX(create_time) FROM ".DB_PREFIX."user_point_log WHERE  `type`=6 AND user_id=".$deal['user_id']);
                    $day = ceil(($last_Rs['true_repay_time']-$befor_repay_time)/24/3600);
                    //当天数大于等于间隔时间 获得信用
                    if($day >= (int)trim(app_conf('REPAY_SUCCESS_DAY'))){
                        modify_account(array("point"=>trim(app_conf('REPAY_SUCCESS_POINT'))),$deal['user_id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],还清借款",4);
                    }
                }

                //用户获得额度
                modify_account(array("quota"=>trim(app_conf('USER_REPAY_QUOTA'))),$deal['user_id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],还清借款获得额度",4);

            }
        }

        sys_user_status(intval($GLOBALS['user_info']['id']),false,true);
        syn_deal_status($deal_id);
        syn_transfer_status(0,$deal_id);

    }else if ($has_repay_0 > 0 && $has_repay_1 == 0){
        $sql = "update ".DB_PREFIX."deal_repay set has_repay = 0 where id = ".$deal_repay_id;
    }else{
        $sql = "update ".DB_PREFIX."deal_repay set has_repay = 2 where id = ".$deal_repay_id;
    }
    $GLOBALS['db']->query($sql);
}

/**
 * 算出出借收益
 */
function bid_calculate($data){
    $uloantype =  intval($data['uloantype']);

    if($uloantype==1){
        $data['money'] = intval($data['money']) * floatval($data['minmoney']);
    }

    $deal['borrow_amount'] = floatval($data['money']);
    $deal['rate'] = floatval($data['rate']);
    $deal['repay_time'] = floatval($data['repay_time']);
    $deal['repay_time_type'] = intval($data['repay_time_type']);
    $deal['loantype'] = intval($data['loantype']);


    if($deal['repay_time_type']==0){
        $all_manage_money = $deal['borrow_amount'] * floatval($data['user_loan_manage_fee']) * 0.01 / 30;
    }
    else{
        $all_manage_money = $deal['borrow_amount'] * floatval($data['user_loan_manage_fee']) * 0.01 * $deal['repay_time'];
    }

    $deal_rs = deal_repay_money($deal);
    $deal_fee['user_loan_interest_manage_fee'] = floatval($data['user_loan_interest_manage_fee']);
    if($GLOBALS['user_info']){
        $deal_fee = get_user_load_fee($GLOBALS['user_info']['id'],0,$deal_fee);
    }
    $all_manage_money += ($deal_rs['remain_repay_money'] - floatval($data['money'])) * floatval($deal_fee['user_loan_interest_manage_fee']) * 0.01;

    return number_format($deal_rs['remain_repay_money'] - floatval($data['money']) - floatval($all_manage_money),2);
}
/*****计算预期收益*****/
function expected_return($data){
    $deal['borrow_amount'] = floatval($data['money']);
    $deal['rate'] = floatval($data['rate'])*0.01;
    $deal['repay_time'] = floatval($data['repay_time']);
    return number_format($deal['borrow_amount']*$deal['rate']*$deal['repay_time']/12,2);
}
/*****计算理财计划预期收益*****/
function plan_expected_return($data){
    $deal['borrow_amount'] = floatval($data['money']);
    $deal['rate'] = floatval($data['rate'])*0.01;
    $deal['repay_time'] = floatval($data['repay_time']);
	//$expect = floor(($deal['borrow_amount']*$deal['rate']*$deal['repay_time']/365)*100)/100;
	$expect = round(($deal['borrow_amount']*$deal['rate']*$deal['repay_time']/365),2);
    return number_format($expect,2);
}
/*****计算体验标预期收益*****/
function experience_deal_return($data){
    $deal['borrow_amount'] = floatval($data['money']); 	//体验金面额
    $deal['rate'] = floatval($data['rate'])*0.01;      	//利率
    $deal['repay_time'] = floatval($data['repay_time']);//出借期限
    return number_format($deal['borrow_amount']*$deal['rate']*$deal['repay_time']/365,2);
}

/*
	体验标
*/
function experience_treetop($limit="",$cate_id=0, $where='',$orderby = '',$user_name='',$user_pwd='',$is_all=false){
    $time = TIME_UTC;
    $count_sql = "select count(*) from ".DB_PREFIX."experience_deal where 1=1 ";
    if($is_all==false)
        $count_sql.=" and is_effect = 1 and is_hidden = 0 and cunguan_tag =1 and publish_wait=1 and deal_status in (1)";

    if(es_cookie::get("shop_sort_field")=="ulevel"){
        $extfield = ",(SELECT u.level_id FROM jctp2p_user u WHERE u.id=user_id ) as ulevel";
    }

    $sql = "select id,name,sub_name,is_new,is_hot,is_recommend,user_id,repay_time,user_bid_rebate,start_time,load_money,(borrow_amount-load_money) as need_money,loantype,repay_time_type,max_loan_money,min_loan_money,rate,deal_status,borrow_amount,start_time as last_time,FORMAT(load_money/borrow_amount*100,2) as progress_point,(start_time + enddate*24*3600 - ".$time.") as remain_time,publish_wait from ".DB_PREFIX."experience_deal where 1=1";

    if($is_all==false)
        $sql.=" and is_effect = 1 and is_hidden = 0 ";

    /*
    if($cate_id>0)
    {
        $ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));
        $sql .= " and cate_id in (".implode(",",$ids).")";
        $count_sql .= " and cate_id in (".implode(",",$ids).")";
    }*/

    if($where != '')
    {
        $sql.=" and ".$where;
        $count_sql.=" and ".$where;
    }

    if($orderby=='')
        $sql.=" order by sort desc ";
    else
        $sql.=" order by ".$orderby;


    if($limit!=""){
        $sql .=" limit ".$limit;
    }


    $deals_count = $GLOBALS['db']->getOne($count_sql);
    if($deals_count > 0){
        $deals = $GLOBALS['db']->getAll($sql);

        if($deals)
        {
            foreach($deals as $k=>$deal)
            {
                //format_deal_item($deal,$user_name,$user_pwd);
                //$deals[$k] = $deal;
                //experdeals
                $deals[$k]['button'] = $deal['deal_status'] ;
                $deals[$k]['url'] = url("index","experdeals",array("id"=>$deal['id']));
            }
        }
    }

    else{
        $deals = array();
    }
    return array('list'=>$deals,'count'=>$deals_count);
}

/**
 * 获取指定的投标
 */
function exper_deal($id){
    $time = TIME_UTC;
    if($id==0)  //有ID时不自动获取
    {
        return false;
    }
    else{
        $deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."experience_deal where id = ".intval($id)."  and is_effect = 1 and publish_wait=1 and is_hidden=0 and is_effect=1");
    }
    return $deal;
}
/**
 * 获取平台加息收益
 */
function get_jct_interest_money($jct_interest,$total_money){
    if($jct_interest['loantype']==0){
        for($i=0;$i<$jct_interest['repay_time'];$i++){
            $month_has_repay_money =pl_it_formula($total_money,$jct_interest['interest_rate']/12/100,$jct_interest['repay_time']);
            $interst += $month_has_repay_money - get_self_money($i,$total_money,$month_has_repay_money,$jct_interest['interest_rate']);
        }
        return $interst;
    }elseif($jct_interest['loantype']==1){
        if($jct_interest['interest_rate']){
            return round(($jct_interest['interest_rate']*$total_money*$jct_interest['repay_time'])/100/12,2);
        }
    }else{
        return false;
    }
}
/**
 * 债转标的投资成功生成还款计划   按月付息 到期还本
 * $data                    投资信息
 * $deal_info               债转标的信息
 * $old_deal_repay_info     原始标的还款信息
 * $load_id                 投资id
 */
function make_repay_plan_loantype1($data,$deal_info,$load_id){
    // 原始标下一次还款信息
    $old_deal_repay_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_repay where deal_id=".$deal_info['old_deal_id']." and has_repay=0 order by repay_time asc limit 1");
    //原始标还款信息
    $old_deal_repay_infos=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_repay where deal_id=".$deal_info['old_deal_id']." and has_repay=0 order by repay_time asc ");
    //原始标已还款信息
    $old_deal_repay = $GLOBALS['db']->getOne('select repay_time from '.DB_PREFIX.'deal_repay where has_repay =1 and deal_id='.$deal_info['old_deal_id'].' order by repay_time desc limit 1');
    // 原始标的信息
    $old_deal_info=$GLOBALS['db']->getRow("select repay_time from ".DB_PREFIX."deal where id=".$deal_info['old_deal_id']);
    //原始标待还款期数
    $old_deal_repay_time=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_repay where deal_id=".$deal_info['old_deal_id']." and has_repay=0 order by repay_time asc");
    //原始标第剩余利息
    $repay_money_fir = $GLOBALS['db']->getAll("select repay_money from ".DB_PREFIX."deal_load_repay where deal_id=".$deal_info['old_deal_id']." and load_id=".$deal_info['old_load_id']." and user_id =".$deal_info['user_id']." and has_repay = 0 order by repay_time asc");
    //原始标第最后一期剩余本金、本息
    $old_deal_repay_last = $GLOBALS['db']->getRow("select self_money,repay_money from ".DB_PREFIX."deal_load_repay where deal_id=".$deal_info['old_deal_id']." and load_id=".$deal_info['old_load_id']." and user_id =".$deal_info['user_id']." and has_repay=0 order by repay_time desc limit 1");
    // 债转标还多少期
    //$debts_repay_time=$old_deal_info['repay_time']-$old_deal_repay_info['l_key'];
    $debts_repay_time=$old_deal_repay_time;
    // 债转标的第一期多少天
    $debts_repay_day=ceil((strtotime(date('Y-m-d',$old_deal_repay_info['repay_time']))+86400-strtotime(date('Y-m-d',$data['create_time'])))/3600/24);
    // 债转标的一共多少天
    $last_repay_time=$GLOBALS['db']->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id=".$deal_info['old_deal_id']." and has_repay=0 order by repay_time desc limit 1");
    $all_repay_day=ceil(($last_repay_time-$data['create_time'])/3600/24);
    //月还利息
    $month_repay_money_f = av_it_formula($data['total_money'],$deal_info['rate']/12/100);
    //月还利息--精确到小数点后两位
    if($old_deal_repay_last['self_money']-$data['total_money']<=0){
        $month_repay_money = $repay_money_fir[1]['repay_money']?$repay_money_fir[1]['repay_money']:0;
        $repay_money = $old_deal_repay_last['repay_money'];
    }else {
        $month_repay_money = round($month_repay_money_f, 2);
        $repay_money = round($data['total_money'] + ($month_repay_money_f*$debts_repay_time) - $month_repay_money*($debts_repay_time-1),2);
    }
    //加息卡收益
    if($data['interestrate_id']>0){
        $sql="select ic.use_time,ic.rate,c.interest_time,c.interest_time_type from ".DB_PREFIX."interest_card ic left join ".DB_PREFIX."coupon c on ic.coupon_id=c.id where ic.id=".$data['interestrate_id'];
        $interest=$GLOBALS['db']->getRow($sql);
        if($interest['use_time']!=0&&$interest['interest_time_type']==0){
            $interestrate_money=format_price(($interest['rate']/100/365) * $data['total_money']*$interest['use_time']);
        }elseif($interest['use_time']==0 && $deal_info['debts']==1){
            $interestrate_money=format_price(($interest['rate']/100/365) * $data['total_money']*$all_repay_day);
        }elseif($interest['use_time']==0 && $deal_info['debts']==0){
            $interestrate_money=format_price(($interest['rate']/100/12) * $data['total_money']*$old_deal_info['repay_time']);
        }elseif($interest['use_time']!=0&&$interest['interest_time_type']==1){
            $interestrate_money=format_price(($interest['rate']/100/12) * $data['total_money']*$interest['use_time']);
        }
    }
    //转让人最后一期应收本息
    $old_load_repay = $GLOBALS['db']->getRow("select self_money,interest_money,repay_money,increase_interest from ".DB_PREFIX."deal_load_repay where  user_id=".$deal_info['user_id']." and load_id=".$deal_info['old_load_id']." order by repay_id desc limit 1");
    // 第一期应还利息
    //$first_repay=($old_deal_repay_info['repay_time']-$data['create_time'])*$data['total_money']*$deal_info['rate']/365/100/3600/24;
    //$first_repay =floor((av_it_formula($data ['total_money'],$deal_info['rate']/12/100)/30)*$debts_repay_day*100)/100;
    //$month_day = ceil(($old_deal_repay_info['repay_time']- $old_deal_repay)/86400);
    //$month_day = date('t',$old_deal_repay_info['repay_time']);
    if(!$old_deal_repay){
        //$month_day = ceil(($old_deal_repay_infos[1]['repay_time']-$old_deal_repay_info['repay_time'])/86400);
        $month_day = date('t',$deal_info['repay_start_time']);
    }else{
        $month_day = date('t',$old_deal_repay+86400);
    }
    /* if(date('Y-m-d',$deal_info['create_time'])==date("Y-m-d")){
        $first_repay = floor((av_it_formula($data ['total_money'],$deal_info['rate']/12/100))*100)/100;
    }else{ */
        $first_repay =floor((av_it_formula($data ['total_money'],$deal_info['rate']/12/100)/$month_day)*$debts_repay_day*100)/100;
    //}
    for($i=0;$i<$debts_repay_time;$i++){
        $repay_data['u_key'] = $deal_info['buy_count']-1;
        $repay_data['l_key'] = $i;
        $repay_data['debts_deal_id'] = $deal_info['id'];
        $repay_data['load_id'] = $load_id;
        $repay_data['repay_id'] = $old_deal_repay_infos[$i]['id'];
        $repay_data['t_user_id'] = 0;
        $repay_data['user_id'] = $data['user_id'];
        $repay_data['repay_time'] = $old_deal_repay_infos[$i]['repay_time']+86400;
        //$repay_data['repay_time'] = strtotime("+" . $i+1 . " months", $data['create_time']);
        $repay_data['repay_date'] = to_date($repay_data['repay_time']);
        if($i == 0){
            $repay_data['repay_money'] = $first_repay;
            $repay_data['increase_interest'] = floor((av_it_formula($data ['total_money'],$deal_info['interest_rate']/12/100)/$month_day)*$debts_repay_day*100)/100;
            if($data['interestrate_id']>0){
                if($debts_repay_time==1){//剩下一期时
                    $interestrate_money = format_price(($interest['rate']/100/12/$month_day) * $data['total_money']*$debts_repay_day);
                }else{
                    if($interest['use_time']!=0){//是否为天加息
                        $interestrate_money = 0;
//                      if($interest['use_time']<=$debts_repay_day){
//                          $interestrate_money = floor(($interest['rate']/100/365) * $data['total_money']*$interest['use_time']*100)/100;
//                      }elseif($interest['use_time']>$debts_repay_day){
//                          $interestrate_money = 0;
//                      }
                    }elseif($interest['use_time']==0){
                        $interestrate_money =floor(($interest['rate']/100/12/$month_day)* $data['total_money']*$debts_repay_day*100)/100;
                    }
                }
                
            }
            if($old_deal_repay_time==1){
                $repay_data['self_money'] =$data['total_money'];
                $repay_data['repay_money'] = $first_repay+$data['total_money'];
            }else{
                $repay_data['self_money'] = 0;
            }

        }elseif($i+1 == $debts_repay_time){
            if(!isset($interestrate_money) || empty($interestrate_money)){
                $interestrate_money=0;
            }
            if($data['interestrate_id']>0){
                if($interest['use_time']==0){//是否为天加息
                    $interestrate_money = floor(av_it_formula($data ['total_money'],$interest['rate']/12/100)*100)/100;
                    
                }else{
                    $last_days = date('t',strtotime('-1 month',$last_repay_time));
                    if($interest['use_time']>0&&$interest['use_time']<=$last_days){
                        $interestrate_money = format_price(($interest['rate']/100/365) * $data['total_money']*$interest['use_time']);
                    }elseif($interest['use_time']>$last_days){
                        $interestrate_money = format_price(($interest['rate']/100/365) * $data['total_money']*$last_days);
                    }else{
                        $interestrate_money=0;
                    }
                }
            }
            // $repay_data['repay_money'] = ($data['total_money'] + round($month_repay_money_f*($debts_repay_time-1)+$first_repay,2)) - round($month_repay_money*($debts_repay_time-2),2)-$first_repay;

            $repay_data['repay_money'] = $repay_money;
            $repay_data['increase_interest'] = av_it_formula($data['total_money'],$deal_info['interest_rate']/12/100);
            if($data['total_money'] == $old_load_repay['self_money']){
                $repay_data['repay_money'] = $old_load_repay['repay_money'];
                $repay_data['increase_interest'] = $old_load_repay['increase_interest'];
            }
            $repay_data['self_money'] = $data['total_money'];

        }else{
            if($data['interestrate_id']>0){
                if($interest['use_time']==0){//是否为天加息
                    $interestrate_money = floor(av_it_formula($data ['total_money'],$interest['rate']/12/100)*100)/100;
                    
                }
            }
            $repay_data['repay_money'] = $month_repay_money;
            $repay_data['self_money'] = 0;
            $repay_data['increase_interest'] = av_it_formula($data['total_money'],$deal_info['interest_rate']/12/100);
        }

        $repay_data['raise_money'] = 0;
        $repay_data['interestrate_money'] = $interestrate_money; //加息券收益
        $repay_data['interest_money'] = $repay_data['repay_money']-$repay_data['self_money'];
        $repay_data['repay_manage_money'] = 0;
        $repay_data['loantype'] = $deal_info['loantype'];
        $repay_data['has_repay'] = 0;
        $repay_data['manage_money'] = 0;
        $repay_data['reward_money'] = 0;
        $repay_data['debts'] = 1;
        $repay_data['deal_id'] = $deal_info['old_deal_id'];
        $repay_data['cunguan_tag'] = 1;
        // 将出借人的还款计划中的对应期金额减掉
        $old_l_key=$i+$old_deal_info['repay_time']-$debts_repay_time;
        $GLOBALS['db']->query("update ".DB_PREFIX."deal_load_repay set repay_money=repay_money-".$repay_data['repay_money'].",self_money=self_money-".$repay_data['self_money'].",interest_money=interest_money-".$repay_data['interest_money'].",increase_interest=increase_interest-".$repay_data['increase_interest']."  where load_id=".$deal_info['old_load_id']." and repay_id=".$repay_data['repay_id']." and user_id =".$deal_info['user_id']);
        /* $GLOBALS['db']->query("update ".DB_PREFIX."deal_load_repay set repay_money=repay_money-".$repay_data['repay_money']." where load_id=".$deal_info['old_load_id']." and repay_id=".$repay_data['repay_id']." and user_id =".$deal_info['user_id']);
        $GLOBALS['db']->query("update ".DB_PREFIX."deal_load_repay set self_money=self_money-".$repay_data['self_money']." where load_id=".$deal_info['old_load_id']." and repay_id=".$repay_data['repay_id']." and user_id =".$deal_info['user_id']);
        $GLOBALS['db']->query("update ".DB_PREFIX."deal_load_repay set interest_money=interest_money-".$repay_data['interest_money']." where load_id=".$deal_info['old_load_id']." and repay_id=".$repay_data['repay_id']." and user_id =".$deal_info['user_id']); */
        $old_repay = $GLOBALS['db']->getRow("select self_money,interest_money from ".DB_PREFIX."deal_load_repay where repay_id =".$repay_data['repay_id']." and user_id=".$deal_info['user_id']." and load_id=".$deal_info['old_load_id']);

        if($old_repay['self_money']==0&&$old_repay['interest_money']==0){
            $datas['repay_money'] =0;
            $datas['self_money'] =0;
            $datas['interest_money'] =0;
            $datas['increase_interest'] =0;
            $datas['raise_money'] =0;
            $datas['interestrate_money'] =0;
            $datas['has_repay'] =1;
            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$datas,"UPDATE","repay_id=".$repay_data['repay_id']." and user_id=".$deal_info['user_id']." and load_id=".$deal_info['old_load_id']);
        }
        /* $GLOBALS['db']->query("update ".DB_PREFIX."deal_load_repay set repay_money=repay_money-".$repay_data['repay_money']." where load_id=".$old_load_id." and repay_id=".$old_l_key);
        $GLOBALS['db']->query("update ".DB_PREFIX."deal_load_repay set self_money=self_money-".$repay_data['self_money']." where load_id=".$old_load_id." and l_key=".$old_l_key);
        $GLOBALS['db']->query("update ".DB_PREFIX."deal_load_repay set insterest_money=insterest_money-".$repay_data['insterest_money']." where load_id=".$old_load_id." and l_key=".$old_l_key); */
        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$repay_data,"INSERT");
    }
    return true;
}
/**
 * 债转标的投资成功生成还款计划   等额本息
 * $data                    投资信息
 * $deal_info               债转标的信息
 * $old_deal_repay_info     原始标的还款信息
 * $load_id                 投资id
 */
function make_repay_plan_loantype0($data,$deal_info,$load_id){
    // 原始标下一次还款信息
    $old_deal_repay_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_repay where deal_id=".$deal_info['old_deal_id']." and has_repay=0 order by repay_time asc limit 1");
    //原始标还款信息
    $old_deal_repay_infos=$GLOBALS['db']->getAll("select id,repay_time from ".DB_PREFIX."deal_repay where deal_id=".$deal_info['old_deal_id']." and has_repay=0 order by repay_time asc ");
    // 原始标的信息
    $old_deal_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id=".$deal_info['old_deal_id']);
    //原始标待还款期数
    $old_deal_repay_time=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_repay where deal_id=".$deal_info['old_deal_id']." and has_repay=0 order by repay_time asc");
    // 债转标还多少期
    //$debts_repay_time=$old_deal_info['repay_time']-$old_deal_repay_info['l_key'];
    $debts_repay_time=$old_deal_repay_time;
    // 债转标的第一期多少天
    $debts_repay_day=ceil(($old_deal_repay_info['repay_time']-$data['create_time'])/3600/24);
    // 债转标的一共多少天
    $last_repay_time=$GLOBALS['db']->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id=".$deal_info['id']." and has_repay=0 order by repay_time desc limit 1");
    $all_repay_day=ceil(($last_repay_time-$data['create_time'])/3600/24);
    //月还本息
    $month_repay_money_f = pl_it_formula($data ['total_money'],$deal_info['rate']/12/100,$debts_repay_time);
    //月还本息--精确到小数点后两位
    $month_repay_money = $month_repay_money_f;

    // 第一期本金
    $first_self_money=round(get_self_money(0,$data['total_money'],$month_repay_money,$deal_info['rate']),2);
    // 第一期应还利息
    //$first_repay=round(($month_repay_money-$first_self_money)/30*$debts_repay_day,2);
    $first_repay=$data['total_money']*($deal_info['rate']/12/100*(pow(1+$deal_info['rate']/12/100,$debts_repay_time)-pow(1+$deal_info['rate']/12/100,0)))/(pow(1+$deal_info['rate']/12/100,$debts_repay_time)-1)/30*$debts_repay_day;
    $total_self_money=0;
    for($i=0;$i<$debts_repay_time;$i++){
        $repay_data['u_key'] = $deal_info['buy_count']-1;
        $repay_data['l_key'] = $i;
        $repay_data['debts_deal_id'] = $deal_info['id'];
        $repay_data['load_id'] = $load_id;
        $repay_data['repay_id'] = $old_deal_repay_infos[$i]['id'];
        $repay_data['t_user_id'] = 0;
        $repay_data['user_id'] = $data['user_id'];
        $repay_data['repay_time'] = $old_deal_repay_infos[$i]['repay_time']+86400;
        //$repay_data['repay_time'] = strtotime("+" . $i+1 . " months", $data['create_time']);
        $repay_data['repay_date'] = to_date($repay_data['repay_time']);
        if($i == 0){
            if($deal_info['interest_rate']>0){
                $repay_data['increase_interest'] = $data['total_money']*($deal_info['interest_rate']/12/100*(pow(1+$deal_info['interest_rate']/12/100,$deal_info['repay_time'])-pow(1+$deal_info['interest_rate']/12/100,$i)))/(pow(1+$deal_info['interest_rate']/12/100,$deal_info['repay_time'])-1)/30*$debts_repay_day;
            }
            $repay_data['repay_money'] = $first_repay+$first_self_money;
        }elseif($i+1 == $debts_repay_time){
            if(!isset($interestrate_money) || empty($interestrate_money)){
                $interestrate_money=0;
            }
            //$repay_data['repay_money'] = (round($month_repay_money_f*($debts_repay_time-1)+$first_repay+$first_self_money,2)) - $month_repay_money*($debts_repay_time-2)-$first_repay-$first_self_money;
            //最后一期应还本息
            $repay_data['repay_money'] = round($month_repay_money*$debts_repay_time,2) - round($month_repay_money,2)*($debts_repay_time-1);
            $repay_data['increase_interest'] = $data['total_money']*($deal_info['interest_rate']/12/100*(pow(1+$deal_info['interest_rate']/12/100,$deal_info['repay_time'])-pow(1+$deal_info['interest_rate']/12/100,$i)))/(pow(1+$deal_info['interest_rate']/12/100,$deal_info['repay_time'])-1);
        }else{
            $repay_data['repay_money'] = $month_repay_money;
            $repay_data['increase_interest'] = $data['total_money']*($deal_info['interest_rate']/12/100*(pow(1+$deal_info['interest_rate']/12/100,$deal_info['repay_time'])-pow(1+$deal_info['interest_rate']/12/100,$i)))/(pow(1+$deal_info['interest_rate']/12/100,$deal_info['repay_time'])-1);
        }
        $repay_data['raise_money'] = 0;
        $repay_data['interestrate_money'] = 0; //加息券收益   转让标的不可用加息卡
        $repay_data['self_money'] = round(get_self_money($i,$data['total_money'],$month_repay_money,$deal_info['rate']),2);
        if($i==($debts_repay_time-1)){
            $repay_data['self_money']=$data['total_money']-$total_self_money;
        }
        $total_self_money+=$repay_data['self_money'];
        $repay_data['interest_money'] = round($repay_data['repay_money']-$repay_data['self_money'],2);
        $repay_data['repay_manage_money'] = 0;
        $repay_data['loantype'] = $deal_info['loantype'];
        $repay_data['has_repay'] = 0;
        $repay_data['debts'] = 1;
        $repay_data['manage_money'] = 0;
        $repay_data['reward_money'] = 0;
        $repay_data['cunguan_tag'] = 1;
        $repay_data['deal_id'] = $deal_info['old_deal_id'];
        $repay_data['plan_id'] = $deal_info['plan_id'];
        // 将出借人的还款计划中的对应期金额减掉
        $old_l_key=$i+$old_deal_info['repay_time']-$debts_repay_time;
        $GLOBALS['db']->query("update ".DB_PREFIX."deal_load_repay set repay_money=repay_money-".$repay_data['repay_money'].",self_money=self_money-".$repay_data['self_money'].",interest_money=interest_money-".$repay_data['interest_money']." ,increase_interest=increase_interest-".$repay_data['increase_interest']." where load_id=".$deal_info['old_load_id']." and repay_id=".$repay_data['repay_id']." and user_id =".$deal_info['user_id']);
        /* $GLOBALS['db']->query("update ".DB_PREFIX."deal_load_repay set self_money=self_money-".$repay_data['self_money']." where load_id=".$deal_info['old_load_id']." and repay_id=".$repay_data['repay_id']." and user_id =".$deal_info['user_id']);
        $GLOBALS['db']->query("update ".DB_PREFIX."deal_load_repay set interest_money=interest_money-".$repay_data['interest_money']." where load_id=".$deal_info['old_load_id']." and repay_id=".$repay_data['repay_id']." and user_id =".$deal_info['user_id']); */
        $old_repay = $GLOBALS['db']->getRow("select self_money,interest_money from ".DB_PREFIX."deal_load_repay where repay_id =".$repay_data['repay_id']." and user_id=".$deal_info['user_id']." and load_id=".$deal_info['old_load_id']);
        if($old_repay['self_money']==0&&$old_repay['interest_money']==0){
            $datas['repay_money'] =0;
            $datas['self_money'] =0;
            $datas['interest_money'] =0;
            $datas['increase_interest'] =0;
            $datas['raise_money'] =0;
            $datas['interestrate_money'] =0;
            $datas['has_repay'] =1;
            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$datas,"UPDATE","repay_id=".$repay_data['repay_id']." and user_id=".$deal_info['user_id']." and load_id=".$deal_info['old_load_id']);
        }
        /* $GLOBALS['db']->query("update ".DB_PREFIX."deal_load_repay set self_money=self_money-".$repay_data['self_money']." where load_id=".$old_load_id." and l_key=".$old_l_key);
        $GLOBALS['db']->query("update ".DB_PREFIX."deal_load_repay set insterest_money=insterest_money-".$repay_data['insterest_money']." where load_id=".$old_load_id." and l_key=".$old_l_key); */
        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$repay_data,"INSERT");
    }
    return true;
}
?>