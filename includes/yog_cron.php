<?php
add_action('yog_cron_open_houses', 'yog_cronUpdateOpenHouses');

/**
* @desc Update open house categories for open house dates in the past
*/
function yog_cronUpdateOpenHouses()
{
  // Retrieve all objects with open house category
  $objecten = get_posts(array('post_type'   => POST_TYPE_WONEN,
                              'category'    => 'open-huis',
                              'numberposts' => -1));

  foreach ($objecten as $object)
  {
    $openHouseStart = get_post_meta($object->ID,'huis_OpenHuisTot', true);
    $openHouseEnd   = get_post_meta($object->ID,'huis_OpenHuisTot',true);

    // Update categories if open house date is old
    if ((empty($openHouseStart) || strtotime($openHouseStart) < time()) && (empty($openHouseEnd) || strtotime($openHouseEnd) < time()))
    {
      $categories     = wp_get_object_terms( $object->ID, 'category' );
      $categorySlugs  = array();

      foreach ($categories as $category)
      {
        if ($category->slug != 'open-huis')
          $categorySlugs[] = $category->slug;
      }

      wp_set_object_terms( $object->ID, $categorySlugs, 'category', false);
    }
  }
}