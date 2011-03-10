/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details. 
 */
dojo.provide("svzsolutions.all");
if(!dojo._hasResource['svzsolutions.generic.Math']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.generic.Math'] = true;
/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions Math				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.8
 * @versionDate:	2010-09-25
 * @date:     		2010-09-25
 */

dojo.provide('svzsolutions.generic.Math');

/**
 * SVZ Loader class
 * 
 */ 
dojo.declare('svzsolutions.generic.Math', null, 
{	
	MILES_TO_KILOMETRES_EQUATION: 1.609344,
	
	/**
   * Constructor
   * 
   * @param void
   * @return object
   */
	constructor: function()
  {				
  }, 
  
  /**
   * Method that converts a value in metres to a value in kilometres
   *
   * @param float / int number
   * @param int numberOfDecimals
   * @return float / int kilometres
   */
  roundNumber: function(number, numberOfDecimals)
  {
  	if (isNaN(numberOfDecimals) || numberOfDecimals < 1)
  		numberOfDecimals = 0;
  
  	var equation 	= Math.pow(10, numberOfDecimals);
    var newNumber = Math.round(number * equation) / equation;

    return newNumber;
  },
  
  /**
   * Method that converts a value in metres to a value in kilometres
   *
   * @param float / int metres
   * @return float / int kilometres
   */
  metresToKilometres: function(metres)
  {
    var kilometres = metres / 1000;

    return kilometres;
  },
  
  /**
   * Method that converts a value in meters to a value in miles
   *
   * @param float / int meters
   * @return float / int miles
   */
  metresToMiles: function(metres)
  {
    var kilometres 	= this.metresToKilometres(metres);    
    var miles 			= this.kilometresToMiles(kilometres); 

    return miles;
  },

  /**
   * Method that converts a value in kilometres to a value in miles
   *
   * @param float / int kilometres
   * @return float / int miles
   */
  kilometresToMiles: function(kilometres)
  {
    var miles = kilometres / this.MILES_TO_KILOMETRES_EQUATION;

    return miles;
  },

  /**
   * Method that converts a value in miles to a value in kilometres
   *
   * @param float / int miles
   * @return float / int kilometres
   */
  milesToKilometres: function(miles)
  {
    var kilometres = miles * this.MILES_TO_KILOMETRES_EQUATION;

    return kilometres;
  }

});

}

if(!dojo._hasResource['svzsolutions.maps.MapManager']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.maps.MapManager'] = true;
/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions Map Manager				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.2
 * @versionDate:	2010-02-06
 * @date:     		2010-02-06
 */

dojo.provide('svzsolutions.maps.MapManager');

/**
 * SVZ Solutions Map Manager
 * 
 */ 
dojo.declare('svzsolutions.maps.MapManager', null, 
{  
	/**
   * Constructor
   * 
   * @param void
   * @return void
   */
	constructor: function()
	{		
		this._maps 												= [];		
		this._googleMapsLibraryLoaded 		= false;
		this._googleMapsLibraryRequested 	= false;
		this._googleMapsConfigs						= [];
		this._mapIndex										= -1;
				
		// Check if google maps is already loaded or not
		if (svzsolutions.global && svzsolutions.global.mapManager && google && google.maps)
			this._googleMapsLibraryLoaded = true;
		
		if (!svzsolutions.global)
			svzsolutions.global = new Object();
		
		svzsolutions.global.mapManager = this;		
		
		dojo.addOnUnload(dojo.hitch(this, this.destroy));			
	},
	
	/**
	 * Method getByIndex which returns the map specified by it's index
	 * 
	 * @param integer mapIndex
	 * @return object
	 */
	getByIndex: function(mapIndex)
	{
		if (mapIndex < 0)
		{
			console.error('MapManager getByIndex: No map index provided.');
			return false;
		}
		
		if (!this._maps[mapIndex])
		{
			console.error('MapManager getByIndex: Could not find a map with index: [', mapIndex, ']');
			return false;
		}
		
		return this._maps[mapIndex];			
	},
	
	/**
	 * Method: adds and initializes a map by a json encoded config string
	 * 
	 * @param string jsonString
	 * @return void
	 */
	initByConfig: function(jsonString)
	{
		if (!jsonString)
			return false;
				
		var config = dojo.fromJson(jsonString);				
		
		console.log('MapManager: Adding and initializing config: ', config);
		
		if (config.libraryConfig)
		{
			switch (config.libraryConfig.name)
			{
			  case 'googlemaps':			  				 
			  	
			  	this._mapIndex++;	
			  				  				  	
					var map 										= new svzsolutions.maps.googlemaps.Map(config);
					map.index										= this._mapIndex;
					this._maps[this._mapIndex] 	= map;
					
					//this._loadGoogleMapsLibrary();			  	
			  	
			  	return this._maps[this._mapIndex];
			  	
			  	break;
			}			
									
		}
		
		return false;
	},
	
	/**
	 * Method which destroys and cleanups a map by its index
	 * 
	 * @param integer mapIndex
	 * @return void
	 */
	destroyByIndex: function(mapIndex)
	{
		console.log('MapManager: Trying to destroy the map by mapIndex', mapIndex);
		
		var map = this.getByIndex(mapIndex);
		
		map.destroy();	
	},	
	
	/**
	 * Method destroy which will clean up and destroy all available map instances
	 * 
	 * @param void
	 * @return void
	 */
	destroy: function()
	{
		if (this._maps)
		{
			for (var i = 0; i < this._maps.length; i++)
			{
				this._maps[i].destroy();
			}					   
		}
		
		this._maps                        = null;
		this._googleMapsConfigs           = null;
		
		this._maps                        = [];    
    this._googleMapsConfigs           = [];
    this._mapIndex                    = -1;    
	},
	
	/**
	 * Method which will startup all the maps
	 * 
	 * @param void
	 * @return void
	 */
	startup: function()
	{
		for (var  i = 0; i < this._maps.length; i++)
		{
			var config = null; 
			
			if (config = this._maps[i].getConfig())
			{
				if (config.libraryConfig)
				{
					switch (config.libraryConfig.name)
					{
					  case 'googlemaps':
					  	
					  		this._loadGoogleMapsLibrary(config);
					  	
					  	break;
					}
				}
			}
		}
	},
	
	/**
	 * Method which loads the google maps library
	 * 
	 * @param void
	 * @return void
	 */
	_loadGoogleMapsLibrary: function(config)
	{
		if (!this._googleMapsLibraryLoaded)
			this._loadGoogleMaps(config);
		else
			this._loadGoogleBasedMaps(config);
		
	},
	
	/**
	 * Method which loads the google maps library
	 * 
	 * @param void
	 * @return void
	 */
	_loadGoogleMaps: function(config)
	{		
		this._googleMapsConfigs.push(config);
		
		if (!this._googleMapsLibraryRequested)
		{
			this._googleMapsLibraryRequested = true;
			
	    var script  = document.createElement("script");
	    script.src  = "http://maps.google.com/maps/api/js?sensor=false&callback=svzsolutions.global.mapManager._loadGoogleMapsCallback";
	    script.type = "text/javascript";
	    document.getElementsByTagName("head")[0].appendChild(script); 	   
		}
	},
	
	/**
	 * Method which is called by google when the map is loaded
	 * 
	 * @param void
	 * @return void
	 */
	_loadGoogleMapsCallback: function()
	{
		this._googleMapsLibraryLoaded = true;

		this._loadGoogleBasedMaps();		
	},
	
	/**
	 * Method _loadGoogleBasedMaps which loads all the google maps based maps
	 * 
	 * @param void
	 * @return void
	 */
	_loadGoogleBasedMaps: function()
	{
		if (this._maps)
		{
			for (var i = 0; i < this._maps.length; i++)
			{
				if (!this._maps[i].isLoaded())
					this._maps[i].load();
				
			}
		}
	}
		
});


}

