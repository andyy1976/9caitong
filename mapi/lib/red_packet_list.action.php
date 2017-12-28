<?php

/**
 * 红包排行榜接口
 * Class red_packet_list
 */
class red_packet_list{

    /**
     * @author:zhuxiang
     */
    public function index(){
        $user = $GLOBALS['user_info'];
       
        $page = intval(base64_decode($GLOBALS['request']['page']));
        $page = $page ? $page :1;
        $root = get_baseroot();
        $redis = new Redis();
        //$redis->connect('127.0.0.1', 6379);
        $redis->connect(REDIS_HOST, REDIS_PORT);
        $redis->auth(REDIS_PWD);
        $redis->select(8);
        //读取缓存配置
        $red_conf = $redis->hGetAll(REDIS_PREFIX.'red_packet_config');
        if($user['user_type']=='1'){
        	$MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
        	if($MachineInfo){
        		if($MachineInfo[0]=='iOS'){
        			$root['response_code'] = 0;
        		}else{
        			$root['response_code'] = 1;
        		}
        	}
        	$root['show_err'] = '企业用户暂不可使用';
        	$root['red_msg'] = $red_conf['rob_msg'];
        	output($root);
        }
        if(empty($red_conf)){
            $red_conf = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."red_packet_config order by id desc limit 1");
            $redis->hMset(REDIS_PREFIX.'red_packet_config',$red_conf);
        }
        //读取好友发的平台红包ID是否存在
        $send_red_num = $redis->get(REDIS_PREFIX.'user_platform_packet'.$user['id']);
        if($send_red_num){
            $root['send_status'] = '0';
        }else{
            $root['send_status'] = '1';
        }
        $root['session_id'] = es_session::id();
        $root['tip_msg'] = "删除后就不能再抢他发的红包了\n\n确定要删除好友吗？";
        $root['red_msg'] = $red_conf['rob_msg'];
        $url = WAP_SITE_DOMAIN . "/index.php?ctl=find&act=W654&code=".$user['mobile'];
        $root['wx_share']['url'] = $url;
        $root['wx_share']['icon'] = $red_conf['icon'];
        $root['wx_share']['content'] = $red_conf['body'];
        $root['wx_share']['title'] = $red_conf['title'];
        if($user['id']>0){
            $self = $GLOBALS['db']->getOne("select user_id from ".DB_PREFIX."red_packet_friends where user_id=".$user['id']." and friend_id=".$user['id']);
            if(!$self){
                //默认只有自己
                $red_data['user_id'] = $user['id'];
                $red_data['friend_id'] = $user['id'];
                $red_data['status'] = 0;
                $red_data['addtime'] = TIME_UTC;
                $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_friends",$red_data,"INSERT");
            }
                $red_friends_list = json_decode($redis->hGet(REDIS_PREFIX."red_friends_list",$user['id']),true);
                $friends_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."red_packet_friends where status = 0 and user_id=".$user['id']);
                if(empty($red_friends_list) || count($red_friends_list)<$friends_count){
                    $red_friends_list = $GLOBALS['db']->getAll("select friend_id  from ".DB_PREFIX."red_packet_friends  where status = 0 and user_id = ".$user['id'] );

                    $redis->hset(REDIS_PREFIX."red_friends_list",$user['id'],json_encode($red_friends_list));
                }

