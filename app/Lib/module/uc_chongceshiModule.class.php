<?php
require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_chongceshiModule extends SiteBaseModule
{
    private $creditsettings;
    private $allow_exchange = false;

    public function __construct()
    {
        if (in_array(ACTION_NAME, array("carry", "savecarry"))) {
            $is_ajax = intval($_REQUEST['is_ajax']);
            //判断是否是黑名单会员
            if ($GLOBALS['user_info']['is_black'] == 1) {
                showErr("您当前无权限提现，具体联系网站客服", $is_ajax, url("index", "uc_center"));
            }
        }
        if (file_exists(APP_ROOT_PATH . "public/uc_config.php")) {
            require_once APP_ROOT_PATH . "public/uc_config.php";
        }
        if (app_conf("INTEGRATE_CODE") == 'Ucenter' && UC_CONNECT == 'mysql') {
            if (file_exists(APP_ROOT_PATH . "public/uc_data/creditsettings.php")) {
                require_once APP_ROOT_PATH . "public/uc_data/creditsettings.php";
                $this->creditsettings = $_CACHE['creditsettings'];
                if (count($this->creditsettings) > 0) {
                    foreach ($this->creditsettings as $k => $v) {
                        $this->creditsettings[$k]['srctitle'] = $this->credits_CFG[$v['creditsrc']]['title'];
                    }
                    $this->allow_exchange = true;
                    $GLOBALS['tmpl']->assign("allow_exchange", $this->allow_exchange);
                }
            }
        }
        parent::__construct();
    }

    public function exchange()
    {
        $user_info = get_user_info("*", "id = " . intval($GLOBALS['user_info']['id']));
        $GLOBALS['tmpl']->assign("user_info", $user_info);
        $GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['UC_EXCHANGE']);
        $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_money_exchange.html");
        $GLOBALS['tmpl']->assign("exchange_data", $this->creditsettings);
        $GLOBALS['tmpl']->assign("exchange_json_data", json_encode($this->creditsettings));
        $GLOBALS['tmpl']->display("page/uc.html");
    }

    public function doexchange()
    {
        if ($this->allow_exchange) {
            $user_pwd = md5(addslashes(trim($_REQUEST['password'])));
            $user_info = get_user_info("*", "id = " . intval($GLOBALS['user_info']['id']));

            if ($user_info['user_pwd'] == "") {
                //判断是否为初次整合
                //载入会员整合
                $integrate_code = trim(app_conf("INTEGRATE_CODE"));
                if ($integrate_code != '') {
                    $integrate_file = APP_ROOT_PATH . "system/integrate/" . $integrate_code . "_integrate.php";
                    if (file_exists($integrate_file)) {
                        require_once $integrate_file;
                        $integrate_class = $integrate_code . "_integrate";
                        $integrate_obj = new $integrate_class;
                    }
                }
                if ($integrate_obj) {
                    $result = $integrate_obj->login($user_info['user_name'], $user_pwd);
                    if ($result['status']) {
                        $GLOBALS['db']->query("update " . DB_PREFIX . "user set user_pwd = '" . $user_pwd . "' where id = " . $user_info['id']);
                        $user_info['user_pwd'] = $user_pwd;
                    }
                }
            }
            if ($user_info['user_pwd'] == $user_pwd) {
                $cfg = $this->creditsettings[addslashes(trim($_REQUEST['key']))];
                if ($cfg) {
                    $amount = floor($_REQUEST['amountdesc']);
                    $use_amount = floor($amount * $cfg['ratio']); //消耗的本系统积分
                    $field = $this->credits_CFG[$cfg['creditsrc']]['field'];

                    if ($user_info[$field] < $use_amount) {
                        $data = array("status" => false, "message" => $cfg['srctitle'] . "不足，不能兑换");
                        ajax_return($data);
                    }

                    include_once(APP_ROOT_PATH . 'uc_client/client.php');
                    $res = call_user_func_array("uc_credit_exchange_request", array(
                        $user_info['integrate_id'],  //uid(整合的UID)
                        $cfg['creditsrc'],  //原积分ID
                        $cfg['creditdesc'],  //目标积分ID
                        $cfg['appiddesc'],  //toappid目标应用ID
                        $amount,  //amount额度(计算过的目标应用的额度)
                    ));
                    if ($res) {
                        //兑换成功
                        $use_amount = 0 - $use_amount;
                        $credit_data = array($field => $use_amount);
                        require_once APP_ROOT_PATH . "system/libs/user.php";
                        modify_account($credit_data, $user_info['id'], "ucenter兑换支出", 22);
                        $data = array("status" => true, "message" => "兑换成功");
                        ajax_return($data);
                    } else {
                        $data = array("status" => false, "message" => "兑换失败");
                        ajax_return($data);
                    }
                } else {
                    $data = array("status" => false, "message" => "非法的兑换请求");
                    ajax_return($data);
                }
            } else {
                $data = array("status" => false, "message" => "登录密码不正确");
                ajax_return($data);
            }
        } else {
            $data = array("status" => false, "message" => "未开启兑换功能");
            ajax_return($data);
        }
    }

    public function incha()
    {
        // $paypassword = $GLOBALS['db']->getOne("SELECT paypassword FROM " . DB_PREFIX . "user WHERE id=" . $GLOBALS['user_info']['id']);
        // $userbank = $GLOBALS['db']->getOne("SELECT bankcard FROM " . DB_PREFIX . "user_bank WHERE user_id=" . $GLOBALS['user_info']['id'] . " and status=1");
        // if (empty($userbank)) {
        //     app_redirect(url("index", "uc_money#bank"));
        // }

        // if (empty($paypassword)) {
        //     app_redirect(url("index", "uc_account#paypassword"));
        // }

       
        $serial= $_GET['odl'];
        var_dump($serial);
        $user_id = $GLOBALS['user_info']['id'];
        var_dump($user_id);exit;
        $vo = $GLOBALS['db']->getRow("select real_name,idno,mobile from " . DB_PREFIX . "user where id =$user_id and cunguan_tag =1");
        if(empty($vo)){ 
            app_redirect(url("index","uc_depository_account#index"));
        }

        $paypassword=$GLOBALS['db']->getOne("SELECT cunguan_pwd FROM ".DB_PREFIX."user WHERE id=$user_id and cunguan_tag=1");    
        if(empty($paypassword)){
            app_redirect(url("index","uc_depository_paypassword#pc_setpaypassword"));
        }

        $userbank=$GLOBALS['db']->getOne("SELECT bankcard FROM ".DB_PREFIX."user_bank WHERE user_id=".$GLOBALS['user_info']['id']." and status=1 and cunguan_tag=1");
        if(empty($userbank)){
            app_redirect(url("index","uc_depository_addbank#check_pwd"));
        }
       
        // $bank = $GLOBALS['db']->getRow("SELECT ub.id,ub.bankcard,ub.region_lv2,ub.region_lv3,ub.region_lv4,ub.bankzone,b.name,b.icon,b.single_quota,b.day_limit,b.ll_single_quota,b.ll_day_limit FROM " . DB_PREFIX . "user_bank as ub join " . DB_PREFIX . "bank as b on ub.bank_id=b.id where ub.user_id=" . $user_id . " and ub.status=1 and b.is_rec=1 order by ub.redline desc limit 1");
        // if ($bank) {
        //     $bank['sub_card'] = substr($bank['bankcard'], -4, 4);
        //     $bank['bankcard'] = substr($bank['bankcard'], 0, 4) . "**** **** ****" . substr($bank['bankcard'], -3, 3);
        //     $sheng = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "region_conf where id =" . $bank['region_lv2']);
        //     $shi = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "region_conf where id =" . $bank['region_lv3']);
        //     $qu = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "region_conf where id =" . $bank['region_lv4']);
        //     $bank['addr'] = $sheng . '-' . $shi . '-' . $qu;
        // }
        // $GLOBALS['tmpl']->assign("bank", $bank);
        // $bankcard = $GLOBALS['db']->getRow("SELECT bank_id,bankcard FROM  " . DB_PREFIX . "user_bank WHERE user_id=$user_id and status=1");
        // $bankcard_info = $GLOBALS['db']->getRow("SELECT icon,day_limit,single_quota,name FROM  " . DB_PREFIX . "bank WHERE id=" . $bankcard['bank_id']);
        // $recharger_explain = trimall($GLOBALS['db']->getOne("SELECT val FROM " . DB_PREFIX . "m_config WHERE code = 'recharger_explain'"));
        // $bankcard['last_four'] = substr($bankcard['bankcard'], -4);
        // $GLOBALS['tmpl']->assign("bank_info", $bankcard_info);
        // $GLOBALS['tmpl']->assign("bankcard", $bankcard);
        // $GLOBALS['tmpl']->assign("recharger_explain", $recharger_explain);
        $GLOBALS['tmpl']->assign("serial", $serial);
        $GLOBALS['tmpl']->assign("cate_title", "充值");
        $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_money_ceshi.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }


    public function DoDpTrade()
    {

        //$now = TIME_UTC;
        date_default_timezone_set('PRC');
        $now = time();
        $user_add_time = date("ymdhis", $now);
        if (!$_REQUEST) {
            app_redirect("404.html");
            exit();
        }
        foreach ($_REQUEST as $k => $v) {
            $_REQUEST[$k] = htmlspecialchars(addslashes($v));
        }
        $user_type = intval(strim($_REQUEST['incharge_mode'])); //快捷还是网
        $pTrdAmt = floatval(strim($_REQUEST['pTrdAmt']));//金钱
        $pTrdBnkCode = strim($_REQUEST['incharge_channel']);//选择渠道
        $user_id = $GLOBALS['user_info']['id'];
        if ($pTrdAmt < 1) {
            exit;
        }
        $datas = get_user_info("*", "id = " . $user_id);
        //银行卡
        $bankcard = $GLOBALS['db']->getRow("SELECT bankcard,bank_mobile,bank_id,baofootag FROM " . DB_PREFIX . "user_bank as u LEFT JOIN " . DB_PREFIX . "bank as b on bank_id = b.id WHERE u.user_id=$user_id and u.status=1 ");
        $record = array(
            'pTrdAmt' => $pTrdAmt,
            'user_id' => $user_id,
            'now' => $now,
            'user_add_time' => $user_add_time,
        );

        if ($pTrdBnkCode == 1) {
            $this->lianlianzhifu($datas, $bankcard, $record);
        } elseif ($pTrdBnkCode == 2) {
            $this->baofoo($datas, $bankcard, $record);
        } elseif ($pTrdBnkCode == 3) {
            $this->webhfb($bankcard, $record);
        } elseif ($pTrdBnkCode == 4) {
            $this->webcj($pTrdAmt, $user_id, $bankcard);
        }
    }

    /**
     * 宝付支付 根据充值信息建立连连表单请求
     * @author dy
     * @param array $info 充值信息
     * @return null
     */
    private function baofoo($datas, $bankcard, $record)
    {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        require APP_ROOT_PATH . "system/utils/BAOFOOSDK/ini.php";
        $pay_code = $bankcard['baofootag'];    //银行编码
        $acc_no = $bankcard['bankcard'];//银行卡卡号
        $id_card = $datas['idno'];//身份证号码
        $id_holder = $datas['real_name'];//姓名
        $mobile = $bankcard['bank_mobile'];//银行预留手机号
        $txn_amt = $record['pTrdAmt'];//交易金额额
        $txn_amt *= 100;//金额以分为单位（把元转换成分）
        //====================系统动态生成值=======================================
        $trans_id = $this->orderId();    //商户订单号
        $trade_date = date('YmdHis', time());    //订单日期
        //=================接口固定参数==========================================
        $version = "4.0.0.0";//接口版本
        $txn_sub_type = "03"; //交易子类
        $biz_type = "0000"; //接入类型
        $txn_type = "03311";//交易类型
        $id_card_type = "01"; //身份证类型
        $page_url = $http_type . $_SERVER['HTTP_HOST'] . "/member.php?ctl=uc_money&act=baofuyemian";//页面通知地址（支付成功跳转的页面）
        $return_url = $http_type . $_SERVER['HTTP_HOST'] . "/member.php?ctl=uc_money&act=baofoonotifyurl";//服务器通知地址。（支付成功后宝付异步通知商户服务器的地址）
        $back_url = $http_type . $_SERVER['HTTP_HOST'] . "/member.php?ctl=uc_money&act=baofuyemian";
        $language = "1"; //固定值1（中文）
        $input_charset = "1";//字符集(1 代表UTF-8，2 代表GBK，3 代表GB2312）
        $data_content_parms = array(
            'txn_sub_type' => $txn_sub_type,
            'biz_type' => $biz_type,
            'terminal_id' => $terminal_id,
            'member_id' => $member_id,
            'pay_code' => $pay_code,
            'acc_no' => $acc_no,
            'id_card_type' => $id_card_type,
            'id_card' => $id_card,
            'id_holder' => $id_holder,
            'mobile' => $mobile,
            'valid_date' => '',//暂不支持信用卡（传空）
            'valid_no' => '',//暂不支持信用卡（传空）
            'trans_id' => $trans_id,
            'txn_amt' => $txn_amt,
            'trade_date' => $trade_date,
            'commodity_name' => '玖财通wap充值',
            'commodity_amount' => '1',//商品数量（默认为1）
            'user_name' => $id_holder,
            'page_url' => $page_url,
            'return_url' => $return_url,
            'additional_info' => '附加字段',
            'req_reserved' => '保留域');

        $trans_data = array(
            'is_paid' => 0,
            'create_time' => time(),
            'money' => $record['pTrdAmt'],
            'order_id' => $trans_id,
            'user_id' => $record['user_id'],
            'outer_notice_sn' => "宝付PC-",
            'payment_id' => 31,
            'create_date' => date("Y-m-d"),
            'bank_id' => $acc_no,
        );
        $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_data, 'INSERT', '', 'SILENT');
        $Encrypted_string = str_replace("\\/", "/", json_encode($data_content_parms));//转JSON
        $baofoosdk = new BaofooSdk($pfxfilename, $cerfilename, $private_key_password, FALSE); //实例化加密类。
        $data_content = $baofoosdk->encryptedByPrivateKey($Encrypted_string);    //RSA加密
        //$this->llpay_gateway_new= "https://gw.baofoo.com/apipay/wap";
        $this->llpay_gateway_new = "https://gw.baofoo.com/apipay/pc";
        $method = "post";
        $button_name = "确定";
        $sHtml = "<form id='llpaysubmit' target='_blank' name='llpaysubmit' action='" . $this->llpay_gateway_new . "' method='" . $method . "'>";
        $sHtml .= "<input type='hidden' name='version' value='" . $version . "'/>";
        $sHtml .= "<input type='hidden' name='input_charset' value='" . $input_charset . "'/>";
        $sHtml .= "<input type='hidden' name='language' value='" . $language . "'/>";
        $sHtml .= "<input type='hidden' name='terminal_id' value='" . $terminal_id . "'/>";
        $sHtml .= "<input type='hidden' name='txn_type' value='" . $txn_type . "'/>";
        $sHtml .= "<input type='hidden' name='txn_sub_type' value='" . $txn_sub_type . "'/>";
        $sHtml .= "<input type='hidden' name='member_id' value='" . $member_id . "'/>";
        $sHtml .= "<input type='hidden' name='data_type' value='" . $data_type . "'/>";
        $sHtml .= "<input type='hidden' name='data_content' value='" . $data_content . "'/>";
        $sHtml .= "<input type='hidden' name='back_url' value='" . $back_url . "'/>";
        $sHtml = $sHtml . "<input type='submit' value='" . $button_name . "'></form>";
        $sHtml = $sHtml . "<script>document.forms['llpaysubmit'].submit();</script>";
        //echo $sHtml;
        //showSuccess("充值", 1);
        exit;
    }

    public function baofoonotifyurl()
    {
        $path = "system/utils";
        require_once $path . '/BAOFOOSDK/ini.php';
        $endata_content = $_REQUEST["data_content"];
        $baofoosdk = new BaofooSdk($pfxfilename, $cerfilename, $private_key_password); //实例化加密类
        $endata_contents = $baofoosdk->decryptByPublicKey($endata_content);  //RSA解密
        $endata_content = json_decode($endata_contents, TRUE);
        $FactMoney = $endata_content['succ_amt']; //交易成功后返回的金额
        $TransID = $endata_content['trans_id'];
        $trans_no = $endata_content['trans_no'];
        $acct_name = $endata_content['additional_info'];
        $no_order = $TransID;
        $noticeid = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "payment_notice where order_id='" . $no_order . "'");
        if ($noticeid['is_paid'] == 1) {
            echo "OK";
            exit;
        }
        $user_id = $noticeid['user_id'];
        $pay_notify = array(
            'version' => 1,
            'order_no' => $noticeid['order_id'],
            'return_mode' => 2,
            'status' => 1,
            'pay_type' => 31,
            'trans_no' => $trans_no,
            'user_id' => $noticeid['user_id'],
            'num' => $FactMoney,
            'addtime' => time(),
            'signstr' => $endata_contents,
        );

        if ($endata_content['resp_code'] == "0000") {
            if ($noticeid['order_id'] == $no_order) {
                if ($noticeid['money'] == $FactMoney) {
                    if ($noticeid['is_paid'] == 0) {
                        $trans_data = array(
                            'is_paid' => 1,
                            'notice_sn' => $trans_no,
                            'memo' => $endata_contents,
                            'outer_notice_sn' => "宝付PC-充值成功",
                            'pay_time' => time(),
                            'pay_date' => date("Y-m-d H:i:s"),
                        );
                        $a = $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_data, "UPDATE", "id=" . $noticeid['id'] . " and is_paid=0 and order_id='" . $noticeid['order_id'] . "' ");
                        if ($a) {
                            $GLOBALS['db']->autoExecute(DB_PREFIX . "pay_notify", $pay_notify, "INSERT");
                            require_once APP_ROOT_PATH . 'system/libs/user.php';
                            $create_time = $GLOBALS['db']->getOne("select create_time  from " . DB_PREFIX . "user_money_log where user_id =$user_id order by create_time desc limit 1");
                            if (time() - $create_time <= 3) {
                                exit;
                            }
                            modify_account(array('money' => $FactMoney), $noticeid['user_id'], "宝付pc端充值", 1, "充值成功");
                        }
                        echo "OK";
                        //app_redirect(url("index", "uc_money#incharge"));
                        exit;
                    } else {
                        echo "{'ret_code':'9994','ret_msg':'验签失败'}";
                        exit;
                    }
                } else {
                    echo "{'ret_code':'9991','ret_msg':'验签失败'}";
                    //app_redirect(url("index", "uc_money#incharge_log"));
                    exit;
                }
            } else {
                echo "{'ret_code':'9992','ret_msg':'验签失败'}";
                //app_redirect(url("index", "uc_money#incharge_log"));
                exit;
            }
        } else {
            echo "fail";
            echo "{'ret_code':'9993','ret_msg':'验签失败'}";
            //app_redirect(url("index", "uc_money#incharge_log"));
            exit;
        }
    }

    public function baofuyemian()
    {
        $path = "system/utils";
        require_once $path . '/BAOFOOSDK/ini.php';
        $endata_content = $_REQUEST["data_content"];
        $baofoosdk = new BaofooSdk($pfxfilename, $cerfilename, $private_key_password); //实例化加密类
        $endata_contents = $baofoosdk->decryptByPublicKey($endata_content);  //RSA解密
        $endata_content = json_decode($endata_contents, TRUE);
        $FactMoney = $endata_content['succ_amt']; //交易成功后返回的金额
        $TransID = $endata_content['trans_id'];
        $trans_no = $endata_content['trans_no'];
        $acct_name = $endata_content['additional_info'];
        $no_order = $TransID;
        $noticeid = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "payment_notice where order_id='" . $no_order . "'");
        if ($noticeid['is_paid'] == 1) {
            app_redirect(url("index", "uc_money#incharge_log"));
            exit;
        }
        $user_id = $noticeid['user_id'];
        $pay_notify = array(
            'version' => 1,
            'order_no' => $noticeid['order_id'],
            'return_mode' => 2,
            'status' => 1,
            'pay_type' => 31,
            'trans_no' => $trans_no,
            'user_id' => $noticeid['user_id'],
            'num' => $FactMoney,
            'addtime' => time(),
            'signstr' => $endata_contents,
        );

        if ($endata_content['resp_code'] == "0000") {
            if ($noticeid['order_id'] == $no_order) {
                if ($noticeid['money'] == $FactMoney) {
                    if ($noticeid['is_paid'] == 0) {
                        $trans_data = array(
                            'is_paid' => 1,
                            'notice_sn' => $trans_no,
                            'memo' => $endata_contents,
                            'outer_notice_sn' => "宝付PC-充值成功",
                            'pay_time' => time(),
                            'pay_date' => date("Y-m-d H:i:s"),
                        );
                        $a = $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $trans_data, "UPDATE", "id=" . $noticeid['id'] . " and is_paid=0 and order_id='" . $noticeid['order_id'] . "' ");
                        if ($a) {
                            $GLOBALS['db']->autoExecute(DB_PREFIX . "pay_notify", $pay_notify, "INSERT");
                            require_once APP_ROOT_PATH . 'system/libs/user.php';
                            $create_time = $GLOBALS['db']->getOne("select create_time  from " . DB_PREFIX . "user_money_log where user_id =$user_id order by create_time desc limit 1");
                            if (time() - $create_time <= 3) {
                                exit;
                            }
                            modify_account(array('money' => $FactMoney), $noticeid['user_id'], "宝付pc端充值", 1, "充值成功");
                        }

                        /************充值成功后微信模板消息开始*********************/
                        $wx_openid = $GLOBALS['db']->getOne("select wx_openid from " . DB_PREFIX . "user where id=" . $noticeid['user_id']);
                        if ($wx_openid) {
                            if (app_conf('WEIXIN_TMPL')) {
                                $tmpl_url = app_conf('WEIXIN_TMPL_URL');
                                $tmpl_datas = array();
                                $tmpl_datas['first'] = '尊敬的用户，您完成了一笔充值';
                                $tmpl_datas['keyword1'] = $FactMoney . '元';
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
                        echo "OK";
                        app_redirect(url("index", "uc_money#incharge_log"));
                        exit;
                    } else {
                        echo "{'ret_code':'9995','ret_msg':'验签失败'}";
                        app_redirect(url("index", "uc_money#incharge_log"));
                        exit;
                    }
                } else {
                    echo "{'ret_code':'9996','ret_msg':'验签失败'}";
                    app_redirect(url("index", "uc_money#incharge_log"));
                    exit;
                }
            } else {
                echo "{'ret_code':'9997','ret_msg':'验签失败'}";
                app_redirect(url("index", "uc_money#incharge_log"));
                exit;
            }

        } else {
            echo "fail";
            echo "{'ret_code':'9998','ret_msg':'验签失败'}";
            app_redirect(url("index", "uc_money#incharge_log"));
            exit;
        }
    }

    public function orderId(){
        $yCode = array('B', 'W', 'E', 'R', 'T', 'Y', 'N','A','M', 'C', 'O');
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }


}