if(!dojo._hasResource['svzsolutions.generic.Loader']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.generic.Loader'] = true;
/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions Loader				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.1
 * @versionDate:	2010-01-21
 * @date:     		2010-01-21
 */

dojo.provide('svzsolutions.generic.Loader');

/**
 * SVZ Loader class
 * 
 */ 
dojo.declare('svzsolutions.generic.Loader', null, 
{		
	LOADER_UNDERLAY_HOLDER_CLASS	: 'sg-loader-underlay-holder',
	LOADER_HOLDER_CLASS						: 'sg-loader-holder',
	LOADER_CANCEL_ELEM_CLASS			: 'sg-loader-cancel',
	DEFAULT_CANCEL_DELAY					: 3000,
	
	/**
   * Constructor
   * 
   * @param string type
   * @param string|DomNode refNode
   * @param string|Number position (optional)
   * @return object
   */
	constructor: function(type, refNode, position, config)
  {			
		if (!config)
			config = {};
		
		this._config = config;
	
		loaderUnderlayHolderClassName = this.LOADER_UNDERLAY_HOLDER_CLASS;
		loaderHolderClassName 				=	this.LOADER_HOLDER_CLASS;
		
		this._cancelDelayHandler			= null;
	
		if (type)
		{
			loaderUnderlayHolderClassName += ' sg-' + type + '-underlay-holder';
			loaderHolderClassName 				+= ' sg-' + type + '-holder';
		}
	
		this._underlayElem = dojo.create('div', { className: loaderUnderlayHolderClassName });
		this._elem				 = dojo.create('div', { className: loaderHolderClassName });

		if (refNode)
			this.placeAt(refNode, position);
			
  }, 
  
  /**
   * Private method _onCancel which is fired on cliking of the cancel element
   * 
   * @param object event
   * @return void
   */
  _onCancel: function(event)
  {
  	dojo.stopEvent(event);
  	
  	this.hide();
  	
  	this.onCancel(event);
  },
  
  /**
   * Method which places and shows the cancel element in the loader
   * 
   * @param void
   * @return void
   */
  _showCancelElem: function()
  {
  	if (this._cancelElem)
  	{
  		dojo.style(this._cancelElem, 'display', 'block');  			  	
  	}
  	else
  	{
  		var textCancel = 'Cancel';
  		
  		if (this._config.textCancel)
  			textCancel = this._config.textCancel;
  		
  		this._cancelElem	 = dojo.create('a', { className: this.LOADER_CANCEL_ELEM_CLASS, innerHTML: textCancel, href: '#' }, this._elem);
			this._cancelHandle = dojo.connect(this._cancelElem, 'onclick', this, '_onCancel');  		
  	}
  },
  
  /**
   * Method onCancel which is fired when the cancel element is clicked
   * 
   * @param object event
   * @return void
   */
  onCancel: function(event)
  {
  	// Overwritable
  },
  
  /**
   * Method show which whill show the loader element
   * 
   * @param void
   * @return void
   */
  show: function()
  {
  	dojo.style(this._elem, 'display', 'block');
  	dojo.style(this._underlayElem, 'display', 'block');
  	
  	var temp = dojo.hitch(this, function()
		{  	
			this._showCancelElem();
		});
  	
		if (this._config.showCancelDelay || this._config.showCancelDelay >= 0)
			this._cancelDelayHandler = window.setTimeout(temp, this._config.showCancelDelay);
		else
			this._cancelDelayHandler = window.setTimeout(temp, this.DEFAULT_CANCEL_DELAY);
  	
  },
  
  /**
   * Method hide which whill hide the loader element
   * 
   * @param void
   * @return void
   */
  hide: function()
  {
  	if (this._cancelDelayHandler)
  	{
  		window.clearTimeout(this._cancelDelayHandler);
  	}
  	
  	if (this._cancelElem)
  		dojo.style(this._cancelElem, 'display', 'none');
  	
  	dojo.style(this._elem, 'display', 'none');
  	dojo.style(this._underlayElem, 'display', 'none');
  },
  
  /**
   * Method placeAt which places the loader into the provided placeholder, matches dojo.place params
   * 
   * @param string|DomNode refNode
   * @param string|Number position (optional)
   */
  placeAt: function(refNode, position)
  {  	
  	dojo.place(this._underlayElem, refNode, position);
  	dojo.place(this._elem, refNode, position);  
  },
  
  /**
   * Method destroy which cleans up the loader
   * 
   * @param void
   * @return void
   */
  destroy: function()
  {  	
  	if (this._cancelDelayHandler)
  		window.clearTimeout(this._cancelDelayHandler);
  	
  	dojo.destroy(this._elem);
  	dojo.destroy(this._underlayElem);
  	
  	if (this._cancelHandle)
  		dojo.disconnect(this._cancelHandle);
  	
  	return true;
  }


});

}

if(!dojo._hasResource['svzsolutions.generic.RequestManager']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.generic.RequestManager'] = true;
/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions GoogleMaps				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.2
 * @versionDate:	2010-02-06
 * @date:     		2010-02-06
 */

dojo.provide('svzsolutions.generic.RequestManager');

/**
 * SVZ Request manager class
 * 
 */
dojo.declare('svzsolutions.generic.RequestManager', null,
{	
	/**
   * Constructor
   * 
   * @param void
   * @return object
   */
	constructor: function()
	{
		this._requests = [];
	},
	
	/**
   * Method get which does a xhrGet request or cancels a previous one
   * 
   * @param object xhrArgs
   * @param string name
   * @return void
   */
	get: function(xhrArgs, name)
	{
		if (!name || !xhrArgs)
			return;
		
		this.cancel(name);
		
		return (this._requests[name] = dojo.xhrGet(xhrArgs));
	},
	
	/**
	 * Method which cancels a request
	 * 
	 * @param string name
	 * @return void
	 */
	cancel: function(name)
	{
		if (!name)
			return;
		
		if (this._requests[name])
			this._requests[name].cancel();
			
	}
	
});

}

