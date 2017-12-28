<?php

//银行信息弹出层接口


class bank_info_tip{

    public function index(){

        $root = get_baseroot();
        $user = $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();

        if($user['id']>0){
            $root['response_code'] = 1;
            $root['bank_help_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_set&act=help&id=47';
            output($root);
        }else{
            $root['response_code'] = 0;
            $root['show_err'] = '请先登录';
            output($root);
        }

    }


}