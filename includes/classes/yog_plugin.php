<?php
  require_once(YOG_PLUGIN_DIR . '/includes/config/config.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_object_search_manager.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_system_link_manager.php');

  /**
  * @desc YogPlugin
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogPlugin
  {
    static private $instance;
    
    protected $wpVersion;
    
    /**
    * @desc Constructor
    * 
    * @param void
    * @return YogPlugin
    */
    private function __construct()
    {
      // Include widgets
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_search_form_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_search_form_bog_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_search_form_nbpr_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_search_form_nbty_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_recent_objects_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_linked_objects_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_linked_relations_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_contact_form_widget.php');
      require_once(YOG_PLUGIN_DIR . '/includes/widgets/yog_object_attachments_widget.php');
      
      global $wp_version;
      $this->wpVersion = (float) $wp_version;
    }
    
    /**
    * @desc Get an instance of the YogPlugin
    * 
    * @param void
    * @return YogPlugin
    */
    static public function getInstance()
    {
      if (is_null(self::$instance))
      {
        if (is_admin())
          self::$instance = new YogPluginAdmin();
        else
          self::$instance = new YogPluginPublic();
      }
        
      return self::$instance;
    }
    
    /**
    * @desc Initialize Wordpress plugin
    * 
    * @param void
    * @return void
    */
    public function init()
    {
      add_theme_support('post-thumbnails');
      add_action('init', array($this, 'registerPostTypes'));
      
      
      add_action('widgets_init', array($this, 'registerWidgets'));
      add_filter('rewrite_rules_array', array($this, 'insertCustomRewriteRules'));
      add_filter('post_type_link', array($this, 'fixPermalinks'), 1, 3);
    }
  
    /**
    * @desc Fix NBty permalinks
    * 
    * @param string $permalink
    * @param StdClass $post
    * @param bool $leavename
    * @return string
    */
    public function fixPermalinks($permalink, $post, $leavename)
    {
	    if  ($post->post_type == POST_TYPE_NBTY)
      {
        $permalink = str_replace( array('/nieuwbouw-type/', '/' . $post->post_name), 
                                  array('/nieuwbouw/', '/type/' . $post->post_name), 
                                  $permalink);
        
        if (strpos($permalink, '%' . POST_TYPE_NBTY . '%') !== false && !empty($post->post_parent))
        {

          
          $permalink = str_replace('%' . POST_TYPE_NBTY . '%', '%pagename%', $permalink);
        }
          
	    }
      
	    return $permalink;
    }

    /**
    * @desc Add custom rewrite rules for NBty
    * 
    * @param array $rules
    * @return array
    */
    public function insertCustomRewriteRules($rules)
    {
	    $newrules = array();
	    $newrules['nieuwbouw/(.+?)/type/(.+?)$'] = 'index.php?' . POST_TYPE_NBTY . '=$matches[2]';
      
	    return $newrules + $rules;
    }
    
    /*
    add_action('wp_loaded', array($this, 'my_flush_rules'));
    function my_flush_rules()
    {
	    $rules = get_option( 'rewrite_rules' );

	    if ( ! isset( $rules['nieuwbouw/(.+?)/type/(.+?)$'] ) )
      {
		    global $wp_rewrite;
	   	  $wp_rewrite->flush_rules();
	    }
    }
    */
    
    /**
    * @desc Enqueue files
    * 
    * @param void
    * @return void    
    */
    public function enqueueFiles()
    {
      wp_enqueue_script('jquery', YOG_PLUGIN_URL . '/javascript/' .'jquery-1.4.1' .'.js');
    }
    
    /**
    * @desc Register post types
    * 
    * @param void
    * @return void
    */
    public function registerPostTypes()
    {
	    register_post_type(POST_TYPE_WONEN,
	                  array('labels'    => array('name'               => 'Wonen',
	                                            'singular_name'       => 'Woon object',
                                              'add_new'             => 'Toevoegen',
                                              'add_new_item'        => 'Object toevoegen',
                                              'search_items'        => 'Objecten zoeken',
                                              'not_found'           => 'Geen objecten gevonden',
                                              'not_found_in_trash'  => 'Geen objecten gevonden in de prullenbak',
                                              'edit_item'           => 'Object bewerken',
                                              'view_item'           => __('View')
                                              ),
                          'public'            => true,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => false,
	                        'rewrite'           => array('slug' => POST_TYPE_WONEN), // Permalinks format
	                        'supports'          => array('title','editor', 'thumbnail'),
	                        'taxonomies'        => array('category', 'post_tag')
	                        )
	    );
      
	    register_post_type(POST_TYPE_BOG,
	                  array('labels'    => array('name'               => 'BOG',
	                                            'singular_name'       => 'BOG object',
                                              'add_new'             => 'BOG object toevoegen',
                                              'add_new_item'        => 'Object toevoegen',
                                              'search_items'        => 'Objecten zoeken',
                                              'not_found'           => 'Geen objecten gevonden',
                                              'not_found_in_trash'  => 'Geen objecten gevonden in de prullenbak',
                                              'edit_item'           => 'Object bewerken',
                                              'view_item'           => __('View')
                                              ),
                          'public'            => true,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => false,
	                        'rewrite'           => array('slug' => POST_TYPE_BOG), // Permalinks format
	                        'supports'          => array('title','editor', 'thumbnail'),
	                        'taxonomies'        => array('category', 'post_tag')
	                        )
	    );
      
	    register_post_type(POST_TYPE_NBPR,
	                  array('labels'    => array('name'               => 'Nieuwbouw',
	                                            'singular_name'       => 'Nieuwbouw project',
                                              'add_new'             => 'Nieuwbouw project toevoegen',
                                              'add_new_item'        => 'Project toevoegen',
                                              'search_items'        => 'Projecten zoeken',
                                              'not_found'           => 'Geen nieuwbouw projecten gevonden',
                                              'not_found_in_trash'  => 'Geen nieuwbouw projecten gevonden in de prullenbak',
                                              'edit_item'           => 'Project bewerken',
                                              'view_item'           => __('View')
                                              ),
                          'public'            => true,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => false,
	                        'rewrite'           => array('slug' => 'nieuwbouw'), // Permalinks format
	                        'supports'          => array('title','editor', 'thumbnail'),
	                        'taxonomies'        => array('category', 'post_tag')
	                        )
	    );
      
	    register_post_type(POST_TYPE_NBTY,
	                  array('labels'    => array('name'               => 'Nieuwbouw types',
	                                            'singular_name'       => 'Nieuwbouw type',
                                              'add_new'             => 'Nieuwbouw type toevoegen',
                                              'add_new_item'        => 'Type toevoegen',
                                              'search_items'        => 'Types zoeken',
                                              'not_found'           => 'Geen nieuwbouw types gevonden',
                                              'not_found_in_trash'  => 'Geen nieuwbouw types gevonden in de prullenbak',
                                              'edit_item'           => 'Type bewerken',
                                              'view_item'           => __('View')
                                              ),
                          'public'            => true,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => true,
	                        'rewrite'           => array('slug' => 'nieuwbouw-type', 'with_front' => false), // Permalinks format
	                        'supports'          => array('title','editor', 'thumbnail'),
	                        'taxonomies'        => array('category', 'post_tag')
	                        )
	    );
      
	    register_post_type(POST_TYPE_NBBN,
	                  array('labels'    => array('name'               => 'Nieuwbouw bouwnummers',
	                                            'singular_name'       => 'Nieuwbouw bouwnummer',
                                              'add_new'             => 'Nieuwbouw bouwnummer toevoegen',
                                              'add_new_item'        => 'Bouwnummer toevoegen',
                                              'search_items'        => 'Bouwnummers zoeken',
                                              'not_found'           => 'Geen nieuwbouw bouwnummers gevonden',
                                              'not_found_in_trash'  => 'Geen nieuwbouw bouwnummers gevonden in de prullenbak',
                                              'edit_item'           => 'Bouwnummer bewerken'
                                              ),
                          'public'            => false,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => false,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => true,
	                        'rewrite'           => array('slug' => 'nieuwbouw-bouwnummer'), // Permalinks format
	                        'supports'          => array('title')
	                        )
	    );
	    
	    register_post_type('relatie',
	                  array('labels'    => array( 'name'                => 'Relaties',
	                                              'singular_name'       => 'Relatie',
                                                'add_new'             => 'Toevoegen',
                                                'add_new_item'        => 'Relatie toevoegen',
                                                'search_items'        => 'Relaties zoeken',
                                                'not_found'           => 'Geen relaties gevonden',
                                                'not_found_in_trash'  => 'Geen relaties gevonden in de prullenbak'
                                                ),
	                        'public'            => false,
	                        'show_ui'           => true, // UI in admin panel
                          'show_in_menu'      => 'yog_posts_menu',
	                        'show_in_nav_menus' => true,
	                        'capability_type'   => 'post',
                          'menu_icon'         => YOG_PLUGIN_URL . '/img/icon_yes-co.gif',
	                        'hierarchical'      => false,
	                        'rewrite'           => array('slug' => POST_TYPE_RELATION), // Permalinks format
	                        'supports'          => array('title')
	                        )
	    );
    }
    
    /**
    * @desc Register widgets
    * 
    * @param void
    * @return void
    */
    public function registerWidgets()
    {
      register_widget('YogRecentObjectsWidget');
      register_widget('YogSearchFormWonenWidget');
      register_widget('YogSearchFormBogWidget');
      register_widget('YogSearchFormNBprWidget');
      register_widget('YogSearchFormNBtyWidget');
      register_widget('YogContactFormWidget');
      register_widget('YogObjectAttachmentsWidget');
      register_widget('YogLinkedObjectsWidget');
      register_widget('YogLinkedRelationsWidget');
    }
  }
  
  /**
  * @desc YogPluginAdmin
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogPluginPublic extends YogPlugin
  {
    /**
    * @desc Initialize Wordpress public
    * 
    * @param void
    * @return void
    */
    public function init()
    {
      parent::init();
      
      add_filter('pre_get_posts',           array($this, 'extendPostQuery'));
      add_filter('the_content',             array($this, 'extendTheContent'));
      add_action('wp_head',                 array($this, 'includeDojo'));
      add_action('init',                    array($this, 'enqueueFiles'));
      
      $searchManager = YogObjectSearchManager::getInstance();
      $searchManager->extendSearch();
      
      
      $this->updateOpenhuizen();
    }
    
    /**
    * @desc Enqueue files
    * 
    * @param void
    * @return void    
    */
    public function enqueueFiles()
    {
      parent::enqueueFiles();
      
      wp_enqueue_script('yog-image-slider', YOG_PLUGIN_URL .'/inc/js/image_slider.js', array(), YOG_PLUGIN_VERSION);
      wp_enqueue_style('yog-photo-slider',  YOG_PLUGIN_URL . '/inc/css/photo_slider.css', array(), YOG_PLUGIN_VERSION);
    }
    
    /**
    * @desc Extend the content, if theme contains no single-*.php template
    * 
    * @param string $content
    * @return string
    */
    public function extendTheContent($content)
    {
      $postType = get_post_type();
      $prefix   = '';
      $suffix   = '';
      
      if (is_single() && !is_file(get_template_directory() .'/single-' . $postType . '.php'))
      {
        switch ($postType)
        {
          case POST_TYPE_WONEN:            
            // Add photo slider
            $prefix .= yog_retrievePhotoSlider();
            
            // Add prices
            $prices = yog_retrievePrices();
            if (count($prices) > 0)
              $prefix .= '<div class="yog-prices">' . implode('<br />', $prices) . '</div>';
              
            // Add open house
            if (yog_hasOpenHouse())
              $prefix .= '<div class="yog-open-house">' . yog_getOpenHouse() . '</div>';
              
            // Add location
            $suffix = yog_retrieveDynamicMap();
            break;
          case POST_TYPE_BOG:
          case POST_TYPE_NBPR:
            // Add photo slider
            $prefix .= yog_retrievePhotoSlider();
            
            // Add prices
            $prices = yog_retrievePrices();
            if (count($prices) > 0)
              $prefix .= '<div class="yog-prices">' . implode('<br />', $prices) . '</div>';
              
            // Add location
            $suffix = yog_retrieveDynamicMap();
            break;
          case POST_TYPE_NBTY:
            // Add photo slider
            $prefix .= yog_retrievePhotoSlider();
            
            // Add prices
            $prices = yog_retrievePrices();
            if (count($prices) > 0)
              $prefix .= '<div class="yog-prices">' . implode('<br />', $prices) . '</div>';
              
            // Add NBbn
            $table = yog_retrieveNbbnTable();
            if (!empty($table))
            {
              $suffix .= '<h2>Bouwnummers</h2>';
              $suffix .= $table;
            }
            
            break;
        }
      }
      
      return $prefix . $content . $suffix;
    }
    
    /**
     * @desc Method which ensures the dojo library is included
     *
     * @param void
     * @return void
     */
    public function includeDojo()
    {
      $html = '<script type="text/javascript">
              // <![CDATA[
                var djConfig = {
                parseOnLoad: false,
                cacheBust: "1",
                baseUrl: "' . home_url() . '/",
                modulePaths: { svzsolutions: "' . substr(YOG_PLUGIN_URL, strpos(YOG_PLUGIN_URL, 'wp-content')) . '/inc/svzsolutions" }
                };
              // ]]>
              </script>
              <script src="http://ajax.googleapis.com/ajax/libs/dojo/1.5.0/dojo/dojo.xd.js"></script>';

      echo $html;
    }
    
    /**
    * @desc Register the post types to use on several pages
    * 
    * @param WP_Query $query
    * @return WP_Query
    */
    public function extendPostQuery($query)
    {
      $extendQuery = true;
      
      if (!(!isset($query->query_vars['suppress_filters']) || $query->query_vars['suppress_filters'] == false))
        $extendQuery = false;
      else if (!($query->is_archive || $query->is_category || $query->is_feed || $query->is_home))
        $extendQuery = false;
      else if ($query->is_archive && !$query->is_category && !get_option('yog_objectsinarchief'))
        $extendQuery = false;
      else if ($query->is_home && !get_option('yog_huizenophome'))
        $extendQuery = false;
      
      if ($extendQuery === true)
      {
        $postTypes  = $query->get('post_type');
        if (empty($postTypes))
          $postTypes = array('post');
        else if (!is_array($postTypes))
          $postTypes = array($postTypes);
        
        if (!in_array(POST_TYPE_WONEN, $postTypes))
          $postTypes[] = POST_TYPE_WONEN;
          
        if (!in_array(POST_TYPE_BOG, $postTypes))
          $postTypes[] = POST_TYPE_BOG;
          
        if (!in_array(POST_TYPE_NBPR, $postTypes))
          $postTypes[] = POST_TYPE_NBPR;
          
        if (!in_array(POST_TYPE_NBTY, $postTypes))
          $postTypes[] = POST_TYPE_NBTY;
          
		    $query->set('post_type', $postTypes);
      }
    }
    
    /**
    * @desc Update open house categories for open house dates in the past
    * 
    * @param void
    * @return void
    */
    public function updateOpenhuizen()
    {
	    // Retrieve all objects with open house category
	    $objecten = get_posts(array('post_type'   => POST_TYPE_WONEN,
                                  'category'    => 'open-huis',
                                  'numberposts' => -1));
      
	    foreach ($objecten as $object)
      {
        $openHouseStart = get_post_meta($object->ID,'huis_OpenHuisTot', true);
        $openHouseEnd   = get_post_meta($object->ID,'huis_OpenHuisTot',true);
        
        // Update categories if open house date is old
        if ((empty($openHouseStart) || strtotime($openHouseStart) < time()) && (empty($openHouseEnd) || strtotime($openHouseEnd) < time()))
        {
          $categories     = wp_get_object_terms( $object->ID, 'category' );
          $categorySlugs  = array();
          
          foreach ($categories as $category)
          {
            if ($category->slug != 'open-huis')
              $categorySlugs[] = $category->slug;
          }
          
          wp_set_object_terms( $object->ID, $categorySlugs, 'category', false);
        }
	    }
    }
  }
  
  /**
  * @desc YogPluginAdmin
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogPluginAdmin extends YogPlugin
  {
    /**
    * @desc Initialize Wordpress admin
    * 
    * @param void
    * @return void
    */
    public function init()
    {
      require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_wp_admin_object_ui.php');
      
      parent::init();
      
      add_action('admin_menu',              array($this, 'createAdminMenu'));
      add_action('wp_ajax_togglehome',      array($this, 'ajaxToggleHome'));
      add_action('wp_ajax_togglearchive',   array($this, 'ajaxToggleArchive'));
      add_action('wp_ajax_addkoppeling',    array($this, 'addSystemLink'));
      add_action('wp_ajax_removekoppeling', array($this, 'ajaxRemoveSystemLink'));
      add_action('init',                    array($this, 'enqueueFiles'));
      add_action('init',                    array($this, 'checkPluginVersion'));
      add_filter('editable_slug',           array($this, 'fixEditableparmalinkSlug'));
      add_action('wp_dashboard_setup',      array($this, 'initDashboardWidgets'));
      
      // Init custom post type admin pages
      if (!empty($_REQUEST['post_type']) || !empty($_REQUEST['post']))
      {
        $postType  = empty($_REQUEST['post_type']) ? get_post_type((int) $_REQUEST['post']) : $_REQUEST['post_type'];
        $wpAdminUi = YogWpAdminUiAbstract::create($postType);
        if (!is_null($wpAdminUi))
          $wpAdminUi->initialize();
      }
    }
    
    /**
    * @desc Check the current plugin version
    * 
    * @param void
    * @return void
    */
    public function checkPluginVersion()
    {
      // Check plugin version
      $currentVersion = get_option('yog_plugin_version');
      if ($currentVersion != YOG_PLUGIN_VERSION)
      {
        // Make sure rewrite rules are up-to-date
        $this->registerPostTypes();
        flush_rewrite_rules();
        
        // Update plugin version
        update_option('yog_plugin_version', YOG_PLUGIN_VERSION);
      }
    }
    
    /**
    * @desc Fix editable permalink slug for NBty
    * 
    * @param string $slug
    * @return string
    */
    public function fixEditableparmalinkSlug($slug)
    {
      if (!empty($GLOBALS['post']))
      {
        $post = $GLOBALS['post'];
      }
      else if (!empty($_POST['post_id']))
      {
        $postId   = (int) $_POST['post_id'];
        $post     = get_post($postId);
      }
      
      if (isset($post) && $post->post_type == POST_TYPE_NBTY && $slug != $post->post_name && (empty($_POST['new_slug']) || $_POST['new_slug'] != $slug))
        $slug = $slug . '/type';
      
      return $slug;
    }
    
    /**
    * @desc Init the dashboard widgets
    * 
    * @param void
    * @return void
    */
    public function initDashboardWidgets()
    {
      wp_add_dashboard_widget('yog-last-updated-objects', 'Laatst gewijzigde objecten', array($this, 'lastUpdatedProjectsDashboardWidget'));
    }
    
    public function lastUpdatedProjectsDashboardWidget()
    {
      $objects = get_posts(array( 'numberposts' => 5,
                                  'post_type'   => array(POST_TYPE_WONEN, POST_TYPE_BOG, POST_TYPE_NBPR, POST_TYPE_NBTY),
                                  'orderby'     => 'modified'));
      
	    // Display whatever it is you want to show
      if (is_array($objects) && count($objects) > 0)
      {
        $thumbnailWidth   = get_option('thumbnail_size_w', 0);
        $noImageHtml      = '<div class="no-image" style="width:' . $thumbnailWidth . 'px;"></div>';
      
        echo '<table class="wp-list-table widefat fixed posts">';
          echo '<tbody>';
        
          foreach ($objects as $object)
          {
            $thumbnail = get_the_post_thumbnail($object->ID, 'thumbnail');
            if (empty($thumbnail))
              $thumbnail = $noImageHtml;
              
            $scenario = yog_retrieveSpec('scenario', $object->ID);
              
            // Determine admin links
            $links = array();
            
            if ($object->post_status != 'trash')
              $links[] = '<a href="' . get_edit_post_link($object->ID) . '">' . __('Edit') . '</a>';
            if ($scenario != 'NBbn' && $object->post_status != 'trash')
              $links[] = '<a href="' . get_permalink($object->ID) . '">' . __('View') . '</a>';
              
            // Determine title
            $title = $object->post_title;
            if ($object->post_status != 'trash')
              $title = '<a href="' . get_edit_post_link($object->ID) . '">' . $title . '</a>';
              
            echo '<tr>';
            echo '<td style="width:' . ($thumbnailWidth + 10) . 'px;">' . $thumbnail . '</td>';
            echo '<td>';
              echo '<strong>' . $title . '</strong>';
              echo '<div class="row-actions"><span>' . implode(' | </span><span>', $links) . '</span></div>';
            echo '</td>';
            echo '</tr>';
          }
          
          echo '</tbody>';
        echo '</table>';
      }
      else
      {
        echo '<p>Er zijn nog geen objecten gepubliceerd</p>'; 
      }
      

        
    } 

    /**
    * @desc Enqueue files
    * 
    * @param void
    * @return void    
    */
    public function enqueueFiles()
    {
      parent::enqueueFiles();
      
      wp_enqueue_script('yog-admin-js',   YOG_PLUGIN_URL .'/inc/js/admin.js', array('jquery'), YOG_PLUGIN_VERSION);
      wp_enqueue_style('yog-admin-css',   YOG_PLUGIN_URL . '/inc/css/admin.css', array(), YOG_PLUGIN_VERSION);
    }
    
    /**
    * @desc Create admin menu
    * 
    * @param void
    * @return void
    */
    public function createAdminMenu()
    {
      if ($this->wpVersion >= 3.1)
        add_object_page('Yes-co ORES', 'Yes-co ORES', 'edit_posts', 'yog_posts_menu', '', YOG_PLUGIN_URL . '/img/icon_yes-co.gif');
      
      add_options_page('Yes-co ORES opties', 'Yes-co ORES', 'edit_plugins', 'yesco_OG', array($this, 'renderSettingsPage'));
    }
    
    /**
    * @desc Render plugin settings page
    * 
    * @param void
    * @return void
    */
    public function renderSettingsPage()
    {
      require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_system_link_manager.php');
      
      /* Start checks */
      $errors   = array();
      $warnings = array();
      
      // Upload folder writable
      $uploadDir = wp_upload_dir();

      if (!empty($uploadDir['error']))
        $errors[] = $uploadDir['error'];
	    else if (!is_writeable($uploadDir['basedir']))
        $errors[] = 'De upload map van uw WordPress installatie is beveiligd tegen schrijven. Dat betekent dat er geen afbeelingen van de objecten gesynchroniseerd kunnen worden. Stel onderstaande locatie zo in, dat deze beschreven kan worden door de webserver. <br /><i><b>' . $uploadDir['basedir'] .'</b></i>';
      
      // PHP version check
      if (!version_compare(PHP_VERSION, '5.2.1', '>='))
        $errors[] = 'PHP versie ' . PHP_VERSION . ' is gedetecteerd, de plugin vereist minimaal PHP versie 5.2.1. Neem contact op met je hosting provider om de PHP versie te laten upgraden';
      
      // Lib XML check
      if (!extension_loaded('libxml'))
        $errors[] = 'De php librairy <b>libxml</b> is niet geinstalleerd. Neem contact op met je hosting provider om libxml te laten installeren';
        
      // allow_url_fopen / CURL check
      if (!ini_get('allow_url_fopen') && !function_exists('curl_init'))
        $errors[] = 'De php setting <b>allow_url_fopen</b> staat uit en de php librairy <b>CURL</b> is niet geinstalleerd. Voor de synchronisatie is 1 van deze 2 noodzakelijk. Neem contact op met je hosting provider hierover.';
        
      // Single huis template check
      if (!is_file(get_template_directory() .'/single-huis.php'))
        $warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-huis.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de Wonen object details.';
        
      // Single bedrijf template check
      if (!is_file(get_template_directory() .'/single-bedrijf.php'))
        $warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-bedrijf.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de BOG object details.';
        
      // Single NBpr template check
      if (!is_file(get_template_directory() .'/single-yog-nbpr.php'))
        $warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-yog-nbpr.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de Nieuwbouw Project details.';
        
      // Single NBpr template check
      if (!is_file(get_template_directory() .'/single-yog-nbty.php'))
        $warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-yog-nbty.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de Nieuwbouw type details.';
        
      // Wordpress version
      global $wp_version;
      if ((float) $wp_version < 3.0)
        $errors[] = 'Wordpress versie ' . $wp_version . ' is gedetecteerd, voor deze plugin is Wordpress versie 3.0 of hoger vereist. Upgrade wordpress naar een nieuwere versie';
      else if ((float) $wp_version < 3.1)
        $warnings[] = 'Wordpress versie ' . $wp_version . ' is gedetecteerd, voor deze plugin raden we minimaal Wordpress versie 3.1 aan.';
      
      /* End checks */
      
      // Render html
	    echo '<div class="wrap">';
        echo '<div class="icon32 icon32-config-yog"><br /></div>';
	      echo '<h2>Yes-co Open Real Estate System instellingen</h2>';
	      wp_nonce_field('update-options');
          
        if (!empty($errors))
        {
		      echo '<div id="message" class="error below-h2" style=" padding: 5px 10px;">';
            echo '<b>Er zijn fouten geconstateerd waardoor de Yes-co ORES plugin niet naar behoren kan functioneren</b>:';
            echo '<ul style="padding-left:15px;list-style-type:circle"><li>' . implode('</li><li>', $errors) . '</li></ul>';
          echo '</div>';
        }
        
        if (!empty($warnings))
        {
		      echo '<div id="message" class="error below-h2" style="padding: 5px 10px; background-color:#feffd1;border-color:#d5d738;">';
            echo '<ul style="padding-left:15px;list-style-type:circle"><li>' . implode('</li><li>', $warnings) . '</li></ul>';
          echo '</div>';
        }
		    
        if (empty($errors))
        {
          echo '<h3>Objecten plaatsen</h3>';
          echo '<div id="yog-objects-on-home">';
            echo '<input type="checkbox" ' .(get_option('yog_huizenophome')?'checked':'') .' id="yog-toggle-home" />';
	          echo '<label for="yog-toggle-home">Objecten plaatsen in blog (Objecten zullen tussen \'normale\' blogposts verschijnen)</label><span id="yog-objects-on-home-msg"></span>';
          echo '</div>';
          echo '<div id="yog-objects-on-archive">';
            echo '<input type="checkbox" ' .(get_option('yog_objectsinarchief')?'checked':'') .' id="yog-toggle-archive" /><span id="yog-objects-on-home-msg"></span>';
	          echo '<label for="yog-toggle-archive">Objecten plaatsen in archief (Objecten zullen tussen \'normale\' blogposts verschijnen)</label><span id="yog-objects-on-archive-msg"></span>';
          echo '</div>';
          
	        echo '<h3>Gekoppelde yes-co open accounts</h3>';
          echo '<span id="yog-add-system-link-holder">';
	          echo '<b>Een koppeling toevoegen:</b><br>';
	          echo 'Activatiecode: <input id="yog-new-secret" name="yog-new-secret" type="text" style="width: 58px" maxlength="6" value="" /> <input type="button" class="button-primary" id="yog-add-system-link" value="Koppeling toevoegen" style="margin-left: 10px;" />';
          echo '</span>';
          
          // Retrieve system links
          $systemLinkManager  = new YogSystemLinkManager();
          $systemLinks        = $systemLinkManager->retrieveAll();

          echo '<div id="yog-system-links">';
	        if (!empty($systemLinks))
          {
		        foreach ($systemLinks as $systemLink)
            {
			        echo '<div class="system-link" id="yog-system-link-' . $systemLink->getActivationCode() . '">';
			          echo '<div>';
                  echo '<b>Naam:</b> ' . $systemLink->getName() .'<br />';
                  echo '<b>Status:</b> ' . $systemLink->getState() .'<br />';
                  echo '<b>Activatiecode:</b> ' . $systemLink->getActivationCode() .' <br />';
                  echo '<a onclick="jQuery(this).next().show(); jQuery(this).hide();">Koppeling verwijderen</a>';
                  echo '<span class="hide" id="yog-system-link-' . $systemLink->getActivationCode() . '-remove">Wilt u deze koppeling verbreken? <span><a onclick="jQuery(this).parent().hide();jQuery(this).parent().prev().show();">annuleren</a> | <a onclick="yogRemoveSystemLink(\'' . $systemLink->getActivationCode() .'\');">doorgaan</a></span></span>';
                echo '</div>';
			        echo '</div>';
		        }
	        }
	        echo '</div>';
        }
	    echo '</div>';
    }
    
    /**
    * @desc Ajax toggle objects on home handler
    * 
    * @param void
    * @return void
    */
	  public function ajaxToggleHome()
	  {
		  update_option('yog_huizenophome',!(get_option('yog_huizenophome')));
		  echo '&nbsp; instelling opgeslagen.';
		  exit();
	  }
    
    /**
    * @desc Ajax toggle objects in archive handler
    * 
    * @param void
    * @return void
    */
    public function ajaxToggleArchive()
    {
		  update_option('yog_objectsinarchief',!(get_option('yog_objectsinarchief')));
		  echo '&nbsp; instelling opgeslagen.';
		  exit();
    }
    
    /**
    * @desc Add a system link
    * 
    * @param void
    * @return void
    */
	  public function addSystemLink()
	  {
		  // geen activatiecode? Geen koppeling toevoegen
		  if (empty($_POST['activatiecode']))
			  exit();
        
      $systemLink         = new YogSystemLink(YogSystemLink::EMPTY_NAME, 'Nog niet geactiveerd', $_POST['activatiecode'], '-');
      
      $systemLinkManager  = new YogSystemLinkManager();
      $systemLinkManager->store($systemLink);
		  
		  echo '<div class="system-link" id="yog-system-link-' . $systemLink->getActivationCode() . '">';
        echo '<div>';
          echo '<b>Naam:</b> ' . $systemLink->getName() .'<br />';
          echo '<b>Status:</b> ' . $systemLink->getState() .'<br />';
          echo '<b>Activatiecode:</b> ' . $systemLink->getActivationCode() .' <br />';
          echo '<a onclick="jQuery(this).next().show(); jQuery(this).hide();">Koppeling verwijderen</a>';
          echo '<span class="hide" id="yog-system-link-' . $systemLink->getActivationCode() . '-remove">Wilt u deze koppeling verbreken? <span><a onclick="jQuery(this).parent().hide();jQuery(this).parent().prev().show();">annuleren</a> | <a onclick="yogRemoveSystemLink(\'' . $systemLink->getActivationCode() .'\');">doorgaan</a></span></span>';
        echo '</div>';
		  echo '</div>';
		  exit();
	  }
    
    /**
    * @desc Remove a system link
    * 
    * @param void
    * @return void
    */
	  public function ajaxRemoveSystemLink()
	  {
		  // geen activatiecode? Geen koppeling toevoegen
		  if (empty($_POST['activatiecode']))
			  exit();
        
      $systemLinkManager  = new YogSystemLinkManager();
      $systemLink         = $systemLinkManager->retrieveByActivationCode($_POST['activatiecode']);

      $systemLinkManager->remove($systemLink);

      echo $_POST['activatiecode'];
		  exit();
	  }
  }
?>
