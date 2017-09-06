function submitOrder(){
	var params = WST.getParams('.j-ipt');
	params.isUseScore = $('#isUseScore').prop('checked')?1:0
	var load = WST.load({msg:'正在提交，请稍后...'});
	$.post(WST.U('home/orders/quickSubmit'),params,function(data,textStatus){
		layer.close(load);   
		var json = WST.toJson(data);
	    if(json.status==1){
	    	 WST.msg(json.msg,{icon:1},function(){
	    		 location.href=WST.U('home/orders/succeed','orderNo='+json.data);
	    	 });
	    }else{
	    	WST.msg(json.msg,{icon:2});
	    }
	});
}

function inEffect(obj,n){
	$(obj).addClass('j-selected').siblings('.wst-frame'+n).removeClass('j-selected');
}

function changeInvoice(t,str,obj){
	WST.showHide(t,str);
	changeSelected(t,'isInvoice',obj);
}
function changeSelected(n,index,obj){
	$('#'+index).val(n);
	inEffect(obj,2);
}
function getCartMoney(){
	var params = {};
	params.isUseScore = $('#isUseScore').prop('checked')?1:0;
	params.useScore = $('#useScore').val();
	params.rnd = Math.random();
	params.deliverType = 1;
	var load = WST.load({msg:'正在计算订单价格，请稍后...'});
	$.post(WST.U('home/carts/getQuickCartMoney'),params,function(data,textStatus){
		layer.close(load);  
		var json = WST.toJson(data);
		if(json.status==1){
		    json = json.data;
		    $('#useScore').val(json.useScore);
		    $('#scoreMoney2').html(json.scoreMoney);
		 	$('#totalMoney').html(json.total);
		}
	});
}
function checkScoreBox(v){
    if(v){
    	var val = $('#isUseScore').attr('dataval');
    	$('#useScore').val(val);
        $('#scoreMoney').show();
    }else{
    	$('#scoreMoney').hide();
    }
    getCartMoney();
}