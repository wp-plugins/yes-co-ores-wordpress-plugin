/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions Geocode				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.1
 * @versionDate:	2010-01-21
 * @date:     		2010-01-21
 */

dojo.provide('svzsolutions.generic.Geocode');

/**
 * SVZ Loader class
 * 
 */ 
dojo.declare('svzsolutions.generic.Geocode', null, 
{			
	DMS_DEG_SIGN_UTF8: '\u00B0',
	DMS_MIN_SIGN_UTF8: '\u2032',
	DMS_SEC_SIGN_UTF8: '\u2033',
	
	/**
   * Constructor
   * 
   * @param float latitude
   * @param float longitude
   * @return object
   */
	constructor: function(latitude, longitude)
  {			
		this._latitude 	= latitude;
		this._longitude = longitude;			
  }, 
  
  /**
   * Check if the latitude and longitude are both valid
   * 
   * @param void
   * @return boolean
   */
  isValid: function()
  {
  	if (this.isValidLatitude() && this.isValidLongitude())
  		return true;
  	
  	return false;
  },
  
  /**
   * Check if the latitude is valid
   * 
   * @param void
   * @return boolean
   */
  isValidLatitude: function()
  {
  	if (isNaN(this._latitude))
  		return false;
  		
  	if (this._latitude >= -90 && this._latitude <= 90)
  		return true;
  	
  	return false;
  },
  
  /**
   * Check if the longitude is valid
   * 
   * @param void
   * @return boolean
   */
  isValidLongitude: function()
  {
  	if (isNaN(this._longitude))
  		return false;
  		
  	if (this._longitude >= -180 && this._longitude <= 180)
  		return true;
  	
  	return false;
  },
  
  /**
   * Converts a decimal value to a degree / minutes / seconds,
   * function inspired from http://andrew.hedges.name/experiments/convert_lat_long/
   *
   * @param float $decimal
   * @return array
   */
  toDMS: function(decimal)
  {
  	if (decimal == '')
  		return false;
  	
  	dms = new Array();
  	
    parts = decimal.toString().split('.');

    // First part is the degree
    dms['degree'] = parts[0];

    // Minutes
    dmsRemainder = ('0.' + parts[1]) * 60;
    dmsRemainderParts = dmsRemainder.toString().split('.');
    dms['minutes'] = dmsRemainderParts[0];

    // Seconds
    dmsRemainder = ('0.' + dmsRemainderParts[1]) * 60;
    dms['seconds'] = Math.round(dmsRemainder);
  	
  	return dms;
  },
  
  /**
   * Returns the DMS of the latitude
   *
   * @param string format
   * @return string
   */
  getLatitudeInDMS: function(format)
  {
  	if (!format)
  		format = '%deg%%deg-sign% %min%%min-sign% %sec%%sec-sign% %car-dir%';

  	var cardinalDirection = 'N';
  	
    if (this._latitude.toString().substr(0, 1) == '-')
      cardinalDirection = 'S';

    var dmsArray = this.toDMS(this._latitude);

    dms = format.replace('%deg%', dmsArray['degree']);
    dms = dms.replace('%deg-sign%', this.DMS_DEG_SIGN_UTF8);
    dms = dms.replace('%min%', dmsArray['minutes']);
    dms = dms.replace('%min-sign%', this.DMS_MIN_SIGN_UTF8);
    dms = dms.replace('%sec%', dmsArray['seconds']);
    dms = dms.replace('%sec-sign%', this.DMS_SEC_SIGN_UTF8);
    dms = dms.replace('%car-dir%', cardinalDirection);

    return dms;
  },

  /**
   * Returns the DMS of the latitude
   *
   * @param string format
   * @return string
   */
  getLongitudeInDMS: function(format)
  {
  	if (!format)
  		format = '%deg%%deg-sign% %min%%min-sign% %sec%%sec-sign% %car-dir%';

  	var cardinalDirection = 'E';
  	
    if (this._longitude.toString().substr(0, 1) == '-')
      cardinalDirection = 'W';

    var dmsArray = this.toDMS(this._longitude);

    dms = format.replace('%deg%', dmsArray['degree']);
    dms = dms.replace('%deg-sign%', this.DMS_DEG_SIGN_UTF8);
    dms = dms.replace('%min%', dmsArray['minutes']);
    dms = dms.replace('%min-sign%', this.DMS_MIN_SIGN_UTF8);
    dms = dms.replace('%sec%', dmsArray['seconds']);
    dms = dms.replace('%sec-sign%', this.DMS_SEC_SIGN_UTF8);
    dms = dms.replace('%car-dir%', cardinalDirection);

    return dms;
  },
  
  /**
   * Tries to find a address with the provided geocode
   * @TODO: place in a seperate object withing the googlemaps directory
   *
   * @param void
   * @return mixed
   */
  findAddress: function()
  {
  	if (!this._geocoder)
  		this._geocoder 	= new google.maps.Geocoder();
  	
  	var geocode = new google.maps.LatLng(this._latitude, this._longitude);
  	
  	var geocoderRequest = { 'latLng': geocode };
  	
  	this._geocoder.geocode(geocoderRequest, dojo.hitch(this, 'onAddressResult'));  	
  },
  
  /**
   * Tries to find addresses within a given bound
   * @TODO: place in a seperate object withing the googlemaps directory
   *
   * @param void
   * @return mixed
   */
  findAddresses: function()
  {
  	if (!this._geocoder)
  		this._geocoder 	= new google.maps.Geocoder();
  	
  	var geocode 				= new google.maps.LatLng(this._latitude, this._longitude);
  	var geocode2 				= new google.maps.LatLng(this._latitude + 10, this._longitude + 10);
  	
  	var bounds 					= new google.maps.LatLngBounds(geocode, geocode2);
  	
  	var geocoderRequest = { 'bounds': bounds };
  	
  	console.log('GeocoderRequest [', geocoderRequest, ']');
  	
  	this._geocoder.geocode(geocoderRequest, dojo.hitch(this, 'onAddressesResult'));  	
  },
  
  /**
   * Tries to find a address with the provided geocode
   *
   * @param object results
   * @param string status
   * @return mixed
   */
  onAddressResult: function(results, status)
  {
  	 	
  },
  
  /**
   * Tries to find addresses within a given bound
   *
   * @param object results
   * @param string status
   * @return mixed
   */
  onAddressesResult: function(results, status)
  {
  	 	
  }

});