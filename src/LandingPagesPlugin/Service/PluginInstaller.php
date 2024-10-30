<?php
namespace LandingPages\Wordpress\Plugin\LandingPagesPlugin\Service;

use LandingPages\Wordpress\Plugin\Framework\Kernel\ContainerCollection;
use LandingPages\Wordpress\Plugin\Framework\Kernel\PluginPartInterface;
use LandingPages\Wordpress\Plugin\Framework\Kernel\AbstractPluginInstaller;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\LandingPagesWordpressPlugin;

class PluginInstaller extends AbstractPluginInstaller implements PluginPartInterface
{
    const PLUGIN_LANDING_PAGES_TOKEN = 'landing_pages_plugin_token';

    private $pluginPath;

    public function __construct(ContainerCollection $containerCollection, $pluginPath)
    {
        parent::__construct($containerCollection);
        $this->pluginPath = $pluginPath;
    }

    private function createLandingPagesOptions()
    {
        add_option(self::PLUGIN_LANDING_PAGES_TOKEN, 'Token');
    }

    private function createLandingPagePostType()
    {
        $landingPostType = $this->containerCollection->get('model.landing.post.type');
        $landingPostType->initialize();
    }

    public function getToken()
    {
        return get_option(self::PLUGIN_LANDING_PAGES_TOKEN);
    }

    protected function registerActivatePluginHooks()
    {
        register_activation_hook($this->pluginPath, function () {
            $this->checkRequirements();
            $this->createLandingPagesOptions();
            $this->createLandingPagePostType();
            flush_rewrite_rules();
        });
    }

    private function checkRequirements() {
        if (version_compare(PHP_VERSION, LandingPagesWordpressPlugin::REQUIRED_PHP, '<')) {
            deactivate_plugins(plugin_basename($this->pluginPath));

            wp_die(sprintf(
                'The %s plugin requires PHP version %s. Your server is running version %s.',
                LandingPagesWordpressPlugin::NAME,
                LandingPagesWordpressPlugin::REQUIRED_PHP,
                PHP_VERSION
            ));
        }
    }

    protected function registerDeactivatePluginHooks()
    {
        register_deactivation_hook($this->pluginPath, function () {
            flush_rewrite_rules();
        });
    }

    public function initialize()
    {
        $this->registerActivatePluginHooks();
        $this->registerDeactivatePluginHooks();
    }
}
