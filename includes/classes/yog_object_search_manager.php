<?php
require_once(YOG_PLUGIN_DIR . '/includes/config/config.php');
require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_fields_settings.php');

/**
* @desc YogObjectSearchManager
* @author Kees Brandenburg - Yes-co Nederland
*/
class YogObjectSearchManager
{
  static public $instance;
  private $db;
  private $searchExtended = false;
  
  /**
  * @desc Constructor
  * 
  * @param void
  * @return YogObjectWonenManager
  */
  private function __construct()
  {
    global $wpdb;
    $this->db = $wpdb;
  }
  
  /**
  * @desc Get the instance of the YogObjectSearch
  * 
  * @param void
  * @return YogObjectSearch
  */
  static public function getInstance()
  {
    if (is_null(self::$instance))
      self::$instance = new self();
      
    return self::$instance;
  }
  
  /**
  * @desc Extend the wordpress search with object functionality
  * 
  * @param void
  * @return void
  */
  public function extendSearch()
  {
    // Make sure the search is only extended once
    if ($this->searchExtended === false)
    {
      add_action('posts_where_request', array($this, 'extendSearchWhere'));
      $this->searchExtended = true;
    }
  }
  
  /**
  * @desc Extend the where to also search on the object custom fields, should nog be called manually
  * 
  * @param string $where
  * @return string
  */
  public function extendSearchWhere($where)
  {
    if (is_search())
    {
      if (!empty($_REQUEST['object_type']) && in_array($_REQUEST['object_type'], array(POST_TYPE_WONEN, POST_TYPE_BOG)))
        $where = $this->extendSearchWhereSearchWidget($where);
      else
        $where = $this->extendSearchWhereDefault($where);
    }
    
    return $where; 
  }
  
  /**
  * @desc Extend normal search queries
  * 
  * @param string $where
  * @return string
  */
  private function extendSearchWhereDefault($where)
  {
    global $wp;
   
    // Check if search field is filled 
		if (!$wp->query_vars['s'] || $wp->query_vars['s'] == '%25' || $wp->query_vars['s'] == '%')
			return $where;
    
    // Escape search term
    if (function_exists('_real_escape'))
		  $searchTerm         = _real_escape($wp->query_vars['s']);
    else
      $searchTerm         = addslashes($wp->query_vars['s']);
      
		$supportedMetaFields  = array('huis_Wijk','huis_Buurt','huis_Land','huis_Provincie','huis_Gemeente','huis_Plaats','huis_Straat','huis_Huisnummer','huis_Postcode','huis_SoortWoning','huis_TypeWoning','huis_KenmerkWoning',
                                  'bedrijf_Wijk', 'bedrijf_Buurt', 'bedrijf_Land', 'bedrijf_Provincie', 'bedrijf_Gemeente', 'bedrijf_Plaats', 'bedrijf_Straat', 'bedrijf_Huisnummer', 'bedrijf_Postcode', 'bedrijf_Type');
    
    $metaTbl              = $this->db->postmeta;
    $postTbl              = $this->db->posts;
    $whereQuery           = array();
    
		foreach ($supportedMetaFields as $metaField)
    {
			$whereQuery[] = "meta_key = '" . $metaField . "' AND meta_value LIKE '%" . $searchTerm . "%'";				
    }
    
		$query = "SELECT DISTINCT post_id FROM " . $metaTbl . " WHERE (" . implode(') OR (', $whereQuery) . ')';
    
		// Retrieve post ids
		$postIds =  $this->db->get_col($query, 0);
    
		if (is_array($postIds) && count($postIds))
    {
			$where  = " AND (" . $postTbl . ".ID IN (" . implode(',', $postIds)  . ")";
			$where .= " OR " . $postTbl . ".post_title LIKE '%" .$searchTerm ."%' OR " . $postTbl . ".post_content LIKE '%" .$searchTerm ."%'";
			$where .= ") AND " . $postTbl . ".post_type IN ('post', 'page', 'attachment', '" . POST_TYPE_WONEN . "', '" . POST_TYPE_BOG . "', '" . POST_TYPE_RELATION . "') AND " . $postTbl . ".post_status = 'publish'";
		}
    
		return $where;
  }
  
