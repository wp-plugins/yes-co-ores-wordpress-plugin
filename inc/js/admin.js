jQuery(document).ready( function($)
{
  /**
  * Toggle home checkbox
  */
  $('#yog-toggle-home').click(function()
  {
    jQuery('#yog-objects-on-home').addClass('loading');
    jQuery('#yog-objects-on-home').addClass('loading-padding');
    jQuery('#yog-objects-on-home-msg').addClass('hide');
    
	  jQuery.post(ajaxurl, {'action': 'togglehome', 'cookie': encodeURIComponent(document.cookie)},
		  function(msg)
		  {
        jQuery('#yog-objects-on-home').removeClass('loading');
        jQuery('#yog-objects-on-home').removeClass('loading-padding');
        
        jQuery('#yog-objects-on-home-msg').html(msg);
        jQuery('#yog-objects-on-home-msg').removeClass('hide');
		  });
  });
  
  /**
  * Toggle archive checkbox
  */
  $('#yog-toggle-archive').click(function()
  {
    jQuery('#yog-objects-on-archive').addClass('loading');
    jQuery('#yog-objects-on-archive').addClass('loading-padding');
    jQuery('#yog-objects-on-archive-msg').addClass('hide');
    
	  jQuery.post(ajaxurl, {'action': 'togglearchive', 'cookie': encodeURIComponent(document.cookie)},
		  function(msg)
		  {
        jQuery('#yog-objects-on-archive').removeClass('loading');
        jQuery('#yog-objects-on-archive').removeClass('loading-padding');
        
        jQuery('#yog-objects-on-archive-msg').html(msg);
        jQuery('#yog-objects-on-archive-msg').removeClass('hide');
		  });
  });
  
  /**
  * Add system link
  */
  $('#yog-add-system-link').click(function()
  {
	  jQuery('#yog-add-system-link').hide();
	  jQuery('#yog-add-system-link-holder').addClass('loading');
    jQuery('#yog-add-system-link-holder').addClass('loading-padding');
    
    var secret  = jQuery('#yog-new-secret').val();

	  jQuery.post(ajaxurl, {'action': 'addkoppeling', 'activatiecode':secret, 'cookie': encodeURIComponent(document.cookie)},
		  function(html)
		  {
			  jQuery('#yog-system-links').append(html);
        jQuery('#yog-add-system-link-holder').removeClass('loading');
        jQuery('#yog-add-system-link-holder').removeClass('loading-padding');
        jQuery('#yog-new-secret').val('');
			  jQuery('#yog-add-system-link').show();
		  });
  });
  
});

/**
* Remove system link
*/
function yogRemoveSystemLink(secret)
{
  jQuery('#yog-system-link-' + secret + '-remove span').hide()
	jQuery('#yog-system-link-' + secret + '-remove').addClass('loading');
  jQuery('#yog-system-link-' + secret + '-remove').addClass('loading-padding');
  
	jQuery.post(ajaxurl, {action:"removekoppeling", 'activatiecode':secret, 'cookie': encodeURIComponent(document.cookie)},
		function(secret)
		{
      jQuery('#yog-system-link-' + secret).fadeOut();
      jQuery('#yog-system-link-' + secret).remove();
		});
}