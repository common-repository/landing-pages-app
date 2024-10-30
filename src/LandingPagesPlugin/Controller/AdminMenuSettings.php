<?php
namespace LandingPages\Wordpress\Plugin\LandingPagesPlugin\Controller;

use LandingPages\Wordpress\Plugin\Framework\Controller\AbstractController;
use LandingPages\Wordpress\Plugin\Framework\Kernel\PluginPartInterface;
use LandingPages\Wordpress\Plugin\Framework\Wrapper\AdminMenuTrait;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Service\PluginInstaller;

class AdminMenuSettings extends AbstractController implements PluginPartInterface
{
    use AdminMenuTrait;

    const ACTION_TAG = 'admin_menu';
    const PAGE_TITLE = 'Landing Pages Settings';
    const SUBMENU_TITLE = 'Settings';
    const MENU_SLUG = 'landing_pages_settings';
    const CAPABILITY = 'manage_options';

    public function action()
    {
        $landingPagesToken = $this->request->getPostParameter('landing_pages_token');

        if (isset($landingPagesToken)) {
            update_option(PluginInstaller::PLUGIN_LANDING_PAGES_TOKEN, $landingPagesToken);
            $returnData['submitted'] = true;
        }

        $returnData['landingPagesToken'] = get_option(PluginInstaller::PLUGIN_LANDING_PAGES_TOKEN);
        $this->response($this->render('admin_menu_settings.html.twig', $returnData));
    }

    public function initialize()
    {
        $this->addAdminSubMenuPage(AdminMenuAvailableLandingPages::MENU_SLUG);
    }
}