  /**
  * @desc Extend search for widgets
  * 
  * @param string $where
  * @return string
  */
  private function extendSearchWhereSearchWidget($where)
  {
    $objectType     = $_REQUEST['object_type'];
    $fieldsSettings = YogFieldsSettingsAbstract::create($objectType);
    $tbl            = $this->db->postmeta;

    $query = array();
    $query[] = $this->db->posts . ".post_type = '" . $objectType . "'";
    
    // Determine parts of query for custom fields
    foreach ($fieldsSettings->getFields() as $metaKey => $options)
    {
      $requestKey = str_replace($objectType . '_', '', $metaKey);
      
      if (!empty($options['search']))
      {
        $selectSql = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $metaKey . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
        
        switch ($options['search'])
        {
          // Exact search
          case 'exact':
            if (!empty($_REQUEST[$requestKey]))
            {
              if (!is_array($_REQUEST[$requestKey]))
                $_REQUEST[$requestKey] = array($_REQUEST[$requestKey]);
              
              $query[] = "(" . $selectSql . ") IN ('" . implode("', '", $_REQUEST[$requestKey]) . "')";
            }
            break;
          // Range search
          case 'range':
            $min = empty($_REQUEST[$requestKey . '_min']) ? 0 : (int) $_REQUEST[$requestKey . '_min'];
            $max = empty($_REQUEST[$requestKey . '_max']) ? 0 : (int) $_REQUEST[$requestKey . '_max'];
            
            if ($min > 0 && $max > 0)
              $query[] = "((" . $selectSql . ") BETWEEN " . $min . " AND " . $max . ")";
            else if ($min > 0 && $max == 0)
              $query[] = "((" . $selectSql . ") >= " . $min . ")";
            else if ($min == 0 && $max > 0)
              $query[] = "((" . $selectSql . ") <= " . $max . ")";
            break;
        }
      }
    }
    
    // Handle price search (koop + huur)
    if (!empty($_REQUEST['Prijs_min']) || !empty($_REQUEST['Prijs_max']))
    {
      $min      = empty($_REQUEST['Prijs_min']) ? 0 : (int) $_REQUEST['Prijs_min'];
      $max      = empty($_REQUEST['Prijs_max']) ? 0 : (int) $_REQUEST['Prijs_max'];
      $koopSql  = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_KoopPrijs' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
      $huurSql  = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_HuurPrijs' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
      
      if ($min > 0 && $max > 0)
        $query[] = "(((" . $koopSql . ") BETWEEN " . $min . " AND " . $max . ") OR ((" . $huurSql . ") BETWEEN " . $min . " AND " . $max . "))";
      else if ($min > 0 && $max == 0)
        $query[] = "((" . $koopSql . ") >= " . $min . " OR (" . $huurSql . ") >= " . $min . ")";
      else if ($min == 0 && $max > 0)
        $query[] = "((" . $koopSql . ") <= " . $max . " OR (" . $huurSql . ") <= " . $max . ")";
    }
    
    // Update where query
    if (!empty($query))
      $where .= ' AND ' . implode(' AND ', $query);
    
    return $where;
  }
  
  /**
  * @desc Retrieve the lowest price
  * 
  * @param $params (optional, default array)
  * @return int
  */
  public function retrieveMinPrijs($params = array(), $field = array())
  {
    return $this->retrieveMinMetaValue($field, $params);
  }
  
  /**
  * @desc Retrieve the Highest price
  * 
  * @param $params (optional, default array)
  * @return int
  */
  public function retrieveMaxPrijs($params = array(), $fields = array())
  {
    return $this->retrieveMaxMetaValue($fields, $params);
  }
  
  /**
  * @desc Retrieve the lowest number of rooms
  * 
  * @param $params (optional, default array)
  * @return int
  */
  public function retrieveMinKamers($params = array())
  {
    return $this->retrieveMinMetaValue('huis_Aantalkamers', $params);
  }
  
  /**
  * @desc Retrieve the highest number of rooms
  * 
  * @param $params (optional, default array)
  * @return int
  */
  public function retrieveMaxKamers($params = array())
  {
    return $this->retrieveMaxMetaValue('huis_Aantalkamers', $params);
  }
  
  /**
  * @desc Retrieve the lowest 'Oppervlakte'
  * 
  * @param $params (optional, default array)
  * @return int
  */
  public function retrieveMinOppervlakte($params = array())
  {
    return $this->retrieveMinMetaValue('huis_Oppervlakte', $params);
  }
  
  /**
  * @desc Retrieve the highest 'Oppervlakte'
  * 
  * @param $params (optional, default array)
  * @return int
  */
  public function retrieveMaxOppervlakte($params = array())
  {
    return $this->retrieveMaxMetaValue('huis_Oppervlakte', $params);
  }
  
  /**
  * @desc Retrieve the lowest 'Inhoud'
  * 
  * @param $params (optional, default array)
  * @return int
  */
  public function retrieveMinInhoud($params = array())
  {
    return $this->retrieveMinMetaValue('huis_Inhoud', $params);
  }
  
