define("new/js/mod/scatter",["$"],function(i,t,n){function o(i){this.container=s(i),this.obj=s(i).find("li")}var s=i("$");n.exports=o,o.prototype.setInitialPosition=function(){this.obj,this.container;this.obj.each(function(){var i=s(this),t=s(this).position().top,n=s(this).position().left;i.css({left:"50%",top:"50%"}),i.animate({left:n,top:t},700)})},o.prototype.enlarge=function(){var i,t,n=0;this.obj.hover(function(){if(i=s(this).css("z-index"),t=s(this).outerWidth(),i_height=s(this).outerHeight(),!n){s(this).css({zIndex:101}),s(this).find("p").show(),n=1,s(this).stop().animate({width:"300px",marginLeft:-(300-t)/2,marginTop:-(300/t*i_height-i_height)/2,borderWidth:1,padding:5},700);{setTimeout(function(){n=0},700)}}},function(){s(this).css({zIndex:i}),s(this).find("p").hide(),s(this).animate({width:t,margin:0,borderWidth:0,padding:0},300)})}});