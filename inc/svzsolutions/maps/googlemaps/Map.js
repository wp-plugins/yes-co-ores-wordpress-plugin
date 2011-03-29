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