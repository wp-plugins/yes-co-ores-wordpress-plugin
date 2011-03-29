<?php
	define("WRONG_SIGNATURE", 1);
	define("SYSTEM_ERROR", 2);
	
	// reply voorbereiden
	$reply = array();
	
	// Check of request getekend is met een geldige secret 
	$gesigneerdekoppeling = requestIsProperlySigned($secret);
	if(!is_array($gesigneerdekoppeling)){
		$reply['status'] = 'error';
		$reply['errorcode'] = WRONG_SIGNATURE;
		$reply['error'] = 'Signature does not match local signature, did you use the wrong secret?';
		returnJSON($reply);
	}

	// secret is ok, we kunnen de UUID toevoegen / wijzigen
	$koppelingen = get_option('yog_koppelingen');
	$newkoppelingen;
	if(is_array($koppelingen)){
		foreach ($koppelingen as $koppeling){
			if($koppeling['activatiecode'] == $gesigneerdekoppeling['activatiecode']){
				$koppeling['UUID'] = $_GET['uuid'];
				$koppeling['naam'] = 'Nog niet bekend, wacht op synchronisatie';
				$koppeling['status'] = 'Geactiveerd';
			}
			$newkoppelingen[] = $koppeling;
		}
	}
	update_option('yog_koppelingen',$newkoppelingen);
	
	$reply['status'] = 'ok';
	$reply['message'] = 'Plug-in activated';
	returnJSON($reply);

?>
