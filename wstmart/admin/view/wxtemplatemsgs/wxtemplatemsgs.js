var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:WST.U('admin/wxtemplatemsgs/pageQuery'),
		pageSize:WST.pageSize,
		pageSizeOptions:WST.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '发送时机', name: 'tplCode', width:120,isSort: false},
            { display: '模板ID', name: 'tplExternaId', isSort: false},
	        { display: '发送内容', name: 'tplContent', isSort: false},
	        /*{ display: '状态', name: 'isEnable', width:120,isSort: false,render: function (rowdata, rowindex, value){
              return (rowdata['isEnbale']==1)?'启用':'停用';
          }},*/
	        { display: '操作', name: 'op',isSort: false,width:120,render: function (rowdata, rowindex, value){
	        	  var h="";
	            if(WST.GRANT.XXMB_02)h += "<a href='javascript:toEdit(" + rowdata['id'] + ")'>编辑</a> "; 
	            return h;
	        }}
        ]
    });
}
function initParamGrid(){
	var loading = WST.msg('正在加载数据，请稍后...', {icon: 16,time:60000});
    var params = {parentId:$('#id').val()};
	$.post(WST.U('admin/wxtemplatemsgs/listQuery'),params,function(data,textStatus){
	    layer.close(loading);
	    var json = WST.toAdminJson(data);
	    if(json.status=='1'){
	    	childrenNum = json.data?json.data.length:0;
	        var gettpl = document.getElementById('paramjs').innerHTML;
	       	laytpl(gettpl).render(json.data, function(html){
	       		$('#paramlist').html(html);
	       	});
	    }
	});
}
var childrenNum = 0;
function addNewRow(){
	var html = ['<tr id="tr_'+childrenNum+'">',
		'<td><input type="text" style="width:92%" id="fiedlCode_'+childrenNum+'"/></td>',
		'<td><input type="text" style="width:98%" id="fiedlVal_'+childrenNum+'"/></td>',
		'<td><input type="button" value="删除" class="btn btn-red" onclick="javascript:deleteRow('+childrenNum+')"></td>',
		'</tr>'
    ];
    $('#paramlist').append(html.join(''));
    childrenNum++;
}
function deleteRow(n){
    $('#tr_'+n).remove();
}
function toEdit(id){
    location.href = WST.U('admin/wxtemplatemsgs/toEdit','id='+id);
}
function save(type){
	var loading = WST.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    var params = WST.getParams('.ipt');
    params.num = childrenNum;
    for(var i=0;i<=params.num;i++){
    	if($.trim($('#fiedlCode_'+i).val())!=''){
	        params['code_'+i] = $('#fiedlCode_'+i).val();
	        params['val_'+i] = $('#fiedlVal_'+i).val();
    	}
    }
	$.post(WST.U('admin/wxtemplatemsgs/edit'),params,function(data,textStatus){
	    layer.close(loading);
	    var json = WST.toAdminJson(data);
	    if(json.status=='1'){
	        WST.msg("操作成功",{icon:1});
	        location.href = WST.U('admin/wxtemplatemsgs/index','src='+type);
	    }else{
	        WST.msg(json.msg,{icon:2});
	    }
	});
}



		