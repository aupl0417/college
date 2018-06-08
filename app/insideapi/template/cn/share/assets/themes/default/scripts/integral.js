var Integral = function () {
	// 路径配置
	require.config({
		paths: {
			echarts: ASSETS + '/assets/global/plugins/echarts'
		}
	});
	
	//积分周报表
    var echartIntegral = function () {      
        require(
            [
                'echarts',
				'echarts/chart/line',
                'echarts/chart/bar' // 使用柱状图就加载bar模块，按需加载
				
            ],
            function (ec) {
                // 基于准备好的dom，初始化echarts图表
				if($('#integral').size() != 1) return false;
                var chartExample1 = ec.init($('#integral')[0]); 
				option = {
					title : {
						text: '周积分报表(2015-10-05至2015-10-11)',
						subtext: '大唐国技'
					},
					legend: {
						data:['当天登录的白积分总数','当天库存积分充值总数','上一天返还单元总数','上一天单元权重返还积分','上一天返还积分总数']
					},
					
					tooltip : {         // Option config. Can be overwrited by series or data
						trigger: 'axis',
						//show: true,   //default true
						showDelay: 0,
						hideDelay: 50,
						transitionDuration:0,
						backgroundColor : 'rgba(25,25,25,0.7)',
						borderColor : '#333333',
						borderRadius : 8,
						borderWidth: 1,
						padding: 10,    // [5, 10, 15, 20]
						position : function(p) {
							// 位置回调
							// console.log && console.log(p);
							return [p[0] + 10, p[1] - 10];
						},
						textStyle : {							
							decoration: 'none',
							fontFamily: 'Verdana, sans-serif',
							fontSize: 14,
							fontWeight: 'bold'
						},
						formatter: function (params) {
							//console.log(params);
							var res = params[0].name + '';
							var units = [1000000,10000000,1000000,1,10000000];
							for (var i = 0, l = params.length; i < l; i++) {
								res += '<br/>' + params[i].seriesName + ' : ' + params[i].series.unit.mul(params[i].value);
							}														
							return res;
						}
						//formatter: "Template formatter: <br/>{b}<br/>{a}:{c}<br/>{a1}:{c1}"
					},
					toolbox: {
						show : true,
						feature : {
							//mark : {show: true},
							//dataView : {show: true, readOnly: false},
							magicType : {show: true, type: ['line', 'bar']},
							//restore : {show: true},
							saveAsImage : {show: true}
						}
					},
					calculable : false,
					xAxis : {
						data : ['10-05 周一','10-06 周二','10-07 周三','10-08 周四','10-09 周五','10-10 周六','10-11 周日']
					},
					yAxis : {
						type : 'value'
					},
					series : [
						{
							name:'当天登录的白积分总数',
							type:'bar',
							unit: 1000000,
							data:[								
								3.20, 3.32, 3.01, 3.34, 3.90, 3.30, 3.20
							]
						},
						{
							name:'当天库存积分充值总数',
							type:'bar',
							unit: 10000000,
							data:[9.8524730, 9.7524730, 11.9524730, 9.5524730, 10.9524730, 9.8524730, 9.9524730]
						},
						{
							name:'上一天返还单元总数',
							type:'bar',
							unit: 1000000,
							data:[8.273933, 8.073933, 7.973933, 7.873933, 8.373933, 8.173933, 8.273933]
						},
						{
							name:'上一天单元权重返还积分',
							type:'bar',
							unit: 1,
							data:[5.02, 5.12, 4.97, 5.05, 4.89, 5.11, 5.22]
						},
						{
							name:'上一天返还积分总数',
							type:'bar',
							unit: 10000000,
							data:[4.580843801, 4.180843801, 4.280843801, 3.980843801, 3.880843801, 4.280843801, 4.080843801]
						}
					]
				};																					
        
                // 为echarts对象加载数据 
                chartExample1.setOption(option); 
				
				
            }
        );
    }
	
	
    return {
       
        init: function () {
            echartIntegral();
        }
    };

}();