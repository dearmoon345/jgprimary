var grid;
$(function(){
	$("#startDate").ligerDateEditor();
	$("#endDate").ligerDateEditor();
})
function initSaleGrid(){
	grid = $("#maingrid").ligerGrid({
		url:WST.U('admin/reports/topSaleGoodsByPage',WST.getParams('.ipt')),
		pageSize:WST.pageSize,
		pageSizeOptions:WST.pageSizeOptions,
		height:'99%',
        width:'100%',
        rowHeight:65,
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '&nbsp;', name: 'goodsName',width:60,align:'left',heightAlign:'left',isSort: false,render: function (rowdata, rowindex, value){
            	return "<img style='height:60px;width:60px;' src='"+WST.conf.ROOT+"/"+rowdata['goodsImg']+"'>";
            }},
	        { display: '商品名称', name: 'goodsName',heightAlign:'left',isSort: false,render: function (rowdata, rowindex, value){
	            return rowdata['goodsName'];
	        }},
	        { display: '商品编号', name: 'goodsSn',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['goodsSn']+"</div>";
	        }},
	        { display: '所属店铺', name: 'shopName',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['shopName']+"</div>";
	        }},
	        { display: '销量', name: 'goodsNum',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['goodsNum']+"</div>";
	        }},
	        { display: '查看', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            h += "<div class='goods-valign-m'><a target='_blank' href='"+WST.U("home/goods/detail","id="+rowdata['goodsId'])+"'>查看</a> ";
	            return h;
	        }}
        ]
    });
}
function loadGrid(){
	var params = WST.getParams('.ipt');
	grid.set('url',WST.U('admin/reports/topSaleGoodsByPage',params));
}