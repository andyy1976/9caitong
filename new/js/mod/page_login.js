/*
 * @date 2016-08-28
 * @description 登录页
 * @version V1.0.0
 */
define('new/js/mod/page_login', ['new/css/layer.css', "$", 'layer', 'new/js/mod/util/FW_Password', 'new/js/mod/util/minLength'], function (require, exports, module) {
	require('new/css/layer.css');
	var $=require("$");
	var layer=require('layer');
	var FW_Password=require('new/js/mod/util/FW_Password').FW_Password;
	var minLength=require('new/js/mod/util/minLength').minLength;
	$("#login_submit").click(function () {
		ajaxCheckLogin();
	});
	function ajaxCheckLogin(){	
		if($.trim($("#user_name").val()).length == 0)
		{
			layer.alert("手机号格式错误，请输入正确手机号！");
			$("#user_name").focus();
			return false;
		}

		if(!minLength($("#login-password").val(),4,false))
		{
			layer.alert("密码格式错误，请重新输入！");
			$("#login-password").focus();
			return false;
		}
		
		var ajaxurl = $("div#Iajax_login_form").attr("action");
		var query = new Object();
		//alert(__LOGIN_KEY)
		query.email = $("div#Iajax_login_form #user_name").val();
		query.user_pwd = FW_Password($("div#Iajax_login_form #login-password").val(),$("#LOGIN_KEY").val());
		
		if($("#Jverify").length > 0)
		query.verify = $("#Jverify").val();
		query.auto_login = $("div#Iajax_login_form #autologin").val();
		query.ajax = 1;

		$.ajax({ 
			url: ajaxurl,
			dataType: "json",
			data:query,
			type: "POST",
			success: function(ajaxobj){
				if(ajaxobj.status==0)
				{
					if($("#Jverify_img").length > 0)
						$("#Jverify_img").attr("src",'/verify.php?w=89&h=44&rand='+ Math.random());
					layer.alert(ajaxobj.info);
					$("#Jverify").focus();
				}
				else
				{
					//layer.alert("aa"+ajaxobj.data);
					//var integrate = $("<span id='integrate'>"+ajaxobj.data+"</span>");
					//$("body").append(integrate);														
					//close_pop();
					
					//update_user_tip();
					
					//$("#integrate").remove();
					if(ajaxobj.status==1){
						layer.alert(ajaxobj.info,function(){
							window.location.reload();
						});
					}
					else{
						layer.confirm(ajaxobj.info,function(){
							location.href = ajaxobj.jump1;
						},function(){
							window.location.reload();
						});
					}					
				}
			},
			error:function(ajaxobj)
			{
				//if(ajaxobj.responseText!='')
				//alert(ajaxobj.responseText);
			}
		});	
		
		return false;
	}
});