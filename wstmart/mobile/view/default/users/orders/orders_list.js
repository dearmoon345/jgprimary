jQuery.noConflict();
// 获取订单列表
function getOrderList(){
  $('#Load').show();
    loading = true;
    var param = {};
    param.type = $('#type').val();
    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    // console.log(param)
    $.post(WST.U('mobile/orders/getOrderList'), param, function(data){
        var json = WST.toJson(data);
        var html = '';
        if(json && json.Rows && json.Rows.length>0){
          var gettpl = document.getElementById('shopList').innerHTML;
          laytpl(gettpl).render(json.Rows, function(html){
            $('#order-box').append(html);
          });

          $('#currPage').val(json.CurrentPage);
          $('#totalPage').val(json.TotalPage);
        }else{
           var mhtml = '<ul>';
          //  mhtml += '<li class="orders-none"></li>';
           mhtml += '<li class="orders-none"><img class="orders-none-pic" src="/wstmart/mobile/view/default/img/wudingdan.jpg"/></li>';
           mhtml += '</ul>';
          $('#order-box').html(mhtml);
        }
        WST.imgAdapt('j-imgAdapt');
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });
}

// 刷新列表页
function reFlashList(){
  $('#currPage').val('0');
  $('#order-box').html(' ');
  getOrderList();
}

function showCancelBox(event){
    $("#wst-event0").attr("onclick","javascript:"+event);
    $("#cancelBox").dialog("show");
}
// 取消订单
function cancelOrder(oid){
  hideDialog('#cancelBox');
  $.post(WST.U('mobile/orders/cancellation'),{id:oid,reason:$('#reason').val()},function(data){
    var json = WST.toJson(data);
    if(json.status==1){
      $('#order-box').html(' ');
      reFlashList();
    }else{
      WST.msg(json.msg,'info');
    }
  });

}

// 拒收
function showRejectBox(event){
    $("#wst-event3").attr("onclick","javascript:"+event);
    $("#rejectBox").dialog("show");
}

function rejectOrder(oid){
  var param = {};
  param.id=oid;
  param.reason=$('#reject').val();
  param.content=$('#content').val();
  if($('reject').val()==10000){
    var content = $.trim($('#content').val());
    if(content == '')
      WST.msg('请输入拒收原因','info');
      return;
  }
  
  $.post(WST.U('mobile/orders/reject'),param,function(data){
    
    hideDialog('#rejectBox');

    var json = WST.toJson(data);
    if(json.status==1){
      $('#content').val(' ');
      reFlashList();
    }else{
      WST.msg(json.msg,'info');
    }
  });

}

// 退款
function showRefundBox(id){
    // 重置表单
    $('#refundReason').val(1);
    $('#refundContent').html(' ');
    $('#money').val(' ');
    $('#refundTr').hide();

    $.post(WST.U('mobile/orders/getRefund'),{id:id},function(data){
            $('#realTotalMoney').html('¥'+data.realTotalMoney);
            $('#useScore').html(data.useScore);
            $('#scoreMoney').html('¥'+data.scoreMoney);
    })
    $("#wst-event8").attr("onclick","javascript:refund("+id+")");
    $("#refundBox").dialog("show");
}



function changeRefundType(v){
  if(v==10000){
    $('#refundTr').show();
  }else{
    $('#refundTr').hide();
  }
}
// 退款
function refund(id){
  var params = {};
      params.reason = $.trim($('#refundReason').val());
      params.content = $.trim($('#refundContent').val());
      params.money = $.trim($('#money').val());
      params.id = id;
      if(params.money<0 || params.money==''){
        WST.msg('无效的退款金额','info');
        return;
      }
      if(params.reason==10000){
        var content = $.trim($('#refundContent').val());
            if(content == ''){
               WST.msg('请输入原因','info');
              return;
            }
      }
      $.post(WST.U('mobile/orderrefunds/refund'),params,function(data){
          var json = WST.toJson(data);
          if(json.status==1){
            WST.msg('申请退款成功','success');
            $("#refundBox").dialog("hide");
            history.go(0);
          }else{
            WST.msg(json.msg,'info');
          }
      })
}

