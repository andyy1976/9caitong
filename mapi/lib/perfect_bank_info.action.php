<?php
//完善银行信息接口

class perfect_bank_info{
    
    public function index(){
        $root=get_baseroot();
        $bank_card = strim(base64_decode($GLOBALS['request']['bank_card']));//银行卡
        $bank_city = strim(base64_decode($GLOBALS['request']['bank_city'])); //开户城市
        $bank_branch = strim(base64_decode($GLOBALS['request']['bank_branch'])); //所属支行
        $user = $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        if($user['id']>0){
            $root['user_login_status'] = 1;
            if(empty($bank_card)){
                $root['response_code'] = 0;
                $root['show_err'] ="银行卡不能为空";
                output($root);
            }
            $bankcard = $GLOBALS['db']->getOne("SELECT bankcard FROM ".DB_PREFIX."user_bank WHERE user_id=".$user['id']." AND bankcard=".$bank_card);
            if(empty($bankcard)){
                $root['response_code'] = 0;
                $root['show_err'] ="银行卡错误";
                output($root);
            }

            if(empty($bank_city) || empty($bank_branch)){
                $root['response_code'] = 0;
                $root['show_err'] ="开户城市或所属支行不能为空";
            }else{
                $bank_arr = explode(" ",$bank_city);
                $region_lv2 = $GLOBALS['db']->getOne("select `id` from ".DB_PREFIX."region_conf where `name` = '".trim($bank_arr[0])."'");
                $region_lv3 = $GLOBALS['db']->getOne("select `id` from ".DB_PREFIX."region_conf where `name` = '".trim($bank_arr[1])."'");
                $bank_info = $bank_branch;
                $res = $GLOBALS['db']->query("update ".DB_PREFIX."user_bank set bankzone='".$bank_info."',region_lv2=".$region_lv2.",region_lv3=".$region_lv3.",region_lv4='' where user_id = ".$user['id']);
                if($res){
                    $root['response_code'] = 1;
                    $root['show_err'] ="修改成功";
                }else{
                    $root['response_code'] = 0;
                    $root['show_err'] ="修改失败";
                }
            }
        }else{
            $root['response_code'] = 0;
            $root['show_err'] ="未登录";
            $root['user_login_status'] = 0;
        }
        output($root);
    }
    
}





?>