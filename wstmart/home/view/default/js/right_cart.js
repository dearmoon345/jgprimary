$(document).ready(function(){
	var cartHeight = WST.pageHeight()-120;
	$('.toolbar-tab').hover(function (){
		$(this).find('.tab-text').addClass("tbar-tab-hover");
		$(this).find('.footer-tab-text').addClass("tbar-tab-footer-hover");
		$(this).addClass("tbar-tab-selected");
		$(this).find('.tab-cart-buy-num').addClass('tab-cart-buy-num-hover');
	}, function(){
		$(this).find('.tab-text').removeClass("tbar-tab-hover");
		$(this).find('.footer-tab-text').removeClass("tbar-tab-footer-hover");
		$(this).removeClass("tbar-tab-selected");
		$(this).find('.tab-cart-buy-num').removeClass('tab-cart-buy-num-hover');
		if($(this).hasClass('tbar-tab-click-selected')){
			$(this).find('.tab-cart-buy-num').addClass('tab-cart-buy-num-hover');
		}
	});
	$(".tbar-tab-code").click(function(){
		$(".tab-code-img").show();
	});

	
	$('.j-global-toolbar').siblings().click(function(){
		if($('.toolbar-wrap').hasClass('toolbar-open')){
			$('.toolbar-wrap').removeClass('toolbar-open');
		}
	})

$('.j-close').each(function(){
	$(this).click(function(){
		if($(this).css('visibility') == 'visible'){
			$('.j-content').css({'visibility':"hidden"});
		}else{
			$('.j-content').css({'visibility':"visible"});
		}
	});
});

	$(".tbar-tab-self").bind('click',function(){
		var target = $(".j-panel")[0];
		var tab = $(".tab-fade-box")[0];
		var condition = $(tab).css("display");
		if(condition == 'block'){
			$(tab).hide();
			$(".tbar-tab-self").removeClass("add-color");
		}else{
			$(tab).show();
			$(".tbar-tab-self").addClass("add-color");
		}
		$(target).children("div").css({"visibility":"hidden"});
		$(".toolbar-tabs").children("div").removeClass("tbar-tab-click-selected");
		$('.toolbar-wrap').removeClass('toolbar-open');
	});
	function self(){
		$($('.tab-fade-box')[0]).css({"display":"none"});
		$('.tbar-tab-self').removeClass("add-color");
	}

	// 首页右侧购物车模块
	$('.tbar-tab-cart').click(function (){

		if($('.toolbar-wrap').hasClass('toolbar-open')){

			if($(this).find('.tab-text1').length > 0){

				if(! $('.tbar-tab-follow').find('.tab-text').length > 0){
					var info = "<em class='tab-text '>收藏</em>";
					$('.tbar-tab-follow').append(info);
					$('.tbar-tab-follow').removeClass('tbar-tab-click-selected'); 
					$('.tbar-panel-follow').css({'visibility':"hidden","z-index":"-1"});
				}
				if(! $('.tbar-tab-history').find('.tab-text').length > 0){
					var info = "<em class='tab-text '>我的足迹</em>";
					$('.tbar-tab-history').append(info);
					$('.tbar-tab-history').removeClass('tbar-tab-click-selected'); 
					$('.tbar-panel-history').css({'visibility':"hidden","z-index":"-1"});
				}
				$(this).addClass('tbar-tab-click-selected'); 
				$(this).find('.tab-text1').remove();
				$('.tbar-panel-cart').css({'visibility':"visible","z-index":"1"});
				getRightCart();

			}else{
				var info = "<em class='tab-text '>收藏</em>";
				$('.toolbar-wrap').removeClass('toolbar-open');
				// $(this).append(info);
				$(this).removeClass('tbar-tab-click-selected');
				$('.tbar-panel-cart').css({'visibility':"hidden","z-index":"-1"});


			}
		}else{
		
			$(this).addClass('tbar-tab-click-selected'); 
			$(this).find('.tab-text1').remove();
			$('.tbar-panel-cart').css({'visibility':"visible","z-index":"1"});
			$('.tbar-panel-follow').css('visibility','hidden');
			$('.tbar-panel-history').css('visibility','hidden');
			$('#cart-panel').css('height',cartHeight+"px").css('overflow-y','auto');
			$('.tbar-tab-cart').find('.tab-cart-buy-num').addClass('tab-cart-buy-num-hover');
			$('.toolbar-wrap').addClass('toolbar-open');
			getRightCart();
			self();
		}
	});



	// 首页右侧收藏模块
	WST.collection = function(){
		freGoodsList();
	// $('.tbar-tab-follow').click(function (){
		$($('.tab-fade-box')[0]).css({"display":"none"});
		$('.tbar-tab-self').removeClass("add-color");
		if($('.toolbar-wrap').hasClass('toolbar-open')){

			if($('.tbar-tab-follow').find('.tab-text').length > 0){
				if(! $('.tbar-tab-cart').find('.tab-text1').length > 0){
					var info = "<em class='tab-text1'></em>";
					$('.tbar-tab-cart').append(info);
					$('.tbar-tab-cart').removeClass('tbar-tab-click-selected'); 
					$('.tbar-panel-cart').css({'visibility':"hidden","z-index":"-1"});
				}
				if(! $('.tbar-tab-history').find('.tab-text').length > 0){
					var info = "<em class='tab-text '>我的足迹</em>";
					$('.tbar-tab-history').append(info);
					$('.tbar-tab-history').removeClass('tbar-tab-click-selected'); 
					$('.tbar-panel-history').css({'visibility':"hidden","z-index":"-1"});
				}
				$('.tbar-tab-follow').addClass('tbar-tab-click-selected'); 
				$('.tbar-tab-follow').find('.tab-text').remove();
				$('.tbar-panel-follow').css({'visibility':"visible","z-index":"1"});

				
			}else{
				var info = "<em class='tab-text '>收藏</em>";
				$('.toolbar-wrap').removeClass('toolbar-open');
				$('.tbar-tab-follow').append(info);
				$('.tbar-tab-follow').removeClass('tbar-tab-click-selected');
				$('.tbar-panel-follow').css({'visibility':"hidden","z-index":"-1"});
			}
		}else{ 
			$('.tbar-tab-follow').addClass('tbar-tab-click-selected'); 
			$('.tbar-tab-follow').find('.tab-text').remove();
			$('.tbar-panel-cart').css('visibility','hidden');
			$('.tbar-panel-follow').css({'visibility':"visible","z-index":"1"});
			$('.tbar-panel-history').css('visibility','hidden');
			$('.toolbar-wrap').addClass('toolbar-open');
			self();
		}
	}

	
											
			

		
		
												
			// $(this).find('.add-cart-button1').hide();


	// 首页右侧浏览记录模块
	WST.record = function(){
	// $('.tbar-tab-history').click(function (){
		$($('.tab-fade-box')[0]).css({"display":"none"});
		$('.tbar-tab-self').removeClass("add-color");
		if($('.toolbar-wrap').hasClass('toolbar-open')){
			if($('.tbar-tab-history').find('.tab-text').length > 0){
				if(! $('.tbar-tab-follow').find('.tab-text').length > 0){
					var info = "<em class='tab-text '>收藏</em>";
					$('.tbar-tab-follow').append(info);
					$('.tbar-tab-follow').removeClass('tbar-tab-click-selected'); 
					$('.tbar-panel-follow').css({'visibility':"hidden","z-index":"-1"});
				}
				if(! $('.tbar-tab-cart').find('.tab-text1').length > 0){
					var info = "<em class='tab-text1'></em>";
					$('.tbar-tab-cart').append(info);
					$('.tbar-tab-cart').removeClass('tbar-tab-click-selected'); 
					$('.tbar-panel-cart').css({'visibility':"hidden","z-index":"-1"});
				}
				$('.tbar-tab-history').addClass('tbar-tab-click-selected'); 
				$('.tbar-tab-history').find('.tab-text').remove();
				$('.tbar-panel-history').css({'visibility':"visible","z-index":"1"});
				getHistoryGoods();
			}else{
				var info = "<em class='tab-text '>我的足迹</em>";
				$('.toolbar-wrap').removeClass('toolbar-open');
				$('.tbar-tab-history').append(info);
				$('.tbar-tab-history').removeClass('tbar-tab-click-selected');
				$('.tbar-panel-history').css({'visibility':"hidden","z-index":"-1"});
			}
		}else{ 
			$('.tbar-tab-history').addClass('tbar-tab-click-selected'); 
			$('.tbar-tab-history').find('.tab-text').remove();
			$('.tbar-panel-cart').css('visibility','hidden');
			$('.tbar-panel-follow').css('visibility','hidden');
			$('.tbar-panel-history').css({'visibility':"visible","z-index":"1"});
			$('.toolbar-wrap').addClass('toolbar-open'); 
			getHistoryGoods();
			self();
		}
	// });
	}
});
function getRightCart(){
	$.post(WST.U('home/carts/getCart'),'',function(data) {
		var json = WST.toJson(data);
		if(json.status==1){
			json = json.data;
			if(json.carts && !json.carts.length){
				var gettpl = document.getElementById('list-rightcart').innerHTML;
				laytpl(gettpl).render(json.carts, function(html){
					$('#cart-panel').html(html);
				});
				$('#j-goods-count').html(json.goodsTotalNum);
				$('#j-goods-total-money').html(json.goodsTotalMoney);
			}else{
				$('#cart-panel').html('<p class="right-carts-empty">购物车空空如也，赶紧去选购吧～</p>');
			}
		}
	});
}
function delRightCart(obj,id){
	var dataval = $(obj).attr('dataid');
	dataval = dataval.split("|");
	if($('#shop-cart-'+dataval[0]).children().size()>2){
		$('.j-goods-item-'+dataval[1]).remove();
	}else{
		$('#shop-cart-'+dataval[0]).remove();
	}
	statRightCartMoney();
	$.post(WST.U('home/carts/delCart'),{id:dataval[1],rnd:Math.random()},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status!=1){
	    	 WST.msg(json.msg,{icon:2});
	     }
	});
}
function jumpSettlement(){
	if($('#cart-panel').children().size()==0){
		WST.msg("您的购物车没有商品哦，请先添加商品~",{icon:2});
		return;
	}
	location.href=WST.U('home/carts/index');
}
function getHistoryGoods(){
	$.post(WST.U('home/goods/historyByGoods'),{},function(data) {
		var json = WST.toJson(data);
		if(json.status==1){
			var gettpl = document.getElementById('list-history-goods').innerHTML;
			laytpl(gettpl).render(json.data, function(html){
				$('#history-goods-panel').html(html);
			});
			$('.jth-item').hover(function (){ $(this).find('.add-cart-button').show(); },function(){ $(this).find('.add-cart-button').hide(); });
		}
	});
}
function checkRightChks(cid,obj){
    WST.changeCartGoods(cid,$('#buyNum_'+cid).val(),obj.checked?1:0);
    statRightCartMoney();
}
function statRightCartMoney(){
	var cartId,goodsNum = 0,goodsMoney = 0,tmpGoodsNum = 0,tmpGoodsMoney = 0;
	$('.jtc-item-goods').each(function(){
		cartId = $(this).attr('dataval');
		if($('#rcart_'+cartId).prop('checked')){
			goodsNum = parseInt($('#buyNum_'+cartId).val(),10);
			goodsMoney = parseFloat($('#gprice_'+cartId).html(),10);
			tmpGoodsNum++;
			tmpGoodsMoney += goodsMoney*goodsNum;
			tmpGoodsMoney = parseFloat(tmpGoodsMoney).toFixed(2);
		}
	})
	if(tmpGoodsNum==0){
		 $('#j-goods-count').html(0);
		 $('#j-goods-total-money').html(0);
	}else{
	    $('#j-goods-count').html(tmpGoodsNum);
	    $('#j-goods-total-money').html(tmpGoodsMoney);
	}
}

// 客服
function kf(){
    // WST.msg('该功能暂未开通！');
    layer.open({
        content:'该功能暂未开通！',
        yed:function(index,layer){
            layer.close(index);
        }
    });
}
