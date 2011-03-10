<?php

  if (!defined('YESCO_OG_TYPE_MAPPING'))
  {
    // Define how certain fields should be displayed
    define('YESCO_OG_TYPE_MAPPING', serialize(array(
      'huis_Updated'            => array( 'title' => 'Laatste update via sync'),
      'huis_uuid'               => array( 'title' => 'UUID van project'),
      'huis_scenario'           => array( 'title' => 'Scenario van project'),
      'huis_Naam'               => array( 'title' => 'Titel van object',
                                          'width' => 450),
      'huis_Straat'             => array(),
      'huis_Huisnummer'         => array( 'width' => 100),
      'huis_Postcode'           => array( 'width' => 100),
      'huis_Wijk'               => array(),
      'huis_Buurt'              => array(),
      'huis_Plaats'             => array( 'search' => 'exact'),
      'huis_Gemeente'           => array(),
      'huis_Provincie'          => array(),
      'huis_Land'               => array(),
      'huis_Longitude'          => array(),
      'huis_Latitude'           => array(),
      'huis_Status'             => array(),
      'huis_Oppervlakte'        => array( 'type'    => 'oppervlakte',
                                          'title'   => 'Woonopp.',
                                          'width'   => 100,
                                          'search'  => 'range'),
      'huis_OppervlaktePerceel' => array( 'type'  => 'oppervlakte',
                                          'title' => 'Perceelopp.',
                                          'width' => 100),
      'huis_Inhoud'             => array( 'type'    => 'inhoud',
                                          'width'   => 100,
                                          'search'  => 'range'), 
      'huis_KoopPrijsSoort'     => array( 'title'   => 'Prijs soort'),
      'huis_KoopPrijs'          => array( 'type'    => 'price',
                                          'title'   => 'Prijs',
                                          'width'   => 100,
                                          'search'  => 'range'),
      'huis_KoopPrijsConditie'  => array( 'title' => 'Prijs conditie',
                                          'width' => 100),
      'huis_HuurPrijs'          => array( 'type'    => 'price',
                                          'title'   => 'Prijs',
                                          'width'   => 100,
                                          'search'  => 'range'),
      'huis_HuurPrijsConditie'  => array( 'title' => 'Prijs conditie',
                                          'width' => 100),
      'huis_Type'               => array( 'title' => 'Type'),
      'huis_SoortWoning'        => array( 'title'   => 'Soort woning',
                                          'search'  => 'exact'),
      'huis_TypeWoning'         => array( 'title'   => 'Type woning',
                                          'search'  => 'exact'),
      'huis_KenmerkWoning'      => array( 'title'   => 'Kenmerk'),
      'huis_Aantalkamers'       => array( 'title'   => 'Kamers',
                                          'width'   => 100,
                                          'search'  => 'range'),
      'huis_Bouwjaar'           => array(),
      'huis_Ligging'            => array(),
      'huis_GarageType'         => array( 'title' => 'Garage'),
      'huis_TuinType'           => array( 'title' => 'Tuin'),
      'huis_BergingType'        => array( 'title' => 'Berging'),
      'huis_PraktijkruimteType' => array( 'title' => 'Praktijkruimte'),
      'huis_EnergielabelKlasse' => array( 'title' => 'Energie label',
                                          'width' => 100),
      'huis_OpenHuisVan'        => array( 'title' => 'Open huis begin'),
      'huis_OpenHuisTot'        => array( 'title' => 'Open huis eind')
    )));
  }
  
  define('YESCO_OG_YOUTUBE_BASE_URL',       'http://www.youtube.com/watch?v=');
  define('YESCO_OG_GOOGLE_VIDEO_BASE_URL',  'http://video.google.nl/videoplay?docid=');

?>