if(!dojo._hasResource['svzsolutions.generic.MarkerManager']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.generic.MarkerManager'] = true;
/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions MarkerManager				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.4
 * @versionDate:	2010-03-21
 * @date:     		2010-03-21
 */

dojo.provide('svzsolutions.generic.MarkerManager');

/**
 * SVZ Loader class
 * 
 */ 
dojo.declare('svzsolutions.generic.MarkerManager', null, 
{				
	/**
   * Constructor
   * 
   * @param void
   * @return object
   */
	constructor: function()
  {			
		this._fixedLayers 	= [];
		this._dynamicLayers = [];
  }, 
  
  /**
   * Method which adds a marker of a certain type onto the marker stack
   * 
   * @param object marker
   * @return void
   */
  add: function(marker)
  {
  	if (marker && marker.type && marker.type.layerName && marker.type.layerType)
  	{
  		var layerName = marker.type.layerName;
  		var layerType = marker.type.layerType;
  		
  		switch (layerType)
  		{
  			case 'fixed':
  				
	  				if (!this._fixedLayers[layerName])
	  	  			this._fixedLayers[layerName] = [];
	  	  		
	  	  		this._fixedLayers[layerName].push(marker);
  				
  				break;
  				
  			case 'dynamic':
  				
	  				if (!this._dynamicLayers[layerName])
	  	  			this._dynamicLayers[layerName] = [];
	  	  		
	  	  		this._dynamicLayers[layerName].push(marker);
  				
  				break;
  		}  		  		
  	}
  	else
  	{
  		console.error('MarkerManager: could not add marker to markerManager, no layerName or layerType provided.');
  	}  	
  },
  
  /**
   * Method that returns all the markers of a certain type
   * 
   * @param string type
   * @return void
   */
  getByType: function(type)  
  {
  	var markers = [];
  	
  	if (!type)
  		return markers;
  	
  	markers = this.getDynamicByType(type);
  	markers = dojo.mixin(this.getFixedByType(type));
  	
  	return markers;
  },
  
  /**
   * Method that returns all the markers in the dynamic layers
   * 
   * @param void
   * @return void
   */
  getDynamic: function()  
  {
  	var markers = [];
  	
  	for (var key in this._dynamicLayers)
  	{  		
  		for (var i = 0; i < this._dynamicLayers[key].length; i++)
  		{  			
  			var marker = this._dynamicLayers[key][i];
  			
  			markers.push(marker);  				
  		} 		  		
  	}
  	
  	return markers;
  },
  
  /**
   * Method that returns all the markers of a certain type within the dynamic layers
   * 
   * @param string type
   * @return void
   */
  getDynamicByType: function(type)  
  {
  	var markers = [];
  	
  	if (!type)
  		return markers;
  	
  	for (var key in this._dynamicLayers)
  	{  		
  		for (var i = 0; i < this._dynamicLayers[key].length; i++)
  		{  			
  			var marker = this._dynamicLayers[key][i];
  			
  			if (marker.typeName == type)
  				markers.push(marker);
  				
  		} 		  		
  	}
  	
  	return markers;
  },
  
  /**
   * Method that returns all the markers in the fixed layers
   * 
   * @param void
   * @return void
   */
  getFixed: function()  
  {
  	var markers = [];
  	
  	for (var key in this._fixedLayers)
  	{  		
  		for (var i = 0; i < this._fixedLayers[key].length; i++)
  		{  			
  			var marker = this._fixedLayers[key][i];
  			
  			markers.push(marker);  				
  		} 		  		
  	}
  	
  	return markers;
  },
  
  /**
   * Method that returns all the markers of a certain type within the fixed layers
   * 
   * @param string type
   * @return void
   */
  getFixedByType: function(type)  
  {
  	var markers = [];
  	
  	if (!type)
  		return markers;
  	
  	for (var key in this._fixedLayers)
  	{  		
  		for (var i = 0; i < this._fixedLayers[key].length; i++)
  		{  			
  			var marker = this._fixedLayers[key][i];
  			
  			if (marker.typeName == type)
  				markers.push(marker);
  				
  		} 		  		
  	}
  	
  	return markers;
  },
  
  /**
   * Method that clears all the markers residing in the dynamic layers
   * 
   * @param void
   * @return void
   */
  clearDynamicMarkersFromMap: function()
  {	
  	for (var key in this._dynamicLayers)
  	{
  		console.log('MarkerManager: current number of markers in layer [', key ,'] is [', this._dynamicLayers[key].length , ']');
  		
  		while (marker = this._dynamicLayers[key].pop())
  		{  			
  			marker.setMap(null);
  		} 		  		
  	}  	
  },
  
  /**
   * Method that clears all the markers residing in the fixed layers
   * 
   * @param void
   * @return void
   */
  clearFixedMarkersFromMap: function()
  {	
  	for (var key in this._fixedLayers)
  	{
  		console.log('MarkerManager: current number of markers in layer [', key ,'] is [', this._fixedLayers[key].length , ']');
  		
  		while (marker = this._fixedLayers[key].pop())
  		{  			
  			marker.setMap(null);
  		} 		  		
  	}  	
  }

});

}

