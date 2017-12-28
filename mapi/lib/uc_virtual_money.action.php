<?php
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_virtual_money
{
    public function index(){

        $root = get_baseroot();

        $user =  $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        $user_id  = intval($user['id']);
        if ($user_id >0){
            $time=time();
            $root['user_login_status'] = 1;
            $root['response_code'] = 1;
            $root['load_red_count']=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id = ".$user_id." and rp.status=0 and rpn.red_type=1 and rp.end_time>$time");
            if($root['load_red_count']){
                $root['load_red_count']=$root['load_red_count']."张可用";
            }else{
                $root['load_red_count']="0张可用";
            }
            $root['cash_red_count']=$GLOBALS['db']->getOne("select sum(rp.money) from ".DB_PREFIX."red_packet rp left join ".DB_PREFIX."red_packet_newconfig rpn on rp.red_type_id=rpn.id where rp.user_id = ".$user_id." and rp.status=0 and rpn.red_type=3 and rp.end_time>$time");
            if($root['cash_red_count']){
                $root['cash_red_count']=$root['cash_red_count']."元";
            }else{
                $root['cash_red_count']="0元";
            }
            $root['interest_card_count']=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."interest_card where user_id = ".$user_id." and status=0 and end_time>".$time);
            if($root['interest_card_count']){
                $root['interest_card_count']=$root['interest_card_count']."张可用";
            }else{
                $root['interest_card_count']="0张可用";
            }
        }else{
            $root['response_code'] = 0;
            $root['show_err'] ="未登录";
            $root['user_login_status'] = 0;
        }
        $root['url']=SITE_DOMAIN."/member.php?ctl=uc_set&act=help&id=49&title=优惠券与体验金";
        output($root);
    }
}
?>
