function checkChks(obj,cobj){

	WST.checkChks(obj,cobj);
	$(cobj).each(function(){
		id = $(this).val();

		if(obj.checked){
			$(this).addClass('selected');
			
		}else{
			$(this).removeClass('selected');
			
		}

		var goods_checkbox = $(obj).closest('.cart-item').find('.j-gchk');
		var goods_checked = $(obj).closest('.cart-item').find('.j-gchk:checked');
		var shop_checked = $(obj).closest('.cart-item').find('.j-ch');
		
		if(goods_checkbox.length == goods_checked.length){

			shop_checked.prop('checked',true);
			if($('.j-ch').length == $('.j-ch:checked').length){
				$('.j-chh').prop('checked',true);
			}else{
				$('.j-chh').prop('checked',false);
			}
			
		}else{
			shop_checked.prop('checked',false);
			$('.j-chh').prop('checked',false);
		}



		



		var cid = $(this).find(".j-chk").val();

		if(cid!=''){
		    WST.changeCartGoods(cid,$('#buyNum_'+cid).val(),obj.checked?1:0);
		    statCartMoney();
	    }
	})

}
function statCartMoney(){
	var cartMoney = 0,goodsTotalPrice,id;
	$('.j-gchk').each(function(){
		id = $(this).val();

		goodsTotalPrice = parseFloat($(this).attr('mval')) * parseInt($('#buyNum_'+id).val());
		goodsTotalPrice = parseFloat(goodsTotalPrice).toFixed(2);
		$('#tprice_'+id).html(goodsTotalPrice);
		if($(this).prop('checked')){	
			cartMoney = cartMoney + goodsTotalPrice;
		}
	});
	cartMoney = parseFloat(cartMoney).toFixed(2);
	$('#totalMoney').html(cartMoney);
	checkGoodsBuyStatus();
}
function checkGoodsBuyStatus(){
	var cartNum = 0,stockNum = 0,cartId = 0;
	$('.j-gchk').each(function(){
		cartId = $(this).val();
		cartNum = parseInt($('#buyNum_'+cartId).val(),10);
		stockNum = parseInt($(this).attr('sval'),10);
	
		if(stockNum < 0 || stockNum < cartNum){
			if($(this).prop('checked')){
				$(this).parent().parent().css('border','2px solid red');
			}else{
				$(this).parent().parent().css('border','0px solid #eeeeee');
				$(this).parent().parent().css('border-bottom','1px solid #eeeeee');
			}
			if(stockNum < 0){
				$('#gchk_'+cartId).attr('allowbuy',0);
				$('#err_'+cartId).css('color','red').html('库存不足');
			}else{
				$('#gchk_'+cartId).attr('allowbuy',1);
				$('#err_'+cartId).css('color','red').html('购买量超过库存');
			}
		}else{
			$('#gchk_'+cartId).attr('allowbuy',10);
			$(this).parent().parent().css('border','0px solid #eeeeee');
			$(this).parent().parent().css('border-bottom','1px solid #eeeeee');
			$('#err_'+cartId).html('');
		}
	});
}
function toSettlement(){
	// alert(1111);
	//添加代码start     商城改成虚拟交易
	//判断是否为  测试人员账号
	$.post(WST.U('home/users/ceshiUser'),function(data){
		var json = WST.toJson(data);
		if(json.status==-1){
			WST.msg('商城正在测试中，暂时无法结算');
		    return;
		}else{
			var isChk = false;
			$('.j-gchk').each(function(){
				if($(this).prop('checked'))isChk = true;
			});
			if(!isChk){
				WST.msg('请选择要结算的商品!',{icon:1});
				return;
			}
			var msg = '';
			$('.j-gchk').each(function(){
				if($(this).prop('checked')){
					if($(this).attr('allowbuy')==0){
						msg = '所选商品库存不足';
						return;
					}else if($(this).attr('allowbuy')==1){
						msg = '所选商品购买量大于商品库存';
						return;
					}
				}
			})
			if(msg!=''){
				WST.msg(msg,{icon:2});
				return;
			}
			location.href=WST.U('home/carts/settlement');
		}
	});
	//添加代码end
	
	// var isChk = false;
	// $('.j-gchk').each(function(){
	// 	if($(this).prop('checked'))isChk = true;
	// });
	// if(!isChk){
	// 	WST.msg('请选择要结算的商品!',{icon:1});
	// 	return;
	// }
	// var msg = '';
	// $('.j-gchk').each(function(){
	// 	if($(this).prop('checked')){
	// 		if($(this).attr('allowbuy')==0){
	// 			msg = '所选商品库存不足';
	// 			return;
	// 		}else if($(this).attr('allowbuy')==1){
	// 			msg = '所选商品购买量大于商品库存';
	// 			return;
	// 		}
	// 	}
	// })
	// if(msg!=''){
	// 	WST.msg(msg,{icon:2});
	// 	return;
	// }
	// location.href=WST.U('home/carts/settlement');

}

