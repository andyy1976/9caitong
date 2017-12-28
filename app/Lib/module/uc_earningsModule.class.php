<?php

require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_earningsModule extends SiteBaseModule
{
    function index() {
    	$user_statics = sys_user_status($GLOBALS['user_info']['id']);
    	$user_statics['all_load_money'] = $user_statics['load_earnings'] + $user_statics['reward_money'] + $user_statics['load_tq_impose'] + $user_statics['load_yq_impose'] + $user_statics['rebate_money'] + $user_statics['referrals_money'] - $user_statics['carry_fee_money']- $user_statics['incharge_fee_money'];
        $money_log= get_user_money_info($GLOBALS['user_info']['id']);
        // 可用余额
        $money_log['money']=sprintf('%.2f',floatval($GLOBALS['user_info']['money']));
        // 冻结余额
        // $money_log['lock_money']=sprintf('%.2f',floatval($GLOBALS['user_info']['lock_money']));
        // $money_log['lock_money']= sprintf('%.2f', floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_carry where user_id= ".$GLOBALS['user_info']['id']." and status in (1,3)")));
        // print_r($money_log);die;
        $GLOBALS['tmpl']->assign("money_log",$money_log);
        $GLOBALS['tmpl']->assign("user_statics",$user_statics);
    	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_EARNINGS']);

    	$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_earnings.html");
		$GLOBALS['tmpl']->display("page/uc.html");
    }
}
?>