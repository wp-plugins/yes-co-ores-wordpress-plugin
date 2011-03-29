<?php

  /**
   * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Google Maps Map File
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.1
   * @versionDate:  2009-10-17
   * @date:         2009-10-17
   */

  /**
   * SVZ_Solutions_Google_Maps_Map main class
   * Desc: Based upon Google Maps API V3
   *
   */
  class SVZ_Solutions_Maps_Google_Maps_Map extends SVZ_Solutions_Maps_MapAbstract
  {
    const MAP_MODES                               = 'static;dynamic';
    const MAP_TYPES                               = 'roadmap;satellite;hybrid;terrain';
    const MAP_TYPE_CONTROL_STYLES                 = 'default;horizontal_bar;dropdown_menu';
    const MAP_NAVIGATION_CONTROL_STYLES           = 'default;android;small;zoom_pan';
    const MAP_SCALE_CONTROL_STYLES                = 'default';
    const MAP_CONTROL_POSITIONS                   = 'bottom;bottom_left;bottom_right;left;right;top;top_left;top_right';

    private $libraryConfig                        = array();
    private $loadDataOnce                         = false;
    private $markerType                           = array();
    private $dataLoadUrl                          = '';
    private $mapType                              = 'hybrid';
    private $mapTypeControlStyle                  = 'default';
    private $mapTypeControlPosition               = 'top_right';
    private $mapNavigationControlStyle            = 'default';
    private $mapNavigationControlPosition         = 'left';
    private $mapScaleControlStyle                 = 'default';
    private $mapScaleControlPosition              = 'bottom_left';
    private $markerManager                        = null;
    private $mode                                 = 'dynamic';
    private $layers                               = array();
    private $enableStreetViewControl              = false;

    /**
     * Constructor
     *
     * @param string $version
     * @return void
     */
    public function __construct($version, $mode = 'dynamic')
    {
      $this->libraryConfig      = array('name' => SVZ_Solutions_Maps_Map::MAP_TYPE_GOOGLE_MAPS, 'version' => $version, 'mode' => $mode);
      $this->mode               = $mode;
      $this->centerGeocode      = new SVZ_Solutions_Generic_Geocode(50.5, 5);
      $this->markerManager      = new SVZ_Solutions_Generic_Marker_Manager();
    }

    /**
     * Method that returns the supported max width
     *
     * @param void
     * @return integer
     */
    public function getMaxWidth()
    {
      if ($this->mode == 'static')
        return 640;

      return false;
    }

    /**
     * Method that returns the supported max height
     *
     * @param void
     * @return integer
     */
    public function getMaxHeight()
    {
      if ($this->mode == 'static')
        return 640;

      return false;
    }

    /**
     * Method thats sets if the map should load his marker / polygon etc.. data only one time
     *
     * @param boolean $loadDataOnce
     * @return void
     */
    public function setLoadDataOnce($loadDataOnce)
    {
      if (!is_bool($loadDataOnce))
        throw new Exception(__METHOD__ . '; Invalid $loadDataOnce, not a bool.');

      $this->loadDataOnce = $loadDataOnce;
    }

    /**
     * Method thats sets which map type is shown on initial load
     *
     * @param string $mapType
     * @return void
     */
    public function setMapType($mapType)
    {
      if (!is_string($mapType))
        throw new Exception(__METHOD__ . '; Invalid $mapType, not a string.');

      if (!in_array($mapType, explode(';', self::MAP_TYPES)))
        throw new Exception(__METHOD__ . '; Invalid $mapType, not one of ' . implode(' / ', explode(';', self::MAP_TYPES)) . '.');

      $this->mapType = $mapType;
    }

    /**
     * Method thats returns the available map control types
     *
     * @param void
     * @return array
     */
    public function getMapTypes()
    {
      return explode(';', self::MAP_TYPES);
    }

    /**
     * Method thats sets the style for the map type control
     *
     * @param string $mapTypeControlStyle
     * @return void
     */
    public function setMapTypeControlStyle($mapTypeControlStyle)
    {
      if (!is_string($mapTypeControlStyle))
        throw new Exception(__METHOD__ . '; Invalid $mapTypeControlStyle, not a string.');

      if (!in_array($mapTypeControlStyle, $this->getMapTypeControlStyles()))
        throw new Exception(__METHOD__ . '; Invalid $mapTypeControlStyle, not one of ' . implode(' / ', explode(';', self::MAP_TYPE_CONTROL_STYLES)) . '.');

      $this->mapTypeControlStyle = $mapTypeControlStyle;
    }

    /**
     * Method thats returns the available map type control styles
     *
     * @param void
     * @return array
     */
    public function getMapTypeControlStyles()
    {
      return explode(';', self::MAP_TYPE_CONTROL_STYLES);
    }

    /**
     * Method thats sets the style for the map navigation control
     *
     * @param string $mapTypeControlStyle
     * @return void
     */
    public function setMapNavigationControlStyle($mapNavigationControlStyle)
    {
      if (!is_string($mapNavigationControlStyle))
        throw new Exception(__METHOD__ . '; Invalid $mapNavigationControlStyle, not a string.');

      if (!in_array($mapNavigationControlStyle, $this->getMapNavigationControlStyles()))
        throw new Exception(__METHOD__ . '; Invalid $mapNavigationControlStyle, not one of ' . implode(' / ', explode(';', self::MAP_NAVIGATION_CONTROL_STYLES)) . '.');

      $this->mapNavigationControlStyle = $mapNavigationControlStyle;
    }

    /**
     * Method thats returns the available map navigation control styles
     *
     * @param void
     * @return array
     */
    public function getMapNavigationControlStyles()
    {
      return explode(';', self::MAP_NAVIGATION_CONTROL_STYLES);
    }

    /**
     * Method thats sets the style for the map scale control
     *
     * @param string $mapScaleControlStyle
     * @return void
     */
    public function setMapScaleControlStyle($mapScaleControlStyle)
    {
      if (!is_string($mapScaleControlStyle))
        throw new Exception(__METHOD__ . '; Invalid $mapScaleControlStyle, not a string.');

      if (!in_array($mapScaleControlStyle, $this->getMapScaleControlStyles()))
        throw new Exception(__METHOD__ . '; Invalid $mapScaleControlStyle, not one of ' . implode(' / ', explode(';', self::MAP_SCALE_CONTROL_STYLES)) . '.');

      $this->mapScaleControlStyle = $mapScaleControlStyle;
    }

    /**
     * Method thats returns the available map scale control styles
     *
     * @param void
     * @return array
     */
    public function getMapScaleControlStyles()
    {
      return explode(';', self::MAP_SCALE_CONTROL_STYLES);
    }

    /**
     * Method thats sets the position for the map type control
     *
     * @param string $mapTypeControlPosition
     * @return void
     */
    public function setMapTypeControlPosition($mapTypeControlPosition)
    {
      if (!is_string($mapTypeControlPosition))
        throw new Exception(__METHOD__ . '; Invalid $mapTypeControlPosition, not a string.');

      if (!in_array($mapTypeControlPosition, $this->getMapControlPositions()))
        throw new Exception(__METHOD__ . '; Invalid $mapTypeControlPosition, not one of ' . implode(' / ', $this->getMapControlPositions()) . '.');

      $this->mapTypeControlPosition = $mapTypeControlPosition;
    }

    /**
     * Method thats sets the position for the map navigation control
     *
     * @param string $mapNavigationControlPosition
     * @return void
     */
    public function setMapNavigationControlPosition($mapNavigationControlPosition)
    {
      if (!is_string($mapNavigationControlPosition))
        throw new Exception(__METHOD__ . '; Invalid $mapNavigationControlPosition, not a string.');

      if (!in_array($mapNavigationControlPosition, $this->getMapControlPositions()))
        throw new Exception(__METHOD__ . '; Invalid $mapNavigationControlPosition, not one of ' . implode(' / ', $this->getMapControlPositions()) . '.');

      $this->mapNavigationControlPosition = $mapNavigationControlPosition;
    }

    /**
     * Method thats sets the position for the map scale control
     *
     * @param string $mapScaleControlPosition
     * @return void
     */
    public function setMapScaleControlPosition($mapScaleControlPosition)
    {
      if (!is_string($mapScaleControlPosition))
        throw new Exception(__METHOD__ . '; Invalid $mapScaleControlPosition, not a string.');

      if (!in_array($mapScaleControlPosition, $this->getMapControlPositions()))
        throw new Exception(__METHOD__ . '; Invalid $mapScaleControlPosition, not one of ' . implode(' / ', $this->getMapControlPositions()) . '.');

      $this->mapScaleControlPosition = $mapScaleControlPosition;
    }

    /**
     * Method thats returns the available map navigation and type control positions
     *
     * @param void
     * @return array
     */
    public function getMapControlPositions()
    {
      return explode(';', self::MAP_CONTROL_POSITIONS);
    }

    /**
     * Method which enables the street view control
     *
     * @param void
     * @return void
     */
    public function enableStreetViewControl()
    {
      $this->enableStreetViewControl = true;
    }

    /**
     * Method thats where the change of a map should load some markers from
     *
     * @param string $markerLoadUrl
     * @return void
     */
    public function setDataLoadUrl($dataLoadUrl)
    {
      if (!is_string($dataLoadUrl))
        throw new Exception(__METHOD__ . '; Invalid $dataLoadUrl, not a string.');

      $this->dataLoadUrl = $dataLoadUrl;
    }

    /**
     * Method thats adds a marker type to the map
     *
     * @param SVZ_Solutions_Generic_Marker_Type $markerType
     * @return void
     */
    public function addMarkerType(SVZ_Solutions_Generic_Marker_Type $markerType)
    {
      $this->markerTypes[] = $markerType;
    }

    /**
     * Method thats adds a marker to the map
     *
     * @param SVZ_Solutions_Generic_Marker $markerType
     * @return void
     */
    public function addMarker(SVZ_Solutions_Generic_Marker $marker)
    {
      $this->markerManager->addMarker($marker);
    }

    /**
     * Method thats adds a layer to the map
     *
     * @param string $layer
     * @return void
     */
    public function addLayer($layer)
    {
      if (empty($layer) && !is_string($layer))
        throw new Exception(__METHOD__ . '; Invalid $layer, not a string or empty.');

      $this->layers[] = $layer;
    }

    /**
     * Method thats generates a config object with the configuration
     *
     * @param void
     * @return StdClass
     */
    public function getConfig()
    {
      $config                           = $this->getMainConfig();
      $config->libraryConfig            = $this->libraryConfig;
      $config->mapContainerId           = $this->getContainerId();
      $config->mapType                  = $this->mapType;
      $config->loadDataOnce             = $this->loadDataOnce;
      $config->zoomLevel                = $this->getZoomLevel();
      $config->centerGeoLat             = $this->getCenterGeocode()->getLatitude();
      $config->centerGeoLng             = $this->getCenterGeocode()->getLongitude();
      $config->mapTypeControl           = true;
      $config->mapTypeControlOptions    = array('style' => 'google.maps.MapTypeControlStyle.' . strtoupper($this->mapTypeControlStyle), 'position' => 'google.maps.ControlPosition.' . strtoupper($this->mapTypeControlPosition));
      $config->navigationControl        = true;
      $config->navigationControlOptions = array('style' => 'google.maps.NavigationControlStyle.' . strtoupper($this->mapNavigationControlStyle), 'position' => 'google.maps.ControlPosition.' . strtoupper($this->mapNavigationControlPosition));
      $config->scaleControl             = true;
      $config->scaleControlOptions      = array('style' => 'google.maps.ScaleControlStyle.' . strtoupper($this->mapScaleControlStyle), 'position' => 'google.maps.ControlPosition.' . strtoupper($this->mapScaleControlPosition));
      $config->enableStreetViewControl  = $this->enableStreetViewControl;

      if ($this->markerManager->hasMarkers())
        $config->markers                = $this->markerManager->toArray();

      if (!empty($this->dataLoadUrl))
        $config->dataLoadUrl            = $this->dataLoadUrl;

      if (!empty($this->layers))
        $config->layers                 = $this->layers;

      if (!empty($this->markerTypes))
      {
        $config->markerTypes            = array();

        foreach ($this->markerTypes as $markerType)
        {
          $markerTypeConfig               = new StdClass();
          $markerTypeConfig->className    = $markerType->getClassName();
          $markerTypeConfig->iconEnabled  = $markerType->isIconEnabled();

          if ($markerType->isIconEnabled())
          {
            if ($markerType->hasIcon())
              $markerTypeConfig->icon         = $markerType->getIcon()->getConfig();

            if ($markerType->hasIconShadow())
              $markerTypeConfig->shadow       = $markerType->getIconShadow()->getConfig();

          }

          $markerTypeConfig->correctionX  = $markerType->getOverlayCorrectionX();
          $markerTypeConfig->correctionY  = $markerType->getOverlayCorrectionY();
          $markerTypeConfig->autoCenter   = $markerType->getAutoCenter();
          $markerTypeConfig->autoCenterY  = $markerType->getAutoCenterY();
          $markerTypeConfig->autoCenterX  = $markerType->getAutoCenterX();
          $markerTypeConfig->clickAction  = $markerType->getClickAction();
          $markerTypeConfig->layerName    = $markerType->getLayer()->getName();
          $markerTypeConfig->layerType    = $markerType->getLayer()->getType();

          $config->markerTypes[$markerType->getName()] = $markerTypeConfig;
        }
      }

      return $config;
    }

    /**
     * Method thats generates a url with the specified configuration
     *
     * @param void
     * @return string
     */
    public function getStaticUrl()
    {
      $url = 'http://maps.google.com/maps/api/staticmap?';

      $url .= 'center=' . $this->getCenterGeocode()->getLatitude() . ',' . $this->getCenterGeocode()->getLongitude();

      $url .= '&zoom=' . $this->getZoomLevel();

      $url .= '&size=' . $this->getWidth() . 'x' . $this->getHeight();

      $url .= '&maptype=' . $this->mapType;

      $url .= '&sensor=false';

      if ($this->markerManager->hasMarkers())
      {
        $markerStack = $this->markerManager->getMarkers();

        foreach ($markerStack as $marker)
        {
          $url .= '&markers=';

          $settings = array();

          if ($marker->hasType())
          {
            if ($marker->getType()->hasIcon())
              $settings[] = 'icon:' . $marker->getType()->getIcon()->getUrl();

            if ($marker->getType()->hasColor())
              $settings[] = 'color:' . $marker->getType()->getColor();

            if ($marker->getType()->hasSize())
              $settings[] = 'size:' . $marker->getType()->getSize();
          }

          if ($marker->hasLabel())
            $settings[] = '|label:' . $marker->getLabel();

          $settings[] = $marker->getGeocode()->getLatitude() . ',' . $marker->getGeocode()->getLongitude();

          $url .= implode('|', $settings);
        }
      }

      return $url;
    }

  }

?>