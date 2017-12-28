<?php
require APP_ROOT_PATH.'app/Lib/deal_func.php';
//虚拟货币体验金接口

class experience_gold{
    public function index(){    
        $root = get_baseroot(); 
        $user_data = $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        $user_id  = intval($user_data['id']);

        if ($user_id >0){
            $nowtime = TIME_UTC; 
            $root['user_login_status'] = 1;     
            $root['response_code'] = 1;               
            $root['nowtime'] = $nowtime;

			$deal = $GLOBALS['db']->getAll("select money,end_time,create_time,use_status,id from ".DB_PREFIX."taste_cash where user_id = ".intval($user_id)."  and use_status=0 and cunguan_tag=1");
            //$foruse = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."experience_deal_load  where user_id = ".intval($user_id)."  and cunguan_tag=1");
            //$root['foruse'] = $foruse;

            foreach ($deal as $key => $v) {
                    $v['create_time']=date("Y-m-d",$v['create_time']);
                    $v['end_time']=date("Y-m-d",$v['end_time']);
                    $v['money'] =strstr($v['money'],'.',true);
                    $v['use_status']  = $v['use_status'];
                    $v['id'] = $v['id'];
                    $v['direction'] ='你好呀';   
                    $list[] = $v;
            }

            if(!$deal){ 
                $root['item'] ='';    
            }
            $voucher_explain = strip_tags($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'cg_explain'"));
            $root['direction'] =str_replace("。","。\n\n" ,$voucher_explain);
            $root['item'] = $list;
        }else{ 

            $root['response_code'] = 0;
            $root['show_err'] ="未登录";
            $root['user_login_status'] = 0;
        }   
        output($root);      
    }
}