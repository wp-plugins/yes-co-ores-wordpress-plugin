<?php
require_once('yesco-og-config.php');
require_once('yesco-og-functions-public.php');
require_once('includes/widgets/yog_search_form_widget.php');
require_once('includes/widgets/yog_recent_objects_widget.php');
require_once('includes/widgets/yog_contact_form_widget.php');
require_once('includes/widgets/yog_object_attachments_widget.php');

// Functies voor Yesco-OG Plugin

// Functies voor Widget
function yog_registerWidgets()
{
  register_widget('YogRecentObjectsWidget');
  register_widget('YogSearchFormWonenWidget');
  register_widget('YogContactFormWidget');
  register_widget('YogObjectAttachmentsWidget');
}

// Registreer huis post-type
function yog_registerPT()
{
	register_post_type('huis',
	array('labels'    => array('name' => __( 'Yes-co ORES' ),
	'singular_name' => __( 'Yes-co ORES' )),
	'public'             => true,
	'show_ui'           => true, // UI in admin panel
	'show_in_nav_menus' => true,
	'capability_type'   => 'post',
	'hierarchical'      => false,
	'rewrite'           => array('slug' => 'huis'), // Permalinks format
	'supports'          => array('title','editor'),
	'taxonomies'        => array('category', 'post_tag')
	)
	);
	
	register_post_type('relatie',
	array('labels'    => array('name' => __( 'Relaties' ),
	'singular_name' => __( 'Relatie' )),
	'public'             => true,
	'show_ui'           => true, // UI in admin panel
	'show_in_nav_menus' => true,
	'capability_type'   => 'post',
	'hierarchical'      => true,
	'rewrite'           => array('slug' => 'relatie'), // Permalinks format
	'supports'          => array('title'),
	'taxonomies'        => array('category', 'post_tag')
	)
	);

	add_action("admin_init", "yog_add_meta_boxes");
	add_action('save_post', 'yog_update_huis', 1, 2);
	// Custom filters
	customFilters();
	add_action('posts_where_request', 'yog_zoeksupport');
}
function customFilters()
{
	// filters en kolommenweergave toevoegen
	add_action("manage_posts_custom_column", "yog_custom_columns");
	add_filter("manage_edit-huis_columns", "yog_custom_filter");
}
// Functies voor weergave kolommen
function yog_custom_filter($columns)
{
	$columns = array(
	"cb" => "<input type=\"checkbox\" />",
	"title" => "Object",
	"omschrijving" => "Omschrijving",
	"adres" => "Adres",
	"scenario" => "Scenario"
	);
	return $columns;
}
// Zoeksupport toevoegen
function yog_zoeksupport($where){
	
	global $wpdb,$wp;
	if (is_search()) {
		if(!$wp->query_vars['s'] || $wp->query_vars['s'] == '%25' || $wp->query_vars['s'] == '%')
			return $where;
		$zoekterm = addslashes($wp->query_vars['s']);
		$zoekenop = array('huis_Wijk','huis_Buurt','huis_Land','huis_Provincie','huis_Gemeente','huis_Plaats','huis_Straat','huis_Huisnummer','huis_Postcode','huis_SoortWoning','huis_TypeWoning','huis_KenmerkWoning');
		foreach ($zoekenop as $zoek)
			$extra[] = 	"( $wpdb->postmeta.meta_key = '" .$zoek ."' AND $wpdb->postmeta.meta_value LIKE '%" .$zoekterm ."%' )";				
		$extra = implode(' OR ',$extra);
		$q = "SELECT DISTINCT(post_id) FROM $wpdb->postmeta WHERE " .$extra;
		// We zoeken zelf in de metadata
		$resultaten =  $wpdb->get_col($q,0);
		if(is_array($resultaten) && count($resultaten)){
			// Ook de gewone data meenemen
			$where= " AND ( $wpdb->posts.ID IN (" .implode(',',$resultaten) .") ";
			$where.= " OR ( $wpdb->posts.post_title LIKE '%" .$zoekterm ."%') OR ( $wpdb->posts.post_content LIKE '%" .$zoekterm ."%') ";
			$where .= ") AND $wpdb->posts.post_type IN ('post', 'page', 'attachment', 'huis', 'relatie') AND $wpdb->posts.post_status = 'publish'";
		}
		return $where;
		
	}
	return $where;
}
function yog_zoeksupportjoins($join)
{
	global $wpdb;
	$join .= ",$wpdb->postmeta";
	return $join;
}

