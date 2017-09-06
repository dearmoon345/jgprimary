jQuery.noConflict();
// 获取订单列表
function getComplainList(){
  $('#Load').show();
    loading = true;
    var param = {};
    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/orderComplains/complainByPage'), param, function(data){
        var json = WST.toJson(data.data);
        var html = '';
        if(json && json.Rows && json.Rows.length>0){
          var gettpl = document.getElementById('complainList').innerHTML;
          laytpl(gettpl).render(json.Rows, function(html){
            $('#complain-list').append(html);
          });

          $('#currPage').val(json.CurrentPage);
          $('#totalPage').val(json.TotalPage);
        }else{
           var mhtml = '<ul class="ui-row-flex wst-flexslp">';
           mhtml += '<li class="ui-col ui-flex ui-flex-pack-center">';
           mhtml += '<p><img src="/wstmart/mobile/view/default/img/tousu.jpg"/></p>';
           mhtml += '</li>';
           mhtml += '</ul>';
          $('#complain-list').html(mhtml);
        }
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });
}
var currPage = totalPage = 0;
var loading = false;
$(document).ready(function(){
  getComplainList();
  WST.initFooter('user');
  // 弹出层
   $("#frame").css('top',0);

    $(window).scroll(function(){  
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
            	getComplainList();
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

function complainDetail(cId){
  $.post(WST.U('mobile/orderComplains/getComplainDetail'),{'id':cId},function(data){
    var json = WST.toJson(data);
    if(json){
      var gettpl = document.getElementById('complainDetail').innerHTML;
          laytpl(gettpl).render(json, function(html){
            // 写入数据
            $('#complainDetailBox').html(html);
            // 设置滚动条
            var screenH = WST.pageHeight();
            var titleH = $('#frame').find('.title').height();
            var contentH = $('#complainDetailBox').height();

            if(screenH-titleH < contentH){
              $('#complainDetailBox').css('height',screenH-titleH);
            }
            // 展示弹出层
            dataShow();
          });
    }

  })
    
}