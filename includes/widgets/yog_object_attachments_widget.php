<?php
/**
* @desc YogObjectAttachmentsWidget
* @author Stefan van Zanden - Yes-co Nederland
*/
class YogObjectAttachmentsWidget extends WP_Widget
{
  const NAME                = 'Yes-co Object Koppelingen';
  const DESCRIPTION         = 'Toont o.a. de website en brochure links die meegegeven zijn bij een object.';
  const CLASSNAME           = 'yog-contact-form';
  
  /**
  * @desc Constructor
  * 
  * @param void
  * @return YogObjectAttachmentsWidget
  */
  public function __construct()
  {
    $options = array( 'classsname'  => self::CLASSNAME,
                      'description' => self::DESCRIPTION);

    parent::__construct(false, $name = self::NAME, $options);
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
    $title          = apply_filters('widget_title', $instance['title']);

    if (is_single() && get_post_type() == 'huis' && have_posts())
    {
      the_post();

      rewind_posts();
    }
    else
    {
      return;
    }

    $links      = yog_retrieveLinks();
    $documents  = yog_retrieveDocuments();

    if (!empty($links) || !empty($documents))
    {
      //echo $args['before_widget'];
      echo '<div class="borderbox widget widget_yogobjectattachments colored">';

      if (!empty($title))
        echo $args['before_title'] . $title . $args['after_title'];

      echo '<ul>';

      // Links
      foreach ($links as $link)
      {
        switch ($link->type)
        {
          case 'previsite tour':
            $url    = $link->url . ((strpos($link->url, '?') !== false) ? '&amp;' : '?') . 'KeepThis=true&amp;TB_iframe=true&amp;height=470&amp;width=700';
            $class  = 'link-' . $link->type . ' thickbox';
            break;
          default:
            $url = $link->url;
            $class  = 'link-' . $link->type . '';
            break;
        }

        echo '<li><div class="link"><a href="' . $url . '" class="' . $class . '">' . $link->title . '</a></div></li>';
      }

      // Documents
      foreach ($documents as $document)
      {
        echo '<li><div class="link"><a href="' . $document->url . '" class="link-default link-' . $document->documentType . '" target="blank">' . $document->title . '</a></div></li>';
      }
      echo '</ul>';

      echo $args['after_widget'];
    }


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
    $instance                     = $old_instance;
    $instance['title']            = empty($new_instance['title']) ? '' : $new_instance['title'];

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
    $title          = empty($instance['title']) ? '' : esc_attr($instance['title']);

    echo '<p>';
      echo '<label for="' . $this->get_field_id('title') . '">' . __('Titel') . ': </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
    echo '</p>';
  }
}
?>