function yog_custom_columns($column)
{
	global $post;
	if ("ID" == $column) echo $post->ID;
	elseif ("scenario" == $column) echo get_post_meta($post->ID, 'huis_scenario', true);
	elseif ("omschrijving" == $column){
		$content = $post->post_content ;
		if(strlen($content) > 50)
		$content = substr($content,0,50) .'...';
		echo $content;
	}elseif ("adres" == $column) echo get_post_meta($post->ID, 'huis_Straat', true) .' ' .get_post_meta($post->ID, 'huis_Huisnummer', true) .' ' .get_post_meta($post->ID, 'huis_Plaats', true);
	
	//print_r($columns);
	//$columns['scenario'] = 'test';
    //return $columns;
}
// Functies voor zoeken



/**
* @desc Register the post types to use on several pages
* 
* @param $query
* @return $query
*/
function yog_registerPTFeed($query)
{
  if (is_feed() || is_category())
  {
    $currentPostType  = $query->get('post_type');
    $postTypes        = array('post', 'huis', 'attachment');
    
    if (!in_array($currentPostType, $postTypes))
      $postTypes[] = $currentPostType;
      
		$query->set('post_type', $postTypes);
  }

	return $query;
}

/**
  * @desc Extend saving of huis post type with storing of custom fields
  * 
  * @param int $postId
  * @param StdClass $post
  * @return void
  */
function yog_update_huis($postId, $post)
{
	
	if ( !wp_verify_nonce( $_POST['yog_nonce'], plugin_basename(__FILE__) )) {
		return $postId;
	}
	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
	// to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
	return $postId;

	// Check permissions
	if ( 'page' == $post->post_type ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
		return $postId;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
		return $postId;
	}

	if ($post->post_type == 'huis')
	{
		// Handle meta data
		if (defined('YESCO_OG_TYPE_MAPPING'))
		$typeMapping = unserialize(YESCO_OG_TYPE_MAPPING);

		if (empty($typeMapping))
		return $postId;

		foreach ($typeMapping as $field => $options)
		{
			if (empty($_POST[$field]))
			delete_post_meta($postId, $field);
			else
			update_post_meta($postId, $field, $_POST[$field]);
		}
		// Kijk of openhuis actief is
		if($_POST['yog_openhuis_actief'] == 'on'){
			$tijdVan = $_POST['yog_oh_van_jaar'] ."-" .$_POST['yog_oh_van_maand'] ."-" .$_POST['yog_oh_van_dag'] ." " .$_POST['yog_oh_van_uur'] .":" .$_POST['yog_oh_van_minuut'];
			$tijdTot = $_POST['yog_oh_van_jaar'] ."-" .$_POST['yog_oh_van_maand'] ."-" .$_POST['yog_oh_van_dag'] ." " .$_POST['yog_oh_tot_uur'] .":" .$_POST['yog_oh_tot_minuut'];
			update_post_meta($postId,'huis_OpenHuisVan',$tijdVan);
			update_post_meta($postId,'huis_OpenHuisTot',$tijdTot);
			
			if(get_post_meta($postId,'huis_OpenHuisTot',true) && strtotime(get_post_meta($postId,'huis_OpenHuisTot',true)) < time()){
				wp_set_object_terms( $postID, 'open-huis', 'category', true );	
			}else if(get_post_meta($postId,'huis_OpenHuisVan',true) && strtotime(get_post_meta($postId,'huis_OpenHuisVan',true)) > time()){
				wp_set_object_terms( $postId, 'open-huis', 'category', true );				
			}
		}
	}
	
	
}

/**
  * @desc Add containers to project screen
  * 
  * @param void
  * @return void
  */
