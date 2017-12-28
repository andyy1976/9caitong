<?php

//体验金使用明细接口

class experience_money_detail{

    public function index(){
        $roots = get_baseroot();
        $user = $GLOBALS['user_info'];
        if($user['id']>0){
            $root = experience_money_detail($user['id']);
            $root['licai_open'] = $roots['licai_open'];
            $root['user_name'] = $roots['user_name'];
            $root['session_id']=es_session::id();
            $root['response_code'] = 1;
            output($root);
        }else{
            $root['response_code'] = 0;
            $root['show_err'] = '请先登录';
            output($root);
        }


        /*******************************以下代码都被封装**********************************/
        
        $weekday = array('星期日','星期一','星期二','星期三','星期四','星期五','星期六');

        //if($user['id']>0){
            $root['response_code'] =1;
            $detail = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."taste_cash_log where user_id=".$user['id']." order by create_time desc");
            foreach($detail as $k=>$v){
                $times = date('Y-m-d',$v['create_time']);//体验表使用记录时间
                $today = date('Y-m-d',time());//今天
                $yesterday = date('Y-m-d',strtotime("-1 day"));//昨天
                if($times==$today){
                    $root['item'][$k]["week"] = "今天";
                }elseif($times==$yesterday){
                    $root['item'][$k]["week"] = "昨天";
                }else{
                    $root['item'][$k]['week'] = $weekday[date('w',$v['create_time'])];
                }
                $root['item'][$k]['time'] = date("H:i",$v['create_time']);
                $root['item'][$k]['addtime'] = date("Y-m-d",$v['create_time']);
                $root['item'][$k]['money'] = $v['change'];
                if($v['change']>0){
                    $root['item'][$k]['icon'] = '1';
                }else{
                    $root['item'][$k]['icon'] = '0';
                }
                $root['item'][$k]['detail'] = $v['detail'];
            }
            $root['show_err']="操作成功";
            output($root);
        //}else{
          //  $root['response_code'] = 0;
          //  $root['show_err'] = '请先登录';
           // output($root);
       // }



    }



}