jQuery(document).ready(function()
{
  var searchFormWidgets = jQuery('.yog-search-form-widget');
  
  for (var i=0; i < searchFormWidgets.length; i++)
  {
    yogSearchFormUpdateNum(searchFormWidgets[i].id);
  }
  
  jQuery('.yog-search-form-widget .yog-object-form-elem').change(function(event)
  {
    yogSearchFormUpdateNum(this.form.id);
  }
  );
});

yogSearchFormUpdateNum = function(formId)
{
  // Detemine base url
  var baseUrl     = YogConfig.baseUrl;
  var formElem    = jQuery('#' + formId);
  
  formElem.addClass('loading');
  
  jQuery.getJSON(baseUrl, formElem.serialize() + '&yog-search-form-widget-ajax-search=true&form_id=' + formId,
    function(data, status)
    {
      if (status == 'success')
      {
        jQuery('#' + formId).removeClass('loading');
        
        var resultMsg = jQuery('#' + data.formId + ' .yog-object-search-result');
        var searchBtn = jQuery('#' + data.formId + ' .yog-object-search-button');
        
        resultMsg.html(data.msg);
        resultMsg.css('display', 'block');
        
        if (data.posts > 0)
          searchBtn.css('display', 'inline');
        else
          searchBtn.css('display', 'none');
      }
    }
  );
}
