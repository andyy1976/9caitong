define("new/js/page/index",["$","new/js/mod/top","new/js/mod/AppAtlas","new/js/mod/goTop","new/js/mod/custom","new/js/mod/login","new/js/mod/scollAnimition","new/js/mod/DataChange"],function(o){var n=o("$");o("new/js/mod/top"),o("new/js/mod/AppAtlas"),o("new/js/mod/goTop"),o("new/js/mod/custom"),o("new/js/mod/login"),o("new/js/mod/scollAnimition");var e=o("new/js/mod/DataChange"),s=new e("#total"),t=new e("#benefit");s.setzero(),t.setzero(),n(document).ready(function(){var o=n("section.data").offset().top,e=n(window).height(),a=o-e;n(window).scrollTop()>a+110?(s.change(),t.change()):n(window).scroll(function(){var o=n(window).scrollTop();o>a+110&&(s.change(),t.change())})})});