function yog_add_meta_boxes()
{
	add_meta_box("yog-meta-sync", "Synchronisatie", "yog_meta_sync",'huis', "normal", "low");
	add_meta_box("yog-standard-meta", "Basis gegevens", "yog_meta_standaard",'huis', "normal", "low");
	add_meta_box('yog-price-meta', 'Prijs', 'yog_meta_price', 'huis', 'normal', 'low');
	add_meta_box("yog-extended-meta", "Gegevens object", "yog_meta_extra",'huis', "normal", "low");
	add_meta_box("yog-openhuis", "Open huis", "yog_meta_openhuis",'huis', "normal", "low");
	add_meta_box("yog-movies", "Video", "yog_movies",'huis', "normal", "low");
	add_meta_box("yog-documents", "Documenten", "yog_documents",'huis', "normal", "low");
	add_meta_box("yog-links", "Externe koppelingen", "yog_links",'huis', "normal", "low");
}
function yog_meta_standaard($post)
{
	echo '<table class="form-table">';
	echo yog_admin_retrieveInputs($post->ID, array('Naam', 'Straat', 'Huisnummer', 'Postcode', 'Wijk', 'Buurt', 'Plaats', 'Gemeente', 'Provincie', 'Land', 'Longitude', 'Latitude', 'Status'));
	echo '</table>';
}
function yog_meta_price($post)
{
	echo '<table class="form-table">';

	// Koop
	echo '<tr>';
	echo '<th colspan="2"><b>Koop</b></th>';
	echo '</tr>';
	echo yog_admin_retrieveInputs($post->ID, array('KoopPrijsSoort', 'KoopPrijs', 'KoopPrijsConditie'));

	// Huur
	echo '<tr>';
	echo '<th colspan="2"><b>Huur</b></th>';
	echo '</tr>';
	echo yog_admin_retrieveInputs($post->ID, array('HuurPrijs', 'HuurPrijsConditie'));

	echo '</table>';
}
function yog_meta_sync($post)
{
	echo '<input type="hidden" name="yog_nonce" id="myplugin_noncename" value="' .wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

	echo '<table class="form-table">';
	echo yog_admin_retrieveInputs($post->ID, array('uuid', 'scenario'), true);
	echo '</table>';
}
function yog_meta_extra($post)
{
	echo '<table class="form-table">';
	echo yog_admin_retrieveInputs($post->ID, array('Type', 'SoortWoning', 'TypeWoning', 'KenmerkWoning', 'Bouwjaar', 'Aantalkamers', 'Oppervlakte', 'OppervlaktePerceel', 'Inhoud', 'Ligging', 'GarageType', 'TuinType', 'BergingType', 'PraktijkruimteType', 'EnergielabelKlasse'));
	echo '</table>';
}
function yog_meta_openhuis($post)
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

