<?php
namespace LandingPages\Wordpress\Plugin\LandingPagesPlugin\Controller;

use LandingPages\Wordpress\Plugin\Framework\Controller\AbstractController;
use LandingPages\Wordpress\Plugin\Framework\Http\Request;
use LandingPages\Wordpress\Plugin\Framework\Kernel\ConfigCollection;
use LandingPages\Wordpress\Plugin\Framework\Kernel\PluginPartInterface;
use LandingPages\Wordpress\Plugin\Framework\Model\Post;
use LandingPages\Wordpress\Plugin\Framework\Util\TwigService;
use LandingPages\Wordpress\Plugin\Framework\Wrapper\AdminMenuTrait;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Model\LandingCollection;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Model\LandingPostType;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Service\ApiClient\InvalidTokenException;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Service\ApiClient\LandingPagesApiErrorException;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Service\ApiClientService;

class AdminMenuAvailableLandingPages extends AbstractController implements PluginPartInterface
{
    use AdminMenuTrait;

    const ACTION_TAG = 'admin_menu';
    const PAGE_TITLE = 'Available Landing Pages';
    const SUBMENU_TITLE = 'Available Landing Pages';
    const MENU_SLUG = 'landing_pages';
    const MENU_ICON = 'desktop.svg';
    const MENU_TITLE = 'Landing Pages';
    const CAPABILITY = 'manage_options';
    const TWIG_TEMPLATE = 'admin_menu_available_landing_pages.html.twig';
    const TWIG_TEMPLATE_SUCCESS = 'admin_menu_publish_landing_page_success.html.twig';

    /**
     * @var ApiClientService
     */
    private $apiClientService;

    /**
     * @var LandingPostType
     */
    private $landingPostType;

    /**
     * @param TwigService $twigService
     * @param Request $request
     * @param ApiClientService $apiClientService
     * @param LandingPostType $landingPostType
     * @param ConfigCollection $configCollection
     */
    public function __construct(
        TwigService $twigService,
        Request $request,
        ApiClientService $apiClientService,
        LandingPostType $landingPostType,
        ConfigCollection $configCollection
    ) {
        parent::__construct($twigService, $request, $configCollection);
        $this->apiClientService = $apiClientService;
        $this->landingPostType = $landingPostType;
    }

    public function action()
    {
        $landingPageId = $this->request->getPostParameter('landingPageId');
        $landingPageName = $this->request->getPostParameter('landingPageName');
        $landingSearchPhrase = $this->request->getGetParameter('s');
        $page = (int) $this->request->getGetParameter('landingPage');
        $page = isset($page) && $page > 0 ? $page : 1;

        try {
            $response = $this->apiClientService->getLandingsForAccount($page, $landingSearchPhrase);
        } catch (InvalidTokenException $exception) {
            return $this->response($this->render(self::TWIG_TEMPLATE, [
                'error' => $exception->getMessage(),
                'settings_url' => admin_url('admin.php?page=' . AdminMenuSettings::MENU_SLUG)
            ]));
        } catch (LandingPagesApiErrorException $exception) {
            return $this->response($this->render(self::TWIG_TEMPLATE, [
                'error' => $exception->getMessage(),
                'settings_url' => admin_url('admin.php?page=' . AdminMenuSettings::MENU_SLUG)
            ]));
        }

        $landings = new LandingCollection();
        $landings->createFromApiResponse($response);

        if (isset($landingPageId, $landingPageName)) {
            $landingPost = new Post($landingPageName, json_encode($landings->getLanding($landingPageId)), $this->landingPostType);
            $landingPost->create();

            return $this->response($this->render(self::TWIG_TEMPLATE_SUCCESS, [
                'url' => admin_url('edit.php?post_type=' . LandingPostType::POST_TYPE)
            ]));
        }

        $maxPage = (int) ceil($landings->getCount() / 10);

        return $this->response($this->render(self::TWIG_TEMPLATE, [
            'landings' => $landings->getLandings(),
            'currentPage' => $page,
            'maxPage' => $maxPage,
            'queryUrl' => menu_page_url(self::MENU_SLUG, 0),
            'searchPhrase' => $landingSearchPhrase,
        ]));
    }

    public function initialize()
    {
        $this->addAdminMenuPage();
        $this->addAdminSubMenuPage(self::MENU_SLUG);
    }
}
