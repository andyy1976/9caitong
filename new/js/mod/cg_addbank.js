define("new/js/mod/cg_addbank",["new/js/mod/util/verify","new/js/mod/util/minLength","new/js/mod/util/lang","new/js/mod/util/bankInput"],function(e,a){var t=e("new/js/mod/util/verify").verify_phone,i=e("new/js/mod/util/minLength").minLength,n=e("new/js/mod/util/lang").lang;e("new/js/mod/util/bankInput"),a.init_ui_textbox=function(){$(".ui-textbox[init!='init'],.ui-textarea[init!='init']").each(function(e,a){$(a).attr("init","init"),ui_textbox($(a))})},$(document).ready(function(){function a(){clearTimeout(r),s>0?(r=setTimeout(a,1e3),$("#get_bankMobile_code").val(s+"秒后重新获取"),$("#get_bankMobile_code").addClass("btn_disable"),s--):(o=!1,$("#get_bankMobile_code").removeClass("btn_disable"),$("#get_bankMobile_code").val("重新获取验证码"),s=0)}$("#Jbank_bankcard").bankInput();var l=0;$("#moreBank").live("click",function(){l?($("#addBank_list").animate({height:"97"}),$(this).html("更多"),l=0):($("#addBank_list").animate({height:"250"}),$(this).html("收起"),l=1)}),$("#addBank_list li").live("click",function(){$(this).css({"border-color":"#f18a21"}).siblings().css({"border-color":"#d7d7d7"}),$("#S_quota").html($(this).attr("S_quota")),$("#SD_quota").html($(this).attr("SD_quota")),$("#bank_id").val($(this).attr("bank_id"))});var r=null,o=!1,s=0;$("#get_bankMobile_code").live("click",function(){if(o||$(this).hasClass(".btn_disable"))return!1;var e=$.trim($("#bankphone").val());if(!e)return layer.tips(n.EMPTY_BANK_PHONE,"#bankphone",{tips:1}),$("#bankphone").focus(),!1;if(!t(e))return layer.tips(n.ERROR_PHONE,"#bankphone",{tips:1}),$("#bankphone").focus(),!1;var l=$.trim($("#Verifycode").val());if(0==l.length)return layer.tips(n.EMPTY_PIC_CODE,"#Verifycode",{tips:1}),$("#Verifycode").focus(),!1;if(!i(l,4,!1))return layer.tips(n.ERROR_PIC_CODE,"#Verifycode",{tips:1}),$("#Verifycode").focus(),!1;$(this).addClass("btn_disable"),layer.load(1);var r=new Object,c="/index.php?ctl=ajax&act=send_phone_verifycode_two";r.user_mobile=e,r.Verifycode=l,$.ajax({type:"post",url:c,dataType:"json",data:r,async:!0,success:function(e){if(layer.closeAll("loading"),!e.status)return $("#Jverify_img").attr("src","/verify.php?w=89&h=44&rand="+Math.random()),layer.alert(e.info,{title:"提示",icon:5,closeBtn:0}),$("#get_bankMobile_code").removeClass("btn_disable"),!1;o=!0,s=60,a();var t=layer.alert(e.info,{title:"成功",icon:6,closeBtn:0},function(){layer.close(t),$("#validateCode").focus()})},error:function(){return layer.closeAll("loading"),layer.alert(n.REQUEST_DATA_FAILED,{title:"错误",icon:2,closeBtn:0}),$("#get_bankMobile_code").removeClass("btn_disable"),!1}})}),$("#add_bank_sunbmit").live("click",function(){if(!$("#Jbank_real_name").hasClass("readonly")&&(!$("#Jbank_real_name").val()||$("#Jbank_real_name").val().length<=0))return layer.tips(n.EMPTY_REALNAME,"#Jbank_real_name",{tips:1}),!1;if($("#idno").hasClass("readonly")){var a=e("new/js/mod/util/verify").verify_IDcard,i=$.trim($("#uc_IDcard").val());if(!i)return layer.tips(n.EMPTY_IDCARD,"#uc_IDcard",{tips:1}),$("#uc_IDcard").focus(),!1;if(!a(i))return layer.tips(n.ERROR_IDCARD,"#uc_IDcard",{tips:1}),$("#uc_IDcard").focus(),!1}if(""==$("#bank_id").val())return layer.alert(n.EMPTY_BANK_SELECT,{title:"提示",icon:5,closeBtn:0}),!1;var l=$("#Jbank_bankcard").val().replace(/\D/g,"");if(l.length<10)return layer.tips("最少输入10位账号信息","#Jbank_bankcard",{tips:1}),$("#Jbank_bankcard").focus(),!1;var r=$.trim($("#bankphone").val());if(!r)return layer.tips(n.EMPTY_BANK_PHONE,"#bankphone",{tips:1}),$("#bankphone").focus(),!1;if(!t(r))return layer.tips(n.ERROR_PHONE,"#bankphone",{tips:1}),$("#bankphone").focus(),!1;var o=$.trim($("#validateCode").val());if(!o)return layer.tips(n.EMPTY_PHONE_CODE,"#validateCode",{tips:1}),$("#validateCode").focus(),!1;if(isNaN(o)||o.length<6)return layer.tips(n.ERROR_PHONE_CODE,"#validateCode",{tips:1}),$("#validateCode").focus(),!1;layer.load(1);var s=$("#addbank_from").attr("action"),c=$("#addbank_from").serialize();$.ajax({url:s,data:c,type:"post",dataType:"json",success:function(e){if(layer.closeAll("loading"),1==e.status)layer.alert(e.info,{title:"成功",icon:6,closeBtn:0},function(){layer.closeAll(),window.location.href=e.jump});else var a=layer.alert(e.info,{title:"提示",icon:5,closeBtn:0},function(){layer.close(a)})},error:function(){layer.closeAll("loading");var e=layer.alert(n.REQUEST_DATA_FAILED,{title:"错误",icon:2,closeBtn:0},function(){layer.close(e)})}})})})});