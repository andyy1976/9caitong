define("new/js/mod/util/jquery.prettyphoto",[],function(){!function(t){function i(){var e=location.href;return hashtag=-1!==e.indexOf("#prettyPhoto")?decodeURI(e.substring(e.indexOf("#prettyPhoto")+1,e.length)):!1,hashtag&&(hashtag=hashtag.replace(/<|>/g,"")),hashtag}function o(){"undefined"!=typeof theRel&&(location.hash=theRel+"/"+rel_index+"/")}function p(){-1!==location.href.indexOf("#prettyPhoto")&&(location.hash="prettyPhoto")}function a(e,t){e=e.replace(/[\[]/,"\\[").replace(/[\]]/,"\\]");var i="[\\?&]"+e+"=([^&#]*)",o=new RegExp(i),p=o.exec(t);return null==p?"":p[1]}t.prettyPhoto={version:"3.1.6"},t.fn.prettyPhoto=function(s){function n(){t(".pp_loaderIcon").hide(),projectedTop=scroll_pos.scrollTop+(I/2-v.containerHeight/2),0>projectedTop&&(projectedTop=0),$ppt.fadeTo(settings.animation_speed,1),$pp_pic_holder.find(".pp_content").animate({height:v.contentHeight,width:v.contentWidth},settings.animation_speed),$pp_pic_holder.animate({top:projectedTop,left:z/2-v.containerWidth/2<0?0:z/2-v.containerWidth/2,width:v.containerWidth},settings.animation_speed,function(){$pp_pic_holder.find(".pp_hoverContainer,#fullResImage").height(v.height).width(v.width),$pp_pic_holder.find(".pp_fade").fadeIn(settings.animation_speed),isSet&&"image"==c(pp_images[set_position])?$pp_pic_holder.find(".pp_hoverContainer").show():$pp_pic_holder.find(".pp_hoverContainer").hide(),settings.allow_expand&&(v.resized?t("a.pp_expand,a.pp_contract").show():t("a.pp_expand").hide()),!settings.autoplay_slideshow||$||y||t.prettyPhoto.startSlideshow(),settings.changepicturecallback(),y=!0}),u(),s.ajaxcallback()}function r(e){$pp_pic_holder.find("#pp_full_res object,#pp_full_res embed").css("visibility","hidden"),$pp_pic_holder.find(".pp_fade").fadeOut(settings.animation_speed,function(){t(".pp_loaderIcon").show(),e()})}function l(e){e>1?t(".pp_nav").show():t(".pp_nav").hide()}function d(e,t){if(resized=!1,h(e,t),imageWidth=e,imageHeight=t,(k>z||P>I)&&doresize&&settings.allow_resize&&!j){for(resized=!0,fitting=!1;!fitting;)k>z?(imageWidth=z-200,imageHeight=t/e*imageWidth):P>I?(imageHeight=I-200,imageWidth=e/t*imageHeight):fitting=!0,P=imageHeight,k=imageWidth;(k>z||P>I)&&d(k,P),h(imageWidth,imageHeight)}return{width:Math.floor(imageWidth),height:Math.floor(imageHeight),containerHeight:Math.floor(P),containerWidth:Math.floor(k)+2*settings.horizontal_padding,contentHeight:Math.floor(w),contentWidth:Math.floor(b),resized:resized}}function h(e,i){e=parseFloat(e),i=parseFloat(i),$pp_details=$pp_pic_holder.find(".pp_details"),$pp_details.width(e),detailsHeight=parseFloat($pp_details.css("marginTop"))+parseFloat($pp_details.css("marginBottom")),$pp_details=$pp_details.clone().addClass(settings.theme).width(e).appendTo(t("body")).css({position:"absolute",top:-1e4}),detailsHeight+=$pp_details.height(),detailsHeight=34>=detailsHeight?36:detailsHeight,$pp_details.remove(),$pp_title=$pp_pic_holder.find(".ppt"),$pp_title.width(e),titleHeight=parseFloat($pp_title.css("marginTop"))+parseFloat($pp_title.css("marginBottom")),$pp_title=$pp_title.clone().appendTo(t("body")).css({position:"absolute",top:-1e4}),titleHeight+=$pp_title.height(),$pp_title.remove(),w=i+detailsHeight,b=e,P=w+titleHeight+$pp_pic_holder.find(".pp_top").height()+$pp_pic_holder.find(".pp_bottom").height(),k=e}function c(e){return e.match(/youtube\.com\/watch/i)||e.match(/youtu\.be/i)?"youtube":e.match(/vimeo\.com/i)?"vimeo":e.match(/\b.mov\b/i)?"quicktime":e.match(/\b.swf\b/i)?"flash":e.match(/\biframe=true\b/i)?"iframe":e.match(/\bajax=true\b/i)?"ajax":e.match(/\bcustom=true\b/i)?"custom":"#"==e.substr(0,1)?"inline":"image"}function _(){if(doresize&&"undefined"!=typeof $pp_pic_holder){if(scroll_pos=g(),contentHeight=$pp_pic_holder.height(),contentwidth=$pp_pic_holder.width(),projectedTop=I/2+scroll_pos.scrollTop-contentHeight/2,0>projectedTop&&(projectedTop=0),contentHeight>I)return;$pp_pic_holder.css({top:projectedTop,left:z/2+scroll_pos.scrollLeft-contentwidth/2})}}function g(){return self.pageYOffset?{scrollTop:self.pageYOffset,scrollLeft:self.pageXOffset}:document.documentElement&&document.documentElement.scrollTop?{scrollTop:document.documentElement.scrollTop,scrollLeft:document.documentElement.scrollLeft}:document.body?{scrollTop:document.body.scrollTop,scrollLeft:document.body.scrollLeft}:void 0}function m(){I=t(window).height(),z=t(window).width(),"undefined"!=typeof $pp_overlay&&$pp_overlay.height(t(document).height()).width(z)}function u(){isSet&&settings.overlay_gallery&&"image"==c(pp_images[set_position])?(itemWidth=57,navWidth="facebook"==settings.theme||"pp_default"==settings.theme?50:30,itemsPerPage=Math.floor((v.containerWidth-100-navWidth)/itemWidth),itemsPerPage=itemsPerPage<pp_images.length?itemsPerPage:pp_images.length,totalPage=Math.ceil(pp_images.length/itemsPerPage)-1,0==totalPage?(navWidth=0,$pp_gallery.find(".pp_arrow_next,.pp_arrow_previous").hide()):$pp_gallery.find(".pp_arrow_next,.pp_arrow_previous").show(),galleryWidth=itemsPerPage*itemWidth,fullGalleryWidth=pp_images.length*itemWidth,$pp_gallery.css("margin-left",-(galleryWidth/2+navWidth/2)).find("div:first").width(galleryWidth+5).find("ul").width(fullGalleryWidth).find("li.selected").removeClass("selected"),goToPage=Math.floor(set_position/itemsPerPage)<totalPage?Math.floor(set_position/itemsPerPage):totalPage,t.prettyPhoto.changeGalleryPage(goToPage),$pp_gallery_li.filter(":eq("+set_position+")").addClass("selected")):$pp_pic_holder.find(".pp_content").unbind("mouseenter mouseleave")}function f(){if(settings.social_tools&&(facebook_like_link=settings.social_tools.replace("{location_href}",encodeURIComponent(location.href))),settings.markup=settings.markup.replace("{pp_social}",""),t("body").append(settings.markup),$pp_pic_holder=t(".pp_pic_holder"),$ppt=t(".ppt"),$pp_overlay=t("div.pp_overlay"),isSet&&settings.overlay_gallery){currentGalleryPage=0,toInject="";for(var e=0;e<pp_images.length;e++)pp_images[e].match(/\b(jpg|jpeg|png|gif)\b/gi)?(classname="",img_src=pp_images[e]):(classname="default",img_src=""),toInject+="<li class='"+classname+"'><a href='#'><img src='"+img_src+"' width='50' alt='' /></a></li>";toInject=settings.gallery_markup.replace(/{gallery}/g,toInject),$pp_pic_holder.find("#pp_full_res").after(toInject),$pp_gallery=t(".pp_pic_holder .pp_gallery"),$pp_gallery_li=$pp_gallery.find("li"),$pp_gallery.find(".pp_arrow_next").click(function(){return t.prettyPhoto.changeGalleryPage("next"),t.prettyPhoto.stopSlideshow(),!1}),$pp_gallery.find(".pp_arrow_previous").click(function(){return t.prettyPhoto.changeGalleryPage("previous"),t.prettyPhoto.stopSlideshow(),!1}),$pp_pic_holder.find(".pp_content").hover(function(){$pp_pic_holder.find(".pp_gallery:not(.disabled)").fadeIn()},function(){$pp_pic_holder.find(".pp_gallery:not(.disabled)").fadeOut()}),itemWidth=57,$pp_gallery_li.each(function(e){t(this).find("a").click(function(){return t.prettyPhoto.changePage(e),t.prettyPhoto.stopSlideshow(),!1})})}settings.slideshow&&($pp_pic_holder.find(".pp_nav").prepend('<a href="#" class="pp_play">Play</a>'),$pp_pic_holder.find(".pp_nav .pp_play").click(function(){return t.prettyPhoto.startSlideshow(),!1})),$pp_pic_holder.attr("class","pp_pic_holder "+settings.theme),$pp_overlay.css({opacity:0,height:t(document).height(),width:t(window).width()}).bind("click",function(){settings.modal||t.prettyPhoto.close()}),t("a.pp_close").bind("click",function(){return t.prettyPhoto.close(),!1}),settings.allow_expand&&t("a.pp_expand").bind("click",function(){return t(this).hasClass("pp_expand")?(t(this).removeClass("pp_expand").addClass("pp_contract"),doresize=!1):(t(this).removeClass("pp_contract").addClass("pp_expand"),doresize=!0),r(function(){t.prettyPhoto.open()}),!1}),$pp_pic_holder.find(".pp_previous, .pp_nav .pp_arrow_previous").bind("click",function(){return t.prettyPhoto.changePage("previous"),t.prettyPhoto.stopSlideshow(),!1}),$pp_pic_holder.find(".pp_next, .pp_nav .pp_arrow_next").bind("click",function(){return t.prettyPhoto.changePage("next"),t.prettyPhoto.stopSlideshow(),!1}),_()}s=jQuery.extend({hook:"rel",animation_speed:"fast",ajaxcallback:function(){},slideshow:5e3,autoplay_slideshow:!1,opacity:.8,show_title:!0,allow_resize:!0,allow_expand:!0,default_width:500,default_height:344,counter_separator_label:"/",theme:"jct",horizontal_padding:20,hideflash:!1,wmode:"opaque",autoplay:!1,modal:!1,deeplinking:!0,overlay_gallery:!1,overlay_gallery_max:30,keyboard_shortcuts:!1,changepicturecallback:function(){},callback:function(){},ie6_fallback:!0,markup:'<div class="pp_pic_holder"> 						<div class="ppt">&nbsp;</div> 						<div class="pp_content_container"> 							<div class="pp_right"> 								<div class="pp_content"> 									<div class="pp_loaderIcon"></div> 									<div class="pp_fade"> 										<div class="pp_hoverContainer"> 											<a class="pp_next" href="#">next</a> 											<a class="pp_previous" href="#">previous</a> 										</div> 										<div id="pp_full_res"></div> 										<div class="pp_details"> 											<p class="pp_description"></p> 										</div> 									</div> 								</div> 							</div> 							</div> 						</div> 					</div> 					<div class="pp_overlay"></div>',gallery_markup:'<div class="pp_gallery"> 								<a href="#" class="pp_arrow_previous">Previous</a> 								<div> 									<ul> 										{gallery} 									</ul> 								</div> 								<a href="#" class="pp_arrow_next">Next</a> 							</div>',image_markup:'<img id="fullResImage" src="{path}" />',flash_markup:'<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{width}" height="{height}"><param name="wmode" value="{wmode}" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="{path}" /><embed src="{path}" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="{width}" height="{height}" wmode="{wmode}"></embed></object>',quicktime_markup:'<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" height="{height}" width="{width}"><param name="src" value="{path}"><param name="autoplay" value="{autoplay}"><param name="type" value="video/quicktime"><embed src="{path}" height="{height}" width="{width}" autoplay="{autoplay}" type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/"></embed></object>',iframe_markup:'<iframe src ="{path}" width="{width}" height="{height}" frameborder="no"></iframe>',inline_markup:'<div class="pp_inline">{content}</div>',custom_markup:"",social_tools:'<div class="twitter"><a href="http://twitter.com/share" class="twitter-share-button" data-count="none">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script></div><div class="facebook"><iframe src="//www.facebook.com/plugins/like.php?locale=en_US&href={location_href}&amp;layout=button_count&amp;show_faces=true&amp;width=500&amp;action=like&amp;font&amp;colorscheme=light&amp;height=23" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:500px; height:23px;" allowTransparency="true"></iframe></div>'},s);var v,y,w,b,P,k,$,x=this,j=!1,I=t(window).height(),z=t(window).width();return doresize=!0,scroll_pos=g(),t(window).unbind("resize.prettyphoto").bind("resize.prettyphoto",function(){_(),m()}),s.keyboard_shortcuts&&t(document).unbind("keydown.prettyphoto").bind("keydown.prettyphoto",function(e){if("undefined"!=typeof $pp_pic_holder&&$pp_pic_holder.is(":visible"))switch(e.keyCode){case 37:t.prettyPhoto.changePage("previous"),e.preventDefault();break;case 39:t.prettyPhoto.changePage("next"),e.preventDefault();break;case 27:settings.modal||t.prettyPhoto.close(),e.preventDefault()}}),t.prettyPhoto.initialize=function(){return settings=s,"pp_default"==settings.theme&&(settings.horizontal_padding=16),theRel=t(this).attr(settings.hook),galleryRegExp=/\[(?:.*)\]/,isSet=galleryRegExp.exec(theRel)?!0:!1,pp_images=isSet?jQuery.map(x,function(e){return-1!=t(e).attr(settings.hook).indexOf(theRel)?t(e).attr("href"):void 0}):t.makeArray(t(this).attr("href")),pp_titles=isSet?jQuery.map(x,function(e){return-1!=t(e).attr(settings.hook).indexOf(theRel)?t(e).find("img").attr("alt")?t(e).find("img").attr("alt"):"":void 0}):t.makeArray(t(this).find("img").attr("alt")),pp_descriptions=isSet?jQuery.map(x,function(e){return-1!=t(e).attr(settings.hook).indexOf(theRel)?t(e).attr("title")?t(e).attr("title"):"":void 0}):t.makeArray(t(this).attr("title")),pp_images.length>settings.overlay_gallery_max&&(settings.overlay_gallery=!1),set_position=jQuery.inArray(t(this).attr("href"),pp_images),rel_index=isSet?set_position:t("a["+settings.hook+"^='"+theRel+"']").index(t(this)),f(this),settings.allow_resize&&t(window).bind("scroll.prettyphoto",function(){_()}),t.prettyPhoto.open(),!1},t.prettyPhoto.open=function(e){return"undefined"==typeof settings&&(settings=s,pp_images=t.makeArray(arguments[0]),pp_titles=t.makeArray(arguments[1]?arguments[1]:""),pp_descriptions=t.makeArray(arguments[2]?arguments[2]:""),isSet=pp_images.length>1?!0:!1,set_position=arguments[3]?arguments[3]:0,f(e.target)),settings.hideflash&&t("object,embed,iframe[src*=youtube],iframe[src*=vimeo]").css("visibility","hidden"),l(t(pp_images).size()),t(".pp_loaderIcon").show(),settings.deeplinking&&o(),settings.social_tools&&(facebook_like_link=settings.social_tools.replace("{location_href}",encodeURIComponent(location.href)),$pp_pic_holder.find(".pp_social").html()),$ppt.is(":hidden")&&$ppt.css("opacity",0).show(),$pp_overlay.show().fadeTo(settings.animation_speed,settings.opacity),$pp_pic_holder.find(".currentTextHolder").text(set_position+1+settings.counter_separator_label+t(pp_images).size()),"undefined"!=typeof pp_descriptions[set_position]&&""!=pp_descriptions[set_position]?$pp_pic_holder.find(".pp_description").show().html(unescape(pp_descriptions[set_position])):$pp_pic_holder.find(".pp_description").hide(),movie_width=parseFloat(a("width",pp_images[set_position]))?a("width",pp_images[set_position]):settings.default_width.toString(),movie_height=parseFloat(a("height",pp_images[set_position]))?a("height",pp_images[set_position]):settings.default_height.toString(),j=!1,-1!=movie_height.indexOf("%")&&(movie_height=parseFloat(t(window).height()*parseFloat(movie_height)/100-150),j=!0),-1!=movie_width.indexOf("%")&&(movie_width=parseFloat(t(window).width()*parseFloat(movie_width)/100-150),j=!0),$pp_pic_holder.fadeIn(function(){switch($ppt.html(settings.show_title&&""!=pp_titles[set_position]&&"undefined"!=typeof pp_titles[set_position]?unescape(pp_titles[set_position]):"&nbsp;"),imgPreloader="",skipInjection=!1,c(pp_images[set_position])){case"image":imgPreloader=new Image,nextImage=new Image,isSet&&set_position<t(pp_images).size()-1&&(nextImage.src=pp_images[set_position+1]),prevImage=new Image,isSet&&pp_images[set_position-1]&&(prevImage.src=pp_images[set_position-1]),$pp_pic_holder.find("#pp_full_res")[0].innerHTML=settings.image_markup.replace(/{path}/g,pp_images[set_position]),imgPreloader.onload=function(){v=d(imgPreloader.width,imgPreloader.height),n()},imgPreloader.onerror=function(){alert("Image cannot be loaded. Make sure the path is correct and image exist."),t.prettyPhoto.close()},imgPreloader.src=pp_images[set_position];break;case"youtube":v=d(movie_width,movie_height),movie_id=a("v",pp_images[set_position]),""==movie_id&&(movie_id=pp_images[set_position].split("youtu.be/"),movie_id=movie_id[1],movie_id.indexOf("?")>0&&(movie_id=movie_id.substr(0,movie_id.indexOf("?"))),movie_id.indexOf("&")>0&&(movie_id=movie_id.substr(0,movie_id.indexOf("&")))),movie="http://www.youtube.com/embed/"+movie_id,movie+=a("rel",pp_images[set_position])?"?rel="+a("rel",pp_images[set_position]):"?rel=1",settings.autoplay&&(movie+="&autoplay=1"),toInject=settings.iframe_markup.replace(/{width}/g,v.width).replace(/{height}/g,v.height).replace(/{wmode}/g,settings.wmode).replace(/{path}/g,movie);break;case"vimeo":v=d(movie_width,movie_height),movie_id=pp_images[set_position];var e=/http(s?):\/\/(www\.)?vimeo.com\/(\d+)/,i=movie_id.match(e);movie="http://player.vimeo.com/video/"+i[3]+"?title=0&amp;byline=0&amp;portrait=0",settings.autoplay&&(movie+="&autoplay=1;"),vimeo_width=v.width+"/embed/?moog_width="+v.width,toInject=settings.iframe_markup.replace(/{width}/g,vimeo_width).replace(/{height}/g,v.height).replace(/{path}/g,movie);break;case"quicktime":v=d(movie_width,movie_height),v.height+=15,v.contentHeight+=15,v.containerHeight+=15,toInject=settings.quicktime_markup.replace(/{width}/g,v.width).replace(/{height}/g,v.height).replace(/{wmode}/g,settings.wmode).replace(/{path}/g,pp_images[set_position]).replace(/{autoplay}/g,settings.autoplay);break;case"flash":v=d(movie_width,movie_height),flash_vars=pp_images[set_position],flash_vars=flash_vars.substring(pp_images[set_position].indexOf("flashvars")+10,pp_images[set_position].length),filename=pp_images[set_position],filename=filename.substring(0,filename.indexOf("?")),toInject=settings.flash_markup.replace(/{width}/g,v.width).replace(/{height}/g,v.height).replace(/{wmode}/g,settings.wmode).replace(/{path}/g,filename+"?"+flash_vars);break;case"iframe":v=d(movie_width,movie_height),frame_url=pp_images[set_position],frame_url=frame_url.substr(0,frame_url.indexOf("iframe")-1),toInject=settings.iframe_markup.replace(/{width}/g,v.width).replace(/{height}/g,v.height).replace(/{path}/g,frame_url);break;case"ajax":doresize=!1,v=d(movie_width,movie_height),doresize=!0,skipInjection=!0,t.get(pp_images[set_position],function(e){toInject=settings.inline_markup.replace(/{content}/g,e),$pp_pic_holder.find("#pp_full_res")[0].innerHTML=toInject,n()});break;case"custom":v=d(movie_width,movie_height),toInject=settings.custom_markup;break;case"inline":myClone=t(pp_images[set_position]).clone().append('<br clear="all" />').css({width:settings.default_width}).wrapInner('<div id="pp_full_res"><div class="pp_inline"></div></div>').appendTo(t("body")).show(),doresize=!1,v=d(t(myClone).width(),t(myClone).height()),doresize=!0,t(myClone).remove(),toInject=settings.inline_markup.replace(/{content}/g,t(pp_images[set_position]).html())}imgPreloader||skipInjection||($pp_pic_holder.find("#pp_full_res")[0].innerHTML=toInject,n())}),!1},t.prettyPhoto.changePage=function(e){currentGalleryPage=0,"previous"==e?(set_position--,0>set_position&&(set_position=t(pp_images).size()-1)):"next"==e?(set_position++,set_position>t(pp_images).size()-1&&(set_position=0)):set_position=e,rel_index=set_position,doresize||(doresize=!0),settings.allow_expand&&t(".pp_contract").removeClass("pp_contract").addClass("pp_expand"),r(function(){t.prettyPhoto.open()})},t.prettyPhoto.changeGalleryPage=function(e){"next"==e?(currentGalleryPage++,currentGalleryPage>totalPage&&(currentGalleryPage=0)):"previous"==e?(currentGalleryPage--,0>currentGalleryPage&&(currentGalleryPage=totalPage)):currentGalleryPage=e,slide_speed="next"==e||"previous"==e?settings.animation_speed:0,slide_to=currentGalleryPage*itemsPerPage*itemWidth,$pp_gallery.find("ul").animate({left:-slide_to},slide_speed)},t.prettyPhoto.startSlideshow=function(){"undefined"==typeof $?($pp_pic_holder.find(".pp_play").unbind("click").removeClass("pp_play").addClass("pp_pause").click(function(){return t.prettyPhoto.stopSlideshow(),!1}),$=setInterval(t.prettyPhoto.startSlideshow,settings.slideshow)):t.prettyPhoto.changePage("next")},t.prettyPhoto.stopSlideshow=function(){$pp_pic_holder.find(".pp_pause").unbind("click").removeClass("pp_pause").addClass("pp_play").click(function(){return t.prettyPhoto.startSlideshow(),!1}),clearInterval($),$=void 0},t.prettyPhoto.close=function(){$pp_overlay.is(":animated")||(t.prettyPhoto.stopSlideshow(),$pp_pic_holder.stop().find("object,embed").css("visibility","hidden"),t("div.pp_pic_holder,div.ppt,.pp_fade").fadeOut(settings.animation_speed,function(){t(this).remove()}),$pp_overlay.fadeOut(settings.animation_speed,function(){settings.hideflash&&t("object,embed,iframe[src*=youtube],iframe[src*=vimeo]").css("visibility","visible"),t(this).remove(),t(window).unbind("scroll.prettyphoto"),p(),settings.callback(),doresize=!0,y=!1,delete settings}))},!e&&i()&&(e=!0,hashIndex=i(),hashRel=hashIndex,hashIndex=hashIndex.substring(hashIndex.indexOf("/")+1,hashIndex.length-1),hashRel=hashRel.substring(0,hashRel.indexOf("/")),setTimeout(function(){t("a["+s.hook+"^='"+hashRel+"']:eq("+hashIndex+")").trigger("click")},50)),this.unbind("click.prettyphoto").bind("click.prettyphoto",t.prettyPhoto.initialize)}}(jQuery);var e=!0});