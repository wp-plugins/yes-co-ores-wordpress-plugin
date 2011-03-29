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