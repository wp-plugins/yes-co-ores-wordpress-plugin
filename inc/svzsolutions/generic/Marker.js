/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions Marker				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.6
 * @versionDate:	2010-07-25
 * @date:     		2010-07-25
 */

dojo.provide('svzsolutions.generic.Marker');

/**
 * SVZ Marker class
 * 
 */ 
dojo.declare('svzsolutions.generic.Marker', null, 
{			
	
	/**
   * Constructor
   * 
   * @param object config
   * @param object map
   * @return object
   */
	constructor: function(config, map)
  {					
		this._config	= config;
		this._map 		= map;
  },  

  /**
   * Method _getConfig which returns an object
   * 
   * @param string libraryType
   * @return object
   */
	_getConfig: function(libraryType)
	{
  	if (libraryType == 'googlemaps')
  	{
	  	var point						= new google.maps.LatLng(parseFloat(this._config.geoLat), parseFloat(this._config.geoLng));  	
	  	
	  	var config 					= {};  	
	  	config.map 					= this._map;
			config.position 		= point;
			config.title				= this._config.title;
	    config.draggable		= this._config.draggable;
	    config.dataLoadUrl 	= this._config.dataLoadUrl;
	  	
	    return config;
  	}
	},
	
	/**
   * Method getConfig which returns an object
   * 
   * @param string libraryType
   * @return object
   */
	getConfig: function(libraryType)
	{
		return this._getConfig(libraryType);
	},
	
	/**
   * Method getEntityId which returns an the entityId attached to this marker
   * 
   * @param void
   * @return mixed
   */
	getEntityId: function()
	{			
		return this._config.entityId;
	}

});