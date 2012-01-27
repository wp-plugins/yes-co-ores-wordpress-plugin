<?php
  /**
  * @desc YogWpAdminObjectUiWonen
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogWpAdminObjectUiWonen extends YogWpAdminObjectUiAbstract
  {
    /**
    * @desc Get the post type
    * 
    * @param void
    * @return string
    */
    public function getPostType()
    {
      return POST_TYPE_WONEN; 
    }
    
    /**
    * @desc Get base name
    * 
    * @param void
    * @return string
    */
    public function getBaseName()
    {
      return plugin_basename(__FILE__);
    }
    
    /**
    * @desc Determine columns used in overview
    * 
    * @param array $columns
    * @return array
    */
    public function determineColumns($columns)
    {
	    return array(
	      'cb'            => '<input type="checkbox" />',
	      'title'         => 'Object',
	      'description'   => 'Omschrijving',
	      'address'       => 'Adres',
        'dlm'           => 'Laatste wijziging',
	      'scenario'      => 'Scenario'
	    );
    }
    
    /**
    * @desc Determine content of a single column in overview
    * 
    * @param string $columnId
    * @return void
    */
    public function generateColumnContent($columnId)
    {
      switch ($columnId)
      {
        case 'description':
          $content = get_the_excerpt();
          if (strlen($content) > 100)
            $content = htmlentities(substr($content, 0, 100)) . '...';
            
          echo $content;
          break;
        case 'address':
          echo yog_getAddress();
          break;
        case 'dlm':
          echo get_the_modified_date() . ' ' . get_the_modified_time();
          break;
        case 'scenario':
          echo yog_retrieveSpec('scenario');
          break;
      }
    }
    
    /**
    * @desc Add containers to project screen
    * 
    * @param void
    * @return void
    */
    public function addMetaBoxes()
    {
	    add_meta_box('yog-standard-meta', 'Basis gegevens',       array($this, 'renderBasicMetaBox'),         POST_TYPE_WONEN, 'normal', 'low');
	    add_meta_box('yog-price-meta',    'Prijs',                array($this, 'renderPriceMetaBox'),         POST_TYPE_WONEN, 'normal', 'low');
	    add_meta_box('yog-extended-meta', 'Gegevens object',      array($this, 'renderObjectDetailsMetaBox'), POST_TYPE_WONEN, 'normal', 'low');
	    add_meta_box('yog-openhuis',      'Open huis',            array($this, 'renderOpenHouseMetaBox'),     POST_TYPE_WONEN, 'normal', 'low');
	    add_meta_box('yog-movies',        'Video',                array($this, 'renderMoviesMetaBox'),        POST_TYPE_WONEN, 'normal', 'low');
	    add_meta_box('yog-documents',     'Documenten',           array($this, 'renderDocumentsMetaBox'),     POST_TYPE_WONEN, 'normal', 'low');
	    add_meta_box('yog-links',         'Externe koppelingen',  array($this, 'renderLinksMetaBox'),         POST_TYPE_WONEN, 'normal', 'low');
      
      add_meta_box('yog-meta-sync',     'Synchronisatie',       array($this, 'renderSyncMetaBox') ,         POST_TYPE_WONEN, 'side', 'low'); 
      add_meta_box('yog-location',      'Locatie',              array($this, 'renderMapsMetaBox'),          POST_TYPE_WONEN, 'side', 'low');
      add_meta_box('yog-relations',     'Relaties',             array($this, 'renderRelationsMetaBox'),     POST_TYPE_WONEN, 'side', 'low');
      add_meta_box('yog-images',        'Afbeeldingen',         array($this, 'renderImagesMetaBox'),        POST_TYPE_WONEN, 'side', 'low');
    }
    
    /**
    * @desc Render basic meta box
    * 
    * @param object $post
    * @return void
    */
    public function renderBasicMetaBox($post)
    {
	    echo '<table class="form-table">';
	    echo $this->retrieveInputs($post->ID, array('Naam', 'Straat', 'Huisnummer', 'Postcode', 'Wijk', 'Buurt', 'Plaats', 'Gemeente', 'Provincie', 'Land', 'Status'));
	    echo '</table>';
    }
    
    /**
    * @desc Render price meta box
    * 
    * @param object $post
    * @return void
    */
    public function renderPriceMetaBox($post)
    {
	    echo '<table class="form-table">';

	    // Koop
	    echo '<tr>';
	    echo '<th colspan="2"><b>Koop</b></th>';
	    echo '</tr>';
	    echo $this->retrieveInputs($post->ID, array('KoopPrijsSoort', 'KoopPrijs', 'KoopPrijsConditie'));

	    // Huur
	    echo '<tr>';
	    echo '<th colspan="2"><b>Huur</b></th>';
	    echo '</tr>';
	    echo $this->retrieveInputs($post->ID, array('HuurPrijs', 'HuurPrijsConditie'));

	    echo '</table>';
    }
    
    /**
    * @desc Render object details meta box
    * 
    * @param object $post
    * @return void
    */
    public function renderObjectDetailsMetaBox($post)
    {
	    echo '<table class="form-table">';
	    echo $this->retrieveInputs($post->ID, array('Type', 'SoortWoning', 'TypeWoning', 'KenmerkWoning', 'Bouwjaar', 'Aantalkamers', 'Oppervlakte', 'OppervlaktePerceel', 'Inhoud', 'Ligging', 'GarageType', 'TuinType', 'BergingType', 'PraktijkruimteType', 'EnergielabelKlasse'));
	    echo '</table>';
    }
    
    /**
    * @desc Render open house meta box
    * 
    * @param object $post
    * @return void
    */
    public function renderOpenHouseMetaBox($post)
    {
	    $openhuisVan = get_post_meta($post->ID,'huis_OpenHuisVan',true);
	    $openhuisTot = get_post_meta($post->ID,'huis_OpenHuisTot',true);
	    
	    $aanwezig = ($openhuisTot && $openhuisVan);
	    
	    echo '<div class="form-table" style="margin: 10px;">';
		    echo '<b>Open huis actief: <input type="checkbox" ' .($aanwezig?'checked':'') .' name="yog_openhuis_actief" id="openhuischeck" onchange="if(jQuery(\'#openhuischeck:checked\').val() !== undefined) { jQuery(\'#datumselectie\').slideDown(); }else{ jQuery(\'#datumselectie\').slideUp(); }"><b>';
	    echo '</div>';
	    echo '<div id="datumselectie" style="margin: 10px; ' .($aanwezig?'':'display: none;') .'">';
		    $van = strtotime($openhuisVan);
		    $tot = strtotime($openhuisTot);
		    echo '<b>Datum: <b>';
		    $select = '<select name="yog_oh_van_dag">';
		    for($dag = 1 ; $dag < 32 ; $dag++)
			    $select.= '<option ' .(trim(strftime('%e',$van))==$dag?'selected':'') .' >' .(strlen($dag)==1?'0':'') .$dag .'</option>';
		    $select.= '</select> - ';
		    echo $select;
		    $select = '<select name="yog_oh_van_maand">';
		    for($maand = 1 ; $maand < 13 ; $maand++)
			    $select.= '<option ' .(trim(strftime('%m',$van))==$maand?'selected':'') .' >' .(strlen($maand)==1?'0':'') .$maand .'</option>';
		    $select.= '</select> - ';
		    echo $select;
		    $select = '<select name="yog_oh_van_jaar">';
		    for($jaar = strftime("%Y",time()) ; $jaar < strftime("%Y",time())+10  ; $jaar++)
			    $select.= '<option ' .(trim(strftime('%Y',$van))==$jaar?'selected':'') .' >' .$jaar .'</option>';
		    $select.= '</select>';
		    echo $select;
		    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;van ';
		    $select = '<select name="yog_oh_van_uur">';
		    for($uur = 0; $uur < 23  ; $uur++)
			    $select.= '<option ' .(trim(strftime('%H',$van))==$uur?'selected':'') .' >' .(strlen($uur)==1?'0':'') .$uur .'</option>';
		    $select.= '</select>';
		    echo $select;
		    $select = '<select name="yog_oh_van_minuut">';
		    for($minuut = 0; $minuut < 59  ; $minuut++)
			    $select.= '<option ' .(trim(strftime('%M',$van))==$minuut?'selected':'') .' >' .(strlen($minuut)==1?'0':'') .$minuut .'</option>';
		    $select.= '</select>';
		    echo $select;
		    echo ' tot ';
		    $select = '<select name="yog_oh_tot_uur">';
		    for($uur = 0; $uur < 23  ; $uur++)
			    $select.= '<option ' .(trim(strftime('%H',$tot))==$uur?'selected':'') .' >' .(strlen($uur)==1?'0':'') .$uur .'</option>';
		    $select.= '</select>';
		    echo $select;
		    $select = '<select name="yog_oh_tot_minuut">';
		    for($minuut = 0; $minuut < 59  ; $minuut++)
			    $select.= '<option ' .(trim(strftime('%M',$tot))==$minuut?'selected':'') .' >' .(strlen($minuut)==1?'0':'') .$minuut .'</option>';
		    $select.= '</select>';
		    echo $select;
	    echo '</div>';
    }
    
    /**
      * @desc Extend saving of huis post type with storing of custom fields
      * 
      * @param int $postId
      * @param StdClass $post
      * @return void
      */
    public function extendSave($postId, $post)
    {
      // Check if post is of type wonen
	    if ($post->post_type != POST_TYPE_WONEN)
        return $postId;

      // Verify nonce
	    if ( !wp_verify_nonce($_POST['yog_nonce'], plugin_basename(__FILE__) ))
		    return $postId;
        
	    // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
	    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
	      return $postId;

	    // Check permissions
		  if (!current_user_can( 'edit_page', $postId ) )
		    return $postId;
      
		  // Handle meta data
      $fieldsSettings = YogFieldsSettingsAbstract::create($post->post_type);

      // Handle normal fields
		  foreach ($fieldsSettings->getFieldNames() as $fieldName)
		  {
			  if (empty($_POST[$fieldName]))
			    delete_post_meta($postId, $fieldName);
			  else
			    update_post_meta($postId, $fieldName, $_POST[$fieldName]);
		  }
      
		  // Handle open huis
		  if ($_POST['yog_openhuis_actief'] == 'on')
      {
			  $tijdVan = $_POST['yog_oh_van_jaar'] . '-' . $_POST['yog_oh_van_maand'] . '-' . $_POST['yog_oh_van_dag'] . ' ' . $_POST['yog_oh_van_uur'] . ':' . $_POST['yog_oh_van_minuut'];
			  $tijdTot = $_POST['yog_oh_van_jaar'] ."-" .$_POST['yog_oh_van_maand'] ."-" .$_POST['yog_oh_van_dag'] ." " .$_POST['yog_oh_tot_uur'] .":" .$_POST['yog_oh_tot_minuut'];
        
			  update_post_meta($postId, POST_TYPE_WONEN . '_OpenHuisVan',$tijdVan);
			  update_post_meta($postId, POST_TYPE_WONEN . '_OpenHuisTot',$tijdTot);
			  
			  if ((!empty($tijdVan) && strtotime($tijdVan) > time()) || (!empty($tijdTot) && strtotime($tijdTot) > time()))
				  wp_set_object_terms( $postID, 'open-huis', 'category', true );
		  }
      else
      {
        delete_post_meta($postId, POST_TYPE_WONEN . '_OpenHuisVan');
        delete_post_meta($postId, POST_TYPE_WONEN . 'huis_OpenHuisTot');
      }
    }
  }
?>
