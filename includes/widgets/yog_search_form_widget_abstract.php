<?php
/**
* @desc YogSearchFormWidgetAbstract
* @author Kees Brandenburg - Yes-co Nederland
*/
abstract class YogSearchFormWidgetAbstract extends WP_Widget
{
  private $fieldsSettings;

  /**
  * @desc Constructor
  *
  * @param mixed $id_base
  * @param string $name
  * @param array $widget_options
  * @param array $control_options
  */
  public function __construct($id_base = false, $name, $widget_options = array(), $control_options = array())
  {
    parent::__construct($id_base, $name, $widget_options, $control_options);

    $searchManager = YogObjectSearchManager::getInstance();
    $searchManager->extendSearch();
  }

  /**
  * @desc Retrieve the number of posts that would be found be the selected criteria
  *
  * @param void
  * @return void
  */
  public function retrieveNumberOfPosts()
  {
    if (!empty($_REQUEST['yog-search-form-widget-ajax-search']) && !empty($_REQUEST['object_type']) && $_REQUEST['object_type'] == $this->getPostType())
    {
      global $wp_query;

      $foundPosts = 0;
      if (!empty($wp_query->found_posts))
        $foundPosts = $wp_query->found_posts;
      else if (!empty($wp_query->post_count))
        $foundPosts = $wp_query->post_count;

      switch ($_REQUEST['object_type'])
      {
        case POST_TYPE_NBPR:
          $objectsText  = 'nieuwbouw projecten';
          $objectText   = 'nieuwbouw project';
          break;
        case POST_TYPE_NBTY:
          $objectsText  = 'nieuwbouw types';
          $objectText   = 'nieuwbouw type';
          break;
        case POST_TYPE_BBPR:
          $objectsText  = 'complexen';
          $objectText   = 'complex';
          break;
        default:
          $objectsText  = 'objecten';
          $objectText   = 'object';
          break;
      }

      switch ($foundPosts)
      {
        case 0:
          $msg = 'Er zijn geen ' . $objectsText . ' die voldoen aan deze criteria';
          break;
        case 1:
          $msg = 'Er is ' . $foundPosts . ' ' . $objectText . ' wat voldoet aan deze criteria';
          break;
        default:
          $msg = 'Er zijn ' . $foundPosts . ' ' . $objectsText . ' die voldoen aan deze criteria';
          break;
      }

      echo json_encode(array('posts' => $foundPosts, 'msg' => $msg, 'formId' => $_REQUEST['form_id']));
      exit;
    }
  }

  /**
  * @desc Retrieve fields setting
  *
  * @param void
  * @return YogFieldsSettingsAbstract
  */
  protected function retrieveFieldsSettings()
  {
    if (is_null($this->fieldsSettings))
      $this->fieldsSettings = YogFieldsSettingsAbstract::create($this->getPostType());

    return $this->fieldsSettings;
  }

  /**
  * @desc Render element for search form
  *
  * @param string $metaKey
  * @param string $content
  * @return string
  */
  protected function renderElement($metaKey, $content)
  {
    $html = '';

    if (!empty($content))
    {
      $fieldsSettings = $this->retrieveFieldsSettings();
      $options        = $fieldsSettings->getField($metaKey);
      $title          = empty($options['title']) ? str_replace($this->getPostType() . '_', '', $metaKey) : $options['title'];

      $html = '<div class="' . $this->getClassName() . '-element">';
        $html .= '<h5>' . $title . '</h5>';
        $html .= '<div class="' . $this->getClassName() . '-content">';
        $html .= $content;
        $html .= '</div>';
      $html .= '</div>';
    }

    return $html;
  }

  /**
  * @desc Render a slider element
  *
  * @param string $fieldName
  * @param int $min
  * @param int $max
  * @return string
  */
  protected function renderSlider($fieldName, $min, $max)
  {
    $html = '';

    if ($min >= 0 && $max > 0 && $min < $max)
    {
      if (!empty($_REQUEST[$fieldName . '_min']))
        $minValue = $_REQUEST[$fieldName . '_min'];
      else
        $minValue = $min;

      if (!empty($_REQUEST[$fieldName . '_max']))
        $maxValue = $_REQUEST[$fieldName . '_max'];
      else
        $maxValue = $max;

      $html .= '<div class="yog-form-slider-holder" id="' . $this->getPostType() . '-' . $fieldName . '-holder">';
        $html .= '<span class="yog-form-slider-settings" style="display:none;">{"min":' . $min . ', "max":' . $max . '}</span>';
        $html .= '<div class="yog-form-slider-labels"  style="display:none;">';
          $html .= '<span class="yog-form-slider-min-label yog-form-slider-label"></span> - <span class="yog-form-slider-max-label yog-form-slider-label"></span>';
        $html .= '</div>';
        $html .= '<input type="text" name="' . $fieldName . '_min" class="yog-form-slider-min yog-object-form-elem" value="' . $minValue . '" />';
        $html .= '<input type="text" name="' . $fieldName . '_max" class="yog-form-slider-max yog-object-form-elem" value="' . $maxValue . '" />';
        $html .= '<div class="yog-form-slider"></div>';
      $html .= '</div>';
    }

    return $html;
  }

  /**
  * @desc Render checkboxes for seach form
  *
  * @param string $fieldName
  * @param array $values
  * @return string
  */
  protected function renderCheckBoxes($fieldName, $values)
  {
    $html = '';

    if (count($values) > 1)
    {
      $checked = array();
      if (!empty($_REQUEST[$fieldName]))
      {
        if (is_array($_REQUEST[$fieldName]))
          $checked = $_REQUEST[$fieldName];
        else
          $checked = array($_REQUEST[$fieldName]);
      }

      foreach ($values as $key => $value)
      {
        $html .= '<div class="' . $this->getClassName() . '-row">';
          $html .= '<input type="checkbox" name="' . $fieldName . '[]" id="' . $fieldName . '' . $key . '" value="' . $value . '"' . (in_array($value, $checked) ? ' checked="checked"' : '') . ' class="yog-object-form-elem" /> <label for="' . $fieldName . '' . $key . '">' . $value . '</label>';
        $html .= '</div>';
      }
    }

    return $html;
  }

  /**
  * @desc Render a multiselect for search form
  *
  * @param string $fieldName
  * @param array $values
  * @return string
  */
  protected function renderMultiSelect($fieldName, $values)
  {
    $html = '';
    if (count($values) > 0)
    {
      $selected = array();
      if (!empty($_REQUEST[$fieldName]))
      {
        if (is_array($_REQUEST[$fieldName]))
          $selected = $_REQUEST[$fieldName];
        else
          $selected = array($_REQUEST[$fieldName]);
      }

      $html = '<div class="' . $this->getClassName() . '-row">';
        $html .= '<select name="' . $fieldName . '[]" id="' . $fieldName . '" multiple="mulitple" class="yog-object-form-elem ' . $this->getClassName() . '-multiselect">';

        foreach ($values as $key => $value)
        {
          $html .= '<option value="' . $value. '"' . (in_array($value, $selected) ? ' selected="selected"' : '') . '>' . $value . '</option>';
        }

        $html .= '</select>';
      $html .= '</div>';
    }

    return $html;
  }

  /**
  * @desc Get widget classname
  *
  * @param void
  * @return string
  */
  abstract protected function getClassName();

  /**
  * @desc Get widget post type
  *
  * @param void
  * @return string
  */
  abstract protected function getPostType();
}