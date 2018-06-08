$('#transfer_detail').handleForm({
	rules: {
		name:{
			required: true
		},
		badgeName:{
			required: true
		},
		courseLevel:{
			required: true
		},
		courseType:{
			required: true
		},
		condition:{
			required: true
		},
	},
	messages: {
		name:{
			required: '请填写等级名称'
		},
		badgeName:{
			required: '请填写徽章名称'
		},
		courseLevel:{
			required: '请选择授课等级'
		},
		courseType:{
			required: '请选择授课类型'
		},
		condition:{
			required: '请填写晋升条件'
		},
	},
	closest: 'td',
	ass: {

	}
},
	function(data, statusText){
		if(data.id == '1001'){
			bootbox.alert('操作成功', function() {
				$('#temp-modal-power').modal('hide');
				Grid.getDataTable().ajax.reload(null, false);
			});
		}else{
			Global.alert( {
				"container": "#transfer_detail",
				"place": "prepend",
				"type": "warning",
				"message": data.info,
				"close": true,
				"reset": true,
				"focus": true,
				"closeInSeconds": "0",
				"icon": "warning"
			});
		}
	}
);

var flowModalUpload = function() {
	$('.modalUpload').each(function(i) {
		$(this).handleUpload(function(data) {
			if (data.status == 'success') {
				var fileinput = $(".modalUpload:eq(" + i + ")").prev('.fileinput');
				$("input[type='hidden']", fileinput).val(data.savename);
				$('.thumbnail img', fileinput).prop('src', data.filename + '?r=' + Math.random());
				$('.thumbnail a.fancybox-button', fileinput).prop('href', data.filename + '?r=' + Math.random());
			} else {
				return false;
			}
		});
	});
};

flowModalUpload();
