<?php


//修改绑定手机号 无法验证姓名身份证认证接口

class identity_verify{

    function index(){
        $realname = strim(base64_decode($GLOBALS['request']['realname'])); //真实姓名
        $idno = strim(base64_decode($GLOBALS['request']['idno'])); //身份号码
        $root = get_baseroot();
        $user = $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();

        if($user['id']>0){
            if(empty($realname) || empty($idno)){
                $root['response_code'] = 0;
                $root['show_err'] = '真实姓名或身份证号码不能为空';
                output($root);
            }
            if($user['user_type']=='1'){
            	$root['response_code'] = 0;
            	$root['show_err'] = '企业用户暂不可使用';
            	output($root);
            }
            //查询用户是否已经实名认证
            $info = $GLOBALS['db']->getRow("SELECT AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno,AES_DECRYPT(real_name_encrypt,'".AES_DECRYPT_KEY."') as real_name FROM ".DB_PREFIX."user WHERE id=".$user['id']);
            if($info['idno']==$idno && $info['real_name'] == $realname){
                $root['response_code'] = 1;
                $root['show_err'] = '操作成功';
                output($root);
            }else{
                $root['response_code'] = 0;
                
                $root['show_err'] = '身份信息与当前账户不一致';
                output($root);
            }

        }else{
            $root['response_code'] = 0;
            $root['show_err'] = "未登录";
            output($root);
        }
    }




}


?>