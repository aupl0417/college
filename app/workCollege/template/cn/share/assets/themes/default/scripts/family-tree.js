var Chart = function () {
	// 路径配置
	require.config({
		paths: {
			echarts: 'frame/public/assets/global/plugins/echarts'
		}
	});


	
	
    return {
       
        init: function (charData) {
			require(
				[
					'echarts',
					'echarts/chart/tree'				
				],
			
				function (ec) {
					// 基于准备好的dom，初始化echarts图表
					if($('#familyTree').size() != 1) return false;
					var chartExample1 = ec.init($('#familyTree')[0]); 
					option = {
						title : {
							text: ''
						},
						toolbox: {
							show : true,
							feature : {
								//mark : {show: true},
								//dataView : {show: true, readOnly: false},
								//restore : {show: true},
								saveAsImage : {show: true}
							}
						},
						series : [
							{
								name:'树图',
								type:'tree',
								orient: 'horizontal',  // vertical horizontal
								rootLocation: {x: 100,y: 230}, // 根节点位置  {x: 100, y: 'center'}
								nodePadding: 8,
								layerPadding: 200,
								hoverable: false,
								roam: true,
								symbolSize: 6,
								itemStyle: {
									normal: {
										color: '#4883b4',
										label: {
											show: true,
											position: 'right',
											formatter: "{b}",
											textStyle: {
												color: '#000',
												fontSize: 5
											}
										},
										lineStyle: {
											color: '#ccc',
											type: 'curve' // 'curve'|'broken'|'solid'|'dotted'|'dashed'

										}
									},
									emphasis: {
										color: '#4883b4',
										label: {
											show: false
										},
										borderWidth: 0
									}
								},
								
								data: [charData]
							}
						]
					};
					var ecConfig = require('echarts/config');
					chartExample1.on(ecConfig.EVENT.CLICK, function (param){
						var selected = param.selected;
						var mapSeries = option.series[0];
						var data = [];
						var legendData = [];
						var name;
					  //console.log(param);

					});					
					// 为echarts对象加载数据 
					chartExample1.setOption(option); 
					
					
				}
			);
        }
    };

}();