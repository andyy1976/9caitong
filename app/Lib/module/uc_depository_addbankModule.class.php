<?php

require_once APP_ROOT_PATH."system/utils/Depository/Require.php";

class uc_depository_addbankModule extends SiteBaseModule
{

    //绑卡--第一步:验证交易密码
    function check_pwd()
    {
        $user = $GLOBALS['user_info'];
        if ($user['id'] > 0) {
            if($user['accno']){
                if($user['cunguan_pwd']){
                    $Publics = new Publics();
                    $seqno = $Publics->seqno();
                    $re = $Publics->verify_trans_password('uc_depository_addbank', "addbank_index_one", $user['id'], '4', $seqno,'_self');
                    echo $re;die;
                }else{
                    app_redirect(url('index','uc_depository_paypassword#setpaypassword'));
                }
            }else{
                app_redirect(url('index','uc_depository_account#index'));
            }
        }else{
            app_redirect(url('index','user#login'));
        }
    }

    //绑卡--第一步:验证交易密码
    function wap_check_pwd()
    {
        $user = $GLOBALS['user_info'];
        if ($user['id'] > 0) {
            if($user['accno']){
                if($user['cunguan_pwd']){
                    $Publics = new Publics();
                    $seqno = $Publics->seqno();
                    $re = $Publics->verify_trans_password('uc_depository_addbank', "cg_bind_bank", $user['id'], '4', $seqno,'_self');
                    echo $re;die;
                }else{
                    app_redirect(url('index','uc_depository_paypassword#setpaypassword'));
                }
            }else{
                app_redirect(url('index','uc_depository_account#index'));
            }
        }else{
            app_redirect(url('index','user#login'));
        }

    }
    function cg_bind_bank(){
        $user = $GLOBALS['user_info'];
        if($user['id']>0){
            $banks = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."bank where bankid != ''");
            $GLOBALS['tmpl']->assign("banks",$banks);
            $seqno = strim($_GET['businessSeqNo']);
            $GLOBALS['tmpl']->assign("seqno",$seqno);
            $GLOBALS['tmpl']->assign("cate_title","绑定银行卡");
            $GLOBALS['tmpl']->assign("inc_file","inc/uc/cg_bank.html");
            $GLOBALS['tmpl']->display("page/uc.html");
        }else{
            app_redirect(url('index','user#login'));
        }

    }

    function cg_bank_success(){
        $jump = machineInfo();
        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->assign("ajax_return",es_cookie::get("jump_url_depository"));
        $url = WAP_SITE_DOMAIN."/index.php?ctl=uc_money&act=incharge";
        $GLOBALS['tmpl']->assign("url",$url);
        $GLOBALS['tmpl']->assign("cate_title","绑定银行卡");
        $GLOBALS['tmpl']->assign("inc_file","inc/uc/cg_bank_success.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }

    function cg_bank_error(){
        $jump = machineInfo();
        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->assign("ajax_return",es_cookie::get("jump_url_depository"));
        $url = WAP_SITE_DOMAIN."/index.php?ctl=uc_money&act=incharge";
        $GLOBALS['tmpl']->assign("url",$url);
        $GLOBALS['tmpl']->assign("cate_title","绑定银行卡");
        $GLOBALS['tmpl']->assign("inc_file","inc/uc/cg_bank_error.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }

    public function addbank_index_one(){
        $user =  $GLOBALS['user_info'];
        if($user['id']>0){
            $vo = $GLOBALS['db']->getRow("select real_name,idno,mobile from " . DB_PREFIX . "user where id = " . $user['id']);
            $banks = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."bank where bankid != ''");

            $vo['bes'] = $_GET['businessSeqNo'];
            $GLOBALS['tmpl']->assign("vo",$vo);
            $GLOBALS['tmpl']->assign("banks",$banks);
            $GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_cg_bank.html");
            $GLOBALS['tmpl']->display("page/uc.html");
        }else{
            app_redirect(url('index','user#login'));
        }

    }
    //绑卡
    function addbank_index()
    {

        $user = $GLOBALS['user_info'];
        if ($user['id'] > 0) {

            $user_info = $GLOBALS['db']->getRow("select accno,real_name,idno,mobile from " . DB_PREFIX . "user where id = " . $user['id']);
            $bank_info = $GLOBALS['db']->getRow("select bankcard,real_name,bank_mobile from ".DB_PREFIX."user_bank where user_id = ".$user['id']." and status =1 and cunguan_tag=1");
            if (!$user_info['accno']) {
                $root['status'] = 0;
                $root['info'] = '请先开户';
                ajax_return($root);
            }
            if ($bank_info['bankcard']) {
                $root['status'] = 0;
                $root['info'] = '已经绑过卡';
                ajax_return($root);
            }
            $validateCode = strim($_REQUEST['validateCode']);
            $sms_code = strim($_REQUEST['sms_code']);
            if($validateCode){
                $sms_code = $validateCode;
            }
            if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($_REQUEST["mobile"])."' AND verify_code='".$sms_code."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
                $json['status'] = 0;
                $json['info'] = "短信验证码出错或已过期";
                ajax_return($json);
            }

            $user_msg['user_id'] = $user['id'];
            $user_msg['real_name'] = $user_info['real_name'];
            $user_msg['idno'] = $user_info['idno'];
            $user_msg['mobile'] = $user_info['mobile'];
            $user_msg['bank_id'] = strim($_REQUEST['bank_code']); //所属银行ID
            $user_msg['bankcard'] = str_replace(' ','',strim($_REQUEST['cardId'])); //银行卡号
            $user_msg['bank_mobile'] = strim($_REQUEST['mobile']); //银行预留手机号
            $user_msg['businessSeqNo'] = strim($_REQUEST['businessSeqNo']); //流水号
            $user_msg['oldbankcard'] = '';
            $user_msg['dep_account'] = $user_info['accno'];
			$bank_name = $GLOBALS['db']->getOne('select cunguan_name from '.DB_PREFIX.'bank where bank_id='.$user_msg['bank_id']);
            /* $Register = new Register();
            $res = $Register->register1($user_msg, 'B01'); */
			if(isMobile()){
				$url = "https://36.110.98.254:19001/p2ph5/standard/cardBind2.html";
			}else{
				$url = "https://36.110.98.254:19001/p2ph5/pc/cardBind2.html";
			}
			$bank['user_id'] = $user['id'];
            $bank['bank_id'] = strim($_REQUEST['bank_code']); //所属银行ID
            $bank['bankcard'] = str_replace(' ','',strim($_REQUEST['cardId'])); //银行卡号
            $bank['bank_mobile'] = strim($_REQUEST['mobile']); //银行预留手机号
            $bank['real_name'] = $user_info['real_name'];
            $bank['create_time'] = TIME_UTC;
            $bank['addip'] = get_client_ip();
            $bank['status'] = 0;
            $bank['cunguan_tag'] = 1;
            $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bank", $bank, "INSERT");
			$data['seqno'] = $user_msg['businessSeqNo'];
            $data['user_id'] = $user['id'];
            $data['bankcard'] = $user_msg['bankcard'];
            $data['accNo'] = $user['id'];
            $data['type'] = 'B01';
            $data['add_time'] = TIME_UTC;
            $data['date_time'] = date('Y-m-d H:i:s');
            $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "INSERT");
			$backurl = urlencode("https://" . $_SERVER['HTTP_HOST'] . "/uc_depository_addbank/back_bank_info"); 
			list($msec, $sec) = explode(' ', microtime());
			$signtime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
			$signature = $signtime."|".$user_msg['user_id'];
			$pub = new Publics();
			$data_content = $pub->rsa_encrypt($signature); //RSA加密
			$urls = "?systemCode=JCT&userId=".$user_msg['user_id']."&backURL=".$backurl."&signTime=".$signtime."&signature=".$data_content."&businessSeqNo=".$user_msg['businessSeqNo']."&channelname=玖财通";
			$s="&cardNo=".$user_msg['bankcard']."&bankname=".$bank_name."&idCardNo=".$user_msg['idno']."&cardPhoneNumber=".$user_msg['bank_mobile']."&userName=".$user_msg['real_name']."&userNameType=2&idCardNoType=2&cardNoType=2&cardPhoneNumberType=2";
			$url = $url.$urls.$s;
			if(isMobile()){
				$root['info'] =$url;
				$root['status'] =1;
				ajax_return($root);exit;
			}else{
				showSuccess('点击确定跳转',1,$url);
			}
			
			//app_redirect($url);
            

           /*  if ($res['res']['respHeader']['respCode'] == 'P2P0000') {
                // 绑卡成功奖励成长值
                require_once APP_ROOT_PATH."system/user_level/Level.php";
                $level=new Level();
                $level->get_grow_point(3);

                $bank['user_id'] = $user['id'];
                $bank['bank_id'] = strim($_REQUEST['bank_code']); //所属银行ID
                $bank['bankcard'] = str_replace(' ','',strim($_REQUEST['cardId'])); //银行卡号
                $bank['bank_mobile'] = strim($_REQUEST['mobile']); //银行预留手机号
                $bank['real_name'] = $user_info['real_name'];
                $bank['create_time'] = TIME_UTC;
                $bank['addip'] = get_client_ip();
                $bank['status'] = 1;
                $bank['cunguan_tag'] = 1;
                $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bank", $bank, "INSERT");
                $root['jump'] = url('index','uc_money#bank');
                $root['status'] = 1;
                $root['info'] = '绑卡成功';
				addsource(0,0,$user['id'],2,1);
                ajax_return($root);
            } else {
                $root['status'] = 0;
                $root['info'] = $res['res']['respHeader']['respMsg'];
                //$root['jump'] = url('index','uc_money#bank');
                ajax_return($root);
            }*/
        } else {
            $root['status'] = 0;
            $root['info'] = '请先登录';
            ajax_return($root);
        }
    }

    function bank_info_tip(){
        $carry_status = $GLOBALS['db']->getAll("SELECT count(*) as user_carry FROM ".DB_PREFIX."user_carry where user_id=".intval($GLOBALS['user_info']['id'])." AND status IN(0,3) and cunguan_tag=1");
        $user_bank = $GLOBALS['db']->getAll("SELECT count(*) as bank FROM ".DB_PREFIX."deal ub LEFT JOIN ".DB_PREFIX."deal_load b on ub.id=b.deal_id where b.user_id=".intval($GLOBALS['user_info']['id'])." AND ub.deal_status IN(1,2,4) and ub.cunguan_tag=1");
        $money=$GLOBALS['user_info']['cunguan_money'];
        if($money !=0.00 || $user_bank[0]['bank']>0 || $carry_status[0]['user_carry']>0) {
            $info =  $GLOBALS["tmpl"]->fetch("inc/uc/uc_money_carry_editbank_no.html");
            showErr($info,1);
        }
        $data['status'] = 1;
        $data['jump'] = url('index','uc_depository_addbank#change_check_pwd');
        ajax_return($data); 
    }
    //换卡--第一步:验证交易密码
    function change_check_pwd()
    {
        $user = $GLOBALS['user_info'];
        if ($user['id'] > 0) {
            $Publics = new Publics();
            $seqno = $Publics->seqno();
            $re = $Publics->verify_trans_password('uc_depository_addbank', "change_bank_index", $user['id'], '4', $seqno,'_self');
            echo $re;die;
        }else{
            app_redirect(url('index','user#login'));
        }

    }

    function change_bank_index(){
        $user = $GLOBALS['user_info'];
        if($user['id']>0){
            $seqno = strim($_GET['businessSeqNo']);
            $GLOBALS['tmpl']->assign("seqno",$seqno);
            $vo['real_name'] = $user['real_name'];
            $vo['idno'] = $user['idno'];
            $GLOBALS['tmpl']->assign('vo',$vo);
            $banks = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."bank where bankid != ''");
            $GLOBALS['tmpl']->assign("banks",$banks);
            $GLOBALS['tmpl']->assign("cate_title","更换银行卡");
            $GLOBALS['tmpl']->assign("inc_file","inc/uc/cg_change_bank.html");
            $GLOBALS['tmpl']->display("page/uc.html");
        }else{
            app_redirect(url('index','user#login'));
        }
    }

    //更换银行卡
    function change_bank()
    {

        $user = $GLOBALS['user_info'];
        if ($user['id'] > 0) {
            if (!$user['accno']) {
                $root['status'] = 0;
                $root['info'] = '请先开户';
                ajax_return($root);
            }

            $bank_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "user_bank where user_id = " . $user['id']." and status=1 and cunguan_tag=1");
            if(empty($bank_info['bankcard'])){
                $root['status'] = 0;
                $root['info'] = '请先绑卡';
                ajax_return($root);
            }

            $validateCode = strim($_REQUEST['validateCode']);
            $sms_code = strim($_REQUEST['sms_code']);
            if($validateCode){
                $sms_code = $validateCode;
            }
            if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($_REQUEST["mobile"])."' AND verify_code='".$sms_code."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
                $json['status'] = 0;
                $json['info'] = "短信验证码出错或已过期";
                ajax_return($json);
            }
            $user_msg['user_id'] = $user['id'];
            $user_msg['real_name'] = $user['real_name'];;
            $user_msg['idno'] = $user['idno'];
            $user_msg['mobile'] = $user['mobile'];
            $user_msg['bank_id'] = strim($_REQUEST['bank_code']); //所属银行ID
            $user_msg['bankcard'] = str_replace(' ','',strim($_REQUEST['cardId'])); //银行卡号
            $user_msg['bank_mobile'] = strim($_REQUEST['mobile']); //银行预留手机号
            $user_msg['businessSeqNo'] = strim($_REQUEST['businessSeqNo']); //流水号
            $user_msg['oldbankcard'] = $bank_info['bankcard'];
            $user_msg['dep_account'] = $user['accno'];

            $Register = new Register();
            $result = $Register->register1($user_msg, 'B03');
            $data['seqno'] = strim($_REQUEST['businessSeqNo']);
            $data['user_id'] = $user['id'];
            $data['accNo'] = $user['accno'];
            $data['secBankaccNo'] = $result['res']['outBody']['secBankaccNo'];
            $data['form_con'] = json_encode($result['map']);
            $data['back_con'] = json_encode($result['res'],JSON_UNESCAPED_UNICODE);
            $data['type'] = "B03";
            $data['add_time'] = TIME_UTC;
            $data['date_time'] = date('Y-m-d H:i:s');

            $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "INSERT");
            if ($result['res']['respHeader']['respCode'] == 'P2P0000') {
                $GLOBALS['db']->query("update ".DB_PREFIX."user_bank set status=0 where id=".$bank_info['id']);
                $bank['user_id'] = $user['id'];
                $bank['bank_id'] = strim($_REQUEST['bank_code']); //所属银行ID
                $bank['bankcard'] = str_replace(' ','',strim($_REQUEST['cardId'])); //银行卡号
                $bank['bank_mobile'] = strim($_REQUEST['mobile']); //银行预留手机号
                $bank['real_name'] = $user['real_name'];
                $bank['create_time'] = TIME_UTC;
                $bank['addip'] = get_client_ip();
                $bank['status'] = 1;
                $bank['cunguan_tag'] = 1;
                $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bank", $bank, "INSERT");
                $root['status'] = 1;
                $root['jump'] = url('index','uc_money#bank');
                $root['info'] = '换卡成功';
                ajax_return($root);
            } else {
                $root['status'] = 0;
                $root['info'] = $result['res']['respHeader']['respMsg'];
                ajax_return($root);
            }
        } else {
            $root['status'] = 0;
            $root['info'] = '请先登录';
            ajax_return($root);
        }
    }


    //解绑--第一步:验证交易密码
    function bank_paypassword()
    {
        $user = $GLOBALS['user_info'];
        if ($user['id']) {
            /* $backurl = "http://" . $_SERVER['HTTP_HOST'] . "/index.php?ctl=depository_addbank&act=delbank";
            list($msec, $sec) = explode(' ', microtime());
            $signtime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
            $signature = $signtime . "|100";
            $Publics = new Publics();
            $data_content = $Publics->rsa_encrypt($signature);    //RSA加密
            $html = "<form action='https://p2.heepay.com/passWord/PASSWORDVERIFY' method='get' target='_self' id='form1'>
                        <input type='hidden' name='systemCode' value='JCT'>
                        <input type='hidden' name='userId' value='" . $user['id'] . "'>
                        <input type='hidden' name='backURL' value='" . $backurl . "'>
                        <input type='hidden' name='signTime' value='" . $signtime . "'>
                        <input type='hidden' name='signature' value='" . $data_content . "'>
                        <input type='hidden' name='businessSeqNo' value='JCT" . TIME_UTC . "'>
                     </form>
                     <script>
                        document.getElementById('form1').submit();
                     </script>";
            echo $html;
            die; */
			$Publics = new Publics();
            $seqno = $Publics->seqno();
            $re = $Publics->verify_trans_password('uc_depository_addbank', "delbank", $user['id'], '4', $seqno,'_self');
            echo $re;die;
			
        } else {
            $root['status'] = 0;
            $root['info'] = '请先登录';
            ajax_return($root);
        }

    }

    function delbank(){
        $user = $GLOBALS['user_info'];
        if ($user['id'] > 0) {
            if (!$user['accno']) {
                $root['status'] = 0;
                $root['info'] = '请先开户';
                ajax_return($root);
            }

            $bank_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "user_bank where user_id = " . $user['id']." and status=1 and cunguan_tag=1 order by id desc limit 1");
            if(empty($bank_info['bankcard'])){
                $root['status'] = 0;
                $root['info'] = '请先绑卡';
                ajax_return($root);
            }
            $user_msg['user_id'] = $user['id'];
            $user_msg['real_name'] = $user['real_name'];;
            $user_msg['idno'] = $user['idno'];
            $user_msg['mobile'] = $user['mobile'];
            $user_msg['bank_id'] = strim($bank_info['bank_id']); //所属银行ID
            $user_msg['bankcard'] = str_replace(' ','',strim($bank_info['bankcard'])); //银行卡号
            //$user_msg['bank_mobile'] = strim($_REQUEST['mobile']); //银行预留手机号
            $user_msg['bank_mobile'] = $bank_info['bank_mobile']; //银行预留手机号
            $user_msg['businessSeqNo'] = strim($_REQUEST['businessSeqNo']); //流水号
            //$user_msg['oldbankcard'] = $bank_info['bankcard'];
            $user_msg['bankcard'] = $bank_info['bankcard'];
            $user_msg['dep_account'] = $user['accno'];

            $Register = new Register();
            $result = $Register->register1($user_msg, 'B02');
            /* $data['orderno'] = strim($_REQUEST['businessSeqNo']);
            $data['user_id'] = $user['id'];
            $data['accNo'] = $user['accno']; */
			/* $pub = new Publics();
			$result['outBody']['secBankaccNo'] = $pub->decrypt($result['outBody']['secBankaccNo']);
            $data['secBankaccNo'] = $result['outBody']['secBankaccNo'];
            $data['from_con'] = json_encode($result['map']);
            $data['back_con'] = json_encode($result,JSON_UNESCAPED_UNICODE);
            $data['type'] = "B02";
            $data['add_time'] = TIME_UTC;
            $data['date_time'] = date('Y-m-d H:i:s');
            $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "INSERT"); */
            if ($result['res']['respHeader']['respCode'] == 'P2P0000') {
                /*$GLOBALS['db']->query("update ".DB_PREFIX."user_bank set status=0 where id=".$bank_info['id']);
                 $root['status'] = 1;
                $root['jump'] = url('index','uc_money#bank');
                $root['info'] = '解绑成功';
                ajax_return($root); */
				app_redirect(url('index','uc_depository_addbank#cg_delbank'));
            } else {
                /* $root['status'] = 0;
                $root['info'] = $result['respHeader']['respMsg'];
                ajax_return($root); */
				app_redirect(url('index','uc_depository_addbank#cg_delbank',array('seqno'=>$result['seqno'])));
            }
        } else {
            /* $root['status'] = 0;
            $root['info'] = '请先登录';
            ajax_return($root); */
			app_redirect(url('index','user#login'));
        }
    }
	//绑卡回调
	function back_bank_info(){
		$flag = $_GET['flag']; 
		$seqno = strim($_GET['businessSeqNo']);
		if($flag==1&&$seqno){
			$deco = $GLOBALS['db']->getRow("select id,user_id,bankcard from ".DB_PREFIX."decository where seqno='".$seqno."' and  type='B01'");
			if(!$deco){
				if(isMobile()){
					showErr("请稍后重试",0,url('index','uc_depository_addbank#cg_bank_error'));
				}else{
					showErr("请稍后重试",0,url('index','uc_money#bank'));
				}
				
			}
			// 绑卡成功奖励成长值
			require_once APP_ROOT_PATH."system/user_level/Level.php";
			$level=new Level();
			$level->get_grow_point(3);
			$id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_bank where bankcard='".$deco['bankcard']."' and user_id=".$deco['user_id']." and cunguan_tag=1 order by id desc limit 1");
			$GLOBALS['db']->query("update ".DB_PREFIX."user_bank set status=1 where id =$id and bankcard='".$deco['bankcard']."' and user_id=".$deco['user_id']." and cunguan_tag=1");
			addsource(0,0,$deco['id'],2,1);
			if(isMobile()){
				showSuccess("绑卡成功",0,url('index','uc_depository_addbank#cg_bank_success'));
			}else{
				showSuccess("绑卡成功",0,url('index','uc_money#bank'));
			}
			
		}else{
			if(isMobile()){
				showErr("请稍后重试",0,url('index','uc_depository_addbank#cg_bank_error'));
			}else{
				showErr("请稍后重试",0,url('index','uc_money#bank'));
			}
		}
	}
	function cg_delbank(){
		$seqno = strim($_GET['seqno']);
		if($seqno){
			$datas = $GLOBALS['db']->getRow("select back_con,seqno from ".DB_PREFIX."decository where seqno ='$seqno' and type='B02'");
			if($datas){
				$res = json_decode($datas['back_con'],true);
				$msg = $res['respHeader']['respMsg'];
			}
		}
		
        $jump = machineInfo();
        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->assign("ajax_return",es_cookie::get("jump_url_depository"));
        $url = WAP_SITE_DOMAIN."/index.php?ctl=uc_money&act=incharge";
        $GLOBALS['tmpl']->assign("url",$url);
        $GLOBALS['tmpl']->assign("cate_title","解绑银行卡");
        $GLOBALS['tmpl']->assign("msg",$msg);
        $GLOBALS['tmpl']->assign("inc_file","inc/uc/cg_delbank.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }

}
?>