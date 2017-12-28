<?php
    class Level{
        //会员等级
        private $level  = '';
        /*
         * 会员等级判断
         */
        function get_user_vip_level($user_id){
            if(!$user_id){
                return "用户信息不存在！";
            }
            $grow_point=$GLOBALS['db']->getOne("select grow_point from ".DB_PREFIX."user where id=".$user_id);

            if($grow_point>=0 && $grow_point<=150){
                $this->level=0;
            }elseif($grow_point<=1300){
                $this->level=1;
            }elseif($grow_point<=6000){
                $this->level=2;
            }elseif($grow_point<=12000){
                $this->level=3;
            }elseif($grow_point<=60000){
                $this->level=4;
            }elseif($grow_point<=120000){
                $this->level=5;
            }elseif($grow_point>120000){
                $this->level=6;
            }
            $data['user_name'] =$GLOBALS['user_info']['real_name']?$GLOBALS['user_info']['real_name']:$GLOBALS['user_info']['mobile'];
            $data['grow_point']=$grow_point;
            $data['user_level']=$this->level;
            $data['next_level']=$this->level+1;
            $data['next_point']=$this->get_level_point($data['next_level']);
            $data['else_point']=$data['next_point']-$data['grow_point'];
            return $data;
        }
        private function get_level_point($level){
            if($level == 1){
                $point=150;
            }elseif($level == 2){
                $point=1300;
            }elseif($level == 3){
                $point=6000;
            }elseif($level == 4){
                $point=12000;
            }elseif($level == 5){
                $point=60000;
            }elseif($level == 6){
                $point=120000;
            }
            return $point;
        }
        /*
         * 查询对应操作类型的成长值
         * $task_type=9时  $paramete：充值金额
         * $task_type=10时  $paramete：邀请好友出借满多少元  $extra:被邀请人id
         * $task_type=11 时  $paramete：玖财通使用时长
         * $task_type=15时  $paramete：累计出借次数
         * $task_type=16时  $paramete：出借金额*月份/12
         * $task_type=17时  $paramete：出借金额*月份/12   当天出借额外获取成长值
         * $task_type=18时  本金复投   次月复投统计
         * $task_type=19时  $paramete 可以不传
         * $task_type=20时  $paramete 提现金额
         * $task_type=21时  $paramete 债券转让金额
         *
         */
        public function get_grow_point($task_type,$paramete='',$extra=''){
            $grow_point_now=$GLOBALS['db']->getOne("select grow_point from ".DB_PREFIX."user where id=".$GLOBALS['user_info']['id']);  // 当前成长值
            if($task_type>0 && $task_type<=8 || $task_type==22){
                $grow_point=$this->get_point($task_type);
            }elseif($task_type==9){   //充值
                $grow_point = ceil(($this->get_rate_limit($task_type))*$paramete);
            }elseif($task_type==10 && $paramete>=$this->get_money_limit($task_type)){
                $grow_point=$this->get_point($task_type);
            }elseif($task_type==11){
                $days1=$this->get_days_limit(11);
                $days2=$this->get_days_limit(12);
                $days3=$this->get_days_limit(13);
                $days4=$this->get_days_limit(14);
                if($paramete>=$days1 && $paramete<$days2){
                    $task_type=11;
                    $grow_point=$this->get_point($task_type);
                }elseif($paramete>=$days2 && $paramete<$days3){
                    $task_type=12;
                    $grow_point=$this->get_point($task_type);
                }elseif($paramete>=$days3 && $paramete<$days4){
                    $task_type=13;
                    $grow_point=$this->get_point($task_type);
                }elseif($paramete>=$days4){
                    $task_type=14;
                    $grow_point=$this->get_point($task_type);
                }
            }elseif($task_type==15 && $paramete==$this->get_days_limit($task_type)){
                $grow_point=$this->get_point($task_type);
            }elseif($task_type==16){
                $grow_point=round($paramete*$this->get_rate_limit($task_type),2);
            }elseif($task_type==17){
                $grow_point=round($paramete*$this->get_rate_limit($task_type)*$this->get_rate_limit(16),2);
            }elseif($task_type==18){
                $grow_point=round($paramete*$this->get_rate_limit($task_type)*$this->get_rate_limit(18),2);
            }elseif($task_type==19){
                $create_time=$GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."user_grow_point where id=".$GLOBALS['user_info']['id']." order by create_time desc limit 1"); // 上一次获取成长值的时间
                $point_limit=$this->get_point_limit($task_type);    // 低于这个数值  不会再扣
                $days_limit=$this->get_days_limit($task_type);    // 配置不活跃天数
                $day_times=((time()-$create_time)/3600/24);         // 实际不活跃天数
                $low_point_limit=ceil($point_limit*(1-$this->get_rate_limit($task_type)));  // 无法全额扣款的临界

                if($day_times>=$days_limit && $grow_point_now>$low_point_limit){
                    $grow_point=-($grow_point_now*$this->get_rate_limit($task_type));
                }elseif($day_times>=$days_limit && $grow_point_now>$point_limit && $grow_point_now<$low_point_limit){
                    $grow_point=-($grow_point_now-$point_limit);
                }
            }elseif($task_type==20 || $task_type==21){
                $cunguan_money=$GLOBALS['user_info']['cunguan_money']+$GLOBALS['user_info']['cunguan_lock_money'];   // 账户总资产
                $rate_limit=$this->get_rate_limit($task_type);
                $grow_point=-floor($paramete*$grow_point_now*$rate_limit/$cunguan_money);
            }else{
                return false;
            }

            $data['intro']=$GLOBALS['db']->getOne("select short_name from ".DB_PREFIX."grow_point_config where task_type=".$task_type);
            $data['intro']= $data['intro']? $data['intro']:'';
            // 执行成长值变更
            if($task_type == 10 || $task_type == 9 || $task_type == 8){
                return $this->editPoint($extra,$grow_point,$task_type, $data['intro']);
            }
            return $this->editPoint($GLOBALS['user_info']['id'],$grow_point,$task_type, $data['intro']);
        }
        /*
         * 执行查找成长值（从配置库）参数  grow_point
         */
        private function get_point($task_type){
            $grow_point=$GLOBALS['db']->getOne("select grow_point from ".DB_PREFIX."grow_point_config where task_type=".$task_type);
            return $grow_point;
        }
        /*
         * 执行查找成长值（从配置库）参数 rate_limit
         */
        private function get_rate_limit($task_type){
            $rate_limit=$GLOBALS['db']->getOne("select rate_limit from ".DB_PREFIX."grow_point_config where task_type=".$task_type);
            return $rate_limit;
        }
        /*
         * 执行查找成长值（从配置库）参数  days_limit
         */
        private function get_days_limit($task_type){
            $days_limit=$GLOBALS['db']->getOne("select days_limit from ".DB_PREFIX."grow_point_config where task_type=".$task_type);
            return $days_limit;
        }
        /*
        * 执行查找成长值（从配置库）参数  money_limit
        */
        private function get_money_limit($task_type){
            $money_limit=$GLOBALS['db']->getOne("select money_limit from ".DB_PREFIX."grow_point_config where task_type=".$task_type);
            return $money_limit;
        }
        /*
        * 执行查找成长值（从配置库）参数  point_limit
        */
        private function get_point_limit($task_type){
            $money_limit=$GLOBALS['db']->getOne("select point_limit from ".DB_PREFIX."grow_point_config where task_type=".$task_type);
            return $money_limit;
        }

        /**
         * @brief 日志记录
         * @param array $config => array('user_id' => 用户ID , 'point' => 成长值增减(正，负区分) , 'log' => 日志记录内容)
         */
        private function writeLog($config)
        {
            $pointLogArray = array(
                'user_id' => $config['user_id'],
                'create_time'=> time(),
                'create_date'=> date('Y-m-d H:i:s',time()),
                'grow_point'   => $config['grow_point'],
                'account_point'   => $config['account_point']+$config['grow_point'],
                'task_type'   => $config['task_type'],
                'intro'   => $config['intro'],
            );
            //  防重复保护

            if(!$config['user_id'] || $config['user_id']==''){
                return false;
            }
            $create_time=$GLOBALS['db']->getOne("select create_time from ".DB_PREFIX."user_grow_point where user_id=".$config['user_id']." and task_type=".$config['task_type']." order by create_time desc limit 1");
            if((time()-$create_time)<2){
                return false;
            }

            return $GLOBALS['db']->autoExecute(DB_PREFIX."user_grow_point",$pointLogArray,"INSERT");
        }
        /**
         * @brief 成长值更新
         * @param int $user_id 用户ID
         * @param int $point   成长值(正，负)
         */
        private function editPoint($user_id,$point,$task_type,$intro)
        {
            if(!isset($user_id) || $user_id == ''){
                return false;
            }
            $GLOBALS['db']->startTrans();   //开始事务
            $user_info=$GLOBALS['db']->getRow("select id,grow_point from ".DB_PREFIX."user where id=".$user_id." FOR UPDATE");
            $res=$GLOBALS['db']->query("update ".DB_PREFIX."user set grow_point=grow_point+".$point." where id=".$user_id);
            if($res){
                $data['user_id']=$user_id;
                $data['grow_point']=$point;
                $data['account_point']=$user_info['grow_point'];
                $data['task_type']=$task_type;
                $data['intro']=$intro;
                $result=$this->writeLog($data);
                if($result){
                    $GLOBALS['db']->commit();
                    return true;
                }else{
                    $GLOBALS['db']->rollback();
                    return false;
                }
            }else{
                $GLOBALS['db']->rollback();
                return false;
            }
        }
    }
?>