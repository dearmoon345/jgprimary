var ugrid,sgrid,mgrid,h;
$(function(){
	var flag = true;
	h = WST.pageHeight();
	$('.l-tab-content').height(h-32);
	$('.l-tab-content-item').height(h-32);
	$('.l-tab-content-item').css('overflow','hidden');
	var tab = $("#wst-tabs").ligerTab({
         height: '99%',
         changeHeightOnResize:true,
         showSwitchInTab : false,
         showSwitch: false,
         onAfterSelectTabItem:function(n){
           if(n=='shops'){
              if(flag){
                shopGridInit();
                flag = false;
              }
           }
         }
    });
    userGridInit();
})
function userGridInit(){
	ugrid = $("#userGrid").ligerGrid({
		url:WST.U('admin/logmoneys/pageQueryByUser'),
		pageSize:WST.pageSize,
		pageSizeOptions:WST.pageSizeOptions,
		height:h-68,
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '账号', name: 'loginName',isSort: false},
	        { display: '名称', name: 'userName',isSort: false},
	        { display: '可用金额', name: 'userMoney',isSort: false},
	        { display: '冻结金额', name: 'lockMoney',isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	        	return '<a href="javascript:tologmoneys(0,'+rowdata['userId']+')">查看</a>';
	        }}
        ]
    });
}
function shopGridInit(){
	sgrid = $("#shopGrid").ligerGrid({
		url:WST.U('admin/logmoneys/pageQueryByShop'),
		pageSize:WST.pageSize,
		pageSizeOptions:WST.pageSizeOptions,
		height:h-68,
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '账号', name: 'loginName',isSort: false},
	        { display: '商家', name: 'shopName',isSort: false},
	        { display: '可用金额', name: 'shopMoney',isSort: false},
	        { display: '冻结金额', name: 'lockMoney',isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	        	return '<a href="javascript:tologmoneys(1,'+rowdata['shopId']+')">查看</a>';
	        }}
        ]
    });
}
function loadUserGrid(){
	ugrid.set('url',WST.U('admin/logmoneys/pageQueryByUser','key='+$('#key1').val()+"&page=0"));
}
function loadShopGrid(){
	sgrid.set('url',WST.U('admin/logmoneys/pageQueryByShop','key='+$('#key2').val()+"&page=0"));
}
function tologmoneys(t,id){
	location.href= WST.U('admin/logmoneys/tologmoneys','id='+id+"&type="+t+"&startDate="+$('#startDate').val()+"&endDate="+'&endDate='+$('#endDate').val());
}

function moneyGridInit(type,id){
	$("#startDate").ligerDateEditor();
	$("#endDate").ligerDateEditor();
    mgrid = $("#moneyGrid").ligerGrid({
		url:WST.U('admin/logmoneys/pageQuery','type='+type+'&id='+id),
		pageSize:WST.pageSize,
		pageSizeOptions:WST.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '来源', name: 'dataSrc',width:100,isSort: false},
	        { display: '金额', name: 'dataSrc',width:100,isSort: false,render: function (rowdata, rowindex, value){
	        	if(rowdata['moneyType']==1){
                    return '<font color="red">+'+rowdata['money']+'</font>';
	        	}else{
                    return '<font color="green">-'+rowdata['money']+'</font>';
	        	}
	        }},
	        { display: '备注', name: 'remark',isSort: false},
	        { display: '外部流水', name: 'tradeNo',width:200,isSort: false},
	        { display: '日期', name: 'createTime',width:150,isSort: false}
        ]
    });
}

function loadMoneyGrid(t,id){
	mgrid.set('url',WST.U('admin/logmoneys/pageQuery','id='+id+"&type="+t+"&startDate="+$('#startDate').val()+"&endDate="+'&endDate='+$('#endDate').val()));
}