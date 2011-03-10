<?php
// Registreren van categorieen
function yog_registerCategories()
{
	// Hoofcategorie aanmaken
	$consument  = get_cat_ID( 'Consument' );
	if(!$consument){		
		$arg = array('description' => "Consument");
		$consument = wp_insert_term("Consument", "category",$arg);
		$consument = $consument['term_id'];
	}
	// Subcategorieen aanmaken
	$subcategorieen = array('Bestaand','Nieuwbouw','Open huis','bouwgrond','Parkeergelegenheid','Berging','Standplaats','Ligplaats','Verhuur','Verkoop','Verkocht/verhuurd');
	foreach ($subcategorieen as $subcategorie) {
		if(get_cat_ID($subcategorie))
			continue;
		$arg = array('description' => $subcategorie, 'parent'=>$consument);
		wp_insert_term($subcategorie, "category",$arg);
	}
	// Woonruimte
	$woonruimte  = get_cat_ID( 'Woonruimte' );
	if(!$woonruimte){
		$arg = array('description' => "Woonruimte",'parent'=>$consument);
		$woonruimte = wp_insert_term("Woonruimte", "category",$arg);
		$woonruimte = $woonruimte['term_id'];
	}
	$woonruimten = array('Appartement','Woonhuis');
	foreach ($woonruimten as $woonsoort) {
		if(get_cat_ID($woonsoort))
			continue;
		$arg = array('description' => $woonsoort,'parent'=>$woonruimte);
		wp_insert_term($woonsoort, "category",$arg);
	}
}

// Template file plaatsen in map theme als deze nog niet bestaat, returned true als er een single-huis template is, of is aangemaakt
function yog_install_singleTemplate()
{
	if(!is_file(get_template_directory() .'/single-huis.php')){
		// Bestand bestaat nog niet
		if(is_writable(get_template_directory())){
			$file = __FILE__;
			$file = str_replace(basename($file),'yesco-og-single-huis.php',$file);
			copy($file,get_template_directory() .'/single-huis.php');
			// Kijk of het bestand gegenereerd is
			return (is_file(get_template_directory() .'/single-huis.php'));
		}else
			return false;
	}else
		return true;
}

function yog_update_openhuizen()
{
	// alle objecten ophalen
	$objecten = get_posts( array('post_type'=>'huis','numberposts'=>-1) );
	foreach ($objecten as $object) {
		$gewijzigd = false;
			$huidigecategorieen = wp_get_object_terms( $object->ID, 'category' );
			$pointer = 0;
			foreach ($huidigecategorieen as $categorie) {
				if($categorie->slug == 'open-huis'){
					// Geen open huis?
					if(!((get_post_meta($object->ID,'huis_OpenHuisTot',true) && get_post_meta($object->ID,'huis_OpenHuisTot',true) < time()) || (get_post_meta($object->ID,'huis_OpenHuisVan',true) && strtotime(get_post_meta($object->ID,'huis_OpenHuisVan',true)) > time()))){
						$gewijzigd = true;
						unset($huidigecategorieen[$pointer]);
						$gewijzigd = true;
						break;
					}
				}
				$pointer++;
			}
		if($gewijzigd){
			$terms = array();
			// maak lijst van terms
			foreach ($huidigecategorieen as $categorie)
				$terms[] = $categorie->slug;
			wp_set_object_terms( $object->ID, $terms, 'category', false );
		} 
	}
}

function yog_publish_homepage($query)
{
	// check instelling
	if(get_option('yog_huizenophome') && is_home() && false == $query->query_vars['suppress_filters'])
			$query->set( 'post_type', array( 'post', 'huis') );
	return $query;
	
}

?>
