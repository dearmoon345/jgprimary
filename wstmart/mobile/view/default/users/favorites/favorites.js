// 获取关注的商品
function getFavorites(){
  $('#Load').show();
    loading = true;
    var param = {};
    param.id = $('#catId').val();
    param.condition = $('#condition').val();
    param.desc = $('#desc').val();
    param.keyword = $('#searchKey').val();
    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/favorites/listGoodsQuery'), param, function(data){
        var json = WST.toJson(data.data);
        var html = '';
        if(json && json.Rows && json.Rows.length>0){
           var gettpl = document.getElementById('fGoods').innerHTML;
          laytpl(gettpl).render(json.Rows, function(html){
            $('#goods-list').html(html);
          });
          $('#currPage').val(data.CurrentPage);
          $('#totalPage').val(data.TotalPage);
        }else{
          html += '<ul class="ui-row-flex wst-flexslp">';
          html += '<li class="ui-col ui-flex ui-flex-pack-center">';
          html += '<p><img src="/wstmart/mobile/view/default/img/guanzhupin.jpg"/></p>';
          html += '</li>';
          html += '</ul>';
          $('#goods-list').html(html);
        }
        WST.imgAdapt('j-imgAdapt');
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });
}
// 全选
function checkAll(obj){
  var chk = $(obj).attr('checked');
  $('.active').each(function(k,v){
    $(this).prop('checked',chk);
  });
}
// 取消关注
function cancelFavorite(){
  WST.dialogHide('prompt');
  var gids = new Array();
  $('.active').each(function(k,v){
    if($(this).attr('checked')){
      gids.push($(this).attr('gid'));
    }
  });
  gids = gids.join(',');
  if(gids==''){
    WST.msg('请先选择商品','info');
    return;
  }
  $.post(WST.U('mobile/favorites/cancel'),{id:gids,type:0},function(data){
    var json = WST.toJson(data);
    if(json.status==1){
      $('#currPage').val('0')
      getFavorites();
    }else{
      WST.msg(json.msg,'info');
    }
  });

}

var currPage = totalPage = 0;
var loading = false;
$(document).ready(function(){
	getFavorites();
    $(window).scroll(function(){  
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
            	getFavorites();
            }
        }
    });
});



function addCart(goodsId){
  $.post(WST.U('mobile/carts/addCart'),{goodsId:goodsId,buyNum:1},function(data,textStatus){
       var json = WST.toJson(data);
       if(json.status==1){
         WST.msg(json.msg,'success');
       }else{
         WST.msg(json.msg,'info');
       }
  });
}