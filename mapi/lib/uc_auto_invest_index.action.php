<?php

//自动投标接口

class uc_auto_invest_index{

    public function index(){
        $user = $GLOBALS['user_info'];
//         $type = intval(strim(base64_decode($GLOBALS['request']['type']))); 
        $root = get_baseroot();
        $root['session_id'] = es_session::id();
        if($user['id']>0){
        	
            //判断是否绑卡
            $user_bank = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user_bank where user_id=".intval($user['id'])." AND cunguan_tag=1");
            //判断是否开通存管
            if($user['user_type']=='1'){
            	if($user['cunguan_tag'] == 0){
            		$root['three_code'] = 1;
            		$root['three_err'] = '请企业认证';
            		$root['jump_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_account&act=index';
            	}
           }else{
           	if($user['cunguan_tag'] == 0){
           		$root['three_code'] = 1;
           		$root['three_err'] = '请您先开通银行存管账户';
           		$root['jump_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_account&act=index';
           	}elseif ($user['cunguan_pwd'] == 0){//判断是否设置存管交易密码
           		$root['three_code'] = 2;
           		$root['three_err'] = '请您先设置存管系统交易密码';
           		$root['jump_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_paypassword&act=setpaypassword';
           	}elseif (empty($user_bank)){
           		$root['three_code'] = 3;
           		$root['three_err'] = '请您先绑定存管系统的银行卡';
           		$root['jump_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_addbank&act=wap_check_pwd';
           	}
           }
            
            
            
            $root['cunguan_money'] = $user['cunguan_money'] ? $user['cunguan_money'] : 0;
            $root['list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."auto_invest_config where user_id=".$user['id']." and is_delete=0");
            foreach ($root['list'] as $k=>$v){
                $root['list'][$k]['title'] = '自动投标';
                $root['list'][$k]['del_str'] = $this->getDelStr($root['list'][$k]['is_ordinary'], $root['list'][$k]['is_advance'], $root['list'][$k]['is_debts']);
                $root['list'][$k]['deadline_str'] = $this->getDeadLineStr($root['list'][$k]['deadline_start'], $root['list'][$k]['deadline_end']);
                $root['list'][$k]['dealtype_str'] = $this->getDealType($root['list'][$k]['is_ordinary'], $root['list'][$k]['is_advance'], $root['list'][$k]['is_debts']);
                if(!$root['list'][$k]['is_long']){
                    $root['list'][$k]['start_time_str'] = date("Y-m-d",$root['list'][$k]['start_time']);
                    $root['list'][$k]['end_time_str'] = date("Y-m-d",$root['list'][$k]['end_time']);
                }
                $root['list'][$k]['money'] = strval(intval($root['list'][$k]['money']));
            }
            $count  = $GLOBALS['db']->getAll("select user_id from ".DB_PREFIX."auto_invest_config where status=1 group by user_id");
            $root['start_num'] = strval(count($count));
            $time = $GLOBALS['db']->getOne("select update_time from ".DB_PREFIX."auto_invest_config where user_id=".$user['id']." and is_delete=0 and status=1 order by id asc limit 1");
            if($time){
//                 $a = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."auto_invest_config where update_time<".$time." and is_delete=0 and status=1");
                $result = $GLOBALS['db']->getAll("select user_id,update_time from ".DB_PREFIX."auto_invest_config where status=1 group by user_id order by update_time asc");
                foreach ($result as $k=>$v){
                    if($result[$k]['user_id'] == $user['id']){
                        $rank = $k;
                    }
                }
                $root['rank'] = strval($rank + 1);
            }else{
                $root['rank'] = 0;
            }
            $root['deadline'] = array('0','1','3','6','12');
            $root['response_code'] = 1;
            $root['rule_url'] = "https://" . $_SERVER['HTTP_HOST'] . "/member.php?ctl=uc_autoinvest&act=rule";
            $root['protocol_url'] = "https://" . $_SERVER['HTTP_HOST'] . "/member.php?ctl=uc_autoinvest&act=protocol";
            //最低起头金额
            $root['min_money'] = '100';
            if($user['user_type']=='1'){
            	$root['response_code'] = 0;
            	$root['show_err'] = '企业用户暂不可使用';
            	output($root);
            }
            output($root);
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }
    }

    /*
     * $is_ordinary 是否普通标
     * $is_advance 是否预售标
     * $is_debts 是否转让标
     */
    public function getDelStr($is_ordinary,$is_advance,$is_debts){
        
        if(($is_ordinary && $is_advance && $is_advance) || ($is_ordinary && $is_advance) || ($is_ordinary && $is_debts)){
            $str = "普通标等";
        }elseif ($is_advance && $is_debts){
            $str = "预售标等";
        }elseif ($is_ordinary){
            $str = "普通标";
        }elseif ($is_advance){
            $str = "预售标";
        }elseif($is_debts){
            $str = "转让标";
        }
        return $str;
    }
    
    public function getDeadLineStr($deadline_start,$deadline_end){
        
        if($deadline_start == 0 || $deadline_end == 0){
            $str = "不限";
        }elseif($deadline_start == $deadline_end){
            $str = $deadline_start."个月";
        }else{
            $str = $deadline_start.'-'.$deadline_end.'个月';
        }
        return $str;
    }
    
    public function getDealType($is_ordinary,$is_advance,$is_debts){
        $str = '';
        if($is_ordinary){
            $str .= '普通标、';
        }
        if($is_advance){
            $str .= '预售标、';
        }
        if($is_debts){
            $str .= '转让标、';
        }
        $str = rtrim($str, '、');
        return $str;
    }
}