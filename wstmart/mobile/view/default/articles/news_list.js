// 获取商城快讯
function getNewList(){
  $('#Load').show();
    loading = true;
    var param = {};
    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/news/getNewsList'), param, function(data){
        var json = WST.toJson(data);
        var html = '';
        if(json && json.Rows && json.Rows.length>0){
           var gettpl = document.getElementById('newsList').innerHTML;
          laytpl(gettpl).render(json.Rows, function(html){
            $('#newsListBox').append(html);
          });
          $('#currPage').val(data.CurrentPage);
          $('#totalPage').val(data.TotalPage);
        }else{
          html += '<ul class="ui-row-flex wst-flexslp">';
          html += '<li class="ui-col ui-flex ui-flex-pack-center">';
          html += '<p>暂无商城快讯</p>';
          html += '</li>';
          html += '</ul>';
          $('#newsListBox').html(html);
        }
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });
}
var currPage = totalPage = 0;
var loading = false;
$(document).ready(function(){
    WST.initFooter();
    getNewList();



    var dataHeight = $("#frame").css('height');
    $("#frame").css('top',0);
     var dataWidth = $("#frame").css('width');
    $("#frame").css('right','-'+dataWidth);

	  
    $(window).scroll(function(){  
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
            	getNewList();
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

function viewNews(id){
  $.post(WST.U('mobile/news/getNews'),{id:id},function(data){
      var json = WST.toJson(data);
      $('#createTime').html(json.createTime);
      $('#articleTitle').html(json.articleTitle);
      $('#articleContent').html(json.articleContent);
      // 计算弹出层是否需要滚动条
      var sHeight = WST.pageHeight();
      var tHeight = $('#articleTitle').height();
      var cHeight = $('#articleContent').height();
      $('#content').css('height',sHeight-tHeight+'px');
      dataShow();
  })
}