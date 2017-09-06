function forgetPwd(){
    var params = {};
    var step = $('#step').val();
    params.modes = modes;
    params.step = step;
    params.loginName = $.trim($('#loginName').val());
    params.Checkcode = $('#checkCode').val();
    if(params.loginName==''){
    	WST.msg('请输入用户名','info');
    	WST.getVerify("#verifyImg1")
    	return;
    }
    params.verifyCode = $('#loginVerfy').val();

    $.post(WST.U('mobile/users/findPass'),params,function(data,textStatus){
      var json = WST.toJson(data);
      if(json.status=='1'){
	        	if(step==1){
		        	location.href=WST.U('mobile/users/forgetPasst','',true);
	        	}else if(step==2){
	        		if(modes==1){
	        			location.href=json.url;
	        		}else{
	        			disabledBtn();
	        		}
	        	}
      }else{
            WST.msg(json.msg,'info');
            WST.getVerify("#verifyImg1")
      }
    });
}

$(function(){
	// 弹出层
   $("#frame").css('top',0);
});

//弹框
function dataShow(type){
	if(type=="email"){
		$('#contentTitle').html('通过邮箱找回密码');
		$('.emailBox').show();
		$('.phoneBox').hide();
	}else if(type=="phone"){
		$('#contentTitle').html('通过手机找回密码');
		$('.emailBox').hide();
		$('.phoneBox').show();
	}else{
		return;
	}
    jQuery('#cover').attr("onclick","javascript:dataHide();").show();
    jQuery('#frame').animate({"right": 0}, 500);
}
function dataHide(){
    var dataHeight = $("#frame").css('height');
    var dataWidth = $("#frame").css('width');
    jQuery('#frame').animate({'right': '-'+dataWidth}, 500);
    jQuery('#cover').hide();
}



/* 手机找回 */
function forgetPhone(){
	var code = $.trim($('#checkCode').val());
	if(code!=''){
		forgetPwd()
	}else{
		WST.msg('请输入手机校验码','info');
		return false;
	}
}
/* 发送邮箱校验码 */
function forgetEmail(){
	modes = 0;
	var code = $.trim($('#loginVerfy').val());
	if(code != ''){
		forgetPwd();
	}else{
		WST.msg('请输入验证码','info');
		return false;
	}	
}
/* 提交邮箱验证码-重置密码 */
function resetByEmail(){
	var code = $.trim($('#emailCode').val());
	if(code == ''){
		WST.msg('请输入校验码','info');
		return false;
	}else{
		$.post(WST.U('mobile/users/forgetPasss'),{secretCode:code},function(data){
			var json = WST.toJson(data);
			if(json.status==-1){
				WST.msg('校验码错误','info');
				WST.getVerify("#verifyImg1")
				return false;
			}else{
				location.href=WST.U('mobile/users/resetPass','',true);
			}
		})
	}	
}

/*重置密码*/
function resetPwd(){
	var params = {};
	params.step = $('#step').val();
	params.loginPwd = $('#loginPwd').val();
	params.repassword = $('#repassword').val();
	$.post(WST.U('mobile/users/findPass'),params,function(data){
		var json = WST.toJson(data);
		if(json.status==1){
			WST.msg('重置成功');
			setTimeout(function(){
				location.href=WST.U('mobile/users/index');
			},1000)
		}else{
			WST.msg(json.msg,'info');
			return false;
		}
	})
}



var modes = '';

/** 校验码 **/
function phoneVerify2(){
	if(!$('#smsVerify').val()){
		// WST.msg('请输入验证码','info');
		$(".errorNotice").html("请输入验证码").slideDown('200').delay('1500').slideUp("200");
		$('#smsVerify').focus();
		return false;
	}

	modes = $('#modes').val();
	var time = 0;
	var isSend = false;
	var smsVerfy = $('#smsVerify').val();
	$.post(WST.U('mobile/users/getfindPhone'),{smsVerfy:smsVerfy},function(data,textStatus){
		var json = WST.toJson(data);
		if(isSend )return;
		isSend = true;
		// if(json.status!=1){
		// 	WST.msg(json.msg, 'info');
		// 	WST.getVerify('#verifyImg2');
		// 	time = 0;
		// 	isSend = false;
		// }
		// 修改代码
		if(json.status==-1){
			/*WST.msg(json.msg, 'info');*/
		$(".errorNotice").html("发送失败").slideDown('200').delay('1500').slideUp("200");
			WST.getVerify('#verifyImg2');
			time = 0;
			isSend = false;
		}
		else if(json.status==-2){
			$(".errorNotice").html("验证码输入错误").slideDown('200').delay('1500').slideUp("200");
		}

		if(json.status==1){
			// WST.msg('短信已发送，请查收');
			$(".errorNotice").html("短信已发送，请查收").slideDown('200').delay('1500').slideUp("200");
			time = 120;
			$('#timeObtain').attr('disabled', 'disabled').css('background','#e8e6e6');
			$('#timeObtain').html('获取验证码(120)').css('width','120px');
			var task = setInterval(function(){
				time--;
				$('#timeObtain').html('重发验证码('+time+"s)");
				if(time==0){
					isSend = false;						
					clearInterval(task);
					$('#timeObtain').html("重获验证码").css('width','100px');
					$('#timeObtain').removeAttr('disabled').css('background','#e23e3d');
				}
			},1000);
		}
	});
}
function disabledBtn(){
	time = 120;
	$('#emailBtn').attr('disabled', 'disabled');
	$('#emailBtn').html('发送验证邮件(120)');
	var task = setInterval(function(){
		time--;
		$('#emailBtn').html('重新发送('+time+")");
		if(time==0){
			isSend = false;						
			clearInterval(task);
			$('#emailBtn').html("发送验证邮件");
			$('#emailBtn').removeAttr('disabled');
		}
	},1000);
}