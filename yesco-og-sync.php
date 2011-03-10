<?php

error_reporting(E_ERROR);
ini_set('display_errors', '1');

// foutmeldingen
define("WRONG_SIGNATURE", 1);
define("SERVER_ERROR", 2);

// gebruikersnaam en wachtwoord MCP
update_option('yog_user','motivowpplugin');
update_option('yog_password','Piwr39qp3@');

// initieer databaseobject
global $wpdb;

$GLOBALS['verwerkteAfbeeldingen'] = array();
$GLOBALS['verwerkteProjecten'] = array();
$GLOBALS['verwerkteRelaties'] = array();

// reply voorbereiden
$reply = array();

if(!requestIsProperlySigned()){
	$reply['status'] = 'error';
	$reply['errorcode'] = WRONG_SIGNATURE;
	$reply['error'] = 'Signature does not match a local secret, did you use the wrong secret?';
	returnJSON($reply);
}

// elke koppeling synchroniseren
$koppelingen = get_option('yog_koppelingen');
if(is_array($koppelingen)){
	foreach ($koppelingen as $koppeling) {
		sync($koppeling);
		$koppeling['status'] = 'Laatste synchronisatie op ' .strftime("%a %d %b %H:%M", time());
		$koppeling['relatie'] = $relatie;
		saveKoppeling($koppeling);
	}
	cleanUp();
}else{
	$reply['status'] = 'error';
	$reply['errorcode'] = SERVER_ERROR;
	$reply['error'] = 'Yes-co Open is not (yet) activated on this blog.';
	returnJSON($reply);
}

$reply['status'] = 'ok';
$reply['message'] = 'Synchronisatie voltooid';
returnJSON($reply);

