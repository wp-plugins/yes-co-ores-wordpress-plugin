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