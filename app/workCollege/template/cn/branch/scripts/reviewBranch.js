$('#formBranch').handleForm({
	rules: {
		'state': {
			required: true,
		},
		'reason': {
			required: true,
		} 
	},
	messages: {
		'state': {
			required: "请填选择审核落地",
		},
		'reason': {
			required: "请填选择审核落地",
		}
	},
	closest: '.form-group',
},
function(data, statusText) {
	var modalOptions = {
		"container": "#formBranch",
		"place": "prepend",
		"type": "warning",
		"message": '',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};
	modalOptions.message = data.info || data.msg;
	if (data.id == '1001') {
		bootbox.alert(data.msg, function() {
			$('#temp-modal-power').modal('hide');
			grid.getDataTable().ajax.reload(null, false);
		});
	} else {
		Global.alert(modalOptions);
	}
});

$('.province').change(function(){
	var pid = $(this).val();
	$.ajax({
		data:{
			'id': pid,
		},
		type:'post',
		url:'/branch/getArea.json',
		dataType:'json',
		success:function(data){
			if(data.id == '1001'){
				$('.cityCode').html('');
				var html = '<option value="">--请选择--</option>';
				var info = data.info;
				var length = info.length;
				for(var i=0; i<length; i++){
					html += '<option value="' + info[i].id +'">' + info[i].name + '</option>';
				}
				$('.cityCode').append(html);
			}
		}
	});
});

$('.cityCode').change(function(){
	var pid = $(this).val();
	$.ajax({
		data:{
			'id': pid,
		},
		type:'post',
		url:'/branch/getArea.json',
		dataType:'json',
		success:function(data){
			if(data.id == '1001'){
				$('.conty').html('');
				var html = '<option value="">--请选择--</option>';
				var info = data.info;
				var length = info.length;
				for(var i=0; i<length; i++){
					html += '<option value="' + info[i].id +'">' + info[i].name + '</option>';
				}
				$('.conty').append(html);
			}
		}
	});
});

