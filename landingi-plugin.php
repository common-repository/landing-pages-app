<?php
/*
Plugin Name: Landing Pages App
Plugin URI: https://landingi.com/
Description: Landing Pages App is a Web app to speed up and simplify the process of building, publishing, optimizing and managing landing pages on a large scale for lead generation process. We are integrated with leading marketing tools so that the marketer can take full advantage of his existing marketing stack and deliver more high quality leads.
Version: 2.3.2
Author: Landing Pages
License: GPLv2
Text Domain: landingi-plugin
*/

use LandingPages\Wordpress\Plugin\LandingPagesPlugin\LandingPagesWordpressPlugin;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/LandingPagesPlugin/PageTemplater.php';

$landingPagesPluginApp = LandingPagesWordpressPlugin::getInstance();
$landingPagesPluginApp->addConfig('landing_pages_plugin_path', __FILE__);
$landingPagesPluginApp->addConfig('landing_pages_singlepost_path', __DIR__ . '/single-post.php');
$landingPagesPluginApp->addConfig('landing_pages_api_url', 'https://api.landingi.com/');
$landingPagesPluginApp->addConfig('landing_pages_export_url', 'https://www.landingiexport.com');
$landingPagesPluginApp->addConfig('landing_pages_tests_domain', 'dotests.com');
$landingPagesPluginApp->addConfig(
    'plugin_images_path',
    sprintf('%s/plugins/%s/resources/images/', content_url(), pathinfo(__DIR__, PATHINFO_FILENAME))
);
$landingPagesPluginApp->initialize();
