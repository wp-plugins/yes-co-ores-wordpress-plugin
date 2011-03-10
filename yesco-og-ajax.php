<?php

	// Registratie functies bij request
	add_action('wp_ajax_ak_attach', 'ajaxResponse');
	add_action('wp_ajax_testmcp', 'testMCP');
	add_action('wp_ajax_removeuuid', 'removeUUID');
	add_action('wp_ajax_forcesync', 'forceSync');
	add_action('wp_ajax_addkoppeling', 'addKoppeling');
	add_action('wp_ajax_togglehome', 'toggleHome');
	add_action('wp_ajax_removekoppeling', 'removeKoppeling');
	add_action('wp_ajax_removelink', 'removeLink');
	add_action('wp_ajax_removedocument', 'removeDocument');
	add_action('wp_ajax_removevideo', 'removeVideo');
	add_action('wp_ajax_addlink', 'addLink');
	add_action('wp_ajax_adddocument', 'addDocument');
	add_action('wp_ajax_addvideo', 'addVideo');
	// Ajax Request handlers
	function ajaxResponse(){
		global $wpdb; 	
		global $userdata;
		get_currentuserinfo();
		echo "Hello ". $userdata->user_login;
		exit;
	}
	
	function forceSync()
	{
		
		$args = array('action'=>'sync_yesco_og');
		ksort($args);
		$payload = '';
		foreach ($args as $key => $value)
		    if ($key != 'signature') 
		        $payload .= $key . '=' . $value;

		$signature = md5($payload .get_option('yog_secret'));
		$answer = parseJSON('http://' .$_SERVER['SERVER_NAME'] .'?action=sync_yesco_og&signature=' .$signature);
		if($answer){
			if($answer->error != '')
				echo 'Er is een fout opgetreden: ' .$answer->error;
			else
				echo $answer->message;
		}else{
			echo 'Er is een fout opgetreden:: systeemfout, kon synchronisatiescript niet aanroepen';
		}
		
		exit();
		//include('yesco-og-sync.php');
		//exit();
	}
	
	function testMCP()
	{
		$url = str_replace('https://','',addslashes($_POST['url']));
		$url = 'https://' .$_POST['user'] .':' .$_POST['password'] .'@' .$url;
				
		$xml=getXML($url);
		if($xml){
			echo 'verbinden gelukt';
		}else{
			echo 'verbinden mislukt';
		}
		exit();
	}
	
	function toggleHome()
	{
		update_option('yog_huizenophome',!(get_option('yog_huizenophome')));
		echo '&nbsp; instelling opgeslagen.';
		exit();
	}
	
	function addKoppeling()
	{
		// geen activatiecode? Geen koppeling toevoegen
		if(!$_POST['activatiecode'])
			exit();	
			
		$koppeling = array();
		$koppeling['naam'] = 'Nog niet bekend, wacht op activatie';
		$koppeling['status'] = 'Nog niet geactiveerd';
		$koppeling['activatiecode'] = $_POST['activatiecode'];
		$koppeling['UUID'] = '-';

		$koppelingen = get_option('yog_koppelingen');
		$koppelingen[] = $koppeling;
		update_option('yog_koppelingen',$koppelingen);
		
		echo '<div style="border: 1px solid #BBB; border-radius: 6px; padding: 3px; margin-top: 15px; width: 440px; overflow: auto;">';
			echo '<img src="' .$GLOBALS['yesco_og']['imagepath'] .'yes-co.png" style="float: left;">';
			$verwijderenlink = '<a style="cursor: pointer;" onclick="jQuery(this).next().show(); jQuery(this).hide();">Koppeling verwijderen</a><span style="display: none;">Wilt u deze koppeling verbreken? <a onclick="jQuery(this).parent().hide();jQuery(this).parent().prev().show();" style="cursor: pointer;">annuleren</a> | <a onclick="verwijderKoppeling(\'' .$koppeling['activatiecode'] .'\',jQuery(this));" style="cursor: pointer;">doorgaan</a></span>';
			echo '<span style="float: left; margin-left: 10px;"><b>Naam:</b> ' .$koppeling['naam'] .'<br><b>Status:</b> ' .$koppeling['status'] .'<br><b>Activatiecode:</b> ' .$koppeling['activatiecode'] .' <br>' .$verwijderenlink .'</span>';
		echo '</div>';
		exit();
	}
	
	function removeKoppeling()
	{
		// geen activatiecode? Geen koppeling toevoegen
		if(!$_POST['activatiecode'])
			exit();	

		$koppelingen = get_option('yog_koppelingen');
		$newkoppelingen;
		
		if(is_array($koppelingen)){
			foreach ($koppelingen as $koppeling){
				// alleen toevoegen als deze koppeling niet verwijderd moet worden
				if($koppeling['activatiecode'] != $_POST['activatiecode']){
					$newkoppelingen[] = $koppeling;
				}
			}
		}
		
		update_option('yog_koppelingen',$newkoppelingen);
		exit();
	}
	
	function removeLink()
	{
		$links = get_post_meta($_POST['postid'],'huis_Links',true);
		if(is_array($links) && count($links)){
			unset($links[$_POST['id']]);
			update_post_meta($_POST['postid'],'huis_Links',$links);
		}
		exit();
	}
	function addLink()
	{
		$uuid = 'zelftoegevoegd-' .time();
		$postID = $_POST['postid'];
		$titel = $_POST['titel'];
		$type = $_POST['type'];
		$url = 'http://' .$_POST['url'];
		
		$links = get_post_meta($postID,'huis_Links',true);
		if(!is_array($links))
			$links = array();
		$links[$uuid] = array('uuid'=>$uuid,'type'=>$type,'title'=>$titel,'url'=>$url);
		update_post_meta($postID,'huis_Links',$links);		
		echo $uuid;
		exit();
	}
	
	function removeDocument()
	{
		$links = get_post_meta($_POST['postid'],'huis_Documenten',true);
		if(is_array($links) && count($links)){
			unset($links[$_POST['id']]);
			update_post_meta($_POST['postid'],'huis_Documenten',$links);
		}
		exit();
	}
	function addDocument()
	{
		$uuid = 'zelftoegevoegd-' .time();
		$postID = $_POST['postid'];
		$titel = $_POST['titel'];
		$type = $_POST['type'];
		$url = 'http://' .$_POST['url'];		
		$documenten = get_post_meta($postID,'huis_Documenten',true);
		$order = 10;
		if(!is_array($documenten))
			$documenten = array();
		else{
			foreach ($documenten as $uuid=>$document) {
				if($document['order'] >= $order)
					$order = $document['order']+1;
			}
		}
		$documenten[$uuid] = array('uuid'=>$uuid,'type'=>$type,'title'=>$titel,'sourceurl'=>$url,'order'=>$order);
		update_post_meta($postID,'huis_Documenten',$documenten);		
		echo $uuid;
		exit();
	}
	
	function removeVideo()
	{
		$videos = get_post_meta($_POST['postid'],'huis_Videos',true);
		if(is_array($videos) && count($videos)){
			unset($videos[$_POST['id']]);
			update_post_meta($_POST['postid'],'huis_Videos',$videos);
		}
		exit();
	}
	function addVideo()
	{
		$uuid = 'zelftoegevoegd-' .time();
		$postID = $_POST['postid'];
		$titel = $_POST['titel'];
		$type = $_POST['type'];
		$url = 'http://' .$_POST['url'];		
		$videos = get_post_meta($postID,'huis_Videos',true);
		$order = 10;
		if(!is_array($videos))
			$videos = array();
		else{
			foreach ($videos as $videouuid=>$video) {
				if($video['order'] >= $order)
					$order = $video['order']+1;
			}
		}
		$videos[$uuid] = array('uuid'=>$uuid,'videoereference_serviceuri'=>$type,'title'=>$titel,'videoereference_id'=>$url,'order'=>$order);
		update_post_meta($postID,'huis_Videos',$videos);	
		echo $uuid;
		exit();
	}
	
	function removeUUID()
	{
		update_option('yog_uuid','');
		echo 'UUID verwijderd';
		exit();
	}

?>
