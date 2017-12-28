<?php

//发红包接口

class red_packet_send{

    public function index(){
        $user = $GLOBALS['user_info'];
        if($user['user_type']=='1'){
        	$root['response_code'] = 0;
        	$root['show_err'] = '企业用户暂不可使用';
        	output($root);
        }
        $type = intval(strim(base64_decode($GLOBALS['request']['type']))); //1平台 2好友
        $root = get_baseroot();
        $root['session_id'] = es_session::id();
        $today_time = strtotime(date('Y-m-d'));
        if($user['id']>0){
            if($type==2){
                $root['response_code'] = 0;
                $root['show_err'] = '第三方服务器升级，好友红包暂不可用。';
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
            if(empty($type)) $type = 1;//1平台 2好友 兼容老版本 //1平台 2好友 兼容老版本
            
            $redis = new redis();
//             $redis->connect("127.0.0.1", 6379);
            $redis->connect(REDIS_HOST, REDIS_PORT);
            $redis->auth(REDIS_PWD);
            $redis->select(8);
            if($redis->incr(REDIS_PREFIX.'send_num'.$user['id'])>1){
                $root['response_code'] = 0;
                $root['show_err'] = '红包每天只能发一个哦';
                output($root);
            }else{
                $redis->expire(REDIS_PREFIX.'send_num'.$user['id'], strtotime(date('Y-m-d 23:59:59'))-time());
            }
            
            //查询当天所发平台红包和好友红包是否被抢光
//             $platform_packet_id = $redis->get(REDIS_PREFIX.'user_platform_packet'.$user['id']);
//             $friend_packet_id = $redis->zRevRangeByScore(REDIS_PREFIX.'user_friend_packet'.$user['id'], strtotime(date('Y-m-d 23:59:59')), strtotime(date('Y-m-d')));
            
//             if(!empty($platform_packet_id)){
//                 $platform_packet_info = json_decode($redis->get(REDIS_PREFIX.'pid'.$platform_packet_id), true);
//                 if($platform_packet_info['last_money'] > 0){
//                     $root['response_code'] = 0;
//                     $root['show_err'] = '红包每天只能发一个哦';
//                     output($root);
//                 }
//             }
//             if(!empty($friend_packet_id)){
//                 foreach($friend_packet_id as $v){
//                     $friend_packet_info = json_decode($redis->get(REDIS_PREFIX.'pid'.$v), true);
//                     if($friend_packet_info['last_money'] > 0){
//                         $root['response_code'] = 0;
//                         $root['show_err'] = '您发的好友红包被抢光后，才能继续发哦！';
//                         output($root);
//                     }
//                 }
//             }
            
            //优先读取redis抢红包配置信息
            $red_packet_config = $redis->hGetAll(REDIS_PREFIX.'red_packet_config');
            if(empty($red_packet_config)){
                $red_packet_config = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."red_packet_config order by id desc limit 1");
                $redis->hMset(REDIS_PREFIX.'red_packet_config',$red_packet_config);
            }
            
            if($type == 1){//平台红包
//                 if($platform_packet_info){
//                     $root['response_code'] = 0;
//                     $root['show_err'] = '红包每天只能发一个哦';
//                     output($root);
//                 }
                //$red_data['send_red_money'] = mt_rand($red_packet_config['red_min_money'],$red_packet_config['red_max_money']) ;
                $money = round(($red_packet_config['red_min_money'] + mt_rand() / mt_getrandmax() * ($red_packet_config['red_max_money'] - $red_packet_config['red_min_money'])),2);
                if($money == (0.01 * $red_packet_config['red_num'])){//防止下面分配金额时超出
                    $money = $red_packet_config['red_max_money'];
                }
                //防止平台红包发包多次
//                 $last_time = $GLOBALS['db']->getOne("select send_time from ".DB_PREFIX."red_packet_send where user_id =".$user['id']." and type=1 order by id desc limit 1");
//                 if($last_time + 10 > time()){
//                     $root['response_code'] = 0;
//                     $root['show_err'] = "操作频繁！";
//                     ajax_return($root);
//                 }
                //防止红包金额发0
                if(empty($money) || $money < 0){
                    $money = 0.5;
                }
                
                $red_id = $this->sendPacket($user['id'], $red_packet_config['red_num'], $type, $money, $redis);
            
            }elseif ($type == 2){//好友红包
                $pattern = "/^(?!0(\.0{1,2})?$)(?:[1-9][0-9]*|0)(?:\.[0-9]{1,2})?$/";
                $money = strim(base64_decode($GLOBALS['request']['money']));
                //判断是否开通存管
                if(!$user['cunguan_tag']){
                    $root['response_code'] = 0;
                    $root['show_err'] = '请您先开通银行存管账户';
//                     $root['jump_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_depository_account&act=index';
                    output($root);
                }
                if(!preg_match($pattern, $money)){
                    $root['response_code'] = 0;
                    $root['show_err'] = '请输入正确的金额';
                    output($root);
                }
                $friend_min_money = $red_packet_config['red_num'] * 0.01;
                if($money < $friend_min_money){
                    $root['response_code'] = 0;
                    $root['show_err'] = '好友红包最少发'.$friend_min_money.'元';
                    output($root);
                }
                if($money > $user['new_red_money']){
                    $root['response_code'] = 0;
                    $root['show_err'] = '红包余额不足';
                    output($root);
                }
                if($money > 500){
                    $root['response_code'] = 0;
                    $root['show_err'] = '好友红包最高只能发500元';
                    output($root);
                }
                $red_id = $this->sendPacket($user['id'], $red_packet_config['red_num'], $type, $money, $redis);
            }
            if(!$red_id){
                $root['response_code'] = 0;
                $root['show_err'] = '发送失败，请稍后再试！';
                output($root);
            }
            //记录日志
            $red_data_log['user_id'] = $user['id'];
            $red_data_log['red_money'] = $type == 2 ? -$money : $money;
            $red_data_log['new_red_money'] = $type == 2 ? $user['new_red_money'] - $money : $user['new_red_money'];
            $red_data_log['addtime'] = date('Y-m-d H:i:s');
            $red_data_log['remark'] = '派发红包成功';
            $red_data_log['type'] = $type;
            $red_data_log['action'] = 1;
            $resl = $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_log",$red_data_log,"INSERT");
            if($red_id && $resl){
                $root['type'] = $type;
                $root['response_code'] = 1;
                $root['red_packet_money'] = strval($money);
                $root['red_id']=$red_id;
                output($root);
            }
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }
    }

