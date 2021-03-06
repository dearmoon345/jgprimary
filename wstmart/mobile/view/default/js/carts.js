$(document).ready(function(){
	WST.initFooter('cart');
	WST.imgAdapt('j-imgAdapt');
	
    statCartMoney();
    //选中店铺
    $('.ui-icon-chooses').click(function(){
    	WST.changeIconStatus($(this), 1);
        var childrenId = $(this).attr('childrenId');
        var goodsCount = $('.'+childrenId).length;//商品个数
        if( $(this).attr('class').indexOf('wst-active') == -1 ){
        	WST.changeIconStatus($('.'+childrenId), 2);//选中
            for(var i=0; i<goodsCount; i++){
            	var cid = $('.'+childrenId).eq(i).attr('cartId');
                WST.changeCartGoods(cid,$('#buyNum_'+cid).val(),0);
            }
        }else{
        	WST.changeIconStatus($('.'+childrenId), 2, 'wst-active');//取消选中
            for(var i=0; i<goodsCount; i++){
            	var cid = $('.'+childrenId).eq(i).attr('cartId');
                WST.changeCartGoods(cid,$('#buyNum_'+cid).val(),1);
            }
        }
        
        //          $(".ui-icon-chooses.wst-active").length; 店铺按钮被选中长度
		//          $(".ui-icon-chooses").length; 店铺按钮长度
        //判断全选按钮是否选中
        if($(".ui-icon-chooses.wst-active").length == $(".ui-icon-chooses").length){//全选按钮选中
        	$(".ui-icon-choose").addClass("wst-active + ui-icon-success-block");
        	$(".ui-icon-choose").removeClass("ui-icon-unchecked-s")
        }else{//全选按钮不选中
        	$(".ui-icon-choose").removeClass("wst-active + ui-icon-success-block")
        	$(".ui-icon-choose").addClass("ui-icon-unchecked-s")
        }
        statCartMoney();
    });
    //选中商品
    $('.ui-icon-chooseg').click(function(){
        if( $(this).attr('class').indexOf('wst-active') == -1 ){
        	var checked = 1;
        	WST.changeIconStatus($(this), 1);//选中
        	
            //      	$(this).parents(".wst-ca-s").find(".ui-icon-chooseg.wst-active").length；本店商品按钮被选中长度
            //			$(this).parents(".wst-ca-s").find(".ui-icon-chooseg").length;本店铺商品按钮长度
	       	if($(this).parents(".wst-ca-s").find(".ui-icon-chooseg.wst-active").length == $(this).parents(".wst-ca-s").find(".ui-icon-chooseg").length){
	        	//店铺按钮选中
	        	$(this).parents(".wst-ca-s").find(".ui-icon-chooses").addClass("wst-active + ui-icon-success-block");
	        	$(this).parents(".wst-ca-s").find(".ui-icon-chooses").removeClass("ui-icon-unchecked-s");
	        	//判断全选按钮是否选中
		        if($(".ui-icon-chooses.wst-active").length == $(".ui-icon-chooses").length){//全选按钮选中
		        	$(".ui-icon-choose").addClass("wst-active + ui-icon-success-block");
		        	$(".ui-icon-choose").removeClass("ui-icon-unchecked-s");
		        }else{//全选按钮不选中
		        	$(".ui-icon-choose").removeClass("wst-active + ui-icon-success-block");
		        	$(".ui-icon-choose").addClass("ui-icon-unchecked-s");
        		}
	        }else{//店铺按钮不选中
	        	$(this).parents(".wst-ca-s").find(".ui-icon-chooses").removeClass("wst-active + ui-icon-success-block");
	        	$(this).parents(".wst-ca-s").find(".ui-icon-chooses").addClass("ui-icon-unchecked-s");
	        	//全选按钮不选中
	        	$(".ui-icon-choose").removeClass("wst-active + ui-icon-success-block");
	        	$(".ui-icon-choose").addClass("ui-icon-unchecked-s");
	        }
        }else{
        	var checked = 0;
        	WST.changeIconStatus($(this), 2);//取消选中        	
        	//店铺按钮不被选中
        	$(this).parents(".wst-ca-s").find(".ui-icon-chooses").removeClass("wst-active + ui-icon-success-block")
        	$(this).parents(".wst-ca-s").find(".ui-icon-chooses").addClass("ui-icon-unchecked-s")
        	//全选按钮不选中
        	$(".ui-icon-choose").removeClass("wst-active + ui-icon-success-block")
        	$(".ui-icon-choose").addClass("ui-icon-unchecked-s")
        }
        var cid = $(this).attr('cartId');
        if(cid!=''){
		    WST.changeCartGoods(cid,$('#buyNum_'+cid).val(),checked);
		    statCartMoney();
	    }
       
    });
    //选中合计
    $('.ui-icon-choose').click(function(){
    	WST.changeIconStatus($(this), 1);
        var shopIconCount = $('.ui-icon-chooses').length;//店铺个数
        var goodsCount = $('.ui-icon-chooseg').length;//商品个数
        if( $(this).attr('class').indexOf('wst-active') == -1 ){
        	//选中所有
            for(var i=0; i<shopIconCount; i++){
            	WST.changeIconStatus($('.ui-icon-chooses').eq(i), 2);
            }
            for(var i=0; i<goodsCount; i++){
            	WST.changeIconStatus($('.ui-icon-chooseg').eq(i), 2);
                var cid = $('.ui-icon-chooseg').eq(i).attr('cartId');
                WST.changeCartGoods(cid,$('#buyNum_'+cid).val(),0);
            }
        }else{
        	//取消选中所有
            for(var i=0; i<shopIconCount; i++){
            	WST.changeIconStatus($('.ui-icon-chooses').eq(i), 2, 'wst-active');
            }
            for(var i=0; i<goodsCount; i++){
            	WST.changeIconStatus($('.ui-icon-chooseg').eq(i), 2, 'wst-active');
                var cid = $('.ui-icon-chooseg').eq(i).attr('cartId');
                WST.changeCartGoods(cid,$('#buyNum_'+cid).val(),1);
            }
        }
        statCartMoney();
    });
});
//合计
function statCartMoney(){
	var cartMoney = 0,goodsTotalPrice,id;
	$('.wst-active').each(function(){
		id = $(this).attr('cartId');
		goodsTotalPrice = parseFloat($(this).attr('mval'))*parseInt($('#buyNum_'+id).val());
		cartMoney = cartMoney + goodsTotalPrice;
	});
	for(var i=1; i<$('#totalshop').val(); i++){
		var shopMoney = 0,goodsTotalPrice2;
		$('.clist'+i).each(function(){
			id = $(this).attr('cartId');
			goodsTotalPrice2 = parseFloat($(this).attr('mval'))*parseInt($('#buyNum_'+id).val());
			shopMoney = shopMoney + goodsTotalPrice2;
		});
		$('#tprice_'+i).html('¥'+shopMoney.toFixed(2));
	 }
	$('#totalMoney').html('¥'+cartMoney.toFixed(2));
	checkGoodsBuyStatus();
}
function checkGoodsBuyStatus(){
	var cartNum = 0,stockNum = 0,cartId = 0;
	$('.wst-active').each(function(){
		cartId = $(this).attr('cartId');
		cartNum = parseInt($('#buyNum_'+cartId).val(),10);
		stockNum = parseInt($('#buyNum_'+cartId).attr('data-max'),10);
		if(stockNum < 0 || stockNum < cartNum){
			if(stockNum < 0){
				msg = '库存不足';
			}else{
				msg = '购买量超过库存';
			}
			$('#noprompt'+cartId).show().html(msg);
			$(this).parent().parent().addClass('nogoods');
	        WST.changeIconStatus($(this), 2);//取消选中
			WST.changeCartGoods(cartId,$('#buyNum_'+cartId).val(),0);
			statCartMoney();
		}else{
			$('#noprompt'+cartId).hide().html('');
			$(this).parent().parent().removeClass('nogoods');
		}
	});
}
//编辑
function edit(type){
	if(type==0){
		WST.showHide('','#edit,#settlement,#total');
		WST.showHide(1,'#complete,#delete');
	}else{
		WST.showHide('','#complete,#delete');
		WST.showHide(1,'#edit,#settlement,#total');
	}
}
//删除
function deletes(){
    var goodsIds = '';
    var goodsIconCount = $('.ui-icon-chooseg').length;//商品个数
    for(var i=0; i<goodsIconCount; i++){
        if( $('.ui-icon-chooseg').eq(i).attr('class').indexOf('wst-active') != -1 ){
            goodsIds += $('.ui-icon-chooseg').eq(i).attr('cartId') + ',';
        }
    }
    if(goodsIds!=''){
    	WST.dialog('确定删除选中的商品吗？','del("'+goodsIds+'")');
    }else{
    	WST.msg('请选择要删除的商品','info');
    }
}
function del(goodsIds){
	$.post(WST.U('mobile/carts/delCart'),{id:goodsIds},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status==1){
			  WST.msg(json.msg,'success');
			  WST.dialogHide('prompt');
		      setTimeout(function(){
		    	  location.href = WST.U('mobile/carts/index');
		      },2000);
	     }else{
	    	 WST.msg(json.msg,'warn');
	     }
	});
}
//结算
function toSettlement(){
    // alert(1111);return false;
    //添加代码start     商城改成虚拟交易
    //判断是否为  测试人员账号
    $.post(WST.U('mobile/users/ceshiUser'),function(data){
        var json = WST.toJson(data);
        if(json.status==-1){
            WST.msg('商城正在测试中，暂时无法结算');
            return;
        }else{
            var goodsIconCount = $('.ui-icon-chooseg').length;//商品个数
            var noGoodsSelected = true;
            for(var i=0; i<goodsIconCount; i++){
                if( $('.ui-icon-chooseg').eq(i).attr('class').indexOf('wst-active') != -1 ){
                    noGoodsSelected = false;
                }
            }
            if(noGoodsSelected){
                WST.msg('请勾选要结算的商品','info');
                return false;
            }
            location.href = WST.U('mobile/carts/settlement');
        }
    });
    //添加代码end

    // var goodsIconCount = $('.ui-icon-chooseg').length;//商品个数
    // var noGoodsSelected = true;
    // for(var i=0; i<goodsIconCount; i++){
    //     if( $('.ui-icon-chooseg').eq(i).attr('class').indexOf('wst-active') != -1 ){
    //         noGoodsSelected = false;
    //     }
    // }
    // if(noGoodsSelected){
    // 	WST.msg('请勾选要结算的商品','info');
    //     return false;
    // }
    // location.href = WST.U('mobile/carts/settlement');
}




/*底部导航*/
   var cheng = false;
			$(".dayuanjia").click(function(){
				cheng = !cheng;
				$(".xiaoyuan1").click(function(){
					$("html,body").css({scrollTop:0});
					/*$(".dayuanjia").attr("src","../img/dayuanjia.png");*/
					$(".xiaoyuan").css({"bottom":"90px","right":"25px"});
					cheng = false;
				})
				if(cheng){
					/*$(".dayuanjia").attr("src","img/dayuancheng.png");*/
					$(".xiaoyuan1").css({"bottom":"160px","right":"30px"});
					$(".xiaoyuan2").css({"bottom":"140px","right":"80px"});
					$(".xiaoyuan3").css({"bottom":"90px","right":"95px"})
				}		
				else{
					/*$(".dayuanjia").attr("src","../img/dayuanjia.png");*/
					$(".xiaoyuan").css({"bottom":"90px","right":"25px"})
				}
			})

   
