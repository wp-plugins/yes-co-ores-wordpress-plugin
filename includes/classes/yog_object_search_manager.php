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
      if (!empty($_REQUEST['object_type']) && in_array($_REQUEST['object_type'], array(POST_TYPE_WONEN, POST_TYPE_BOG, POST_TYPE_NBPR, POST_TYPE_NBTY)))
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
                                  'bedrijf_Wijk', 'bedrijf_Buurt', 'bedrijf_Land', 'bedrijf_Provincie', 'bedrijf_Gemeente', 'bedrijf_Plaats', 'bedrijf_Straat', 'bedrijf_Huisnummer', 'bedrijf_Postcode', 'bedrijf_Type',
                                  'yog-nbpr_Wijk', 'yog-nbpr_Buurt', 'yog-nbpr_Land', 'yog-nbpr_Provincie', 'yog-nbpr_Gemeente', 'yog-nbpr_Plaats', 'yog-nbpr_Straat', 'yog-nbpr_Huisnummer', 'yog-nbpr_Postcode', 'yog-nbpr_ProjectSoort');
    
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
			$where .= ") AND " . $postTbl . ".post_type IN ('post', 'page', 'attachment', '" . POST_TYPE_WONEN . "', '" . POST_TYPE_BOG . "', '" . POST_TYPE_NBPR . "', '" . POST_TYPE_NBTY . "') AND " . $postTbl . ".post_status = 'publish'";
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
          // Exact search on parent
          case 'parent-exact':
            if (!empty($options['parentKey']))
            {
              $metaKey    = $options['parentKey'];
              $selectSql  = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $metaKey . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".post_parent";
              
              $sql  = $this->db->posts . '.post_parent IS NOT NULL AND ';
              $sql .= $this->db->posts . '.post_parent > 0 AND ';
              $sql .= "(" . $selectSql . ") IN ('" . implode("', '", $_REQUEST[$requestKey]) . "')";
              
              $query[] = '(' . $sql . ')';
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
              $query[] = "((" . $selectSql . ") <= " . $max . " OR (" . $selectSql . ") IS NULL)";
            break;
          // Range search on Min / Max fields
          case 'minmax-range':
            $requestKey = str_replace(array('Min', 'Max'), '', $requestKey);
            
            $min        = empty($_REQUEST[$requestKey . '_min']) ? 0 : (int) $_REQUEST[$requestKey . '_min'];
            $max        = empty($_REQUEST[$requestKey . '_max']) ? 0 : (int) $_REQUEST[$requestKey . '_max'];
            
            $metaKey  = str_replace(array('Min', 'Max'), '', $metaKey);
            $minField = $metaKey . 'Min';
            $maxField = $metaKey . 'Max';
            
            $sqlMin   = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $minField . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
            $sqlMax   = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $maxField . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
            
            if ($min > 0)
              $query[] = "(" . $min . " BETWEEN (" . $sqlMin . ") AND (" . $sqlMax . "))";
            if ($max > 0)
              $query[] = "(" . $max . " BETWEEN (" . $sqlMin . ") AND (" . $sqlMax . "))";

            break;
        }
      }
    }
    
    // Handle price search (koop + huur)
    if (!empty($_REQUEST['Prijs_min']) || !empty($_REQUEST['Prijs_max']))
    {
      $min      = empty($_REQUEST['Prijs_min']) ? 0 : (int) $_REQUEST['Prijs_min'];
      $max      = empty($_REQUEST['Prijs_max']) ? 0 : (int) $_REQUEST['Prijs_max'];
        
      if (in_array($objectType, array(POST_TYPE_NBPR, POST_TYPE_NBTY)))
      {
        $koopMinField = ($objectType == POST_TYPE_NBTY) ? 'KoopPrijsMin' : 'KoopAanneemSomMin';
        $koopMaxField = ($objectType == POST_TYPE_NBTY) ? 'KoopPrijsMax' : 'KoopAanneemSomMax';
        $huurMinField = 'HuurPrijsMin';
        $huurMaxField = 'HuurPrijsMax';
        
        $koopSqlMin   = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_" . $koopMinField . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
        $koopSqlMax   = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_" . $koopMaxField . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
        $huurSqlMin   = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_" . $huurMinField . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
        $huurSqlMax   = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_" . $huurMaxField . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
        
        if ($min > 0 && $max > 0)
        {
          $sql  = '(';
          $sql .= '((' . $koopSqlMin . ') BETWEEN ' . $min . ' AND ' . $max . ') OR ';
          $sql .= '((' . $koopSqlMin . ') <= ' . $min . ' AND (' . $koopSqlMax . ') >= ' . $max . ')';
          $sql .= ')';
          $sql .= ' OR ';
          $sql = '(';
          $sql .= '((' . $huurSqlMin . ') BETWEEN ' . $min . ' AND ' . $max . ') OR ';
          $sql .= '((' . $huurSqlMin . ') <= ' . $min . ' AND (' . $huurSqlMax . ') >= ' . $max . ')';
          $sql .= ')';
        }
        else if ($min > 0)
        {
          $sql  = '(' . $min . ' BETWEEN (' . $koopSqlMin . ') AND (' . $koopSqlMax . '))';
          $sql .= ' OR ';
          $sql .= '(' . $min . ' BETWEEN (' . $huurSqlMin . ') AND (' . $huurSqlMax . '))';
        }
        else if ($max > 0)
        {
          $sql  = '(' . $max . ' BETWEEN (' . $koopSqlMin . ') AND (' . $koopSqlMax . '))';
          $sql .= ' OR ';
          $sql .= '(' . $max . ' BETWEEN (' . $huurSqlMin . ') AND (' . $huurSqlMax . '))';
        }
        
        if (!empty($sql))
          $query[] = '(' . $sql . ')';
      }
      else
      {
        $koopSql  = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_KoopPrijs' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
        $huurSql  = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_HuurPrijs' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
        
        if ($min > 0 && $max > 0)
          $query[] = "(((" . $koopSql . ") BETWEEN " . $min . " AND " . $max . ") OR ((" . $huurSql . ") BETWEEN " . $min . " AND " . $max . "))";
        else if ($min > 0 && $max == 0)
          $query[] = "((" . $koopSql . ") >= " . $min . " OR (" . $huurSql . ") >= " . $min . ")";
        else if ($min == 0 && $max > 0)
          $query[] = "((" . $koopSql . ") <= " . $max . " OR (" . $huurSql . ") <= " . $max . " OR ((" . $koopSql . ") IS NULL AND (" . $huurSql . ") IS NULL))";
      }
    }

    // Update where query
    if (!empty($query))
      $where .= ' AND ' . implode(' AND ', $query);
    
    return $where;
  }
  
  /**
  * @desc Retrieve the lowest available price for a specific meta field
  * 
  * @param mixed $metaKeys (string or array)
  * @param $params (optional, default array)
  * @return mixed
  */
  public function retrieveMinMetaValue($metaKeys, $params = array())
  {
    if (!is_array($metaKeys))
      $metaKeys = array($metaKeys);
      
    $postType = substr($metaKeys[0], 0, strpos($metaKeys[0], '_'));
    
    // Determine where parts
    $where    = array();
    $where[]  = $this->db->posts . ".post_type = '" . $postType . "'";
    $where    = array_merge($where, $this->determineGlobalMetaWhere($params, false));
    
    $sql  = "SELECT DISTINCT (";
      $sql .= "SELECT MIN(CAST(meta_value  AS UNSIGNED INTEGER)) FROM " . $this->db->postmeta . " WHERE ";
        $sql .= "meta_key IN ('" . implode("', '", $metaKeys) . "') AND ";
        $sql .= $this->db->postmeta . ".post_id = " . $this->db->posts . ".ID";
      $sql .= ") AS value FROM " . $this->db->posts;
    $sql .= " WHERE " . implode(' AND ', $where);
    
    $results  = $this->db->get_results($sql);

    $min      = null;
    foreach ($results as $result)
    {
      if (empty($result->value))
      {
        $min = 0;
        break;
      }
      else if (is_null($min) || (int) $result->value < $min)
      {
        $min = (int) $result->value;
      }
    }
    
    return $min;
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
  * @param bool $relativeToMeta (optional, default true)
  * @return array
  */
  private function determineGlobalMetaWhere($params, $relativeToMeta = true)
  {
    $where        = array();
    $postIdField  = $relativeToMeta ? $this->db->postmeta . '.post_id' : $this->db->posts . '.ID';
    
    // Category based
    if (!empty($params['cat']))
    {
      $where[]  = $postIdField . " IN (SELECT " . $this->db->term_relationships . ".object_id FROM " . $this->db->term_relationships . " WHERE " . $this->db->term_relationships . ".term_taxonomy_id = " . (int) $params['cat'] . ")";
    }
    
    return $where;
  }
}
?>
