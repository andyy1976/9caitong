define("new/js/mod/goTop",["$"],function(o){var n=o("$");n("#goTop,.backTop").click(function(){return n("body,html").animate({scrollTop:0},1e3),!1})});