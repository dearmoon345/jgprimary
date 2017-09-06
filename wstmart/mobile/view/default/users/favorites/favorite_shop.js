// 获取关注的店铺
function getFavorites(){
  $('#Load').show();
    loading = true;
    var param = {};
    param.pagesize = 10;
    param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/favorites/listShopQuery'), param, function(data){
        var json = WST.toJson(data.data);
        var html = '';
        if(json && json.Rows && json.Rows.length>0){
          
          var gettpl = document.getElementById('shopList').innerHTML;
          laytpl(gettpl).render(json.Rows, function(html){
            $('#shopBox').html(html);
          });

          $('#currPage').val(json.CurrentPage);
          $('#totalPage').val(json.TotalPage);
        }else{
          var mhtml = '<ul class="ui-row-flex wst-flexslp">';
           mhtml += '<li class="ui-col ui-flex ui-flex-pack-center">';
           mhtml += '<p><img src="/wstmart/mobile/view/default/img/guanzhudian.jpg"/></p>';
           mhtml += '</li>';
           mhtml += '</ul>';
          $('#shopBox').html(mhtml);
        }
        WST.imgAdapt('j-imgAdapt');
        loading = false;
        $('#Load').hide();
        echo.init();//图片懒加载
    });


}
function goToShop(sid){
  location.href=WST.U('mobile/shops/home','shopId='+sid);
}
// 全选
function checkAll(obj){
  var chk = $(obj).prop('checked');
  $('.s-active').each(function(k,v){
    $(this).prop('checked',chk);
  });
}
// 取消关注
function cancelFavorite(){
  WST.dialogHide('prompt');
  var fids = new Array();
  $('.s-active').each(function(k,v){
    if($(this).attr('checked')){
      fids.push($(this).attr('fid'));
    }
  });
  fids = fids.join(',');
  if(fids==''){
    WST.msg('请先选择店铺','info');
    return;
  }
  $.post(WST.U('mobile/favorites/cancel'),{id:fids,type:1},function(data){
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

