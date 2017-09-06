jQuery.noConflict();
$(function(){
    // 加载商品列表
    shopsList();

    var w = WST.pageWidth();
    // 商家推荐
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
    // 推荐
    WST.imgAdapt('j-imgRec');
    // 热卖
    WST.imgAdapt('j-imgRec1');
    // 楼层商品
    WST.imgAdapt('j-imgAdapt');

    $('.wst-gol-adsb').css('height',$('.j-imgRec').width()+20);

    $("#ui-scrollerl").css('height',document.body.clientHeight-60 );
    var scroll = new fz.Scroll('.ui-scrollerl', {
        scrollY: true,
        slidingY: 'y'
    });

    var dataHeight = $("#frame").css('height');
    if(parseInt(dataHeight)>230){
        $('#content').css('overflow-y','scroll').css('height',document.body.clientHeight-40);
        
    }

    $("#frame").css('top',0);
     var dataWidth = $("#frame").css('width');
    $("#frame").css('right','-'+dataWidth);

});

//弹框
function dataShow(){
    jQuery('#cover').attr("onclick","javascript:dataHide();").show();
    jQuery('#frame').animate({"right": 0}, 500);
    $("body").css("overflow","hidden")
}
function dataHide(){
    var dataHeight = $("#frame").css('height');
    var dataWidth = $("#frame").css('width');
    jQuery('#frame').animate({'right': '-'+dataWidth}, 500);
    jQuery('#cover').hide();
    $("body").css("overflow","")
}

function showRight(obj, index){
    $(obj).addClass('wst-goodscate_selected').siblings('#goodscate').removeClass('wst-goodscate_selected');
    $('.goodscate1').eq(index).show().siblings('.goodscate1').hide();
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
//排序条件
function orderCondition(obj,condition){
    var classContent = $(obj).attr('class');
    var status = $(obj).attr('status');
    var theSiblings = $(obj).siblings('.sorts');
    theSiblings.removeClass('active').attr('status','down');
    $(obj).addClass('active');
    if(classContent.indexOf('active')==-1){
        $(obj).children('i').addClass('down2').removeClass('down');
        theSiblings.children('i').addClass('down').removeClass('down2');
    }
    if(status.indexOf('down')>-1){
        if(classContent.indexOf('active')==-1){
            $(obj).children('i').addClass('down2').removeClass('up2');
            $('#desc').val('0');
        }else{
            $(obj).children('i').addClass('up2').removeClass('down2');
            $(obj).attr('status','up');
            $('#desc').val('1');
        }
    }else{
        $(obj).children('i').addClass('down2').removeClass('up2');
        $(obj).attr('status','down');
        $('#desc').val('0');
    }
    $('#condition').val(condition);//排序条件
    $('#currPage').val('0');//当前页归零
    $('#goods-list').html('');
    shopsList();
}


//获取商品列表
function shopsList(){
    $('#Load').show();
    loading = true;
    var param = {};
    param.shopId = $('#shopId').val();
    param.msort = $('#condition').val();
    param.mdesc = $('#desc').val();
    param.goodsName = $('#keyword').val();
    param.ct1 = $('#ct1').val();
    param.ct2 = $('#ct2').val();

    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/shops/getShopGoods'), param, function(data){
        var json = WST.toJson(data);
        var html = '';
        if(json && json.Rows && json.Rows.length>0){
            var gettpl = document.getElementById('shopList').innerHTML;
              laytpl(gettpl).render(json.Rows, function(html){
                $('#shops-list').html(html);
              }); 

            $('#currPage').val(json.CurrentPage);
            $('#totalPage').val(json.TotalPage);
        }else{
            html += '<ul class="ui-row-flex wst-flexslp">';
            html += '<li class="ui-col ui-flex ui-flex-pack-center">';
            html += '<p>对不起，没有相关商品。</p>';
            html += '</li>';
            html += '</ul>';
            $('#shops-list').html(html);
        }
        WST.imgAdapt('j-imgAdapt');
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });
}

/*分类*/
function goGoodsList(ct1,ct2,categoryName){
    var keyword = document.getElementById('keyword');
    var wstSearch = document.getElementById('wst-search');
    if(keyword.value != null){
        keyword.value = '';
        wstSearch.value = categoryName;
    }
    $('#ct2').val(' ');
    $('#ct1').val(ct1);
    if(ct2)$('#ct2').val(ct2);
    shopsList();
    dataHide();
}


function toShopInfo(sid){
    location.href=WST.U('mobile/shops/index',{'shopId':sid},true)
}
