jQuery(document).ready(function()
{
  var searchFormWidgets = jQuery('.yog-search-form-widget');

  for (var i=0; i < searchFormWidgets.length; i++)
  {
    yogSearchFormUpdateNum(searchFormWidgets[i].id);

    jQuery('.price-type-holder', searchFormWidgets[i]).change(function()
    {
      var koopChecked     = jQuery('#PrijsType0', this).prop('checked');
      var huurChecked     = jQuery('#PrijsType1', this).prop('checked');
      var koopPrijsHolder = jQuery('#Koopprijs-holder', searchFormWidgets[i]);
      var huurPrijsHolder = jQuery('#Huurprijs-holder', searchFormWidgets[i]);

      if (koopPrijsHolder.length === 1)
      {
        if (koopChecked || (!koopChecked && !huurChecked))
          koopPrijsHolder.css('display', 'block');
        else
          koopPrijsHolder.css('display', 'none');
      }

      if (huurPrijsHolder.length === 1)
      {
        if (huurChecked || (!koopChecked && !huurChecked))
          huurPrijsHolder.css('display', 'block');
        else
          huurPrijsHolder.css('display', 'none');
      }
    });

    jQuery('.price-holder', searchFormWidgets[i]).each(function()
    {
      var minElem = jQuery('.price-min', this);
      var maxElem = jQuery('.price-max', this);

      if (minElem.length === 1 && maxElem.length === 1)
      {
        minElem.change(function()
        {
          var minElemValue  = parseInt(this.value, 10);
          var maxElemValue  = parseInt(maxElem.val(), 10);
          var numOptions    = jQuery('option', maxElem).length;

          if (maxElemValue > 0 && maxElemValue <= minElemValue)
          {
            var index = maxElem[0].selectedIndex;
            if ((index + 1) < numOptions)
            {
              while (maxElemValue > 0 && maxElemValue <= minElemValue)
              {
                jQuery('option:nth-child(' + index + ')', maxElem).prop('selected', true);

                maxElemValue = parseInt(maxElem.val(), 10);
                index++;
              }
            }
          }
        });

        maxElem.change(function()
        {
          var minElemValue  = parseInt(minElem.val(), 10);
          var maxElemValue  = parseInt(this.value, 10);

          if (minElemValue > 0 && minElemValue >= maxElemValue)
          {
            var index = minElem[0].selectedIndex;

            while (minElemValue > 0 && minElemValue >= maxElemValue)
            {
              jQuery('option:nth-child(' + index + ')', minElem).prop('selected', true);

              minElemValue  = parseInt(minElem.val(), 10);
              index--;
            }
          }
        });
      }
    });
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