function addrBoxOver(t){
	$(t).addClass('radio-box-hover');
	$(t).find('.operate-box').show();
}
function addrBoxOut(t){
	$(t).removeClass('radio-box-hover');
	$(t).find('.operate-box').hide();
}



function setDeaultAddr(id){
	$.post(WST.U('home/useraddress/setDefault'),{id:id},function(data){
		var json = WST.toJson(data);
		if(json.status==1){
			getAddressList();
			changeAddrId(id);
		}
	});
}


function changeAddrId(id){
	$.post(WST.U('home/useraddress/getById'),{id:id},function(data){
		var json = WST.toJson(data);

		var userPhone = json.data.userPhone;
      	$('#s_userPhone').attr('value',userPhone);
      	
		if(json.status==1){
			inEffect($('#addr-'+id),1);
			$('#s_addressId').val(json.data.addressId);
			$("select[id^='area_0_']").remove();
			var areaIdPath = json.data.areaIdPath.split("_");
			// 设置收货地区市级id
			$('#s_areaId').val(areaIdPath[1]);
             
	     	$('#area_0').val(areaIdPath[0]);
	     	// 计算运费
			getCartMoney();
	     	var aopts = {id:'area_0',val:areaIdPath[0],childIds:areaIdPath,className:'j-areas'}
	 		WST.ITSetAreas(aopts);
			WST.setValues(json.data);
		}
	})
}

function delAddr(id){
	WST.confirm({content:'您确定要删除该地址吗？',yes:function(index){
		$.post(WST.U('home/useraddress/del'),{id:id},function(data,textStatus){
		     var json = WST.toJson(data);
		     if(json.status==1){
		    	 WST.msg(json.msg,{icon:1});
		    	 getAddressList();
		     }else{
		    	 WST.msg(json.msg,{icon:2});
		     }
		});
	}});
}

function getAddressList(obj){
	var id = $('#s_addressId').val();
	var load = WST.load({msg:'正在加载记录，请稍后...'});
	$.post(WST.U('home/useraddress/listQuery'),{rnd:Math.random()},function(data,textStatus){
		 layer.close(load);
	     var json = WST.toJson(data);
	     if(json.status==1){
	    	 if(json.data && json.data && json.data.length){
	    		 var html = [],tmp;
	    		 for(var i=0;i<json.data.length;i++){
	    			 tmp = json.data[i];
	    			 var selected = (id==tmp.addressId)?'j-selected':'';
	    			 html.push(
	    					 '<div class="wst-frame1 '+selected+'" onclick="javascript:changeAddrId('+tmp.addressId+')" id="addr-'+tmp.addressId+'" >'+tmp.userName+'<i></i></div>',
	    					 '<li class="radio-box" onmouseover="addrBoxOver(this)" onmouseout="addrBoxOut(this)">',
	    					 tmp.userName,
	    					 '&nbsp;&nbsp;',
	    					 tmp.areaName+tmp.userAddress,
	    					 '&nbsp;&nbsp;&nbsp;&nbsp;',
	    					 tmp.userPhone
	    					 )

	    			if(tmp.isDefault==1){
	    				html.push('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="j-default">默认地址</span>')
	    			}		
	    			html.push('<div class="operate-box">');
	    			if(tmp.isDefault!=1){
	    				html.push('<a href="javascript:;" onclick="setDeaultAddr('+tmp.addressId+')">设为默认地址</a>&nbsp;&nbsp;');
	    			}
	    			html.push('<a href="javascript:void(0)" onclick="javascript:toEditAddress('+tmp.addressId+',this,1,1)">编辑</a>&nbsp;&nbsp;');
	    			if(json.data.length>1){
	    				html.push('<a href="javascript:void(0)" onclick="javascript:delAddr('+tmp.addressId+',this)">删除</a></div>');
	    			}
	    			html.push('<div class="wst-clear"></div>','</li>');
	    		 }
	    		 html.push('<a style="color:#1c9eff" onclick="editAddress()" href="javascript:;">收起地址</a>'); 


	    		 $('#addressList').html(html.join(''));
	    	 }else{
	    		 $('#addressList').empty();
	    	 }
	     }else{
	    	 $('#addressList').empty();
	     }
	})
}












