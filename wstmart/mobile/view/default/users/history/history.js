// 获取浏览记录
function getHistory(){
  $('#Load').show();
    loading = true;
    var param = {};
    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/goods/historyQuery'), param, function(data){
        var json = WST.toJson(data);
        var html = '';
        if(json && json.Rows && json.Rows.length>0){
           var gettpl = document.getElementById('list').innerHTML;
          laytpl(gettpl).render(json.Rows, function(html){
            $('#listBox').append(html);
          });
          $('#currPage').val(data.CurrentPage);
          $('#totalPage').val(data.TotalPage);
        }else{
          html += '<ul class="ui-row-flex wst-flexslp">';
          html += '<li class="ui-col ui-flex ui-flex-pack-center">';
          html += '<p><img src="/wstmart/mobile/view/default/img/liulan.jpg"/></p>';
          html += '</li>';
          html += '</ul>';
          $('#listBox').html(html);
        }
        WST.imgAdapt('j-imgAdapt');
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });
}
var currPage = totalPage = 0;
var loading = false;
$(document).ready(function(){
    WST.initFooter();
    getHistory();
    $(window).scroll(function(){  
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
            	getHistory();
            }
        }
    });
});

