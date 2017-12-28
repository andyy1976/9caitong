<?php
class add_information
{
	function index($data) {
		$root = get_baseroot ();
		$user = $GLOBALS ['user_info']; // user_check($email,$pwd);
		$root ['session_id'] = es_session::id ();
		$profession =  base64_decode ( $GLOBALS ['request'] ['profession'] ) ;
		$degree =  base64_decode ( $GLOBALS ['request'] ['degree'] ) ;
		$sex =base64_decode ( $GLOBALS ['request'] ['sex']);
		$user_id  = intval($user['id']);
		$idcard = $user ['idno'] ? $user ['idno'] : "";
		$serror='';
		if (! empty ( $user ['idno'] )) {
			$sex = substr ( $idcard, (strlen ( $idcard ) == 15 ? - 2 : - 1), 1 ) % 2 ? '1' : '2'; // 1为男 2为女                                                                           // $root("gender")="男";
		}
		if ($user_id > 0) {
			$exist_userid=$GLOBALS ['db']->getRow("select * from ".DB_PREFIX."address where user_id=".$user_id);//.DB_PREFIX."address where user_id=".$user_id
			if($exist_userid){
				$sql = "update ".DB_PREFIX."address set profession=" . "'$profession'" . ", graduation=" . "'$degree'". ", sex=" ."'$sex'". " where user_id =".$user_id;
				$serror='更新成功';
			}else{
				$sql = "INSERT INTO ".DB_PREFIX."address ( profession,graduation,sex,user_id) VALUES ("."'$profession'".","."'$degree'".",".$sex.",".$user_id.")";
				$serror='添加成功';
			}
// 			$GLOBALS ['db']->startTrans (); // 开始事务
			if(empty($profession)||empty($degree)||empty($sex)){
				$root ['show_err'] = '请填写完整数据';
				$root ['response_code'] = 0;
				output ( $root );
			}
			$s = $GLOBALS ['db']->query ($sql);
			if (!$s) {
// 				$GLOBALS ['db']->rollback ();
				$root['sql']="";
				$root ['response_code'] = 0;
				$root ['show_err'] = '添加失败';
				output ( $root );
			} else {
// 				$GLOBALS['db']->commit();
				$root ['response_code'] = 1;
				$root ['show_err'] = $serror;
				$root ['succeed'] = "";
				output ( $root );
			}
		} else {
			$root ['user_login_status'] = 0;
			$root ['response_code'] = 0;
			$root ['show_err'] = '请先登录';
			output ( $root );
		}
	}
}

?>