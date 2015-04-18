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
   * Toggle javascript dojo checkbox
   */
   $('#yog-toggle-javascript-dojo-dont-enqueue').click(function()
   {
     jQuery('#yog-on-javascript-dojo-dont-enqueue').addClass('loading');
     jQuery('#yog-on-javascript-dojo-dont-enqueue').addClass('loading-padding');
     jQuery('#yog-on-javascript-dojo-dont-enqueue-msg').addClass('hide');

     jQuery.post(ajaxurl, {'action': 'togglejavascriptdojo', 'cookie': encodeURIComponent(document.cookie)},
       function(msg)
       {
         jQuery('#yog-on-javascript-dojo-dont-enqueue').removeClass('loading');
         jQuery('#yog-on-javascript-dojo-dont-enqueue').removeClass('loading-padding');

         jQuery('#yog-on-javascript-dojo-dont-enqueue-msg').html(msg);
         jQuery('#yog-on-javascript-dojo-dont-enqueue-msg').removeClass('hide');
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

  /**
  * Make sure NBty / NBbn links are hidden for older browsers
  */
  var mainMenuItem  = jQuery('#toplevel_page_yog_posts_menu');
  if (mainMenuItem.length > 0)
  {
    jQuery('li a[href="edit.php?post_type=yog-nbty"]', mainMenuItem).parent().hide();
    jQuery('li a[href="edit.php?post_type=yog-nbbn"]', mainMenuItem).parent().hide();
    jQuery('li a[href="edit.php?post_type=yog-bbty"]', mainMenuItem).parent().hide();
  }
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

/**
* Activate NB admin menu
*/
var yogActivateNbAdminMenu = function ()
{
  var mainMenuItem  = jQuery('#toplevel_page_yog_posts_menu');
  var wpBodyContent = jQuery('#wpbody-content');

  if (mainMenuItem.length > 0)
  {
	var nbMenuLink    = jQuery('li a[href="edit.php?post_type=yog-nbpr"]', mainMenuItem);
    var nbMenuItem    = nbMenuLink.parent();

    if (nbMenuItem.length > 0 && nbMenuLink.length > 0)
    {
      nbMenuItem.addClass('current');
      nbMenuLink.addClass('current');
    }
  }

  if (wpBodyContent.length > 0)
  {
    var scenario = jQuery('#yog_scenario');
    if (scenario.length > 0)
      wpBodyContent.addClass('yog-' + scenario.attr('value'));
  }
}

/**
* Activate BBpr admin menu
*/
var yogActivateComplexAdminMenu = function ()
{
  var mainMenuItem  = jQuery('#toplevel_page_yog_posts_menu');
  var wpBodyContent = jQuery('#wpbody-content');

  if (mainMenuItem.length > 0)
  {
	var nbMenuLink    = jQuery('li a[href="edit.php?post_type=yog-bbpr"]', mainMenuItem);
    var nbMenuItem    = nbMenuLink.parent();

    if (nbMenuItem.length > 0 && nbMenuLink.length > 0)
    {
      nbMenuItem.addClass('current');
      nbMenuLink.addClass('current');
    }
  }

  if (wpBodyContent.length > 0)
  {
    var scenario = jQuery('#yog_scenario');
    if (scenario.length > 0)
      wpBodyContent.addClass('yog-' + scenario.attr('value'));
  }
}