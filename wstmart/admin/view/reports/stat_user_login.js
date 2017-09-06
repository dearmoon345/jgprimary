$(function(){
    $("#startDate").ligerDateEditor();
	$("#endDate").ligerDateEditor();
    loadStat();
});
function loadStat(){
    var loading = WST.msg('正在查询数据，请稍后...', {icon: 16,time:60000});
	$.post(WST.U('admin/reports/statUserLogin'),WST.getParams('.ipt'),function(data,textStatus){
	    layer.close(loading);
	    var json = WST.toAdminJson(data);
	    var myChart = echarts.init(document.getElementById('main'));
	    myChart.clear();
	    if(json.status=='1'){
			var option = {
			    tooltip: {
			        trigger: 'axis'
			    },
			    legend: {
			        data:['会员','店铺']
			    },
			    toolbox: {
			        show : true,
			        feature : {
			            mark : {show: true},
			            dataView : {show: false, readOnly: false},
			            magicType : {show: true, type: ['line', 'bar']},
			            restore : {show: true},
			            saveAsImage : {show: true}
			        }
			    },
			    xAxis: {
			        type: 'category',
			        boundaryGap: false,
			        data: json.data.days
			    },
			    yAxis: {
			        type: 'value'
			    },
			    series: [
			        {
			            name:'会员',
			            type:'line',
			            data:json.data.u0
			        },
			        {
			            name:'店铺',
			            type:'line',
			            data:json.data.u1
			        }
			    ]
			};
                    
			myChart.setOption(option);
	    }
	});  
}