                foreach ($red_friends_list as $k=>$v){
                    //查找好友本周抢到的红包总额
                    $last_monday_time = strtotime('-2 monday');
                    $next_monday_time = strtotime("next monday"); //下周一零点
                    $monday_time = strtotime(date('Y-m-d',(time()-((date('w')==0?7:date('w'))-1)*24*3600))); //本周一
                    //好友上周所抢总金额
                    $last_week_total = $redis->zScore(REDIS_PREFIX."last_week_total".$user['id'],$v['friend_id']); //返回有序中，用户的上周金额。
                    if(empty($last_week_total)){
                        $last_week_total = $GLOBALS['db']->getOne("select SUM(rob_red_money) from ".DB_PREFIX."red_packet_rob where user_id = ".$v['friend_id']." and rob_time <=".$monday_time." and rob_time >=".$last_monday_time);
                        $last_week_total = $last_week_total?$last_week_total:0;
                        $redis->zAdd(REDIS_PREFIX."last_week_total".$user['id'] , $last_week_total , $v['friend_id']);
                        $redis->expire(REDIS_PREFIX."last_week_total".$user['id'],$next_monday_time-time()+600);
                    }
                    //好友信息
                    $friend_info = json_decode($redis->hGet(REDIS_PREFIX."user_info",$v['friend_id']),true);
                    if(empty($friend_info)){
                        $friend_info = $GLOBALS['db']->getRow("select id,header_url,real_name as realname,mobile from ".DB_PREFIX."user where id=".$v['friend_id']);
                        $friend_info['mobile'] = cut_str($friend_info['mobile'], 3, 0).'****'.cut_str($friend_info['mobile'], 2, -2);
                        $redis->hSet(REDIS_PREFIX.'user_info',$v['friend_id'],json_encode($friend_info));
                    }
                    if($user['id'] == $v['friend_id']){
                        $friend_info['realname'] = '我';
                        $friend_info['mobile'] = '我';
                    }
                    
                    $mvp_uid = $redis->zRevRange(REDIS_PREFIX.'last_week_total'.$user['id'],0,0);

                    if($mvp_uid[0] == $v['friend_id']){
                        $friend_info['mvp']="1";
                        $friend_info['vip']="1";
                    }else{
                        $friend_info['mvp']="0";
                        $friend_info['vip']="0";
                    }

                    $red_friends_info[$k]['friends_info'] = $friend_info;

                    $red_money_total = $redis->zScore(REDIS_PREFIX."red_money_total",$v['friend_id']);
                    if(empty($red_money_total)){
//                         $red_money_total = $GLOBALS['db']->getOne("select SUM(rob_red_money) from ".DB_PREFIX."red_packet_rob where user_id = ".$v['friend_id']." and rob_time >=".$monday_time." and rob_time <=".$next_monday_time);
                        $s_time = strtotime("2017-12-01");
                        $e_time = time();
                        $red_money_total = $GLOBALS['db']->getOne("select SUM(rob_red_money) from ".DB_PREFIX."red_packet_rob where user_id = ".$v['friend_id']." and rob_time >=".$s_time." and rob_time <=".$e_time);
                        $red_money_total = $red_money_total?$red_money_total:0;
                        $redis->zAdd(REDIS_PREFIX."red_money_total",$red_money_total,$v['friend_id']);
                        $redis->expire(REDIS_PREFIX."red_money_total",$next_monday_time-time()+600);
                    }
                    $red_friends_info[$k]['red_money_total'] = strval(sprintf("%.2f", $red_money_total));

                    //查询当天所发平台红包和好友红包
                    $platform_packet_id = $redis->get(REDIS_PREFIX.'user_platform_packet'.$v['friend_id']);
                    $friend_packet_id = $redis->zRevRangeByScore(REDIS_PREFIX.'user_friend_packet'.$v['friend_id'], strtotime(date('Y-m-d 23:59:59')), strtotime(date('Y-m-d')));
                    if(!empty($platform_packet_id) && empty($friend_packet_id[0])){
                        $red_friends_info[$k]['packet_status'] = $this->packet_status($redis,$user['id'],$platform_packet_id,1);
                    }elseif(empty($platform_packet_id) && !empty($friend_packet_id[0])){
                        $red_friends_info[$k]['packet_status'] = $this->packet_status($redis,$user['id'],$friend_packet_id[0],2);
                    }elseif(!empty($platform_packet_id) && !empty($friend_packet_id)){
                        if($platform_packet_id>$friend_packet_id[0]){
                            $red_friends_info[$k]['packet_status'] = $this->packet_status($redis,$user['id'],$platform_packet_id,1);
                        }else{
                            $red_friends_info[$k]['packet_status'] = $this->packet_status($redis,$user['id'],$friend_packet_id[0],2);
                        }
                    }else{
                        $red_friends_info[$k]['packet_status'] = null;
                    }

                    $sort[] = $red_friends_info[$k]['red_money_total'];

                }
            array_multisort($sort,SORT_DESC,$red_friends_info);
            $num = ceil(count($red_friends_info)/20);
            $root['page'] = array("page"=>$page,"page_total"=>ceil(count($red_friends_info)/20),"page_size"=>'20');
            if($page>1){
                $red_friends_info=null;
            }

            $root['friends_list'] = $red_friends_info;
            $root['response_code'] = 1;
            output($root);
        }else{
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }
    }


    /**
     * 红包列表红包状态显示
     * @param $redis  redis
     * @param $user_id 用户ID
     * @param $red_packet_id 红包ID
     * @return mixed 红包状态数组
     * @author:zhuxiang
     */
    private function packet_status($redis,$user_id,$red_packet_id,$type){
        if(!empty($red_packet_id)){
            $friend_packet_info = json_decode($redis->get(REDIS_PREFIX.'pid'.$red_packet_id), true);
            if($friend_packet_info['last_money'] > 0){
                for($i=0; $i<$redis->lLen(REDIS_PREFIX.'rob_list'.$red_packet_id); $i++){
                    $rob_red_list[$i] = json_decode($redis->lindex(REDIS_PREFIX.'rob_list'.$red_packet_id,$i), true);
                }
                $user_ids = array();
                foreach($rob_red_list as $k=>$v){
                    $user_ids[] = $v['user_id'];
                }
                if(in_array($user_id,$user_ids)){
                    $packet_status['red_packet_status'] = 2; //已抢过
                    $packet_status['source'] = $type; //1.平台红包 2.好友红包
                    $packet_status['id'] = $red_packet_id; //红包ID
                }else{
                    $packet_status['red_packet_status'] = 1; //未抢过
                    $packet_status['source'] = $type; //1.平台红包 2.好友红包
                    $packet_status['id'] = $red_packet_id; //红包ID
                }


            }else{
                $packet_status['red_packet_status'] = 3;
                $packet_status['source'] = $type; //1.平台红包 2.好友红包
                $packet_status['id'] = $red_packet_id;
            }
        }else{
            $packet_status = null;
        }
        return $packet_status;
    }

}