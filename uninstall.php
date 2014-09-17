<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
  exit();

$pluginOptions = array( 'yog_plugin_version', 'yog_3mcp_version', 'yog_koppelingen',
                        'yog_huizenophome', 'yog_objectsinarchief');

foreach ($pluginOptions as $pluginOption)
{
  delete_option($pluginOption);
  delete_site_option($pluginOption);
}