    private function sendPacket($user_id, $red_num, $type, $money, $redis){
        $MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
        $red_data['user_id'] = $user_id;
        $red_data['send_time'] = TIME_UTC;
        $red_data['red_num'] = $red_num;
        $red_data['send_date'] = date('Y-m-d H:i:s');
        $red_data['red_packet_status'] = 1;
        $red_data['type'] = $type;
        $red_data['send_red_money'] = $money;
        if($MachineInfo[0]=='iOS') {
            $red_data['source'] = 1;
        }elseif ($MachineInfo[0]=='Android') {
            $red_data['source'] = 2;
        }
        $res = $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_send",$red_data,"INSERT");
        $red_data['id'] = $GLOBALS['db']->insert_id();
        if($red_data['id']){
            $red_data['last_money'] = $red_data['send_red_money'];//红包剩余金额
            $red_data['last_num'] = $red_data['red_num'];//红包剩余个数
            $red_data['header_url'] = $GLOBALS['user_info']['header_url'];
            $red_data['realname'] = $GLOBALS['user_info']['real_name'];
            $red_data['mobile'] = $GLOBALS['user_info']['mobile'];
            if($type == 1){
                //平台红包记录并设置过期时间当晚23:59:59
                $redis->setex(REDIS_PREFIX.'pid'.$red_data['id'], strtotime(date('Y-m-d 23:59:59'))-time(), json_encode($red_data));
                //用户平台红包集合
                $redis->setex(REDIS_PREFIX.'user_platform_packet'.$user_id, strtotime(date('Y-m-d 23:59:59'))-time(), $red_data['id']);
                //生成红包队列
                for ($i=$red_num; $i>=1; $i--){
                    static $total;
                    if($i == $red_data['red_num']){
                        $total = $money;
                        $redmoney = $this->round_money($i,$total);
                        $total = $money - $redmoney;
                    }elseif($i == 1){
                        $redmoney = sprintf("%.2f", $total);
                    }else{
                        $redmoney = $this->round_money($i,$total);
                        $total = $total - $redmoney;
                    }
                    $redis->rPush(REDIS_PREFIX."money_list".$red_data['id'],$redmoney);
                }
                $redis->expire(REDIS_PREFIX.'money_list'.$red_data['id'], strtotime(date('Y-m-d 23:59:59'))-time());
                return $red_data['id'];
            }elseif($type == 2){
                //更新好友红包余额
                $GLOBALS['db']->query("update ".DB_PREFIX."user set new_red_money = new_red_money-".$money." where id = ".$user_id);
                //好友红包记录，不设置过期时间，跑脚本时处理完后删除
                $redis->set(REDIS_PREFIX.'pid'.$red_data['id'], json_encode($red_data));
                //用户好友红包集合 用于判断红包是否被抢光
                $redis->zAdd(REDIS_PREFIX.'user_friend_packet'.$user_id, time(), $red_data['id']);
                //好友红包总集合
                $redis->zAdd(REDIS_PREFIX.'friend_packet_zset', time(), $red_data['id']);
                return $red_data['id'];
            }
        }else{
            return false;
        }
        
    }

    
    public function round_money($red_num,$total){
        
        $min =0.01;
        $num = $red_num;
        $max = $total-$min*$num;
        
        $kmix = max($min, $total - $num * $max);
        $kmax = min($max, $total - $num * $min);
        $kAvg = $total / ($num + 1);
        //获取最大值和最小值的距离之间的最小值
        $kDis = min($kAvg - $kmix, $kmax - $kAvg);
        //获取0到1之间的随机数与距离最小值相乘得出浮动区间，这使得浮动区间不会超出范围
        $r = ((float)(mt_rand(1, 10000) / 10000)-0.3 ) * $kDis * 2;
        $rob_red_money = round($kAvg + $r,2);
        
        return $rob_red_money;
    }
}