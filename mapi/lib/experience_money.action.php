<?php
require APP_ROOT_PATH.'app/Lib/deal_func.php';
//虚拟货币体验金接口

class experience_money{

    public function index(){
        $roots = get_baseroot();
        $user = $GLOBALS['user_info'];
        $device = strim(base64_decode($GLOBALS['request']['device']));
        
        if($user['id']>0){
            //$root  = experience_money($user['id'],$device);
            /******************体验金修改*********************/
            //体验金的标
            /*$conditis='';
            $conditis .= " cunguan_tag =1 and publish_wait=1";
            $result = experience_treetop('',0,$conditis,'');*/
            $result = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."taste_cash where cunguan_tag=1 and user_id=".$user['id']);
            foreach ($result as $k=>$v){
                $root['item'][$k]['title'] = $v['disc']; //体验金标题
                $root['item'][$k]['money'] = strval($v['money']); //体验标金额
                $root['item'][$k]['id'] = $v['id']; //体验标id
                $has_repay = $GLOBALS['db']->getOne("select has_repay from ".DB_PREFIX."experience_deal_load where learn_id=".$v['id']." and user_id=".$user['id'] );
                if($v['use_status']==1){
                    $root['item'][$k]['button'] = '已使用';
                    $root['item'][$k]['button_status'] = '3';
                }else if($v['use_status']==1 && $has_repay==0){
                    $root['item'][$k]['button'] = '计息中';
                    $root['item'][$k]['button_status'] = '2';
                }else{
                    if(time()>$v['end_time']){
                        $root['item'][$k]['button'] = '已过期';
                        $root['item'][$k]['button_status'] = '4';
                    }else{
                        $root['item'][$k]['button'] = '立即使用';
                        $root['item'][$k]['button_status'] = '1';
                    }
                }
            }
            $root['response_code'] = 1;
            //体验金总金额
            $root['money_total'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."taste_cash where cunguan_tag = 1 and user_id=".$user['id']);
            $root['money_total'] = $root['money_total']?$root['money_total']:"0.00";
            //体验金可用余额
            $root['can_use_money'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."taste_cash where cunguan_tag =1 and user_id=".$user['id']." and use_status=0 and end_time>=".time());
            $root['can_use_money']= $root['can_use_money']? $root['can_use_money']:"0.00";
            // //体验经已收收益
            // $root['incomed'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."taste_cash where cunguan_tag =1 and user_id=".$user['id']." and use_status=1 and get_interest_status=1");
            // $root['incomed']= $root['incomed']? $root['incomed']:"0.00";
            // //体验金待收收益
            // $root['incomeing'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."taste_cash where cunguan_tag =1 and user_id=".$user['id']." and use_status=1 and get_interest_status=0");
            // $root['incomeing']= $root['incomeing']? $root['incomeing']:"0.00";

            //体验经已收收益
            // $root['incomed'] = $GLOBALS['db']->getOne("select SUM(experience_money) from ".DB_PREFIX."experience_deal_load where user_id=".$user['id']." and has_repay=1 ");
            // $root['incomed']= $root['incomed']? $root['incomed']:"0.00";

            //体验经已收收益
            $root['incomed'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."red_packet where user_id=".$user['id']." and status=1 and packet_type=3 and publish_wait=1 ");
            $root['incomed']=  $root['incomed']?  $root['incomed']:"0.00";

            
            //体验金待收收益
            $root['incomeing'] = $GLOBALS['db']->getOne("select sum(experience_money) from ".DB_PREFIX."experience_deal_load where user_id=".$user['id']." and has_repay=0");
            $root['incomeing']= $root['incomeing']? $root['incomeing']:"0.00";
    

            /*****************************体验金修改*******************************/

            $root['licai_open'] = $roots['licai_open'];
            $root['user_name'] = $roots['user_name'];
            $root['session_id']=es_session::id();
            $root['response_code'] = 1;
            //$root['huoqu_str']="新用户注册成功后，即可获得8888注册体验金+16666分享体验金+58888出借体验金+50元代金券。";
            $root['shiyong_str'] = strip_tags($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'cg_explain'"));;
            //$root['shouyi_str'] = "1、注册体验金收益，出借任意项目（含新手标）后，即可领取。\r\n2、其他体验金收益，到期后即可领取。\r\n3、收益有效期为30天，在有效期内未领取，则由系统自动回收。\r\n4、体验金到期后收益总计为：84442*10%/365*5=115.6元。";
            output($root);
        }else{
            $root['response_code'] = 0;
            $root['show_err'] = '请先登录';
            output($root);
        }

        /*******************************以下代码都被封装**********************************/
        
        $times = TIME_UTC;

        //if($user['id']>0){
            $root['response_code'] = 1;
            //体验金总金额
            $root['money_total'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."taste_cash where user_id=".$user['id']);
            $root['money_total'] = $root['money_total']?$root['money_total']:"0.00";
            //体验金可用余额
            $root['can_use_money'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."taste_cash where user_id=".$user['id']." and use_status=0 and end_time>=".$times);
            $root['can_use_money']= $root['can_use_money']? $root['can_use_money']:"0.00";
            //体验经已收收益
            $root['incomed'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."taste_cash where user_id=".$user['id']." and use_status=1 and get_interest_status=1");
            $root['incomed']= $root['incomed']? $root['incomed']:"0.00";
        //体验金待收收益
            $root['incomeing'] = $GLOBALS['db']->getOne("select SUM(money) from ".DB_PREFIX."taste_cash where user_id=".$user['id']." and use_status=1 and get_interest_status=0");
            $root['incomeing']= $root['incomeing']? $root['incomeing']:"0.00";
            //体验金列表
            $experience_money_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."taste_cash where user_id=".$user['id']);
            $friendnum = 0;//邀请好友数量
            $trade = 1; //用户是否出借

