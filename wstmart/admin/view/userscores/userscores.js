var mgrid;

function gridInit(id){
	$("#startDate").ligerDateEditor();
	$("#endDate").ligerDateEditor();
    mgrid = $("#moneyGrid").ligerGrid({
		url:WST.U('admin/userscores/pageQuery','id='+id),
		pageSize:WST.pageSize,
		pageSizeOptions:WST.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '来源', name: 'dataSrc',width:100,isSort: false},
	        { display: '积分变化', name: 'dataSrc',width:100,isSort: false,render: function (rowdata, rowindex, value){
	        	if(rowdata['scoreType']==1){
                    return '<font color="red">+'+rowdata['score']+'</font>';
	        	}else{
                    return '<font color="green">-'+rowdata['score']+'</font>';
	        	}
	        }},
	        { display: '备注', name: 'dataRemarks',isSort: false},
	        { display: '日期', name: 'createTime',width:150,isSort: false}
        ]
    });
}
function loadGrid(id){
	mgrid.set('url',WST.U('admin/userscores/pageQuery','id='+id+"&startDate="+$('#startDate').val()+"&endDate="+'&endDate='+$('#endDate').val()));
}
var w;
function toAdd(id){
	var ll = WST.msg('正在加载信息，请稍候...');
	$.post(WST.U('admin/userscores/toAdd',{id:id}),{},function(data){
		layer.close(ll);
		w =WST.open({type: 1,title:"调节会员积分",shade: [0.6, '#000'],offset:'50px',border: [0],content:data,area: ['550px', '260px']});
	});
}
function editScore(){
	var params = WST.getParams('.ipt');
	var loading = WST.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(WST.U('admin/userscores/add'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = WST.toAdminJson(data);
    	if(json.status=='1'){
    		WST.msg("操作成功",{icon:1});
    		closeFrom();
    		loadGrid(params.userId);
    	}else{
    		WST.msg(json.msg,{icon:2});
    	}
    });
}
function closeFrom(){
    layer.close(w);
}