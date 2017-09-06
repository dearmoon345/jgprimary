var grid;
var h;
function initGridMsg(){
	$('.wst-tab-2').height(h-25);
	grid = $("#maingrid-msg").ligerGrid({
		url:WST.U('admin/templatemsgs/pageMsgQuery'),
		pageSize:WST.pageSize,
		pageSizeOptions:WST.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '发送时机', name: 'tplCode', width:120,isSort: false},
	        { display: '发送内容', name: 'tplContent', isSort: false},
	        /*{ display: '状态', name: 'isEnable', width:120,isSort: false,render: function (rowdata, rowindex, value){
              return (rowdata['isEnbale']==1)?'启用':'停用';
          }},*/
	        { display: '操作', name: 'op',isSort: false,width:120,render: function (rowdata, rowindex, value){
	        	  var h="";
	            if(WST.GRANT.XXMB_02)h += "<a href='javascript:toEditMsg(" + rowdata['id'] + ")'>编辑</a> "; 
	            return h;
	        }}
        ]
    });
}
function initGridEmail(){
  $('.wst-tab-2').height(h-25);
  grid = $("#maingrid-email").ligerGrid({
    url:WST.U('admin/templatemsgs/pageEmailQuery'),
    pageSize:WST.pageSize,
    pageSizeOptions:WST.pageSizeOptions,
    height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
          { display: '发送时机', name: 'tplCode', width:120,isSort: false},
          { display: '发送内容', name: 'tplContent', isSort: false},
          /*{ display: '状态', name: 'isEnable', width:120,isSort: false,render: function (rowdata, rowindex, value){
              return (rowdata['isEnbale']==1)?'启用':'停用';
          }},*/
          { display: '操作', name: 'op',isSort: false,width:120,render: function (rowdata, rowindex, value){
              var h="";
              if(WST.GRANT.XXMB_02)h += "<a href='javascript:toEditEmail(" + rowdata['id'] + ")'>编辑</a> "; 
              return h;
          }}
        ]
    });
}
function initGridSMS(){
  $('.wst-tab-2').height(h-25);
  grid = $("#maingrid-sms").ligerGrid({
    url:WST.U('admin/templatemsgs/pageSMSQuery'),
    pageSize:WST.pageSize,
    pageSizeOptions:WST.pageSizeOptions,
    height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
          { display: '发送时机', name: 'tplCode', width:120,isSort: false},
          { display: '发送内容', name: 'tplContent', isSort: false},
          /*{ display: '状态', name: 'isEnable', width:120,isSort: false,render: function (rowdata, rowindex, value){
              return (rowdata['isEnbale']==1)?'启用':'停用';
          }},*/
          { display: '操作', name: 'op',isSort: false,width:120,render: function (rowdata, rowindex, value){
              var h="";
              if(WST.GRANT.XXMB_02)h += "<a href='javascript:toEditSMS(" + rowdata['id'] + ")'>编辑</a> "; 
              return h;
          }}
        ]
    });
}
var editor1;
function initEditor(){
  KindEditor.ready(function(K) {
    editor1 = K.create('textarea[name="tplContent"]', {
      uploadJson : WST.conf.ROOT+'/admin/messages/editorUpload',
      height:'350px',
      allowFileManager : false,
      allowImageUpload : true,
      items:[
              'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
              'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
              'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
              'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
              'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
              'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|','image','table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
              'anchor', 'link', 'unlink', '|', 'about'
      ],
      afterBlur: function(){ this.sync(); }
    });
  });
}

function toEditMsg(id){
    location.href = WST.U('admin/templatemsgs/toEditMsg','id='+id);
}
function toEditEmail(id){
    location.href = WST.U('admin/templatemsgs/toEditEmail','id='+id);
}
function toEditSMS(id){
    location.href = WST.U('admin/templatemsgs/toEditSMS','id='+id);
}
function save(type){
	  var loading = WST.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    var params = WST.getParams('.ipt');
	  $.post(WST.U('admin/templatemsgs/edit'),params,function(data,textStatus){
	      layer.close(loading);
	      var json = WST.toAdminJson(data);
	      if(json.status=='1'){
	          WST.msg("操作成功",{icon:1});
	          location.href = WST.U('admin/templatemsgs/index','src='+type);
	      }else{
	          WST.msg(json.msg,{icon:2});
	      }
	  });
}
var flag1 = false;
var flag2 = false;
var flag3 = false;
//切换卡
$(function (){ 
  h = WST.pageHeight();
  $('.l-tab-content').height(h-32);
  $('.l-tab-content-item').height(h-32);
  $('.l-tab-content-item').css('overflow-y','auto');
  $('.l-tab-loading').remove();
  var tabno = $('#wst-tabs').attr('dataval');
  tab = $("#wst-tabs").ligerTab({
      height: '99%',
      changeHeightOnResize:true,
      showSwitchInTab : false,
      showSwitch: false,
      onAfterSelectTabItem:function(n){
        if(n=='tabitem2' && !flag2){
            initGridEmail();
            flag2 = false;
        }
        if(n=='tabitem1' && !flag1){
            initGridMsg();
            flag1 = false;
        }
        if(n=='tabitem3' && !flag3){
            initGridSMS();
            flag3 = false;
        }
      }
  });
  if($('#wst-tabs').attr('dataval')==1){
      flag2 = true;
      tab.selectTabItem("tabitem2");  
      initGridEmail();
  }
  if($('#wst-tabs').attr('dataval')==0){
      tab.selectTabItem("tabitem1"); 
      flag1 = true;
      initGridMsg();
      
  }
  if($('#wst-tabs').attr('dataval')==2){
      tab.selectTabItem("tabitem3"); 
      flag3 = true;
      initGridSMS();
      
  }
});



		