function changeRejectType(v){
  if(v==10000){
    $('#rejectTr').show();
  }else{
    $('#rejectTr').hide();
  }
}

//  隐藏对话框
function hideDialog(id){
  $(id).dialog("hide");
}

// 确认收货
function receive(oid){
  hideDialog('#wst-di-prompt');
  $.post(WST.U('mobile/orders/receive'),{id:oid},function(data){
      var json = WST.toJson(data);
      if(json.status==1){
        reFlashList();// 刷新列表
      }else{
        WST.msg(json.msg,'info');
      }
  });
}



/*********************** 订单详情 ****************************/
//弹框
function dataShow(){
    jQuery('#cover').attr("onclick","javascript:dataHide();").show();
    jQuery('#frame').animate({"right": 0}, 500);
    setTimeout(function(){$('#shopBox').hide();},600)
    
}
function dataHide(){
    $('#shopBox').show();
    var dataHeight = $("#frame").css('height');
    var dataWidth = $("#frame").css('width');
    jQuery('#frame').animate({'right': '-'+dataWidth}, 500);
    jQuery('#cover').hide();
}



function getOrderDetail(oid){
  $.post(WST.U('mobile/orders/getDetail'),{id:oid},function(data){
      var json = WST.toJson(data);
      if(json.status!=-1){
        var gettpl1 = document.getElementById('detailBox').innerHTML;
          laytpl(gettpl1).render(json, function(html){
            $('#content').html(html);
            // 弹出层滚动条
            var clientH = WST.pageHeight();// 屏幕高度
            var boxheadH = $('#boxTitle').height();// 弹出层标题高度
            var contentH = $('#content').height(); // 弹出层内容高度
            if((clientH-boxheadH) < contentH){
              $('#content').css('height',clientH-boxheadH+'px');
            }
            dataShow();
          });
      }else{
        WST.msg(json.msg,'info');
      }
  });
}
// 跳转到评价页
function toAppr(oid){
  location.href=WST.U('mobile/orders/orderappraise',{'oId':oid});
}
// 投诉
function complain(oid){
  location.href=WST.U('mobile/ordercomplains/complain',{'oId':oid});
}

//余额支付
function walletPay(){
	// var payPwd = $('#payPwd').val();
	// if(!payPwd){
	// 	WST.msg('请输入支付密码','info');
	// 	return;
	// }
	// WST.load('正在核对密码···');
	var params = {};
    // params.payPwd = payPwd;
    params.orderNo = $('#orderNo').val();
    params.isBatch = $('#isBatch').val();
    $('.wst-btn-dangerlo').attr('disabled', 'disabled');
	$.post(WST.U('mobile/wallets/payByWallet'),params,function(data,textStatus){
		WST.noload(); 
		var json = WST.toJson(data);
	    if(json.status==1){
	    	WST.msg(json.msg,'success');
	        setTimeout(function(){
	        	location.href = WST.U('mobile/orders/index');
	        },2000);
	    }else{
	    	WST.msg(json.msg,'info');
	        setTimeout(function(){
	            $('.wst-btn-dangerlo').removeAttr('disabled');
	        },2000);   
	    }
	});
}
//选择支付方式
function choicePay(orderNo,isBatch){
	location.href=WST.U('mobile/orders/succeed',{'orderNo':orderNo,'isBatch':isBatch});
}
//跳转支付
function toPay(orderNo,isBatch,n){
	if(n=='alipays'){
		location.href=WST.U('mobile/alipays/toAliPay',{'orderNo':orderNo,'isBatch':isBatch});
	}else if(n=='wallets'){
		location.href = WST.U('mobile/wallets/payment',{"orderNo":orderNo,'isBatch':isBatch});
	}
}