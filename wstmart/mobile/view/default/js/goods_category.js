function showRight(obj, index){
	$(obj).addClass('wst-goodscate_selected').siblings('#goodscate').removeClass('wst-goodscate_selected');
	$('.goodscate1').eq(index).show().siblings('.goodscate1').hide();
}
//商品列表页
function getGoodsList(goodsCatId){
	
	location.href = WST.U('mobile/goods/lists','catId='+goodsCatId,true);
}

//品牌-商品列表页
function getBrandGoodsList(brandId){
	location.href = WST.U('mobile/goods/lists','brandId='+brandId,true);
}
$(document).ready(function(){
	WST.initFooter('category');
	var h = WST.pageHeight();
	var o = document.getElementById('ui-scrollerl');
	var a = h-50;
	o.style.height=a+'px';
    var scroll = new fz.Scroll('.ui-scrollerl', {
        scrollY: true,
        slidingY: 'y'
    });
    var w = WST.pageWidth();
    var wImg=(w*0.76-30)/3;
    var hImg = wImg*9/14;
    $('.wst-gc-br img').css('height',hImg)
});


