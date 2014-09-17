<?php
/**
* @desc YogLinkedRelationsWidget
* @author Kees Brandenburg - Yes-co Nederland
*/
class YogLinkedRelationsWidget extends WP_Widget
{
  const NAME                    = 'Yes-co gelinkte relaties';
  const DESCRIPTION             = 'Toont de relaties die aan een object gekoppeld zijn.';
  const CLASSNAME               = 'yog-linked-relations';
  const DEFAULT_SPECS_BUSINESS  = 'Telefoonnummer,Faxnummer,Emailadres,Website,Hoofdadres';
  const DEFAULT_SPECS_PERSON    = 'Telefoonnummer,Faxnummer,Emailadres,Website';

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

    // Add needed javascript/css to header of website (not admin)
    if (!is_admin())
      wp_enqueue_style('yog-widgets-css', YOG_PLUGIN_URL . '/inc/css/widgets.css', array(), YOG_PLUGIN_VERSION);
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
    if (!(is_single() && yog_isObject()))
      return;

    // Retrieve linked relations
    $relations = yog_retrieveRelations();

    if (!empty($relations))
    {
      // Retrieve widget settings
      $title            = apply_filters('widget_title', $instance['title']);
      $specsBusiness    = explode(',', empty($instance['specs_business']) ? self::DEFAULT_SPECS_BUSINESS : $instance['specs_business']);
      $specsPerson      = explode(',', empty($instance['specs_person']) ? self::DEFAULT_SPECS_PERSON : $instance['specs_person']);
      $beforeWidget     = isset($args['before_widget']) ? $args['before_widget'] : '';
      $afterWidget      = isset($args['after_widget']) ? $args['after_widget'] : '';
      $beforeTitle      = isset($args['before_title']) ? $args['before_title'] : '';
      $afterTitle       = isset($args['after_title']) ? $args['after_title'] : '';

      // Specs to show
      $usedSpecs  = array('Business' => $specsBusiness, 'Person' => $specsPerson);

      // Show widget start
      echo $beforeWidget;
      echo $beforeTitle . $title . $afterTitle;

      // Show relations
      if (!empty($relations))
      {
        echo '<div class="yog-relations-holder">';
        foreach ($relations as $role => $relation)
        {
          // Retrieve specs
          $type   = yog_retrieveSpec('type', $relation->ID);
          $specs  = yog_retrieveSpecs($usedSpecs[$type], $relation->ID);

          echo '<div class="yog-relation yog-relation-' . strtolower($role) . ' yog-relation-' . strtolower($type) . '">';
            echo '<h5 class="yog-relation-role">' . $role . ':</h5>';
            echo '<div class="yog-relation-value yog-title">' . get_the_title($relation->ID) . '</div>';

            foreach ($specs as $label => $value)
            {
              $label = strtolower($label);

              switch ($label)
              {
                case 'emailadres':
                  $value = '<a href="mailto:' . $value . '">' . $value . '</a>';
                  break;
                case 'website':
                  $link = $value;
                  if (strpos($link, 'http://') === false && strpos($link, 'https://') === false)
                    $link = 'http://' . $link;

                  $value = '<a href="' . $link . '" rel="external">' . $value . '</a>';
                  break;
              }

              echo '<div class="yog-relation-value yog-' . $label . '" title="' . $label . '">' . $value . '</div>';
            }

            // Handle addresses
            $addressTypes = array('Hoofdadres', 'Postadres');
            foreach ($addressTypes as $addressType)
            {
              if (in_array($addressType, $usedSpecs[$type]))
              {
                $specs   = yog_retrieveSpecs(array($addressType . '_straat', $addressType .'_huisnummer', $addressType .'_stad', $addressType . '_postcode'), $relation->ID);
                if (!empty($specs))
                {
                  echo '<div class="yog-relation-value yog-relation-address yog-' . strtolower($addressType) . '" title="' . $addressType . '">';

                  if (!empty($specs['Straat']))
                    echo $specs['Straat'] . ' ';
                  if (!empty($specs['Huisnummer']))
                    echo $specs['Huisnummer'];

                  echo '<br />';

                  if (!empty($specs['Postcode']))
                    echo $specs['Postcode'] . '&nbsp;&nbsp;';
                  if (!empty($specs['Stad']))
                    echo $specs['Stad'];

                  echo '</div>';
                }

              }
            }

          echo '</div>';
        }

        echo '</div>';
      }

      // Show widget end
      echo $afterWidget;
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
    $instance                   = $old_instance;
    $instance['title']          = empty($new_instance['title']) ? '' : $new_instance['title'];
    $instance['specs_business'] = empty($new_instance['specs_business']) ? '' : implode(',', $new_instance['specs_business']);
    $instance['specs_person']   = empty($new_instance['specs_person']) ? '' : implode(',', $new_instance['specs_person']);

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
    $title              = empty($instance['title']) ? '' : esc_attr($instance['title']);
    $specsBusiness      = explode(',', empty($instance['specs_business']) ? self::DEFAULT_SPECS_BUSINESS : $instance['specs_business']);
    $specsPerson        = explode(',', empty($instance['specs_person']) ? self::DEFAULT_SPECS_PERSON : $instance['specs_person']);

    $availableSpecs     = array('Telefoonnummer', 'Faxnummer', 'Emailadres', 'Website', 'Hoofdadres', 'Postadres');

    echo '<p>';
      echo '<label for="' . $this->get_field_id('title') . '">' . __('Titel') . ': </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
    echo '</p>';

    $name = $this->get_field_name('specs_business');
    $id   = $this->get_field_id('specs_business');
    echo '<p>';
      echo '<label>' . __('Te tonen kenmerken bedrijf') . ': </label><br />';
      foreach ($availableSpecs as $availableSpec)
      {
        echo '<input type="checkbox" name="' . $name . '[]" value="' . $availableSpec . '" id="' . $id . $availableSpec . '"' . (in_array($availableSpec, $specsBusiness) ? ' checked="checked"' : '') . '  /><label for="' . $id . $availableSpec . '">' . $availableSpec . '</label><br />';
      }
    echo '</p>';

    $name = $this->get_field_name('specs_person');
    $id   = $this->get_field_id('specs_person') . 'Business';
    echo '<p>';
      echo '<label>' . __('Te tonen kenmerken persoon') . ': </label><br />';
      foreach ($availableSpecs as $availableSpec)
      {
        echo '<input type="checkbox" name="' . $name . '[]" value="' . $availableSpec . '" id="' . $id . $availableSpec . '"' . (in_array($availableSpec, $specsPerson) ? ' checked="checked"' : '') . '  /><label for="' . $id . $availableSpec . '">' . $availableSpec . '</label><br />';
      }
    echo '</p>';
  }
}