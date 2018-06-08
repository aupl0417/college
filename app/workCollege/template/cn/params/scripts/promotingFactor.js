var Params = function () {
	var dataGrid = new Datatable();

	var chart, chartData;
	function initChart(data){
		chartData = data;
		$('#chartdiv').remove();
		$('#block-chart').append($('<div id="chartdiv" style="width: 100%; height: 400px;"></div>'));
	
		chart = AmCharts.makeChart("chartdiv", {
			type: "serial",
			
			// setting language
			language: "zh",
			
			dataProvider: chartData,
			categoryField: "date",
			categoryAxis: {
				parseDates: true,
				gridAlpha: 0.15,
				minorGridEnabled: true,
				axisColor: "#DADADA"
			},
			valueAxes: [{
				axisAlpha: 0.2,
				id: "v1"
			}],
			graphs: [
				{
					type: "step",
					title: "推广系数",
					id: "g1",
					valueAxis: "v1",
					valueField: "val",
					bullet: "round",
					/* bulletField: "bullet", */
					bulletBorderColor: "#FFFFFF",
					bulletBorderAlpha: 1,
					lineThickness: 2,
					lineColor: "#b5030d",
					negativeLineColor: "#0352b5",
					hideBulletsCount:30,
					balloonText: "[[time]]<br><b><span style='font-size:14px;'>推广系数: [[value]]</span></b>",
					//customBullet: "images/star.png",
/* 					bulletSize: 12,
					customBulletField: "customBullet" */
				}
			],
			chartCursor: {
				fullWidth:true,
				cursorAlpha:0.1,
				pan: true,
				categoryBalloonDateFormat: 'YYYY-MM-DD'
			},
			chartScrollbar: {
				scrollbarHeight: 40,
				color: "#FFFFFF",
				autoGridCount: true,
				graph: "g1"
			}
		});
		
		chart.addListener("dataUpdated", zoomChart);
		
	}
	
	
	function zoomChart() {
		chart.zoomToIndexes(chartData.length - 40, chartData.length);
	}
	
	// changes cursor mode from pan to select
	function setPanSelect() {
		var chartCursor = chart.chartCursor;
		chartCursor.pan = !chartCursor.pan;
		chartCursor.zoomable = !chartCursor.zoomable;
		chart.validateNow();
	}	
	return {
		chart: function(type){
			$.ajax({
				url: "/params/promotingFactorChart.json",
				data:{
					type : type,  
				},
				type: "get",
				cache:false,
				dataType:'json',
				success: function (data) {					
					if (data.id == '1001') {
						initChart(data.info);
						zoomChart();
					}
				}
			});			
	
		},
		
		initParamList: function () {
			var table = $("#paramList");
			var columns = [];
			$('thead > tr > th', table).each(function(){
				!!$(this).data('dt') && columns.push({
					"data" : $(this).data('dt'),
					"sortable": !!$(this).data('sort'),
					"visible": !$(this).data('hide')
				});
			});

			dataGrid.init({
				src : table,
				dataTable : {
					"ajax" : {
						"url" : "/params/promotingFactor.json"//"/public/empty.json"
					},
					"columns" :columns,
					"order" : [
						[2, "desc"]
					]
				}
			});
			return dataGrid;
		},
		
		delete: function(id){
			bootbox.confirm('确定删除吗?', function(res){
				if(res){
					$.ajax({
						'url' : '/params/delPromotingFactor.json?id='+id,
						'dataType' : 'JSON',
						'success' : function(data){
							if(data.id == '1001'){
								bootbox.alert(data.msg, function() {
									grid.getDataTable().ajax.reload(null, false);
								});
							}else{
								var msg = data.info || data.msg;
								bootbox.alert(msg);
							}
						}
					});
				}
			});
		}
	};

}();


/* 	 */