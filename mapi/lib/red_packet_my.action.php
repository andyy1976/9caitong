<?php
//我的红包接口
class red_packet_my{

    public function index(){

        $root = get_baseroot();
        $user = $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        if($user['id']>0){
            $root['response_code'] = 1;
            $red_conf = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."red_packet_config order by id desc limit 1");
            $min_money = $red_conf['min_money'];
            $root['money_msg'] = '未使用的现金红包';
            $root['condition'] = '满'.$min_money.'元可兑换成余额';
            //抢到的红包总额
            $root['red_money_obtained'] = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."red_packet where user_id=".$user['id']." and red_type_id=12");
            $root['red_money_obtained'] = "已累计兑换". strval(floor(($root['red_money_obtained'])*100)/100) .'元';
            
            $root['red_money_total'] = $user['new_red_money'];
            $root['red_money_total'] = $root['red_money_total'] ? $root['red_money_total'] :'0';
            $root['header_url'] = $user['header_url'] ? $user['header_url'] : WAP_SITE_DOMAIN."/app/Tpl/wap/images/wap2/my/head_img.png";
            
            $url = WAP_SITE_DOMAIN . "/index.php?ctl=find&act=W645&code=" . $user['mobile'];
            $root['wx_share']['url'] = $url;
            $root['wx_share']['icon'] = $red_conf['icon'];
            $root['wx_share']['content'] = $red_conf['body'];
            $root['wx_share']['title'] = $red_conf['title'];
            
            output($root);
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '请先登录';
            output($root);
        }
    }

}