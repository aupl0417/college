$('#formTransfer').handleForm({
	rules: {
		'purpos': {
			required: true,
		}
	},
	messages: {
		'purpos': {
			required: "请填写转让人",
		}
	}, 
	closest: '.form-group',
},
function(data, statusText) {
	var modalOptions = {
		"container": "#formTransfer",
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
			$('#formModal').modal('hide');
			grid.getDataTable().ajax.reload(null, false);
		});
	} else {
		Global.alert(modalOptions);
	}
});

$('.check').on('click', function(){
	var purposName = $('input[name=purpos]').val();
	var id = $('input[name=id]').val();
	$.ajax({
		data:{
			'username': purposName,
			'id'      : id,
		},
		type:'post',
		url:'/enroll/getUser.json',
		dataType:'json',
		success:function(data){
			$('.userInfo').html('');
			if(data.id == '1001'){
				var html = '';
				var info = data.info;
				html += info.trueName + '　　'　 + info.mobile;
				$('input[name=purId]').val(info.id);
				$('.btn-default').attr('disabled', false);
				$('.userInfo').html(html);
			}else {
				$('.userInfo').html(data.info);
			}
		}
	});
});
