var ScrollTimer;

jQuery(document).ready(function()
{
  /**
  * Image slider thumbnail click
  */
  jQuery('.yog-thumb').click(function(event)
  {
    event.preventDefault();
    
    var enableScrolling = (jQuery('#imgsliderholder').attr("class") == 'yog-scrolling-enabled');
    var elem            = event.currentTarget;

    if (enableScrolling)
    {
      var child   = jQuery(elem).children();
      var imageId = child.attr('id');
      var number  = imageId.replace('image', '');
      
      if ((firstImage = document.getElementById('image0')) && (image = document.getElementById(imageId)) && (holder = document.getElementById('imgslider')))
      {
        var pos = (image.offsetLeft - firstImage.offsetLeft) - (holder.offsetWidth  / 2) + (image.offsetWidth / 2);
        if (pos < 0)
          pos = 0;
        
        jQuery('#imgslider').animate({scrollLeft: pos}, 'slow');
      }
    }

    jQuery('#bigImage').attr('src', elem.href);
  });
  
  /**
  * Stop scrolling on mouse out
  */
  jQuery('#imgsliderholder.yog-scrolling-enabled .yog-scroll').mouseout(function()
  {
    clearInterval(ScrollTimer);
  });
  
  /**
  * Scroll left
  */
  jQuery('#imgsliderholder.yog-scrolling-enabled .yog-scroll.left').mouseover(function()
  {
    ScrollTimer = setInterval("document.getElementById('imgslider').scrollLeft -= 2", 15);
  });
  
  /**
  * Scroll right
  */
  jQuery('#imgsliderholder.yog-scrolling-enabled .yog-scroll.right').mouseover(function()
  {
    ScrollTimer = setInterval("document.getElementById('imgslider').scrollLeft += 2", 15);
  });
  
  // Adjust .left / .right height
  var yogSliderHeight = jQuery('#imgsliderholder.yog-scrolling-enabled').height();
  if (yogSliderHeight)
  {
    jQuery('#imgsliderholder .left').height(yogSliderHeight);
    jQuery('#imgsliderholder .right').height(yogSliderHeight);
  }
});
