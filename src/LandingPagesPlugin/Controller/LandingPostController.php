<?php
namespace LandingPages\Wordpress\Plugin\LandingPagesPlugin\Controller;

use LandingPages\Wordpress\Plugin\Framework\Controller\PostController;
use LandingPages\Wordpress\Plugin\Framework\Http\Request;
use LandingPages\Wordpress\Plugin\Framework\Kernel\ConfigCollection;
use LandingPages\Wordpress\Plugin\Framework\Util\TwigService;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\DomTree\DomDocumentWrapper;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\DomTree\Exception\EmptyDomContentException;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\DomTree\Exception\NodeDoesNotExistsException;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\DomTree\Nodes\LightboxHandlerNode;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Model\Landing;
use LandingPages\Wordpress\Plugin\LandingPagesPlugin\Service\LandendApiClientService;

class LandingPostController extends PostController
{
    /**
     * @var LandendApiClientService
     */
    private $landendApiClientService;

    /**
     * @param TwigService $twigService
     * @param Request $request
     * @param ConfigCollection $configCollection
     * @param LandendApiClientService $landendApiClientService
     */
    public function __construct(
        TwigService $twigService,
        Request $request,
        ConfigCollection $configCollection,
        LandendApiClientService $landendApiClientService
    ) {
        parent::__construct($twigService, $request, $configCollection);
        $this->landendApiClientService = $landendApiClientService;
    }

    public function action($customPost = null)
    {
        if ($customPost == null) {
            $object = get_queried_object();
        } else {
            $object = $customPost;
        }

        $landingData = json_decode((string) $object->post_content, true);
        $landing = new Landing(
            $landingData['id'],
            $landingData['name'],
            $landingData['hash'],
            $landingData['slug']
        );
        $landing->setTestId($this->request->getCookie('tid'));
        $currentUrl = parse_url(get_permalink());
        $apiResponse = $this->landendApiClientService->getLandingFromApi(
            $landing,
            empty($currentUrl['host']) ? '' : $currentUrl['host'],
            empty($currentUrl['path']) ? '/' : $currentUrl['path'],
            $this->request->getGetParameter('hash')
        );

        if (false === empty($apiResponse['redirect']) && in_array($apiResponse['status_code'], [301, 302], true)) {
            return $this->response(
                'Redirect',
                $apiResponse['status_code'],
                [
                    'Location' => $this->removeInternalQueryParameters($apiResponse['redirect'])
                ]
            );
        }

        if ($apiResponse['status_code'] !== 200) {
            return $this->response(
                $apiResponse['content'],
                $apiResponse['status_code']
            );
        }

        $landing->setContent($apiResponse['content']);
        $landing->setTestId($apiResponse['tid']);
        $this->setCookie('tid', $landing->getTestId());

        return $this->response(
            $this->fixBrokenHtmlTags(
                $this->injectLightboxJsHandler(
                    $this->modifyButtonSubmissionEndpoints(
                        $this->modifyFormAndRedirectInputEndpoints($landing, $object),
                        $landing
                    ),
                    $landing,
                    $object
                )
            )
        );
    }

    /**
     * @param string $content
     * @param Landing $landing
     * @param $object
     * @throws EmptyDomContentException
     * @throws NodeDoesNotExistsException
     * @return string
     */
    private function injectLightboxJsHandler($content, Landing $landing, $object)
    {
        $domDocumentWrapper = new DomDocumentWrapper($content);
        $domDocumentWrapper->insertAfterScriptSourceRegex(
            '/lightbox-handler/',
            new LightboxHandlerNode(
                $domDocumentWrapper->getDomDocument(),
                $landing,
                get_option('siteurl'),
                $object->post_name,
                $this->getConfig('landing_pages_export_url')
            )
        );

        return $domDocumentWrapper->save();
    }

    /**
     * @param Landing $landing
     * @param $object
     * @return string|string[]|null
     */
    private function modifyFormAndRedirectInputEndpoints(Landing $landing, $object)
    {
        return preg_replace(
            '/(<input type="hidden" name="_redirect" value)="">/',
            sprintf(
                '$1="%s/%s">',
                get_option('siteurl'),
                $object->post_name
            ),
            preg_replace(
                '/ action="\/([\s\S]*?)"/',
                sprintf(
                    ' action="%s/${1}?export_hash=%s&tid=%s"',
                    $this->getConfig('landing_pages_export_url'),
                    $landing->getHash(),
                    $landing->getTestId()
                ),
                $landing->getContent()
            )
        );
    }

    /**
     * @param $content
     * @param Landing $landing
     * @return string|string[]|null
     */
    private function modifyButtonSubmissionEndpoints($content, Landing $landing)
    {
        return preg_replace(
            '/ href="(?:\/[^\/]+)?(\/button\/[a-zA-z0-9]{32})"/',
            sprintf(
                ' href="%s${1}?export_hash=%s&tid=%s"',
                $this->getConfig('landing_pages_export_url'),
                $landing->getHash(),
                $landing->getTestId()
            ),
            preg_replace(
                '/ href="(?:\/[^\/]+)?(\/button\/[a-zA-z0-9]{32})\?lightbox=([a-z0-9]{8}(?:-[a-z0-9]{4}){3}-[a-z0-9]{12})"/',
                sprintf(
                    ' href="%s${1}?export_hash=%s&tid=%s&lightbox=${2}"',
                    $this->getConfig('landing_pages_export_url'),
                    $landing->getHash(),
                    $landing->getTestId()
                ),
                $content
            )
        );
    }

    /**
     * @param string $url
     * @return string
     */
    private function removeInternalQueryParameters($url)
    {
        $parsedUrl = parse_url($url);
        $queryArray = [];

        if (!empty($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryArray);
        }

        $queryArray = array_merge($queryArray, $_REQUEST);
        unset($queryArray['tid'], $queryArray['export_hash']);
        $queryString = http_build_query($queryArray);

        $scheme = !empty($parsedUrl['scheme']) ? "{$parsedUrl['scheme']}://" : '';
        $host = !empty($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $path = !empty($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query = !empty($queryString) ? "?$queryString" : '';

        return "{$scheme}{$host}{$path}{$query}";
    }

    private function fixBrokenHtmlTags($htmlString)
    {
        /*
         * </g is treated as a closing html tag by the parser and is cut out - it's actually part of a regexp inside
         * a dynamic text replacement JS included on the LP
         */
        return str_replace(
            ".replace(/\, '<')",
            ".replace(/\</g, '<')",
            $htmlString
        );
    }
}
