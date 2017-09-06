//排序条件
function orderCondition(obj,condition){
    var classContent = $(obj).attr('class');
    var status = $(obj).attr('status');
    var theSiblings = $(obj).siblings('.sorts');
    theSiblings.removeClass('active').attr('status','down');
    $(obj).addClass('active');
    $('.wst-shl-select').removeClass('active');
    if(classContent.indexOf('active')==-1){
    	$('.wst-shl-head .evaluate i').addClass('down2').removeClass('down');
    }
    if(status.indexOf('down')>-1){
        if(classContent.indexOf('active')==-1){
        	$('.wst-shl-head .evaluate i').addClass('down2').removeClass('up2');
            $('#desc').val('0');
        }else{
        	$('.wst-shl-head .evaluate i').addClass('up2').removeClass('down2');
            $(obj).attr('status','up');
            $('#desc').val('1');
        }
    }else{
    	$('.wst-shl-head .evaluate i').addClass('down2').removeClass('up2');
        $(obj).attr('status','down');
        $('#desc').val('0');
    }
    $('#condition').val(condition);//排序条件
    $('#currPage').val('0');//当前页归零
    $('#shops-list').html('');
    shopsList();
}
function orderSelect(id){
	$('.wst-shl-select').addClass('active');
	$('.evaluate .choice').removeClass('active');
	$('.wst-shl-head .evaluate i').addClass('down').removeClass('down2');
	$('#catId').val(id);
    $('#currPage').val('0');//当前页归零
    $('#shops-list').html('');
    shopsList();
}
//获取店铺列表
function shopsList(){
	$('#Load').show();
    loading = true;
    var param = {};
    param.id = $('#catId').val();
    param.condition = $('#condition').val();
    param.desc = $('#desc').val();
    param.keyword = $('#keyword').val();
	param.pagesize = 10;
	param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/shops/pageQuery'), param,function(data){
        var json = WST.toJson(data);
        $('#currPage').val(json.CurrentPage);
        $('#totalPage').val(json.TotalPage);
        var gettpl = document.getElementById('list').innerHTML;
        laytpl(gettpl).render(json.Rows, function(html){
            $('#shops-list').append(html);
        });
        WST.imgAdapt('j-imgAdapt');
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });
}
var currPage = totalPage = 0;
var loading = false;
$(document).ready(function(){
	WST.initFooter('home');
	//中间小广告
	var w = WST.pageWidth();
    new Swiper('.swiper-container', {
        slidesPerView: 4,
        freeMode : true,
        spaceBetween: 3,
        autoplay : 2000,
        speed:1200,
        loop : true,
        autoplayDisableOnInteraction : false,
        onSlideChangeEnd: function(swiper){
            echo.init();//图片懒加载
        }
    });
	var ws = (w-20)*24.666666/100;
	var hs = ws*110/87;
	$('.wst-shl-adsb').css('height',hs);
    shopsList();
    $(window).scroll(function(){  
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
                shopsList();
            }
        }
    });
});
function goShopHome(sid){
    location.href=WST.U('mobile/shops/home','shopId='+sid,true);
}