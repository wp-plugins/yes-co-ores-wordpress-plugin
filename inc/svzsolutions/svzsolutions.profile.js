/**
 * Copyright (c) 2010, SVZ Solutions All Rights Reserved.
 * Available via BSD license, see license file included for details.
 * 
 * @title:				SVZ Solutions Profile file				
 * @description:  Layer file, used for building the svzsolutions package
 * @authors:   		Stefan van Zanden <info@svzsolutions.nl>
 * @company:  		SVZ Solutions
 * @contributers:	
 * @version:  		0.6
 * @versionDate:	2010-10-03
 * @date:     		2010-06-28
 */
dependencies = {
	action: "clean,release",
	version: "0.6.1.alpha",
	optimize: "shrinksafe",
	releaseName: "svzsolutions",
	release: "svzsolutions",
	
  layers: [
    {
      name: "../svzsolutions/all.js",
      resourceName: "svzsolutions.all",
      copyrightFile: "../../../opensource/svzgooglemaps/inc/svzsolutions/COPYRIGHT.txt", // Relative to the util/buildscripts directory
      dependencies: [
        "svzsolutions.layer"
      ]
    }
  ],

  prefixes: [    
    [ "svzsolutions", "../../opensource/svzgooglemaps/inc/svzsolutions"] 
  ]
};