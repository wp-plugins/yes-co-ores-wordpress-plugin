<?php
/**
* @desc YogRecentObjectsWidget
* @author Kees Brandenburg - Yes-co Nederland
*/
class YogRecentObjectsWidget extends WP_Widget
{
  const NAME              = 'Yes-co Recente objecten';
  const DESCRIPTION       = 'De laatst gepubliceerde objecten';
  const CLASSNAME         = 'yog-recent-list';
  const DEFAULT_LIMIT     = 5;
  const DEFAULT_IMG_SIZE  = 'thumbnail';
  
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
    
    parent::__construct(false, $name = self::NAME, $options);
    
    // Add needed css to header
    if (!file_exists(get_template_directory() . '/recent_objects.css'))
      wp_enqueue_style('yog-recent-object', YOG_PLUGIN_URL . '/inc/css/recent_objects.css');
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
    $title    = apply_filters('widget_title', $instance['title']);
    $limit    = empty($instance['limit']) ? self::DEFAULT_LIMIT : (int) $instance['limit'];
    $imgSize  = empty($instance['img_size']) ? self::DEFAULT_IMG_SIZE : $instance['img_size'];

    $posts = get_posts(array('numberposts' => $limit, 'post_type' => 'huis', 'orderby' => 'date'));
    
    echo $args['before_widget'];
    if (!empty($title))
      echo $args['before_title'] . $title . $args['after_title'];

    echo '<div class="recent-objects">';

    foreach ($posts as $post)
    {
      $images     = yog_retrieveImages($imgSize, 1, $post->ID);
      $title      = get_the_title($post->ID);
      $link       = get_permalink($post->ID);
      $prices     = yog_retrievePrices('recent-price-label', 'recent-price-specification', $post->ID);
      $openHouse  = yog_getOpenHouse('Open huis', $post->ID);
      $city       = yog_retrieveSpec('Plaats', $post->ID);

      echo '<div class="recent-object">';
        // Image
        if (!empty($images))
        {
          echo '<div class="recent-img">';
            echo '<a href="' . $link . '" rel="bookmark" title="' . $title . '">';
              echo '<img src="' . $images[0][0] . '" width="' . $images[0][1] . '" height="' . $images[0][2] . '" alt="' . $title . '" />';
            echo '</a>';
          echo '</div>';
        }

        echo '<h2><a href="' . $link . '" rel="bookmark" title="' . $title . '">' . $title . '</a></h2>';
        echo '<h3><a href="' . $link . '" rel="bookmark" title="' . $title . '">' . $city . '</a></h3>';

        // Prices
        if (!empty($prices))
        {
          echo '<div class="recent-prices">';
          foreach ($prices as $price)
          {
            echo '<div class="recent-price">' . $price . '</div>';
          }
          echo '</div>';
        }
        // Open house
        if (!empty($openHouse))
          echo '<div class="recent-open-house">' . $openHouse . '</div>';

      echo '</div>';
    }

    echo '</div>';

    echo $args['after_widget'];
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
    $instance             = $old_instance;
    $instance['title']    = empty($new_instance['title']) ? '' : $new_instance['title'];
    $instance['img_size'] = empty($new_instance['img_size']) ? self::DEFAULT_IMG_SIZE : $new_instance['img_size'];
    if (!empty($new_instance['limit']) && ctype_digit($new_instance['limit']))
      $instance['limit'] = (int) $new_instance['limit'];
    
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
    $title    = empty($instance['title']) ? '' : esc_attr($instance['title']);
    $limit    = empty($instance['limit']) ? self::DEFAULT_LIMIT : (int) $instance['limit'];
    $imgSize  = empty($instance['img_size']) ? self::DEFAULT_IMG_SIZE : $instance['img_size'];
    
    echo '<p>';
      echo '<label for="' . $this->get_field_id('title') . '">' . __('Titel') . ': </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
    echo '</p>';
    
    echo '<p>';
      echo '<label for="' . $this->get_field_id('img_size') . '">' . __('Formaat afbeeldingen') . ': </label>';
      echo '<select id="' . $this->get_field_id('img_size') . '" name="' . $this->get_field_name('img_size') . '">';
      foreach (get_intermediate_image_sizes() as $size)
      {
        echo '<option value="' . $size . '"' . (($size == $imgSize) ? ' selected="selected"' : '') . '>' . __(ucfirst($size)) . '</option>'; 
      }
      echo '</select>';
    echo '</p>';
    
		echo '<p>';
      echo '<label for="' . $this->get_field_id('limit') . '">' . __('Aantal te tonen objecten') . ': </label>';
      echo '<input id="' . $this->get_field_id('limit') . '" name="' . $this->get_field_name('limit') . '" type="text" value="' . $limit . '" size="3" maxlength="1" />';
    echo '</p>';
  }
}
?>