function yog_links($post)
{
	$formulier = '';
	$formulier.= '<br><b>Een externe koppeling toevoegen: </b><br>';
	$formulier.= '<div style="width: 620px; border: 1px solid #ccc; margin: 10px 5px; background: #eee; padding: 10px; border-radius: 5px;"><span style="width: 280px; float: left;"><b>Type (bijvoorbeeld \'website\')</b>: </span><input id="link_type" style="margin: 0; width: 237px;" type="text" value="" /><br>';
	$formulier.= '<br><span style="width: 280px; float: left;"><b>Titel</b>: </span><input style="margin: 0; width: 237px;" id="link_titel" type="text" value="" /><br>';
	$formulier.= '<br><span style="width: 280px; float: left;"><b>Link</b>: </span>http://<input style="margin: 0; width: 200px;" type="text" id="link_url" value="" /><input type="button" class="button-primary" onclick="addLink(' .$post->ID .',jQuery(\'#link_titel\').val(),jQuery(\'#link_type\').val(),jQuery(\'#link_url\').val());" value="Toevoegen" style="margin-left: 10px;"></div>';
	
	echo $formulier;
	
	$links      = yog_retrieveLinks($post->ID);
	$newLinkId  = 0;
	
	$overzicht.= '<br><b>Gekoppelde links: </b><br>';

	$overzicht.= '<div style="border: 1px solid #ccc; margin: 10px 5px; background: #eee; padding: 10px; border-radius: 5px;"><table class="form-table">';
	$overzicht.= '<thead id="link_tabel">';
	$overzicht.= '<th style="width: 60px;"><b>Type</b></th>';
	$overzicht.= '<th><b>Titel</b></th>';
	$overzicht.= '<th><b>Link</b></th>';
	$overzicht.= '<th style="width: 40px;"></th>';
	$overzicht.= '</thead>';
	if(is_array($links) && count($links)){
		foreach ($links as $linkUUID => $link)
		{
			$overzicht.= '<tr>';
			$overzicht.= '<td>' . $link['type'] . '</td>';
			$overzicht.= '<td>' . $link['title'] . '</td>';
			$overzicht.= '<td><a href="' .$link['url'] .'">' . $link['url'] . '</a></td>';
			$overzicht.= '<td><input type="button" class="button-primary" onclick="removeLink(\'' .$link['uuid'] .'\',\'' .$post->ID .'\', jQuery(this).parent().parent() );" value="Verwijderen" style="margin-left: 5px;"></td>';
			$overzicht.= '</tr>';
		}
	}
	$overzicht.= '</table></div>';
	echo $overzicht;
}
function yog_documents($post)
{
	$formulier = '';
	$formulier.= '<br><b>Een document toevoegen: </b><br>';
	$formulier.= '<div style="width: 620px; border: 1px solid #ccc; margin: 10px 5px; background: #eee; padding: 10px; border-radius: 5px;"><span style="width: 280px; float: left;"><b>Type (bijvoorbeeld \'brochure\')</b>: </span><input id="document_type" style="margin: 0; width: 237px;" type="text" value="" /><br>';
	$formulier.= '<br><span style="width: 280px; float: left;"><b>Titel</b>: </span><input style="margin: 0; width: 237px;" id="document_titel" type="text" value="" /><br>';
	$formulier.= '<br><span style="width: 280px; float: left;"><b>Link</b>: </span>http://<input style="margin: 0; width: 200px;" type="text" id="document_url" value="" /><input type="button" class="button-primary" onclick="addDocument(' .$post->ID .',jQuery(\'#document_titel\').val(),jQuery(\'#document_type\').val(),jQuery(\'#document_url\').val());" value="Toevoegen" style="margin-left: 10px;"></div>';
	
	echo $formulier;
	$documents  = yog_retrieveDocuments($post->ID);
	$overzicht.= '<br><b>Gekoppelde documenten: </b><br>';

	$overzicht.= '<div style="border: 1px solid #ccc; margin: 10px 5px; background: #eee; padding: 10px; border-radius: 5px;"><table class="form-table">';
	$overzicht.= '<thead id="documenten_tabel">';
	$overzicht.= '<th style="width: 60px;"><b>Type</b></th>';
	$overzicht.= '<th><b>Titel</b></th>';
	$overzicht.= '<th><b>Link</b></th>';
	$overzicht.= '<th style="width: 40px;"></th>';
	$overzicht.= '</thead>';
	if(is_array($documents) && count($documents)){
		foreach ($documents as $documentsUUID => $document)
		{
			$overzicht.= '<tr>';
			$overzicht.= '<td>' . $document['type'] . '</td>';
			$overzicht.= '<td>' . $document['title'] . '</td>';
			$overzicht.= '<td><a href="' .$document['sourceurl'] .'">' . $document['sourceurl'] . '</a></td>';
			$overzicht.= '<td><input type="button" class="button-primary" onclick="removeDocument(\'' .$document['uuid'] .'\',\'' .$post->ID .'\', jQuery(this).parent().parent() );" value="Verwijderen" style="margin-left: 5px;"></td>';
			$overzicht.= '</tr>';
		}
	}
	$overzicht.= '<tr id="laatste_document"><td colspan="4"></td></tr>';
	$overzicht.= '</table></div>';
	echo $overzicht;
}
function yog_movies($post)
{
	$formulier = '';
	$formulier.= '<br><b>Een video toevoegen: </b><br>';
	
	$videoservices = array();
	$videoservices['Youtube'] = 'http://www.youtube.com/';
	$videoservices['Vimeo'] = 'http://vimeo.com/';
	$videoservices['Flickr'] = 'http://www.flickr.com/';
	
	$select = '<select id="video_type" style="margin: 0; width: 237px;">';
	foreach ($videoservices as $videoservice=>$link) {
		$select.= '<option value="' .$link .'">' .$videoservice .'</option>';
	}
	$select.= '</select>';
	
	$formulier.= '<div style="width: 740px; border: 1px solid #ccc; margin: 10px 5px; background: #eee; padding: 10px; border-radius: 5px;"><span style="width: 400px; float: left;"><b>Videoprovider</b>: </span>' .$select .'<br>';
	$formulier.= '<br><span style="width: 400px; float: left;"><b>Titel</b>: </span><input style="margin: 0; width: 237px;" id="video_titel" type="text" value="" /><br>';
	$formulier.= '<br><span style="width: 400px; float: left;"><b>Link waarop de video te zien is, bijvoorbeeld <br><i>www.youtube.com/watch?v=duqr82aYKRY)</i></b>:  </span>http://<input style="margin: 0; width: 200px;" type="text" id="video_url" value="" /><input type="button" class="button-primary" onclick="addVideo(' .$post->ID .',jQuery(\'#video_titel\').val(),jQuery(\'#video_type\').val(),jQuery(\'#video_url\').val());" value="Toevoegen" style="margin-left: 10px;"></div>';
	
	echo $formulier;
	
	$videos      = yog_retrieveMovies($post->ID);
	$newLinkId  = 0;
	
	$overzicht.= '<br><b>Gekoppelde videos: </b><br>';

	$overzicht.= '<div style="border: 1px solid #ccc; margin: 10px 5px; background: #eee; padding: 10px; border-radius: 5px;"><table class="form-table">';
	$overzicht.= '<thead id="video_tabel">';
	$overzicht.= '<th><b>Titel</b></th>';
	$overzicht.= '<th><b>Link</b></th>';
	$overzicht.= '<th style="width: 40px;"></th>';
	$overzicht.= '</thead>';
	if(is_array($videos) && count($videos)){
		foreach ($videos as $videoUUID => $video)
		{
			$overzicht.= '<tr>';
			$overzicht.= '<td>' . $video['title'] . '</td>';
			$overzicht.= '<td><a href="' .$video['videoereference_id'] .'">' . $video['videoereference_id'] . '</a></td>';
			$overzicht.= '<td><input type="button" class="button-primary" onclick="removeVideo(\'' .$video['uuid'] .'\',\'' .$post->ID .'\', jQuery(this).parent().parent() );" value="Verwijderen" style="margin-left: 5px;"></td>';
			$overzicht.= '</tr>';
		}
	}
	$overzicht.= '<tr id="laatste_video"><td colspan="3"></td></tr>';
	$overzicht.= '</table></div>';
	echo $overzicht;
}

