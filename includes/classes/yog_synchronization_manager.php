<?php
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_3mcp.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_project_translation.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_project_wonen_translation.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_relation_translation.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_image_translation.php');
  require_once(ABSPATH . 'wp-admin/includes/image.php');

  /**
  * @desc YogSynchronizationManager
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogSynchronizationManager
  {
    private $systemLink;
    private $feedReader;
    private $db;
    private $uploadDir;
    private $warnings = array();
    
    /**
    * @desc Constructor
    * 
    * @param YogSystemLink $systemLink
    * @return YogSynchronizationManager
    */
    public function __construct(YogSystemLink $systemLink)
    {
      $this->systemLink = $systemLink;
      
      $this->feedReader = Yog3McpFeedReader::getInstance();
      $this->feedReader->read($systemLink->getCollectionUuid());
      
      global $wpdb;
      $this->db = $wpdb;
      
      // Determine upload directory
      $wpUploadDir = wp_upload_dir();
      if (!empty($wpUploadDir['basedir']) && is_writeable($wpUploadDir['basedir']))
        $this->uploadDir = $wpUploadDir['basedir'] . '/';
    }
    
    /**
    * @desc Synchronize relations
    * 
    * @param void
    * @return void
    */
    public function syncRelations()
    {
      $existingRelationUuids  = $this->retrieveRelationUuidMapping();
      $relationEntityLinks    = $this->feedReader->getRelationEntityLinks();
      $processedRelationUuids = array();
      
      foreach ($relationEntityLinks as $relationEntityLink)
      {
        $uuid                     = $relationEntityLink->getUuid();
        $processedRelationUuids[] = $uuid;
        $publicationDlm           = strtotime($relationEntityLink->getDlm());
        
        // Check if relation allready exists
        $postType             = YogRelationTranslationAbstract::POST_TYPE;
        $existingRelation     = array_key_exists($uuid, $existingRelationUuids);
        $postId               = ($existingRelation) ? $existingRelationUuids[$uuid] : null;
        $postDlm              = $this->retrievePostDlm($postId, $postType);

        if ($publicationDlm > $postDlm)
        {
          $mcp3Relation         = $this->feedReader->retrieveRelationByLink($relationEntityLink);
          $translationRelation  = YogRelationTranslationAbstract::create($mcp3Relation, $relationEntityLink);
          
          // Insert / Update post
          if ($existingRelation)
            @wp_update_post(array_merge(array('ID' => $postId), $translationRelation->getPostData()));
          else
            $postId = @wp_insert_post($translationRelation->getPostData());
          
          // Store meta data
          $this->handlePostMetaData($postId, $postType, $translationRelation->getMetaData());
          
          // Update system link name (if needed)
          if ($mcp3Relation->getType() == 'office' && $this->systemLink->getName() == YogSystemLink::EMPTY_NAME)
          {
            $this->systemLink->setName($translationRelation->determineTitle());
            
            $systemLinkManager = new YogSystemLinkManager();
            $systemLinkManager->store($this->systemLink);
          }
        }
      }
      
      /* Cleanup old relations */
      $deleteRelationUuids = array_diff(array_flip($existingRelationUuids), $processedRelationUuids);

      foreach ($deleteRelationUuids as $uuid)
      {
        $postId = $existingRelationUuids[$uuid];
        wp_delete_post($postId);
      }
    }
    
    /**
    * @desc Synchronize projects
    * 
    * @param void
    * @return void
    */
    public function syncProjects()
    {
      // Register categories if needed
      $this->registerCategories();
      
      $existingProjectUuids   = $this->retrieveProjectUuidsMapping();
      $existingRelationUuids  = $this->retrieveRelationUuidMapping();
      $projectEntityLinks     = $this->feedReader->getProjectEntityLinks();
      $processedProjectUuids  = array();
      
      // Set timezone to europe/amsterdam
      date_default_timezone_set('Europe/Amsterdam');
      
      foreach ($projectEntityLinks as $projectEntityLink)
      {
        $uuid                     = $projectEntityLink->getUuid();
        $processedProjectUuids[]  = $uuid;
        $publicationDlm           = strtotime(date('c', strtotime($projectEntityLink->getDlm())));
        
        // Determine post type
        $postType                 = null;
        if (in_array($projectEntityLink->getScenario(), array('BBvk', 'BBvh', 'NBvk', 'LIvk')))
          $postType = POST_TYPE_WONEN;
        else if (in_array($projectEntityLink->getScenario(), array('BOvk', 'BOvh')))
          $postType = POST_TYPE_BOG;
        
        // Only process supported scenario's
        if (!is_null($postType))
        {
          // Check if project allready exists
          $existingProject          = array_key_exists($uuid, $existingProjectUuids);
          $postId                   = ($existingProject) ? $existingProjectUuids[$uuid] : null;
          $postDlm                  = $this->retrievePostDlm($postId, $postType);

          if ($publicationDlm > $postDlm)
          {
            $mcp3Project            = $this->feedReader->retrieveProjectByLink($projectEntityLink);
            $translationProject     = YogProjectTranslationAbstract::create($mcp3Project, $projectEntityLink);
            
            // Insert / Update post
            if ($existingProject)
              @wp_update_post(array_merge(array('ID' => $postId), $translationProject->getPostData()));
            else
              $postId = @wp_insert_post($translationProject->getPostData());
              
            // Store meta data
            $this->handlePostMetaData($postId, $postType, $translationProject->getMetaData());
            
            // Handle linked relations
            $existingLinkedRelations  = array_intersect_key($translationProject->getRelationLinks(), $existingRelationUuids);
            $relations                = array();
            foreach ($existingLinkedRelations as $uuid => $role)
            {
              $relations[$uuid] = array('rol' => $role, 'postId' => $existingRelationUuids[$uuid]);
            }
            update_post_meta($postId, $postType . '_Relaties', $relations);
            
            // Handle images
            $this->handlePostImages($postId, $mcp3Project->getMediaImages());
            
            // Handle video
            $this->handleMediaLink($postId, $postType, 'Videos', $translationProject->getVideos());
            
            // Handle external documents
            $this->handleMediaLink($postId, $postType, 'Documenten', $translationProject->getExternalDocuments());
            
            // Handle links
            $this->handleMediaLink($postId, $postType, 'Links', $translationProject->getLinks());

            // Handle categories
            wp_set_object_terms($postId, $translationProject->getCategories(), 'category', false);
          }
        }
      }
      
      /* Cleanup old projects */
      $deleteProjectUuids = array_diff(array_flip($existingProjectUuids), $processedProjectUuids);

      foreach ($deleteProjectUuids as $uuid)
      {
        $postId = $existingProjectUuids[$uuid];
        
        $this->deletePostImages($postId);
        wp_delete_post($postId);
      }
    }
    
    /**
    * @desc Check if there are warnings
    * 
    * @param void
    * @return bool
    */
    public function hasWarnings()
    {
      return count($this->warnings) > 0; 
    }
    
    /**
    * @desc Get the warnings
    * 
    * @param void
    * @return array
    */
    public function getWarnings()
    {
      return $this->warnings; 
    }
    
    /**
    * @desc Store images for a post
    * 
    * @param int $postId
    * @param array $mcp3Images
    * @return void
    */
    private function handlePostImages($parentPostId, $mcp3Images)
    {
      if (!is_null($this->uploadDir))
      {
	      // Create projects directory (if needed)
	      if (!is_dir($this->uploadDir .'projecten/' .$parentPostId))
        {
	        if (!is_dir($this->uploadDir .'projecten'))
		        mkdir($this->uploadDir . 'projecten');
          
		      mkdir($this->uploadDir . 'projecten/' .$parentPostId);	
        }
        
        // Delete post images
        $this->deletePostImages($parentPostId);

        // Handle images
        foreach ($mcp3Images as $mcp3Image)
        {
          try
          {
            $imageUuid        = $mcp3Image->getUuid();
            $imageLink        = $this->feedReader->getMediaLinkByUuid($imageUuid);
            $translationImage = YogImageTranslation::create($mcp3Image, $imageLink);
            
            // Retrieve image data
            if (ini_get('allow_url_fopen'))
              $imageData = file_get_contents($imageLink->getUrl());
            else
              $imageData = wp_remote_fopen($imageLink->getUrl());
            
            if ($imageData !== false)
            {
              // Copy image
	            $destination    = $this->uploadDir .'projecten/' .$parentPostId .'/' .$imageUuid .'.jpg';
              file_put_contents($destination, $imageData);
	            
	            $attachmentId   = @wp_insert_attachment($translationImage->getPostData(), $destination, $parentPostId);
	            $attachmentMeta = wp_generate_attachment_metadata($attachmentId, $destination);
	            wp_update_attachment_metadata($attachmentId, $attachmentMeta);
              
              $metaData       = $translationImage->getMetaData();
              foreach ($metaData as $key => $value)
              {
                if (!empty($value))
                  update_post_meta($attachmentId, 'attachment_' . $key, $value);
              }
            }
          }
          catch (Exception $e)
          {
            $this->warnings[] = $e->getMessage();
          }
        }
      }
    }
    
    /**
    * @desc Delete post images
    * 
    * @param int $postId
    * @return void
    */
    private function deletePostImages($postId)
    {
      $postType = YogImageTranslation::POST_TYPE;
      
	    // Remove existing images
	    $mediaPostIds = $this->db->get_col("SELECT ID FROM " . $this->db->prefix . "posts WHERE post_parent = " . $postId . " AND post_type = '" . $postType . "' AND post_content != ''");
	    foreach ($mediaPostIds as $mediaPostId)
      {
		    wp_delete_attachment($mediaPostId, true); 
	    }
    }
    
    /**
    * @desc Handle media links
    * 
    * @param int $postId
    * @param string $postType
    * @param string $type
    * @param array $mediaLinks
    */
    private function handleMediaLink($postId, $postType, $type, $newMediaLinks)
    {
      $metaKey    = $postType . '_' . $type;
      
      // Retrieve already set media links
      $mediaLinks = get_post_meta($postId, $metaKey, true);
      if (!is_array($mediaLinks))
        $mediaLinks = array();
      
      // Remove all media links not added through WP admin
		  foreach ($mediaLinks as $uuid => $mediaLink)
      {
			  if (strpos($mediaLink['uuid'],'zelftoegevoegd') === false)
				  unset($mediaLinks[$mediaLink['uuid']]);
		  }
      
      // Add new media links to array
      $mediaLinks = array_merge($mediaLinks, $newMediaLinks);
      
      // Store media links
      update_post_meta($postId, $metaKey, $mediaLinks);	
    }
    
    /**
    * @desc Retrieve post dlm
    * 
    * @param mixed $postId
    * @return int
    */
    private function retrievePostDlm($postId, $postType)
    {
      $dlm = 0;
      
      if (is_numeric($postId))
      {
        $dlm = get_post_meta($postId, $postType . '_dlm', true);
        $dlm = strtotime($dlm);
      }
      
      return $dlm;
    }
    
    /**
    * @desc Retrieve relation uuid mapping
    * 
    * @param void
    * @return array
    */
    private function retrieveRelationUuidMapping()
    {
      $postType   = YogRelationTranslationAbstract::POST_TYPE;
      $metaKey    = $postType . '_' . $this->systemLink->getCollectionUuid() . '_uuid';
      $tableName  = $this->db->prefix .'postmeta';
      $results    = $this->db->get_results("SELECT post_id, meta_value AS uuid FROM " . $tableName . " WHERE meta_key = '" . $metaKey . "'"); 
      $uuids      = array();
      
      foreach ($results as $result)
      {
        $uuids[$result->uuid] = (int) $result->post_id;
      }
      
      return $uuids;
    }
    
    /**
    * @desc Retrieve project uuid mapping
    * 
    * @param void
    * @return array
    */
    private function retrieveProjectUuidsMapping()
    {
      $metaKeyWonen = POST_TYPE_WONEN . '_' . $this->systemLink->getCollectionUuid() . '_uuid';
      $metaKeyBog   = POST_TYPE_BOG . '_' . $this->systemLink->getCollectionUuid() . '_uuid';
      
      $tableName    = $this->db->prefix .'postmeta';
      $results      = $this->db->get_results("SELECT post_id, meta_value AS uuid FROM " . $tableName . " WHERE meta_key IN ('" . $metaKeyWonen . "', '" . $metaKeyBog . "')"); 
      $uuids        = array();
      
      foreach ($results as $result)
      {
        $uuids[$result->uuid] = (int) $result->post_id;
      }
      
      return $uuids;
    }
    
    /**
    * Store meta data for a specific post
    * 
    * @param int $postId
    * @param string $postType
    * @param array $metaData
    * @return void
    */
    private function handlePostMetaData($postId, $postType, $metaData)
    {
      // Add uuid / collection uuid mapping to meta data
      if (isset($metaData['uuid']))
        $metaData[$this->systemLink->getCollectionUuid() . '_uuid'] = $metaData['uuid'];
        
      // Retrieve current meta data
      $oldFields = get_post_custom_keys((int) $postId);
      if (empty($oldFields))
        $oldFields = array();
      
      // Insert new meta data
      $updatedFields = array();
      if (count($metaData) > 0)
      {
		    foreach ($metaData as $key => $val)
        {
          if (!empty($val))
          {
				    update_post_meta($postId, $postType . '_' . $key, $val);
            $updatedFields[] = $postType . '_' . $key;
          }
		    }
      }
      
      /* Cleanup old meta data */
      // Do not delete media link / relation fields
      $deleteFields = array_diff($oldFields, array($postType . '_Relaties', $postType . '_Links', $postType . '_Documenten', $postType . '_Videos'));
      // Do not delete updated fields
      $deleteFields = array_diff($deleteFields, $updatedFields);
      
      if (is_array($deleteFields) && count($deleteFields) > 0)
      {
        foreach ($deleteFields as $deleteField)
        {
          delete_post_meta((int) $postId, $deleteField);
        }
      }
    }
    
    /**
    * @desc Register project categories if needed
    * 
    * @param void
    * @return void
    */
    private function registerCategories()
    {
      /* Wonen */
      $consumentId  = $this->createCategory('Consument', 'consument');
      $woonruimteId = $this->createCategory('Woonruimte', 'woonruimte', $consumentId);
      
	    // Subcategories
	    $subcategories  = array('bestaand'            => 'Bestaand',
                              'nieuwbouw'           => 'Nieuwbouw',
                              'open-huis'           => 'Open huis',
                              'bouwgrond'           => 'Bouwgrond',
                              'parkeergelegenheid'  => 'Parkeergelegenheid',
                              'berging'             => 'Berging',
                              'standplaats'         => 'Standplaats',
                              'ligplaats'           => 'Ligplaats',
                              'verhuur'             => 'Verhuur',
                              'verkoop'             => 'Verkoop',
                              'verkochtverhuurd'    => 'Verkocht/verhuurd');
      
	    foreach ($subcategories as $slug => $name)
      {
        $this->createCategory($name, $slug, $consumentId);
	    }

      // Woonruimte subcategories
      if (!is_null($woonruimteId))
      {
        $subcategories = array( 'appartement' => 'Appartement',
                                'woonhuis'    => 'Woonhuis');

	      foreach ($subcategories as $slug => $name)
        {
          $this->createCategory($name, $slug, $woonruimteId);
	      }
      }
      
      /* BOG */
      $bogId = $this->createCategory('Bedrijf', 'bedrijf');
      
      // Subcategories
	    $subcategories  = array('bog-bestaand'          => 'Bestaand',
                              'bog-nieuwbouw'         => 'Nieuwbouw',
                              'bog-verkoop'           => 'Verkoop',
                              'bog-verhuur'           => 'Verhuur',
                              'bog-verkochtverhuurd'  => 'Verkocht/verhuurd',
                              'bedrijfsruimte'        => 'Bedrijfsruimte',
                              'bog-bouwgrond'         => 'Bouwgrond',
                              'horeca'                => 'Horeca',
                              'kantooruimte'          => 'Kantooruimte',
                              'winkelruimte'          => 'Winkelruimte');
                              
	    foreach ($subcategories as $slug => $name)
      {
        $this->createCategory($name, $slug, $bogId);
	    }
    }
    
    /**
    * @desc Create a term (if not existing
    * 
    * @param string $name
    * @param int $parentTermId (optional)
    * @return int
    */
    private function createCategory($name, $slug, $parentTermId = 0)
    {
	    $term  = get_term_by('slug', $slug, 'category', ARRAY_A);

	    if (!$term)
		    $term   = @wp_insert_term($name, 'category', array('description' => $name, 'parent' => $parentTermId, 'slug' => $slug));
      
      if ($term instanceOf WP_Error)
      {
        return (int) $term->error_data['term_exists'];
      }
      else
      {
        return (int) $term['term_id'];
      }
    }
  }
?>
