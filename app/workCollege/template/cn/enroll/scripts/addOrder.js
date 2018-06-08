$('#formOrder').handleForm({
	rules: {
		'userInfo': {
			required: true,
		},
		'className': {
			required: true,
		},
		'classId': {
			required: true,
		},
		'state': { 
			required: true,
		},
	},
	messages: {
		'userInfo': {
			required: "请填写用户名或者联系电话",
		},
		'className': {
			required: "请填写班级",
		},
		'classId': {
			required: "请选择班级",
		},
		'state': {
			required: "请选择状态",
		},
	},
	closest: '.form-group',
},
function(data, statusText) {
	var modalOptions = {
		"container": "#formOrder",
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

$('.check').click(function(){
	var userInfo = $('input[name=userInfo]').val();
	$.ajax({
		data:{
			'userInfo': userInfo,
		},
		type:'post',
		url:'/enroll/getUserInfo.json',
		dataType:'json',
		success:function(data){
			$('.userInfo').html('');
			if(data.id == '1001'){
				var html = '';
				var info = data.info;
				info.trueName = info.trueName || info.nick;
				info.mobile = info.mobile || ' - ';
				html += info.trueName + '　　'　 + info.mobile;
				$('input[name=uid]').val(info.id);
				$('.userInfo').html(html);
			}else {
				$('.userInfo').html(data.info);
			}
		}
	});
});

$('input[name=className]').blur(function(){
	var className = $(this).val();
	$.ajax({
		data:{
			'className': className,
		},
		type:'post',
		url:'/enroll/getClass.json',
		dataType:'json',
		success:function(data){
			$('.classId').html('');
			var html = '<option value="">--请选择--</option>';
			if(data.id == '1001'){
				var info = data.info;
				var length = info.length;
				for(var i=0; i<length; i++){
					html += '<option value="' + info[i].id + '">' + info[i].className +'</option>'
				}
				$('.classId').html(html);
			}else {
				$('.classId').html(html);
			}
		}
	});
});

$('.classId').change(function(){
	var classId = $(this).val();
	var className = $('option:selected', $(this)).text();
	$.ajax({
		data:{
			'classId': classId,
		},
		type:'post',
		url:'/enroll/getClassInfo.json',
		dataType:'json',
		success:function(data){
			if(data.id == '1001'){
				$('input[name=className]').val(className);
				$('input[name=fee]').val(data.info.cl_cost);
				$('input[name=payFee]').val(0);
			}
		}
	});
});
$('input[name=className]').trigger('blur');