if(!dojo._hasResource['svzsolutions.maps.googlemaps.Map']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.maps.googlemaps.Map'] = true;
/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions - GoogleMaps				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.1
 * @versionDate:	2009-10-17
 * @date:     		2009-10-17
 */


dojo.provide('svzsolutions.maps.googlemaps.Map');

/**
 * SVZ Solutions Google Maps Map class
 * 
 * @TODO:
 * - Need to find a way to render different areas for the clustering
 */ 
dojo.declare('svzsolutions.maps.googlemaps.Map', null, 
{    
	INFO_WINDOW_CONTENT_CLASS: 'info-window-content',
	
	/**
   * Constructor
   * 
   * @param array config
   * @return object
   */
  constructor: function(config)
  {        		
    // Init vars in this object only
		this._config						= config;
    this._map          			= null;    
    this._markerManager			= new svzsolutions.generic.MarkerManager();
    this._requestManager 		= new svzsolutions.generic.RequestManager();
    this._loadDataForm			= null;
    this._loader						= null;
    this._loaderInfoWindow 	= null; 
    this._loaded						= false;
  },
  
  /**
   * Method which sets the load data once params in the config
   * 
   * @param boolean loadDataOnce
   * @return void
   */
  setLoadDataOnce: function(loadDataOnce)
  {
  	if (loadDataOnce)
  		this._config.loadDataOnce = true;
  	else
  		this._config.loadDataOnce = false;
  	
  	this._dataLoaded					= false;  		
  },
  
  /**
   * Returns this maps config
   * 
   * @param void
   * @return object
   */
  getConfig: function()
  {
  	return this._config;
  },
  
	/**
   * Method that loads and shows the map
   * 
   * @param void
   * @return void
   */
  load: function()
  {         	
  	this._infoWindow     								= new google.maps.InfoWindow();
  	
  	var config 													= {};
  	config.center 											= new google.maps.LatLng(this._config.centerGeoLat, this._config.centerGeoLng);
  	config.zoom 												= this._config.zoomLevel;
  	config.mapTypeId 										= this._config.mapType;
  	config.mapTypeControl 							= this._config.mapTypeControl;
  
  	// Checking street view
  	if (this._config.enableStreetViewControl)
  	    config.streetViewControl = this._config.enableStreetViewControl; 
  	
  	if (this._config.mapTypeControlOptions)
  	{  		
  		var mapTypeControlOptions      		= {};
  		mapTypeControlOptions.position 		= dojo.getObject(this._config.mapTypeControlOptions.position);
  		mapTypeControlOptions.style				= dojo.getObject(this._config.mapTypeControlOptions.style);
  		
  		config.mapTypeControlOptions  		= mapTypeControlOptions;
  	}
  	
  	config.navigationControl 						= this._config.navigationControl;
  	
  	if (this._config.navigationControlOptions)
  	{
  		var navigationControlOptions      = {};
  		navigationControlOptions.position = dojo.getObject(this._config.navigationControlOptions.position);
  		navigationControlOptions.style		= dojo.getObject(this._config.navigationControlOptions.style);
  		
  		config.navigationControlOptions  	= navigationControlOptions;
  	}
  	
  	config.scaleControl 								= this._config.scaleControl;
  	
  	if (this._config.scaleControlOptions)
  	{
  		var scaleControlOptions      			= {};
  		scaleControlOptions.position 			= dojo.getObject(this._config.scaleControlOptions.position);
  		scaleControlOptions.style					= dojo.getObject(this._config.scaleControlOptions.style);
  		
  		config.scaleControlOptions  			= scaleControlOptions;
  	}  	
  	
  	console.log('Generated the following Google Maps config [', config, ']');
  	  	  	
    this._map 													= new google.maps.Map(dojo.byId(this._config.mapContainerId), config);        
       
    if (navigator.userAgent.indexOf('iPhone') != -1 || navigator.userAgent.indexOf('Android') != -1 ) 
    {
    	dojo.style(this.getMapContainer(), 'width', '100%');
    	dojo.style(this.getMapContainer(), 'height', '100%');
    }
    
    if (this._config.layers)
  	{
  		
  		for (var i = 0; i < this._config.layers.length; i++)
  		{  		
  			var layer = new google.maps.Layer(this._config.layers[i]);
  			this.getMap().addOverlay(layer);
  		}
  	}
    
    google.maps.event.addListener(this.getMap(), 'idle', dojo.hitch(this, this.onIdle));
    google.maps.event.addListener(this.getMap(), 'zoom_changed', dojo.hitch(this, this.onZoomLevelChanged));
    google.maps.event.addListener(this.getMap(), 'dragend', dojo.hitch(this, this.onDragend));
    google.maps.event.addListener(this.getMap(), 'center_changed', dojo.hitch(this, this.onCenterChanged));
    
    if (this._config.markers)
    {
    	var data = {};
    	data.markers = this._config.markers;
    	
    	this.processMarkers(data.markers);
    }       
    
    // Set the loaded flag
    this._loaded = true;
    
    this.onMapLoaded();    
  },
  
  /**
   * Method which checks if this map is loaded
   * 
   * @param void
   * @return boolean
   */
  isLoaded: function()
  {
  	return this._loaded;
  },
  
  /**
   * Method which is called when the map is initialized
   * Can be overridden or connected to
   * 
   * @param void
   * @return void
   */
  onMapLoaded: function()
  {  	
  	
  }, 
  
  /**
   * Method which is called when the center of the map has changed
   * 
   * @param void
   * @return void
   */
  onCenterChanged: function()
  {  	
  	
  },
  
  /**
   * Method which returns the current map instance
   * 
   * @param void
   * @return google.maps.Map
   */
  getMap: function()
  {
  	return this._map;
  },
  
  /**
   * Method which returns the current map instance container
   * 
   * @param void
   * @return HTMLDomElement
   */
  getMapContainer: function()
  {
  	return this.getMap().getDiv();
  },    
  
  /**
   * Method which is called when the map is in a idle state
   * 
   * @param object event
   * @return void
   */
  onIdle: function(event) 
  {
    this.loadData();      
  },    
  
  /**
   * Method which is called when the map changes its zoom level
   * 
   * @param object event
   * @return void
   */
  onZoomLevelChanged: function(event) 
  {
    this._infoWindow.close();
  },   
  
  /**
   * Method which is called when the map stopped being dragged
   * 
   * @param object event
   * @return void
   */
  onDragend: function(event) 
  {
    this._infoWindow.close();
  },    
  
  /**
   * Private method which is called when a draggable marker is stoped being moved on the screen
   * 
   * @param object event
   * @return void
   */
  _onMarkerDragEnd: function(event)
  {  	
  	this.instance.onMarkerDragEnd(this.marker);
  }, 
  
  /**
   * Method which is called when a draggable marker is stoped being moved on the screen
   * Can be overridden
   * 
   * @param object marker
   * @return void
   */
  onMarkerDragEnd: function(marker)
  {  	
  },
  
  /**
   * Method which is called when a marker on the map has been clicked.
   * 
   * @param object event
   * @return void
   */
  _onMarkerClick: function(event) 
  {  	  	
    var dataLoadUrl = this._infoWindow.dataLoadUrl;
        
    if (dataLoadUrl)
    {
	    var xhrArgs = 
	    {
        url: dataLoadUrl,
        handleAs: "json",
        load: dojo.hitch(this, '_loadInfoWindowCallback'),
        error: dojo.hitch(this, function(error) 
        {
	    		if (this._loaderInfoWindow)
	    		{
		    		this._loaderInfoWindow.destroy();
		    		this._loaderInfoWindow = null;
	    		}
	    	
    	  	if (error.dojoType == 'cancel')
    	  		return;    	  
    	  	
    	  	var messageHolder = dojo.create('div', { className: 'sg-message-holder sg-error' } );
    	  	var message				= dojo.create('p', {}, messageHolder);
    	  	message.innerHTML = 'An error occured trying to load the content, please try again.';
    	  	
    	  	this._infoWindow.setContent(messageHolder);
    	  
          console.error("MarkerClick: An unexpected error occurred: ", error);
        })
	    };		   	    
	
	    // Call the asynchronous xhrGet    
	    this._requestManager.get(xhrArgs, 'loadInfoWindowData');	    
    }  
  },
  
  /**
   * Method which is called when the ajax request has finished on opening a info window.
   * 
   * @param jsonObject data
   * @return void
   */
  _loadInfoWindowCallback: function(data) 
  {
  	console.log('InfoWindowCallback');
  	
  	if (this._loaderInfoWindow)
  	{
	  	this._loaderInfoWindow.destroy();
			this._loaderInfoWindow = null;
  	}  	  	
		
  	if (data && typeof(data) == 'object')
    {              		
      if (data.content)
      {	      	      	           	
      	// Temp: Create a dom element from the provided html string, no good public way available yet in dojo 1.3.2
      	// Follow: http://trac.dojotoolkit.org/ticket/8613
      	var domElem = dojo._toDom(data.content);
      	
      	this._infoWindow.setContent(domElem);
      	
      	// Workaround for the map not showing the info window in the middle when first setting the content
      	// Follow bug in topic I created: http://groups.google.com/group/google-maps-js-api-v3/browse_thread/thread/c3175c59c174f49f/e1dff9fc4453ef3d?lnk=gst&q=info+window+ajax#e1dff9fc4453ef3d
      	var timeoutHandler = dojo.hitch(this, function()
      	{
      		this._infoWindow.open(this.getMap(), this.markerClicked);
      		
      		var infoWindow = new svzsolutions.maps.googlemaps.InfoWindow(this._infoWindow.getContent());
      		infoWindow.setMarker(this._infoWindow._marker);
      		
      		this.onInfoWindowContentLoaded(infoWindow);
      		
      		dojo.connect(infoWindow, 'initCustomTabContent', this, function()
      				{
      					this.onInfoWindowTabContentLoaded(infoWindow);
      				});
      		
      	});
      	
      	setTimeout(timeoutHandler, 1);      	      	 
      }
    }  	  	
  },
  
  /**
   * Method onInfoWindowContentLoaded which is fired whenever a info windows is opened
   * 
   * @param object infoWindow
   * @return void
   */
  onInfoWindowContentLoaded: function(infoWindow)
  {
  	
  },
  
  /**
   * Method onInfoWindowTabContentLoaded which is fired whenever a info windows dynamic tab is opened
   * 
   * @param object infoWindow
   * @return void
   */
  onInfoWindowTabContentLoaded: function(infoWindow)
  {
  	
  },
  
  /**
   * Method which checks if the given marker is in the viewport
   * 
   * @param object marker
   */
  isMarkerInViewPort: function(marker)
  {
  	if (marker)
  	{
  		var position = marker.getPosition();
  		var bounds	 = this.getMap().getBounds();
  		
  		return bounds.contains(position);	
  	}
  	
  	return false;
  },
  
  /**
   * Method which returns the current zoom level
   * 
   * @param void
   * @return integer
   */
  getZoomLevel: function()
  {
  	return this.getMap().getZoom();
  },
  
  /**
   * Method that returns all the viewport information like the zoom level / sw and ne latitude longitude
   * 
   * @param void
   * @return array
   */
  getViewPortInfo: function()
  {
    // Get the lat / lon bounderies of the viewport
    var bounds = this.getMap().getBounds();    
    
    // South west coordinates of viewport
    var swLatLng = bounds.getSouthWest();
    
    // North east coordinates of viewport
    var neLatLng = bounds.getNorthEast();
    
    // Get the current zoom level
    var zoomLevel = this.getMap().getZoom();
    
    var ceLatLng = this.getMap().getCenter();    
    
    var returnObject = { 
    	zoom: zoomLevel, 
    	sw_lat: swLatLng.lat(), 
    	sw_lng: swLatLng.lng(), 
    	ne_lat: neLatLng.lat(), 
    	ne_lng: neLatLng.lng(),
    	ce_lat: ceLatLng.lat(),
    	ce_lng: ceLatLng.lng(),
    	w: this.getConfig().width,
    	h: this.getConfig().height
    };    
    
    return returnObject;
  },    
  
  /**
   * Method which will add a form to the query of loadData
   * 
   * @param object form
   * @return void
   */
  setLoadDataForm: function(form)
  {
  	this._loadDataForm = form;
  },
  
  /**
   * Method that loads all the markers on the map
   * 
   * @param void
   * @return void
   */
  loadData: function()
  {        
  	console.log('Map: loading data, with option loadDataOnce [', this._config.loadDataOnce, '] / dataLoaded [', this._dataLoaded ,'] and dataLoadUrl [', this._config.dataLoadUrl, ']');
  	
  	if (this._config.loadDataOnce && this._dataLoaded)
  		return;  		  	  	  	  	
  	
    var viewPortInfo = this.getViewPortInfo();   

    if (this._config.dataLoadUrl)
    {
	    var xhrArgs = 
	    {
	      url: this._config.dataLoadUrl,
	      failOk: true,
	      content: viewPortInfo,
	      form: this._loadDataForm,
	      handleAs: "json",
	      load: dojo.hitch(this, this.loadDataCallback),
	      error: function(error) 
	      {
	    		this._loader.hide();
	    	
	    	  if (error.dojoType == 'cancel')
	    	  	return;	    	 	    	  
	    	  
	    		// @TODO write something back to the user
	        console.log("An unexpected error occurred: " + error);
	      }
	    };
	    
	    if (!this._loader)
	    {	    	 
	    	// Get the right layer to put the loader in
	    	// @TODO: place this loader so it won't block the info window
	    	var mapPanes = this.getMap().getDiv().childNodes[0];
	    	
	    	this._loader 												= new svzsolutions.generic.Loader('load-data', mapPanes, 'first');
	      this._loader.onCancel 							= dojo.hitch(this, function(event)
	  	    {
	      		this._requestManager.cancel('loadData' + this._config.mapIndex);
	  	    });		
	    }	   	   
	    
	    this._loader.show();
	
	    // Call the asynchronous xhrGet    
	    this._requestManager.get(xhrArgs, 'loadData' + this._config.mapIndex);
    }
  },
  
  /**
   * Method processMarkers which will put the markers on the map
   * 
   * @param array markers
   * @return void
   */
  processMarkers: function(markers)
  {
  	for (var i = 0; i < markers.length; i++) 
    {              
    	var markerConfig					= markers[i];        

    	if (!this._config.markerTypes[markerConfig.type])
    	{
    		console.error('The marker type called "' + markerConfig.type + '" seems to be not registered.');
    		continue;
    	}
    	else
    	{
    		try
    		{        			
        	var markerType 						= this._config.markerTypes[markerConfig.type];		        	
        	markerConfig.markerType 	= markerType;
        	
        	var marker 								= false;
        	var config 								= false;
        			        	
        	if (markerConfig.type == 'cluster')
        	{
        		var newTempMarker = new svzsolutions.generic.MarkerCluster(markerConfig, this.getMap());		        		
        		config 						= newTempMarker.getConfig('googlemaps');
        		
        		marker						= new svzsolutions.maps.googlemaps.CustomOverlay(config);  
        	}
        	else if (markerConfig.type == 'list') 
        	{ 
        		var newTempMarker = new svzsolutions.generic.MarkerList(markerConfig, this.getMap());
        		config 						= newTempMarker.getConfig('googlemaps');
        		
        		marker						= new svzsolutions.maps.googlemaps.CustomOverlay(config);
        	}
        	else
        	{		        		
        		var newTempMarker = new svzsolutions.generic.Marker(markerConfig, this.getMap());
        		config 						= newTempMarker.getConfig('googlemaps');		        				        		
        		
        		// @TODO: Implement anchor / scaledsize / origin functionality
	          if (markerType.iconEnabled)
	          {				
	          	if (markerType.icon)
	          	{
		          	config.icon 				= new google.maps.MarkerImage(
		          			markerType.icon.url,
		          			new google.maps.Size(markerType.icon.size.width, markerType.icon.size.height)
		          	);
	          	}
	          	
	          	if (markerType.shadow)
	          	{			          		
	          		config.shadow	= new google.maps.MarkerImage(
	          				markerType.shadow.url,
	          				new google.maps.Size(markerType.shadow.size.width, markerType.shadow.size.height)
	          		);
	          	}
		          
		          marker 							= new google.maps.Marker(config);
	          }
	          else
	          {	
	          	config.className 		= markerType.className;
        			config.typeConfig   = markerType;
        			config.label				= markerConfig.label;
        		
        			marker 							= new svzsolutions.maps.googlemaps.CustomOverlay(config);		          			          		          	                  		         	         
	          }
        	} 

        	marker.type 				= markerType;
        	marker.typeName			= markerConfig.type;
        	
        	if (markerConfig.entityId)
        		marker.entityId = markerConfig.entityId;
        	
        	if (markerConfig.draggable)	    
        	{
        		var markerThis 			= {};
        		markerThis.instance = this;
        		markerThis.marker		= marker;
        		google.maps.event.addListener(marker, 'dragend', dojo.hitch(markerThis, this._onMarkerDragEnd));
        	}
        		       		        	
        	this.bindMarker(marker, markerConfig.content, config.dataLoadUrl, newTempMarker);

          // Push the marker onto the marker manager stack
          this._markerManager.add(marker);
    		}
    		catch (e)
    		{
    			console.error('SvzMaps: loading marker failed [', e, ']');
    		}
    	}
    }
  },
  
  /**
   * Method that executes when the request for data has been finished
   * 
   * @param jsonObject data
   * @return void
   */
  loadDataCallback: function(data)
  {    
  	console.log('Map: load DataCallback fired with data [', data, ']');
  	
  	if (this._loader)
    	this._loader.hide();  
  	
  	// Clear all the current dynamic markers
  	this._markerManager.clearDynamicMarkersFromMap();
    
  	if (!this._config.markerTypes)
  	{
  		console.error('No marker types seems to be registered.');
  		return;
  	}    	
  	
    this._dataLoaded = true; // Set flag for data being loaded already
  	
    if (data && typeof(data) == 'object')
    {               	
    	// Process any markers returned
      if (data.markers)
      	this.processMarkers(data.markers);      	        
    
    }   
    
    console.log('Map: finished processing of returned data.');      
  }, 
  
  /**
   * Method that binds events to the marker
   * 
   * @param object instance
   * @param object marker
   * @param string dataLoadUrl
   * @param object newTempMarker
   * @return void
   */
  bindMarker: function (marker, content, dataLoadUrl, newTempMarker) 
  {  	  	
  	if (marker.type.clickAction == 'zoom')
  	{
  		var onClickFunction = function()
  		{
  			var zoomLevel = 0; 
  			
  			if (marker._config.smartNavigation && marker._config.smartNavigation.zoomToLevel)
  				zoomLevel = marker._config.smartNavigation.zoomToLevel;
  			else
  				zoomLevel = this.getMap().getZoom() + 1;
			  
			  this.getMap().setCenter(marker._config.position);
  			this.getMap().setZoom(zoomLevel);
  		};
  		
  		if (marker.declaredClass && marker.declaredClass == 'svzsolutions.maps.googlemaps.CustomOverlay')
  			dojo.connect(marker, 'onClick', dojo.hitch(this, onClickFunction));
  		else
	  		google.maps.event.addListener(marker, 'click', dojo.hitch(this, onClickFunction));

  	}
  	else
  	{   		  	
  		var onClickFunction2 = function()
  		{  			
  			if (dataLoadUrl)
	    	{  		
		    	// Set the loader img
		  		var body      = dojo.create('div', { className: this.INFO_WINDOW_CONTENT_CLASS }); 		  			  		
			   
	    		this._infoWindow.setContent(body);
	    		this._infoWindow._marker = newTempMarker; 
	    		
	    		if (!this._loaderInfoWindow)
	  	    {
	    			this._loaderInfoWindow 							= new svzsolutions.generic.Loader('load-info-window-data', body, 'first');
	    			this._loaderInfoWindow.onCancel 		= dojo.hitch(this, function(event)
	  	  	    {
	    				this._requestManager.cancel('loadInfoWindowData');
	    				this._infoWindow.close();
	  	  	    });		
	  	    }	    			    			    		
	    			    						  						  		   
	    		this._infoWindow.dataLoadUrl 	= dataLoadUrl;	    		
	    		
	    		if (marker._googleOverlay)
	    		{
	    			this.markerClicked 						= marker._googleOverlay;
	    			this._infoWindow.open(this.getMap(), marker._googleOverlay);
	    		}
	    		else
	    		{
	    			this.markerClicked 						= marker;
	    			this._infoWindow.open(this.getMap(), marker);
	    		}
	    		
	    	}
	    	else if (content)
	    	{
	    		this._infoWindow.setContent(content);	    			    			    		
	    		this._infoWindow.open(this.getMap(), marker);
	    		
	    		var infoWindowContent = new svzsolutions.maps.googlemaps.InfoWindow(this._infoWindow.getContent());	 
	    		infoWindowContent.setMarker(newTempMarker);
	    	}	
  		};  	  		  		
  		
  		if (marker.declaredClass && marker.declaredClass == 'svzsolutions.maps.googlemaps.CustomOverlay')
  		{
  			dojo.connect(marker, 'onClick', dojo.hitch(this, onClickFunction2));
  			
  			if (dataLoadUrl)
  				dojo.connect(marker, 'onClick', this, '_onMarkerClick');
  			
  		}
  		else
  		{
  			google.maps.event.addListener(marker, 'click', dojo.hitch(this, onClickFunction2));	    			  			
  			
  			if (dataLoadUrl)  				
  				google.maps.event.addListener(marker, 'click', dojo.hitch(this, this._onMarkerClick));
  			
  		}
  	}
    
  }, 
  
  /**
   * Method that returns the marker manager
   * 
   * @param void
   * @return svzsolutions.generic.MarkerManager
   */
  getMarkerManager: function()
  {
  	return this._markerManager;
  },  
  
  /**
   * Method resize that fixes grey tiles when a map is rendered but not shown immediately
   * 
   * @param boolean preventRecenter
   * @return void
   */
  resize: function(preventRecenter)
  {
  	console.log('Map: firing resize ');
  	
  	google.maps.event.trigger(this.getMap(), 'resize');
  	
  	if (!preventRecenter)
  	{
  		var centerCoordinates = new google.maps.LatLng(this._config.centerGeoLat, this._config.centerGeoLng);  		
  		this.getMap().setCenter(centerCoordinates);
  	}
  		
  }, 
  
  /**
   * Method that cleanes up this map and removes it from the dom
   * 
   * @param void
   * @return void
   */
  destroy: function()
  {  	
  	this._markerManager.clearDynamicMarkersFromMap();
  	this._markerManager.clearFixedMarkersFromMap();
  	
  	this._markerManager = null;
  	
  	if (this.getMap().getDiv())
  		dojo.destroy(this.getMap().getDiv());
  	
  	this._requestManager.cancel('loadData' + this._config.mapIndex);
  }
  
});

}

