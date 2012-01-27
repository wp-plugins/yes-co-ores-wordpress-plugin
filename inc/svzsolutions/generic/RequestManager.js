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