define("new/js/mod/reset_login_pwd",["new/css/layer.css","$","layer","new/js/mod/util/pwd_get_validate_code","new/js/mod/util/minLength","new/js/mod/util/lang"],function(t){t("new/css/layer.css");var s=t("$"),e=t("layer"),i=t("new/js/mod/util/pwd_get_validate_code").pwd_get_validate_code,a=t("new/js/mod/util/minLength").minLength,n=t("new/js/mod/util/lang").lang;s("#r_pwd_get_validate_code").click(function(){var t=s(this).attr("ajax_url");i(s(this),0,t,"#Verifycode")}),s("#sub_btn_reset_pwd").click(function(){var t=s("#validateCode").val();if(""==t)return e.tips(n.EMPTY_PHONE_CODE,"#validateCode",{tips:1}),s("#validateCode").focus(),!1;if(!a(t,6,!1))return e.tips(n.ERROR_PHONE_CODE,"#validateCode",{tips:1}),s("#validateCode").focus(),!1;var i=s("#settings-password").val(),o=/^[a-zA-z0-9]{6,}$/,r=/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/;if(0==i.length)return e.tips(n.EMPTY_NEW_LOGIN_PWD,"#settings-password"),s("#settings-password").focus(),!1;if(!o.test(i))return e.tips(n.ERROR_PWD,"#settings-password"),s("#settings-password").focus(),!1;if(!r.test(i))return e.tips(n.ERROR_PWD,"#settings-password"),s("#settings-password").focus(),!1;if(s("#settings-password-confirm").val()!=s("#settings-password").val())return e.tips(n.NOTSAME_PWD,"#settings-password-confirm",{tips:1}),s("#settings-password-confirm").focus(),!1;e.load(1);var l=s("#reset_login_psw").attr("ajax_url"),d=new Object;d.is_ajax=1,d.pw=s.trim(s("#settings-password").val()),d.sms_code=s.trim(s("#validateCode").val()),d.user_mobile=s.trim(s("#user_mobile").val()),d.sta=1,s.ajax({url:l,data:d,type:"post",dataType:"json",success:function(t){e.closeAll("loading"),1==t.status?e.alert(t.info,{icon:6,title:"成功",closeBtn:0},function(){t.jump?window.location.href=t.jump:window.location.reload()}):(s("#Jverify_img").attr("src","/verify.php?w=89&h=44&rand="+Math.random()),e.alert(t.info,{icon:5,title:"提示",closeBtn:0}))},error:function(){e.closeAll("loading"),e.alert(n.REQUEST_DATA_FAILED,{icon:2,title:"失败",closeBtn:0})}})})});