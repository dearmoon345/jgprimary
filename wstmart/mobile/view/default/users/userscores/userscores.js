jQuery.noConflict();
// 获取订单列表
function getScoreList(){
  $('#Load').show();
    loading = true;
    var param = {};
    param.type = $('#type').val() || -1;
    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/userscores/pageQuery'), param, function(data){
        var json = WST.toJson(data.data);
        var html = '';
        if(json && json.Rows && json.Rows.length>0){
          var gettpl = document.getElementById('scoreList').innerHTML;
          laytpl(gettpl).render(json.Rows, function(html){
            $('#score-list').append(html);
          });

          $('#currPage').val(json.CurrentPage);
          $('#totalPage').val(json.TotalPage);
        }else{
           var mhtml = '<ul class="ui-row-flex wst-flexslp">';
           mhtml += '<li class="ui-col ui-flex ui-flex-pack-center">';
           mhtml += '<p>暂无相关信息</p>';
           mhtml += '</li>';
           mhtml += '</ul>';
          $('.score-detail').html(mhtml);
        }
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });
}
var currPage = totalPage = 0;
var loading = false;
$(document).ready(function(){
  getScoreList();
  WST.initFooter('user');
  // 弹出层
   $("#frame").css('top',0);

    $(window).scroll(function(){  
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
            	getScoreList();
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