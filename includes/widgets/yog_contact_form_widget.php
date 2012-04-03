<?php
/**
* @desc YogContactFormWidget
* @author Kees Brandenburg - Yes-co Nederland
*/
class YogContactFormWidget extends WP_Widget
{
  const NAME                = 'Yes-co Contact formulier';
  const DESCRIPTION         = 'Contact formulier wat direct in je eigen Yes-co systeem binnen komt.';
  const CLASSNAME           = 'yog-contact-form';
  const FORM_ACTION         = 'http://api.yes-co.com/1.0/response';
  const JS_LOCATION         = 'http://api.yes-co.com/1.0/embed/js/response-forms.js';
  const DEFAULT_THANKS_MSG  = 'Het formulier is verzonden, we nemen zo spoedig mogelijk contact met u op.';
  const WIDGET_ID_PREFIX    = 'yogcontactformwidget-';

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
    $yescoKey       = empty($instance['yesco_key']) ? '' : $instance['yesco_key'];
    $actions        = empty($instance['actions']) ? '' : $instance['actions'];
    $thanksMsg      = empty($instance['thanks_msg']) ? self::DEFAULT_THANKS_MSG : $instance['thanks_msg'];
    $showFirstname  = empty($instance['show_firstname']) ? false : true;
    $showLastname   = empty($instance['show_lastname']) ? false : true;
    $showEmail      = empty($instance['show_email']) ? false : true;
    $showPhone      = empty($instance['show_phone']) ? false : true;
    $showAddress    = empty($instance['show_address']) ? false : true;
    $showRemarks    = empty($instance['show_remarks']) ? false : true;
    $showNewsletter = empty($instance['show_newsletter']) ? false : true;
    $widgetId       = empty($args['widget_id']) ? 0 : str_replace(self::WIDGET_ID_PREFIX, '', $args['widget_id']);

