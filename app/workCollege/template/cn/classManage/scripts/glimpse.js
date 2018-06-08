$('.imageList').hover(function(){
	  var $objImg = $(this).find('div').eq(0).children('.logo-show');
	  var cls = $objImg.attr('class');
	  if(cls == 'logo-show'){
		  $objImg.css('top', '-237px');
	  }
	  var cls = $(this).find('menu-default').css('display', 'inline');
	  $(this).find('ul').show();
  },function(){
	  var $objImg = $(this).find('div').eq(0).children('.logo-show');
	  $objImg.css('top', '-200px');
	  $(this).find('ul').hide();
	  $(this).find('ul').find('li').hide();
	  $(this).find('ul').removeClass('show-menu');
  });
  
  var i = 0;
  $('.menu').click(function(){
	  var cls = $(this).next().attr('class');
	  if(cls != ''){
		  $(this).next().css('top', '-367px');
	  }

	  if(++i % 2 == 1){//奇数次点击收起列表
		  $(this).addClass('show-menu');
		  $(this).find('li').show();
	  }else {
		  $(this).next().css('top', '-237px');
		  $(this).removeClass('show-menu');
		  $(this).find('li').hide();
	  }
  });
  
  $('.menu').find('li').click(function(){
	  var index = $(this).attr('index');
	  var pictureId = $(this).data('id');
	  
	  if(index != 0){//删除、上移、下移、设为封面
		  $.ajax({
				data:{
					'id': pictureId,
					'act' : index
				},
				type:'post',
				url:'/classManage/glimpse.json',
				dataType:'json',
				success:function(result){
					if(result.id == '1001'){
						var domain   = window.location.host;
						var protocol = window.location.protocol
						var url = protocol + '//' + domain + '/?return=/classManage/glimpse?clID=' + classId + '&root=1';
	                    window.location.href = url;
					}else {
						bootbox.alert(result.info);
					}
				}
			});
		}else {//编辑
			var $obj = $(this).parent().parent().next();
			var title = $.trim($obj.html());
			
			//形成编辑状态
			$obj.html('')
			var html = '<input name="title" type="text" value="' + title + '" maxlength="30" class="form-control input-sm">';
			$obj.html(html);
			
			//实时监听输入，并刷新保存
			var $objInput = $obj.find('input');
			$objInput.bind('input propertychange', function(){
				var newTitle = $objInput.val();
				var length   = newTitle.length;
				if(length <= 30){
					ajaxInfo($(this), pictureId, index, newTitle);
				}
		  	});
			
			//如果没有输入，则恢复
			$objInput.blur(function(){
				$obj.html($(this).val());
			});
	  }
  });
  
  function ajaxInfo(obj, id, act, title){
	  obj.blur(function(){
		  $.ajax({
				data:{
					'id': id,
					'act' : act,
					'title' : title
				},
				type:'post',
				url:'/classManage/glimpse.json',
				dataType:'json',
				success:function(result){
					if(result.id == '1001'){
						obj.parent().html(title);
					}
				}
			});
	  });
  }