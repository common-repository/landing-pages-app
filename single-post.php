<?php
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\LandingPagesWordpressPlugin;

require_once __DIR__ . '/vendor/autoload.php';

$landingPagesPluginApp = LandingPagesWordpressPlugin::getInstance();
$landingPagesPluginApp->dispatchPost();