/**
  * @desc Retrieve input fields for specific fields
  * 
  * @param $postId
  * @param array $fields
  * @param $readOnly (default: false)
  * @return array
  */
function yog_admin_retrieveInputs($postId, $fields, $readOnly = false)
{
	if (!is_array($fields))
	throw new Exception(__METHOD__ . '; Invalid specs provided, must be an array');

	foreach ($fields as $key => $field)
	{
		$fields[$key] = 'huis_' . $field;
	}

	$typeMapping        = array();
	$html               = '';
	$customFieldValues  = get_post_custom($postId);


	if (defined('YESCO_OG_TYPE_MAPPING'))
	$typeMapping = unserialize(YESCO_OG_TYPE_MAPPING);

	foreach ($fields as $field)
	{
		$value    = array_key_exists($field, $customFieldValues) ? $customFieldValues[$field][0] : '';
		$title    = empty($typeMapping[$field]['title']) ? str_replace('huis_', '', $field) : $typeMapping[$field]['title'];
		$width    = empty($typeMapping[$field]['width']) ? 300 : $typeMapping[$field]['width'];
		$prefix   = '';
		$addition = '';

		if (!empty($typeMapping[$field]['type']))
		{
			switch ($typeMapping[$field]['type'])
			{
				case 'oppervlakte':
					$addition = ' m&sup2;';
					break;
				case 'inhoud';
				$addition = ' m&sup3;';
				break;
				case 'price':
					$prefix = '&euro; ';
					break;
			}
		}

		$html .= '<tr>';
		$html .= '<th scope="row">' . $title . '</th>';
		$html .= '<td>';
		$html .= $prefix;
		$html .= '<input type="text" ' . (($readOnly === true) ? 'readonly="true" ' : '')  . 'style="width: ' . $width . 'px;" name="' . $field . '" value="' . $value . '" />';
		$html .= $addition;
		$html .= '</td>';
		$html .= '</tr>';
	}

	return $html;
}



