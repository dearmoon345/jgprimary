var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:WST.U('admin/wxpassivereplys/textPageQuery'),
		pageSize:WST.pageSize,
		pageSizeOptions:WST.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '关键字', name: 'keyword', isSort: false},
	        { display: '回复内容', name: 'content', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(WST.GRANT.WX_WBXX_02)h += "<a href='"+WST.U('admin/wxpassivereplys/textEdit','id='+rowdata['id'])+"'>修改</a> ";
	            if(WST.GRANT.WX_WBXX_03)h += "<a href='javascript:toDel(" + rowdata['id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function toDel(id){
	var box = WST.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = WST.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(WST.U('admin/wxpassivereplys/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg("操作成功",{icon:1});
	           			    	layer.close(box);
	           		            grid.reload();
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}

function textEditInit(){
 /* 表单验证 */
    $('#replyForm').validator({
            fields: {
                keyword: {
                  rule:"required",
                  msg:{required:"请输入关键字"},
                  tip:"请输入关键字",
                  ok:"",
                },
                content: {
                  rule:"required",
                  msg:{required:"请输入回复内容"},
                  tip:"请输入回复内容",
                  ok:"",
                }
                
            },

          valid: function(form){
            var params = WST.getParams('.ipt');
            var loading = WST.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(WST.U('admin/wxpassivereplys/'+((params.id==0)?"add":"edit")),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toAdminJson(data);
              if(json.status=='1'){
                  WST.msg("操作成功",{icon:1});
                  location.href=WST.U('Admin/wxpassivereplys/text');
              }else{
                    WST.msg(json.msg,{icon:2});
              }
            });

      }

    });




};
  




		