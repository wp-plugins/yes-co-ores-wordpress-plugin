<?php
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_exception.php');
  
  /**
  * @desc YogFieldsSettingsAbstract
  * @author Kees Brandenburg - Yes-co Nederland
  */
  abstract class YogFieldsSettingsAbstract
  {
    protected $fieldsSettings;
    
    /**
    * @desc Create YogTypMappingAbstract
    * 
    * @param string $postType
    * @return YogTypMappingAbstract
    */
    static public function create($postType)
    {
      switch ($postType)
      {
        case POST_TYPE_WONEN:
          return new YogWonenFieldsSettings();
          break;
        case POST_TYPE_BOG:
          return new YogBogFieldsSettings();
          break;
        case POST_TYPE_RELATION:
          return new YogRelationFieldsSettings();
          break;
        default:
          throw new YogException(__METHOD__ . '; Unknown post type', YogException::GLOBAL_ERROR);
          break;
      }
    }
    
    /**
    * @desc Get all field names
    * 
    * @param void
    * @return array
    */
    public function getFieldNames()
    {
      return array_keys($this->fieldsSettings);
    }
    
    /**
    * @desc Check if mapping contains a specific field
    * 
    * @param string $field
    * @return bool
    */
    public function containsField($field)
    {
      return array_key_exists($field, $this->fieldsSettings);
    }
    
    /**
    * @desc Get field settings
    * 
    * @param string $field
    * @return array
    */
    public function getField($field)
    {
      if (!$this->containsField($field))
        return array();
        
      return $this->fieldsSettings[$field];
    }
    
    /**
    * @desc Get all field settings
    * 
    * @param void
    * @return array
    */
    public function getFields()
    {
      return $this->fieldsSettings;
    }
  }
  
  /**
  * @desc YogWonenFieldsSettings
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogWonenFieldsSettings extends YogFieldsSettingsAbstract
  {
    public function __construct()
    {
      $this->fieldsSettings = array(
        'huis_Updated'            => array( 'title' => 'Laatste update via sync'),
        'huis_uuid'               => array( 'title' => 'UUID'),
        'huis_scenario'           => array( 'title' => 'Scenario'),
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
      );
    }
  }
  
  /**
  * @desc YogBogFieldsSettings
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogBogFieldsSettings extends YogFieldsSettingsAbstract
  {
    public function __construct()
    {
      $this->fieldsSettings = array(
        'bedrijf_Updated'                                   => array( 'title' => 'Laatste update via sync'),
        'bedrijf_uuid'                                      => array( 'title' => 'UUID'),
        'bedrijf_scenario'                                  => array( 'title' => 'Scenario'),
        'bedrijf_Naam'                                      => array( 'title' => 'Titel van object',
                                                                      'width' => 450),
        'bedrijf_Straat'                                    => array(),
        'bedrijf_Huisnummer'                                => array( 'width' => 100),
        'bedrijf_NummerreeksStart'                          => array( 'title' => 'Nummerreeks start',
                                                                      'width' => 50),
        'bedrijf_NummerreeksEind'                           => array( 'title' => 'Nummerreeks eind',
                                                                      'width' => 50),
        'bedrijf_Postcode'                                  => array( 'width' => 100),
        'bedrijf_Wijk'                                      => array(),
        'bedrijf_Buurt'                                     => array(),
        'bedrijf_Plaats'                                    => array( 'search' => 'exact'),
        'bedrijf_Gemeente'                                  => array(),
        'bedrijf_Provincie'                                 => array(),
        'bedrijf_Land'                                      => array(),
        'bedrijf_Longitude'                                 => array(),
        'bedrijf_Latitude'                                  => array(),
        'bedrijf_Status'                                    => array(),
        'bedrijf_Aanmelding'                                => array(),
        'bedrijf_Aanvaarding'                               => array(),
        'bedrijf_Hoofdbestemming'                           => array( 'title'     => 'Hoofd bestemming'),
        'bedrijf_Nevenbestemming'                           => array( 'title'     => 'Neven bestemming'),
        'bedrijf_KoopPrijs'                                 => array( 'title'     => 'Prijs',
                                                                      'type'      => 'priceBtw'),
        'bedrijf_KoopPrijsConditie'                         => array( 'title'     => 'Prijs conditie',
                                                                      'width'     => 100),
        'bedrijf_KoopPrijsVervanging'                       => array( 'title'     => 'Prijs vervanging'),
        'bedrijf_Bouwrente'                                 => array( 'type'      => 'bool'),
        'bedrijf_Erfpacht'                                  => array( 'type'      => 'price',
                                                                      'addition'  => 'per jaar',
                                                                      'width'     => 100),
        'bedrijf_ErfpachtDuur'                              => array( 'title'     => 'Erfpacht duur'),
        'bedrijf_HuurPrijs'                                 => array( 'title'     => 'Prijs',
                                                                      'type'      => 'priceBtw'),
        'bedrijf_HuurPrijsConditie'                         => array( 'title'     => 'Prijs conditie',
                                                                      'width'     => 100),
        'bedrijf_HuurPrijsVervanging'                       => array( 'title'     => 'Prijs vervanging'),
        'bedrijf_Servicekosten'                             => array( 'title'     => 'Servicekosten',
                                                                      'type'      => 'priceBtw'),
        'bedrijf_ServicekostenConditie'                     => array( 'title'     => 'Servicekosten conditie',
                                                                      'width'     => 100),
        'bedrijf_PerceelOppervlakte'                        => array( 'title'     => 'Perceel oppervlakte',
                                                                      'type'      => 'oppervlakte',
                                                                      'width'     => 100),
        'bedrijf_WoonruimteSituatie'                        => array( 'title'     => 'Woonruimte situatie'),
        'bedrijf_WoonruimteStatus'                          => array( 'title'     => 'Woonruimte status'),
        'bedrijf_AantalHuurders'                            => array( 'title'     => 'Aantal huurders'),
        'bedrijf_BeleggingExpiratieDatum'                   => array( 'title'     => 'Expiratie datum'),
        'bedrijf_Huuropbrengst'                             => array( 'type'      => 'priceBtw'),
        'bedrijf_Type'                                      => array( 'type'      => 'select',
                                                                      'options'   => array('Bedrijfsruimte', 'Bouwgrond', 'Horeca', 'Kantooruimte', 'Winkelruimte'),
                                                                      'search'    => 'exact'),
        'bedrijf_BouwgrondBebouwingsmogelijkheid'           => array( 'title'     => 'Bebouwingsmogelijkheid',
                                                                      'object'    => array('Bouwgrond')),
        'bedrijf_BouwgrondBouwhoogte'                       => array( 'title'     => 'Bouwhoogte',
                                                                      'width'     => 100,
                                                                      'type'      => 'meter',
                                                                      'object'    => array('Bouwgrond')),
        'bedrijf_BouwgrondInUnitsVanaf'                     => array( 'title'     => 'In units vanaf',
                                                                      'width'     => 100,
                                                                      'object'    => array('Bouwgrond')),
        'bedrijf_BouwgrondVloerOppervlakte'                 => array( 'title'     => 'Vloer oppervlakte',
                                                                      'width'     => 100,
                                                                      'object'    => array('Bouwgrond')),
        'bedrijf_BouwgrondVloerOppervlakteProcentueel'      => array( 'title'     => 'Vloer oppervlakte procentueel',
                                                                      'object'    => array('Bouwgrond')),   
        'bedrijf_InAanbouw'                                 => array( 'title'     => 'In aanbouw',
                                                                      'type'      => 'bool',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_Bouwjaar'                                  => array( 'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_OnderhoudBinnen'                           => array( 'title'     => 'Binnen',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_OnderhoudBinnenOmschrijving'               => array( 'title'     => 'Binnen omschrijving',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_OnderhoudBuiten'                           => array( 'title'     => 'Buiten',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_OnderhoudBuitenOmschrijving'               => array( 'title'     => 'Buiten omschrijving',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_LokatieOmschrijving'                       => array( 'title'     => 'Omschrijving lokatie',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_SnelwegAfrit'                              => array( 'title'     => 'Afstand afrit snelweg',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_NsStation'                                 => array( 'title'     => 'Afstand NS station',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_NsVoorhalte'                               => array( 'title'     => 'Afstand NS voorhalte',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_BusKnooppunt'                              => array( 'title'     => 'Afstand bus knooppunt',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_TramKnooppunt'                             => array( 'title'     => 'Afstand tram knooppunt',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_MetroKnooppunt'                            => array( 'title'     => 'Afstand metro knooppunt',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_Bushalte'                                  => array( 'title'     => 'Afstand bushalte',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_Tramhalte'                                 => array( 'title'     => 'Afstand tramhalte',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_Metrohalte'                                => array( 'title'     => 'Afstand metrohalte',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_BankAfstand'                               => array( 'title'     => 'Afstand bank',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_BankAantal'                                => array( 'title'     => 'Aantal banken',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_OntspanningAfstand'                        => array( 'title'     => 'Afstand ontspanning',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_OntspanningAantal'                         => array( 'title'     => 'Aantal ontspanning',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_RestaurantAfstand'                         => array( 'title'     => 'Afstand restaurant',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_RestaurantAantal'                          => array( 'title'     => 'Aantal restaurants',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_WinkelAfstand'                             => array( 'title'     => 'Afstand winkel',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_WinkelAantal'                              => array( 'title'     => 'Aantal winkels',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_ParkerenOmschrijving'                      => array( 'title'     => 'Omschrijving',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_AantalParkeerplaatsen'                     => array( 'title'     => 'Aantal',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_AantalParkeerplaatsenOverdekt'             => array( 'title'     => 'Aantal overdekt',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_AantalParkeerplaatsenNietOverdekt'         => array( 'title'     => 'Aantal niet overdekt',
                                                                      'object'    => array('Bedrijfsruimte', 'Horeca', 'Kantooruimte', 'Winkelruimte')),
        'bedrijf_BedrijfshalOppervlakte'                    => array( 'title'     => 'Oppervlakte',
                                                                      'type'      => 'oppervlakte',
                                                                      'width'     => 100,
                                                                      'object'    => array('Bedrijfsruimte')),
        'bedrijf_BedrijfshalInUnitsVanaf'                   => array( 'title'     => 'In units vanaf',
                                                                      'type'      => 'oppervlakte',
                                                                      'width'     => 100,
                                                                      'object'    => array('Bedrijfsruimte')),
        'bedrijf_BedrijfshalVrijeHoogte'                    => array( 'title'     => 'Vrije hoogte',
                                                                      'type'      => 'cm',
                                                                      'width'     => 100,
                                                                      'object'    => array('Bedrijfsruimte')),
        'bedrijf_BedrijfshalVrijeOverspanning'              => array( 'title'     => 'Vrije overspanning',
                                                                      'type'      => 'meter',
                                                                      'width'     => 100,
                                                                      'object'    => array('Bedrijfsruimte')),
        'bedrijf_BedrijfshalVloerbelasting'                 => array( 'title'     => 'Vloerbelasting',
                                                                      'addition'  => ' kg / m2',
                                                                      'width'     => 100,
                                                                      'object'    => array('Bedrijfsruimte')),
        'bedrijf_BedrijfshalVoorzieningen'                  => array( 'title'     => 'Voorzieningen',
                                                                      'object'    => array('Bedrijfsruimte')),
        'bedrijf_BedrijfshalPrijs'                          => array( 'title'     => 'Prijs',
                                                                      'type'      => 'priceBtw',
                                                                      'object'    => array('Bedrijfsruimte')),
        'bedrijf_KantoorruimteOppervlakte'                  => array( 'title'     => 'Oppervlakte',
                                                                      'type'      => 'oppervlakte',
                                                                      'width'     => 100,
                                                                      'object'    => array('Bedrijfsruimte', 'Kantoorruimte')),
        'bedrijf_KantooruimteAantalVerdiepingen'            => array( 'title'     => 'Aantal verdiepingen',
                                                                      'width'     => 100,
                                                                      'object'    => array('Bedrijfsruimte', 'Kantoorruimte')),
        'bedrijf_KantooruimteVoorzieningen'                 => array( 'title'     => 'Voorzieningen',
                                                                      'object'    => array('Bedrijfsruimte', 'Kantoorruimte')),
        'bedrijf_KantooruimtePrijs'                         => array( 'title'     => 'Prijs',
                                                                      'type'      => 'priceBtw',
                                                                      'object'    => array('Bedrijfsruimte')),
        'bedrijf_KantooruimteInUnitsVanaf'                  => array( 'title'     => 'In units vanaf',
                                                                      'width'     => 100,
                                                                      'object'    => array('Kantoorruimte')),
        'bedrijf_KantooruimteTurnKey'                       => array( 'title'     => 'Turnkey',
                                                                      'type'      => 'bool',
                                                                      'object'    => array('Kantoorruimte')),
        'bedrijf_TerreinOppervlakte'                        => array( 'title'     => 'Oppervlakte',
                                                                      'type'      => 'oppervlakte',
                                                                      'width'     => 100,
                                                                      'object'    => array('Bedrijfsruimte')),
        'bedrijf_TerreinBouwvolumeBouwhoogte'               => array( 'title'     => 'Bouwhoogte',
                                                                      'type'      => 'meter',
                                                                      'width'     => 100,
                                                                      'object'    => array('Bedrijfsruimte')),
        'bedrijf_TerreinBouwvolumeVloerOppervlakte'         => array( 'title'     => 'Bruto vloeroppervlak',
                                                                      'type'      => 'oppervlakte',
                                                                      'width'     => 100,
                                                                      'object'    => array('Bedrijfsruimte')),
        'bedrijf_TerreinPrijs'                              => array( 'title'     => 'Prijs',
                                                                      'type'      => 'priceBtw',
                                                                      'object'    => array('Bedrijfsruimte')),
        'bedrijf_WinkelruimteOppervlakte'                   => array( 'title'     => 'Oppervlakte',
                                                                      'type'      => 'oppervlakte',
                                                                      'width'     => 100,
                                                                      'object'    => array('Winkelruimte')),
        'bedrijf_WinkelruimteVerkoopVloerOppervlakte'       => array( 'title'     => 'Verkoop vloer oppervlakte',
                                                                      'type'      => 'oppervlakte',
                                                                      'width'     => 100,
                                                                      'object'    => array('Winkelruimte')),
        'bedrijf_WinkelruimteInUnitsVanaf'                  => array( 'title'     => 'In units vanaf',
                                                                      'width'     => 100,
                                                                      'object'    => array('Winkelruimte')),
        'bedrijf_WinkelruimteFrontBreedte'                  => array( 'title'     => 'Front breedte',
                                                                      'type'      => 'cm',
                                                                      'width'     => 100,
                                                                      'object'    => array('Winkelruimte')),
        'bedrijf_WinkelruimteAantalVerdiepingen'            => array( 'title'     => 'Aantal verdiepingen',
                                                                      'width'     => 100,
                                                                      'object'    => array('Winkelruimte')),
        'bedrijf_WinkelruimteWelstandsklasse'               => array( 'title'     => 'Welstandsklasse',
                                                                      'object'    => array('Winkelruimte')),
        'bedrijf_WinkelruimteBrancheBeperking'              => array( 'title'     => 'Branche beperking',
                                                                      'type'      => 'bool',
                                                                      'object'    => array('Winkelruimte')),
        'bedrijf_WinkelruimteHorecaToegestaan'              => array( 'title'     => 'Horeca toegestaan',
                                                                      'type'      => 'bool',
                                                                      'object'    => array('Winkelruimte')),
        'bedrijf_WinkelruimteBijdrageWinkeliersvereniging'  => array( 'title'     => 'Bijdrage winkeliers vereniging',
                                                                      'type'      => 'bool',
                                                                      'object'    => array('Winkelruimte')),
        'bedrijf_WinkelruimtePersoneelTerOvername'          => array( 'title'     => 'Personeel ter overname',
                                                                      'type'      => 'bool',
                                                                      'object'    => array('Winkelruimte')),
        'bedrijf_WinkelruimtePrijsInventarisGoodwill'       => array( 'title'     => 'Prijs inventaris & goodwill',
                                                                      'type'      => 'priceBtw',
                                                                      'object'    => array('Winkelruimte')),
        'bedrijf_HorecaType'                                => array( 'title'     => 'Type',
                                                                      'object'    => array('Horeca')),
        'bedrijf_HorecaOppervlakte'                         => array( 'title'     => 'Oppervlakte',
                                                                      'type'      => 'oppervlakte',
                                                                      'width'     => 100,
                                                                      'object'    => array('Horeca')),
        'bedrijf_HorecaVerkoopVloerOppervlakte'             => array( 'title'     => 'Verkoop vloer oppervlakte',
                                                                      'type'      => 'oppervlakte',
                                                                      'width'     => 100,
                                                                      'object'    => array('Horeca')),
        'bedrijf_HorecaAantalVerdiepingen'                  => array( 'title'     => 'Aantal verdiepingen',
                                                                      'width'     => 100,
                                                                      'object'    => array('Horeca')),
        'bedrijf_HorecaWelstandsklasse'                     => array( 'title'     => 'Welstandsklasse',
                                                                      'object'    => array('Horeca')),
        'bedrijf_HorecaConcentratieGebied'                  => array( 'title'     => 'Concentratie gebied',
                                                                      'type'      => 'bool',
                                                                      'object'    => array('Horeca')),
        'bedrijf_HorecaRegio'                               => array( 'title'     => 'Regio',
                                                                      'object'    => array('Horeca')),
        'bedrijf_HorecaPersoneelTerOvername'                => array( 'title'     => 'Persoon ter overname',
                                                                      'type'      => 'bool',
                                                                      'object'    => array('Horeca')),
        'bedrijf_HorecaPrijsInventarisGoodwill'             => array( 'title'     => 'Prijs inventaris & goodwill',
                                                                      'type'      => 'priceBtw',
                                                                      'object'    => array('Horeca'))
      );
    }
  }
  
  /**
  * @desc YogRelationFieldsSettings
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogRelationFieldsSettings extends YogFieldsSettingsAbstract
  {
    /**
    * @desc Constructor
    * 
    * @param void
    * @return YogRelationFieldsSettings
    */
    public function __construct()
    {
      $this->fieldsSettings = array(
        'relatie_Titel'                 => array(),
        'relatie_Initialen'             => array(),
        'relatie_Voornaam'              => array(),
        'relatie_Voornamen'             => array(),
        'relatie_Tussenvoegsel'         => array(),
        'relatie_Achternaam'            => array(),
        'relatie_Emailadres'            => array(),
        'relatie_Website'               => array(),
        'relatie_Telefoonnummer'        => array(),
        'relatie_Telefoonnummerwerk'    => array('title' => 'Telefoonnummer werk'),
        'relatie_Telefoonnummermobiel'  => array('title' => 'Telefoonnummer mobiel'),
        'relatie_Faxnummer'             => array(),
        'relatie_Hoofdadres_land'       => array('title' => 'Land'),
        'relatie_Hoofdadres_provincie'  => array('title' => 'Provincie'),
        'relatie_Hoofdadres_gemeente'   => array('title' => 'Gemeente'),
        'relatie_Hoofdadres_stad'       => array('title' => 'Stad'),
        'relatie_Hoofdadres_wijk'       => array('title' => 'Wijk'),
        'relatie_Hoofdadres_buurt'      => array('title' => 'Buurt'),
        'relatie_Hoofdadres_straat'     => array('title' => 'Straat'),
        'relatie_Hoofdadres_postcode'   => array('title' => 'Postcode'),
        'relatie_Hoofdadres_huisnummer' => array('title' => 'Huisnummer'),
        'relatie_Postadres_land'        => array('title' => 'Land'),
        'relatie_Postadres_provincie'   => array('title' => 'Provincie'),
        'relatie_Postadres_gemeente'    => array('title' => 'Gemeente'),
        'relatie_Postadres_stad'        => array('title' => 'Stad'),
        'relatie_Postadres_wijk'        => array('title' => 'Wijk'),
        'relatie_Postadres_buurt'       => array('title' => 'Buurt'),
        'relatie_Postadres_straat'      => array('title' => 'Straat'),
        'relatie_Postadres_postcode'    => array('title' => 'Postcode'),
        'relatie_Postadres_huisnummer'  => array('title' => 'Huisnummer')
      );
    }
  }
?>
