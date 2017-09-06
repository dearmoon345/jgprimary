function login(){
	var loginName = $('#loginName').val();
	var loginPwd = $('#loginPwd').val();
	var loginVerfy = $('#loginVerfy').val();
	if(loginName==''){
    	WST.msg('请输入账号','info');
        return false;
    }
	if(loginPwd==''){
    	WST.msg('请输入密码','info');
        return false;
    }
	if(loginVerfy==''){
    	WST.msg('请输入验证码','info');
        return false;
    }
	WST.load('绑定中···');
    var param = {};
    param.loginName = loginName;
    param.loginPwd = loginPwd;
    param.verifyCode = loginVerfy;
	$('#loginButton').addClass("active").attr('disabled', 'disabled');
    $.post(WST.U('mobile/users/checkLogin'), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
        	WST.msg(json.msg,'success');
            setTimeout(function(){
            	location.href = WST.U('mobile/users/index');
            },2000);
        }else{
        	WST.msg(json.msg,'warn');
        	WST.getVerify("#verifyImg1");
        	$('#loginButton').removeAttr('disabled').removeClass("active");
        }
        WST.noload();
        data = json = null;
    });
}
var nameType = 2;
function onTesting(obj){
	var data = $(obj).val();
	var  regEmail = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	var  regMobile = /^0?1[3|4|5|8][0-9]\d{8}$/;
	if(regEmail.test(data)) {//邮箱
		nameType = 1;
		WST.showHide('','.phone');
		WST.showHide(1,'#mailbox');
	}else if(regMobile.test(data)){//手机
		nameType = 3;
		if(window.conf.SMS_OPEN==1){
			WST.showHide('','#mailbox');
			WST.showHide(1,'.phone');
		}
	}else{
		nameType = 2;
		WST.showHide('','.phone');
		WST.showHide(1,'#mailbox');
	}
}
function register(){
	var regName = $('#regName').val();
	var regPwd = $('#regPwd').val();
	var regcoPwd = $('#regcoPwd').val();
	var regVerfy = $('#regVerfy').val();
	var phoneCode = $('#phoneCode').val();
	
	var regCheck = $('#vip_box').is(':checked');
	if(!regCheck){
			WST.msg('请查看隐私协议','info');
	        return false;
		}

    var param = {};
	if(regName==''){
    	WST.msg('请输入账号','info');
        return false;
    }
	if(regPwd==''){
    	WST.msg('请输入密码','info');
        return false;
    }
	if(regPwd.length < 6 || regPwd.length > 16){
	    WST.msg('请输入密码为6-16位字符','info');
	    return false;
	}
	if(regcoPwd==''){
    	WST.msg('确认密码不能为空','info');
        return false;
    }
	if(regPwd!=regcoPwd){
    	WST.msg('确认密码不一致','info');
        return false;
    }
	if(nameType==2 || nameType==1){
		if(regVerfy==''){
	    	WST.msg('请输入验证码','info');
	        return false;
	    }
		param.verifyCode = regVerfy;
	}else{
		if(window.conf.SMS_OPEN==1 && phoneCode==''){
			WST.msg('请输入短信验证码','info');
		}
		param.mobileCode = phoneCode;
	}

	WST.load('注册中···');
    param.nameType = nameType;
    param.loginName = regName;
    param.loginPwd = regcoPwd;
    param.reUserPwd = regPwd;
	$('#regButton').addClass("active").attr('disabled', 'disabled');
    $.post(WST.U('mobile/users/register'), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
        	WST.msg(json.msg,'success');
            setTimeout(function(){
            	location.href = WST.U('mobile/users/index');
            },2000);
        }else{
        	WST.msg(json.msg,'warn');
            WST.getVerify("#verifyImg0");
        	$('#regButton').removeAttr('disabled').removeClass("active");
        }
        WST.noload();
        data = json = null;
    });
}
var time = 0;
var isSend = false;
function obtainCode(){
	var userPhone = $('#regName').val();
    if(userPhone ==''){
    	WST.msg('请输入帐号为手机号码','info');
	    $('#userPhone').focus();
        return false;
    }
	if(window.conf.SMS_VERFY==1){
		var smsVerfy = $('#smsVerfy').val();
	    if(smsVerfy ==''){
	    	WST.msg('请输入验证码','info');
		    $('#smsVerfy').focus();
	        return false;
	    }
	}
    var param = {};
	param.userPhone = userPhone;
	param.smsVerfy = smsVerfy;
	if(isSend)return;
	isSend = true;
    $.post(WST.U('mobile/users/getPhoneVerifyCode'), param, function(data){
        var json = WST.toJson(data);
        if( json.status == 1 ){
        	WST.msg(json.msg,'success');
			time = 120;
			$('#obtain').attr('disabled', 'disabled').html('120秒获取');
			var task = setInterval(function(){
				time--;
				$('#obtain').html(''+time+"秒获取");
				if(time==0){
					isSend = false;
					clearInterval(task);
					$('#obtain').removeAttr('disabled').html("重新发送");
				}
			},1000);
        }else{
        	WST.msg(json.msg,'warn');
        	WST.getVerify("#verifyImg3");
        	isSend = false;
        }
        data = json = null;
    });
}