if(!dojo._hasResource['svzsolutions.maps.googlemaps.CustomOverlay']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.maps.googlemaps.CustomOverlay'] = true;
/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions - Google Maps Custom Overlay				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.1
 * @versionDate:	2009-10-17
 * @date:     		2009-10-17
 */

dojo.provide('svzsolutions.maps.googlemaps.CustomOverlay');

/**
 * SVZ Solutions GoogleMaps CustomOverlay class
 * 
 */ 
dojo.declare('svzsolutions.maps.googlemaps.CustomOverlay', null,
{
	/**
   * Constructor
   * 
   * @param array config
   * @return void
   */
	constructor: function(config)
	{
		// Because of cross domain problems we cannot extend from this object directly
		this._googleOverlay 						= new google.maps.OverlayView();
		this._googleOverlay.draw 				= this._draw;
		this._googleOverlay.remove 			= this._remove;
		this._googleOverlay.getPosition = this._getPosition;
		this._googleOverlay._config			= config;
	
		this._config  									= config;
	
		this._googleOverlay.latlng_ 		= config.position;
		
		google.maps.event.addListener(this._googleOverlay, 'click', dojo.hitch(this, 'onClick'));
	
	  // Once the LatLng and text are set, add the overlay to the map.  This will
	  // trigger a call to panes_changed which should in turn call draw.
	  this.setMap(config.map);
	},
	
	/**
	 * Method setMap
	 * 
	 * @param object map
	 * @return void
	 */
	setMap: function(map)
	{
		this._googleOverlay.setMap(map);
	},
	
	/**
	 * Method onClick
	 * 
	 * @param object event
	 * @return void
	 */
	onClick: function(event)
	{		
	},
	
	/**
   * Method that draws the overlay on the map
   * 
   * @param void
   * @return void
   */
	_draw: function() 
	{
		// Get the div from the parent object
		var overlay = this.div_;

    if (!overlay) 
    {    	   
    	overlay = this.div_ = dojo.create('div', { className: this._config.className, title: this._config.title });
    	
    	if (this._config.label && this._config.label != 'undefined')
    		dojo.create('div', { className: 'sg-label', innerHTML: this._config.label }, overlay );    		
      
      google.maps.event.addDomListener(overlay, "click", dojo.hitch(this, function(event) 
      	{
	        google.maps.event.trigger(this, "click");
	      })
      );

      // Then add the overlay to the DOM
      var panes = this.getPanes();
      panes.overlayImage.appendChild(overlay);      
    }

    // Position the overlay 
    var point = this.getProjection().fromLatLngToDivPixel(this.getPosition());
    
    if (point) 
    {
    	var correctionX = 0;
    	var correctionY = 0;

    	if (this._config.typeConfig.autoCenter)
    	{
    		correctionX = (overlay.clientWidth / 2);
    		correctionY = (overlay.clientHeight / 2);
    	}
    	else if (this._config.typeConfig.autoCenterY)
    	{
    		correctionX = overlay.clientWidth;
    		correctionY = (overlay.clientHeight / 2);
    	}
    	else if (this._config.typeConfig.autoCenterX)
    	{
    		correctionX = (overlay.clientWidth / 2);
    		correctionY = overlay.clientHeight;
    	}
    	else
    	{    	
    		correctionX = this._config.typeConfig.correctionX;
    		correctionY = this._config.typeConfig.correctionY;    		
    	}    
    	
    	overlay.style.left = (point.x - correctionX) + 'px';
  		overlay.style.top = (point.y - correctionY) + 'px';
    }
    
    dojo.style(overlay, 'position', 'absolute');
  },

	/**
   * Method that removes the overlay from the map
   * 
   * @param void
   * @return void
   */
  remove : function() 
  {
    // Check if the overlay is on the map and needs to be removed.
    if (this._googleOverlay.div_) 
    {      
      dojo.destroy(this._googleOverlay.div_);
    }
  },
  
	/**
   * Method that removes the overlay from the map
   * 
   * @param void
   * @return void
   */
  _remove : function() 
  {
    // Check if the overlay is on the map and needs to be removed.
    if (this.div_) 
    {      
      dojo.destroy(this.div_);
    }
  },
  
	/**
   * Method that returns the position of the overlay on the map
   * 
   * @param object point
   * @return void
   */
  setPosition : function(point) 
  {
  	console.log('Setting a new position');
  	this._googleOverlay.setPosition(point);
  },
  
	/**
   * Method that returns the position of the overlay on the map
   * 
   * @param void
   * @return void
   */
  getPosition : function() 
  {
   return this._googleOverlay.getPosition();
  },

	/**
   * Method that returns the position of the overlay on the map
   * 
   * @param void
   * @return void
   */
  _getPosition : function() 
  {
   return this.latlng_;
  }

});

}

