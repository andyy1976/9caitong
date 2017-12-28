define("new/js/mod/ModifyPhone",["new/css/layer.css","$","layer","new/js/mod/util/minLength","new/js/mod/util/checkMobilePhone","new/js/mod/util/sendPhoneCode","new/js/mod/util/lang"],function(e){e("new/css/layer.css");var t=e("$"),o=e("layer"),n=e("new/js/mod/util/minLength").minLength,l=e("new/js/mod/util/checkMobilePhone").checkMobilePhone,i=e("new/js/mod/util/sendPhoneCode").sendPhoneCode,d=e("new/js/mod/util/lang").lang;t("#sendOldPhoneCode").click(function(){var e=t(this).attr("ajax_url");i(t(this),"#old_phone",0,e,"#Verifycode")}),t("#ModifyPhone").unbind("submit"),t("#sub_btn_validateoldPhone").click(function(){if(""==t.trim(t("#old_phone").val()))return o.tips(d.EMPTY_OLD_PHONE,"#old_phone"),t("#old_phone").focus(),!1;if(!l(t.trim(t("#old_phone").val())))return o.tips(d.ERROR_PHONE,"#old_phone"),t("#old_phone").focus(),!1;if(""==t.trim(t("#oldvalidateCode").val()))return o.tips(d.EMPTY_PHONE_CODE,"#oldvalidateCode",{tips:1}),t("#oldvalidateCode").focus(),!1;if(!n(t.trim(t("#oldvalidateCode").val()),6,!1))return o.tips(d.ERROR_PHONE_CODE,"#oldvalidateCode",{tips:1}),t("#oldvalidateCode").focus(),!1;o.load(1);var e=new Object;e.old_mobile=t("#old_phone").val(),e.oldverify=t("#oldvalidateCode").val(),e.ajax=1,t.ajax({url:"/member.php?ctl=uc_account&act=pc_regsms_code_fir",data:e,type:"post",dataType:"json",async:!0,success:function(e){if(o.closeAll("loading"),e.status){var t="";t+="<form method='post' name='newPhone' id='newPhone' width='360' style='padding:50px 0 0 42px'>",t+="<table cellpadding='5' cellspacing='0' border='0'  class='uc_table_form'>",t+="<tbody>",t+="<tr>",t+="<td class='ta_r'>新手机号：</td>",t+="<td><input name='new_phone' id='new_phone' class='uc_input input_onfocus_shadow' placeholder='请输入新手机号' maxlength='11' /></td>",t+="<td></td>",t+="</tr>",t+="<tr>",t+="<tr>",t+="<td class='ta_r'>验证码：</td>",t+="<td><input name='newvalidateCode' id='newvalidateCode' class='uc_input input_onfocus_shadow' placeholder='请输入短信验证码' maxlength='6' /></td>",t+="<td><input type='button' class='ucbt3 bgC_blue1 C_white' id='sendNewPhoneCode' value='获取短信验证码' /></td>",t+="</tr>",t+="<tr>",t+="<td></td>",t+="<td><input type='button' id='sub_btn_bindnewPhone' value='确认绑定' class='uc_submit_blue' /></td>",t+="<td></td>",t+="</tr>",t+="</tbody>",t+="</table>",t+="</form>",o.open({type:1,move:!1,area:["550px","355px"],fix:!1,offset:"100px",title:"新手机号",content:t})}else o.alert(e.info,{title:"提示",icon:5,closeBtn:0})},error:function(){o.closeAll("loading"),o.alert(d.REQUEST_DATA_FAILED,{title:"错误",icon:2,closeBtn:0})}}),t("#sendNewPhoneCode").live("click",function(){i(t(this),"#new_phone",1,"/member.php?ctl=ajax&act=get_register_verify_code")}),t("#sub_btn_bindnewPhone").live("click",function(){if(""==t.trim(t("#new_phone").val()))return o.tips(d.EMPTY_NEW_PHONE,"#new_phone"),t("#new_phone").focus(),!1;if(!l(t.trim(t("#new_phone").val())))return o.tips(d.ERROR_PHONE,"#new_phone"),t("#new_phone").focus(),!1;if(""==t.trim(t("#newvalidateCode").val()))return o.tips(d.EMPTY_PHONE_CODE,"#newvalidateCode",{tips:1}),t("#newvalidateCode").focus(),!1;if(!n(t.trim(t("#newvalidateCode").val()),6,!1))return o.tips(d.ERROR_PHONE_CODE,"#newvalidateCode",{tips:1}),t("#newvalidateCode").focus(),!1;o.load(1);var e=new Object;e.mobile=t("#new_phone").val(),e.code=t("#newvalidateCode").val(),e.ajax=1,t.ajax({url:"/member.php?ctl=uc_account&act=regsms_code",data:e,type:"post",dataType:"json",success:function(e){o.closeAll("loading"),e.status?o.alert(e.info,{title:"成功",icon:6,closeBtn:0},function(){e.jump?window.location.href=e.jump:window.location.reload()}):o.alert(e.info,{title:"提示",icon:5,closeBtn:0})},error:function(){o.closeAll("loading"),o.alert(d.REQUEST_DATA_FAILED,{title:"错误",icon:2,closeBtn:0})}})})})});