  /**
  * @desc Retrieve the highest 'Inhoud'
  * 
  * @param $params (optional, default array)
  * @return int
  */
  public function retrieveMaxInhoud($params = array())
  {
    return $this->retrieveMaxMetaValue('huis_Inhoud', $params);
  }
  
  /**
  * @desc Retrieve all available 'Soort woning'
  * 
  * @param $params (optional, default array)
  * @return array
  */
  public function retrieveSoortWoningList($params = array())
  {
    return $this->retrieveMetaList('huis_SoortWoning', $params);
  }
  
  /**
  * @desc Retrieve all available 'Type woning'
  * 
  * @param $params (optional, default array)
  * @return array
  */
  public function retrieveTypeWoningList($params = array())
  {
    return $this->retrieveMetaList('huis_TypeWoning', $params);
  }
  
  /**
  * @desc Retrieve all available 'Plaats'
  * 
  * @param $params (optional, default array)
  * @return array
  */
  public function retrievePlaatsList($params = array())
  {
    return $this->retrieveMetaList('huis_Plaats', $params);
  }
  
  /**
  * @desc Retrieve the lowest available value for a specific meta field
  * 
  * @param mixed $metaKeys (string or array)
  * @param $params (optional, default array)
  * @return mixed
  */
  public function retrieveMinMetaValue($metaKeys, $params = array())
  {
    if (!is_array($metaKeys))
      $metaKeys = array($metaKeys);
    
    // Determine where parts
    $where    = array();
    $where[]  = $this->db->postmeta . ".meta_key IN ('" . implode("', '", $metaKeys) . "')";
    $where[]  = $this->db->postmeta . ".meta_value != ''";
    $where    = array_merge($where, $this->determineGlobalMetaWhere($params));
    
    $sql  = "SELECT " . $this->db->postmeta . ".meta_value FROM " . $this->db->postmeta . " WHERE ";
    $sql .= implode(' AND ', $where) . ' ';
    $sql .= "ORDER BY CAST(meta_value AS UNSIGNED INTEGER) LIMIT 1";
    
    return (int) $this->db->get_var($sql);
  }
  
  /**
  * @desc Retrieve the highest available value for a specific meta field
  * 
  * @param mixed $metaKeys (string or array)
  * @param $params (optional, default array)
  * @return mixed
  */
  public function retrieveMaxMetaValue($metaKeys, $params = array())
  {
    if (!is_array($metaKeys))
      $metaKeys = array($metaKeys);
      
    // Determine where parts
    $where    = array();
    $where[]  = $this->db->postmeta . ".meta_key IN ('" . implode("', '", $metaKeys) . "')";
    $where[]  = $this->db->postmeta . ".meta_value != ''";
    $where    = array_merge($where, $this->determineGlobalMetaWhere($params));
    
    $sql  = "SELECT " . $this->db->postmeta . ".meta_value FROM " . $this->db->postmeta . " WHERE ";
    $sql .= implode(' AND ', $where) . ' ';
    $sql .= "ORDER BY CAST(meta_value AS UNSIGNED INTEGER) DESC LIMIT 1";
    
    return (int) $this->db->get_var($sql);
  }
  
  /**
  * @desc Retrieve all available values for a specfic meta field
  * 
  * @param string $metaKey
  * @param $params (optional, default array)
  * @return array
  */
  public function retrieveMetaList($metaKey, $params = array())
  {
    // Determine where parts
    $where    = array();
    $where[]  = $this->db->postmeta . ".meta_key = '" . $metaKey . "'";
    $where[]  = $this->db->postmeta . ".meta_value != ''";
    $where    = array_merge($where, $this->determineGlobalMetaWhere($params));
    
    $sql  = "SELECT DISTINCT " . $this->db->postmeta . ".meta_value FROM " . $this->db->postmeta . " WHERE ";
    $sql .= implode(' AND ', $where) . ' ';
    $sql .= "ORDER BY meta_value";
    
    $results  = $this->db->get_results($sql);
    $values   = array();
    
    foreach ($results as $result)
    {
      $values[] = $result->meta_value;
    }

    return $values;
  }
  
  /**
  * @desc Determine global where for meta selection
  * 
  * @param array $params
  * @return array
  */
  private function determineGlobalMetaWhere($params)
  {
    $where = array();
    
    // Category based
    if (!empty($params['cat']))
    {
      $where[]  = $this->db->postmeta . ".post_id IN (SELECT " . $this->db->term_relationships . ".object_id FROM " . $this->db->term_relationships . " WHERE " . $this->db->term_relationships . ".term_taxonomy_id = " . (int) $params['cat'] . ")";
    }
    
    return $where;
  }
}
?>
