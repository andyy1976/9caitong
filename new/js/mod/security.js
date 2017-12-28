/*
 * @date 2016-09-05
 * @description 会员中心--安全信息
 * @version V1.0.0
 */
define('new/js/mod/security', ['new/css/layer.css', "$", 'layer'], function (require, exports, module) {
	require('new/css/layer.css');
	var $=require("$");
	var layer=require('layer');
	// 修改手机号
	$("#ModifyPhone").click();
	$("#ModifyPhone").click(function () {
		var ajaxurl = $(this).attr("ajaxurl");
		$.ajax({
			url:ajaxurl,
			data:"&is_ajax=1",
			type:"post",
			dataType:"text",
			success:function(ajaxobj){
				//$("#setting-mobile-box").html(ajaxobj);
				//$("#setting-mobile-box").slideDown();
				//init_ui_textbox();
				layer.open({
					type: 1,
					area: ['500px', '500px'],
					fix: true, 
					maxmin: false,
					title: "修改手机号",
					content:ajaxobj
				})
			}
		});
	});
});