// Weergave functies OG
function yesco_OG_Huis() {;
echo "<p id='huis'>Huis</p>";

}

// Checked of databasetabellen al zijn aangemaakt
function yesco_OG_pluginInstalled()
{
	/*global $wpdb;
	$table_name = $wpdb->prefix .'yesco_objecten';
	return ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name); */
	return true;
}

// General functies
function yesco_OG_addJavaScript($bestand)
{
}

// Opties van Plugin
function yesco_OG_optiemenu() {
	add_options_page('Yes-co ORES opties', 'Yes-co ORES', 'edit_plugins', 'yesco_OG', 'yesco_OG_pluginOpties');
}
function yesco_OG_pluginOpties() {
	echo '<div class="wrap" style="padding-left: 20px;">';
	echo '<h2>Yes-co Open Real Estate System instellingen</h2>';
	wp_nonce_field('update-options');
	if(!yog_install_singleTemplate())
		echo '<div id="message" class="error below-h2" style=" padding: 5px 10px;"><b>Let op</b>: het ingestelde thema heeft op dit moment geen template om objecten te tonen. Op zich is dat geen probleem, maar er kan meer informatie over een object worden getoond. We hebben geprobeerd het standaard-template in te stellen, maar de rechten op de map verbieden ons dat.<br><br>Als u de webserver schrijfrechten geeft op de volgende map, dan plaatsen wij automatisch een template om objecten weer te geven.<br><i><b>' .get_template_directory() .'</b></i></div>';
	
	if (!yog_uploadFolderWritable())
  {
    $uploadDir = wp_upload_dir();
		echo '<div id="message" class="error below-h2" style=" padding: 5px 10px;"><b>Let op</b>: de plug-in kon niet geactiveerd worden, de upload map van uw WordPress installatie is beveiligd tegen schrijven. Dat betekent dat we geen afbeelingen voor u kunnen opslaan. Stel onderstaande locatie zo in, dat deze beschreven kan worden door de webserver. <br><i><b>' . $uploadDir['basedir'] .'</b></i></div>';
		echo '</div>';
		return;
	}
		
		
	echo 'Projecten plaatsen in blog (Projecten zullen tussen \'normale\' blogposts verschijnen): ';
	echo '<input type="checkbox" ' .(get_option('yog_huizenophome')?'checked':'') .' onclick="toggleHome(jQuery(this));"></input><span></span>';
	echo '<h3>Gekoppelde yes-co open accounts</h3>';
	echo '<b>Een koppeling toevoegen:</b><br>';
	echo 'Activatiecode: <input id="newsecret" type="text" style="width: 58px" maxlength="6" value="" /> <input type="button" class="button-primary" id="yog_add_koppeling" onclick="addKoppeling(jQuery(\'#newsecret\').val());jQuery(\'#newsecret\').val(\'\');" value="Koppeling toevoegen" style="margin-left: 10px;"><img style="display: inline; margin-left: 10px; display: none;" src="' .$GLOBALS['yesco_og']['imagepath'] .'loading.gif"><span class="description" style="margin-left: 5px;"></span>';

	$koppelingen = get_option('yog_koppelingen');
	if(is_array($koppelingen)){
		foreach ($koppelingen as $koppeling) {
			echo '<div style="border: 1px solid #BBB; border-radius: 6px; padding: 3px; margin-top: 15px; width: 440px; overflow: auto;">';
			echo '<img src="' .$GLOBALS['yesco_og']['imagepath'] .'yes-co.png" style="float: left;">';
			$verwijderenlink = '<a style="cursor: pointer;" onclick="jQuery(this).next().show(); jQuery(this).hide();">Koppeling verwijderen</a><span style="display: none;">Wilt u deze koppeling verbreken? <a onclick="jQuery(this).parent().hide();jQuery(this).parent().prev().show();" style="cursor: pointer;">annuleren</a> | <a onclick="verwijderKoppeling(\'' .$koppeling['activatiecode'] .'\',jQuery(this));" style="cursor: pointer;">doorgaan</a></span>';
			// naam ophalen
			$koppeling['naam'] = 'Nog niet bekend, wacht op activatie';
			// select Business die hoort bij Koppeling
			global $wpdb;
			$postmetatable = $wpdb->prefix .'postmeta';
			$posttable = $wpdb->prefix .'posts';
			$postTitle = $wpdb->get_var("SELECT p.post_title as post FROM " .$posttable ." p," .$postmetatable ." pm1," .$postmetatable ." pm2 WHERE p.ID=pm1.post_id AND p.ID = pm2.post_id AND pm1.meta_key = 'relatie_koppelinguuid' AND pm1.meta_value = '" .$koppeling['UUID'] ."' AND pm2.meta_key = 'relatie_type' AND pm2.meta_value = 'Business'");	
			if($postTitle)
				$koppeling['naam'] = $postTitle;
			echo '<span style="float: left; margin-left: 10px;"><b>Naam:</b> ' .$koppeling['naam'] .'<br><b>Status:</b> ' .$koppeling['status'] .'<br><b>Activatiecode:</b> ' .$koppeling['activatiecode'] .' <br>' .$verwijderenlink .'</span>';
			echo '</div>';
		}
	}
	echo '<span id="laatstekoppeling"></span>';
	echo '</div>';
}

