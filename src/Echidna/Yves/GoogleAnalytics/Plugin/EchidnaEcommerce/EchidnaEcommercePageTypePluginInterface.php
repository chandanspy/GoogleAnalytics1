<?php


namespace Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce;

use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

interface EchidnaEcommercePageTypePluginInterface
{
    /**
     * @param \Twig_Environment $twig
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array|null $params
     *
     * @return string
     */
    public function handle(Twig_Environment $twig, Request $request, ?array $params = []): string;

    /**
     * @return string
     */
    public function getTemplate(): string;
}
