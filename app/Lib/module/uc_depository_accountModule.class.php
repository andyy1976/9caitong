<?php

require APP_ROOT_PATH."system/utils/Depository/Require.php";

class uc_depository_accountModule extends SiteBaseModule{

    function index()
    {
        $user =  $GLOBALS['user_info'];
        if($user['id']>0){
            if($user['accno']){
                app_redirect(url('index','uc_center#index'));
            }else if($user['user_type']=='1'){
            	app_redirect(url('index','user#company_steptwo'));
            }
            else{
                $vo = $GLOBALS['db']->getRow("select real_name,idno,mobile from " . DB_PREFIX . "user where id = " . $user['id']);
                $GLOBALS['tmpl']->assign("vo",$vo);
                $GLOBALS['tmpl']->assign("cate_title","开户");
                $GLOBALS['tmpl']->assign("inc_file","inc/uc/dep_register_index.html");
                $GLOBALS['tmpl']->display("page/uc.html");
            }

        }else{
            app_redirect(url('index','user#login'));
        }

    }

    /**
     * wap开户成功页
     * @author:zhuxiang
     */
    function account_success()
    {
        /*移动端交互处理*/
        $jump = machineInfo();
        $accno = $GLOBALS['user_info']['accno'];
        $GLOBALS['tmpl']->assign('accno',$accno);
        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->assign("ajax_return",es_cookie::get("jump_url_depository"));
        $GLOBALS['tmpl']->assign("cate_title","开户");
        $GLOBALS['tmpl']->assign("inc_file","inc/uc/cg_account_success.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }


    //开户
    function register_index(){

        $user =  $GLOBALS['user_info'];
        if($user['id']>0){
            if($user['accno']){
                $root['status'] = 0;
                $root['info'] = '已经开过户';
                ajax_return($root);
            }
            $real_name = strim($_REQUEST['real_name']); //真实姓名
			$idnos =strim($_REQUEST['IDcard']);
            $idno = strtoupper(strim($_REQUEST['IDcard'])); //身份证号码
            if(empty($real_name) || empty($idno)){
                $root['status'] = 0;
                $root['info'] = '不能为空';
                ajax_return($root);
            }
            if (!empty($user['idno'])){
                if ( $user['idno'] != $idnos) {
                    $root['status'] = 0;
                    $root['info'] = '身份信息不一致';
                    ajax_return($root);
                }
            }else{
                $uinfo = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where idno='".$idnos."'");
                if($uinfo>0){
                    $root['status'] = 0;
                    $root['info'] = '身份信息被占用';
                    ajax_return($root);
                }
            }

            $user_msg['user_id'] = $user['id'];
            $user_msg['real_name'] = $real_name;
            $user_msg['idno'] = $idno;
            $user_msg['mobile'] = $user['mobile'];
            //var_dump($user_msg);die;
            $Register = new Register();
            $res = $Register->register1($user_msg, 'U01');
			$pub = new Publics();
			$res['res']['outBody']['accNo']=$pub->decrypt($res['res']['outBody']['accNo']);
            $data['seqno'] = $res['map']['inBody']['businessSeqNo'];
            $data['user_id'] = $user['id'];
            $data['accNo'] = $res['res']['outBody']['accNo'];
            $data['secBankaccNo'] = $res['res']['outBody']['secBankaccNo'];
            $data['form_con'] = json_encode($res['map']);
            $data['back_con'] = json_encode($res['res'],JSON_UNESCAPED_UNICODE);
            $data['type'] = 'U01';
            $data['add_time'] = TIME_UTC;
            $data['date_time'] = date('Y-m-d H:i:s');
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$data,"INSERT");
            if($res['res']['respHeader']['respCode']=='P2P0000' || $res['callback_return_data']['respHeader']['respCode']=='P2P0000'){
                if(empty($res['res']['outBody']['accNo']) && !empty($res['callback_return_data']['outBody']['accountNo'])){
                    $res['res']['outBody']['accNo'] = $res['callback_return_data']['outBody']['accountNo'];
                }
                $sql = "UPDATE ".DB_PREFIX."user SET accno='".$res['res']['outBody']['accNo']."',cunguan_tag=1 WHERE id=".$user['id'];
                $GLOBALS['db']->query($sql);
                $GLOBALS['user_info']['accno'] = $res['res']['outBody']['accNo'];
                if (empty($user_info['idno'])){
                    $GLOBALS['user_info']['real_name'] = $real_name;
                    $GLOBALS['user_info']['idno'] = $idno;
                    $dataes['real_name'] = $real_name;
                    $dataes['idno'] = $idno;
                    $dataes['idcardpassed']=1;
                    $dataes['idcardpassed_time'] = TIME_UTC;
                    $dataes["real_name_encrypt"] = " AES_ENCRYPT('".$real_name."','".AES_DECRYPT_KEY."') ";
                    $dataes["idno_encrypt"] = " AES_ENCRYPT('".$idno."','".AES_DECRYPT_KEY."') ";
                    $GLOBALS['db']->autoExecute(DB_PREFIX."user",$dataes,"UPDATE","id=".$user['id']);
                    }
                // 开通存管奖励成长值
                require_once APP_ROOT_PATH."system/user_level/Level.php";
                $level=new Level();
                $level->get_grow_point(2);

                $root['status'] = 1;
                $root['jump'] = url("index","uc_depository_account#account_success");
                $root['info'] = '开户成功';
				addsource(0,0,$user['id'],4,1);
                ajax_return($root);
            }else{
                $root['status'] = 0;
                $root['info'] = $res['respHeader']['respMsg']?$res['respHeader']['respMsg']:'开户失败';
                ajax_return($root);
            }
        }else{
            $root['status'] = 0;
            $root['info'] = '请先登录';
            ajax_return($root);
        }
    }

    //企业用户开户
    public function company_register(){
        $user =  $GLOBALS['user_info'];
        
        if(!$user['id'])
        {
            $root['status'] = 0;
            $root['info'] = '请先登录';
            ajax_return($root);
        }

        $user_data = $_POST;
        if(!$user_data){
             app_redirect("404.html");
             exit();
        }
        foreach($user_data as $k=>$v)
        {
            $user_data[$k] = htmlspecialchars(addslashes($v));
        }
        
        // $updata['customerId'] ='1123667';//会员编号
        // $updata['companyName'] = '企业名称3';//企业名称
        // $updata['entType'] = '企业';//主体类型
        // $updata['dateOfEst'] = '20170829' ;//成立日期
        // $updata['corpacc'] = '玖承资产管理有限公司';//对公户账号
        // $updata['corpAccBankNo'] ='313671000017';//对公户开户行号
        // $updata['corpAccBankNm'] ='农业银行';//对公户开户行名称
        // $updata['uniSocCreCode'] = '12';//统一社会信用代码
        // $updata['uniSocCreDir'] ='统一社会信用地址';//统一社会信用地址
        // $updata['bizLicDomicile'] ='营业执照住所';//营业执照住所
        // $updata['username'] ='用户名';//用户名
        // $username = $GLOBALS['db']->getOne("SELECT count(id) FROM ".DB_PREFIX."company_reginfo WHERE username ='".$user_data['company_name']."'");
        // if($username>=1){

        //     $root['status'] = 0;
        //     $root['info'] = '该企业已被注册';
        //     ajax_return($root);
        // }
        
        $user_data['start_data'] =  strtotime($user_data['start_data']);
        $user_data['start_data'] = date("Ymd",$user_data['start_data']);
        $updata['customerId'] =$user['id'];//会员编号
        $updata['companyName'] = $user_data['company_name'];//企业名称
        $updata['entType'] = $user_data['subject_type'];//主体类型
        $updata['dateOfEst'] = $user_data['start_data'] ;//成立日期
        $updata['corpacc'] = $user_data['P_account'];//对公户账号
        $updata['corpAccBankNo'] =$user_data['P_account_bank_num'];//对公户开户行号
        $updata['corpAccBankNm'] =$user_data['P_account_bank'];//对公户开户行名称
        $updata['uniSocCreCode'] = $user_data['credit_code'];//统一社会信用代码
        $updata['uniSocCreDir'] =$user_data['credit_code'];//统一社会信用地址
        $updata['bizLicDomicile'] =$user_data['license_add'];//营业执照住所
        $updata['username'] =$user_data['corporate'];//用户名
        $updata['phone'] =$user['mobile'];//用户名
        $Register = new Register();
        $res = $Register->register2($updata, 'U04','01','09');

        if($res['res']['respHeader']['respCode']=='P2P0000')
        {
            $insertData['user_id']  = $updata['customerId'];
            $insertData['username'] = $updata['companyName'];
            $insertData['businessSeqNo'] = $res['map']['inBody']['businessSeqNo'];
            $insertData['busiTradeType'] = 'U04';
            $insertData['ctype'] = '01';
            $insertData['crole'] = '09';
            $insertData['companyName'] = $updata['companyName'];
            $insertData['entType'] = $updata['entType'];
            $insertData['dateOfEst'] = $updata['dateOfEst'];
            $insertData['bizLicDomicile'] = $updata['bizLicDomicile'];
            $insertData['corpacc'] = $updata['corpacc'];
            $insertData['corpAccBankNo'] = $updata['corpAccBankNo'];
            $insertData['corpAccBankNm'] = $updata['corpAccBankNm'];
            $insertData['bindFlag'] = '00';
            $insertData['uniSocCreCode'] = $updata['uniSocCreCode'];
            $insertData['uniSocCreDir'] = $updata['uniSocCreDir'];
            $insertData['accNo'] = $res['res']['outBody']['accNo'];
            $insertData['create_time'] = TIME_UTC;
            $insertData['form_con'] = json_encode($res['map']);
            $insertData['back_con'] = json_encode($res['res']);

            $GLOBALS['db']->autoExecute(DB_PREFIX."company_reginfo",$insertData,"INSERT");
            $insert_id1 = $GLOBALS['db']->insert_id();
            if($insert_id1){
                $datas['accno'] = $res['res']['outBody']['accNo'];
                $datas['real_name'] = $insertData['username'];
                $real_name= $datas['real_name'];
                $datas["real_name_encrypt"] = " AES_ENCRYPT('".$real_name."','".AES_DECRYPT_KEY."') ";
                $datas['cunguan_tag'] =1;
                $datas['cunguan_pwd'] =1;
                $results = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$datas,"UPDATE","id=".$updata['customerId']);
                
                if($results){
                    $root['status'] = 1;
                    $root['jump'] = url("index","uc_center#index");
                    $root['info'] = '开户成功';
                    ajax_return($root);
                }
            }

            $root['status'] = 0;
            $root['info'] = '开户失败';
            ajax_return($root);
            
        }else{

            $root['status'] = 0;
            $root['info'] = $res['res']['respHeader']['respMsg']?$res['res']['respHeader']['respMsg']:'开户失败';
            $insertData['form_con'] = json_encode($res['map']);
            $insertData['back_con'] = json_encode($res['res']);
            $GLOBALS['db']->autoExecute(DB_PREFIX."company_reginfo",$insertData,"INSERT");
            ajax_return($root);
        }
        

        //print_r($res);die;

    }
}
?>