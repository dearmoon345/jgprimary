var grid;
$(function(){
	$("#startDate").ligerDateEditor();
	$("#endDate").ligerDateEditor();
})
function initSaleGrid(){
	grid = $("#maingrid").ligerGrid({
		url:WST.U('admin/reports/topShopSalesByPage',WST.getParams('.ipt')),
		pageSize:WST.pageSize,
		pageSizeOptions:WST.pageSizeOptions,
		height:'99%',
        width:'100%',
        rowHeight:65,
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '&nbsp;', name: 'shopImg',width:60,align:'left',heightAlign:'left',isSort: false,render: function (rowdata, rowindex, value){
            	return "<img style='height:60px;width:60px;' src='"+WST.conf.ROOT+"/"+rowdata['shopImg']+"'>";
            }},
	        { display: '店铺', name: 'shopName',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['shopName']+"</div>";
	        }},
	        { display: '销售额', name: 'totalMoney',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>￥"+rowdata['totalMoney']+"</div>";
	        }},
	        { display: '订单数', name: 'orderNum',isSort: false,render: function (rowdata, rowindex, value){
	        	return "<div class='goods-valign-m'>"+rowdata['orderNum']+"</div>";
	        }}
        ]
    });
}
function loadGrid(){
	var params = WST.getParams('.ipt');
	grid.set('url',WST.U('admin/reports/topShopSalesByPage',params));
}