    if (!empty($_GET['send']) && $_GET['send'] == $widgetId)
    {
      // Show thank you page
      echo $args['before_widget'];
      if (!empty($title))
        echo $args['before_title'] . $title . $args['after_title'];

      echo '<p>' . $thanksMsg . '</p>';

      echo $args['after_widget'];
    }
    else if (!empty($yescoKey))
    {
      // Show form
      if (!empty($_SERVER['HTTP_HOST']))
      {
        $thankYouPage  = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $thankYouPage .= ((strpos($thankYouPage, '?') === false) ? '?' : '&amp;') . 'send=' . $widgetId;
      }

      echo $args['before_widget'];
      if (!empty($title))
        echo $args['before_title'] . $title . $args['after_title'];

      echo '<form method="post" action="#" onsubmit="this.action = \'' . self::FORM_ACTION . '\';">';
        echo '<input type="hidden" name="yesco_key" value="' . $yescoKey . '" />';

        echo '<input type="hidden" name="title" value="' . $title . '" />';
        echo '<input type="hidden" name="source" value="' . get_bloginfo('name') . '" />';

        if (!empty($thankYouPage))
          echo '<input type="hidden" name="thank_you_page" value="' . $thankYouPage . '" />';

        if (is_single() && have_posts() && yog_isObject())
        {
          the_post();

          $projectApiKey = yog_retrieveSpec('ApiKey');
          if (!empty($projectApiKey))
            echo '<input type="hidden" name="project_id"  value="' . $projectApiKey. '" />';

          rewind_posts();
        }

        // First name
        if ($showFirstname)
        {
          echo '<p>';
            echo '<label for="person[firstname]">Voornaam:</label>';
            echo '<input type="text" name="person[firstname]" id="person[firstname]" value="" />';
          echo '</p>';
        }
        // Achternaam
        if ($showLastname)
        {
          echo '<p>';
            echo '<label for="person[lastname]">Achternaam:</label>';
            echo '<input type="text" name="person[lastname]" id="person[lastname]" value="" class="required" />';
          echo '</p>';
        }
        // E-mail
        if ($showEmail)
        {
          echo '<p>';
            echo '<label for="person[email]">E-mail:</label>';
            echo '<input type="text" name="person[email]" id="person[email]" value="" class="required" />';
          echo '</p>';
        }
        // Telephone
        if ($showPhone)
        {
          echo '<p>';
            echo '<label for="person[phone]">Telefoon:</label>';
            echo '<input type="text" name="person[phone]" id="person[phone]" value="" />';
          echo '</p>';
        }
        // Address
        if ($showAddress)
        {
          echo '<p>';
            echo '<label for="person[street]">Straat:</label>';
            echo '<input type="text" name="person[street]" id="person[street]" value="" />';
          echo '</p>';
          echo '<p>';
            echo '<label for="personHousenumber" class="label-housenumber">Huisnummer</label><label for="personZipcode" class="label-zipcode"> / Postcode:</label><br />';
            echo '<input type="text" name="person[housenumber]" id="personHousenumber" value="" /><input type="text" name="person[zipcode]" id="personZipcode" value="" />';
          echo '</p>';
          echo '<p>';
            echo '<label for="person[city]">Plaats:</label>';
            echo '<input type="text" name="person[city]" id="person[city]" value="" />';
          echo '</p>';
        }
        // Actions
        if (!empty($actions))
        {
          $actions = explode("\n", $actions);
          echo '<p>';
            echo '<label>Acties:</label><br />';
            foreach ($actions as $key => $action)
            {
              echo '<input type="checkbox" name="actions[]" id="actions_' . $key . '" value="' . $action . '" />';
              echo '<label for="actions_' . $key . '">' . $action . '</label><br />';
            }
          echo '</p>';
        }
        // Opmerkingen
        if ($showRemarks)
        {
          echo '<p>';
            echo '<label for="comments">Opmerkingen:</label>';
            echo '<textarea name="comments" id="comments"></textarea>';
          echo '</p>';
        }
        // Newsletter
        if ($showNewsletter)
        {
          echo '<p><input type="checkbox" name="person_tags[]" id="person_tag_nieuwsbrief" value="nieuwsbrief" /> <label for="person_tag_nieuwsbrief">Schrijf mij in voor uw nieuwbrief</label></p>';
        }

        echo '<p><label>&nbsp;</label><input type="submit" value="Verzenden" /></p>';
      echo '</form>';
      echo '<script type="text/javascript" src="' . self::JS_LOCATION . '"></script>';

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
    $instance['yesco_key']        = empty($new_instance['yesco_key']) ? '' : $new_instance['yesco_key'];
    $instance['actions']          = empty($new_instance['actions']) ? '' : $new_instance['actions'];
    $instance['thanks_msg']       = empty($new_instance['thanks_msg']) ? '' : $new_instance['thanks_msg'];
    $instance['show_firstname']   = empty($new_instance['show_firstname']) ? 0 : 1;
    $instance['show_lastname']    = empty($new_instance['show_lastname']) ? 0 : 1;
    $instance['show_email']       = empty($new_instance['show_email']) ? 0 : 1;
    $instance['show_phone']       = empty($new_instance['show_phone']) ? 0 : 1;
    $instance['show_address']     = empty($new_instance['show_address']) ? 0 : 1;
    $instance['show_remarks']     = empty($new_instance['show_remarks']) ? 0 : 1;
    $instance['show_newsletter']  = empty($new_instance['show_newsletter']) ? 0 : 1;
    
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
    $yescoKey       = empty($instance['yesco_key']) ? '' : $instance['yesco_key'];
    $actions        = empty($instance['actions']) ? '' : esc_attr($instance['actions']);
    $thanksMsg      = empty($instance['thanks_msg']) ? self::DEFAULT_THANKS_MSG : esc_attr($instance['thanks_msg']);
    $showFirstname  = empty($instance['show_firstname']) ? false : true;
    
    $showFields = array('show_firstname'  => 'Voornaam',
                        'show_lastname'   => 'Achternaam',
                        'show_email'      => 'E-mail',
                        'show_phone'      => 'Telefoon nummer',
                        'show_address'    => 'Adres',
                        'show_remarks'    => 'Opmerkingen',
                        'show_newsletter' => 'Inschrijven nieuwsbrief');
    
    echo '<p>';
      echo '<label for="' . $this->get_field_id('title') . '">' . __('Titel') . ': </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
    echo '</p>';
    
    echo '<p>';
      echo '<label for="' . $this->get_field_id('yesco_key') . '">' . __('Yes-co key') . ': </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('yesco_key') . '" name="' . $this->get_field_name('yesco_key') . '" type="text" value="' . $yescoKey . '" />';
      echo '<small>' . __('Te achterhalen in Yes-co App Market') . '</small>';
    echo '</p>';
    
    echo '<strong>Tonen</strong>';
    echo '<table>';
    foreach ($showFields as $field => $label)
    {
      $show = empty($instance[$field]) ? false : true;
		  echo '<tr>';
        echo '<td><label for="' . $this->get_field_id($field) . '">' . __($label) . '</label>: </td>';
        echo '<td><input id="' . $this->get_field_id($field) . '" name="' . $this->get_field_name($field) . '" type="checkbox" value="1" ' . ($show === true ? 'checked="checked" ' : '') . '/></td>';
      echo '</tr>';
    }
    echo '</table><br />';
    
    echo '<p>';
      echo '<label for="' . $this->get_field_id('actions') . '"><strong>' . __('Acties') . '</strong></label>';
      echo '<textarea name="' . $this->get_field_name('actions') . '" id="' . $this->get_field_id('actions') . '" class="widefat">' . $actions . '</textarea>';
      echo '<small>' . __('1 actie per regel') . '</small>';
    echo '</p>';
    
    echo '<p>';
      echo '<label for="' . $this->get_field_id('thanks_msg') . '"><strong>' . __('Formulier verstuurd boodschap') . '</strong></label>';
      echo '<textarea name="' . $this->get_field_name('thanks_msg') . '" id="' . $this->get_field_id('thanks_msg') . '" class="widefat">' . $thanksMsg . '</textarea>';
    echo '</p>';
    
  }
}

?>
