<?php
define(MODULE_NAME,"index");
class wjxsModule extends SiteBaseModule
{
    public function changjie_notify(){
        $outer_trade_no = $_REQUEST['outer_trade_no'];
        $inner_trade_no = $_REQUEST['inner_trade_no'];
        $trade_status = $_REQUEST['trade_status'];
        $trade_amount = $_REQUEST['trade_amount'];

        $noticeid = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."payment_notice where order_id='".$outer_trade_no."'");
        if($noticeid['is_paid']==1){
            exit;
        }

        $trans_datas=array(
            'outer_trade_no'=>$outer_trade_no,
            'inner_trade_no'=>$inner_trade_no,
            'trade_status'=>$trade_status,
            'trade_amount'=>$trade_amount,
        );
        $trans_datas=json_encode($trans_datas);

        $pay_notify = array(
            'version'=>1,
            'order_no'=>$noticeid['order_id'],
            'return_mode'=>2,
            'status'=>1,
            'pay_type'=>32,
            'trans_no'=>$inner_trade_no,
            'user_id'=>$noticeid['user_id'],
            'addtime'=>time(),
            'num'=>$trade_amount,
            'signstr'=>$trans_datas,
        );

        if($trade_status=="TRADE_SUCCESS"){
            if($noticeid['order_id'] == $outer_trade_no) {
                if ($noticeid['money'] == $trade_amount) {
                    $trans_data = array(
                        'is_paid' => 1,
                        'notice_sn' => $inner_trade_no,
                        'memo' => $trans_datas,
                        'outer_notice_sn'=>"畅捷-充值成功",
                        'pay_time' => time(),
                        'pay_date' => date("Y-m-d H:i:s"),
                    );
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "pay_notify", $pay_notify, "INSERT");
                    $order_id = intval($GLOBALS['db']->insert_id());
                    if ($order_id) {
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_data, "UPDATE", "id=" . $noticeid['id']);
                        require_once APP_ROOT_PATH . 'system/libs/user.php';
                        modify_account(array('money' => $trade_amount, 'recharge_money' => $trade_amount), $noticeid['user_id'], "畅捷pc端充值成功", "充值成功", 1, "充值成功");
                    }

                    /************充值成功后微信模板消息开始*********************/
                    $wx_openid = $GLOBALS['db']->getOne("select wx_openid from ".DB_PREFIX."user where id=".$noticeid['user_id']);
                    $user_money = $GLOBALS['db']->getOne("select AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."') as money from ".DB_PREFIX."user where id =".$noticeid['user_id']);
                    if($wx_openid){
                        if(app_conf('WEIXIN_TMPL')){
                            $tmpl_url = app_conf('WEIXIN_TMPL_URL');
                            $tmpl_datas = array();
                            $tmpl_datas['first'] = '尊敬的用户，您完成了一笔充值';
                            $tmpl_datas['keyword1'] = $trade_amount.'元';
                            $tmpl_datas['keyword2'] = date('Y-m-d H:i:s');
                            $tmpl_datas['keyword3'] = $user_money.'元';
                            $tmpl_datas['remark'] = "请登录玖财通查看详情~\r\n\r\n下载客户端，理财更简单。推荐您下载玖财通APP！";
                            $tmpl_data = create_request_data('3',$wx_openid,app_conf('WEIXIN_JUMP_URL'),$tmpl_datas);
                            $resl=request_curl($tmpl_url,$tmpl_data);
                            $tmpl_msg['dest'] = $wx_openid;
                            $tmpl_msg['send_type'] = 3;
                            $tmpl_msg['content'] = serialize($tmpl_datas);
                            $tmpl_msg['send_time'] = time();
                            $tmpl_msg['create_time'] = time();
                            $tmpl_msg['user_id'] = $noticeid['user_id'];
                            $tmpl_msg['title'] = '充值成功';
                            if($resl===true){
                                $GLOBALS['db']->query("update ".DB_PREFIX."user set wx_openid_status=1 where id=".$noticeid['user_id']);
                                $tmpl_msg['is_send'] = 1;
                                $tmpl_msg['result'] = '发送成功';
                                $tmpl_msg['is_success'] = 1;
                            }else{
                                $tmpl_msg['is_send'] = 0;
                                $tmpl_msg['result'] = $resl['message'];
                                $tmpl_msg['is_success'] = 0;
                            }
                            $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_msg_list",$tmpl_msg,'INSERT','','SILENT');
                        }
                    }
                    /************充值成功后微信模板消息结束*********************/
                    echo "OK";
                    app_redirect(url("index", "uc_money#incharge"));
                    exit;
                } else {
                    echo "{'ret_code':'9999','ret_msg':'验签失败'}";
                    app_redirect(url("index", "uc_money#incharge_log"));
                    exit;
                }

            }else{
                echo "{'ret_code':'9999','ret_msg':'验签失败'}";
                app_redirect(url("index", "uc_money#incharge_log"));
                exit;
            }

        }else{
            echo "fail";
            echo "{'ret_code':'9999','ret_msg':'验签失败'}";
            app_redirect(url("index", "uc_money#incharge_log"));
            exit;
        }
    }

    public function changjie_return(){
        $outer_trade_no = $_REQUEST['outer_trade_no'];
        $inner_trade_no = $_REQUEST['inner_trade_no'];
        $trade_status = $_REQUEST['trade_status'];
        $trade_amount = $_REQUEST['trade_amount'];
        $noticeid = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."payment_notice where order_id='".$outer_trade_no."'");
        if($noticeid['is_paid']==1){
            exit;
        }

        $trans_datas=array(
                'outer_trade_no'=>$outer_trade_no,
                'inner_trade_no'=>$inner_trade_no,
                'trade_status'=>$trade_status,
                'trade_amount'=>$trade_amount,
        );
        $trans_datas=json_encode($trans_datas);

        $pay_notify = array(
            'version'=>1,
            'order_no'=>$noticeid['order_id'],
            'return_mode'=>2,
            'status'=>1,
            'pay_type'=>32,
            'trans_no'=>$inner_trade_no,
            'user_id'=>$noticeid['user_id'],
            'addtime'=>time(),
            'num'=>$trade_amount,
            'signstr'=>$trans_datas,
        );

        if($trade_status=="TRADE_SUCCESS"){
            if($noticeid['order_id'] == $outer_trade_no) {
                if ($noticeid['money'] == $trade_amount) {
                    $trans_data = array(
                        'is_paid' => 1,
                        'notice_sn' => $inner_trade_no,
                        'memo' => $trans_datas,
                        'outer_notice_sn'=>"畅捷-充值成功",
                        'pay_time' => time(),
                        'pay_date' => date("Y-m-d H:i:s"),
                    );
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "pay_notify", $pay_notify, "INSERT");
                    $order_id = intval($GLOBALS['db']->insert_id());
                    if ($order_id) {
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_data, "UPDATE", "id=" . $noticeid['id']);
                        require_once APP_ROOT_PATH . 'system/libs/user.php';
                        modify_account(array('money' => $trade_amount, 'recharge_money' => $trade_amount), $noticeid['user_id'], "畅捷pc端充值成功", "充值成功", 1);
                    }
                    echo "OK";
                    app_redirect(url("index", "uc_money#incharge"));
                    exit;

                } else {
                    echo "{'ret_code':'9999','ret_msg':'验签失败'}";
                    app_redirect(url("index", "uc_money#incharge_log"));
                    exit;
                }

            }else{
                echo "{'ret_code':'9999','ret_msg':'验签失败'}";
                app_redirect(url("index", "uc_money#incharge_log"));
                exit;
            }

        }else{
            echo "fail";
            echo "{'ret_code':'9999','ret_msg':'验签失败'}";
            app_redirect(url("index", "uc_money#incharge_log"));
            exit;
        }
    }

    /*连连支付*/
    public function nihao(){
        /*
        require APP_ROOT_PATH."system/utils/Pcllpay/llpay_notify.class.php";
        $wapllpay_config = $this->llpay_config();
        $llpayNotify = new LLpayNotify($wapllpay_config);
        var_dump($llpayNotify);
        $verify_result = $llpayNotify->verifyNotify();
        var_dump($verify_result);
        if ($llpayNotify->result) {
            $no_order = $llpayNotify->notifyResp['no_order'];//商户订单号
            var_dump($no_order);
            $oid_paybill = $llpayNotify->notifyResp['oid_paybill'];//连连支付单号
            $result_pay = $llpayNotify->notifyResp['result_pay'];//支付结果，SUCCESS：为支付成功
            $money_order = $llpayNotify->notifyResp['money_order'];// 支付金额
            $acct_name = $llpayNotify->notifyResp['acct_name'];//真实姓名
            $id_no = $llpayNotify->notifyResp['id_no'];//身份证号码
            die("{'ret_code':'0000','ret_msg':'交易成功'}");
        }
        exit;*/
        //商户订单时间
        $dt_order= $_POST['dt_order' ];
        //商户订单号
        $no_order = $_POST['no_order' ];
        //支付单号
        $oid_paybill = $_POST['oid_paybill' ];
        //交易金额
        $money_order = $_POST['money_order' ];
        //支付结果
        $result_pay =  $_POST['result_pay'];
        //清算日期
        $settle_date =  $_POST['settle_date'];
        //订单描述
        $info_order =  $_POST['info_order'];
        //支付方式
        $pay_type =  $_POST['pay_type'];
        //银行编号
        $bank_code =  $_POST['bank_code'];

        $noticeid = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."payment_notice where order_id='".$no_order."'");
        if($noticeid['is_paid']==1){
            app_redirect(url("index", "uc_money#incharge_log"));
            exit;
        }
        $user_id=$noticeid['user_id'];
        $res_data=array(
            'dt_order'=> $_POST['dt_order' ],
            'no_order' =>$_POST['no_order' ],
            'oid_paybill' => $_POST['oid_paybill' ],
            'money_order' => $_POST['money_order' ],
            'result_pay' =>  $_POST['result_pay'],
            'settle_date' =>  $_POST['settle_date'],
            'info_order' =>  $_POST['info_order'],
            'pay_type' =>  $_POST['pay_type'],
            'bank_code' =>  $_POST['bank_code'],
            'user_id'=>$noticeid['user_id'],
        );
        $res_data=json_encode($res_data);

        $pay_notify = array(
            'version'=>1,
            'order_no'=>$noticeid['order_id'],
            'return_mode'=>2,
            'status'=>1,
            'pay_type'=>30,
            'trans_no'=>$oid_paybill,
            'user_id'=>$noticeid['user_id'],
            'num'=>$money_order,
            'addtime'=>time(),
            'signstr'=>$res_data,
        );

        $trans_data=array(
            'is_paid'=>1,
            'notice_sn'=>$oid_paybill,
            'outer_notice_sn'=>"连连PC-充值成功",
            'pay_time'=>time(),
            'pay_date'=>date("Y-m-d H:i:s"),
            'memo'=>$res_data,
        );

        if($result_pay=="SUCCESS"){
            if($noticeid['order_id'] == $no_order) {
                if($noticeid['money'] == $money_order ) {
                    if($noticeid['is_paid'] == 0) {
                        $a = $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_data, "UPDATE", "id=" . $noticeid['id'] . " and is_paid=0 and order_id='".$noticeid['order_id']."' ");
                        if ($a) {
                            $GLOBALS['db']->autoExecute(DB_PREFIX . "pay_notify", $pay_notify, "INSERT");
                            $GLOBALS['db']->query("update " . DB_PREFIX . "user_bank set redline = redline +  " . $money_order . " where user_id=" . $noticeid['user_id'] . " and  bankcard=" . $noticeid['bank_id'] . "");
                            require_once APP_ROOT_PATH . 'system/libs/user.php';
                            $create_time = $GLOBALS['db']->getOne("select create_time  from " . DB_PREFIX . "user_money_log where user_id =$user_id order by create_time desc limit 1");
                            if (time() - $create_time <= 3) {
                                exit;
                            }
                            modify_account(array('money' => $money_order), $noticeid['user_id'], "连连支付pc端充值", 1, "充值成功");
                        }
                        /************充值成功后微信模板消息开始*********************/
                        $wx_openid = $GLOBALS['db']->getOne("select wx_openid from " . DB_PREFIX . "user where id=" . $noticeid['user_id']);
                        if ($wx_openid) {
                            if (app_conf('WEIXIN_TMPL')) {
                                $tmpl_url = app_conf('WEIXIN_TMPL_URL');
                                $tmpl_datas = array();
                                $tmpl_datas['first'] = '尊敬的用户，您完成了一笔充值';
                                $tmpl_datas['keyword1'] = $money_order . '元';
                                $tmpl_datas['keyword2'] = date('Y-m-d H:i:s');
                                $tmpl_datas['keyword3'] = $GLOBALS['user_info']['money'] . '元';
                                $tmpl_datas['remark'] = "请登录玖财通查看详情~\r\n\r\n下载客户端，理财更简单。推荐您下载玖财通APP！";
                                $tmpl_data = create_request_data('3', $wx_openid, app_conf('WEIXIN_JUMP_URL'), $tmpl_datas);
                                $resl = request_curl($tmpl_url, $tmpl_data);
                                $tmpl_msg['dest'] = $wx_openid;
                                $tmpl_msg['send_type'] = 3;
                                $tmpl_msg['content'] = serialize($tmpl_datas);
                                $tmpl_msg['send_time'] = time();
                                $tmpl_msg['create_time'] = time();
                                $tmpl_msg['user_id'] = $noticeid['user_id'];
                                $tmpl_msg['title'] = '充值成功';
                                if ($resl === true) {
                                    $GLOBALS['db']->query("update " . DB_PREFIX . "user set wx_openid_status=1 where id=" . $noticeid['user_id']);
                                    $tmpl_msg['is_send'] = 1;
                                    $tmpl_msg['result'] = '发送成功';
                                    $tmpl_msg['is_success'] = 1;
                                } else {
                                    $tmpl_msg['is_send'] = 0;
                                    $tmpl_msg['result'] = $resl['message'];
                                    $tmpl_msg['is_success'] = 0;
                                }
                                $GLOBALS['db']->autoExecute(DB_PREFIX . "weixin_msg_list", $tmpl_msg, 'INSERT', '', 'SILENT');
                            }
                        }
                        /************充值成功后微信模板消息结束*********************/
                        echo "{'ret_code':'0000','ret_msg':'交易成功'}";
                        echo "ok";
                        app_redirect(url("index", "uc_money#incharge_log"));
                        exit;
                    }else{
                        echo "{'ret_code':'9994','ret_msg':'验签失败'}";
                        app_redirect(url("index", "uc_money#incharge_log"));
                        exit;
                    }
                }else{
                    echo "{'ret_code':'9991','ret_msg':'验签失败'}";
                    app_redirect(url("index", "uc_money#incharge_log"));
                    exit;
                }

            }else{
                echo "{'ret_code':'9992','ret_msg':'验签失败'}";
                app_redirect(url("index", "uc_money#incharge_log"));
                exit;
            }

        }else{
            echo "fail";
            echo "{'ret_code':'9993','ret_msg':'验签失败'}";
            app_redirect(url("index", "uc_money#incharge_log"));
            exit;
        }
    }



    /*连连支付*/
    public function llpay_urll(){
        //商户订单时间
        $dt_order= $_POST['dt_order' ];
        //商户订单号
        $no_order = $_POST['no_order' ];
        //支付单号
        $oid_paybill = $_POST['oid_paybill' ];
        //交易金额
        $money_order = $_POST['money_order' ];
        //支付结果
        $result_pay =  $_POST['result_pay'];
        //清算日期
        $settle_date =  $_POST['settle_date'];
        //订单描述
        $info_order =  $_POST['info_order'];
        //支付方式
        $pay_type =  $_POST['pay_type'];
        //银行编号
        $bank_code =  $_POST['bank_code'];

        $noticeid = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."payment_notice where order_id='".$no_order."'");
        if($noticeid['is_paid']==1){
            echo "{'ret_code':'0000','ret_msg':'交易成功'}";
            exit;
        }
        $user_id=$noticeid['user_id'];
        $res_data=array(
            'dt_order'=> $_POST['dt_order' ],
            'no_order' =>$_POST['no_order' ],
            'oid_paybill' => $_POST['oid_paybill' ],
            'money_order' => $_POST['money_order' ],
            'result_pay' =>  $_POST['result_pay'],
            'settle_date' =>  $_POST['settle_date'],
            'info_order' =>  $_POST['info_order'],
            'pay_type' =>  $_POST['pay_type'],
            'bank_code' =>  $_POST['bank_code'],
            'user_id'=>$noticeid['user_id'],
        );
        $res_data=json_encode($res_data);

        $pay_notify = array(
            'version'=>1,
            'order_no'=>$noticeid['order_id'],
            'return_mode'=>2,
            'status'=>1,
            'pay_type'=>30,
            'trans_no'=>$oid_paybill,
            'user_id'=>$noticeid['user_id'],
            'num'=>$money_order,
            'addtime'=>time(),
            'signstr'=>$res_data,
        );

        $trans_data=array(
            'is_paid'=>1,
            'notice_sn'=>$oid_paybill,
            'outer_notice_sn'=>"连连PC-充值成功",
            'pay_time'=>time(),
            'pay_date'=>date("Y-m-d H:i:s"),
            'memo'=>$res_data,
        );

        if($result_pay=="SUCCESS"){
            if($noticeid['order_id'] == $no_order) {
                if($noticeid['money'] == $money_order ) {
                    if($noticeid['is_paid'] == 0) {
                        $a = $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_data, "UPDATE", "id=" . $noticeid['id'] . " and is_paid=0 and order_id='".$noticeid['order_id']."' ");
                        if ($a) {
                            $GLOBALS['db']->autoExecute(DB_PREFIX . "pay_notify", $pay_notify, "INSERT");
                            $GLOBALS['db']->query("update " . DB_PREFIX . "user_bank set redline = redline +  " . $money_order . " where user_id=" . $noticeid['user_id'] . " and  bankcard=" . $noticeid['bank_id'] . "");
                            require_once APP_ROOT_PATH . 'system/libs/user.php';

                            $create_time = $GLOBALS['db']->getOne("select create_time  from " . DB_PREFIX . "user_money_log where user_id =$user_id order by create_time desc limit 1");
                            if (time() - $create_time <= 3) {
                                exit;
                            }
                            modify_account(array('money' => $money_order), $noticeid['user_id'], "连连支付pc端充值", 1, "充值成功");
                        }
                        echo "{'ret_code':'0000','ret_msg':'交易成功'}";
                        exit;
                    }else{
                        echo "{'ret_code':'9995','ret_msg':'验签失败'}";
                        exit;
                    }
                }else{
                    echo "{'ret_code':'9996','ret_msg':'验签失败'}";
                    exit;
                }

            }else{
                echo "{'ret_code':'9997','ret_msg':'验签失败'}";
                exit;
            }
        }else{
            echo "{'ret_code':'9998','ret_msg':'验签失败'}";
            exit;
        }
        //end
    }

    public function huifuzhifu_notify(){
        $agent_id=$_GET['agent_id'];
        $agent_bill_id=$_GET['agent_bill_id'];
        $pay_type=$_GET['pay_type'];
        $bill_time=$_GET['agent_bill_time'];
        $pay_amt=$_GET['pay_amt'];
        $key='F7BFD9C34C364C9F9BEE7E52';
        $result=$_GET['result'];
        $jnet_bill_no=$_GET['jnet_bill_no'];
        $remark=urldecode($_GET['remark']);
        $remark = iconv("GB2312","UTF-8//IGNORE",$remark);
        $returnSign=$_GET['sign'];
        $noticeid = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."payment_notice where order_id='".$agent_bill_id."'");
        if($noticeid['is_paid']==1){
			echo "ok";
            exit;
        }
        $user_id=$noticeid['user_id'];
        $trans_datas=array(
            'agent_id'=>$agent_id,
            'agent_bill_id'=>$agent_bill_id,
            'pay_type'=>$pay_type,
            'agent_bill_time'=>$bill_time,
            'pay_amt'=>$pay_amt,
            'result'=>$result,
            'jnet_bill_no'=>$jnet_bill_no,
        );
        $trans_datas=json_encode($trans_datas);

        $pay_notify = array(
            'version'=>1,
            'order_no'=>$noticeid['order_id'],
            'return_mode'=>2,
            'status'=>1,
            'pay_type'=>33,
            'trans_no'=>$jnet_bill_no,
            'user_id'=>$noticeid['user_id'],
            'num'=>$pay_amt,
            'addtime'=>time(),
            'signstr'=>$trans_datas,
        );

        $signStr = '';
        $signStr = $signStr . 'result=' . $result;
        $signStr = $signStr . '&agent_id=' . $agent_id;
        $signStr = $signStr . '&jnet_bill_no=' . $jnet_bill_no;
        $signStr = $signStr . '&agent_bill_id=' . $agent_bill_id;
        $signStr = $signStr . '&pay_type=' . $pay_type;
        $signStr = $signStr . '&pay_amt=' . $pay_amt;
        $signStr = $signStr . '&remark=' . $remark;
        $signStr = $signStr . '&key=' . $key;
        $sign = '';
        $sign =  md5($signStr);
        if($sign == $returnSign) {
            if ($result == 1) {
                if($noticeid['order_id'] == $agent_bill_id) {
                    if ($noticeid['money'] == $pay_amt) {
                        if($noticeid['is_paid']==0) {
                            $trans_data = array(
                                'is_paid' => 1,
                                'notice_sn' => $jnet_bill_no,
                                'memo' => $trans_datas,
                                'outer_notice_sn' => "汇付宝PC-充值成功",
                                'pay_time' => time(),
                                'pay_date' => date("Y-m-d H:i:s"),
                                // 'version'=>$noticeid['version']+1,
                            );
                            $a = $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_data, "UPDATE", "id=" . $noticeid['id'] . " and is_paid=0 and order_id='".$noticeid['order_id']."' ");
                            if ($a) {
                                require_once APP_ROOT_PATH . 'system/libs/user.php';
                                $GLOBALS['db']->autoExecute(DB_PREFIX . "pay_notify", $pay_notify, "INSERT");
                                $create_time = $GLOBALS['db']->getOne("select create_time  from " . DB_PREFIX . "user_money_log where user_id =$user_id order by create_time desc limit 1");
                                if (time() - $create_time <= 3) {
                                    exit;
                                }
                                modify_account(array('money' => $pay_amt), $noticeid['user_id'], "汇付宝pc端充值", 1, "充值成功");
                            }
                            echo "ok";
                            // app_redirect(url("index", "uc_money#incharge"));
                            exit;
                        }else{
                            echo "error";
                            echo "{'ret_code':'9995','ret_msg':'验签失败'}";
                            /// app_redirect(url("index", "uc_money#incharge_log"));
                            exit;
                        }
                    } else {
                        echo "error";
                        echo "{'ret_code':'9996','ret_msg':'验签失败'}";
                       /// app_redirect(url("index", "uc_money#incharge_log"));
                        exit;
                    }
                }else{
                    echo "error";
                    echo "{'ret_code':'9997','ret_msg':'验签失败'}";
                    //app_redirect(url("index", "uc_money#incharge_log"));
                    exit;
                }

            } else {
                echo "error";
                echo "{'ret_code':'9998','ret_msg':'验签失败'}";
               // app_redirect(url("index", "uc_money#incharge_log"));
                exit;
            }
        }else{
            echo "error";
            echo "{'ret_code':'9999','ret_msg':'验签失败'}";
           // app_redirect(url("index", "uc_money#incharge_log"));
            exit;
        }
        //end
    }

    public function llpay_config(){
        $wapllpay_config =array(
            'oid_partner'=>'201411031000083504',
            'RSA_PRIVATE_KEY'=>'-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCmRl6Zn4MmtoBoelHRT6j6ounts/x1+GiJTB9/eBTl01cBK50h
mOUtGBcOVrJCa0C1NkR8BYgOT/WLfFT8cICw6XSJtf2uzZco71jbwXfFe8MiEx/L
XiQNQHuclpkUa1hXFUUo6Qat8X8L++pVZfjav40dPKf7oFWCYLWBCDOdyQIDAQAB
AoGANe0mqz4/o+OWu8vIE1F5pWgG5G/2VjBtfvHwWUARzwP++MMzX/0dfsWMXLsj
b0UnpF3oUizdFn86TLXTPlgidDg6h0RbGwMZou/OIcwWRzgMaCVePT/D1cuhyD7Y
V8YkjVHGnErfxyia1COswAqcpiS4lcTG/RqkAMsdwSZe640CQQDRvkQ7M2WJdydc
9QLQ9FoIMnKx9mDge7+aN6ijs9gEOgh1gKUjenLr6hcGlLRyvYDKQ4b1kes22FUT
/n+AMaEPAkEAyvH05KRzax3NNdRPI45N1KuT1kydIwL3KpOK6mWuHlffed2EiWLS
dhZNiZy9wWuwFPqkrZ8g+jL0iKcCD0mjpwJBAKbWxWmeCZ+eY3ZjAtl59X/duTRs
ekU2yoN+0KtfLG64RvBI45NkHLQiIiy+7wbyTNcXfewrJUIcNRjRcVRkpesCQEM8
BbX6BYLnTKUYwV82NfLPJRtKJoUC5n/kgZFGPnkvA4qMKOybIL6ehPGiS/tYge1x
XD1pCrPZTco4CiambuECQDNtlC31iqzSKmgSWmA5kErqVJB0f1i+a0CbQLlaPGYN
/qwa7TE13yByaUdDDaTIEUrDyuqWd5+IvlbwuVsSlMw=
            -----END RSA PRIVATE KEY-----',
            'key'=>'jiuxindai521',
            'version'=>'1.0',
            'app_request'=>'3',
            'sign_type'=>strtoupper('MD5'),
            'valid_order'=>'10080',
            'input_charset'=>strtolower('utf-8'),
            'id_type'=>'0',
            'transport'=>'http',
        );
    }
}
?>