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
}

$('.add').click(function(){
	var $obj = $(this).parent();
	var selectString = $(this).parent().children().eq(0).html();
	var html = '<div class="row" style="margin-top:10px;">';
		html += 	'<div class="col-md-8">' + selectString + '</div>';
		html += 	'<div class="col-md-2"><input type="text" name="score[]" class="form-control score" onblur="getTotal(this);" placeholder="分值"/></div>';
		html +=     '<label class="control-label delete"><font style="margin-top:-2px;font-size:18px;color:blue;">-</font></label>';
		html += '</div>';
		html = html.replace('selected="selected"', ''); 
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
			'courseId': courseId,
			'cid' : itemId
		},
		type:'post',
		url:'/itemPool/getExamList.json',
		dataType:'json',
		success:function(data){
			var html = '';
			if(data.id == '1001'){
				$('#examList').html('');
				var info = data.info;
				console.log(info);
				var length = info.length;
				var totalScore = 0;
				if(length > 0){
					for(var i=0; i<length; i++){
						html += "<div class='row'>";
				        html +=		"<div class='col-md-8'>";
				        html +=		"<select class='form-control exam' onchange='selectItem(this);'>";
				        html +=			"<option value=''>--请选择--</option>";
				        html +=			info[i].listString;
				        html +=		"</select>";
				        html +=		"<input type='hidden' name='question[]' value='" + info[i].id + "'/>";
						html +=	"</div>";
				        html += "<div class='col-md-2'><input type='text' name='score[]' class='form-control score' onblur='getTotal(this);' value='" + info[i].score + "'/></div>"
				        if(i == length - 1){
				        	html +=	"<label class='control-label add'>+</label>";
				        }else {
				        	html +=	"<label class='control-label add'>-</label>";
				        }
				      	html += "</div>";
				      	totalScore = parseInt(totalScore) + parseInt(info[i].score);
					}
				}else {
					var html = '';
					html += "<div class='row'>";
			        html +=		"<div class='col-md-8'>";
			        html +=		"<select class='form-control exam' onchange='selectItem(this);'>";
			        html +=			"<option value=''>--请选择--</option>";
			        html +=		"</select>";
			        html +=		"<input type='hidden' name='question[]'/>";
					html +=	"</div>";
			        html += "<div class='col-md-2'><input type='text' name='score[]' class='form-control score' onblur='getTotal(this);' value=''  placeholder='分值'/></div>"
			        html +=	"<label class='control-label add'>+</label>";
			      	html += "</div>";
				}
				$('#examList').append(html);
				$('input[name=totalScore]').val(totalScore);
			}
		}
	});
});
