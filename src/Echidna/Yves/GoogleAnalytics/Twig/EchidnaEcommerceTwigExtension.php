<?php

namespace Echidna\Yves\GoogleAnalytics\Twig;

use Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce\EchidnaEcommercePageTypePluginInterface;
use Spryker\Shared\Twig\TwigExtension;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;
use Twig_SimpleFunction;

class EchidnaEcommerceTwigExtension extends TwigExtension
{
    public const FUNCTION_ECHIDNA_ECOMMERCE = 'EchidnaEcommerce';

    /**
     * @var EchidnaEcommercePageTypePluginInterface[]
     */
    protected $plugin;

    public function __construct(array $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            $this->createEchidnaEcommerceFunction(),
        ];
    }

    /**
     * @return \Twig_SimpleFunction
     */
    protected function createEchidnaEcommerceFunction(): Twig_SimpleFunction
    {
            return new Twig_SimpleFunction(
                static::FUNCTION_ECHIDNA_ECOMMERCE,
                [$this, 'renderEchidnaEcommerce'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            );
    }

    /**
     * @param \Twig_Environment $twig
     * @param string $page
     * @param Request|null $request
     * @param array $params
     *
     * @throws
     *
     * @return string
     */
    public function renderEchidnaEcommerce(Twig_Environment $twig, string $page, ?Request $request, array $params = []): string
    {
        if (array_key_exists($page, $this->plugin)) {
            return $this->plugin[$page]->handle($twig, $request, $params);
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getEnhancedMicrodateTemplateName(): string
    {
        return '@GoogleAnalytics/partials/echidna-ecommerce.twig';
    }
}