// Verwijdert oude afbeeldingen, relaties en huizen
function cleanUp()
{
	global $wpdb;
	// Oude afbeeldingen verwijderen
	if(is_array($GLOBALS['verwerkteAfbeeldingen']) && count($GLOBALS['verwerkteAfbeeldingen'])){
		$table_name = $wpdb->prefix;
		$query = "SELECT p1.ID FROM " .$table_name ."posts p1, " .$table_name ."posts p2 WHERE p1.post_content NOT IN ('" .implode("','",$GLOBALS['verwerkteAfbeeldingen']) ."') AND p1.post_type='attachment' AND p1.post_parent=p2.ID AND p2.post_type = 'huis'";
		$teverwijderen = $wpdb->get_col($query);
		foreach ($teverwijderen as $verwijderid)
			wp_delete_attachment($verwijderid,true); 
	}
	// Oude relaties verwijderen
	if(is_array($GLOBALS['verwerkteRelaties']) && count($GLOBALS['verwerkteRelaties'])){
		$table_name = $wpdb->prefix;
		$query = "SELECT post_id FROM " .$table_name ." WHERE meta_key = 'relatie_uuid' AND meta_value NOT IN ('" .implode("','",$GLOBALS['verwerkteRelaties']) ."')";
		$teverwijderen = $wpdb->get_col($query);
		foreach ($teverwijderen as $verwijderid)
			wp_delete_attachment($verwijderid);
	}
	// Oude projecten verwijderen
	if(is_array($GLOBALS['verwerkteProjecten']) && count($GLOBALS['verwerkteProjecten'])){
		$table_name = $wpdb->prefix .'postmeta';
		$query = "SELECT post_id FROM " .$table_name ." WHERE meta_key = 'huis_uuid' AND meta_value NOT IN ('" .implode("','",$GLOBALS['verwerkteProjecten']) ."')";
		$teverwijderen = $wpdb->get_col($query);
		foreach ($teverwijderen as $verwijderid)
			wp_delete_post($verwijderid); 
	}
}
// Doet het daadwerkelijke syncen
function sync($koppeling)
{
	$url = 'https://webservice.yes-co.com/3mcp/collection/' .$koppeling['UUID'] .'/1.3/feed/' .$koppeling['UUID'] .'.xml';
	$url = str_replace('https://','',$url);
	$url = 'https://' .get_option('yog_user') .':' .get_option('yog_password') .'@' .$url;
	
	$feed = getXML($url);
	if(!$feed){
		$reply['status'] = 'error';
		$reply['errorcode'] = SERVER_ERROR;
		$reply['error'] = 'The url: ' .str_replace(get_option('yog_user') .':' .get_option('yog_password') .'@','',$url) .' could not be read from';
		returnJSON($reply);
	}
	
	$xml = new SimpleXMLElement($feed);
	// sync projects
	foreach ($xml->entry as $entry){
		if (trim($entry->category['term']) == 'project'){
			//echo '<br>Project start<br>';
    		processProject($koppeling,trim($entry->link['href']),$xml,$entry);
		}
	}
}
// Projectfuncties
function processProject($koppeling,$href,$feedXML,$projectNode )
{
	$title = trim($projectNode->title);	
	$published = trim($projectNode->published);
	$updated = trim($projectNode->updated);
	$uuid = str_replace('urn:uuid:','',trim($projectNode->id));
	$link = trim($projectNode->link['href']);
	
	// opslaan dat we dit project verwerkt hebben
	$GLOBALS['verwerkteProjecten'][] = $uuid;
	
	// check of uuid al bestaat
	global $wpdb;
	$table_name = $wpdb->prefix .'postmeta';
	$postID = $wpdb->get_var("SELECT post_id as post FROM " .$table_name ." WHERE meta_key = 'huis_uuid' AND meta_value = '" .$uuid ."'");	
	$vorigepublish = 0;
	$newpost = false;
	if(!$postID)
		$newpost = true;
	else{
		// Vorige datum opvragen
		$table_name = $wpdb->prefix .'posts';
		$vorigepublish = $wpdb->get_var("SELECT post_date as post FROM " .$table_name ." WHERE ID = '" .$postID ."' LIMIT 1");	
	}
	// open XML van project
	$file = getXML('https://' .get_option('yog_user') .':' .get_option('yog_password') .'@' .str_replace('https://','',$link));
	$projectXML = new SimpleXMLElement($file);
  	$projectXML->registerXPathNamespace('project', 'http://webservice.yesco.nl/mcp/1.3/Project');
	$scenario = trim($projectXML->Scenario);
	
	// met BOG objecten doen we nog even niets
	if(strtolower(substr($scenario,1,1)) == 'o')
		return;
		
	// data verzamelen
	$post = array();
	// alle teksten verzamelen
	$content = '';
	
	foreach ($projectXML->$scenario->Text as $text){
		if($text->Type == 'intro'){
			// Intro, plakken we dus vooraan de string
			$content = '<div class="yogcontent ' .$text->Type .'"><p>' .nl2br($text->Content) .'</p></div>' .$content;
		}else{
			// Standaard tekst, dus gewoon achteraan sluiten
			$content .= '<div class="yogcontent ' .$text->Type .'"><p>' .nl2br($text->Content) .'</p></div>';
		}
	}
	// Als de post nieuw is, forceren we een mooie perma-link, werkt altijd, wat de gebruiker ook voor WP instellingen heeft\	
	if($newpost)
		$post['post_title'] = uniekeTitel($projectXML,$scenario);
	else
		$post['post_title'] = $title;
	$post['post_content'] = $content;
	$post['post_status'] = 'publish';
	$post['post_author'] = 1;
	$post['menu_order'] = 0;
	$post['comment_status'] = 'closed';
	$post['ping_status'] = 'closed';
	$post['post_date'] = $updated;
	$post['post_date_gmt'] = $updated;
	$post['post_parent'] = 0;
	$post['post_type'] = 'huis';
	
	if($newpost){
		// Post inserten
		$postID = wp_insert_post( $post );	
		add_post_meta($postID, 'huis_uuid', $uuid);
	}else{
		// Documenten, Videos en 3Dfotos leegmaken, die hebben geen updated datum, dus we vullen ze allemaal weer opnieuw
		
		// zelf toegevoegde items niet verwijderen.
		$videos = get_post_meta($postID,'huis_Videos',true);
		foreach ($videos as $videouuid=>$video) {
			if(strpos($video['uuid'],'zelftoegevoegd') === false){
				unset($videos[$video['uuid']]);
			}
		}
		$documenten = get_post_meta($postID,'huis_Documenten',true);
		foreach ($documenten as $documentuuid=>$document) {
			if(strpos($document['uuid'],'zelftoegevoegd') === false){
				unset($documenten[$document['uuid']]);
			}
		}
		$links = get_post_meta($postID,'huis_Links',true);
		foreach ($links as $linkuuid=>$link) {
			if(strpos($link['uuid'],'zelftoegevoegd') === false){
				unset($links[$link['uuid']]);
			}
		}
		update_post_meta($postID, 'huis_Documenten',$documenten);	
		update_post_meta($postID, 'huis_Videos',$videos);	
		update_post_meta($postID, 'huis_Fotos360',array());	
		update_post_meta($postID, 'huis_Links',$links);	
		// Post updaten
		$post['ID'] = $postID;
		wp_update_post( $post );
	}
	
	// Relaties van post parsen en koppelen
	foreach ($projectXML->Relation as $relatie){
		// Zoek relatie in feed
		$rol = trim($relatie->Role);
		foreach ($feedXML->entry as $entry){
			if(trim($entry->category['term']) == 'relation' && trim($entry->id) == ('urn:uuid:' .$relatie['uuid'])){
				processRelation($koppeling,trim(trim($entry->link['href'])),$postID,$rol);
			}
		}
	}
	// Links van post parsen en koppelen
	foreach ($projectXML->$scenario->Link as $link){
		$order = 0;
		$order = trim($link['order']);
		processLink($koppeling,$link,$postID,$order);
	}
	// Media van post parsen en koppelen
	foreach ($projectXML->$scenario->Media as $media){
		$mediatype = '';
		$order = 0;
		$ondersteund = array('Image','Photo360','Video','Document');
		foreach ($ondersteund as $type) {
			if($media->$type){
								$mediatype = $type;
				$typenode = $media->$mediatype;
				$order = trim($typenode['order']);
			}
		}
		if($mediatype == 'Image'){
			foreach ($feedXML->entry as $entry){
				if(trim($entry->category['term']) == 'media' && trim($entry->id) == ('urn:uuid:' .$media['uuid'])){
					processImage($koppeling,$entry,$postID,$order,$media);
				}
			}
		}else
			processMediaItem($koppeling,$media,$postID,$mediatype,$order);
	}
	// Moeten we wel verder te gaan, of is dit project nog up to date?
	if(!$newpost && strtotime($updated) >= strtotime($vorigepublish)){
		// Niet nodig
		return;
	}
	// metadata voor post genereren
	$metadata = array();
	
	// basisgegevens in metadata	
	$metadata = parseGeneralDatatometa($projectXML,$scenario);
	// aanvullende metadata verzamelen
	$metadata['huis_scenario'] = strtolower($scenario);
	switch (strtolower($scenario)){
		case 'bbvh':
		case 'bbvk':
		case 'nbvk':
			$metadata = array_merge($metadata,parseWonentometa($projectXML));
		break;
	}
	// metadata aan post koppelen
	if(count($metadata)){
		foreach ($metadata as $key=>$val){
			if($newpost)
				add_post_meta($postID,$key,$val);
			else
				update_post_meta($postID,$key,$val);
		}
	}
	// titel updaten, we hebben nu de juiste informatie
	if($newpost){
		$post = array();
		$post['post_title'] = $title;
		$post['ID'] = $postID;
		wp_update_post( $post );
	}
	
	// Categorieen koppelen
	addCategories($postID,$metadata);
}

