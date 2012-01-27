/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions Address				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.4
 * @versionDate:	2010-03-07
 * @date:     		2010-03-07
 */

dojo.provide('svzsolutions.maps.googlemaps.Address');

/**
 * SVZ Loader class
 * 
 */ 
dojo.declare('svzsolutions.maps.googlemaps.Address', null, 
{			

	/**
   * Constructor
   * 
   * @param string address
   * @return object
   */
	constructor: function(address)
  {			
		this._address 	= address;
		this._geocoder 	= new google.maps.Geocoder();
  }, 
  
  /**
   * Tries to find a geocode with the provided address
   *
   * @param void
   * @return void
   */
  findGeocode: function()
  {
  	var geocoderRequest = { 'address': this._address };
  	
  	this._geocoder.geocode(geocoderRequest, dojo.hitch(this, 'onGeocodeResult'));
  },
  
  /**
   * Methid is fired whenever the find geocode has some results, needs to be overriden 
   *
   * @param object results
   * @param string status
   * @return void
   */
  onGeocodeResult: function(results, status)
  {
  }

});