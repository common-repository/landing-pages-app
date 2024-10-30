<?php
namespace LandingPages\Wordpress\Plugin\Framework\Controller;

use LandingPages\Wordpress\Plugin\Framework\Http\Request;
use LandingPages\Wordpress\Plugin\Framework\Http\Response;
use LandingPages\Wordpress\Plugin\Framework\Kernel\ConfigCollection;
use LandingPages\Wordpress\Plugin\Framework\Util\TwigService;

abstract class AbstractController
{
    private $twigService;
    private $configCollection;
    protected $request;

    public abstract function action();

    public function __construct(TwigService $twigService, Request $request, ConfigCollection $configCollection)
    {
        $this->twigService = $twigService;
        $this->request = $request;
        $this->configCollection = $configCollection;
    }

    protected function getConfig($key)
    {
        return $this->configCollection->get($key);
    }

    protected function render($template, $variables = [])
    {
        return $this->twigService->render($template, $variables);
    }

    protected function setCookie($name, $value)
    {
        setcookie($name, $value, time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
    }

    public function response($content, $statusCode = 200, $headers = [])
    {
        $response = new Response();
        $response->setContent($content);
        $response->setStatusCode($statusCode);

        if (!empty($headers)) {
            foreach ($headers as $name => $value) {
                $response->setHeader($name, $value);
            }
        }

        return $response->dispatch();
    }
}
