<?php

  /**
   * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic Marker List file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.1
   * @versionDate:  2009-10-17
   * @date:         2009-10-17
   */

  require_once('MarkerCluster.php');

  /**
   * SVZ_Solutions_Generic_Marker_List class
   *
   */
  class SVZ_Solutions_Generic_Marker_List extends SVZ_Solutions_Generic_Marker_Cluster
  {
    /**
     * Constructor
     *
     * @param void
     * @return void
     */
    public function __construct($listDataLoadUrl)
    {
      parent::__construct();

      // Override the type
      $this->setTypeName('list');
      $this->setListDataLoadUrl($listDataLoadUrl);
    }

    /**
     * Method thats sets the data load url for the list marker
     *
     * @param string $listDataLoadUrl
     * @return void
     */
    public function setListDataLoadUrl($listDataLoadUrl)
    {
      if (!is_string($listDataLoadUrl) || empty($listDataLoadUrl))
        throw new Exception(__METHOD__ . '; Invalid $listDataLoadUrl, not a string or empty.');

      $this->listDataLoadUrl = $listDataLoadUrl;
    }

    /**
     * Method thats gets the data load url for the list marker
     *
     * @param void
     * @return string
     */
    public function getListDataLoadUrl()
    {
      return $this->listDataLoadUrl;
    }
  }

?>