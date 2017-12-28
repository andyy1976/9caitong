/**
 * Created by admin on 2016/8/1.
 */
$(function(){
    var code ; //在全局定义验证码
    //产生验证码
    function createCode(){
        code = "";
        var codeLength = 4;
        var checkCode =$('#code');
        var random = new Array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R',
            'S','T','U','V','W','X','Y','Z');
        for(var i = 0; i < codeLength; i++) {
            var index = Math.floor(Math.random()*36);
            code += random[index];
        }
        checkCode.val(code);
    }
    function validate(){
        var inputCode =$('#input').val().toUpperCase();
        if(inputCode.length <= 0) {
        }
        else if(inputCode != code ) {
            createCode();
            $('#input').val('');
        }
        else {
            $('#code').attr('data-Code',code);
        }
    }
    createCode();
    $('#code').on('click',function(){
        createCode();
    })
})