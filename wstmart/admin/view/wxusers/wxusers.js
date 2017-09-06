var grid;
function initGrid(){
	grid = $('#maingrid').ligerGrid({
		url:WST.U('admin/wxusers/pageQuery'),
		pageSize:100,
		pageSizeOptions:[100],
		height:'99%',
        width:'100%',
        minColToggle:6,
        rowHeight:50,
        rownumbers:true,
        columns: [
            { display: '用户头像', name: 'userPhoto',width: 100,isSort: false,
            	render: function (rowdata){
            		if(rowdata["userPhoto"]){
            		var i = '<span><img style="max-height:100%;" src="'+rowdata["userPhoto"]+'" /></span>';
            		return i;
            		}
            	}
      	    },
	        { display: '用户名称', name: 'userName' ,isSort: false},
            { display: '性别', name: 'userSex',isSort: false,
            	render: function (rowdata){
	        		if(rowdata['userSex']==0)s = "保密";
			        if(rowdata['userSex']==1)s = "男";
			        if(rowdata['userSex']==2)s = "女";
            		return s;
            	}
      	    },
	        { display: '用户所在地', name: 'userAreas',isSort: false},
	        { display: 'openId', name: 'openId',width: 320,isSort: false},
	        { display: '用户关注时间', name: 'subscribeTime',isSort: false,
            	render: function (rowdata){
            		if(WST.blank(rowdata["subscribeTime"]))return rowdata["subscribeTime"];
            	}
	        },
	        { display: '用户备注', name: 'userRemark',isSort: false},
	        { display: '操作', name: 'op',width: 150,isSort: false,
	        	render: function (rowdata){
		            var h = "";
		            if(WST.GRANT.WX_ZDYCD_02)h += "<a href='javascript:toEdit("+rowdata["userId"]+")'>修改备注</a> ";
		            return h;
	        }}
        ]
    });
}

function loadGrid(){
	grid.set('url',WST.U('admin/wxusers/pageQuery','key='+$('#key').val()));
}

//与微信用户管理同步
var userTotal,num=0;
function wxSynchro(){
	var box = WST.confirm({content:"您确定与微信用户管理同步吗?</br>(用户越多同步时间将越久)",yes:function(){
        var loading = WST.msg('正在同步数据，请稍后...', {icon: 16,time:60000});
        $.post(WST.U('admin/wxusers/synchroWx'),'',function(data,textStatus){
        			  layer.close(loading);
        			  var json = WST.toAdminJson(data);
        			  if(json.status=='1'){
        				    userTotal = json.data;
        			    	WST.msg(json.msg,{icon:1});
        			    	layer.close(box);
        		            grid.reload();
        		            wxLoad();
        			  }else{
        			    	WST.msg(json.msg,{icon:2});
        			  }
        		});
         }});
}

function wxLoad(){
		id = userTotal[num]['openId'];
        $.post(WST.U('admin/wxusers/wxLoad'),{id:id},function(data,textStatus){
        			  var json = WST.toAdminJson(data);
        			  if(json.status=='1'){
        				    if(num < userTotal.length-1){
        				    	num++
        				    	WST.msg("当前正在同步第"+num+"个用户,进度"+num+"/"+userTotal.length);
        				    	wxLoad();
        				        return;
        				    }else{
            			    	num=0;
            			    	WST.msg("同步完成",{icon:1});
            		            grid.reload();
        				    }
        			  }else{
        			    	WST.msg(json.msg,{icon:2});
        			  }
        		});
}

function toEdit(id){
	$('#wxusersForm')[0].reset();
		$.post(WST.U('admin/wxusers/getById'),{id:id},function(data,textStatus){
			var json = WST.toAdminJson(data);
			if(json){
				WST.setValues(json);
				var box = WST.open({title:'修改备注',type:1,content:$('#wxusersBox'),area: ['460px', '160px'],btn:['确定','取消'],yes:function(){
						if(!$('#userRemark').isValid())return;
				        var params = WST.getParams('.ipt');
				        params.id = id;
				        var loading = WST.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
			    		$.post(WST.U('admin/wxusers/edit'),params,function(data,textStatus){
			    			  layer.close(loading);
			    			  var json = WST.toAdminJson(data);
			    			  if(json.status=='1'){
			    			    	WST.msg(json.msg,{icon:1});
			    			    	layer.close(box);
			    			    	grid.reload(params.parentId);
			    			  }else{
			    			        WST.msg(json.msg,{icon:2});
			    			  }
			    		});
				          }});
			}
		});
}