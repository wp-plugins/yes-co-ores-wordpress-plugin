<?php
/**
* @desc YogSearchFormWidgetAbstract
* @author Kees Brandenburg - Yes-co Nederland
*/
abstract class YogSearchFormWidgetAbstract extends WP_Widget
{
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
}
?>
