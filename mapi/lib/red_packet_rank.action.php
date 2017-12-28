<?php

/**
 * 红包排行榜接口
 * Class red_packet_rank
 */
class red_packet_rank{

    /**
     * @author:zhuxiang
     */
    public function index(){
        $user = $GLOBALS['user_info'];
        $page = intval(base64_decode($GLOBALS['request']['page']));
        $root = get_baseroot();
        $redis = new Redis();
        //$redis->connect('127.0.0.1', 6379);
        $redis->connect(REDIS_HOST, REDIS_PORT);
        $redis->auth(REDIS_PWD);
        $redis->select(8);
        $root['session_id'] = es_session::id();
        if($user['id']>0){
            //昨日排行榜
            $yesterday_rank_list = $redis->zRevRange(REDIS_PREFIX.date('Ymd',strtotime('-1 day')), 0, 99, true);
            foreach ($yesterday_rank_list as $key=>$value){
                $rank_info = json_decode($redis->hGet(REDIS_PREFIX.'user_info',$key),true);
                if(empty($rank_info)){
                    $rank_info=$GLOBALS['db']->getRow("select id,real_name as realname,header_url,mobile from ".DB_PREFIX."user where id=".$key);
                    $rank_info['mobile'] = cut_str($rank_info['mobile'], 3, 0).'****'.cut_str($rank_info['mobile'], 2, -2);

                    $redis->hSet(REDIS_PREFIX.'user_info',$key,json_encode($rank_info));
                }
                $rank_info['realname']=$rank_info['realname']?'*'.cut_str($rank_info['realname'], 1, -1):'';
                if($user['id'] == $key){
                    $rank_info['realname'] = '我';
                    $rank_info['mobile'] = '我';
                }

                $rank_info['red_money_total'] = strval(sprintf("%.2f", $value));
                $yesterday_rank[]=$rank_info;
            }

            $yesterday_people_count = $redis->zCard(REDIS_PREFIX.date('Ymd',strtotime('-1 day')));
            $root['yesterday_people'] = "昨日共".($yesterday_people_count+2000)."人参与抢红包";

            $num = $redis->zRevRank(REDIS_PREFIX.date('Ymd',strtotime('-1 day')),$user['id']);
            $root['num'] = $num;
            if($num===false){
                $num = '未参与';
            }else{
                $num = $num+1;
            }
            $root['yesterday_me'] = "我当前排名：".$num;
            $root['yesterday_rank'] = $yesterday_rank?$yesterday_rank:null;
            //七日排行榜
            for($i = 1; $i < 8; $i++){
                $dates[] =  date('Ymd', strtotime('-'.$i.' day'));
            }
            $keys = array_map(function($date) {
                return REDIS_PREFIX . $date;
            }, $dates);

            $weights = array_fill(0, count($keys), 1);
            $redis->zUnion('seven_day', $keys, $weights);
            $sevenday_rank_list =  $redis->zRevRange('seven_day', 0, 99, true);
            foreach ($sevenday_rank_list as $key=>$value){
                $seven_rank_info = json_decode($redis->hGet(REDIS_PREFIX.'user_info',$key),true);
                if(empty($seven_rank_info)){
                    $seven_rank_info=$GLOBALS['db']->getRow("select id,real_name as realname,header_url,mobile from ".DB_PREFIX."user where id=".$key);
                    $seven_rank_info['mobile'] = cut_str($seven_rank_info['mobile'], 3, 0).'****'.cut_str($seven_rank_info['mobile'], 2, -2);
                    $redis->hSet(REDIS_PREFIX.'user_info',$key,json_encode($seven_rank_info));
                }
                $seven_rank_info['realname']=$seven_rank_info['realname']?'*'.cut_str($seven_rank_info['realname'], 1, -1):'';
                if($user['id'] == $key){
                    $seven_rank_info['realname'] = '我';
                    $seven_rank_info['mobile'] = '我';
                }

                $seven_rank_info['red_money_total'] =strval(sprintf("%.2f", $value));
                $seven_rank[]=$seven_rank_info;
            }

            $seven_people_count = $redis->zCard('seven_day');
            $root['seven_people'] = "近七日共".($seven_people_count+5000)."人参与抢红包";
            $num1 = $redis->zRevRank('seven_day',$user['id']);
            if($num1===false){
                $num1 = '未参与';
            }else{
                $num1 = $num1+1;
            }
            $root['seven_rank'] = $seven_rank?$seven_rank:'';
            $root['seven_me'] = "我当前排名：".$num1;
            $root['response_code'] = 1;
            output($root);
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }
    }
}