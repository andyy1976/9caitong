define("new/js/mod/util/verify",[],function(d,e){e.verify_IDcard=function(d){var e=/^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/;return e.test(d)},e.verify_phone=function(d){var e=/^1[3|4|5|7|8]\d{9}$/;return e.test(d)}});