<?php
require_once(YOG_PLUGIN_DIR . '/includes/config/config.php');
require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_fields_settings.php');
require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_object_search_manager.php');
require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_search_form_widget_abstract.php');

/**
* @desc YogSearchFormNBprWidget
* @author Kees Brandenburg - Yes-co Nederland
*/
class YogSearchFormNBprWidget extends YogSearchFormWidgetAbstract
{
  const NAME        = 'Yes-co Nieuwbouw Projecten zoeken';
  const DESCRIPTION = 'Zoek formulier voor nieuwbouw projecten';
  const CLASSNAME   = 'yog-object-search';
  
  /**
  * @desc Constructor
  * 
  * @param void
  * @return YogRecentObjectsWidget
  */
  public function __construct()
  {
    $options = array( 'classsname'  => self::CLASSNAME,
                      'description' => self::DESCRIPTION);
    
    parent::__construct(false, self::NAME, $options);
    
    if (!is_admin())
    {
      // Add needed javascript/css to header of website (not admin)
      wp_enqueue_script('jquery-ui-widget', YOG_PLUGIN_URL .'/inc/js/jquery.ui.widget.js', array('jquery', 'jquery-ui-core'));
      wp_enqueue_script('jquery-ui-mouse', YOG_PLUGIN_URL .'/inc/js/jquery.ui.mouse.js', array('jquery', 'jquery-ui-core'));
      wp_enqueue_script('jquery-ui-slider', YOG_PLUGIN_URL .'/inc/js/jquery.ui.slider.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse'));
    
      wp_enqueue_script('yog-form-slider', YOG_PLUGIN_URL .'/inc/js/form-slider.js', array('jquery-ui-slider'), YOG_PLUGIN_VERSION);
      wp_enqueue_script('yog-search-form-widget', YOG_PLUGIN_URL .'/inc/js/yog_search_form_widget.js', array('jquery'), YOG_PLUGIN_VERSION);
      wp_enqueue_style('yog-widgets-css', YOG_PLUGIN_URL . '/inc/css/widgets.css', array(), YOG_PLUGIN_VERSION);
      
      // Do ajax search when needed
      add_action('get_header', array($this, 'retrieveNumberOfPosts'));
    }
  }
  
  /**
  * @desc Get the widget classname
  * 
  * @param void
  * @return string
  */
  protected function getClassName()
  {
    return self::CLASSNAME; 
  }
  
  /**
  * @desc Get the post type
  * 
  * @param void
  * @return string
  */
  protected function getPostType()
  {
    return POST_TYPE_NBPR;
  }
  
  /**
  * @desc Display widget
  * 
  * @param array $args
  * @param array $instance
  * @return void
  */
  public function widget($args, $instance)
  {
    // Retrieve managers
    $searchManager    = YogObjectSearchManager::getInstance();
    
    // Retrieve arguments
    $beforeWidget     = isset($args['before_widget']) ? $args['before_widget'] : '';
    $afterWidget      = isset($args['after_widget']) ? $args['after_widget'] : '';
    $beforeTitle      = isset($args['before_title']) ? $args['before_title'] : '';
    $afterTitle       = isset($args['after_title']) ? $args['after_title'] : '';
    
    // Retrieve settings
    $title            = empty($instance['title']) ? '' : esc_attr($instance['title']);
    $useCurrentCat    = empty($instance['use_cur_cat']) ? false : true;
    $showPrice        = empty($instance['show_price']) ? false : true;
    $showCity         = empty($instance['show_city']) ? false : true;
    $showLivingSpace  = empty($instance['show_living_space']) ? false : true;
    $showVolume       = empty($instance['show_volume']) ? false : true;
    $params           = array();
    
    // Use current category name in widget title?
    if (is_category())
    {
      $title = str_replace('%category%', single_cat_title('', false), $title);
    }

    // Output widget
    echo $beforeWidget;
    echo $beforeTitle . $title . $afterTitle;
    echo '<form method="get" class="yog-search-form-widget ' . self::CLASSNAME . '" id="yog-nbpr-search-form-widget" action="' . get_bloginfo('url') . '/">';
    echo '<div style="display:none;">';
      echo '<input type="hidden" name="s" value=" " />';
      echo '<input type="hidden" name="object_type" value="' . $this->getPostType() . '" />';
    
    // Only use object specs of current category?
    if (is_category() && $useCurrentCat)
    {
      echo '<input type="hidden" name="cat" value="' . get_query_var('cat') . '" />';
      $params['cat'] = get_query_var('cat');
    }
    
    echo '</div>';
    
    // Prijs
    if ($showPrice === true)
      echo $this->renderElement('Prijs', $this->renderSlider('Prijs', $searchManager->retrieveMinMetaValue(array('yog-nbpr_KoopAanneemSomMin', 'yog-nbpr_HuurPrijsMin'), $params), $searchManager->retrieveMaxMetaValue(array('yog-nbpr_KoopAanneemSomMax', 'yog-nbpr_HuurPrijsMax'), $params)));
    
    // Plaats
    if ($showCity === true)
      echo $this->renderElement('yog-nbpr_Plaats', $this->renderMultiSelect('Plaats', $searchManager->retrieveMetaList('yog-nbpr_Plaats', $params)));
      
    // Oppervlakte
    if ($showLivingSpace === true)
      echo $this->renderElement('Woon oppervlakte', $this->renderSlider('WoonOppervlakte', $searchManager->retrieveMinMetaValue('yog-nbpr_WoonOppervlakteMin', $params), $searchManager->retrieveMaxMetaValue('yog-nbpr_WoonOppervlakteMax', $params)));
    
    // Inhoud
    if ($showVolume === true)
      echo $this->renderElement('Inhoud', $this->renderSlider('Inhoud', $searchManager->retrieveMinMetaValue('yog-nbpr_InhoudMin', $params), $searchManager->retrieveMaxMetaValue('yog-nbpr_InhoudMax', $params)));

    echo '<p class="' . self::CLASSNAME . '-result">Er zijn <span class="object-search-result-num"></span> nieuwbouw projecten die voldoen aan deze criteria</p>';
    echo '<div><input type="submit" class="' . self::CLASSNAME . '-button" value=" Tonen " /></div>';
    echo '</form>';
    echo $afterWidget;
  }
  
  /**
  * @desc Update widget settings
  * 
  * @param array $new_instance
  * @param array $old_instance
  * @return array
  */
  public function update($new_instance, $old_instance)
  {
    $instance                       = $old_instance;
    $instance['title']              = empty($new_instance['title']) ? '' : $new_instance['title'];
    $instance['use_cur_cat']        = empty($new_instance['use_cur_cat']) ? 0 : 1;
    $instance['show_price']         = empty($new_instance['show_price']) ? 0 : 1;
    $instance['show_city']          = empty($new_instance['show_city']) ? 0 : 1;
    $instance['show_living_space']  = empty($new_instance['show_living_space']) ? 0 : 1;
    $instance['show_volume']        = empty($new_instance['show_volume']) ? 0 : 1;
    
    return $instance;
  }
  
  /**
  * @desc Display widget form
  * 
  * @param array $instance
  * @return void
  */
  public function form($instance)
  {
    $title            = empty($instance['title']) ? '' : esc_attr($instance['title']);
    $useCurrentCat    = empty($instance['use_cur_cat']) ? false : true;
    $showPrice        = empty($instance['show_price']) ? false : true;
    $showCity         = empty($instance['show_city']) ? false : true;
    $showLivingSpace  = empty($instance['show_living_space']) ? false : true;
    $showVolume       = empty($instance['show_volume']) ? false : true;
    
    // Title
    echo '<p>';
      echo '<label for="' . $this->get_field_id('title') . '">' . _e('Titel') . ': </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
    echo '</p>';
    echo '<table>';
    // Use current category
		echo '<tr>';
      echo '<td><label for="' . $this->get_field_id('use_cur_cat') . '">' . __('Alleen objecten uit huidige category gebruiken') . '</label>: </td>';
      echo '<td><input id="' . $this->get_field_id('use_cur_cat') . '" name="' . $this->get_field_name('use_cur_cat') . '" type="checkbox" value="1" ' . ($useCurrentCat === true ? 'checked="checked" ' : '') . '/></td>';
    echo '</tr>';
    // Seperator
    echo '<tr><td colspan="2">&nbsp;</td></tr>';
    // Show price
		echo '<tr>';
      echo '<td><label for="' . $this->get_field_id('show_price') . '">' . __('Prijs tonen') . '</label>: </td>';
      echo '<td><input id="' . $this->get_field_id('show_price') . '" name="' . $this->get_field_name('show_price') . '" type="checkbox" value="1" ' . ($showPrice === true ? 'checked="checked" ' : '') . '/></td>';
    echo '</tr>';
    // Show city
		echo '<tr>';
      echo '<td><label for="' . $this->get_field_id('show_city') . '">' . __('Plaats tonen') . '</label>: </td>';
      echo '<td><input id="' . $this->get_field_id('show_city') . '" name="' . $this->get_field_name('show_city') . '" type="checkbox" value="1" ' . ($showCity === true ? 'checked="checked" ' : '') . '/></td>';
    echo '</tr>';
    // Show living space
		echo '<tr>';
      echo '<td><label for="' . $this->get_field_id('show_living_space') . '">' . __('Woon oppervlakte tonen') . '</label>: </td>';
      echo '<td><input id="' . $this->get_field_id('show_living_space') . '" name="' . $this->get_field_name('show_living_space') . '" type="checkbox" value="1" ' . ($showLivingSpace === true ? 'checked="checked" ' : '') . '/></td>';
    echo '</tr>';
    // Show Volume
		echo '<tr>';
      echo '<td><label for="' . $this->get_field_id('show_volume') . '">' . __('Inhoud tonen') . '</label>: </td>';
      echo '<td><input id="' . $this->get_field_id('show_volume') . '" name="' . $this->get_field_name('show_volume') . '" type="checkbox" value="1" ' . ($showVolume === true ? 'checked="checked" ' : '') . '/></td>';
    echo '</tr>';
    echo '</table>';
  }
}
?>