            foreach($experience_money_list as $k=> $v){
                $experience_money_config = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."taste_cash_config where id=".$experience_money_list[$k]['taste_cash_id']);
                $root['item'][$k]['id'] = $v['id'];//体验金id
                $root['item'][$k]['title'] = $experience_money_config['title']; //体验金标题
                $root['item'][$k]['time'] = $experience_money_config['time_limit']; //体验金期限
                $root['item'][$k]['money'] = $experience_money_config['money']; //体验金金额
                $root['item'][$k]['rate'] = $experience_money_config['rate']; //体验金年化收益
                $root['item'][$k]['income'] = $v['interest']; //体验金收益
                $usetime = $times - $v['use_time']; //使用过后的时间
                $limit_time = $experience_money_config['time_limit'] *3600*24; //注册体验金的有效期
                $drawtime = ($experience_money_config['time_limit'] + $experience_money_config['get_income_limit'])*3600*24; //收益领取的期限
                if($v['use_status'] ==0 && $v['get_interest_status']==0){
                    //注册体验金未使用状态
                    if($v['end_time']>$times){
                        if($experience_money_config['id']==1){
                            $root['item'][$k]['button_status'] = '1';
                            $root['item'][$k]['button'] = '立即使用';
                        }elseif($experience_money_config['id']==2){
                            if($friendnum >=3){
                                $root['item'][$k]['button'] = '立即使用';
                                $root['item'][$k]['button_status'] = '1';
                            }else{
                                $root['item'][$k]['button_status'] = '5';
                                $root['item'][$k]['button'] = '分享领取';
                            }
                        }else{
                            //if($trade){
                                $root['item'][$k]['button_status'] = '1';
                                $root['item'][$k]['button'] = '立即使用';
                            //}else{
                             //   $root['item'][$k]['button_status'] = '6';
                             //   $root['item'][$k]['button'] = '立即使用';
                            //}

                        }
                    }else{
                        $root['item'][$k]['button_status'] = '4';
                        $root['item'][$k]['button'] = '已过期';
                        $data['user_id'] = $user['id'];
                        $data['taste_id'] = $experience_money_config['id'];
                        $data['create_time'] = time();
                        $data['change'] = $experience_money_config['money'];
                        $data['device'] = $device;
                        $data['detail'] = $experience_money_config['title'].'-过期';
                        $data['taste_id'] = $v['id'];
                        $GLOBALS['db']->autoExecute(DB_PREFIX."taste_cash_log",$data,"INSERT");
                    }
                }elseif($v['use_status'] ==1 && $v['get_interest_status']==0){
                    //注册体验金已使用状态
                    if($usetime>$limit_time){
                        if($usetime > $drawtime){
                            $root['item'][$k]['button_status'] = '4';
                            $root['item'][$k]['button'] = '已过期';
                            $data['user_id'] = $user['id'];
                            $data['taste_id'] = $experience_money_config['id'];
                            $data['create_time'] = time();
                            $data['change'] = $experience_money_config['money'];
                            $data['device'] = $device;
                            $data['detail'] = $experience_money_config['title'].'-收益-过期';
                            $data['taste_id'] = $v['id'];
                            $GLOBALS['db']->autoExecute(DB_PREFIX."taste_cash_log",$data,"INSERT");
                        }else{
							if($GLOBALS['user_info']['cunguan_tag']){//判断是否存管用户
								$root['item'][$k]['button_status'] = '8';//跳转验证存管交易密码链接
							}else{
								$root['item'][$k]['button_status'] = '3';
							}
                            $root['item'][$k]['button'] = '领取收益';
                        }
                    }else{
                        $root['item'][$k]['button_status'] = '2';
                        $root['item'][$k]['button'] = '计息中';
                    }
                }elseif ($v['use_status'] ==1 && $v['get_interest_status']==1 ){
                    $root['item'][$k]['button_status'] = '7';
                    $root['item'][$k]['button'] = '已领取';
                }
            }
        output($root);
        //}else{
         //   $root['response_code'] = 0;
         //   $root['show_err'] = '请先登录';
         //   output($root);
        //}




    }



}