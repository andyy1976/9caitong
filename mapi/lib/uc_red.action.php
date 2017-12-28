<?php
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_red
{
    public function index(){

        $root = get_baseroot();

        $user =  $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        $user_id  = intval($user['id']);
        $machine_info=explode('|||',base64_decode($GLOBALS['request']['MachineInfo']));
        if ($user_id >0){
            require APP_ROOT_PATH.'app/Lib/uc_func.php';
            $root['user_login_status'] = 1;
            $root['response_code'] = 1;
            $page = intval(base64_decode($GLOBALS['request']['page']));
            $type_id = intval(base64_decode($GLOBALS['request']['type_id']));
            $condition='';
            if($page==0)
                $page = 1;

            /*$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");*/
            $limit = " 0,1000";
            // 获取用户中心的红包列表    type_id  1加息卡   2 投资红包  3现金红包
            $timenow =time();
            if($type_id==1){
                $condition=" ";
                $result=get_uc_interest_card_list($limit,$user_id,$condition);
                $root['new_jiaxi_card_id'] =$GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."interest_card where user_id =".$user_id. " order by id desc limit 1");
            }elseif($type_id==2){
                $condition.=" and rpn.red_type=1";
                $result = get_uc_red_list($limit,$user_id,$condition);
                $root['new_coupon_red_id']=$GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."red_packet where user_id =".$user_id. " and  packet_type=1  order by id desc limit 1");
            }elseif($type_id==3){
                $condition.=" and rpn.red_type=3";
                $result = get_uc_red_list($limit,$user_id,$condition);
                $root['new_cash_red_id']=$GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."red_packet where user_id =".$user_id. "  and  packet_type=3 order by id desc limit 1");
            }
		
//            foreach ($result['list'] as $k => $v) {
//                if($v['status'] == 0 && $v['end_time'] >time()){
//                    $v['max_use_money'] = $v['money']*$v['ratio'];
//                    $v['begin_time'] =date("Y-m-d",$v['begin_time']);
//                    $v['end_time'] =date("Y-m-d",$v['end_time']);
//                    $v['status_format'] = "未使用";
//                    $not_use[] = $v;
//                }
//                if($v['status'] == 1){
//                    $v['max_use_money'] = $v['money']*$v['ratio'];
//                    $v['begin_time'] =date("Y-m-d",$v['begin_time']);
//                    $v['end_time'] =date("Y-m-d",$v['end_time']);
//                    $v['status_format'] = "已使用";
//                    $use[] = $v;
//                }
//                if($v['status'] != 1 && $v['end_time'] < time()){
//                    $v['max_use_money'] = $v['money']*$v['ratio'];
//                    $v['begin_time'] =date("Y-m-d",$v['begin_time']);
//                    $v['end_time'] =date("Y-m-d",$v['end_time']);
//                    $v['status_format'] = "已过期";
//                    $expired[] = $v;
//                }
//            }
            foreach ($result['list'] as $k => $v) {

                //去掉前面逗号 cy
                $res = strpos($v['use_condition'],',');

                if($res ===0){
                    $v['use_condition'] = substr($v['use_condition'],1);
                	//$result['list'][$k]['use_condition'] =ltrim($v['use_condition'],","); gby
                }

                if($v['red_type']==3){
                    $v['url']="";
                }

                if($type_id==1&&$v['interest_time']==0){
                    $v['interest_time']="全程加息";
                }elseif($type_id==1&&$v['interest_time']!=0){
                    if($machine_info[0]=='Android'){
                        $v['interest_time']=$v['interest_time'];
                    }else{
                        $v['interest_time']="加息".$v['interest_time']."天";
                    }
                }

                if($v['status'] == 0 && $v['end_time'] >time()){
                    $v['max_use_money'] = strval(intval($v['ratio']));
                    $v['begin_time'] =date("Y-m-d",$v['begin_time']);
                    $v['end_time'] =date("Y-m-d",$v['end_time']);
                    $v['status_format'] = "未使用";
                    $not_use[] = $v;
                }elseif($v['status'] == 1){
                    $v['max_use_money'] = strval(intval($v['ratio']));

                    $v['begin_time'] =date("Y-m-d",$v['begin_time']);
                    $v['end_time'] =date("Y-m-d",$v['end_time']);
                    $v['status_format'] = "已使用";
                    $use[] = $v;
                }else{
                    $v['max_use_money'] = strval(intval($v['ratio']));
                    $v['begin_time'] =date("Y-m-d",$v['begin_time']);
                    $v['end_time'] =date("Y-m-d",$v['end_time']);
                    $v['status_format'] = "已过期";
                    $expired[] = $v;
                }
            }
            $root['response_code'] = 1;
            if($not_use == null){
                $not_use=array();
                $root['item']['not_use'] = $not_use;
            }else{
                $root['item']['not_use'] = $not_use;
            }
            if($expired == null){
                $expired=array();
                $root['item']['expired'] = $expired;
            }else{
                $root['item']['expired'] = $expired;
            }
            if($use == null){
                $use=array();
                $root['item']['use'] = $use;
            }else{
                $root['item']['use'] = $use;
            }
            $root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/app_conf("PAGE_SIZE")),"page_size"=>app_conf("PAGE_SIZE"));

        }else{
            $root['response_code'] = 0;
            $root['show_err'] ="未登录";
            $root['user_login_status'] = 0;
        }
//        $voucher_explain = strip_tags($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'voucher_explain'"));
//        $root['voucher_explain'] = str_replace("。","。\n\n" ,$voucher_explain);
//        $red_explain = strip_tags($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'red_explain'"));
//        $root['red_explain'] = str_replace("。","。\n\n" ,$red_explain);
//        $root['program_title'] = "代金券";
        output($root);
    }
}
?>
