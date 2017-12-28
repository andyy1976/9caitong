/*
 * @date 2016-08-28
 * @description 登录页
 * @version V1.0.0
 */
define('new/js/page/page_reg_step1', ['new/css/layer.css', "$", 'layer', 'new/js/mod/util/FW_Password', 'new/js/mod/util/minLength', 'new/js/mod/util/verify_phone'], function (require, exports, module) {
	require('new/css/layer.css');
	var $=require("$");
	var layer=require('layer');
	var FW_Password=require('new/js/mod/util/FW_Password').FW_Password;
	var minLength=require('new/js/mod/util/minLength').minLength;
	var verify_phone=require('new/js/mod/util/verify_phone').verify_phone;
	$("#get_regsms_code").click(function () {
		//验证手机
		var phone=$.trim($("#user_reg_phone").val());
		if (!phone) {
			layer.tips("请输入手机号","#user_reg_phone");
			return false;
		} else{
			if (!verify_phone(phone)) {
				layer.tips("请输入正确的手机格式！","#user_reg_phone");
				return false;
			}
		}
		
		//发送手机验证码
		var query = new Object();
		var ajax_url=$("#page_reg_form").attr("ajax_url");
		query.phone=phone;
		$.ajax({
			type:"post",
			url:ajax_url,
			dataType:"json",
			data:query,
			async:true,
			success:function (obj) {
				if (obj.status) {
					layer.alert(obj.info,{icon:6,title:"成功"},function () {
						$("#sms_code").focus();
					});					
				} else{
					layer.alert(obj.info,{icon:5,title:"提示"});
					return false;
				}
			},
			error:function (obj) {
				layer.alert("链接失败！",{icon:2,title:"失败"});
				return false;
			}
		});
	});
	$("#page_reg_submit").click(function () {
		//验证手机号
		var phone=$.trim($("#user_reg_phone").val());
		if (!phone) {
			layer.tips("请输入手机号","#user_reg_phone");
			return false;
		} else{
			if (!verify_phone(phone)) {
				layer.tips("请输入正确的手机格式！","#user_reg_phone");
				return false;
			}
		}
		
		//验证密码
		var pwd=$.trim($("#user_reg_pwd").val());
		var reg_pwd=/^[a-zA-z0-9]{6,}$/;  
		var regs_pwd=/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/;
		if(reg_pwd.test(pwd)){	
			if(!regs_pwd.test(pwd)){
				layer.tips("安全等级低，请用数字+字母！","#user_reg_pwd");
				return false;
			}
		}
		else{
			layer.tips("长度在6~16之间，只能包含字符、数字和下划线！",'#user_reg_pwd');
			return false;
		}
		if(!minLength($("#user_reg_pwd").val(),6,false))
		{
			formError($("#user_reg_pwd"));	
			return false;
		}
		
		//验证确认的密码
		var v_pwd=$.trim($("#user_reg_verify_pwd").val());
		if (pwd!=v_pwd) {
			layer.tips("两次密码输入不一致！","#user_reg_verify_pwd");
			return false;
		}
		
		//验证手机验证码
		var sms_code=$.trim($("#sms_code").val());
		if (sms_code) {
			if (sms_code.length!=6) {
				layer.tips("手机验证码有误！","#sms_code");
			return false;
			}
		}else{
			layer.tips("请输入手机验证码！","#sms_code");
			return false;
		}
		//是否同意注册协议
		if (!$("#agreement").attr("checked")) {
			layer.tips("请同意注册协议！","#agreement",{tips: 1});
			return false;
		}
		var query_reg=new Object();
		query_reg.phone=phone;
		query_reg.pwd=pwd;
		query_reg.sms_code=sms_code;
		if ($("#referer_num").val()) {
			query_reg.referer=$.trim($("#referer_num").val());
		}
		var ajax_url=$("#page_reg_form").attr("ajax_url");
		$.ajax({
			type:"post",
			url:ajax_url,
			dataType:"json",
			data:query_reg,
			async:true,
			success:function (obj) {
				if (obj.status) {
					layer.alert(obj.info,{icon:6,title:"成功"},function () {
						window.location.href="{url x="index" r="uc_depository_account#account_success"}";
					});					
				} else{
					layer.alert(obj.info,{icon:5,title:"提示"});
					return false;
				}
			},
			error:function () {
				layer.alert("链接失败！",{icon:2,title:"失败"});
				return false;
			}
		});
	});
});