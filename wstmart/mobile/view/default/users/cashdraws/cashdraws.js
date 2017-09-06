jQuery.noConflict();
// 获取提现记录
function getCashDraws(){
  $('#Load').show();
    loading = true;
    var param = {};
    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/cashdraws/pageQuery'), param, function(data){
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
  getCashDraws();
  WST.initFooter('user');
    $(window).scroll(function(){  
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
            	getCashDraws();
            }
        }
    });
});
