var YZM=function(a){a=a||{};this.defaultCfg={getSmsObj:$("#getSms"),cTimer:$("#cTimer"),timer:$("#timer"),scode:$("#scode"),mobile:$("#mobile"),getSmsVo:$("#getSmsVo"),getSmsCtr:$("#getSmsCtr"),jdtiao:$("#jdtiao"),jdt:$("#jdt"),rt:0,ct:0,tu:1,fft:0,entt:0,smsSendLabel:1,voSendLabel:1,smsLabel:"",voLabel:"",canSend:true};$.extend(this,this.defaultCfg,a);this._init()};YZM.prototype={ci:null,reset:function(){if(this.ci!=null){clearInterval(this.ci)}this.cTimer.css("display","none");this.jdtiao.css("display","none");this.getSmsObj.css("display","");this.canSend=true},_handle:function(a){var o=this.getSmsObj;var i=this.getSmsVo;var l=this.getSmsCtr;var g=this.cTimer;var c=this.timer;var d=this.scode;var b=this.mobile;var n=this.jdtiao;var f=this.jdt;var j=this;var k=$(o).html();var m="";if(i.length>0){m=$(i).html()}if(a=="sms"){if(this.smsSendLabel==1){if(this.smsLabel==""){$(o).html("发送中...")}else{$(o).html(this.smsLabel)}}}else{if(a=="vo"){if(this.voSendLabel==1){if(this.voLabel==""){$(i).html("拨打中...")}else{$(i).html(this.voLabel)}}}}if(j.showInfo!=null){j.showInfo()}var e=j.ct;if($(o).attr("ctt")!=null&&$(o).attr("ctt")!=""){e=$(o).attr("ctt")}var p=j.fft;if($(o).attr("fft")!=null&&$(o).attr("fft")!=""){p=$(o).attr("fft")}var q=j.entt;if($(o).attr("entt")!=null&&$(o).attr("entt")!=""){q=$(o).attr("entt")}$.ajax({url:"/dt/getSmsCode",data:{mobile:$.trim(b.val()),rt:j.rt,ct:e,tu:j.tu,fft:p,entt:q,st:a,smsvf:$("#smsvf").val()},type:"get",dataType:"json",success:function(h){if(j.smsSendLabel==1){$(o).html(k)}if(j.voSendLabel==1){$(i).html(m)}j.canSend=true;if(h.ret==1){i.css("display","none");o.css("display","none");l.css("display","none");g.show();n.show();c.html("60");f.width("100%");ci=setInterval(function(){var s=parseInt(c.html(),10);s--;var r=f.width();if(s<=0){clearInterval(ci);g.hide();n.hide();o.css("display","");i.css("display","");l.css("display","")}c.html(s);f.width(""+s*1.67+"%")},1000);if(j.sendSucc!=null){j.sendSucc()}if(h.isHasFft==1){$("#fftShowPro").show();$("#fftFgtPro").show();$("#fftProPassword").val("")}else{$("#fftShowPro").hide();$("#fftFgtPro").hide();$("#fftProPassword").val("")}if(h.vo){if(j.voFunc!=null){j.voFunc()}}}else{switch(h.errcode){case 0:if(j.errMobile!=null){j.errMobile()}else{openL("获取验证码","错误的手机号码")}break;case 1:if(j.toManyTimes!=null){j.toManyTimes()}else{openL("获取验证码","您的请求过于频繁，请在一分种后尝试")}break;case 2:if(j.errGenCode!=null){j.errGenCode()}else{openL("获取验证码","验证码生成失败")}break;case 5:if(j.emptyMobile!=null){j.emptyMobile()}else{openL("获取验证码","该手机号码未注册")}break;case 6:if(j.regMobile!=null){j.regMobile()}else{openL("获取验证码","该手机号码已注册");j.reset()}break;case 7:if(j.emptyImg!=null){j.emptyImg()}else{openL("获取验证码","图形验证码为空");j.reset()}break;case 8:if(j.errImg!=null){j.errImg()}else{openL("获取验证码","图形验证码不正确");j.reset()}break;case 9:if(j.errImg!=null){j.errImg()}else{openL("获取验证码","图形验证码发送失败");j.reset()}break}}}})},_init:function(){var b=this.getSmsObj;var c=this.getSmsVo;var f=this.cTimer;var h=this.timer;var e=this.scode;var d=this.mobile;var g=this;var a=function(i){if(!g.canSend){return}g.canSend=false;if(!isMobile(d.val())){if(g.errMobile!=null){g.errMobile()}else{openL("获取验证码","错误的手机号码")}g.canSend=true;return}if(g.clearCode!=null){g.clearCode()}var j=[];var k=new Date();j.push("<div id='smsImgDv'>");j.push("<input maxlength='10' type='text' name='smsvf'  id='smsvf'  size='18' style='width:90px;font-family:微软雅黑;float:left;border:1px solid;height:28px;line-height:28px;padding:1px' />");j.push("<img style='float: left;cursor: pointer;width:126px;border:0;height:32px;margin-left:5px'   title='验证码' id='imgverCode2' src='/smsVerfyCode?a=",k.getTime(),"'  />");j.push("</div>");if(g.tu==1){openL2("输入图片验证码",j.join(""),function(){g.canSend=true},function(){g._handle(i)})}else{g._handle(i)}$("#smsImgDv").delegate("img","click",function(){var l=new Date();$(this).attr("src","/dt/smsVerfyCode?a="+l.getTime())})};$(b).click(function(){if(!g.canSend){return false}a("sms");return false});$(c).click(function(){if(!g.canSend){return false}a("vo");return false})}};