jQuery.noConflict();
//切换
function pageSwitch(obj,type){
	$(obj).addClass('active').siblings('.ui-tab-nav li.switch').removeClass('active');
	$('#goods'+type).show().siblings('section.ui-container').hide();
	$("header").hide();
	$("#help").hide();
	$("footer").hide();
	

}

//商品评价列表
function evaluateList(){
    loading = true;
    var param = {};
    param.goodsId = $('#goodsId').val();
	param.pagesize = 10;
	param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/goodsappraises/getById'), param,function(data){
        var json = WST.toJson(data);
        $('#currPage').val(json.data.CurrentPage);
        $('#totalPage').val(json.data.TotalPage);
        var gettpl = document.getElementById('list').innerHTML;
        laytpl(gettpl).render(json.data.Rows, function(html){
            $('#evaluate-list').append(html);
        });
        loading = false;
        echo.init();//图片懒加载
    });
}
var currPage = totalPage = 0;
var loading = false;
$(document).ready(function(){
	//商品图片
     var slider = new fz.Scroll('.ui-slider', {
        role: 'slider',
        indicator: true,
        autoplay: true,
        interval: 3000
    });
	var w = WST.pageWidth();
    evaluateList();
	WST.imgAdapt('j-imgAdapt');
    $(window).scroll(function(){  
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
            	evaluateList();
            }
        }
    });
	if(goodsInfo.sku){
		var specs,dv;
		for(var key in goodsInfo.sku){
			if(goodsInfo.sku[key].isDefault==1){
				specs = key.split(':');
				$('.j-option').each(function(){
					dv = $(this).attr('data-val')
					if($.inArray(dv,specs)>-1){
						$(this).addClass('active');
					}
				})
				$('#buyNum').attr('data-max',goodsInfo.sku[key].specStock);
			}
		}
	}else{
		$('#buyNum').attr('data-max',goodsInfo.goodsStock);
	}
	checkGoodsStock();
	//选择规格
	$('.spec .j-option').click(function(){
		$(this).addClass('active').siblings().removeClass('active');
		checkGoodsStock();
	});
    //弹框的高度
    var dataHeight = $("#frame").css('height');
    var cartHeight = parseInt($("#frame-cart").css('height'))+52+'px';
    if(parseInt(dataHeight)>230){
        $('#content').css('overflow-y','scroll').css('height','200');
    }
    if(parseInt(cartHeight)>420){
        $('#standard').css('overflow-y','scroll').css('height','auto');
    }
    var dataHeight = $("#frame").css('height');
    var cartHeight = parseInt($("#frame-cart").css('height'))+52+'px';
    $("#frame").css('bottom','-'+dataHeight);
    $("#frame-cart").css('bottom','-'+cartHeight);
});
function checkGoodsStock(){
	var specIds = [],stock = 0,goodsPrice=0,marketPrice=0;
	if(goodsInfo.isSpec==1){
		$('.spec .active').each(function(){
			specIds.push(parseInt($(this).attr('data-val'),10));
		});
		specIds.sort(function(a,b){return a-b;});
		if(goodsInfo.sku[specIds.join(':')]){
			stock = goodsInfo.sku[specIds.join(':')].specStock;
			marketPrice = goodsInfo.sku[specIds.join(':')].marketPrice;
			goodsPrice = goodsInfo.sku[specIds.join(':')].specPrice;
		}
	}else{
		stock = goodsInfo.goodsStock;
		marketPrice = goodsInfo.marketPrice;
		goodsPrice = goodsInfo.goodsPrice;
	}
	$('#goods-stock').html(stock);
	$('#buyNum').attr('data-max',stock);
	$('#j-market-price').html('¥'+marketPrice);
	$('#j-shop-price').html('¥'+goodsPrice);
	if(stock<=0){
		$('#addBtn').addClass('disabled');
		$('#buyBtn').addClass('disabled');
	}else{
		$('#addBtn').removeClass('disabled');
		$('#buyBtn').removeClass('disabled');
	}
}
//弹框
function dataShow(){
	jQuery('#cover').attr("onclick","javascript:dataHide();").show();
	jQuery('#frame').animate({"bottom": 0}, 500);
	$("body").css("overflow","hidden")
}
function dataHide(){
	var dataHeight = $("#frame").css('height');
	jQuery('#frame').animate({'bottom': '-'+dataHeight}, 500);
	jQuery('#cover').hide();
	$("body").css("overflow","")
}
//弹框
var type;
function cartShow(t){
	type = t;
	jQuery('#cover').attr("onclick","javascript:cartHide();").show();
	jQuery('#frame-cart').animate({"bottom": 0}, 500);
	$("body").css("overflow","hidden")
}
function cartHide(){
	var cartHeight = parseInt($("#frame-cart").css('height'))+52+'px';
	jQuery('#frame-cart').animate({'bottom': '-'+cartHeight}, 500);
	jQuery('#cover').hide();
	$("body").css("overflow","")
}
//加入购物车
function addCart(){
	var goodsSpecId = 0;
	if(goodsInfo.isSpec==1){
		var specIds = [];
		$('.spec .active').each(function(){
			specIds.push($(this).attr('data-val'));
		});
		if(specIds.length==0){
			WST.msg('请选择你要购买的商品信息','info');
		}
		specIds.sort();
		if(goodsInfo.sku[specIds.join(':')]){
			goodsSpecId = goodsInfo.sku[specIds.join(':')].id;
		}
	}


	var goodsType = $("#goodsType").val();
	var buyNum = $("#buyNum").val()?$("#buyNum").val():1;

	$.post(WST.U('mobile/carts/addCart'),{goodsId:goodsInfo.id,goodsSpecId:goodsSpecId,buyNum:buyNum,rnd:Math.random()},function(data,textStatus){
	     var json = WST.toJson(data);
	     if(json.status==1){
	    	 WST.msg(json.msg,'success');
	    	 cartHide();
	    	 if(type==1){
	    		 setTimeout(function(){
	    			 if(goodsType==1){
	    				 location.href=WST.U('mobile/carts/'+json.data.forward);
	    			 }else{
	    				 location.href=WST.U('mobile/carts/index');
	    			 }
	    		 },1000);
	    	 }
	     }else{
	    	 WST.msg(json.msg,'info');
	     }
	});
}

// 表格
$("table").css("width","100%")
$("tr,td,tr p,td p,tr span,td span").css("font-size","12px")