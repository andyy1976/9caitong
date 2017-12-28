/*
 * @date 2016-09-06
 * @description 会员中心--安全信息--修改手机号
 * @version V1.0.0
 */
define('new/js/mod/reset_login.pwd', ['new/css/layer.css', "$", 'layer', 'new/js/mod/util/pwd_get_validate_code'], function (require, exports, module) {
	require('new/css/layer.css');
	var $=require("$");
	var layer=require('layer');
	var pwd_get_validate_code=require('new/js/mod/util/pwd_get_validate_code').pwd_get_validate_code;
	//验证原手机号码
	$(document).ready(function () {
		alert("aa")
	});
	$("#pwd_get_validate_code").click(function () {
		var ajax_url=$(this).attr("ajax_url");
		pwd_get_validate_code($(this),0,ajax_url);
	});
	
	$("#ModifyPhone").unbind('submit');
	$("#ModifyPhone").bind("submit",function () {
		var query = new Object();
		query.ctl = 'ajax';
		query.act = 'check_verify_code';
		query.old_mobile = $("#old_phone").val();
		query.oldverify = $("#oldvalidateCode").val();
		query.ajax = 1;
		$.ajax({
			url : "/index.php",
			data:query,
			type:"post",
			dataType:"json",
			success: function(obj){
				if(obj.status)
				{			
					layer.alert(obj.info,function(){
						//window.location.reload();
					});
				}
				else{
					layer.alert(obj.info);
				}
					
			},
			error:function(ajaxobj)
			{
				/*if(ajaxobj.responseText!='')
				alert(ajaxobj.responseText);*/
			}
		});
	});
});