jQuery.noConflict();
var loading = false;
$(function(){
    // 加载商品列表
    shopsList();
    var w = WST.pageWidth();
    // 商家推荐
    new Swiper('.swiper-container', {
        slidesPerView: 5,
        freeMode : true,
//      spaceBetween: 3,
//      autoplay : 2000,
//      speed:1200,
        loop : false,
        paginationClickable: true,
        autoplayDisableOnInteraction : false,
        onSlideChangeEnd: function(swiper){
            echo.init();//图片懒加载
        }
    });
    // 推荐
    WST.imgAdapt('j-imgRec');
    // 热卖
    WST.imgAdapt('j-imgRec1');
    $('.wst-gol-adsb').css('height',$('.j-imgRec').width()+20);
    // 商品分类
    if(parseInt(dataHeight)>230){
        $('#content').css('overflow-y','scroll').css('height','200');
    }
    var dataHeight = $("#frame").css('height');
    $("#frame").css('top',0);

     var dataWidth = $("#frame").css('width');
    $("#frame").css('right','-'+dataWidth);

    $(window).scroll(function(){
        if (loading) return;
        if (($(window).scrollTop()) >= ($(document).height() - screen.height)) {
            currPage = Number( $('#currPage').val() );
            if(currPage < 5 ){
                shopsList();
            }
        }
    });
});

//弹框
function dataShow(){
    jQuery('#cover').attr("onclick","javascript:dataHide();").show();
    jQuery('#frame').animate({"right": 0}, 500);
}
function dataHide(){
    var dataHeight = $("#frame").css('height');
    var dataWidth = $("#frame").css('width');
    jQuery('#frame').animate({'right': '-'+dataWidth}, 500);
    jQuery('#cover').hide();
}

function showRight(obj, index){
    $(obj).addClass('wst-goodscate_selected').siblings('#goodscate').removeClass('wst-goodscate_selected');
    $('.goodscate1').eq(index).show().siblings('.goodscate1').hide();
}
function searchGoods(){
    location.href=WST.U('mobile/shops/home','goodsName='+$('#searchKey').val(),true);
}
/*分类*/
function goGoodsList(ct1,ct2){
    var param = 'shopId=1&ct1='+ct1;
    if(ct2)
        param += '&ct2='+ct2;
    location.href=WST.U('mobile/shops/home',param,true);
}




function shopAds(){
     //广告
    var slider = new fz.Scroll('.ui-slider', {
        role: 'slider',
        indicator: true,
        autoplay: true,
        interval: 3000
    });
    var w = WST.pageWidth();
    var h = w*2/5;
        var o = $('.ui-slider').css("padding-top",h);
        var scroll = new fz.Scroll('.ui-slider', {
            scrollY: true
        });
}

//获取商品列表
function shopsList(){
    $('#Load').show();
     loading = true;
     var param = {};
     param.currPage = Number( $('#currPage').val() ) + 1;
     $.post(WST.U('mobile/shops/getFloorData'), param, function(data){
         var json = WST.toJson(data);
         if(json && json.catId && json.goods.length>0){
            var gettpl = document.getElementById('gList').innerHTML;
              laytpl(gettpl).render(json, function(html){
                $('#goods-list').append(html);
              }); 
             $('#currPage').val(json.currPage);
             WST.imgAdapt('j-imgAdapt');
         }
         loading = false;
         $('#Load').hide();
     });
}