function inEffect(obj,n){
	
	$(obj).addClass('j-selected').siblings('.wst-frame'+n).removeClass('j-selected');
}
function editAddress(){
	var isNoSelected = false;
	$('.j-areas').each(function(){
		isSelected = true;
		if($(this).val()==''){
			isNoSelected = true;
			return;
		}
	})
	if(isNoSelected){
		WST.msg('请选择完整收货地址！',{icon:2});
		return;
	}
	layer.close(layerbox);
	var load = WST.load({msg:'正在提交数据，请稍后...'});
	var params = WST.getParams('.j-eipt');
	params.areaId = WST.ITGetAreaVal('j-areas');

	$.post(WST.U('home/useraddress/'+((params.addressId>0)?'toEdit':'add')),params,function(data,textStatus){
		layer.close(load);
		var json = WST.toJson(data);
	     if(json.status==1){
	    	 $('.j-edit-box').hide();
	    	 $('.j-list-box').hide();
	    	 $('#j-show-box').show();
	    	 if(params.addressId==0){
	    		 $('#s_addressId').val(json.data.addressId);
	    	 }else{
	    		 $('#s_addressId').val(params.addressId);
	    	 }
	    	 var areaIds = WST.ITGetAllAreaVals('area_0','j-areas');
	    	 $('#s_areaId').val(areaIds[1]);
	    	 getCartMoney();
	    	 window.location.reload();
	    	 var areaNames = [];
	    	 $('.j-areas').each(function(){
	    		 areaNames.push($('#'+$(this).attr('id')+' option:selected').text());
	    	 })
	    	 $('#s_userName').html(params.userName+'<i></i>');
	    	 $('#s_address').html(params.userName+'&nbsp;&nbsp;&nbsp;'+areaNames.join('')+'&nbsp;&nbsp;'+params.userAddress+'&nbsp;&nbsp;'+params.userPhone);

	    	 $('#s_address').siblings('.operate-box').find('a').attr('onclick','toEditAddress('+params.addressId+',this,1,1,1)');

	    	 if(params.isDefault==1){
	    		 $('#isdefault').html('默认地址').addClass('j-default');
	    	 }else{
	    		 $('#isdefault').html('').removeClass('j-default');
	    	 }

	     }else{
	    	 WST.msg(json.msg,{icon:2});
	     }
	});

}
var layerbox;
function showEditAddressBox(){
	getAddressList();
	toEditAddress();
}
function emptyAddress(obj,n){
	inEffect(obj,n);
	$('#addressForm')[0].reset();
	$('#s_addressId').val(0);
	$('#addressId').val(0);
	$("select[id^='area_0_']").remove();

	layerbox =	layer.open({
					title:'新增收货人信息',
					type: 1,
					area: ['800px', '300px'],
					content: $('.j-edit-box')
					});
}
function toEditAddress(id,obj,n,flag,type){
	inEffect(obj,n);
	id = (id>0)?id:$('#s_addressId').val();
	$.post(WST.U('home/useraddress/getById'),{id:id},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status == 1){
	     	if(flag){
		     	layerbox =	layer.open({
					title:'新增收货人信息',
					type: 1,
					area: ['800px', '300px'], //宽高
					content: $('.j-edit-box')
				});
	     	}
	     	if(type!=1){
				 $('.j-list-box').show();
		    	 $('#j-show-box').hide();
	     	}
	    	 WST.setValues(json.data);
	    	 $('input[name="addrUserPhone"]').val(json.data.userPhone)
	    	 $("select[id^='area_0_']").remove();
	    	 if(id>0){
		    	 var areaIdPath = json.data.areaIdPath.split("_");
		     	 $('#area_0').val(areaIdPath[0]);
		     	 var aopts = {id:'area_0',val:areaIdPath[0],childIds:areaIdPath,className:'j-areas'}
		 		 WST.ITSetAreas(aopts);
	    	 }
	     }else{
	    	 WST.msg(json.msg,{icon:2});
	     }
	});
}
function getCartMoney(){
	var params = {};
	params.isUseScore = $('#isUseScore').prop('checked')?1:0;
	params.useScore = $('#useScore').val();
	params.areaId2 = $('#s_areaId').val();
	params.rnd = Math.random();
	params.deliverType = $('#deliverType').val();
	var load = WST.load({msg:'正在计算订单价格，请稍后...'});
	$.post(WST.U('home/carts/getCartMoney'),params,function(data,textStatus){
		layer.close(load);  
		var json = WST.toJson(data);
		console.log(json);
		if(json.status==1){
		    json = json.data;
		    var shopFreight = 0;
		    for(var key in json.shops){
		    	// 设置每间店铺的运费及总价格
		    	$('#shopF_'+key).html(json.shops[key]['freight']);
		    	$('#shopC_'+key).html(json.shops[key]['goodsMoney']);
		    	shopFreight = shopFreight + json.shops[key]['freight'];
		    }
		    $('#deliverMoney').html(shopFreight);
		    $('#useScore').val(json.useScore);
		    $('#scoreMoney2').html(json.scoreMoney);
		 	$('#totalMoney').html(json.realTotalMoney);
		}
	});
}
function changeDeliverType(n,index,obj){
	changeSelected(n,index,obj);
	getCartMoney();
}
function sub(){

    var params = WST.getParams('.j-ipt');
    params.isUseScore = $('#isUseScore').prop('checked')?1:0;
    if(params.s_userPhone == ""){
		params.s_userPhone = $('#ph').val();
	}
    var load = WST.load({msg:'正在提交，请稍后...'});
    $.post(WST.U('home/orders/submit'),params,function(data,textStatus){
        layer.close(load);
        var json = WST.toJson(data);
        console.log(json);
        if(json.status==1){
            WST.msg(json.msg,{icon:1},function(){
                location.href=WST.U('home/orders/succeed','orderNo='+json.data);
            });
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}

function submitOrder(obj){
    if($('#need').hasClass('j-selected')) {
		if($('#invoiceClient').val() == ""){
            WST.msg('请填写发票信息');
            return false;
		}else{
			sub();
		}
    }else{
       sub();

	}
}

function changeInvoice(t,str,obj){
	WST.showHide(t,str);
	changeSelected(t,'isInvoice',obj);
}
function changeSelected(n,index,obj){
	$('#'+index).val(n);
	inEffect(obj,2);
}

function getPayUrl(){
	var params = {};
		params.payObj = "orderPay";
		params.orderNo = $("#orderNo").val();
		params.isBatch = $("#isBatch").val();
		params.payCode = $.trim($("#payCode").val());
	if(params.payCode==""){
		WST.msg('请先选择支付方式', {icon: 5});
		return;
	}

	jQuery.post(WST.U('home/'+params.payCode+'/get'+params.payCode+"Url"),params,function(data) {
		var json = WST.toJson(data);
		if(json.status==1){
			if(params.payCode=="weixinpays" || params.payCode=="wallets"){
				location.href = json.url;
			}else{
				if(params.payCode=="unionpays"){
					location.href = WST.U('home/unionpays/tounionpays',params);
				}else{
					location.href = json.url;
				}
			}
		}else{
			WST.msg('您的订单已支付!', {icon: 5,time:1500},function(){
				window.location = WST.U('home/orders/waitReceive');
			});
		}
	});
}

function payByWallet(){
    var params = WST.getParams('.j-ipt');
	var load = WST.load({msg:'正在核对支付密码，请稍后...'});
	$.post(WST.U('home/wallets/payByWallet'),params,function(data,textStatus){
		layer.close(load);   
		var json = WST.toJson(data);
	    if(json.status==1){
	    	WST.msg(json.msg, {icon: 1,time:1500},function(){
                window.location = WST.U('home/orders/waitReceive');
	    	});
	    }else{
	    	WST.msg(json.msg,{icon:2,time:1500});
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