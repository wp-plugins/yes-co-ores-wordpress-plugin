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