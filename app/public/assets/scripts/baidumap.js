var baidumap = function() {
	var marker; 
    var map;
	var localSearch;
	var setMarker = function (poi, callback){
		callback && callback(poi);	
		map.clearOverlays();//清空原来的标注
		map.centerAndZoom(poi.point, 16);
		marker = new BMap.Marker(new BMap.Point(poi.point.lng, poi.point.lat));  // 创建标注，为要查询的地方对应的经纬度
		marker.enableDragging();
		map.addOverlay(marker);
		marker.addEventListener("dragend", function(e){
			callback && callback(e); 
		});		
	}
	var options = {
		'id': '',
		'address': '广州市天河区广州天河体育中心',
		'lng': '23.143213',//
		'lat': '113.331301'
	}
    return {     
        init: function(config, callback) { 
			//options = $.extend(options, config);
			options.address = config.address != '' ? config.address : options.address;
			if(config.lng != 0 && config.lat != 0){
				options.lng = config.lng;
				options.lat = config.lat;
			}
			console.log(options, config);
			map = new BMap.Map(options.id);
			if(options.lng > 0 || options.lat > 0){
				options.address = new BMap.Point(options.lng, options.lat);
			}
			map.centerAndZoom(options.address, 16);
			//			
			map.enableScrollWheelZoom();    //启用滚轮放大缩小，默认禁用
			map.enableContinuousZoom();    //启用地图惯性拖拽，默认禁用
			map.addControl(new BMap.NavigationControl());  //添加默认缩放平移控件
			map.addControl(new BMap.OverviewMapControl()); //添加默认缩略地图控件
			map.addControl(new BMap.OverviewMapControl({ isOpen: true, anchor: BMAP_ANCHOR_BOTTOM_RIGHT }));   //右下角，打开	
			
			if(options.lng > 0 || options.lat > 0){
				data = {
					"point": {
						lat: options.lat,
						lng: options.lng
					}
				}				
				setMarker(data, callback);
			}else{
				baidumap.search(options.address, callback);
			}
			return map;
        },
		
		search: function(address, callback){			
			localSearch = new BMap.LocalSearch(map);
			localSearch.enableAutoViewport(); //允许自动调节窗体大小						
			localSearch.setSearchCompleteCallback(function (searchResult) {
				var poi = searchResult.getPoi(0);
				//console.log(searchResult.getPoi(1));
				setMarker(poi, callback);
			});
			localSearch.search(address);
			
		}
    };

}();