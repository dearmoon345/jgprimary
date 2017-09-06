var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:WST.U('admin/addons/pageQuery'),
		pageSize:WST.pageSize,
		pageSizeOptions:WST.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '名称', name: 'title', isSort: false,width:150},
	        { display: '标识', name: 'name', isSort: false,width:100},
	        { display: '描述', name: 'description', isSort: false},
	        { display: '状态', name: 'statusName', isSort: false,width:60},
	        { display: '作者', name: 'author', isSort: false,width:80},
	        { display: '版本', name: 'version', isSort: false,width:60},
	        { display: '操作', name: 'op',isSort: false,width:100,render: function (rowdata, rowindex, value){
	        	var h = "";
	            if(WST.GRANT.CJGL_01 && rowdata['status']>0 && rowdata['isConfig']==1)h += "<a href='"+WST.U('admin/Addons/toEdit','id='+rowdata['addonId'])+"'>设置</a> ";
	            if(WST.GRANT.CJGL_02 && rowdata['status']==0)h += "<a href='javascript:install(" + rowdata['addonId'] + ")'>安装</a> "; 
	            if(WST.GRANT.CJGL_03 && rowdata['status']>0)h += "<a href='javascript:uninstall(" + rowdata['addonId'] + ")'>卸载</a> "; 
	            if(WST.GRANT.CJGL_04 && rowdata['status']==2)h += "<a href='javascript:enable(" + rowdata['addonId'] + ")'>启用</a> "; 
	            if(WST.GRANT.CJGL_05 && rowdata['status']==1)h += "<a href='javascript:disable(" + rowdata['addonId'] + ")'>禁用</a> ";
	            return h;
	        }}
        ]
    });
}

//安装
function install(id){
	var loading = WST.msg('正在安装，请稍后...', {icon: 16,time:60000});
	$.post(WST.U('admin/addons/install'),{id:id},function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
	       	WST.msg("安装成功,请刷页面",{icon:1});
	        layer.close(loading);
	         grid.reload();
		}else{
			WST.msg(json.msg,{icon:2});
	     }
	});
}

//卸载
function uninstall(id){
	var box = WST.confirm({content:"您确定要卸载吗?",yes:function(){
		var loading = WST.msg('正在卸载，请稍后...', {icon: 16,time:60000});
		$.post(WST.U('admin/addons/uninstall'),{id:id},function(data,textStatus){
			layer.close(loading);
			var json = WST.toAdminJson(data);
			if(json.status=='1'){
	        	WST.msg("卸载成功,请刷页面",{icon:1});
	         	layer.close(box);
	         	grid.reload();
			}else{
				WST.msg(json.msg,{icon:2});
	     	}
		});
	}});
}

//禁用
function enable(id){
	var loading = WST.msg('正在启用，请稍后...', {icon: 16,time:60000});
	$.post(WST.U('admin/addons/enable'),{id:id},function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
	       	WST.msg("启用成功",{icon:1});
	        layer.close(loading);
	         grid.reload();
		}else{
			WST.msg(json.msg,{icon:2});
	     }
	});
}

//启用
function disable(id){
	var loading = WST.msg('正在禁用，请稍后...', {icon: 16,time:60000});
	$.post(WST.U('admin/addons/disable'),{id:id},function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
	       	WST.msg("禁用成功",{icon:1});
	        layer.close(loading);
	         grid.reload();
		}else{
			WST.msg(json.msg,{icon:2});
	     }
	});
}

//查询
function addonsQuery(){
	var query = WST.getParams('.query');
	grid.set('url',WST.U('admin/addons/pageQuery',query));
}

