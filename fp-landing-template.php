<?php
/*
 * Template Name: FullPage Landing
 * Description: FullPage template to be used to set landing page as a Wordpress Front Page.
 */
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\LandingPagesWordpressPlugin;

require_once __DIR__ . '/vendor/autoload.php';

$landingSlug = $post->post_content;
$landingPost = get_posts([
    'name'              => $landingSlug,
    'posts_per_page'    => 1,
    'post_type'         => 'landing',
    'post_status'       => 'publish'
]);

if ($landingPost) {
    $landingPagesPluginApp = LandingPagesWordpressPlugin::getInstance();
    $landingPagesPluginApp->dispatchPost($landingPost[0]);
}
