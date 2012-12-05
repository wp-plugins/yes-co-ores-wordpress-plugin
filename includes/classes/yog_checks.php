<?php
class YogChecks
{
	/**
	 * Check for errors
	 * 
	 * @param void
	 * @return array
	 */
	public static function checkForErrors()
	{
		$errors   = array();
		
		// Upload folder writable
		$uploadDir = wp_upload_dir();
		
		if (!empty($uploadDir['error']))
			$errors[] = $uploadDir['error'];
		else if (!is_writeable($uploadDir['basedir']))
			$errors[] = 'De upload map van uw WordPress installatie is beveiligd tegen schrijven. Dat betekent dat er geen afbeelingen van de objecten gesynchroniseerd kunnen worden. Stel onderstaande locatie zo in, dat deze beschreven kan worden door de webserver. <br /><i><b>' . $uploadDir['basedir'] .'</b></i>';
		
		// PHP version check
		if (!version_compare(PHP_VERSION, '5.2.1', '>='))
			$errors[] = 'PHP versie ' . PHP_VERSION . ' is gedetecteerd, de plugin vereist minimaal PHP versie 5.2.1. Neem contact op met je hosting provider om de PHP versie te laten upgraden';
		
		// Lib XML check
		if (!extension_loaded('libxml'))
			$errors[] = 'De php librairy <b>libxml</b> is niet geinstalleerd. Neem contact op met je hosting provider om libxml te laten installeren';
		
		// allow_url_fopen / CURL check
		if (!ini_get('allow_url_fopen') && !function_exists('curl_init'))
			$errors[] = 'De php setting <b>allow_url_fopen</b> staat uit en de php librairy <b>CURL</b> is niet geinstalleerd. Voor de synchronisatie is 1 van deze 2 noodzakelijk. Neem contact op met je hosting provider hierover.';
	
		// Wordpress version
		global $wp_version;
		if ((float) $wp_version < 3.0)
			$errors[] = 'Wordpress versie ' . $wp_version . ' is gedetecteerd, voor deze plugin is Wordpress versie 3.0 of hoger vereist. Upgrade wordpress naar een nieuwere versie';
		
		return $errors;
	}

	/**
	 * Check for warnings
	 * 
	 * @param void
	 * @return array
	 */
	public static function checkForWarnings()
	{
		$warnings = array();
		
		// Single huis template check
		if (!is_file(get_template_directory() .'/single-huis.php'))
			$warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-huis.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de Wonen object details.';
		
		// Single bedrijf template check
		if (!is_file(get_template_directory() .'/single-bedrijf.php'))
			$warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-bedrijf.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de BOG object details.';
		
		// Single NBpr template check
		if (!is_file(get_template_directory() .'/single-yog-nbpr.php'))
			$warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-yog-nbpr.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de Nieuwbouw Project details.';
		
		// Single NBpr template check
		if (!is_file(get_template_directory() .'/single-yog-nbty.php'))
			$warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-yog-nbty.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de Nieuwbouw type details.';
		
		// Wordpress version
		global $wp_version;
		if ((float) $wp_version >= 3.0 && (float) $wp_version < 3.1)
			$warnings[] = 'Wordpress versie ' . $wp_version . ' is gedetecteerd, voor deze plugin raden we minimaal Wordpress versie 3.1 aan.';
		
		return $warnings;
	}
	
	/**
	 * Get wordpress settings
	 * 
	 * @Param void
	 * @return array
	 */
	public static function getSettings()
	{
		$settings = array();
		
		// Wordpress version
		global $wp_version;
		$settings['Wordpress version'] = (float) $wp_version;
		
		// Plugin version
		$settings['Plugin version'] = YOG_PLUGIN_VERSION;
		
		// PHP version
		$settings['PHP version'] = PHP_VERSION;
		
		// allow_url_fopen
		$settings['allow_url_fopen'] = (ini_get('allow_url_fopen')) ? 'enabled' : 'disabled';
		
		// CURL
		$settings['CURL'] = function_exists('curl_init') ? 'enabled' : 'disabled'; 
		
		return $settings;
	}
}
?>