jQuery(document).ready( function($)
{
  var mainMenuItem  = jQuery('#toplevel_page_yog_posts_menu');
  if (mainMenuItem.length > 0)
  {
    jQuery('li a[href="edit.php?post_type=yog-bbpr"]', mainMenuItem).parent().hide();
  }
});