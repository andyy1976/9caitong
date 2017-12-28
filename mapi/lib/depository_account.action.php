<?php
require APP_ROOT_PATH."system/utils/Depository/Require.php";
class depository_account{

    public function index()
    {

        $root = get_baseroot();
        $user = $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        if ($user['id'] > 0) {

            $real_name = strim(base64_decode($GLOBALS['request']['real_name'])); //真实姓名
            $idno = strim(base64_decode($GLOBALS['request']['idno'])); //身份证号码
            $user_info = $GLOBALS['db']->getRow("select real_name,idno,mobile from " . DB_PREFIX . "user where id = " . $user['id']);
            if (!empty($user_info['idno'])) {
                if ($user_info['real_name'] != $real_name || $user_info['idno'] != $idno) {
                    $root['response_code'] = 0;
                    $root['show_err'] = '身份信息不一致';
                    output($root);
                }
            }
            $uinfo = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where idno=".$idno);
            if($uinfo>0){
                $root['response_code'] = 0;
                $root['show_err'] = '身份信息被占用';
                output($root);
            }
            if($user_info['accno']){
                $root['response_code'] = 0;
                $root['show_err'] = '已经开过户';
                output($root);
            }
            $user_msg['user_id'] = $user['id'];
            $user_msg['real_name'] = $real_name;
            $user_msg['idno'] = $idno;
            $Register = new Register();
            $res = $Register->register($user_msg, 'U01');
            if ($res['respHeader']['respCode'] == 'P2P0000' && !empty($res['outBody']['accNo'])) {
                $data['orderno'] = $res['liushui'];
                $data['user_id'] = $user['id'];
                $data['accNo'] = $res['outBody']['accNo'];
                $data['secBankaccNo'] = $res['outBody']['secBankaccNo'];
                $data['form_con'] = json_encode($user_msg);
                $data['back_con'] = json_encode($res);
                $data['type'] = 1;
                $GLOBALS['db']->autoExecute(DB_PREFIX . "decository", $data, "INSERT");
                $user_msg['dep_account'] = $res['outBody']['accNo'];
                $user_msg['addtime'] = time();
                $user_msg['updatetime'] = time();
                $GLOBALS['db']->autoExecute(DB_PREFIX . "dep_account", $user_msg, "INSERT", "");
                $id = $GLOBALS['db']->insert_id();
                if ($id > 0) {
                    $GLOBALS['db']->query("UPDATE ".DB_PREFIX."user SET accno='".$res['outBody']['accNo']."',cunguan_tag=1 WHERE id=".$user['id']);
                    $root['response_code'] = 1;
                    $root['show_err'] = '开户成功';
                } else {
                    $root['response_code'] = 0;
                    $root['show_err'] = '开户失败';
                }
                output($root);
            } else {
                $root['response_code'] = 0;
                $root['show_err'] = $res['respHeader']['respMsg'];
                output($root);
            }
        }else{
            $root['response_code'] = 0;
            $root['show_err'] = '请先登录';
            output($root);
        }
    }
}
?>
