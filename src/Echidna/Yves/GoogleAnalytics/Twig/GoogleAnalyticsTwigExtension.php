<?php

/**
 * Google Tag Manager tracking integration for Spryker
 *
 * @author Chandan Kumar <ranjanpratik@yahoo.in>
 */

namespace Echidna\Yves\GoogleAnalytics\Twig;

use Echidna\Shared\GoogleAnalytics\GoogleAnalyticsConstants;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientInterface;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Spryker\Shared\Config\Config;
use Spryker\Shared\Tax\TaxConstants;
use Spryker\Shared\Twig\TwigExtension;
use Twig_Environment;
use Twig_SimpleFunction;

class GoogleAnalyticsTwigExtension extends TwigExtension
{
    const FUNCTION_GOOGLE_TAG_MANAGER = 'GoogleAnalytics';
    const FUNCTION_DATA_LAYER = 'dataLayer';

    /**
     * @var \Silex\Application
     */
    protected $app;

    /**
     * @var string
     */
    protected $containerID;

    /**
     * @var \Spryker\Client\Cart\CartClientInterface
     */
    protected $cartClient;

    /**
     * @var \Spryker\Client\Session\SessionClientInterface
     */
    protected $sessionClient;

    /**
     * @var \Spryker\Yves\Cart\CartFactory
     */
    protected $cartFactory;

    /**
     * @var array
     */
    protected $dataLayerVariables = [];

    /**
     * @var bool
     */
    protected $isEnabled;

    /**
     * @var array
     */
    protected $variableBuilders;

    /**
     * @param string $containerID
     * @param bool $isEnabled
     * @param array $variableBuilders
     * @param \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToCartClientInterface $cartClient
     * @param \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientInterface $sessionClient
     */
    public function __construct(
        string $containerID,
        bool $isEnabled,
        array $variableBuilders,
        GoogleAnalyticsToCartClientInterface $cartClient,
        GoogleAnalyticsToSessionClientInterface $sessionClient
    ) {
        $this->sessionClient = $sessionClient;
        $this->containerID = $containerID;
        $this->cartClient = $cartClient;
        $this->isEnabled = $isEnabled;
        $this->variableBuilders = $variableBuilders;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            $this->createGoogleAnalyticsFunction(),
            $this->createDataLayerFunction(),
        ];
    }

    /**
     * @return \Twig_SimpleFunction
     */
    protected function createGoogleAnalyticsFunction()
    {
        return new Twig_SimpleFunction(
            static::FUNCTION_GOOGLE_TAG_MANAGER,
            [$this, 'renderGoogleAnalytics'],
            [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]
        );
    }

    /**
     * @return \Twig_SimpleFunction
     */
    protected function createDataLayerFunction()
    {
        return new Twig_SimpleFunction(
            static::FUNCTION_DATA_LAYER,
            [$this, 'renderDataLayer'],
            [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]
        );
    }

    /**
     * @param \Twig_Environment $twig
     * @param string $templateName
     *
     * @throws
     *
     * @return string
     */
    public function renderGoogleAnalytics(Twig_Environment $twig, $templateName): string
    {
        if (!$this->isEnabled || !$this->containerID) {
            return '';
        }

        return $twig->render($templateName, [
            'containerID' => $this->containerID,
        ]);
    }

    /**
     * @param \Twig_Environment $twig
     * @param string $page
     * @param array $params
     *
     * @throws
     *
     * @return string
     */
    public function renderDataLayer(Twig_Environment $twig, $page, $params): string
    {
        if (!$this->isEnabled || !$this->containerID) {
            return '';
        }

        $this->addDefaultVariables($page);

        switch ($page) {
            case GoogleAnalyticsConstants::PAGE_TYPE_PRODUCT:
                $productAbstractTransfer = (new ProductAbstractTransfer())
                    ->setTaxRate(Config::get(TaxConstants::DEFAULT_TAX_RATE))
                    ->fromArray($params['product']->toArray(), true);

                $this->addProductVariables($productAbstractTransfer);
                $this->addQuoteVariables();
                break;

            case GoogleAnalyticsConstants::PAGE_TYPE_CATEGORY:
                $this->addCategoryVariables($params['category'], $params['products']);
                $this->addQuoteVariables();
                break;

            case GoogleAnalyticsConstants::PAGE_TYPE_ORDER:
                $this->addOrderVariables($params['order']);

                break;

            case GoogleAnalyticsConstants::PAGE_TYPE_NEWSLETTER_SUBSCRIBE:
                $this->addNewsletterSubscribeVariables($page);

                break;

            default:
                $this->addQuoteVariables();

                break;
        }

        return $twig->render($this->getDataLayerTemplateName(), [
            'data' => $this->dataLayerVariables,
        ]);
    }

    /**
     * @param string $page
     *
     * @return array
     */
    protected function addDefaultVariables($page): array
    {
        return $this->dataLayerVariables = array_merge(
            $this->dataLayerVariables,
            $this->variableBuilders[GoogleAnalyticsConstants::PAGE_TYPE_DEFAULT]->getVariable($page, [
                'clientIp' => $this->getClientIpAddress(),
            ])
        );
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractTransfer $product
     *
     * @return array
     */
    protected function addProductVariables(ProductAbstractTransfer $product): array
    {
        return $this->dataLayerVariables = array_merge(
            $this->dataLayerVariables,
            $this->variableBuilders[GoogleAnalyticsConstants::PAGE_TYPE_PRODUCT]->getVariables($product)
        );
    }

    /**
     * @param array $category
     * @param array $products
     *
     * @return array
     */
    protected function addCategoryVariables($category, $products): array
    {
        return $this->dataLayerVariables = array_merge(
            $this->dataLayerVariables,
            $this->variableBuilders[GoogleAnalyticsConstants::PAGE_TYPE_CATEGORY]->getVariables($category, $products)
        );
    }

    /**
     * @return array
     */
    protected function addQuoteVariables(): array
    {
        $quoteTransfer = $this->cartClient->getQuote();

        if (!$quoteTransfer || count($quoteTransfer->getItems()) == 0) {
            return $this->dataLayerVariables;
        }

        return $this->dataLayerVariables = array_merge(
            $this->dataLayerVariables,
            $this->variableBuilders[GoogleAnalyticsConstants::PAGE_TYPE_QUOTE]->getVariables($quoteTransfer, $this->sessionClient->getId())
        );
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return array
     */
    protected function addOrderVariables(OrderTransfer $orderTransfer)
    {
        return $this->dataLayerVariables = array_merge(
            $this->dataLayerVariables,
            $this->variableBuilders[GoogleAnalyticsConstants::PAGE_TYPE_ORDER]->getVariables($orderTransfer)
        );
    }

    protected function addNewsletterSubscribeVariables(string $page)
    {
        return $this->dataLayerVariables = array_merge(
            $this->dataLayerVariables,
            $this->variableBuilders[GoogleAnalyticsConstants::PAGE_TYPE_NEWSLETTER_SUBSCRIBE]->getVariables($page)
        );
    }

    /**
     * @return string
     */
    protected function getDataLayerTemplateName(): string
    {
        return '@GoogleAnalytics/partials/data-layer.twig';
    }

    /**
     * @return string|null
     */
    protected function getClientIpAddress(): ?string
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $ipAddress;
    }
}
