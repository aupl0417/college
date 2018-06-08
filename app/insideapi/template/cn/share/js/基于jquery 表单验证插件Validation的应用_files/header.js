// JavaScript Document
$(function(){
	$("div.top ul li").mouseover(function(){
		$("div.top ul li").css("background","none");
		$(this).css("background","#194876");
	});
	$("div.banner .in form input[type=\"text\"]").focus(function(){
		if($(this).val()=="«Î ‰»Îπÿº¸◊÷"){
			$(this).val("");
		}
	})
	$("div.banner .in form input[type=\"text\"]").blur(function(){
		if($(this).val()==""){
			$(this).val("«Î ‰»Îπÿº¸◊÷");
		}
	})
	
	$("div.mid div.left .title h2").mouseover(function(){
		var num=$(this).index();
		$("div.mid div.left .title h2").css("background","#D6D6D6");
		$(this).css("background","#54AED0");
		$("div.mid div.left ul.list").hide();
		$("div.mid div.left ul.list:eq("+num+")").show();
	})
})
function check_search(){
	if($("div.banner .in form input[type=\"text\"]").val()==""||$("div.banner .in form input[type=\"text\"]").val()=="«Î ‰»Îπÿº¸◊÷"){
		return false;
	}else{
		return true;
	}
}
$(function(){
		var a;
		$(".top ul li").mouseover(function(e){
			var index= $(this).index();
			$(this).css("background","#194876")
			if(index!=0&&index!=6&&index!=7){
				if(e.pageY<0||e.pageY>70){
					$("div.sub").slideUp();
				}else{
					fun(index);
				}
			}else{
				$("div.sub").slideUp();
			}
		})
		$("div.top").bind("mouseout",function(e){
			if(e.pageY<0){
				$("div.sub").slideUp();
			}
		})
		$("body div:not('.top')").mouseover(function(e){
			if(e.pageY<0||e.pageY>70){
				$("div.sub").slideUp();
			}
		});
	})
	function fun(n){
		$("div.sub").slideDown();
		$("div.sub ul li").hide();
		$("div.sub ul li:eq("+n+")").show();
	}
