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