if(!dojo._hasResource['svzsolutions.maps.googlemaps.InfoWindow']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.maps.googlemaps.InfoWindow'] = true;
/**
 * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions - Google Maps Info Window				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.1
 * @versionDate:	2009-10-17
 * @date:     		2009-10-17
 */

dojo.provide('svzsolutions.maps.googlemaps.InfoWindow');

/**
 * SVZ GoogleMaps InfoWindow class
 * 
 * TODO:
 * - Fix dojo.query using the infoWindowContent HTML element as path to search in instead of the entire body
 */ 
dojo.declare('svzsolutions.maps.googlemaps.InfoWindow', null, 
{		
	COMPONENT_TAB_HOLDER_CLASSNAME          	: 'sg-component-tabs-holder',
  COMPONENT_TAB_LINKS_HOLDER_CLASSNAME    	: 'sg-component-tab-links-holder',
  COMPONENT_TAB_LINK_LOAD_DYNAMIC_CLASSNAME : 'sg-component-tab-link-load-dynamic',
  COMPONENT_TAB_CONTENTS_HOLDER_CLASSNAME 	: 'sg-component-tab-contents-holder',
  COMPONENT_TAB_CONTENT_HOLDER_CLASSNAME  	: 'sg-component-tab-content-holder', 
  COMPONENT_LIST_HOLDER_CLASSNAME       		: 'sg-component-list-holder',
  COMPONENT_LIST_ITEM_HOLDER_CLASSNAME  		: 'sg-component-list-item-holder',  
  MAIN_HOLDER_CLASSNAME											: 'sg-info-window-content-main-holder',
	_tabContent																: false,
	_tabLinks																	: false,
	
	/**
   * Constructor
   * 
   * @param string infoWindowContent
   * @return object
   */
	constructor: function(infoWindowContent)
  {			
	  console.log('InfoWindow: Constructor');
	  
		this._tabLinks 					= false;
		this._tabContent 				= false;
		this._loader						= false;
		this._loaderTab					= false;
		this.mainHolder 				= false;
		this.content 						= infoWindowContent;
		this._requestManager 		= new svzsolutions.generic.RequestManager();
		this._currentTabContent = false;
		this._marker						= false;
		
		var mainHolder					= dojo.query('.' + this.MAIN_HOLDER_CLASSNAME, infoWindowContent);
		
  	if (mainHolder && mainHolder[0])
			this.mainHolder = mainHolder[0];
		
		this.init();			
  },
  
  /**
   * Method getElem which returns the base element for this info window
   * 
   * @param void
   * @return DomElement elem
   */
  getElem: function()
  {
  	return this.content;
  },
  
	/**
   * Method Init which init the created content
   * 
   * @param void
   * @return object
   */
  init: function()
  {		
  	console.log('InfoWindow: Init');
  	
		var tabComponentElem = dojo.query('.' + this.COMPONENT_TAB_HOLDER_CLASSNAME, this.content);

		if (tabComponentElem && tabComponentElem[0])
			this.initTabComponent(tabComponentElem[0]);	
		
		var listComponentElem = dojo.query('.' + this.COMPONENT_LIST_HOLDER_CLASSNAME, this.content);

		if (listComponentElem && listComponentElem[0])
			this.initListComponent(listComponentElem[0]);
		
		this.initCustomContent();
  },
  
  /**
   * Method that instantiates the html for an tab component
   * 
   * TODO:
   * - Create a tab manager class to work handle this
   * 
   * @param HTMLDomElement elem
   * @return void
   */
  initTabComponent : function(elem) 
  {  	  	
		this._tabLinks 		= dojo.query('.' + this.COMPONENT_TAB_LINKS_HOLDER_CLASSNAME + ' a', elem);
		this._tabContent 	= dojo.query('.' + this.COMPONENT_TAB_CONTENTS_HOLDER_CLASSNAME + ' .' + this.COMPONENT_TAB_CONTENT_HOLDER_CLASSNAME, elem);		  	
		
		// Iterate through all the tab links
		for (var i = 0; i < this._tabLinks.length; i++)
		{
			this._tabLinks[i].linkIndex = i;
			
			dojo.connect(this._tabLinks[i], 'onclick', this, function(event) 
  			{
					dojo.stopEvent(event);				
				
					if (event.target)
						this.activateTab(event.target.linkIndex);							
  					
  			});
		}  	  		  		  		      	  	
  },
  
  /**
   * Method that instantiates the html for an list component
   * 
   * TODO:
   * - Create a list manager class to work handle this
   * 
   * @param HTMLDomElement elem
   * @return void
   */
  initListComponent : function(elem) 
  {  	  	
		this.listLinks 		= dojo.query('.' + this.COMPONENT_LIST_ITEM_HOLDER_CLASSNAME + ' a', elem);		
		
		// Iterate through all the list links
		for (var i = 0; i < this.listLinks.length; i++)
		{
			dojo.connect(this.listLinks[i], 'onclick', this, function(event) 
				{
					dojo.stopEvent(event);
					
					var dataLoadUrl = dojo.attr(event.target, 'href');
					
					this.loadData(dataLoadUrl);				
				});
		}  	  		  		  		      	  	
  },  
  
  /**
   * Method that may be overriden. 
   * 
   * @param void
   * @return void
   */  
  initCustomContent : function()
  {

  },
  
  /**
   * Method that may be overriden. 
   * 
   * @param void
   * @return void
   */  
  initCustomTabContent : function()
  {

  },  
  
  /**
   * Method that activates the current selected tab
   * 
   * @param integer index
   * @return void
   */
  activateTab: function(index) 
  {
  	console.log('InfoWindow: activating tab with index [', index, ']');
  	
  	var activeHref = '';
  	var loadAjax   = false;
  	
  	// Iterate through all the tab links
  	for (var i = 0; i < this._tabLinks.length; i++)
		{
  		dojo.removeClass(this._tabLinks[i], 'active');
  		
  		if (i == index)
  		{
  			dojo.addClass(this._tabLinks[i], 'active');
  			
  			activeHref = dojo.attr(this._tabLinks[i], 'href');
  			
  			if (dojo.hasClass(this._tabLinks[i], this.COMPONENT_TAB_LINK_LOAD_DYNAMIC_CLASSNAME))
  				loadAjax = true;
  				
  		}  		  					  			
		}
  	
  	// Iterate through all the tab content
  	for (var i = 0; i < this._tabContent.length; i++)
		{
  		dojo.removeClass(this._tabContent[i], 'active');
  		
  		if (i == index)
  		{
  			dojo.addClass(this._tabContent[i], 'active');
  			this._currentTabContent = this._tabContent[i];  			  			
  			
  		}
  			
		}
  	
  	if (loadAjax)
  		this.loadTabData(activeHref);
		else
			this._requestManager.cancel('infoWindowLoadTabData');
  	
  },
  
  /**
   * Method that loads additional content in the info window
   * 
   * @param string dataLoadUrl
   * @return void
   */
  loadData: function(dataLoadUrl)
  {
  	if (!dataLoadUrl)
  		return false;

    var xhrArgs = 
    {
      url: dataLoadUrl,
      handleAs: "json",
      load: dojo.hitch(this, this.loadDataCallback),
      error: dojo.hitch(this, function(error) 
      {    	
	  		this._loader.destroy();
		  	this._loader = null;
	  	
	  		if (error.dojoType == 'cancel')
	  			return;
  		
        console.log("An unexpected error occurred: " + error);
      })
    };

    this._loader = new svzsolutions.generic.Loader('infowindow-load-data');
    this._loader.onCancel 							= dojo.hitch(this, function(event)
  	    {
      		this._requestManager.cancel('infoWindowLoadData');
  	    });
    
    this._loader.placeAt(this.mainHolder, 'first');
    
    this._loader.show();

    // Call the asynchronous xhrGet
    this._requestManager.get(xhrArgs, 'infoWindowLoadData');
  },
  
  /**
   * Method that executes when the request for data has been finished
   * 
   * @param jsonObject data
   * @return void
   */
  loadDataCallback: function(data)
  {
   	if (this.mainHolder)
  	{   		
	    if (data && data.content)
	    {	 
	    	var domElem = dojo._toDom(data.content);
	    	
	    	dojo.place(domElem, this.mainHolder, 'only');
	    	
	    	this.initTabComponent(domElem);
	    	
	    	this.initCustomContent();
	    }
  	}
    
  },   
  
  /**
   * Method that loads additional content in the info window
   * 
   * @param string dataLoadUrl
   * @return void
   */
  loadTabData: function(dataLoadUrl)
  {
  	console.log('InfoWindow: load tab data from url [', dataLoadUrl, ']');
  	
  	if (!dataLoadUrl)
  		return false;

    var xhrArgs = 
    {
      url: dataLoadUrl,
      handleAs: "json",
      load: dojo.hitch(this, this.loadTabDataCallback),
      error: dojo.hitch(this, function(error) 
      {
      	if (this._loaderTab)
      	{
    	  	this._loaderTab.destroy();
    			this._loaderTab = null;
      	} 
    	
    		if (error.dojoType == 'cancel')
    			return;
    	    	  
        console.log("An unexpected error occurred: " + error);
      })
    };
    
    if (!this._loaderTab)
    {	    	 
    	this._loaderTab 												= new svzsolutions.generic.Loader('infowindow-load-tab-data', this._currentTabContent, 'first');
      this._loaderTab.onCancel 								= dojo.hitch(this, function(event)
  	    {
      		this._requestManager.cancel('infoWindowLoadTabData');
  	    });		
    }	   	   
    
    this._loaderTab.show();    

    // Call the asynchronous xhrGet
    this._requestManager.get(xhrArgs, 'infoWindowLoadTabData');
  },
  
  /**
   * Method that executes when the request for data has been finished
   * 
   * @param jsonObject data
   * @return void
   */
  loadTabDataCallback: function(data)
  {
   	if (this._currentTabContent)
  	{   		
	    if (data && data.content)
	    {	 
	    	var domElem = dojo._toDom(data.content);
	    	
	    	dojo.place(domElem, this._currentTabContent, 'only');

	    	this.initCustomTabContent();
	    }
  	}
   	
   	this._loaderTab.destroy();   	
   	this._loaderTab = null;    
  },
  
  /**
   * Method setMarker which sets the marker attached to this info window
   * 
   * @param object marker
   * @return void
   */
  setMarker: function(marker)
  {
  	console.log('InfoWindow: setting marker to [', marker, ']');
  	
  	this._marker = marker;
  },
  
  /**
   * Method getMarker which gets the marker attached to this info window
   * 
   * @param void
   * @return object marker
   */
  getMarker: function()
  {
  	return this._marker;
  }

});

}

