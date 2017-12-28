<?php

/**
 * 抢红包接口
 * Class red_packet_rob
 */
class red_packet_rob{

    private $rob_status;
    public function index(){
        $red_packet_id = strim(base64_decode($GLOBALS['request']['red_packet_id']));
        $friend_id = strim(base64_decode($GLOBALS['request']['friend_id']));
        $A_MachineInfo = strim($GLOBALS['request']['MachineInfo']);
        $root = get_baseroot();
        $root['ttt']=time();
        $user = $GLOBALS['user_info'];
        if($user['user_type']=='1'){
        	$root['response_code'] = 0;
        	$root['show_err'] = '企业用户暂不可使用';
        	output($root);
        }
        $today_time = strtotime(date('Y-m-d'));
        $next_monday_time = strtotime("next monday"); //下周一零点
        $root['session_id'] = es_session::id();
        if($user['id']>0){
            if(empty($red_packet_id) || empty($friend_id)){
                $root['response_code'] = 0;
                $root['show_err'] = '参数错误';
                output($root);
            }
            if($user['red_packet_status']==0){
                $root['response_code'] = 0;
                $root['show_err'] = '您涉嫌违规操作，暂停抢红包功能';
                output($root);
            }
            if($user['is_effect'] != 1){
                $root['response_code'] = 0;
                $root['show_err'] = '您的帐号异常，请联系客服';
                output($root);
            }
            $MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
            $Android = explode("|||",$A_MachineInfo);
            $redis = new Redis();
            //             $redis->connect("127.0.0.1", 6379);
            $redis->connect(REDIS_HOST, REDIS_PORT);
            $redis->auth(REDIS_PWD);
            $redis->select(8);
            
            
//             if($MachineInfo[0] == 'iOS' &&  str_replace(".","",$MachineInfo[3]) <= '218'){
                
//             }else{
//                 $user_last_time = $redis->get(REDIS_PREFIX.'lasttime'.$user['id']);
//                 if($user_last_time === false){
//                     $redis->setex(REDIS_PREFIX.'lasttime'.$user['id'], strtotime(date('Y-m-d 23:59:59'))-time(), time());
//                 }elseif(($user_last_time+1) > time()){
//                     $root['response_code'] = 0;
//                     $root['show_err'] = '操作频繁';
// //                     $GLOBALS['db']->query("update ".DB_PREFIX."user set red_packet_status=0 where id = ".$user['id']);
//                     output($root);
//                 }else{
//                     $redis->setex(REDIS_PREFIX.'lasttime'.$user['id'], strtotime(date('Y-m-d 23:59:59'))-time(), time());
//                 }
//             }
            
            $red_packet = json_decode($redis->get(REDIS_PREFIX.'pid'.$red_packet_id), true);
            $user_send_packet_id = $redis->get(REDIS_PREFIX.'user_platform_packet'.$friend_id);
            if(!$red_packet || empty($red_packet)){
                $root['response_code'] = 0;
                $root['show_err'] = '系统繁忙请稍后再试';
                output($root);
            }
            if(empty($user_send_packet_id) || $user_send_packet_id != $red_packet_id){
                $root['response_code'] = 0;
                $root['show_err'] = '非法请求';
                output($root);
            }
            
            $user_friends = $GLOBALS['db']->getAll("select friend_id from ".DB_PREFIX."red_packet_friends where user_id=".$user['id']);
            $ids = array();
            foreach ($user_friends as $k=>$v){
                $ids[] = $v['friend_id'];
            }
            if(in_array($red_packet['user_id'],$ids)){
                $root['red_info']['realname'] = $red_packet['realname'];
                if($red_packet['type'] == 1){
                    $root['red_info']['new_realname'] = $red_packet['realname'] ? $red_packet['realname'].' 的红包' : '';
                }else{
                    $root['red_info']['new_realname'] = $red_packet['realname'] ? $red_packet['realname'].' 的好友红包' : '';
                }
            }else{
                $root['red_info']['realname']=$red_packet['realname'] ? '*'.cut_str($red_packet['realname'], 1, -1):'';
                if($red_packet['type'] == 1){
                    $root['red_info']['new_realname'] = $red_packet['realname'] ? '*'.cut_str($red_packet['realname'], 1, -1).' 的红包':'';
                }else{
                    $root['red_info']['new_realname'] = $red_packet['realname'] ? '*'.cut_str($red_packet['realname'], 1, -1).' 的好友红包':'';
                }
            }
            $root['red_info']['header_url'] = $red_packet['header_url'];
            $root['red_info']['mobile'] =  cut_str($red_packet['mobile'], 3, 0).'****'.cut_str($red_packet['mobile'], 2, -2);
            if($red_packet['type'] == 1){
                $root['red_info']['new_mobile'] =  cut_str($red_packet['mobile'], 3, 0).'****'.cut_str($red_packet['mobile'], 2, -2).' 的红包';
                $root['red_info']['red_sum_money'] = '红包共'. $red_packet['send_red_money'] .'元'; 
            }else{
                $root['red_info']['new_mobile'] =  cut_str($red_packet['mobile'], 3, 0).'****'.cut_str($red_packet['mobile'], 2, -2).' 的好友红包';
                $root['red_info']['red_sum_money'] = '好友红包共'. $red_packet['send_red_money'] .'元';
            }
            
            if($user['id'] == $red_packet['user_id']){
                $root['red_info']['mobile'] =  '我';
                $root['red_info']['realname'] = '我';
                if($red_packet['type'] == 1){
                    $root['red_info']['new_mobile'] =  '我 的红包';
                    $root['red_info']['new_realname'] = '我 的红包';
                }elseif ($red_packet['type'] == 2){
                    $root['red_info']['new_mobile'] =  '我 的好友红包';
                    $root['red_info']['new_realname'] = '我 的好友红包';
                }
            }
            
            
            //该红包被抢走多少钱
            $rob_friend_money = $red_packet['send_red_money'] - $red_packet['last_money'];
            //剩余红包金额
            $total = $red_packet['last_money'];

            //该红包被抢记录
            $rob_friend_money_all = $this->rob_list_info($redis,$red_packet_id,$red_packet['last_money'],$user_friends);
            if($this->rob_status){
                $root['response_code'] = 1;
                $root['red_info']['red_status'] = '2';//已抢过
                $root['red_info']['rob_red_money'] = strval($this->rob_status['rob_red_money']); //抢到多少钱
                $root['red_info']['send_red_money'] = strval($red_packet['send_red_money']); //该红包总额
                $root['rob_friend_money_all']=$rob_friend_money_all;
                output($root);
            }
            //抢到红包总额是否等于发红包总额,抢到总个数是否等于发红包总个数
            if($rob_friend_money >= $red_packet['send_red_money'] || count($rob_friend_money_all) >= $red_packet['red_num']){
                $root['red_info']['red_status'] = '3';//被抢光
                $root['rob_friend_money_all'] = $rob_friend_money_all;
                $root['red_info']['rob_red_money'] = strval($this->rob_status['rob_red_money']);
                $root['red_info']['send_red_money'] = strval($red_packet['send_red_money']);
                $root['response_code'] = 1;
                output($root);
            }else{
                
                //防止该用户超过抢红包次数  
                $user_rob_num = $redis->get(REDIS_PREFIX.'rob_num'.$user['id']);
                //当天奖励次数
                $user_reward_num = $redis->get(REDIS_PREFIX."reward".$user['id']);
                //邀请好友奖励次数
                $share_num = $redis->get(REDIS_PREFIX.'share_num'.$user['id']);
                //当天总共能抢多少次
                $rob_num_sum = $user_reward_num + 10;
                
                //防止该用户超过抢红包次数  
                if(empty($user_rob_num)){
                    $redis->incr(REDIS_PREFIX.'rob_num'.$user['id']);
                    $redis->expire(REDIS_PREFIX.'rob_num'.$user['id'], strtotime(date('Y-m-d 23:59:59'))-time());
                }elseif ($user_rob_num >= $rob_num_sum){
                    if($share_num > 0){//判断邀请好友的奖励用完没有
                        $redis->decr(REDIS_PREFIX.'share_num'.$user['id']);
                        $redis->incr(REDIS_PREFIX.'rob_num'.$user['id']);
                    }else {
                        //兼容老版本
                        if($MachineInfo[0]=='iOS' &&  str_replace(".","",$MachineInfo[3]) >= '218'){
                            $root['response_code'] = 3;
                            $root['show_err'] = '抢红包机会用完咯~';
                        }elseif ($Android[0]=='Android' &&  str_replace(".","",$Android[1]) >= '220'){
                            $root['response_code'] = 3;
                            $root['show_err'] = '抢红包机会用完咯~';
                        }else{
                            $root['response_code'] = 0;
                            $root['show_err'] = '每天最多抢10个红包哦，明天再来抢吧！';
                        }
                        $root['url'] = WAP_SITE_DOMAIN."/index.php?ctl=score&act=user_red_log";
                        output($root);
                    }
                }else{
                    $redis->incr(REDIS_PREFIX.'rob_num'.$user['id']);
                }
                //防止同一用户抢同一红包1次以上
                if($redis->incr(REDIS_PREFIX.$user['id'].'is_rob'.$red_packet_id)>1){
                    $root['response_code'] = 0;
                    $root['show_err'] = '您已抢过该红包哦';
                    output($root);
                }else{
                    $redis->expire(REDIS_PREFIX.$user['id'].'is_rob'.$red_packet_id, strtotime(date('Y-m-d 23:59:59'))-time());
                }
                
                $rob_red_money = $redis->lPop(REDIS_PREFIX."money_list".$red_packet_id);
                if(empty($rob_red_money) || $rob_red_money <= 0){
                    $root['response_code'] = 0;
                    $root['show_err'] = '手慢了，红包已被抢光！';
                    output($root);
                }
                
                if($red_packet['red_num']- count($rob_friend_money_all) == 1){
                    $this->insert_list($redis, $red_packet_id, $rob_red_money);
                    $total = $rob_red_money;
                    $GLOBALS['db']->startTrans();
                    $GLOBALS['db']->getAll("select * from ".DB_PREFIX."red_packet_rob where red_packet_id=".$red_packet_id." FOR UPDATE");
                    //最后一个红包
                    $result = $this->redpacket_rob($user['id'],$total,$friend_id,$red_packet_id,$red_packet['type']);
                    if($result){
                        $rob_friend_money_all = $this->rob_list_info($redis,$red_packet_id,0,$user_friends);
                        $moneys = $user['new_red_money'] + $total;
                        $GLOBALS['db']->query("update ".DB_PREFIX."user set new_red_money = ".$moneys." where id = ".$user['id']);
                        $root['red_info']['rob_red_money'] = $total;
                        $root['rob_friend_money_all'] = $rob_friend_money_all;
                        $root['red_info']['send_red_money'] = $red_packet['send_red_money'];
                        $root['response_code'] = 1;
                        if(count($rob_friend_money_all)>$red_packet['red_num']){
                            $root['red_info']['red_status'] = 3 ;
                            $GLOBALS['db']->rollback();
                            $root['rob_friend_money_all'] = $this->rob_list_info($redis,$red_packet_id,$red_packet['last_money'],$user_friends);
                        }else{
                            $redis->zIncrBy(REDIS_PREFIX.date('Ymd'), $total, $user['id']);
                            $redis->zIncrBy(REDIS_PREFIX.'red_money_total', $total, $user['id']);
                            $root['red_info']['red_status'] = 1 ;
                            $GLOBALS['db']->commit();
                            
                        }

                    }else{
                        $GLOBALS['db']->rollback();
                    }

                }else{

//                     $min =0.01;
//                     $num = $red_packet['red_num']- count($rob_friend_money_all);
//                     $max = $total-$min*$num;

//                     $kmix = max($min, $total - $num * $max);
//                     $kmax = min($max, $total - $num * $min);
//                     $kAvg = $total / ($num + 1);
                    //获取最大值和最小值的距离之间的最小值
//                     $kDis = min($kAvg - $kmix, $kmax - $kAvg);
                    //获取0到1之间的随机数与距离最小值相乘得出浮动区间，这使得浮动区间不会超出范围
//                     $r = ((float)(mt_rand(1, 10000) / 10000)-0.3 ) * $kDis * 2;
//                     $rob_red_money = round($kAvg + $r,2);



                    /*$safe_total = ($total - ($red_packet['red_num'] - count($rob_friend_money_all)) * $min) / ($red_packet['red_num']-count($rob_friend_money_all)) ;//随机安全上限
                    $rob_red_money = mt_rand($min * 100 , $safe_total * 100) / 100;*/
                    
                    $new_last_money = $this->insert_list($redis, $red_packet_id, $rob_red_money);
                    $GLOBALS['db']->startTrans();
                    $GLOBALS['db']->getAll("select * from ".DB_PREFIX."red_packet_rob where red_packet_id=".$red_packet_id." FOR UPDATE");
                    
                    $result = $this->redpacket_rob($user['id'],$rob_red_money,$friend_id,$red_packet_id,$red_packet['type']);

                    if($result){
                        $GLOBALS['db']->query("update ".DB_PREFIX."red_packet_send set red_packet_status = 0 where id = ".$red_packet_id);
                        $rob_friend_money_all = $this->rob_list_info($redis,$red_packet_id,$new_last_money,$user_friends);
                        $moneys = $user['new_red_money'] + $rob_red_money;
                        $GLOBALS['db']->query("update ".DB_PREFIX."user set new_red_money = ".$moneys." where id = ".$user['id']);
                        $root['red_info']['rob_red_money'] = $rob_red_money;
                        $root['red_info']['send_red_money'] = $red_packet['send_red_money'];
                        $root['rob_friend_money_all'] = $rob_friend_money_all;
                        $root['response_code'] = 1;
                        $root['red_info']['red_status'] = 1 ;
                        if(count($rob_friend_money_all)>$red_packet['red_num']){
                            $root['red_info']['red_status'] = 3 ;
                            $GLOBALS['db']->rollback();
                        }else{
                            $redis->zIncrBy(REDIS_PREFIX.date('Ymd'), $rob_red_money, $user['id']);
                            $redis->zIncrBy(REDIS_PREFIX.'red_money_total', $rob_red_money, $user['id']);
                            $root['red_info']['red_status'] = 1 ;
                            $GLOBALS['db']->commit();
                            
                        }
                    }else{
                        $GLOBALS['db']->rollback();
                    }
                }

                output($root);
            }

        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '请先登录';
            output($root);
        }
    }

    
    /**
     * 抢红包记录入redis队列
     * @param $redis redis对象
     * @param $red_packet_id 红包ID
     * @param $rob_red_money 抢到的金额
     *
     */
    private function insert_list($redis, $red_packet_id, $rob_red_money){
        //修改红包剩余金额和个数 并更新redis记录
        $red_packet = json_decode($redis->get(REDIS_PREFIX.'pid'.$red_packet_id), true);
        $red_packet['last_money'] = $red_packet['last_money'] - $rob_red_money;
        $red_packet['last_num'] = $red_packet['last_num'] - 1;
        if($red_packet['type'] == 1) {//平台红包设置过期时间
            $redis->setex(REDIS_PREFIX.'pid'.$red_packet_id, strtotime(date('Y-m-d 23:59:59'))-time(), json_encode($red_packet));
        }else{
            $redis->set(REDIS_PREFIX.'pid'.$red_packet_id, json_encode($red_packet));
        }
        //新抢到的记录入队列
        $rob_packet['user_id'] = $GLOBALS['user_info']['id'];
        $rob_packet['sum_money'] = $red_packet['send_red_money'];
        $rob_packet['rob_red_money'] = $rob_red_money;
        $rob_packet['header_url'] = $GLOBALS['user_info']['header_url'];
        $rob_packet['realname'] = $GLOBALS['user_info']['real_name'];
        $rob_packet['mobile'] = $GLOBALS['user_info']['mobile'];
        $rob_packet['rob_date'] = date('H:i',time());
        $redis->rPush(REDIS_PREFIX.'rob_list'.$red_packet_id, json_encode($rob_packet));
        $redis->expire(REDIS_PREFIX.'rob_list'.$red_packet_id, strtotime(date('Y-m-d 23:59:59'))-time());
        return $red_packet['last_money'];//更新后的红包余额
    }
    