// Synchronisatie functies MCP
function getXML($url)
{
	// Forceer HTTP 1.0 IVM Authenticatie via url
	ini_set('user_agent','MSIE 4\.0b2;');
	if($contents = file_get_contents($url))
	return $contents;
	return '';

}
function getFeedsFromIndexXML($xmlstring)
{
	$feeds = array();
	$xml = new SimpleXMLElement($xmlstring);
	foreach ($xml->entry as $entry)
	$feeds[trim($entry->title)] = trim($entry->link['href']);
	return $feeds;
}
function getSelectedFeed($feeds)
{
	foreach ($feeds as $titel=>$link) {
		// select good string
		if(strtolower($titel) != ''){
			// URL voor feed
			$url = str_replace('https://','',$link);
			// User/Pass toevoegen aan url
			$url = 'https://' .get_option('yog_user') .':' .get_option('yog_password') .'@' .$url;
			return getXML($url);
		}
	}
	return null;
}

// return JSON to requester
function returnJSON($reply)
{
	echo json_encode($reply);
	exit();
}
// parseJSON
function parseJSON($url)
{
	if($contents = file_get_contents($url))
	return json_decode($contents);
	return false;
}

// kijk of get request fatsoenlijk getekend is en check dat tegen lokale secrets,
// returned koppeling waarvoor getekend is
function requestIsProperlySigned()
{
	$koppelingen = get_option('yog_koppelingen');
	if(is_array($koppelingen)){
		foreach ($koppelingen as $koppeling) {
			$args = $_GET;
			ksort($args);
			$payload = '';
			foreach ($args as $key => $value)
			if ($key != 'signature')
			$payload .= $key . '=' . $value;

			if (md5($payload . $koppeling['activatiecode']) == $args['signature']){
				return $koppeling;
			}
		}
	}
	return false;
}

function saveKoppeling($koppeling)
{
	$koppeling['LastSync'] = time();
	// secret is ok, we kunnen de UUID toevoegen / wijzigen
	$koppelingen = get_option('yog_koppelingen');
	$newkoppelingen = array();
	if(is_array($koppelingen)){
		foreach ($koppelingen as $koppelingc){
			if($koppelingc['activatiecode'] == $koppeling['activatiecode'])
			$newkoppelingen[] = $koppeling;
			else
			$newkoppelingen[] = $koppelingc;
		}
	}
	update_option('yog_koppelingen',$newkoppelingen);
}

/**
* @desc Check if upload folder is writable
* 
* @param void
* @return bool
*/
function yog_uploadFolderWritable()
{
  $uploadDir = wp_upload_dir();
	return(is_writeable($uploadDir['basedir']));
}
?>
