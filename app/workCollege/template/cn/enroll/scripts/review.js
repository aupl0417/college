$('#formTransfer').handleForm({
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
			required: "请填写理由",
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
			grid.getDataTable().draw(false);
		});
	} else {
		Global.alert(modalOptions);
	}
});

$(function(){
	$('input[name=reason]').attr('disabled', true);
	$('.reason').hide();
	$('#state').change(function(){
		var state = $(this).val();
		if(state == -1){
			$('input[name=reason]').attr('disabled', false);
			$('.reason').show();
		}else if(state == 1) {
			$('input[name=reason]').attr('disabled', true);
			$('.reason').hide();
		}
	});
});