    /**
     * 获取红包队列里面所有信息
     * @param $redis redis对象
     * @param $red_packet_id 红包ID
     * @param $last_money 抢到的金额
     *
     */
    private function rob_list_info($redis, $red_packet_id, $last_money, $user_friends){
        for($i=0; $i<$redis->lLen(REDIS_PREFIX.'rob_list'.$red_packet_id); $i++){
            $rob_friend_money_all[$i] = json_decode($redis->lindex(REDIS_PREFIX.'rob_list'.$red_packet_id,$redis->lLen(REDIS_PREFIX.'rob_list'.$red_packet_id)-$i-1), true);
        }
//         $user_friends = $GLOBALS['db']->getAll("select friend_id from ".DB_PREFIX."red_packet_friends where user_id=".$GLOBALS['user_info']['id']);
        $ids = array();
        foreach ($user_friends as $k=>$v){
            $ids[] = $v['friend_id'];
        }
        $temp = 0;
        foreach ($rob_friend_money_all as $k=>$v){
            if($rob_friend_money_all[$k]['rob_red_money'] > $temp){
                $temp = $rob_friend_money_all[$k]['rob_red_money'];
            }
            if(in_array($v['user_id'],$ids)){
                $rob_friend_money_all[$k]['realname'] = $rob_friend_money_all[$k]['realname'];
                $rob_friend_money_all[$k]['mobile'] =  cut_str($rob_friend_money_all[$k]['mobile'], 3, 0).'****'.cut_str($rob_friend_money_all[$k]['mobile'], 2, -2);
            }else{
                $rob_friend_money_all[$k]['realname'] = $rob_friend_money_all[$k]['realname'] ? '*'.cut_str($rob_friend_money_all[$k]['realname'], 1, -1):'';
                $rob_friend_money_all[$k]['mobile'] =  cut_str($rob_friend_money_all[$k]['mobile'], 3, 0).'****'.cut_str($rob_friend_money_all[$k]['mobile'], 2, -2);
            }
            $rob_friend_money_all[$k]['rob_red_money'] = sprintf("%.2f",$rob_friend_money_all[$k]['rob_red_money']).'元';
            if($GLOBALS['user_info']['id'] == $v['user_id']){
                $rob_friend_money_all[$k]['mobile'] =  '我';
                $rob_friend_money_all[$k]['realname'] = '我';
                $this->rob_status = $v;
            }
        }
        if($last_money <= 0 ){
            foreach ($rob_friend_money_all as $k=>$v){
                if($rob_friend_money_all[$k]['rob_red_money'] == $temp){
                    $rob_friend_money_all[$k]['mvp'] = 1;
                    break;
                }
            }
        }
        
        return $rob_friend_money_all;
    }
    
    
    /**
     * 红包被抢记录
     * @param $user_id 用户ID
     * @param $red_packet_id  红包ID
     * @return mixed
     * @author:zhuxiang
     
    protected function friend_red_logs($user_id,$red_packet_id){
        //该红包的被抢记录
        $rob_friend_money_all = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."red_packet_rob where red_packet_id=".$red_packet_id." order by rob_time desc");
        $user_friends = $GLOBALS['db']->getAll("select friend_id from ".DB_PREFIX."red_packet_friends where user_id=".$user_id);
        $ids = array();
        foreach ($user_friends as $k=>$v){
            $ids[] = $v['friend_id'];
        }
        foreach ($rob_friend_money_all as $k=>$v){
            $friend_infos = $GLOBALS['db']->getRow("select header_url,AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as realname,AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') as mobile from ".DB_PREFIX."user where id=".$v['user_id']);
            if(in_array($v['user_id'],$ids)){
                $rob_friend_money_all[$k]['realname'] = $friend_infos['realname'];
                $rob_friend_money_all[$k]['mobile'] =  cut_str($friend_infos['mobile'], 3, 0).'****'.cut_str($friend_infos['mobile'], 2, -2);
            }else{
                $rob_friend_money_all[$k]['realname'] = $friend_infos['realname'] ? '*'.cut_str($friend_infos['realname'], 1, -1):'';
                $rob_friend_money_all[$k]['mobile'] =  cut_str($friend_infos['mobile'], 3, 0).'****'.cut_str($friend_infos['mobile'], 2, -2);
            }
            $rob_friend_money_all[$k]['rob_date'] = date('H:i',$v['rob_time']);
            $rob_friend_money_all[$k]['rob_red_money'] = $rob_friend_money_all[$k]['rob_red_money'].'元';
            if($user_id == $v['user_id']){
                $rob_friend_money_all[$k]['mobile'] =  '我';
                $rob_friend_money_all[$k]['realname'] = '我';
            }
            $rob_friend_money_all[$k]['header_url'] = $friend_infos['header_url'];
        }
        return $rob_friend_money_all;
    }*/

