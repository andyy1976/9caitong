<?php

//体验金使用接口
require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
class experience_money_use{
    
    public function index(){

        $roots = get_baseroot();
        //体验金id
        $experience_id = strim(base64_decode($GLOBALS['request']['experience_id']));
        //设备信息
        $device = strim(base64_decode($GLOBALS['request']['device']));
        $user = $GLOBALS['user_info'];

        if(!check_ipop_limit(CLIENT_IP,"experience_money_use",3,0))
        {
            $root['response_code'] = 0;
            $root['show_err'] = '提交太快'; //短信发送太快
            output($root);
        }

        if($user['id']>0){
			$experience = $GLOBALS['db']->getRow("select interest from ".DB_PREFIX."taste_cash where id=".$experience_id);
			 if($user['cunguan_tag']==1){
				$publics = new Publics();
				$seqno = $publics -> seqno();
			}
			//开启事务
			$GLOBALS['db'] -> startTrans();
			$root = experience_money_use($user['id'],$experience_id,$device,$seqno);
			if($root['success_status']==1&&$user['cunguan_tag']==1){
				//$experience = $GLOBALS['db']->getRow("select interest from ".DB_PREFIX."taste_cash where id=".$experience_id);
				$Deal = new Deal();
				$res = $Deal->experience_money($seqno,'T10',$experience['interest'],$user['id']);
				$res_code = $res['respHeader']['respCode'];
				if($res_code!="P2P0000"){//如果存管领取失败，则回滚操作
					$GLOBALS['db'] -> rollback();
					$root['response_code'] = 1;
					$root['success_status'] = "2";
					$root['show_err'] = $res['respHeader']['respMsg'];
					output($root);
				}else{
					$GLOBALS['db'] -> commit();//提交
				}
			}else{
				$GLOBALS['db'] -> commit();//提交
			}
            $root['licai_open'] = $roots['licai_open'];
            $root['user_name'] = $roots['user_name'];
            $root['session_id']=es_session::id();
            output($root);
        }else{
            $root['response_code'] = 0;
            $root['show_err'] = '请先登录';
            output($root);
        }

        



        /*******************************以下代码都被封装**********************************/
        $time = time();
        //if($user['id']>0){
            //体验金的详细信息
            $experience = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."taste_cash where id=".$experience_id);
            $user_money = $user['money'] + $experience['interest'];
            if($experience){
                if($experience['taste_cash_id'] == 1){ //注册体验金
                    if($experience['use_status'] ==0 && $experience['get_interest_status']==0){
                        $res = $GLOBALS['db']->query("update ".DB_PREFIX."taste_cash set use_status=1,use_time=".$time." where id = ".$experience_id);
                        if($res){
                            $data['user_id'] = $user['id'];
                            $data['taste_cash_id'] = $experience['taste_cash_id'];
                            $data['create_time'] = $time;
                            $data['change'] = $experience['money'];
                            $data['device'] = $device;
                            $data['detail'] = '使用体验金';
                            $data['taste_id'] = $experience['id'];
                            $GLOBALS['db']->autoExecute(DB_PREFIX."taste_cash_log",$data,"INSERT");
                            $root['response_code'] = 1;
                            $root['success_status'] = "3";
                            $root['show_err'] = '恭喜你，体验金使用成功到期后可领取收益';
                            output($root);
                        }else{
                            $root['response_code'] = 0;
                            $root['show_err'] = '体验金使用失败';
                            output($root);
                        }
                    }elseif($experience['use_status'] ==1 && $experience['get_interest_status']==0){
                        $trade = $GLOBALS['db']->getOne("select dl.*,de.is_new from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal on dl.deal_id=de.id where de.is_new<>1 and dl.user_id=".$user['id']);
                        if($trade>0){
                            $res = $GLOBALS['db']->query("update ".DB_PREFIX."taste_cash set get_interest_status=1 ,get_interest_time=".$time." where id = ".$experience_id);
                            if($res){
                                $data['user_id'] = $user['id'];
                                $data['money'] = $experience['interest'];
                                $data['account_money'] = $user['money'];
                                $data['momo'] = '获得体验金收益'.$experience['interest'].'元';
                                $data['type'] = 47;
                                $data['create_time'] = time();
                                $data['create_time_ymd'] = date('y-m-d');
                                $data['create_time_ym'] = date('ym');
                                $data['create_time_y'] = date('y');
                                $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$data,"INSERT");
                                $GLOBALS['db']->query("update ".DB_PREFIX."user set money_encrypt = AES_ENCRYPT(ROUND(ifnull(AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."'),0),2) + ".round($user_money,2).",'".AES_DECRYPT_KEY."') where id =".$user['id']);

                                $root['response_code'] = 1;
                                $root['success_status'] = "1";
                                $root['show_err'] = ' 恭喜你，收益领取成功请到“账户/可用现金”中查看';
                                output($root);
                            }
                        }else{
                            $root['response_code'] = 0;
                            $root['success_status'] = "2";
                            $root['show_err'] = '出借任意产品后，才可领取体验金收益，是否去出借？';
                            output($root);
                        }

                    }else{
                        $root['response_code'] = 1;
                        $root['show_err'] = '您已经领过收益了,不能重复领取';
                        output($root);
                    }
                }elseif($experience['taste_cash_id'] == 2){  //分享体验金
                    if($experience['use_status'] ==0 && $experience['get_interest_status']==0){
                        $friendnum = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_referer_log where pid=".$user['id']);
                        if($friendnum>=3){
                            $res = $GLOBALS['db']->query("update ".DB_PREFIX."taste_cash set use_status=1,use_time=".$time." get_interest_status=1 ,get_interest_time=".$time." where id = ".$experience_id);
                            if($res){
                                $data['user_id'] = $user['id'];
                                $data['money'] = $experience['interest'];
                                $data['account_money'] = $user['money'];
                                $data['momo'] = '获得体验金收益'.$experience['interest'].'元';
                                $data['type'] = 47;
                                $data['create_time'] = time();
                                $data['create_time_ymd'] = date('y-m-d');
                                $data['create_time_ym'] = date('ym');
                                $data['create_time_y'] = date('y');
                                $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$data,"INSERT");
                                $GLOBALS['db']->query("update ".DB_PREFIX."user set money_encrypt = AES_ENCRYPT(ROUND(ifnull(AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."'),0),2) + ".round($user_money,2).",'".AES_DECRYPT_KEY."') where id =".$user['id']);
                                $root['response_code'] = 1;
                                $root['success_status'] = "1";
                                $root['show_err'] = ' 恭喜你，收益领取成功请到“账户/可用现金”中查看';
                                output($root);
                            }
                        }else{
                            $root['response_code'] = 0;
                            $root['show_err'] = '邀请好友数量不够';
                            output($root);
                        }
                    }else{
                        $root['response_code'] = 0;
                        $root['show_err'] = '您已经领过收益了,不能重复领取';
                        output($root);
                    }
                }elseif($experience['taste_cash_id'] == 3){  //出借体验金
                    if($experience['use_status'] ==0 && $experience['get_interest_status']==0){
                        $trade = $GLOBALS['db']->getOne("select dl.*,de.is_new from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal on dl.deal_id=de.id where de.is_new<>1 and dl.user_id=".$user['id']);
                        if($trade>0){
                            $res = $GLOBALS['db']->query("update ".DB_PREFIX."taste_cash set use_status=1,use_time=".$time." get_interest_status=1 ,get_interest_time=".$time." where id = ".$experience_id);
                            if($res){
                                $data['user_id'] = $user['id'];
                                $data['money'] = $experience['interest'];
                                $data['account_money'] = $user['money'];
                                $data['momo'] = '获得体验金收益'.$experience['interest'].'元';
                                $data['type'] = 47;
                                $data['create_time'] = time();
                                $data['create_time_ymd'] = date('y-m-d');
                                $data['create_time_ym'] = date('ym');
                                $data['create_time_y'] = date('y');
                                $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$data,"INSERT");
                                $GLOBALS['db']->query("update ".DB_PREFIX."user set money_encrypt = AES_ENCRYPT(ROUND(ifnull(AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."'),0),2) + ".round($user_money,2).",'".AES_DECRYPT_KEY."') where id =".$user['id']);
                                $root['response_code'] = 1;
                                $root['success_status'] = "1";
                                $root['show_err'] = ' 恭喜你，收益领取成功请到“账户/可用现金”中查看';
                                output($root);
                            }
                        }else{
                            $root['response_code'] = 0;
                            $root['success_status'] = "2";
                            $root['show_err'] = '出借任意产品后，才可领取体验金收益，是否去出借？';
                            output($root);
                        }
                    }else{
                        $root['response_code'] = 0;
                        $root['show_err'] = '您已经领过收益了,不能重复领取';
                        output($root);
                    }
                }else{
                    $root['response_code'] = 0;
                    $root['show_err'] = '体验金类型不存在';
                    output($root);
                }
            }else{
                $root['response_code'] = 0;
                $root['show_err'] = '体验金不存在';
                output($root);
            }
        //}else{
          //  $root['response_code'] = 0;
          //  $root['show_err'] = '请先登录';
           // output($root);
        //}
        
        
    }

    
}