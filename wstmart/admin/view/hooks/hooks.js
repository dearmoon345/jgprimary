var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:WST.U('admin/hooks/pageQuery'),
		pageSize:WST.pageSize,
		pageSizeOptions:WST.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '名称', name: 'name', isSort: false},
	        { display: '描述', name: 'hookRemarks', isSort: false},
	        { display: '对应插件', name: 'addons', isSort: false}
        ]
    });
}


//查询
function hooksQuery(){
	var query = WST.getParams('.query');
	grid.set('url',WST.U('admin/hooks/pageQuery',query));
}

