<?php
  /*
  Plugin Name: Yes-co ORES
  Plugin URI: http://yes-co.nl/wordpress-voor-makelaars/
  Description: Publiceert uw onroerend goed op uw Wordpress Blog
  Version: 1.1
  Author: Yes-co
  Author URI: http://yes-co.nl
  License: GPL2
  */
	
	// Determine plugin directory
	if (!defined('YOG_PLUGIN_DIR'))
		define('YOG_PLUGIN_DIR', dirname(__FILE__));
	
	if (!defined('YOG_PLUGIN_URL'))
		define('YOG_PLUGIN_URL', plugins_url(null, __FILE__));
    
  // Include files
  require_once(YOG_PLUGIN_DIR . '/includes/config/config.php');
  
  // Determine action
  $action     = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
  
  try
  {
    switch ($action)
    {
      // Activate plugin (called with URL from Yes-co)
      case 'activate_yesco_og':
        require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_system_link_manager.php');
        
        $yogSystemLinkManager       = new YogSystemLinkManager();
        $yogSystemLink              = $yogSystemLinkManager->retrieveByRequest($_REQUEST);
        $yogSystemLinkManager->activate($yogSystemLink);
        
        echo json_encode(array( 'status'  => 'ok',
	                              'message' => 'Plug-in activated')
                        );
        
        break;
      // Synchronize objects / relations
      case 'sync_yesco_og':
        require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_system_link_manager.php');
        require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_synchronization_manager.php');
        
        $yogSystemLinkManager       = new YogSystemLinkManager();
        $yogSystemLink              = $yogSystemLinkManager->retrieveByRequest($_REQUEST);
        
        $yogSynchronizationManager  = new YogSynchronizationManager($yogSystemLink);
        $yogSynchronizationManager->syncRelations();
        $yogSynchronizationManager->syncProjects();
        
        $response = array('status'   => 'ok',
                          'message' => 'Synchronisatie voltooid');
        if ($yogSynchronizationManager->hasWarnings())
          $response['warnings'] = $yogSynchronizationManager->getWarnings();
        
        echo json_encode($response);
        
        exit;
        
        break;
      // Initialize plugin
      default:
        require_once(YOG_PLUGIN_DIR . '/includes/yog_public_functions.php');
        require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_plugin.php');
        
        $yogPlugin = YogPlugin::getInstance();
        $yogPlugin->init();
        break;
    }
  }
  catch (YogException $e)
  {
    echo $e->toJson();
    exit;
  }
?>
