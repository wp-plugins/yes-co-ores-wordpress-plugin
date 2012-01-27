<?php
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_wp_admin_object_ui_wonen.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_wp_admin_object_ui_bog.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_wp_admin_ui_relation.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_fields_settings.php');
  
  /**
  * @desc YogWpAdminUiAbstract
  * @author Kees Brandenburg - Yes-co Nederland
  */
  abstract class YogWpAdminUiAbstract
  {
    /**
    * @desc Create a YogWpAdminUiAbstract based on the post type
    * 
    * @param string $postType
    * @return mixed
    */
    static public function create($postType)
    {
      switch ($postType)
      {
        case POST_TYPE_WONEN:
          return new YogWpAdminObjectUiWonen();
          break;
        case POST_TYPE_BOG:
          return new YogWpAdminObjectUiBog();
          break;
        case POST_TYPE_RELATION:
          return new YogWpAdminUiRelation();
          break;
      }
    }
    
    /**
    * @desc Initialize WP admin interface
    * 
    * @param void
    * @return void
    */
    public function initialize()
    {
	    add_action('admin_init',                                        array($this, 'addMetaBoxes'));
	    add_action('save_post',                                         array($this, 'extendSave'), 1, 2);
	    add_action('manage_posts_custom_column',                        array($this, 'generateColumnContent'));
	    add_filter('manage_edit-' . $this->getPostType() . '_columns',  array($this, 'determineColumns'));
      add_action('init',                                              array($this, 'enqueueFiles'));
      
      
      $this->addAjaxActions();
    }
    
    /**
    * @desc Enqueue files
    * 
    * @param void
    * @return void
    */
    public function enqueueFiles()
    {
      wp_enqueue_script('yog-admin-object-js',     YOG_PLUGIN_URL .'/inc/js/admin_object.js', array('jquery'));
    }
    
    /**
    * @desc Retrieve input fields for specific fields
    * 
    * @param $postId
    * @param array $fields
    * @param $readOnly (default: false)
    * @return array
    */
    protected function retrieveInputs($postId, $fields, $readOnly = false)
    {
	    if (!is_array($fields))
	      throw new Exception(__METHOD__ . '; Invalid specs provided, must be an array');
        
      $postType           = get_post_type($postId);

	    foreach ($fields as $key => $field)
	    {
		    $fields[$key] = $postType . '_' . $field;
	    }

	    $html               = '';
	    $customFieldValues  = get_post_custom($postId);
      $fieldsSettings     = YogFieldsSettingsAbstract::create($postType);

	    foreach ($fields as $field)
	    {
        $settings = $fieldsSettings->getField($field);
        
		    $value    = array_key_exists($field, $customFieldValues) ? $customFieldValues[$field][0] : '';
		    $title    = empty($settings['title']) ? str_replace($postType . '_', '', $field) : $settings['title'];
		    $width    = empty($settings['width']) ? 300 : $settings['width'];
		    $prefix   = '';
		    $addition = empty($settings['addition']) ? '' : ' ' . $settings['addition'];

		    if (!empty($settings['type']))
		    {
			    switch ($settings['type'])
			    {
            case 'meter':
              $addition = ' m';
              break;
            case 'cm':
              $addition = ' cm';
              break;
				    case 'oppervlakte':
					    $addition = ' m&sup2;';
					    break;
				    case 'inhoud';
				      $addition = ' m&sup3;';
				      break;
				    case 'price':
            case 'priceBtw':
					    $prefix = '&euro; ';
					    break;
			    }
		    }

		    $html .= '<tr>';
		    $html .= '<th scope="row">' . $title . '</th>';
		    $html .= '<td>';
		    $html .= $prefix;
        
        if ($readOnly === true)
        {
          $html .= '<input type="hidden" name="' . $field . '" value="' . $value . '" />';
          $html .= '<b>' . $value . '</b>';
        }
        else if (!empty($settings['type']) && $settings['type'] == 'bool')
        {
          $html .= '<input type="checkbox" name="' . $field . '" value="ja"' . ($value == 'ja' ? ' checked="checked"' : '') . ' />';
        }
        else if (!empty($settings['type']) && $settings['type'] == 'priceBtw')
        {
          $btwField = $field . 'BtwPercentage';
          $btwValue = array_key_exists($btwField, $customFieldValues) ? $customFieldValues[$btwField][0] : '';
          
          $html .= '<input type="text" style="width: 100px;" name="' . $field . '" value="' . $value . '" />';
          $html .= '&nbsp;&nbsp;btw <input type="text" style="width: 40px;" name="' . $btwField . '" value="' . $btwValue . '" /> %';
        }
        else if (!empty($settings['type']) && $settings['type'] == 'select' && !empty($settings['options']))
        {
          $html .= '<select name="' . $field . '" id="yog-' . $field . '">';
          foreach ($settings['options'] as $option)
          {
            $html .= '<option value="' . $option . '"' . ($option == $value ? ' selected="selected"' : '') . '>' . $option . '</option>';
          }
          $html .= '</select>';
        }
        else
        {
		      $html .= '<input type="text" style="width: ' . $width . 'px;" name="' . $field . '" value="' . $value . '" />';
        }
        
		    $html .= $addition;
		    $html .= '</td>';
		    $html .= '</tr>';
	    }

	    return $html;
    }
    
    abstract public function getPostType();
    abstract public function determineColumns($columns);
    abstract public function generateColumnContent($column);
    abstract public function addMetaBoxes();
    abstract public function extendSave($postId, $post);
    
    /**
    * @desc Add ajax actions
    * Can be overwritten by implementing class
    * 
    * @param void
    * @return void
    */
    public function addAjaxActions()
    {
      
    }
  }
  
  /**
  * @desc YogWpAdminObjectUiAbstract
  * @author Kees Brandenburg - Yes-co Nederland
  */
  abstract class YogWpAdminObjectUiAbstract extends YogWpAdminUiAbstract
  {
    /**
    * @desc Add ajax actions
    * 
    * @param void
    * @return void
    */
    public function addAjaxActions()
    {
	    add_action('wp_ajax_removelink',      array($this, 'ajaxRemoveLink'));
	    add_action('wp_ajax_removedocument',  array($this, 'ajaxRemoveDocument'));
	    add_action('wp_ajax_removevideo',     array($this, 'ajaxRemoveVideo'));
	    add_action('wp_ajax_addlink',         array($this, 'ajaxAddLink'));
	    add_action('wp_ajax_adddocument',     array($this, 'ajaxAddDocument'));
	    add_action('wp_ajax_addvideo',        array($this, 'ajaxAddVideo')); 
    }
    
    /**
    * @desc Render synchronization meta box
    * 
    * @param object $post
    * @return void
    */
    public function renderSyncMetaBox($post)
    {
	    echo '<input type="hidden" name="yog_nonce" id="myplugin_noncename" value="' .wp_create_nonce($this->getBaseName()) . '" />';
      echo '<input type="hidden" name="uuid" value="' . yog_retrieveSpec('uuid', $post->ID) . '" />';
      
	    echo '<table class="form-table">';
	    echo $this->retrieveInputs($post->ID, array('scenario'), true);
	    echo '</table>';
    }
    
    /**
    * @desc Render Google maps meta box
    * 
    * @param object @post
    * @return void
    */
    public function renderMapsMetaBox($post)
    {
      $staticMap = yog_retrieveStaticMap('hybrid', 18, 260, 260); 
      
      if (empty($staticMap))
        echo '<p>Er is geen locatie bekend.</p>';
      else
        echo $staticMap;
    }
    
    /**
    * @desc Render images meta box
    * 
    * @param object $post
    * @return void
    */
    public function renderImagesMetaBox($post)
    {
      $images = yog_retrieveImages('thumbnail', null, $post->ID);
      
      $html = '<div class="images-holder">';
      foreach ($images as $image)
      {
        $html .= '<div class="image-holder">';
          $html .= '<img src="' . $image[0] . '" />'; 
        $html .= '</div>';
      }
      $html .= '</div>';
      
      echo $html;
    }
    
    /**
    * @desc Render movies meta box
    * 
    * @param object $post
    * @return void
    */
    public function renderMoviesMetaBox($post)
    {
      $videos      = yog_retrieveMovies($post->ID);
      
	    $videoservices = array( 'Youtube' => 'http://www.youtube.com/',
	                            'Vimeo'   => 'http://vimeo.com/',
	                            'Flickr'  => 'http://www.flickr.com/');
                              
	    $select = '<select id="video_type" name="video_type">';
	    foreach ($videoservices as $videoservice => $link)
      {
		    $select.= '<option value="' .$link .'">' .$videoservice .'</option>';
	    }
	    $select.= '</select>';
      
      $html  = '<div class="media-box" id="yog-video-form">';
        $html .= '<div class="row">';
	        $html .= '<label for="video_type">Videoprovider: </label>' . $select;
        $html .= '</div>';
        $html .= '<div class="row">';
	        $html .= '<label for="video_titel">Titel: </label>';
          $html .= '<input id="video_titel" name="video_titel" type="text" value="" />';
        $html .= '</div>';
        $html .= '<div class="row">';
	        $html .= '<label for="video_url">Link waarop de video te zien is, bijvoorbeeld <br /><i>www.youtube.com/watch?v=duqr82aYKRY</i>:  </label>';
          $html .= 'http://<input id="video_url" name="video_url" class="input-small" type="text" value="" />';
          $html .= '<input type="button" class="button-primary" id="yog-add-video" value="Toevoegen" />';
        $html .= '</div>';
      $html .= '</div>';
	    
      $html .= '<div id="yog-videos-overview" class="media-overview' . ((is_array($videos) && count($videos) > 0) ? '' : ' hide') . '">';
	      $html .= '<b>Gekoppelde videos:</b>';
	      $html .= '<div class="media-box">';
          $html .= '<table class="form-table" id="yog-video-tabel">';
            $html .= '<tbody>';
            
	          if (is_array($videos) && count($videos))
            {
		          foreach ($videos as $videoUUID => $video)
		          {
                $url  = !empty($video['websiteurl']) ? $video['websiteurl'] : $video['videostreamurl'];
                
			          $html .= '<tr id="video-' . $video['uuid'] . '">';
			          $html .= '<td><a href="' . $url .'" class="' . $video['type'] . '" target="_blank">' . $video['title'] . '</a></td>';
			          $html .= '<td class="actions"><input type="button" class="button-primary" onclick="yogRemoveVideo(\'' .$video['uuid'] .'\');" value="Verwijderen" style="margin-left: 5px;"></td>';
			          $html .= '</tr>';
		          }
	          }
            
            $html .= '</tbody>';
	        $html .= '</table>';
        $html .= '</div>';
      $html .= '</div>';
      
	    echo $html;
    }
    
    /**
    * @desc Render documents meta box
    * 
    * @param object $post
    * @return void
    */
    public function renderDocumentsMetaBox($post)
    {
      $documents  = yog_retrieveDocuments($post->ID);
      
	    $html  = '<div class="media-box" id="yog-document-form">';
        $html .= '<div class="row">';
          $html .= '<label for="document_type">Type (bijvoorbeeld \'brochure\'): </label>';
          $html .= '<input id="document_type" name="document_type" type="text" value="" />';
        $html .= '</div>';
        $html .= '<div class="row">';
	        $html .= '<label for="document_titel">Titel: </label>';
          $html .= '<input id="document_titel" name="document_titel" type="text" value="" />';
        $html .= '</div>';
        $html .= '<div class="row">';
	        $html .= '<label for="document_url">Link: </label>';
          $html .= 'http://<input id="document_url" name="document_url" class="input-small" type="text" value="" />';
          $html .= '<input type="button" class="button-primary" id="yog-add-document" value="Toevoegen" />';
        $html .= '</div>';
      $html .= '</div>';
	    
      $html .= '<div id="yog-documents-overview" class="media-overview' . ((is_array($documents) && count($documents) > 0) ? '' : ' hide') . '">';
	      $html .= '<b>Gekoppelde documenten:</b>';
	      $html .= '<div class="media-box">';
          $html .= '<table class="form-table" id="yog-documents-table">';
            $html .= '<tbody>';
            
	          if(is_array($documents) && count($documents))
            {
		          foreach ($documents as $documentsUUID => $document)
		          {
                $type = empty($document['type']) ? 'brochure' : $document['type'];
                
			          $html .= '<tr id="document-' . $document['uuid'] . '">';
			            $html .= '<td><a href="' . $document['url'] . '" class="' . $type . '">' . $document['title'] . '</a></td>';
			            $html .= '<td class="actions"><input type="button" class="button-primary" onclick="yogRemoveDocument(\'' .$document['uuid'] .'\');" value="Verwijderen" /></td>';
			          $html .= '</tr>';
		          }
	          }
            
            $html .=  '</tbody>';
	        $html .= '</table>';
        $html .= '</div>';
      $html .= '</div>';
      
	    echo $html;
    }
    
    /**
    * @desc Render links meta box
    * 
    * @param object $post
    * @return void
    */
    public function renderLinksMetaBox($post)
    {
      $links      = yog_retrieveLinks($post->ID);
      
	    $html  = '<div class="media-box" id="yog-link-form">';
        $html .= '<div class="row">';
          $html .= '<label for="link_type">Type: </label>';
          $html .= '<select id="link_type" name="link_type"><option value="website">Website</option></select>';
        $html .= '</div>';
        $html .= '<div class="row">';
	        $html .= '<label for="link_titel">Titel: </label>';
          $html .= '<input id="link_titel" name="link_titel" type="text" value="" />';
        $html .= '</div>';
        $html .= '<div class="row">';
	        $html .= '<label for="link_url">Link: </label>';
          $html .= 'http://<input id="link_url" name="link_url" class="input-small" type="text" value="" />';
          $html .= '<input type="button" id="yog-add-link" class="button-primary" value="Toevoegen" />';
        $html .= '</div>';
      $html .= '</div>';

      $html .= '<div id="yog-links-overview" class="media-overview' . ((is_array($links) && count($links) > 0) ? '' : ' hide') . '">';
	      $html .= '<b>Gekoppelde links: </b>';
	      $html .= '<div class="media-box">';
          $html .= '<table class="form-table" id="yog-links-table">';
	          $html .= '<tbody>';
            
	          if (is_array($links) && count($links) > 0)
            {
		          foreach ($links as $linkUUID => $link)
		          {
                $type = empty($link['type']) ? 'website' : $link['type'];
                
			          $html .= '<tr id="link-' . $linkUUID . '">';
			          $html .= '<td><a href="' . $link['url'] . '" class="' . $type . '" target="_blank">' . $link['title'] . '</a></td>';
			          $html .= '<td class="actions"><input type="button" class="button-primary" onclick="yogRemoveLink(\'' .$link['uuid'] .'\');" value="Verwijderen" /></td>';
			          $html .= '</tr>';
		          }
	          }
            
            $html .= '</tbody>';
	        $html .= '</table>';
        $html .= '</div>';
      $html .= '</div>';

	    echo $html;
    }
    
    /**
    * @desc Render linked relations meta box
    * 
    * @param void
    * @return void
    */
    public function renderRelationsMetaBox()
    {
      $relations = yog_retrieveRelations();
      
      $html  = '<table class="form-table" id="yog-links-table">';
        $html .= '<tbody>';
      
        foreach ($relations as $role => $relation)
        {
          $html .= '<tr id="relation-' . $relation->ID . '">';
            $html .= '<td>' . $role . ':</td>';
            $html .= '<td><a href="' . get_edit_post_link($relation->ID) . '">' . $relation->post_title . '</a></td>';
          $html .= '</tr>';
        }
        
        $html .= '</tbody>';
      $html .= '</table>';
      
      echo $html;
    }
    
    /**
    * @desc Add video handler
    * 
    * @param void
    * @return void
    */
	  public function ajaxAddVideo()
	  {
		  $uuid       = 'zelftoegevoegd-' .time();
		  $postId     = (int) $_POST['post'];
      $postType   = get_post_type($postId);
		  $titel      = $_POST['titel'];
		  $serviceUri = $_POST['type'];
		  $url        = $_POST['url'];
      
      if (strpos($url, 'http://') === false && strpos($url, 'https://') === false && strpos($url, 'ftp://') === false)
        $url = 'http://' . $url;
      
		  $videos = get_post_meta($postId, $postType . '_Videos',true);
		  $order  = 10;
      
		  if (!is_array($videos))
      {
			  $videos = array();
      }
		  else
      {
			  foreach ($videos as $videouuid => $video)
        {
				  if ($video['order'] >= $order)
					  $order = $video['order'] + 1;
			  }
		  }
      
		  $videos[$uuid] = array('uuid'                       => $uuid,
                            'videoereference_serviceuri'  => $serviceUri,
                            'title'                       => $titel,
                            'websiteurl'                  => $url,
                            'order'                       => $order);
      
		  update_post_meta($postId, $postType . '_Videos', $videos);
      
		  echo $uuid;
		  exit();
	  }
    
    /**
    * @desc Remove video handle
    * 
    * @param void
    * @return void
    */
	  public function ajaxRemoveVideo()
	  {
      $uuid     = $_POST['uuid'];
      $postId   = (int) $_POST['post'];
      $postType = get_post_type($postId);
      
		  $videos = get_post_meta($postId, $postType . '_Videos', true);
		  if (is_array($videos) && count($videos))
      {
        if (isset($videos[$uuid]))
			    unset($videos[$uuid]);
        
			  update_post_meta($postId, $postType . '_Videos', $videos);
		  }
      
      echo $uuid;
		  exit();
	  }
    
    /**
    * @desc Add link handler
    * 
    * @param void
    * @return void
    */
	  public function ajaxAddLink()
	  {
		  $uuid     = 'zelftoegevoegd-' .time();
		  $postId   = (int) $_POST['post'];
      $postType = get_post_type($postId);
		  $titel    = $_POST['titel'];
		  $type     = $_POST['type'];
      $url      = $_POST['url'];
      
      if (strpos($url, 'http://') === false && strpos($url, 'https://') === false && strpos($url, 'ftp://') === false)
        $url = 'http://' . $url;
		  
		  $links = get_post_meta($postId, $postType . '_Links', true);
		  if (!is_array($links))
			  $links = array();
        
		  $links[$uuid] = array('uuid' => $uuid,'type' => $type,'title' => $titel,'url' => $url);
		  update_post_meta($postId, $postType . '_Links', $links);
      
		  echo $uuid;
		  exit();
	  }
    
    /**
    * @desc Remove link handler
    * 
    * @param void
    * @return void
    */
	  public function ajaxRemoveLink()
	  {
      $postId   = (int) $_POST['post'];
      $postType = get_post_type($postId);
      $uuid     = $_POST['uuid'];
		  $links    = get_post_meta($postId, $postType . '_Links', true);
      
		  if (is_array($links) && count($links))
      {
        if (isset($links[$uuid]))
			    unset($links[$uuid]);
        
			  update_post_meta($postId, $postType . '_Links',$links);
		  }
      
      echo $uuid;
		  exit();
	  }
    
    /**
    * @desc Add document handler
    * 
    * @param void
    * @return void
    */
	  public function ajaxAddDocument()
	  {
		  $uuid       = 'zelftoegevoegd-' .time();
		  $postId     = (int) $_POST['post'];
      $postType   = get_post_type($postId);
		  $titel      = $_POST['titel'];
		  $type       = $_POST['type'];
		  $url        = $_POST['url'];
      
      if (strpos($url, 'http://') === false && strpos($url, 'https://') === false && strpos($url, 'ftp://') === false)
        $url = 'http://' . $url;
      
		  $documenten = get_post_meta($postId, $postType . '_Documenten', true);
		  $order      = 10;
      
		  if(!is_array($documenten))
      {
			  $documenten = array();
      }
		  else
      {
			  foreach ($documenten as $uuid => $document)
        {
				  if($document['order'] >= $order)
					  $order = $document['order']+1;
			  }
		  }
      
		  $documenten[$uuid] = array( 'uuid'  => $uuid,
                                  'type'  => $type,
                                  'title' => $titel,
                                  'url'   => $url,
                                  'order' => $order);
      
		  update_post_meta($postId, $postType . '_Documenten', $documenten);		
		  echo $uuid;
		  exit();
	  }
    
    /**
    * @desc Remove document handler
    * 
    * @param void
    * @return void
    */
	  public function ajaxRemoveDocument()
	  {
      $postId   = (int) $_POST['post'];
      $postType = get_post_type($postId);
      $uuid     = $_POST['uuid'];
      
		  $links = get_post_meta($postId, $postType . '_Documenten', true);
      
		  if (is_array($links) && count($links))
      {
        if (isset($links[$uuid]))
			    unset($links[$uuid]);
        
			  update_post_meta($postId, $postType . '_Documenten', $links);
		  }
      
      echo $uuid;
		  exit();
	  }
  }
?>
