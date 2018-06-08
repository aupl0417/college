$('#transfer_detail').handleForm(
{
	rules: {
		source:{
			required: true
		},
		username:{
			required: true
		},
		trueName:{
			required: true
		},
		mobile:{
			required: true
		},
		email:{
			required: true
		},
		IDNum:{
			required: true
		},
		teacherLevel:{
			required: true
		},
		workExperience:{
			required: true
		},
		courseReward:{
			required: true
		},
		teachGrade:{
			required: true
		},
		branchId:{
			required: true
		},
		eduLevel:{
			required: true
		}
	},
	messages: {
		source:{
			required: '请选择讲师来源',
		},
		username:{
			required: '请填写会员名',
		},
		trueName:{
			required: '请填写真实姓名',
		},
		mobile:{
			required: '请填写手机号码',
		},
		email:{
			required: '请填写邮箱',
		},
		IDNum:{
			required: '请填写身份证号码',
		},
		teacherLevel:{
			required: '请选择讲师等级',
		},
		workExperience:{
			required: '请填写讲师从业经历',
		},
		courseReward:{
			required: '请填写讲师课时薪酬',
		},
		teachGrade:{
			required: '请选择授课等级',
		},
		branchId:{
			required: '请选择所属院校',
		},
		eduLevel:{
			required: '请选择讲师学历',
		}
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

function show_reson(obj){
	if(2 == $(obj).val()){
		$('#reason').show();
	}else{
		$('#reason').hide();
	}
}

$("select[name=source]").change(function(){
	var source = $(this).val();
	$('input[name=username]').blur(function(){
		var username = $(this).val();
		$.ajax({
			data:{
				'username': username
			},
			type:'post',
			url:'/student/getUser.json',
			dataType:'json',
			success:function(data){
				if(data.id == '1001' && source!=3){
					var info = data.info;
					$('input[name=trueName]').val(info.name);
					$('input[name=mobile]').val(info.tel);
					$('input[name=email]').val(info.email);
					$('input[name=IDNum]').val(info.certNum);
				}
			}
		});
	});
});

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


