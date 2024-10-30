<?php
namespace LandingPages\Wordpress\Plugin\LandingPagesPlugin;

use LandingPages\Wordpress\Plugin\Framework\Kernel\PluginKernel;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Controller\AdminMenuImportedLandingPages;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Controller\LandingPostController;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Model\LandingPostType;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Service\ApiClientService;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Service\LandendApiClientService;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Service\PluginInstaller;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Controller\AdminMenuSettings;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Controller\AdminMenuAvailableLandingPages;

class LandingPagesWordpressPlugin extends PluginKernel
{
    const NAME = 'Landing Pages App';
    const REQUIRED_PHP = '5.5';

    protected function initializeContainers()
    {
        $this->containerCollection->set('service.plugin.installer', new PluginInstaller(
            $this->containerCollection,
            $this->getConfig('landing_pages_plugin_path')
        ));

        $this->containerCollection->set('service.api.client', new ApiClientService(
            $this->getConfig('landing_pages_api_url'),
            $this->containerCollection->get('service.plugin.installer')->getToken()
        ));

        $this->containerCollection->set('service.api.landend.client', new LandendApiClientService(
            $this->getConfig('landing_pages_export_url')
        ));

        $this->containerCollection->set(
            'model.landing.post.type',
            new LandingPostType($this->getConfig('landing_pages_singlepost_path'))
        );

        $this->containerCollection->set('controller.admin.menu.available_landing_pages', new AdminMenuAvailableLandingPages(
            $this->containerCollection->get('framework.twig'),
            $this->containerCollection->get('framework.http.request'),
            $this->containerCollection->get('service.api.client'),
            $this->containerCollection->get('model.landing.post.type'),
            $this->configCollection
        ));

        $this->containerCollection->set('controller.admin.menu.imported_landing_pages', new AdminMenuImportedLandingPages());

        $this->containerCollection->set('controller.admin.menu.settings', new AdminMenuSettings(
            $this->containerCollection->get('framework.twig'),
            $this->containerCollection->get('framework.http.request'),
            $this->configCollection
        ));

        $this->containerCollection->set('postcontroller.landing', new LandingPostController(
            $this->containerCollection->get('framework.twig'),
            $this->containerCollection->get('framework.http.request'),
            $this->configCollection,
            $this->containerCollection->get('service.api.landend.client')
        ));

        $this->compileCollections();
    }

    private function compileCollections()
    {
        $this->containerCollection->get('framework.post.type.collection')->addPostType(
            $this->containerCollection->get('model.landing.post.type')
        );
    }
}
