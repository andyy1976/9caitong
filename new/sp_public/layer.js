!function(e,t){"use strict";var i,n,a={getPath:function(){var e=document.scripts,t=e[e.length-1],i=t.src;return t.getAttribute("merge")?void 0:i.substring(0,i.lastIndexOf("/")+1)}(),enter:function(e){13===e.keyCode&&e.preventDefault()},config:{},end:{},btn:["&#x786E;&#x5B9A;","&#x53D6;&#x6D88;"],type:["dialog","page","iframe","loading","tips"]},o={v:"2.4",ie6:!!e.ActiveXObject&&!e.XMLHttpRequest,index:0,path:a.getPath,config:function(e,t){var n=0;return e=e||{},o.cache=a.config=i.extend(a.config,e),o.path=a.config.path||o.path,"string"==typeof e.extend&&(e.extend=[e.extend]),o.use("skin/layer.css",e.extend&&e.extend.length>0?function r(){var i=e.extend;o.use(i[i[n]?n:n-1],n<i.length?function(){return++n,r}():t)}():t),this},use:function(e,t,n){var a=i("head")[0],e=e.replace(/\s/g,""),r=/\.css$/.test(e),l=document.createElement(r?"link":"script"),s="layui_layer_"+e.replace(/\.|\//g,"");return o.path?(r&&(l.rel="stylesheet"),l[r?"href":"src"]=/^http:\/\//.test(e)?e:o.path+e,l.id=s,i("#"+s)[0]||a.appendChild(l),function c(){(r?1989===parseInt(i("#"+s).css("width")):o[n||s])?function(){t&&t();try{r||a.removeChild(l)}catch(e){}}():setTimeout(c,100)}(),this):void 0},ready:function(e,t){var n="function"==typeof e;return n&&(t=e),o.config(i.extend(a.config,function(){return n?{}:{path:e}}()),t),this},alert:function(e,t,n){var a="function"==typeof t;return a&&(n=t),o.open(i.extend({content:e,yes:n},a?{}:t))},confirm:function(e,t,n,r){var l="function"==typeof t;return l&&(r=n,n=t),o.open(i.extend({content:e,btn:a.btn,yes:n,btn2:r},l?{}:t))},msg:function(e,n,r){var s="function"==typeof n,c=a.config.skin,f=(c?c+" "+c+"-msg":"")||"layui-layer-msg",u=l.anim.length-1;return s&&(r=n),o.open(i.extend({content:e,time:3e3,shade:!1,skin:f,title:!1,closeBtn:!1,btn:!1,end:r},s&&!a.config.skin?{skin:f+" layui-layer-hui",shift:u}:function(){return n=n||{},(-1===n.icon||n.icon===t&&!a.config.skin)&&(n.skin=f+" "+(n.skin||"layui-layer-hui")),n}()))},load:function(e,t){return o.open(i.extend({type:3,icon:e||0,shade:.01},t))},tips:function(e,t,n){return o.open(i.extend({type:4,content:[e,t],closeBtn:!1,time:3e3,shade:!1,fix:!1,maxWidth:210},n))}},r=function(e){var t=this;t.index=++o.index,t.config=i.extend({},t.config,a.config,e),t.creat()};r.pt=r.prototype;var l=["layui-layer",".layui-layer-title",".layui-layer-main",".layui-layer-dialog","layui-layer-iframe","layui-layer-content","layui-layer-btn","layui-layer-close"];l.anim=["layer-anim","layer-anim-01","layer-anim-02","layer-anim-03","layer-anim-04","layer-anim-05","layer-anim-06"],r.pt.config={type:0,shade:.3,fix:!0,move:l[1],title:"&#x4FE1;&#x606F;",offset:"auto",area:"auto",closeBtn:1,time:0,zIndex:19891014,maxWidth:360,shift:0,icon:-1,scrollbar:!0,tips:2},r.pt.vessel=function(e,t){var i=this,n=i.index,o=i.config,r=o.zIndex+n,s="object"==typeof o.title,c=o.maxmin&&(1===o.type||2===o.type),f=o.title?'<div class="layui-layer-title" style="'+(s?o.title[1]:"")+'">'+(s?o.title[0]:o.title)+"</div>":"";return o.zIndex=r,t([o.shade?'<div class="layui-layer-shade" id="layui-layer-shade'+n+'" times="'+n+'" style="'+("z-index:"+(r-1)+"; background-color:"+(o.shade[1]||"#000")+"; opacity:"+(o.shade[0]||o.shade)+"; filter:alpha(opacity="+(100*o.shade[0]||100*o.shade)+");")+'"></div>':"",'<div class="'+l[0]+(" layui-layer-"+a.type[o.type])+(0!=o.type&&2!=o.type||o.shade?"":" layui-layer-border")+" "+(o.skin||"")+'" id="'+l[0]+n+'" type="'+a.type[o.type]+'" times="'+n+'" showtime="'+o.time+'" conType="'+(e?"object":"string")+'" style="z-index: '+r+"; width:"+o.area[0]+";height:"+o.area[1]+(o.fix?"":";position:absolute;")+'">'+(e&&2!=o.type?"":f)+'<div id="'+(o.id||"")+'" class="layui-layer-content'+(0==o.type&&-1!==o.icon?" layui-layer-padding":"")+(3==o.type?" layui-layer-loading"+o.icon:"")+'">'+(0==o.type&&-1!==o.icon?'<i class="layui-layer-ico layui-layer-ico'+o.icon+'"></i>':"")+(1==o.type&&e?"":o.content||"")+'</div><span class="layui-layer-setwin">'+function(){var e=c?'<a class="layui-layer-min" href="javascript:;"><cite></cite></a><a class="layui-layer-ico layui-layer-max" href="javascript:;"></a>':"";return o.closeBtn&&(e+='<a class="layui-layer-ico '+l[7]+" "+l[7]+(o.title?o.closeBtn:4==o.type?"1":"2")+'" href="javascript:;"></a>'),e}()+"</span>"+(o.btn?function(){var e="";"string"==typeof o.btn&&(o.btn=[o.btn]);for(var t=0,i=o.btn.length;i>t;t++)e+='<a class="'+l[6]+t+'">'+o.btn[t]+"</a>";return'<div class="'+l[6]+'">'+e+"</div>"}():"")+"</div>"],f),i},r.pt.creat=function(){var e=this,t=e.config,r=e.index,s=t.content,c="object"==typeof s;if(!i("#"+t.id)[0]){switch("string"==typeof t.area&&(t.area="auto"===t.area?["",""]:[t.area,""]),t.type){case 0:t.btn="btn"in t?t.btn:a.btn[0],o.closeAll("dialog");break;case 2:var s=t.content=c?t.content:[t.content||"http://layer.layui.com","auto"];t.content='<iframe scrolling="'+(t.content[1]||"auto")+'" allowtransparency="true" id="'+l[4]+r+'" name="'+l[4]+r+'" onload="this.className=\'\';" class="layui-layer-load" frameborder="0" src="'+t.content[0]+'"></iframe>';break;case 3:t.title=!1,t.closeBtn=!1,-1===t.icon&&0===t.icon,o.closeAll("loading");break;case 4:c||(t.content=[t.content,"body"]),t.follow=t.content[1],t.content=t.content[0]+'<i class="layui-layer-TipsG"></i>',t.title=!1,t.tips="object"==typeof t.tips?t.tips:[t.tips,!0],t.tipsMore||o.closeAll("tips")}e.vessel(c,function(n,a){i("body").append(n[0]),c?function(){2==t.type||4==t.type?function(){i("body").append(n[1])}():function(){s.parents("."+l[0])[0]||(s.show().addClass("layui-layer-wrap").wrap(n[1]),i("#"+l[0]+r).find("."+l[5]).before(a))}()}():i("body").append(n[1]),e.layero=i("#"+l[0]+r),t.scrollbar||l.html.css("overflow","hidden").attr("layer-full",r)}).auto(r),2==t.type&&o.ie6&&e.layero.find("iframe").attr("src",s[0]),i(document).off("keydown",a.enter).on("keydown",a.enter),e.layero.on("keydown",function(){i(document).off("keydown",a.enter)}),4==t.type?e.tips():e.offset(),t.fix&&n.on("resize",function(){e.offset(),(/^\d+%$/.test(t.area[0])||/^\d+%$/.test(t.area[1]))&&e.auto(r),4==t.type&&e.tips()}),t.time<=0||setTimeout(function(){o.close(e.index)},t.time),e.move().callback(),l.anim[t.shift]&&e.layero.addClass(l.anim[t.shift])}},r.pt.auto=function(e){function t(e){e=r.find(e),e.height(s[1]-c-f-2*(0|parseFloat(e.css("padding"))))}var a=this,o=a.config,r=i("#"+l[0]+e);""===o.area[0]&&o.maxWidth>0&&(/MSIE 7/.test(navigator.userAgent)&&o.btn&&r.width(r.innerWidth()),r.outerWidth()>o.maxWidth&&r.width(o.maxWidth));var s=[r.innerWidth(),r.innerHeight()],c=r.find(l[1]).outerHeight()||0,f=r.find("."+l[6]).outerHeight()||0;switch(o.type){case 2:t("iframe");break;default:""===o.area[1]?o.fix&&s[1]>=n.height()&&(s[1]=n.height(),t("."+l[5])):t("."+l[5])}return a},r.pt.offset=function(){var e=this,t=e.config,i=e.layero,a=[i.outerWidth(),i.outerHeight()],o="object"==typeof t.offset;e.offsetTop=(n.height()-a[1])/2,e.offsetLeft=(n.width()-a[0])/2,o?(e.offsetTop=t.offset[0],e.offsetLeft=t.offset[1]||e.offsetLeft):"auto"!==t.offset&&(e.offsetTop=t.offset,"rb"===t.offset&&(e.offsetTop=n.height()-a[1],e.offsetLeft=n.width()-a[0])),t.fix||(e.offsetTop=/%$/.test(e.offsetTop)?n.height()*parseFloat(e.offsetTop)/100:parseFloat(e.offsetTop),e.offsetLeft=/%$/.test(e.offsetLeft)?n.width()*parseFloat(e.offsetLeft)/100:parseFloat(e.offsetLeft),e.offsetTop+=n.scrollTop(),e.offsetLeft+=n.scrollLeft()),i.css({top:e.offsetTop,left:e.offsetLeft})},r.pt.tips=function(){var e=this,t=e.config,a=e.layero,o=[a.outerWidth(),a.outerHeight()],r=i(t.follow);r[0]||(r=i("body"));var s={width:r.outerWidth(),height:r.outerHeight(),top:r.offset().top,left:r.offset().left},c=a.find(".layui-layer-TipsG"),f=t.tips[0];t.tips[1]||c.remove(),s.autoLeft=function(){s.left+o[0]-n.width()>0?(s.tipLeft=s.left+s.width-o[0],c.css({right:12,left:"auto"})):s.tipLeft=s.left},s.where=[function(){s.autoLeft(),s.tipTop=s.top-o[1]-10,c.removeClass("layui-layer-TipsB").addClass("layui-layer-TipsT").css("border-right-color",t.tips[1])},function(){s.tipLeft=s.left+s.width+10,s.tipTop=s.top,c.removeClass("layui-layer-TipsL").addClass("layui-layer-TipsR").css("border-bottom-color",t.tips[1])},function(){s.autoLeft(),s.tipTop=s.top+s.height+10,c.removeClass("layui-layer-TipsT").addClass("layui-layer-TipsB").css("border-right-color",t.tips[1])},function(){s.tipLeft=s.left-o[0]-10,s.tipTop=s.top,c.removeClass("layui-layer-TipsR").addClass("layui-layer-TipsL").css("border-bottom-color",t.tips[1])}],s.where[f-1](),1===f?s.top-(n.scrollTop()+o[1]+16)<0&&s.where[2]():2===f?n.width()-(s.left+s.width+o[0]+16)>0||s.where[3]():3===f?s.top-n.scrollTop()+s.height+o[1]+16-n.height()>0&&s.where[0]():4===f&&o[0]+16-s.left>0&&s.where[1](),a.find("."+l[5]).css({"background-color":t.tips[1],"padding-right":t.closeBtn?"30px":""}),a.css({left:s.tipLeft-(t.fix?n.scrollLeft():0),top:s.tipTop-(t.fix?n.scrollTop():0)})},r.pt.move=function(){var e=this,t=e.config,a={setY:0,moveLayer:function(){var e=a.layero,t=parseInt(e.css("margin-left")),i=parseInt(a.move.css("left"));0===t||(i-=t),"fixed"!==e.css("position")&&(i-=e.parent().offset().left,a.setY=0),e.css({left:i,top:parseInt(a.move.css("top"))-a.setY})}},o=e.layero.find(t.move);return t.move&&o.attr("move","ok"),o.css({cursor:t.move?"move":"auto"}),i(t.move).on("mousedown",function(e){if(e.preventDefault(),"ok"===i(this).attr("move")){a.ismove=!0,a.layero=i(this).parents("."+l[0]);var o=a.layero.offset().left,r=a.layero.offset().top,s=a.layero.outerWidth()-6,c=a.layero.outerHeight()-6;i("#layui-layer-moves")[0]||i("body").append('<div id="layui-layer-moves" class="layui-layer-moves" style="left:'+o+"px; top:"+r+"px; width:"+s+"px; height:"+c+'px; z-index:2147483584"></div>'),a.move=i("#layui-layer-moves"),t.moveType&&a.move.css({visibility:"hidden"}),a.moveX=e.pageX-a.move.position().left,a.moveY=e.pageY-a.move.position().top,"fixed"!==a.layero.css("position")||(a.setY=n.scrollTop())}}),i(document).mousemove(function(e){if(a.ismove){var i=e.pageX-a.moveX,o=e.pageY-a.moveY;if(e.preventDefault(),!t.moveOut){a.setY=n.scrollTop();var r=n.width()-a.move.outerWidth(),l=a.setY;0>i&&(i=0),i>r&&(i=r),l>o&&(o=l),o>n.height()-a.move.outerHeight()+a.setY&&(o=n.height()-a.move.outerHeight()+a.setY)}a.move.css({left:i,top:o}),t.moveType&&a.moveLayer(),i=o=r=l=null}}).mouseup(function(){try{a.ismove&&(a.moveLayer(),a.move.remove(),t.moveEnd&&t.moveEnd()),a.ismove=!1}catch(e){a.ismove=!1}}),e},r.pt.callback=function(){function e(){var e=r.cancel&&r.cancel(t.index,n);e===!1||o.close(t.index)}var t=this,n=t.layero,r=t.config;t.openLayer(),r.success&&(2==r.type?n.find("iframe").on("load",function(){r.success(n,t.index)}):r.success(n,t.index)),o.ie6&&t.IE6(n),n.find("."+l[6]).children("a").on("click",function(){var e=i(this).index();if(0===e)r.yes?r.yes(t.index,n):r.btn1?r.btn1(t.index,n):o.close(t.index);else{var a=r["btn"+(e+1)]&&r["btn"+(e+1)](t.index,n);a===!1||o.close(t.index)}}),n.find("."+l[7]).on("click",e),r.shadeClose&&i("#layui-layer-shade"+t.index).on("click",function(){o.close(t.index)}),n.find(".layui-layer-min").on("click",function(){var e=r.min&&r.min(n);e===!1||o.min(t.index,r)}),n.find(".layui-layer-max").on("click",function(){i(this).hasClass("layui-layer-maxmin")?(o.restore(t.index),r.restore&&r.restore(n)):(o.full(t.index,r),setTimeout(function(){r.full&&r.full(n)},100))}),r.end&&(a.end[t.index]=r.end)},a.reselect=function(){i.each(i("select"),function(){var e=i(this);e.parents("."+l[0])[0]||1==e.attr("layer")&&i("."+l[0]).length<1&&e.removeAttr("layer").show(),e=null})},r.pt.IE6=function(e){function t(){e.css({top:o+(a.config.fix?n.scrollTop():0)})}var a=this,o=e.offset().top;t(),n.scroll(t),i("select").each(function(){var e=i(this);e.parents("."+l[0])[0]||"none"===e.css("display")||e.attr({layer:"1"}).hide(),e=null})},r.pt.openLayer=function(){var e=this;o.zIndex=e.config.zIndex,o.setTop=function(e){var t=function(){o.zIndex++,e.css("z-index",o.zIndex+1)};return o.zIndex=parseInt(e[0].style.zIndex),e.on("mousedown",t),o.zIndex}},a.record=function(e){var t=[e.width(),e.height(),e.position().top,e.position().left+parseFloat(e.css("margin-left"))];e.find(".layui-layer-max").addClass("layui-layer-maxmin"),e.attr({area:t})},a.rescollbar=function(e){l.html.attr("layer-full")==e&&(l.html[0].style.removeProperty?l.html[0].style.removeProperty("overflow"):l.html[0].style.removeAttribute("overflow"),l.html.removeAttr("layer-full"))},e.layer=o,o.getChildFrame=function(e,t){return t=t||i("."+l[4]).attr("times"),i("#"+l[0]+t).find("iframe").contents().find(e)},o.getFrameIndex=function(e){return i("#"+e).parents("."+l[4]).attr("times")},o.iframeAuto=function(e){if(e){var t=o.getChildFrame("html",e).outerHeight(),n=i("#"+l[0]+e),a=n.find(l[1]).outerHeight()||0,r=n.find("."+l[6]).outerHeight()||0;n.css({height:t+a+r}),n.find("iframe").css({height:t})}},o.iframeSrc=function(e,t){i("#"+l[0]+e).find("iframe").attr("src",t)},o.style=function(e,t){var n=i("#"+l[0]+e),o=n.attr("type"),r=n.find(l[1]).outerHeight()||0,s=n.find("."+l[6]).outerHeight()||0;(o===a.type[1]||o===a.type[2])&&(n.css(t),o===a.type[2]&&n.find("iframe").css({height:parseFloat(t.height)-r-s}))},o.min=function(e){var t=i("#"+l[0]+e),n=t.find(l[1]).outerHeight()||0;a.record(t),o.style(e,{width:180,height:n,overflow:"hidden"}),t.find(".layui-layer-min").hide(),"page"===t.attr("type")&&t.find(l[4]).hide(),a.rescollbar(e)},o.restore=function(e){var t=i("#"+l[0]+e),n=t.attr("area").split(",");t.attr("type"),o.style(e,{width:parseFloat(n[0]),height:parseFloat(n[1]),top:parseFloat(n[2]),left:parseFloat(n[3]),overflow:"visible"}),t.find(".layui-layer-max").removeClass("layui-layer-maxmin"),t.find(".layui-layer-min").show(),"page"===t.attr("type")&&t.find(l[4]).show(),a.rescollbar(e)},o.full=function(e){var t,r=i("#"+l[0]+e);a.record(r),l.html.attr("layer-full")||l.html.css("overflow","hidden").attr("layer-full",e),clearTimeout(t),t=setTimeout(function(){var t="fixed"===r.css("position");o.style(e,{top:t?0:n.scrollTop(),left:t?0:n.scrollLeft(),width:n.width(),height:n.height()}),r.find(".layui-layer-min").hide()},100)},o.title=function(e,t){var n=i("#"+l[0]+(t||o.index)).find(l[1]);n.html(e)},o.close=function(e){var t=i("#"+l[0]+e),n=t.attr("type");if(t[0]){if(n===a.type[1]&&"object"===t.attr("conType")){t.children(":not(."+l[5]+")").remove();for(var r=0;2>r;r++)t.find(".layui-layer-wrap").unwrap().hide()}else{if(n===a.type[2])try{var s=i("#"+l[4]+e)[0];s.contentWindow.document.write(""),s.contentWindow.close(),t.find("."+l[5])[0].removeChild(s)}catch(c){}t[0].innerHTML="",t.remove()}i("#layui-layer-moves, #layui-layer-shade"+e).remove(),o.ie6&&a.reselect(),a.rescollbar(e),i(document).off("keydown",a.enter),"function"==typeof a.end[e]&&a.end[e](),delete a.end[e]}},o.closeAll=function(e){i.each(i("."+l[0]),function(){var t=i(this),n=e?t.attr("type")===e:1;n&&o.close(t.attr("times")),n=null})};var s=o.cache||{},c=function(e){return s.skin?" "+s.skin+" "+s.skin+"-"+e:""};o.prompt=function(e,t){e=e||{},"function"==typeof e&&(t=e);var n,a=2==e.formType?'<textarea class="layui-layer-input">'+(e.value||"")+"</textarea>":function(){return'<input type="'+(1==e.formType?"password":"text")+'" class="layui-layer-input" value="'+(e.value||"")+'">'}();return o.open(i.extend({btn:["&#x786E;&#x5B9A;","&#x53D6;&#x6D88;"],content:a,skin:"layui-layer-prompt"+c("prompt"),success:function(e){n=e.find(".layui-layer-input"),n.focus()},yes:function(i){var a=n.val();""===a?n.focus():a.length>(e.maxlength||500)?o.tips("&#x6700;&#x591A;&#x8F93;&#x5165;"+(e.maxlength||500)+"&#x4E2A;&#x5B57;&#x6570;",n,{tips:1}):t&&t(a,i,n)}},e))},o.tab=function(e){e=e||{};var t=e.tab||{};return o.open(i.extend({type:1,skin:"layui-layer-tab"+c("tab"),title:function(){var e=t.length,i=1,n="";if(e>0)for(n='<span class="layui-layer-tabnow">'+t[0].title+"</span>";e>i;i++)n+="<span>"+t[i].title+"</span>";return n}(),content:'<ul class="layui-layer-tabmain">'+function(){var e=t.length,i=1,n="";if(e>0)for(n='<li class="layui-layer-tabli xubox_tab_layer">'+(t[0].content||"no content")+"</li>";e>i;i++)n+='<li class="layui-layer-tabli">'+(t[i].content||"no  content")+"</li>";return n}()+"</ul>",success:function(t){var n=t.find(".layui-layer-title").children(),a=t.find(".layui-layer-tabmain").children();n.on("mousedown",function(t){t.stopPropagation?t.stopPropagation():t.cancelBubble=!0;var n=i(this),o=n.index();n.addClass("layui-layer-tabnow").siblings().removeClass("layui-layer-tabnow"),a.eq(o).show().siblings().hide(),"function"==typeof e.change&&e.change(o)})}},e))},o.photos=function(t,n,a){function r(e,t,i){var n=new Image;return n.src=e,n.complete?t(n):(n.onload=function(){n.onload=null,t(n)},void(n.onerror=function(e){n.onerror=null,i(e)}))}var l={};if(t=t||{},t.photos){var s=t.photos.constructor===Object,f=s?t.photos:{},u=f.data||[],d=f.start||0;if(l.imgIndex=(0|d)+1,t.img=t.img||"img",s){if(0===u.length)return o.msg("&#x6CA1;&#x6709;&#x56FE;&#x7247;")}else{var y=i(t.photos),p=function(){u=[],y.find(t.img).each(function(e){var t=i(this);t.attr("layer-index",e),u.push({alt:t.attr("alt"),pid:t.attr("layer-pid"),src:t.attr("layer-src")||t.attr("src"),thumb:t.attr("src")})})};if(p(),0===u.length)return;if(n||y.on("click",t.img,function(){var e=i(this),n=e.attr("layer-index");o.photos(i.extend(t,{photos:{start:n,data:u,tab:t.tab},full:t.full}),!0),p()}),!n)return}l.imgprev=function(e){l.imgIndex--,l.imgIndex<1&&(l.imgIndex=u.length),l.tabimg(e)},l.imgnext=function(e,t){l.imgIndex++,l.imgIndex>u.length&&(l.imgIndex=1,t)||l.tabimg(e)},l.keyup=function(e){if(!l.end){var t=e.keyCode;e.preventDefault(),37===t?l.imgprev(!0):39===t?l.imgnext(!0):27===t&&o.close(l.index)}},l.tabimg=function(e){u.length<=1||(f.start=l.imgIndex-1,o.close(l.index),o.photos(t,!0,e))},l.event=function(){l.bigimg.hover(function(){l.imgsee.show()},function(){l.imgsee.hide()}),l.bigimg.find(".layui-layer-imgprev").on("click",function(e){e.preventDefault(),l.imgprev()}),l.bigimg.find(".layui-layer-imgnext").on("click",function(e){e.preventDefault(),l.imgnext()}),i(document).on("keyup",l.keyup)},l.loadi=o.load(1,{shade:"shade"in t?!1:.9,scrollbar:!1}),r(u[d].src,function(n){o.close(l.loadi),l.index=o.open(i.extend({type:1,area:function(){var a=[n.width,n.height],o=[i(e).width()-50,i(e).height()-50];return!t.full&&a[0]>o[0]&&(a[0]=o[0],a[1]=a[0]*n.height/n.width),[a[0]+"px",a[1]+"px"]}(),title:!1,shade:.9,shadeClose:!0,closeBtn:!1,move:".layui-layer-phimg img",moveType:1,scrollbar:!1,moveOut:!0,shift:5*Math.random()|0,skin:"layui-layer-photos"+c("photos"),content:'<div class="layui-layer-phimg"><img src="'+u[d].src+'" alt="'+(u[d].alt||"")+'" layer-pid="'+u[d].pid+'"><div class="layui-layer-imgsee">'+(u.length>1?'<span class="layui-layer-imguide"><a href="javascript:;" class="layui-layer-iconext layui-layer-imgprev"></a><a href="javascript:;" class="layui-layer-iconext layui-layer-imgnext"></a></span>':"")+'<div class="layui-layer-imgbar" style="display:'+(a?"block":"")+'"><span class="layui-layer-imgtit"><a href="javascript:;">'+(u[d].alt||"")+"</a><em>"+l.imgIndex+"/"+u.length+"</em></span></div></div></div>",success:function(e){l.bigimg=e.find(".layui-layer-phimg"),l.imgsee=e.find(".layui-layer-imguide,.layui-layer-imgbar"),l.event(e),t.tab&&t.tab(u[d],e)},end:function(){l.end=!0,i(document).off("keyup",l.keyup)}},t))},function(){o.close(l.loadi),o.msg("&#x5F53;&#x524D;&#x56FE;&#x7247;&#x5730;&#x5740;&#x5F02;&#x5E38;<br>&#x662F;&#x5426;&#x7EE7;&#x7EED;&#x67E5;&#x770B;&#x4E0B;&#x4E00;&#x5F20;&#xFF1F;",{time:3e4,btn:["&#x4E0B;&#x4E00;&#x5F20;","&#x4E0D;&#x770B;&#x4E86;"],yes:function(){u.length>1&&l.imgnext(!0,!0)}})})}},a.run=function(){i=jQuery,n=i(e),l.html=i("html"),o.open=function(e){var t=new r(e);return t.index}},"function"==typeof define?define(function(){return a.run(),o}):function(){a.run(),o.use("skin/layer.css")}()}(window);