if(!dojo._hasResource['svzsolutions.maps.googlemaps.Address']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.maps.googlemaps.Address'] = true;
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

}

if(!dojo._hasResource['svzsolutions.generic.Marker']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.generic.Marker'] = true;
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

}

if(!dojo._hasResource['svzsolutions.generic.MarkerCluster']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.generic.MarkerCluster'] = true;
/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions MarkerCluster				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.6
 * @versionDate:	2010-07-25
 * @date:     		2010-07-25
 */

dojo.provide('svzsolutions.generic.MarkerCluster');

/**
 * SVZ Marker Cluster class
 * 
 */ 
dojo.declare('svzsolutions.generic.MarkerCluster', svzsolutions.generic.Marker, 
{			
	
	/**
   * Constructor
   * 
   * @param void
   * @return object
   */
	constructor: function()
  {					
  },
  
	/**
   * Method getConfig which returns an object
   * 
   * @param string libraryType
   * @return object
   */
	getConfig: function(libraryType)
	{
  	var config 							= this._getConfig(libraryType);
  	
  	var markerCount 				= new String(this._config.count);
	   
		config.className				= this._config.markerType.className;
		
		if (markerCount.length < 5)
			config.className += ' sg-marker-cluster-size-' + markerCount.length;
		else
			config.className += ' sg-marker-cluster-size-5';

		config.typeConfig   		= this._config.markerType;
		config.smartNavigation 	= this._config.smartNavigation;
		config.bounds						= this._config.bounds;
		config.label						= this._config.label;
		config.closestsMarkers	= this._config.closestsMarkers;  	
  	
		return config;
	}

});

}

