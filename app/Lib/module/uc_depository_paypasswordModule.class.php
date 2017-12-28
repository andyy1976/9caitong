<?php

require APP_ROOT_PATH."system/utils/Depository/Require.php";

class uc_depository_paypasswordModule extends SiteBaseModule{

    //设置存管交易密码
    function setpaypassword(){

        $user =  $GLOBALS['user_info'];
        if($user['id']>0){
            $cunguan_info = $GLOBALS['db']->getRow("select cunguan_pwd,accno from ".DB_PREFIX."user where id=".$user['id']);
            if($cunguan_info['accno']){
                if($cunguan_info['cunguan_pwd']){
                    app_redirect(url('index','uc_account#security'));
                }else{
                    $Publics = new Publics();
                    $seqno = $Publics->seqno();
                    $Publics->verify_trans_password('uc_depository_paypassword','back_paypassword',$user['id'],'1',$seqno,'_self');
                }
            }else{
                app_redirect(url('index','uc_depository_account#index'));
            }
        }else{
            app_redirect(url('index','user#login'));
        }
    }

    //设置存管交易密码
    function pc_setpaypassword(){

        $user =  $GLOBALS['user_info'];
        if($user['id']>0){
            $cunguan_info = $GLOBALS['db']->getRow("select cunguan_pwd,accno from ".DB_PREFIX."user where id=".$user['id']);
            if($cunguan_info['accno']){
                if($cunguan_info['cunguan_pwd']){
                    app_redirect(url('index','uc_account#security'));
                }else{
                    $Publics = new Publics();
                    $seqno = $Publics->seqno();
                    $Publics->verify_trans_password('uc_depository_paypassword','back_paypassword1',$user['id'],'1',$seqno,'_self');
                }
            }else{
                app_redirect(url('index','uc_depository_account#index'));
            }
        }else{
            app_redirect(url('index','user#login'));
        }
    }

    //修改存管交易密码
    function changepaypassword(){

        $user =  $GLOBALS['user_info'];
        if($user['id']>0){
            $paypwd = $GLOBALS['db']->getOne("select cunguan_pwd from ".DB_PREFIX."user where id=".$user['id']);
            if(!$paypwd){
                app_redirect(url('index','uc_account#security'));
            }else{
                $Publics = new Publics();
                $seqno = $Publics->seqno();
                $Publics->verify_trans_password('uc_depository_paypassword','back_paypassword',$user['id'],'2',$seqno,'_self');
            }


        }else{
            app_redirect(url('index','user#login'));
        }
    }

    //修改存管交易密码
    function pc_changepaypassword(){

        $user =  $GLOBALS['user_info'];
        if($user['id']>0){
            $paypwd = $GLOBALS['db']->getOne("select cunguan_pwd from ".DB_PREFIX."user where id=".$user['id']);
            if(!$paypwd){
                app_redirect(url('index','uc_account#security'));
            }else{
                $Publics = new Publics();
                $seqno = $Publics->seqno();
                $Publics->verify_trans_password('uc_depository_paypassword','back_paypassword1',$user['id'],'2',$seqno,'_self');
            }


        }else{
            app_redirect(url('index','user#login'));
        }
    }

    //重置存管交易密码
    function resetpaypassword(){

        $user =  $GLOBALS['user_info'];
        if($user['id']>0){
            $paypwd = $GLOBALS['db']->getOne("select cunguan_pwd from ".DB_PREFIX."user where id=".$user['id']);
            if(!$paypwd){
                app_redirect(url('index','uc_account#security'));
            }else{
                $Publics = new Publics();
                $seqno = $Publics->seqno();
                $Publics->verify_trans_password('uc_depository_paypassword','back_paypassword',$user['id'],'3',$seqno,'_self');
            }


        }else{
            app_redirect(url('index','user#login'));
        }
    }

