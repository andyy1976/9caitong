var _jsonSubmit=function(a,b){$.ajax({url:b.url,type:"post",data:a.serialize(),dataType:"json",success:b.success})};var notice=function(b){var a=setInterval(function(){$(b).html("");$(b).hide();clearInterval(a)},2000)};var loadCaptch=function(a,b){a.attr("src",captcha+"&t="+new Date().getTime());$.ajax({url:challenge+"&t="+new Date().getTime(),type:"get",dataType:"jsonp",success:function(c){if(c.ret){b.val(c.id)}}})};var ageMore18=function(e){var d=0,a=0,b=0;var c=new Date();switch(e.length){case 15:d=parseInt(e.substr(6,2));a=parseInt(e.substr(8,2));b=parseInt(e.substr(10,2));d=1900+d;break;case 18:d=parseInt(e.substr(6,4));a=parseInt(e.substr(10,2));b=parseInt(e.substr(12,2));break;default:r=false}if(d==0&&a==0&&b==0){return false}if(c.getFullYear()-d<18){return false}else{if(c.getFullYear()-d>18){return true}else{if(c.getMonth()+1<a){return false}else{if(c.getMonth()+1>a){return true}else{if(c.getDate()<b){return false}else{return true}}}}}};var isValidIdCard=function(d){d=d.replace("x","X");var e={11:"北京",12:"天津",13:"河北",14:"山西",15:"内蒙古",21:"辽宁",22:"吉林",23:"黑龙江",31:" 上海",32:"江苏",33:"浙江",34:"安徽",35:"福建",36:"江西",37:"山东",41:"河南",42:"湖北",43:" 湖南",44:"广东",45:"广西",46:"海南",50:"重庆",51:"四川",52:"贵州",53:"云南",54:"西藏",61:" 陕西",62:"甘肃",63:"青海",64:"宁夏",65:"新疆",71:"台湾",81:"香港",82:"澳门",91:"国外"};var f,b;var c,g;var a=new Array();a=d.split("");if(e[parseInt(d.substr(0,2))]==null){return false}switch(d.length){case 15:if((parseInt(d.substr(6,2))+1900)%4==0||((parseInt(d.substr(6,2))+1900)%100==0&&(parseInt(d.substr(6,2))+1900)%4==0)){ereg=/^[1-9][0-9]{5}[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))[0-9]{3}$/}else{ereg=/^[1-9][0-9]{5}[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8]))[0-9]{3}$/}if(ereg.test(d)){return true}else{return false}break;case 18:if(parseInt(d.substr(6,4))%4==0||(parseInt(d.substr(6,4))%100==0&&parseInt(d.substr(6,4))%4==0)){ereg=/^[1-9][0-9]{5}19[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))[0-9]{3}[0-9Xx]$/}else{ereg=/^[1-9][0-9]{5}19[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8]))[0-9]{3}[0-9Xx]$/}if(ereg.test(d)){c=(parseInt(a[0])+parseInt(a[10]))*7+(parseInt(a[1])+parseInt(a[11]))*9+(parseInt(a[2])+parseInt(a[12]))*10+(parseInt(a[3])+parseInt(a[13]))*5+(parseInt(a[4])+parseInt(a[14]))*8+(parseInt(a[5])+parseInt(a[15]))*4+(parseInt(a[6])+parseInt(a[16]))*2+parseInt(a[7])*1+parseInt(a[8])*6+parseInt(a[9])*3;f=c%11;g="F";b="10X98765432";g=b.substr(f,1);if(g==a[17]){return true}else{return false}}else{return false}break;default:return false}};var getMidScreen=function(c){var d={left:0,top:0};var f=$(c).height();var k=$(c).width();var e=window.innerWidth?window.innerWidth:$(window).width();var a=window.innerHeight?window.innerHeight:$(window).height();var b=0;if(e>k){b=(e-k)/2}else{b=(1024-k)/2}var i=0;if(a>f){i=(a-f)/2}else{i=(768-f)/2}var j=document.documentElement,g=document.body;i=i+(j&&j.scrollTop||g&&g.scrollTop||0)-(j&&j.clientTop||g&&g.clientTop||0);d.top=i;d.left=b;return d};var closeDiv=function(){var a=$("#coverdv");var b=$("#upperdv");b.empty();a.addClass("dn");b.addClass("dn")};var closeDiv1=function(){var a=$("#coverdv");var b=$("#cardmaskdiv");b.empty();a.addClass("dn");b.addClass("dn")};var openParentDiv=function(d,b,a){var c=$("#divly",parent.document);var f=$("#tandiv",parent.document);var e=d();f.html(e);$(f).removeClass("dn");c.removeClass("dn");c.height($(parent.document).height());if(a){f.find(".sqdiv").addClass("extsqdiv")}else{f.find(".sqdiv").addClass("lextsqdiv");f.find(".sqdiv").attr("style","");f.find("#htitle").attr("style","");f.find("#tbRepayDetails").attr("style","")}if(b!=null){b()}};var openDiv=function(c,a){var b=$("#coverdv");var e=$("#upperdv");var d=c();e.html(d);$(e).removeClass("dn");var f=getMidScreen(e);$(e).css("left",f.left);$(e).css("top",f.top);b.removeClass("dn");b.height($(document).height());if(a!=null){a()}};var openDiv3=function(c,a){var b=$("#coverdv");var e=$("#upperdv");var d=c();e.html(d);$(e).removeClass("dn");var f=getMidScreen(e);$(e).offset(f);b.removeClass("dn");b.height($(document).height());if(a!=null){a()}};var openDivApp1=function(c,a){var b=$("#coverdv");var e=$("#upperdv");var d=c();e.html(d);$(e).removeClass("dn");b.removeClass("dn");if(a!=null){a()}};var openDivAllWindow=function(c,a){var b=$("#coverdv");var e=$("#upperdv");var d=c();e.html(d);$(e).removeClass("dn");var f={left:0,top:0};$(e).offset(f);b.removeClass("dn");b.height($(document).height());if(a!=null){a()}};var openDiv5=function(d,a){var b=$("#coverdv");var c=$("#cardmaskdiv");c.html(d);$(c).removeClass("dn");b.removeClass("dn");b.height($(document).height());if(a!=null){a()}};var openDiv6=function(d,a){var b=$("#coverdv");var c=$("#cardmaskdiv1");c.html(d);$(c).removeClass("dn");b.removeClass("dn");b.height($(document).height());if(a!=null){a()}};var openDiv2=function(d,a){var b=$("#coverdv");var c=$("#upperdv");c.html(d);$(c).removeClass("dn");var e=getMidScreen(c);$(c).offset(e);b.removeClass("dn");b.height($(document).height());if(a!=null){a()}};var notify2=function(d,a,b,e,c){if(c=="input"){if(b){$(d).find("span[tar='"+a+"'] i").addClass("q_bg dis");$(d).find("span[tar='"+a+"'] span[class='tipscontent']").html(e)}else{$(d).find("span[tar='"+a+"'] i").removeClass("q_bg dis");$(d).find("span[tar='"+a+"'] span[class='tipscontent']").html("")}}};var notify=function(a,b,d,c){if(c=="input"){if(b){$("span[tar='"+a+"'] i").addClass("q_bg dis");$("span[tar='"+a+"'] span[class='tipscontent']").html(d)}else{$("span[tar='"+a+"'] i").removeClass("q_bg dis");$("span[tar='"+a+"'] span[class='tipscontent']").html("")}}};var getTimeLeft=function(f){var c=parseInt(f/(24*60*60*1000),10);var b=f%(24*60*60*1000);var a=parseInt(b/(60*60*1000),10);var e=b%(60*60*1000);var h=parseInt(e/(60*1000),10);var g=e%(60*1000);var d=parseInt(Math.ceil(g/1000),10);return{day:c,hour:a,minute:h,second:d}};var round=function(c,g){var e=parseFloat(c);if(isNaN(e)){return}var a=1;for(var d=0;d<g;d++){a*=10}e=Math.floor(c*a)/a;return e};var toDecimal=function(a){var b=parseFloat(a);if(isNaN(b)){return}b=Math.floor(a*100)/100;return b};var replaceFullWithNumber=function(a){a=a.replace(/１/g,1).replace(/２/g,2).replace(/３/g,3).replace(/４/g,4).replace(/５/g,5).replace(/６/g,6).replace(/７/g,7).replace(/８/g,8).replace(/９/g,9).replace(/０/g,0);return a};var replaceNoFloat=function(a){a=replaceFullWithNumber(a);a=a.replace(/[^0-9\.]/g,"");return a};var replaceNoNumber=function(a){a=replaceFullWithNumber(a);a=a.replace(/[^0-9]/g,"");return a};var isEmpty=function(a){return a==null||$.trim(a)==""};var isPartnerPath=function(b){if(b==null){return false}var a=/^\/\/www.xiaoyoucai.com\/source\/images\/.*\.(jpg|png)$/;return a.test($.trim(b))};var isMobile=function(a){if(a==null){return false}var b=/^\d{11}$/;return b.test($.trim(a))};var isNumber=function(b){if(b==null){return false}var a=/^\d+$/;return a.test(b)};var isFloat=function(b){if(b==null){return false}var a=/^(\d+|\d+\.\d+)$/;return a.test(b)};var isPhone=function(a){if(a==null){return false}var b=/^[\d][\d\-]*$/;return b.test(a)};var isLoginName=function(a){if(a==null){return false}var b=/^[a-zA-Z][\da-zA-Z_]*$/;return b.test(a)};var isPinYin=function(a){if(a==null){return false}var b=/^[a-zA-Z]+$/;return b.test(a)};var isValidIdNo=function(s){s=s.toUpperCase();if(!(/(^\d{15}$)|(^\d{17}([0-9]|X)$)/.test(s))){return false}var q,i;q=s.length;if(q==15){i=new RegExp(/^(\d{6})(\d{2})(\d{2})(\d{2})(\d{3})$/);var m=s.match(i);var u=new Date("19"+m[2]+"/"+m[3]+"/"+m[4]);var v;v=(u.getYear()==Number(m[2]))&&((u.getMonth()+1)==Number(m[3]))&&(u.getDate()==Number(m[4]));if(!v){return false}else{var o=new Array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);var n=new Array("1","0","X","9","8","7","6","5","4","3","2");var p=0,t;s=s.substr(0,6)+"19"+s.substr(6,s.length-6);for(t=0;t<17;t++){p+=s.substr(t,1)*o[t]}s+=n[p%11];return true}}if(q==18){i=new RegExp(/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/);var m=s.match(i);var u=new Date(m[2]+"/"+m[3]+"/"+m[4]);var v;v=(u.getFullYear()==Number(m[2]))&&((u.getMonth()+1)==Number(m[3]))&&(u.getDate()==Number(m[4]));if(!v){return false}else{var w;var o=new Array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);var n=new Array("1","0","X","9","8","7","6","5","4","3","2");var p=0,t;for(t=0;t<17;t++){p+=s.substr(t,1)*o[t]}w=n[p%11];if(w!=s.substr(17,1)){return false}return true}}return false};var isMail=function(a){if(a==null){return false}var b=/^([a-zA-Z0-9]+[_|\_|\.|\-]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.|\-]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;return b.test(a)};var isBankNo=function(a){if(a==null){return fasle}var b=/^[0-9]{16,19}$/;return b.test(a)};var showConfirmDlg2=function(f,e,d,b){var c=new Dlg({width:"400"});c.setHeader("<h2 class='title'>"+f+"</h2>");var a=e;c.setBody(a);c.setBtns([{text:"确定",fn:function(g){b(g);if(d){c.close()}}},{text:"取消",fn:function(g){c.close()}}]);c.show()};var showConfirmDlg4=function(f,e,d,b,g){var c=new Dlg({width:"400"});c.setHeader("<h2 class='title'>"+f+"</h2>");var a=e;c.setBody(a);c.setBtns([{text:"通过复审",fn:function(h){b(g);if(d){c.close()}}},{text:"取消",fn:function(h){c.close()}}]);c.show()};var showConfirmDlg3=function(f,e,d,b,g){var c=new Dlg({width:"400"});c.setHeader("<h2 class='title'>"+f+"</h2>");var a=e;c.setBody(a);c.setBtns([{text:"通过初审",fn:function(h){b(g);if(d){c.close()}}},{text:"取消",fn:function(h){c.close()}}]);c.show()};var showConfirmDlg5=function(f,e,d,b,g){var c=new Dlg({width:"400"});c.setHeader("<h2 class='title'>"+f+"</h2>");var a=e;c.setBody(a);c.setBtns([{text:"确定",fn:function(h){b(g);if(d){c.close()}}},{text:"取消",fn:function(h){c.close()}}]);c.show()};var showConfirmDlg=function(e,d,b){var c=new Dlg({width:"400"});c.setHeader("<h2 class='title'>"+e+"</h2>");var a=d;c.setBody(a);c.setBtns([{text:"确定",fn:function(){c.close();if(b!=null){b()}}},{text:"取消",fn:function(){c.close()}}]);c.show()};var showNoticeDlg=function(e,d,b){var c=new Dlg({width:"400"});c.setHeader("<h2 class='title'>"+e+"</h2>");var a=d;c.setBody(a);c.setBtns([{text:"确定",fn:function(){c.close();if(b!=null){b()}}}]);c.show()};function isUrl(b){var a=/^(http|https|ftp):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?$/;return a.test(b)}var showLay=function(c,b){var a=getMidScreen(c);$(c).css("top",a.top+"px");$(b).attr("style","height:"+$(document).height()+"px;");$(b).removeClass("dn");$(c).removeClass("dn")};var showNoTopLay=function(b,a){getMidScreen(b);$(a).attr("style","height:"+$(document).height()+"px;");$(a).removeClass("dn");$(b).removeClass("dn")};var showParentLay=function(c,b){var a=getMidScreen(c);$(c).css("top",a.top+"px");$(b).attr("style","height:"+$(parent.document).height()+"px;");$(b).removeClass("dn");$(c).removeClass("dn")};var showXmsParentLay=function(b,a){$(a).attr("style","height:"+$(parent.document).height()+"px;");$(a).removeClass("dn");$(b).removeClass("dn")};function validateUploadImg(c){var b=$("#"+c);if(b.length==0){return false}var d=b.children("img:first");var a=true;if(d.length==0){a=false}else{if(isEmpty(d.attr("dl"))){a=false}}b.removeClass("redBd");if(!a){b.addClass("redBd")}return a}function luhmCheck(e){if(e==""||e==null){return false}var y=e.substr(e.length-1,1);var b=e.substr(0,e.length-1);var B=new Array();for(var x=b.length-1;x>-1;x--){B.push(b.substr(x,1))}var t=new Array();var a=new Array();var g=new Array();for(var w=0;w<B.length;w++){if((w+1)%2==1){if(parseInt(B[w])*2<9){t.push(parseInt(B[w])*2)}else{a.push(parseInt(B[w])*2)}}else{g.push(B[w])}}var d=new Array();var c=new Array();for(var z=0;z<a.length;z++){d.push(parseInt(a[z])%10);c.push(parseInt(a[z])/10)}var A=0;var v=0;var l=0;var f=0;var D=0;for(var s=0;s<t.length;s++){A=A+parseInt(t[s])}for(var q=0;q<g.length;q++){v=v+parseInt(g[q])}for(var o=0;o<d.length;o++){l=l+parseInt(d[o]);f=f+parseInt(c[o])}D=parseInt(A)+parseInt(v)+parseInt(l)+parseInt(f);var u=parseInt(D)%10==0?10:parseInt(D)%10;var C=10-u;if(e.length==17){C=y}if(y==C){return true}else{return false}}function checkFile(a){if(isEmpty(a)){return null}var b=a.substr(a.lastIndexOf(".")+1);b=b.toLowerCase();if("jpg"==b||"gif"==b||"png"==b||"bmp"==b){return false}return true}function convertCurrency(x){var c=99999999999.99;var B="零";var F="壹";var j="贰";var k="叁";var m="肆";var H="伍";var E="陆";var A="柒";var J="捌";var C="玖";var g="拾";var o="佰";var t="仟";var f="万";var h="亿";var w="元";var e="角";var u="分";var y="整";var b,M,v,I;var K,n,s,q;var a;var G,D,L;var N,l;if(x.toString()==""){return""}if(!isFloat(x)){return""}var z=/^((\d{1,3}(,\d{3})*(.((\d{3},)*\d{1,3}))?)|(\d+(.\d+)?))$/;if(!z.test(x)){return""}x=x.replace(/,/g,"");x=x.replace(/^0+/,"");if(Number(x)>c){return""}I=x.split(".");if(I.length>1){b=I[0];M=I[1];M=M.substr(0,2)}else{b=I[0];M=""}K=new Array(B,F,j,k,m,H,E,A,J,C);n=new Array("",g,o,t);s=new Array("",f,h);q=new Array(e,u);v="";if(Number(b)>0){a=0;for(G=0;G<b.length;G++){D=b.length-G-1;L=b.substr(G,1);N=D/4;l=D%4;if(L=="0"){a++}else{if(a>0){v+=K[0]}a=0;v+=K[Number(L)]+n[l]}if(l==0&&a<4){v+=s[N]}}v+=w}if(M!=""){for(G=0;G<M.length;G++){L=M.substr(G,1);if(L!="0"){v+=K[Number(L)]+q[G]}}}if(v==""){v=B+w}if(M==""){v+=y}return v};