    /**
     * 抢红包和红包记录入库
     * red_packet_rob constructor.
     * @param $user_id 用户ID
     * @param $total 红包金额
     * @param $friend_id 好友ID
     * @param $red_packet_id 红包ID
     * @return boolean
     *
     */
    protected function redpacket_rob($user_id,$total,$friend_id,$red_packet_id,$type){
        $MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
        $rob_data['user_id'] = $user_id;
        $rob_data['rob_red_money'] = $total;
        $rob_data['friend_id'] = $friend_id;
        $rob_data['rob_time'] = TIME_UTC;
        $rob_data['rob_date'] = date('Y-m-d H:i:s');
        $rob_data['type'] = $type;
        $rob_data['red_packet_id'] = $red_packet_id;
        if($MachineInfo[0]=='iOS') {
            $rob_data['source'] = 1;
        }elseif ($MachineInfo[0]=='Android') {
            $rob_data['source'] = 2;
        }
        $resl = $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_rob",$rob_data,"INSERT");
        $red_data_log['user_id'] = $user_id;
        $red_data_log['red_money'] = $total;
        $red_data_log['new_red_money'] = $total + $GLOBALS['user_info']['new_red_money'];
        $red_data_log['addtime'] = date('Y-m-d H:i:s');
        $red_data_log['remark'] = '抢红包成功';
        $red_data_log['type'] = $type;
        $red_data_log['action'] = 2;
        $resll = $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_log",$red_data_log,"INSERT");
        if($resl && $resll){
            return true;
        }else{
            return false;
        }
    }
    
}