    //重置存管交易密码
    function pc_resetpaypassword(){

        $user =  $GLOBALS['user_info'];
        if($user['id']>0){
            $paypwd = $GLOBALS['db']->getOne("select cunguan_pwd from ".DB_PREFIX."user where id=".$user['id']);
            if(!$paypwd){
                app_redirect(url('index','uc_account#security'));
            }else{
                $Publics = new Publics();
                $seqno = $Publics->seqno();
                $Publics->verify_trans_password('uc_depository_paypassword','back_paypassword1',$user['id'],'3',$seqno,'_self');
            }


        }else{
            app_redirect(url('index','user#login'));
        }
    }

    //支付密码回调
    function back_paypassword1(){

        $user_info['userId'] = $_GET['userId'];
        $user_info['flag'] = $_GET['flag'];
        $user_info['signTime'] = $_GET['signTime'];
        $user_info['signature'] = $_GET['signature'];
        $user_info['businessSeqNo'] = $_GET['businessSeqNo'];

        $map=array('back_con' =>json_encode($user_info),);
        $a=$GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $map, "UPDATE", "seqno='" . $_GET['businessSeqNo']. "'");      
        if($user_info['flag']==1){
            $is_get=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_grow_point where user_id=".$user_info['userId']." and task_type=4");
                if(!$is_get){
                    // 设置存管交易密码奖励成长值
                    require_once APP_ROOT_PATH."system/user_level/Level.php";
                    $level=new Level();
                    $level->get_grow_point(4);
                }        
            $trans_progress = array('cunguan_pwd' =>1,);
            $recharge = $GLOBALS['db']->autoExecute(DB_PREFIX . "user", $trans_progress, "UPDATE", "id=" . $user_info['userId']. " and  cunguan_tag=1");             
            app_redirect(url("index","uc_account#security"));
        }else{ 
            app_redirect(url("index","uc_account#security"));
        }
    }

    //支付密码回调
    function back_paypassword(){
        $user_info['userId'] = $_GET['userId'];
        $user_info['flag'] = $_GET['flag'];
        $user_info['signTime'] = $_GET['signTime'];
        $user_info['signature'] = $_GET['signature'];
        $user_info['businessSeqNo'] = $_GET['businessSeqNo'];
        $map['back_con'] = json_encode($user_info);
        if($_GET['businessSeqNo']){
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$map,"UPDATE","seqno='".$_GET['businessSeqNo']."'");
        }else{
            $GLOBALS['db']->autoExecute(DB_PREFIX."decository",$map);
        }

        if($user_info['flag']==1){
            $is_get=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_grow_point where user_id=".$user_info['userId']." and task_type=4");
            if(!$is_get){
                // 设置存管交易密码奖励成长值
                require_once APP_ROOT_PATH."system/user_level/Level.php";
                $level=new Level();
                $level->get_grow_point(4);
            }
            $types = $GLOBALS['db']->getOne("select `type` from ".DB_PREFIX."decository where seqno='".$_GET['businessSeqNo']."'");
            $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "user SET cunguan_pwd=1 WHERE id=" . $user_info['userId']);

            if($types=='J01'){
                app_redirect(url("index","uc_depository_paypassword#cg_password_success"));
            }elseif($types=='J02' || $types=='J03'){
                app_redirect(url("index","uc_depository_paypassword#cg_change_password_success"));
            }else{
                app_redirect(url("index","uc_depository_paypassword#cg_change_password_success"));
            }

        }else{
            app_redirect(url("index","uc_account#security"));
        }

    }

    function cg_password_success(){
        /*移动端交互处理*/
        $jump = machineInfo();
        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->assign("ajax_return",es_cookie::get("jump_url_depository"));
        $GLOBALS['tmpl']->assign("cate_title","支付密码");
        $GLOBALS['tmpl']->assign("inc_file","inc/uc/cg_password_success.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }

    function cg_change_password_success(){
        /*移动端交互处理*/
        $jump = machineInfo();
        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->assign("ajax_return",es_cookie::get("jump_url_depository"));
        $GLOBALS['tmpl']->assign("cate_title","支付密码");
        $GLOBALS['tmpl']->assign("inc_file","inc/uc/cg_change_password_success.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }


}
?>