if(!dojo._hasResource['svzsolutions.generic.MarkerList']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.generic.MarkerList'] = true;
/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions MarkerList				
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.6
 * @versionDate:	2010-07-25
 * @date:     		2010-07-25
 */

dojo.provide('svzsolutions.generic.MarkerList');

/**
 * SVZ MarkerList class
 * 
 */ 
dojo.declare('svzsolutions.generic.MarkerList', svzsolutions.generic.MarkerCluster, 
{			
	
	/**
   * Constructor
   * 
   * @param void
   * @return object
   */
	constructor: function()
  {			
					
  },  

	/**
	 * Method getConfig which returns an object
	 * 
	 * @param string libraryType
	 * @return object
	 */
	getConfig: function(libraryType)
	{	
  	var config 							= this._getConfig(libraryType);
		var markerCount 				= new String(this._config.count);
		
		config.className				= this._config.markerType.className;
		
		if (markerCount.length < 5)
			config.className += ' sg-marker-list-size-' + markerCount.length;
		else
			config.className += ' sg-marker-list-size-5';
		
		config.typeConfig				= this._config.markerType;
		config.label						= this._config.label; 
		config.entityIds				= this._config.entityIds;
		config.closestsMarkers	= this._config.closestsMarkers;
		
		/* BEGIN Temporary should be fixed in something more smart */
		this._config.dataLoadUrl += '?entityIds=';
		
		if (this._config.entityIds.length > 0)
		{
			for (var j = 0; j < this._config.entityIds.length; j++)
			{
				this._config.dataLoadUrl += this._config.entityIds[j] + ',';
			}
			
			// Remove final ,
			this._config.dataLoadUrl = this._config.dataLoadUrl.replace(/,$/, '');
		}
		/* END Temporary should be fixed in something more smart */
		
		config.dataLoadUrl				= this._config.dataLoadUrl;
		
		return config;
	}

});

}

if(!dojo._hasResource['svzsolutions.generic.Geocode']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.generic.Geocode'] = true;
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

}

if(!dojo._hasResource['svzsolutions.layer']){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource['svzsolutions.layer'] = true;
/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions Layer file				
 * @description:  Layer file, used for containing the full module (debug / building purposes only)
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.6
 * @versionDate:	2010-06-28
 * @date:     		2010-06-28
 */

dojo.provide('svzsolutions.layer');















}

