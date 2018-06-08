var FormImageCrop = function () {

    var demo8 = function() {
        $('#demo8').Jcrop({
          aspectRatio: 0.5,
		  minSize: [100, 200],
		  //maxSize: [300, 200],
          onSelect: updateCoords
        });

        function updateCoords(c)
          {
            $('#crop_x').val(c.x);
            $('#crop_y').val(c.y);
            $('#crop_w').val(c.w);
            $('#crop_h').val(c.h);
            $('#x1').val(c.x);
            $('#y1').val(c.y);
            $('#x2').val(c.x2);
            $('#y2').val(c.y2);
            $('#w').val(c.w);
            $('#h').val(c.h);
          };

          $('#demo8_form').submit(function(){
            if (parseInt($('#crop_w').val())) return true;
            alert('Please select a crop region then press submit.');
            return false;
            });

    }

    var handleResponsive = function() {
      if ($(window).width() <= 1024 && $(window).width() >= 678) {
        $('.responsive-1024').each(function(){
          $(this).attr("data-class", $(this).attr("class"));
          $(this).attr("class", 'responsive-1024 col-md-12');
        }); 
      } else {
        $('.responsive-1024').each(function(){
          if ($(this).attr("data-class")) {
            $(this).attr("class", $(this).attr("data-class"));  
            $(this).removeAttr("data-class");
          }
        });
      }
    }

    return {
      
        init: function () {
            
            if (!jQuery().Jcrop) {;
                return;
            }

            Global.addResizeHandler(handleResponsive);
            handleResponsive();

            demo8();
        }

    };

}();