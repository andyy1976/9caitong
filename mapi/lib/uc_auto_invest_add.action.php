<?php

//自动投标添加配置接口

class uc_auto_invest_add{

    public function index(){
        $user = $GLOBALS['user_info'];
        $root = get_baseroot();
        $id = intval(strim(base64_decode($GLOBALS['request']['id'])));
        $root['session_id'] = es_session::id();
        if($user['user_type']=='1'){
        	$root['response_code'] = 0;
        	$root['show_err'] = '企业用户暂不可使用';
        	output($root);
        }
        
//         $root['response_code'] = 0;
//         $root['show_err'] = '敬请期待';
//         output($root);
        
        
        if($user['id']>0){
            $count = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."auto_invest_config where user_id=".$user['id']." and is_delete=0");
            if(!$id && $count >= 3){
                $root['response_code'] = 0;
                $root['show_err'] = '每人只能添加3条规则';
                output($root);
            }
            $update_time = $GLOBALS['db']->getOne("select update_time from ".DB_PREFIX."auto_invest_config where user_id=".$user['id']." and status=1 and is_delete=0 order by update_time asc");
            $data['cid'] = $id;
            $data['user_id'] = $user['id'];
            $data['deadline_start'] = intval(strim(base64_decode($GLOBALS['request']['deadline_start'])));
            $data['deadline_end'] = intval(strim(base64_decode($GLOBALS['request']['deadline_end'])));
            $data['money'] = intval(strim(base64_decode($GLOBALS['request']['money'])));
            $data['set_money'] = intval(strim(base64_decode($GLOBALS['request']['money'])));
            $data['is_new'] = intval(strim(base64_decode($GLOBALS['request']['is_new'])));
            $data['is_debts'] = intval(strim(base64_decode($GLOBALS['request']['is_debts'])));
            $data['is_advance'] = intval(strim(base64_decode($GLOBALS['request']['is_advance'])));
            $data['is_ordinary'] = intval(strim(base64_decode($GLOBALS['request']['is_ordinary'])));
            $data['is_long'] = intval(strim(base64_decode($GLOBALS['request']['is_long'])));
            $data['start_time'] = strtotime(strim(base64_decode($GLOBALS['request']['start_time'])));
            $data['end_time'] = strtotime(strim(base64_decode($GLOBALS['request']['end_time']))." 23:59:59");
            $data['is_part_load'] = intval(strim(base64_decode($GLOBALS['request']['is_part_load'])));
            $data['create_time'] = time();
            $data['update_time'] = $update_time ? $update_time : time();
            $data['status'] = 1;
            if($data['deadline_start'] == 0 || $data['deadline_end'] == 0){
                $data['deadline_start'] = 0;
                $data['deadline_end'] = 0;
            }
            if(empty($data['money'])){
                $root['response_code'] = 0;
                $root['show_err'] = "请填写金额";
                output($root);
            }
            $cunguan_money = $GLOBALS['db']->getOne("select AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as money from ".DB_PREFIX."user where id=".$user['id']);
            if($data['money'] > $cunguan_money){
                $root['response_code'] = 0;
                $root['show_err'] = "余额不足";
                output($root);
            }
            if($data['money'] < 100){
                $root['response_code'] = 0;
                $root['show_err'] = "最低100元起投";
                output($root);
            }
            if($data['deadline_start'] > $data['deadline_end']){
                $result['status'] = 0;
                $result['info'] = "投标期限错误";
                ajax_return($result);
            }
            if(!$data['is_debts'] && !$data['is_advance'] && !$data['is_ordinary']){
                $result['status'] = 0;
                $result['info'] = "请选择投标类型";
                ajax_return($result);
            }
            if($data['is_long'] != 1){
                if(empty($data['start_time']) || empty($data['end_time'])){
                    $root['response_code'] = 0;
                    $root['show_err'] = "请填写有效期";
                    output($root);
                }
                if($data['start_time'] > $data['end_time']){
                    $root['response_code'] = 0;
                    $root['show_err'] = "结束日期必须大于开始日期";
                    output($root);
                }
                if($data['end_time'] < time()){
                    $root['response_code'] = 0;
                    $root['show_err'] = "结束日期已经过期";
                    output($root);
                }
                if(!$id){
                    if($data['start_time'] < strtotime(date("Y-m-d",time()))){
                        $root['response_code'] = 0;
                        $root['show_err'] = "开始日期最小为当天";
                        output($root);
                    }
                }
            }
           
            $rs = $GLOBALS['db']->autoExecute(DB_PREFIX."auto_invest_config_log",$data,"INSERT");
            $insert_id = intval($GLOBALS['db']->insert_id());
            if($rs){
                $root['response_code'] = 1;
                $root['jump'] = "https://" . $_SERVER['HTTP_HOST'] . "/index.php?ctl=uc_autoinvest&act=check_pwd&id=".$insert_id;
                output($root);
            }else{
                $root['response_code'] = 0;
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