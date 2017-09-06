//商品列表页
function getGoodsList(brandId){
	location.href = WST.U('mobile/goods/lists','brandId='+brandId,true);
}
//获取品牌列表
function brandsList(){
	 $('#Load').show();
	 loading = true;
	 var param = {};
	 param.pagesize = 10;
	 param.page = Number( $('#currPage').val() ) + 1;
    $.post(WST.U('mobile/brands/pageQuery'), param,function(data){
        var json = WST.toJson(data);
        if(json && json.Rows && json.Rows.length>0){
	        $('#currPage').val(json.CurrentPage);
	        $('#totalPage').val(json.TotalPage);
            var gettpl = document.getElementById('list').innerHTML;
            laytpl(gettpl).render(json.Rows, function(html){
                $('#info-list').append(html);
            });
        }
	     loading = false;
	     $('#Load').hide();
    });
}
var currPage = totalPage = 0;
var loading = false;
$(document).ready(function(){
	WST.initFooter('home');
	brandsList();
    $(window).scroll(function(){  
        if (loading) return;
        if ((5 + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
            currPage = Number( $('#currPage').val() );
            totalPage = Number( $('#totalPage').val() );
            if( totalPage > 0 && currPage < totalPage ){
            	brandsList();
            }
        }
    });
});