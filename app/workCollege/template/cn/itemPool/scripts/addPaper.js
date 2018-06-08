$('#transfer_detail').handleForm({
	rules: {
		'courseId': {
			required: true,
		},
		'name': {
			required: true,
		},
		'question': {
			required: true,
		},
		'score': {
			required: true,
		}
	},
	messages: {
		'courseId': {
			required: "请选择所属课程",
		},
		'name': {
			required: "请填写考卷名称",
		},
		'question': {
			required: '请填选择题目',
		},
		'score': {
			required: '请填写分值',
		}
	},
	closest: '.form-group',
},
function(data, statusText) {
	var modalOptions = {
		"container": "#formCourse",
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
		bootbox.alert(data.info, function() {
			$('#temp-modal-power').modal('hide');
			Grid.getDataTable().ajax.reload(null, false);
		});
	} else {
		Global.alert(modalOptions);
	}
});

function selectItem(obj){
	var val = $(obj).val();
	$(obj).next().val(val);
	console.log(val);
}

$('.add').click(function(){
	var $obj = $(this).parent();
	var html = '<div class="row" style="margin-top:10px;">';
		html += $obj.html();
		html += '</div>';
		html = html.replace('+', '-');
		html = html.replace('add', 'delete');
	$($obj.parent()).append(html);
	
	deleteItem();
});

deleteItem();

function deleteItem(){
	$('.delete').click(function(){
		$($(this).parent()).remove();
	});
}

function getTotal(obj){
	totalScore = 0;
	$('.score').each(function(){
		var score = $(this).val();
		if(score == ''){
			score = 0;
		}
		totalScore = parseInt(totalScore) + parseInt(score);
	});
	
	$('input[name=totalScore]').val(totalScore);
}

$('.courseSelect').change(function(){
	var courseId = $(this).val();
	$.ajax({
		data:{
			'courseId': courseId
		},
		type:'post',
		url:'/itemPool/getExamList.json',
		dataType:'json',
		success:function(data){
			var html = "<option value=''>--请选择--</option>";
			if(data.id == '1001'){
				$('.exam').html('');
				html += data.info; 
				$('.exam').append(html);
			}
			
			
		}
	});
});

