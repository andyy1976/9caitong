<?php

//自动投标接口

class uc_auto_invest_delete{

    public function index(){
        $user = $GLOBALS['user_info'];
        $id = intval(strim(base64_decode($GLOBALS['request']['id'])));
        $root = get_baseroot();
        $root['session_id'] = es_session::id();
        if($user['id']>0){
            if(empty($id)){
                $root['response_code'] = 0;
                $root['user_login_status'] = 0;
                $root['show_err'] = '参数错误';
                output($root);
            } 
            $time = time();
            $data = $GLOBALS['db']->getRow("select money,status from ".DB_PREFIX."auto_invest_config where id=".$id." and user_id=".$user['id']);
            if($data['status'] == 1){
                //将之前设置金额解冻
                modify_account(array('cunguan_money'=>$data['money'],'cunguan_lock_money'=>-$data['money']),$user['id'],"自动投标返还",48,"自动投标返还",1);
            }
            $rs = $GLOBALS['db']->query("update ".DB_PREFIX."auto_invest_config set is_delete=1,status=0,update_time=".$time." where id = ".$id." and user_id = ".$user['id']);
            if($rs){
                $root['response_code'] = 1;
                $root['show_err'] = "删除成功!";
                output($root);
            }else{
                $root['response_code'] = 0;
                $root['show_err'] = "删除失败!";
                output($root);
            }
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }
    }

}