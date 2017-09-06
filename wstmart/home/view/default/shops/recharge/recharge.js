
function getPayUrl(){
	var params = {};
		params.payObj = "recharge";
		params.targetType = 1;
		params.needPay = $.trim($("#needPay").val());
		params.payCode = $.trim($("#payCode").val());
	if(params.needPay<=0){
		WST.msg('请输入充值金额', {icon: 5});
		return;
	}
	if(params.payCode==""){
		WST.msg('请先选择支付方式', {icon: 5});
		return;
	}
	jQuery.post(WST.U('home/'+params.payCode+'/get'+params.payCode+"URL"),params,function(data) {
		var json = WST.toJson(data);
		if(json.status==1){
			if(params.payCode=="weixinpays"){
				location.href = json.url;
			}else{
				window.open(json.url);
			}
		}else{
			WST.msg('充值失败', {icon: 5});
		}
	});
}