function uniekeTitel($xml, $scenario)
{
	return trim($xml->$scenario->General->Address->Street) .' ' .trim($xml->$scenario->General->Address->Housenumber) .' ' .trim($xml->$scenario->General->Address->HousenumberAddition) .' ' .trim($xml->$scenario->General->Address->City);  
}
function parseGeneralDatatometa($xml, $scenario)
{
	$metadata = array();
	
	$metadata['huis_ApiKey'] = trim($xml->YProjectNumber);  
	$metadata['huis_Naam'] = trim($xml->$scenario->General->Name);  
	$metadata['huis_Land'] = trim($xml->$scenario->General->Address->Country);  
	$metadata['huis_Provincie'] = trim($xml->$scenario->General->Address->State);  
	$metadata['huis_Gemeente'] = trim($xml->$scenario->General->Address->Municipality);  
	$metadata['huis_Plaats'] = trim($xml->$scenario->General->Address->City);  
	$metadata['huis_Wijk'] = trim($xml->$scenario->General->Address->Area);  
	$metadata['huis_Buurt'] = trim($xml->$scenario->General->Address->Neighbourhood);  
	$metadata['huis_Straat'] = trim($xml->$scenario->General->Address->Street);  
	$metadata['huis_Huisnummer'] = trim($xml->$scenario->General->Address->Housenumber) .' ' .trim($xml->$scenario->General->Address->HousenumberAddition);  
	$metadata['huis_Postcode'] = trim($xml->$scenario->General->Address->Zipcode);  
	$metadata['huis_Longitude'] = trim($xml->$scenario->General->GeoCode->Longitude);  
	$metadata['huis_Latitude'] = trim($xml->$scenario->General->GeoCode->Latitude);

	// Translate scenario
	switch ($scenario)
	{
	  case 'BBvk':
	    $metadata['huis_scenario'] = 'Bestaande bouw verkoop';
	    break;
	  case 'BBvh':
	    $metadata['huis_scenario'] = 'Bestaande bouw verhuur';
	    break;
	  case 'NBvk':
	    $metadata['huis_scenario'] = 'Nieuwbouw verkoop';
	    break;
	  default:
	    $metadata['huis_scenario'] = $scenario;
	    break;
	}

	// Status
	$objectStatus         = retrieveStringFromXml($xml, '//p:General/p:ObjectStatus');
	$voorbehoudDatum      = retrieveStringFromXml($xml, '//p:General/p:Voorbehoud');
	$transactieNodes      = $xml->xpath('//p:Transactiegegevens/p:Transactie');
	
	if ($transactieNodes !== false && count($transactieNodes) > 0)
	{
	  if ($objectStatus != 'verkocht onder voorbehoud' || empty($voorbehoudDatum) || strtotime($voorbehoudDatum) < date('U'))
	  {
	    if (!empty($transactieNodes[0]->Koop))
	      $objectStatus = 'Verkocht';
	    else if (!empty($transactieNodes[0]->Huur))
	      $objectStatus = 'Verhuurd';
	  }
	}
	
	$metadata['huis_Status'] = $objectStatus;

	return $metadata;
}
function parseWonentometa($xml)
{	
	$metadata = array();

	// Type / SubType
	$type = '';
	if (isset($xml->SubType))
		$type = (string) $xml->SubType;
	else if (isset($xml->Type))
	$type = (string) $xml->Type;
	
	$metadata["huis_Type"] = $type;
	// Premiesubsidies
	$psnodes = $xml->xpath('//project:Details/project:Woonruimte/project:PremieSubsidie/project:Soort');
	$premiesubsidies = '';
	if(is_array($psnodes) && count($psnodes)){
		foreach ($psnodes as $premiesubsidie) {
			if($premiesubsidies)
				$premiesubsidies.= ', ';
			$premiesubsidies.= $premiesubsidie['naam'];
		}
		$metadata['huis_PremieSubsidies'] = $premiesubsidies;
	}
	// Bijzonderheden
	$bznodes = $xml->xpath('//project:Details/project:Woonruimte/project:Diversen/project:Bijzonderheden/project:Bijzonderheid');
	$bijzonderheden = '';
	if(is_array($bznodes) && count($bznodes)){
		foreach ($bznodes as $bijzonderheid) {
			if($bijzonderheden)
				$bijzonderheden.= ', ';
			$bijzonderheden.= $bijzonderheid['naam'];
		}
		$metadata['huis_Bijzonderheden'] = $premiesubsidies;
	}
	
	switch (strtolower($xml->Type))
	{
		case 'woonruimte':
			if (strtolower($type) == 'woonhuis'){
				$metadata["huis_SoortWoning"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Woonhuis/project:SoortWoning');
				$metadata["huis_TypeWoning"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Woonhuis/project:TypeWoning');
				$metadata["huis_KenmerkWoning"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Woonhuis/project:Kenmerk');
			} 
			else{
				$metadata["huis_SoortWoning"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Appartement/project:SoortAppartement');
				$metadata["huis_KenmerkWoning"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Appartement/project:Kenmerk');
			}
			// Kamers / WoonOppervlakte / Inhoud 
			$metadata["huis_Aantalkamers"] = retrieveIntFromXml($xml, '//project:Details/project:Woonruimte/project:Verdieping/project:AantalKamers');
			$metadata["huis_AantalSlaapkamers"] = retrieveIntFromXml($xml, '//project:Details/project:Woonruimte/project:Verdieping/project:AantalSlaapkamers');
			$metadata["huis_Oppervlakte"] = retrieveIntFromXml($xml, '//project:Details/project:Woonruimte/project:WoonOppervlakte');
			$metadata["huis_Inhoud"] = retrieveIntFromXml($xml, 	'//project:Details/project:Woonruimte/project:Inhoud');
			$metadata["huis_Woonkamer"] = retrieveStringFromXml($xml, 	'//project:Details/project:Woonruimte/project:Verdieping/project:Kamers/project:Woonkamer/project:Type');
			$metadata["huis_Keuken"] = retrieveStringFromXml($xml, 	'//project:Details/project:Woonruimte/project:Verdieping/project:Kamers/project:Keuken/project:Type');
			$metadata["huis_KeukenVernieuwd"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Verdieping/project:Kamers/project:Keuken/project:JaarVernieuwd');
			
			// Bouwjaar
			$bouwjaarPeriode = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Bouwjaar/project:Periode');
			$bouwjaar = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Bouwjaar/project:BouwjaarOmschrijving/project:Jaar');
			$metadata["huis_Bouwjaar"] = empty($bouwjaarPeriode) ? $bouwjaar : str_replace(array('2001-', '-1905'), array('na 2001', 'voor 1906'), $bouwjaarPeriode);
			
			// Ligging / Garage / Tuin / Berging / Praktijkruimte / Energielabel
			$metadata["huis_Ligging"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Ligging');
			$metadata["huis_GarageType"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Garage/project:Type');
			$metadata["huis_GarageCapaciteit"] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Garage/project:Capaciteit');
			// Tuin
			$metadata["huis_TuinType"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Tuin/project:Type');
			$metadata["huis_TuinTotaleOppervlakte"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Tuin/project:TotaleOppervlakte');
			$metadata["huis_HoofdTuinType"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Tuin/project:hoofdtuinType');
			$metadata["huis_HoofdTuinTotaleOppervlakte"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Tuin/project:Oppervlakte');
			$metadata["huis_TuinLigging"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Tuin/project:Ligging');
			
			$metadata["huis_BergingType"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:SchuurBerging/project:Soort');
			$metadata["huis_PraktijkruimteType"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Praktijkruimte/project:Type');
			$metadata["huis_PraktijkruimteMogelijk"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:PraktijkruimteMogelijk/project:Type');
			$metadata["huis_EnergielabelKlasse"] = retrieveStringFromXml($xml, '//project:Details/project:Woonruimte/project:Energielabel/project:Energieklasse');
			break;
		case 'bouwgrond':
			$metadata["huis_Oppervlakte"] = retrieveIntFromXml($xml, '//project:Details/project:Bouwgrond/project:Oppervlakte');
			$metadata["huis_Ligging"] = retrieveStringFromXml($xml, '//project:Details/project:Bouwgrond/project:Ligging');
			break;
		case 'parkeergelegenheid':
			$metadata["huis_Oppervlakte"] = retrieveIntFromXml($xml, '//project:Details/project:Parkeergelegenheid/project:Oppervlakte');
			break;
		case 'berging':
			$metadata["huis_Oppervlakte"] = retrieveIntFromXml($xml, '//project:Details/project:Berging/project:Oppervlakte');
			break;
		case 'standplaats':
			$metadata["huis_Oppervlakte"] = retrieveIntFromXml($xml, '//project:Details/project:Standplaats/project:Oppervlakte');
			break;
		case 'ligplaats':
			$metadata["huis_Oppervlakte"] = retrieveIntFromXml($xml, '//project:Details/project:Ligplaats/project:Oppervlakte');
			break;
	}
    
	
    // Koop prijs
	$metadata["huis_KoopPrijsSoort"] = retrieveStringFromXml($xml, '//project:Details/project:Koop/project:PrijsSoort');
	$metadata["huis_KoopPrijs"] = retrieveIntFromXml($xml, '//project:Details/project:Koop/project:Prijs');
    $koopPrijsConditie = retrieveStringFromXml($xml, '//project:Details/project:Koop/project:PrijsConditie');
	$metadata["huis_KoopPrijsConditie"] = str_replace(array('kosten koper', 'vrij op naam'), array('k.k.', 'v.o.n.'), $koopPrijsConditie);
    $metadata["huis_Veilingdatum"] = retrieveStringFromXml($xml, '//project:Details/project:Koop/project:Veiling/project:Datum');
	$metadata['huis_Servicekosten'] = retrieveStringFromXml($xml,'//project:Details/project:Koop/project:Servicekosten')?retrieveStringFromXml($xml,'//project:Details/project:Koop/project:Servicekosten'):retrieveStringFromXml($xml,'//project:Details/project:ZakelijkeLasten/project:BijdrageVve');
    
    // Huur prijs
	$metadata["huis_HuurPrijs"] = retrieveIntFromXml($xml, '//project:Details/project:Huur/project:Prijs');
    $huurPrijsConditie = retrieveStringFromXml($xml, '//project:Details/project:Huur/project:PrijsConditie');
	$metadata["huis_HuurPrijsConditie"] = str_replace(array('per maand', 'per jaar'), array('p.m.', 'p.j.'), $huurPrijsConditie);
    
    // Open House
    $metadata["huis_OpenHuisVan"] = retrieveStringFromXml($xml, '//project:Details/project:OpenHuis/project:Van');
    $metadata["huis_OpenHuisTot"] = retrieveStringFromXml($xml, '//project:Details/project:OpenHuis/project:Tot');
    
    // PerceelOppervlakte
    $metadata["huis_OppervlaktePerceel"] = retrieveIntFromXml($xml, '//project:KadastraleInformatie/project:PerceelOppervlakte');    
    
    // Diversen details
   	if( retrieveStringFromXml($xml,'//project:Details/project:Aanvaarding/project:Type') == 'per datum')
		$metadata['huis_Aanvaarding'] = 'per ' .retrieveStringFromXml($xml,'//project:Details/project:Aanvaarding/project:Datum');
    else
		$metadata['huis_Aanvaarding'] = retrieveStringFromXml($xml,'//project:Details/project:Aanvaarding/project:Type');
    $toelichting = retrieveStringFromXml($xml,'//project:Details/project:Aanvaarding/project:Toelichting');
	if($toelichting)
		$metadata['huis_Aanvaarding'] .= ', ' .$toelichting;
	
	$zrnodes = $xml->xpath('//project:Details/project:ZakelijkeRechten/project:ZakelijkRecht');
	$zakelijkerechten = '';
	if(is_array($zrnodes) && count($zrnodes)){
		foreach ($zrnodes as $recht) {
			if($zakelijkerechten)
				$zakelijkerechten.= ', ';
			$zakelijkerechten.= $recht['naam'];
		}
		$metadata['huis_ZakelijkeRechten'] = $zakelijkerechten;
	}
	
	$metadata['huis_Informatieplicht'] = retrieveStringFromXml($xml,'//project:Details/project:Informatie/project:Informatieplicht');
		
	// Lasten
	$metadata['huis_OzbGebruikersDeel'] = retrieveStringFromXml($xml,'//project:Details/project:ZakelijkeLasten/project:OzbGebruikersDeel');
	$metadata['huis_OzbZakelijkeDeel'] = retrieveStringFromXml($xml,'//project:Details/project:ZakelijkeLasten/project:OzbZakelijkeDeel');
	$metadata['huis_Waterschapslasten'] = retrieveStringFromXml($xml,'//project:Details/project:ZakelijkeLasten/project:WaterschapsLasten');
	$metadata['huis_Stookkosten'] = retrieveStringFromXml($xml,'//project:Details/project:ZakelijkeLasten/project:Stookkosten');
	$metadata['huis_RuilverkavelingsRente'] = retrieveStringFromXml($xml,'//project:Details/project:ZakelijkeLasten/project:RuilverkavelingsRente');
	$metadata['huis_Rioolrechten'] = retrieveStringFromXml($xml,'//project:Details/project:ZakelijkeLasten/project:Rioolrechten');
	$metadata['huis_Eigendomsoort'] = retrieveStringFromXml($xml,'//project:KadastraleInformatie/project:Eigendomsoort');
	
	$epnodes = retrieveStringFromXml($xml,'//project:KadastraleInformatie/project:Eigendom/project:ErfpachtPerJaar');
	$epnodes = explode(',',$epnodes);
	$erfpachtperjaar = 0;
	foreach ($epnodes as $bedrag) {
		$erfpachtperjaar+= $bedrag;
	}
	if($bedrag > 0)
		$metadata['huis_ErfpachtPerJaar'] = $erfpachtperjaar;

	$metadata['huis_ErfpachtDuur'] = retrieveStringFromXml($xml,'//project:KadastraleInformatie/project:Eigendom/project:ErfpachtDuur');
	if($metadata['huis_ErfpachtDuur'] != 'eeuwig')
		$metadata['huis_ErfpachtDuur'] .= ' ' .retrieveStringFromXml($xml,'//project:KadastraleInformatie/project:Eigendom/project:EindDatum');
	
	// Bestemming
	$metadata['huis_HuidigGebruik'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Bestemming/project:HuidigGebruik');
	$metadata['huis_HuidigeBestemming'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Bestemming/project:HuidigeBestemming');
	$metadata['huis_PermanenteBewoning'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Bestemming/project:PermanenteBewoning');
	$metadata['huis_Recreatiewoning'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Bestemming/project:Recreatiewoning');

	// Voorzieningen
	$voorzieningnodes = $xml->xpath('//project:Details/project:Woonruimte/project:Voorzieningen/project:Voorziening');
	$voorzieningen = array();
	if(is_array($voorzieningnodes) && count($voorzieningnodes)){
		foreach ($voorzieningnodes as $voorziening) {
			$voorzieningen[] = trim($voorziening['naam']);
		}
	}
	$voorzieningnodes = $xml->xpath('//project:Details/project:Woonruimte/project:Verdieping/project:Indelingen/project:Indeling');
	if(is_array($voorzieningnodes) && count($voorzieningnodes)){
		foreach ($voorzieningnodes as $voorziening) {
			$voorzieningen[] = trim($voorziening['naam']);
		}
	}
	$voorzieningen = array_unique($voorzieningen);
	if(is_array($voorzieningen) && count($voorzieningen))
		$metadata['huis_Voorzieningen'] = implode(', ',$voorzieningen);

	$metadata['huis_Verwarming'] = retrieveStringFromXml($xml,'//project:Detailsproject:/Woonruimte/project:Installatie/project:Verwarming/project:Type');
	$metadata['huis_WarmWater'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Installatie/project:WarmWater/project:Type');
	$metadata['huis_CvKetel'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Installatie/project:CvKetel/project:Type');
	$metadata['huis_CvKetelBouwjaar'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Installatie/project:CvKetel/project:Bouwjaar');
	// Isolatie
	$isolatienodes = $xml->xpath('//project:Details/project:Woonruimte/project:Diversen/project:Isolatievormen/project:Isolatie');
	$isolaties = array();
	if(is_array($isolatienodes) && count($isolatienodes))
		foreach ($isolatienodes as $isolatie) 
			$isolaties[] = trim($isolatie['naam']);
	if(is_array($isolaties) && count($isolaties))
		$metadata['huis_Isolatie'] = utf8_decode(implode(', ',$isolaties));
		
	// Dak
	$metadata['huis_Dak'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Diversen/project:Dak');
	$daknodes = $xml->xpath('//project:Details/project:Woonruimte/project:Diversen/project:DakMaterialen/project:DakMateriaal');
	$dakmaterialen = array();
	if(is_array($daknodes) && count($daknodes))
		foreach ($daknodes as $dakmateriaal) 
			$dakmaterialen[] = trim($dakmateriaal['naam']);
	if(is_array($dakmaterialen) && count($dakmaterialen))
		$metadata['huis_DakMaterialen'] = utf8_decode(implode(', ',$dakmaterialen));
	
	// Onderhoud
	$metadata['huis_OnderhoudBinnen'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Onderhoud/project:Binnen/project:Waardering');
	$metadata['huis_OnderhoudBuiten'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Onderhoud/project:Buiten/project:Waardering');
	$metadata['huis_OnderhoudSchilderwerkBinnen'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Onderhoud/project:SchilderwerkBinnen');
	$metadata['huis_OnderhoudSchilderwerkBuiten'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Onderhoud/project:SchilderwerkBuiten');
	$metadata['huis_OnderhoudPlafond'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Onderhoud/project:Plafond');
	$metadata['huis_OnderhoudMuren'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Onderhoud/project:Muren');
	$metadata['huis_OnderhoudVloer'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Onderhoud/project:Vloer');
	$metadata['huis_OnderhoudDak'] = retrieveStringFromXml($xml,'//project:Details/project:Woonruimte/project:Onderhoud/project:Dak');
		
	return $metadata;
}
// Mediafuncties
function processMediaItem($koppeling,$node,$postID,$type,$order)
{
	switch ($type){
		case 'Photo360':
			processPhoto360($koppeling,$node,$postID,$order);
			break;	
		case 'Document':
			processDocument($koppeling,$node,$postID,$order);
			break;	
		case 'Video':
			processVideo($koppeling,$node,$postID,$order);
			break;			
	}
}
function processImage($koppeling,$node,$postID,$order,$projectNode)
{ 
	$uuid = str_replace('urn:uuid:','',trim($node->id));
	$GLOBALS['verwerkteAfbeeldingen'][] = $uuid;

	// Zoek bestaande afbeelding
	global $wpdb;
	$query = "SELECT post_date FROM " .$wpdb->prefix ."posts WHERE post_content = '" .$uuid ."' LIMIT 1";
	$date = strtotime($wpdb->get_var($query));

	// Update niet nodig?
	if($date >= strtotime( trim($node->updated) ) )
		return;
	
	$date = strtotime( trim($node->updated) );	

	// Oude afbeelding verwijderen, ook rekening houden met dubbele entry's
	$query = "SELECT ID FROM " .$wpdb->prefix ."posts WHERE post_content = '" .$uuid ."'";
	$postids = $wpdb->get_col($query);
	foreach ($postids as $deleteid){
		// wat heeft de oude afbeelding voor metadata
		wp_delete_attachment( $deleteid ,true ); 
	}
	// Link naar afbeelding
	$url = str_replace('https://','',trim($node->link['href']));
	$url = 'https://' .get_option('yog_user') .':' .get_option('yog_password') .'@' .$url;
	
	// Kijk of we mogen schrijven op de server
	$basedir = str_replace('/wp-admin','',getcwd()) .'/wp-content/uploads/';
	if(!is_writeable($basedir))
		return;
	// Kijk of afbeeldingmap bestaat
	if(!is_dir($basedir .'projecten'))
		mkdir($basedir . 'projecten');
	// Kijk of postID map bestaat
	if(!is_dir($basedir .'projecten/' .$postID))
		mkdir($basedir . 'projecten/' .$postID);	
	// Afbeelding kopieren
	$afbeeldinglocatie = $basedir .'projecten/' .$postID .'/' .$uuid .'.jpg';
	copy($url,$afbeeldinglocatie);

	// Afbeelding opslaan als attachment
	$title = trim($node->title);
	$filetype = wp_check_filetype(basename($afbeeldinglocatie),null);
	$attachment =	array(
		'post_mime_type' => $filetype['type'],
		'post_date' => date('Y-m-d H:i:s', $date),
		'post_date_gmt' => date('Y-m-d H:i:s', $date),
		'post_title' => $title,
		'post_content' => $uuid,
		'post_status' => 'inherit',
		'menu_order' => $order
	);
	// Metadata
	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	$attachmentid = wp_insert_attachment( $attachment, $afbeeldinglocatie, $postID );

	$attachmentmeta = wp_generate_attachment_metadata( $attachmentid, $afbeeldinglocatie );
	wp_update_attachment_metadata($attachmentid, $attachmentmeta);	
	
}
function processPhoto360($koppeling,$node,$postID,$order)
{
	$rondkijken = get_post_meta($postID, 'huis_Fotos360', true);
	// Zeker weten dat de array al eens geinitialiseerd is
	if(!is_array($rondkijken)){
		unset($rondkijken);
		$rondkijken = array();
	}
	$rondkijken[trim($node['uuid'])] = array(
		'uuid'=>trim($node['uuid']),
		'order'=>$order,
		'title'=>trim($node->Photo360->Title),
		'ipixurl'=>trim($node->Photo360->IpixUrl)
	);
	// Terugschrijven
	update_post_meta($postID, 'huis_Fotos360',$rondkijken);	
}
function processVideo($koppeling,$node,$postID,$order)
{
	$videos = get_post_meta($postID, 'huis_Videos', true);
	// Zeker weten dat de array al eens geinitialiseerd is
	if(!is_array($videos)){
		unset($videos);
		$videos = array();
	}
	$videos[trim($node['uuid'])] = array(
		'uuid'=>trim($node['uuid']),
		'order'=>$order,
		'videostreamurl'=>trim($node->VideoStreamUrl),
		'title'=>trim($node->Video->Title),
		'popupurl'=>trim($node->Video->PopupUrl),
		'websiteurl'=>trim($node->Video->WebsiteUrl),
		'videoereference_serviceuri'=>trim($node->Video->VideoReference->ServiceUri),
		'videoereference_id'=>trim($node->Video->VideoReference->Id)
	);	
	// Terugschrijven
	update_post_meta($postID, 'huis_Videos',$videos);
}
function processDocument($koppeling,$node,$postID,$order)
{
	$documenten = get_post_meta($postID, 'huis_Documenten', true);
	// Zeker weten dat de array al eens geinitialiseerd is
	if(!is_array($documenten)){
		unset($documenten);
		$documenten = array();
	}
	$documenten[trim($node['uuid'])] = array(
		'uuid'=>trim($node['uuid']),
		'order'=>$order,
		'title'=>trim($node->Document->Title),
		'type'=>trim($node->Document->Type),
		'sourceurl'=>trim($node->Document->SourceUrl)
	);
	// Terugschrijven
	update_post_meta($postID, 'huis_Documenten',$documenten);	
}
// Linksfuncties
function processLink($koppeling,$node,$postID,$order)
{
	$links = get_post_meta($postID, 'huis_Links', true);
	// Zeker weten dat de array al eens geinitialiseerd is
	if(!is_array($links)){
		unset($links);
		$links = array();
	}
	$links[trim($node['uuid'])] = array(
		'uuid'=>trim($node['uuid']),
		'order'=>$order,
		'title'=>trim($node->Title),
		'type'=>trim($node->Type),
		'url'=>trim($node->Url)
	);
	// Terugschrijven
	update_post_meta($postID, 'huis_Links',$links);	
}
// Categorieen
function addCategories($postID,$metadata)
{
	$categorieen = array();
	if(in_array(strtolower($metadata['huis_scenario']),array('bbvk','bbvh','nbvk') )){
		$categorieen[] = 'consument';
		if(in_array(strtolower($metadata['huis_scenario']),array('bbvk','bbvh') ))
			$categorieen[] = 'bestaand';
		elseif(in_array(strtolower($metadata['huis_scenario']),array('nbvk') ))
			$categorieen[] = 'nieuwbouw';
		// Woonruimte
		if(in_array(strtolower($metadata['huis_Type']),array('woonruimte','woonhuis','appartement') )){
			$categorieen[] = 'woonruimte';
			if(in_array(strtolower($metadata['huis_Type']),array('appartement') ))
				$categorieen[] = 'appartement';
 			elseif(in_array(strtolower($metadata['huis_Type']),array('woonhuis') ))
 				$categorieen[] = 'woonhuis';
			// Open huis?
			if($metadata['huis_OpenHuisTot'] && strtotime($metadata['huis_OpenHuisTot']) < time())
				$categorieen[] = 'open-huis';
			else if($metadata['huis_OpenHuisVan'] && strtotime($metadata['huis_OpenHuisVan']) > time())
				$categorieen[] = 'open-huis'; 
		}
		// Bouwgrond
		if(in_array(strtolower($metadata['huis_Type']),array('bouwgrond') ))
			$categorieen[] = 'bouwgrond';
		// Parkeergelegenheid
		if(in_array(strtolower($metadata['huis_Type']),array('parkeergelegenheid') ))
			$categorieen[] = 'parkeergelegenheid';
		// Berging
		if(in_array(strtolower($metadata['huis_Type']),array('berging') ))
			$categorieen[] = 'berging';
		// Standplaats
		if(in_array(strtolower($metadata['huis_Type']),array('standplaats') ))
			$categorieen[] = 'standplaats';
		// Ligplaats
		if(in_array(strtolower($metadata['huis_Type']),array('ligplaats') ))
			$categorieen[] = 'ligplaats';	
		
		// Verkoop
		if($metadata['huis_KoopPrijs'])
			$categorieen[] = 'verkoop';	
		// Verhuur
		if($metadata['huis_HuurPrijs'])
			$categorieen[] = 'verhuur';	
		// Verkocht/verhuurd
		if(in_array(strtolower($metadata['huis_Status']),array('verkocht','verhuurd')))	
			$categorieen[] = 'verkochtverhuurd';					
		
	}
	wp_set_object_terms( $postID, $categorieen, 'category', false );
	//exit();
	
	
	
}
// Relations
function processRelation($koppeling,$href,$postID,$rol)
{
	$url = str_replace('https://','',$href);
	$url = 'https://' .get_option('yog_user') .':' .get_option('yog_password') .'@' .$url;
	$file = getXML($url);

	$relatieXML = new SimpleXMLElement($file);
  	$relatieXML->registerXPathNamespace('relation', 'http://webservice.yesco.nl/mcp/1.3/Relation');
  	if(nodeExists($relatieXML,'//relation:Business'))
  		processBusiness($koppeling,$relatieXML,$postID,$rol);
  	else if(nodeExists($relatieXML,'//relation:Person'))
  		processPerson($koppeling,$relatieXML,$postID,$rol);
}
function processPerson($koppeling,$relatieXML,$postID,$rol)
{
	$uuid = trim($relatieXML['uuid']);
	$GLOBALS['verwerkteRelaties'][] = $uuid;
	global $wpdb;
	// Zoek bestaande relatie
	$table_name = $wpdb->prefix .'postmeta';
	$relatieID = $wpdb->get_var("SELECT post_id as post FROM " .$table_name ." WHERE meta_key = 'relatie_uuid' AND meta_value = '" .$uuid ."'");
	$nieuwerelatie = false;
	if(!$relatieID)
		$nieuwerelatie = true;	
		
	$post = array();
	$post['post_title'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:Name/relation:Firstname') .' ' .retrieveStringFromXml($relatieXML, '//relation:Person/relation:Name/relation:LastnamePrefix') .' ' .retrieveStringFromXml($relatieXML, '//relation:Person/relation:Name/relation:Lastname') ;
	$post['post_status'] = 'publish';
	$post['post_author'] = 1;
	$post['menu_order'] = 0;
	$post['comment_status'] = 'closed';
	$post['ping_status'] = 'closed';
	$post['post_author'] = $user_id;
	$post['post_date'] = $updated;
	$post['post_date_gmt'] = $updated;
	$post['post_parent'] = 0;
	$post['post_type'] = 'relatie';
	
	$metadata = array();
	// Person
  	$metadata['relatie_koppelinguuid'] = $koppeling['UUID'];
	$metadata['relatie_type'] = 'Person';
  	$metadata['relatie_uuid'] = $uuid;
  	$metadata['relatie_Titel'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:Name/relation:Title');
  	$metadata['relatie_Initialen'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:Name/relation:Initials');
  	$metadata['relatie_Voornaam'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:Name/relation:Firstname');
  	$metadata['relatie_Voornamen'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:Name/relation:Firstnames');
  	$metadata['relatie_Tussenvoegsel'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:Name/relation:LastnamePrefix');
  	$metadata['relatie_Achternaam'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:Name/relation:Lastname');
  	$metadata['relatie_Adres'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:MainAddress');
  	$metadata['relatie_Postadres'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:PostalAddress');
  	$metadata['relatie_Emailadres'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:EmailAddress');
  	$metadata['relatie_Telefoonnummer'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:PhoneNR');
  	$metadata['relatie_Telefoonnummerwerk'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:WorkPhoneNR');
  	$metadata['relatie_Telefoonnummermobiel'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:MobilePhoneNR');
  	$metadata['relatie_Faxnummer'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:FaxNR');
  	$metadata['relatie_Functie'] = retrieveStringFromXml($relatieXML, '//relation:Person/relation:Position');
  	
  	// Sla op in content tabel
	if($nieuwerelatie){
		$relatieID = wp_insert_post( $post );	
		add_post_meta($relatieID, 'relatie_uuid', $uuid);
	}else{
		$post['ID'] = $relatieID;
		wp_update_post( $post );
	}

	// metadata aan post koppelen
	if(count($metadata)){
		foreach ($metadata as $key=>$val){
			if($nieuwerelatie)
				add_post_meta($relatieID,$key,$val);
			else
				update_post_meta($relatieID,$key,$val);
		}
	}
	
	$relaties = get_post_meta($postID,'huis_Relaties',1);	
	$persons = $relaties['Persons'];
	$persons[$rol] = $relatieID;
	$relaties['Persons'] = $persons;
	// koppel relatie aan huis
	update_post_meta($postID,'huis_Relaties',$relaties);
			
}
function processBusiness($koppeling,$relatieXML,$postID,$rol)
{
	$uuid = trim($relatieXML['uuid']);
	$GLOBALS['verwerkteRelaties'][] = $uuid;
	global $wpdb;
	// Zoek bestaande relatie
	$table_name = $wpdb->prefix .'postmeta';
	$relatieID = $wpdb->get_var("SELECT post_id as post FROM " .$table_name ." WHERE meta_key = 'relatie_uuid' AND meta_value = '" .$uuid ."'");
	$nieuwerelatie = false;
	if(!$relatieID)
		$nieuwerelatie = true;

	$post = array();
	$post['post_title'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:Name');
	$post['post_status'] = 'publish';
	$post['post_author'] = 1;
	$post['menu_order'] = 0;
	$post['comment_status'] = 'closed';
	$post['ping_status'] = 'closed';
	$post['post_author'] = $user_id;
	$post['post_date'] = $updated;
	$post['post_date_gmt'] = $updated;
	$post['post_parent'] = 0;
	$post['post_type'] = 'relatie';
	$metadata = array();
	// Business
	$metadata['relatie_koppelinguuid'] = $koppeling['UUID'];
  	$metadata['relatie_type'] = 'Business';
	$metadata['relatie_uuid'] = $uuid;
  	$metadata['relatie_Emailadres'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:EmailAddress');
  	$metadata['relatie_Website'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:WebsiteURL');
  	$metadata['relatie_Telefoonnummer'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:PhoneNR');
  	$metadata['relatie_Faxnummer'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:FaxNR');
  	// MainAddress
  	$mainAdress = $relatieXML->xpath('//relation:Business/relation:MainAddress');
  	if ($mainAdress !== false && count($mainAdress) > 0){
      	$metadata['relatie_Hoofdadres_land'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:MainAddress/relation:Country');
      	$metadata['relatie_Hoofdadres_provincie'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:MainAddress/relation:State');
      	$metadata['relatie_Hoofdadres_gemeente'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:MainAddress/relation:Municipality');
      	$metadata['relatie_Hoofdadres_stad'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:MainAddress/relation:City');
      	$metadata['relatie_Hoofdadres_wijk'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:MainAddress/relation:Area');
      	$metadata['relatie_Hoofdadres_buurt'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:MainAddress/relation:Neighbourhood');
      	$metadata['relatie_Hoofdadres_straat'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:MainAddress/relation:Street');
      	$metadata['relatie_Hoofdadres_huisnummer'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:MainAddress/relation:Housenumber');
      	$metadata['relatie_Hoofdadres_postcode'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:MainAddress/relation:Zipcode');
  	}
  	// PostalAddress
  	$postalAdress = $relatieXML->xpath('//relation:Business/relation:PostalAddress');
  	if ($postalAdress !== false && count($postalAdress) > 0){
      	$metadata['relatie_Postadres_land'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:PostalAddress/relation:Country');
      	$metadata['relatie_Postadres_provincie'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:PostalAddress/relation:State');
      	$metadata['relatie_Postadres_gemeente'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:PostalAddress/relation:Municipality');
      	$metadata['relatie_Postadres_stad'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:PostalAddress/relation:City');
      	$metadata['relatie_Postadres_wijk'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:PostalAddress/relation:Area');
      	$metadata['relatie_Postadres_buurt'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:PostalAddress/relation:Neighbourhood');
      	$metadata['relatie_Postadres_straat'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:PostalAddress/relation:Street');
      	$metadata['relatie_Postadres_huisnummer'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:PostalAddress/relation:Housenumber');
      	$metadata['relatie_Postadres_postcode'] = retrieveStringFromXml($relatieXML, '//relation:Business/relation:PostalAddress/relation:Zipcode');
  	}
  	
  	// Sla op in content tabel
	if($nieuwerelatie){
		$relatieID = wp_insert_post( $post );	
		add_post_meta($relatieID, 'relatie_uuid', $uuid);
	}else{
		$post['ID'] = $relatieID;
		wp_update_post( $post );
	}

	// metadata aan post koppelen
	if(count($metadata)){
		foreach ($metadata as $key=>$val){
			if($nieuwerelatie)
				add_post_meta($relatieID,$key,$val);
			else
				update_post_meta($relatieID,$key,$val);
		}
	}
	
	$relaties = get_post_meta($postID,'huis_Relaties',1);	
	$businesses = $relaties['Businesses'];
	$businesses[$rol] = $relatieID;
	$relaties['Businesses'] = $businesses;
	// koppel relatie aan huis
	update_post_meta($postID,'huis_Relaties',$relaties);
}

// Hulpfuncties
function retrieveStringFromXml($xml, $xpath)
{
$nodes  = $xml->xpath($xpath);
$values = array();

if ($nodes !== false && count($nodes) > 0)
{
  foreach ($nodes as $node)
  {
    $values[] = (string) $node;
  }
}

if (empty($values))
  return '';
else
  return utf8_decode(implode(', ', $values));
}
function retrieveIntFromXml($xml, $xpath)
{
$nodes  = $xml->xpath($xpath);
$value  = 0;

if ($nodes !== false && count($nodes) > 0)
{
  foreach ($nodes as $node)
  {
    $value += (int) $node;
  }
}

if (empty($value))
  return '';
else
  return $value;
}
function nodeExists($xml,$xpath)
{
	$nodes  = $xml->xpath($xpath);
$value  = 0;

return ($nodes !== false && count($nodes) > 0);
}
?>
