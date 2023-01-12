<?php


namespace Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce;

use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Generated\Shared\Transfer\EchidnaEcommerceTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig getConfig()()
 */
class EchidnaEcommerceCheckoutBillingAddressPlugin extends AbstractPlugin implements EchidnaEcommercePageTypePluginInterface
{
    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return '@GoogleAnalytics/partials/echidna-ecommerce-default.twig';
    }

    /**
     * @param \Twig_Environment $twig
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array|null $params
     *
     * @throws
     *
     * @return string
     */
    public function handle(Twig_Environment $twig, Request $request, ?array $params = []): string
    {
        return $twig->render($this->getTemplate(), [
            'data' => [
                $this->getCartEvent(),
            ],
        ]);
    }

    /**
     * @return array
     */
    protected function getCartEvent(): array
    {
        $EchidnaEcommerceTransfer = (new EchidnaEcommerceTransfer())
            ->setEvent(EchidnaEcommerceConstants::EVENT_GENERIC)
            ->setEventCategory(EchidnaEcommerceConstants::EVENT_CATEGORY)
            ->setEventAction(EchidnaEcommerceConstants::EVENT_CHECKOUT)
            ->setEventLabel(EchidnaEcommerceConstants::CHECKOUT_STEP_BILLING_ADDRESS)
            ->setEcommerce([
                    EchidnaEcommerceConstants::EVENT_CHECKOUT => [
                        'actionField' => [
                            'step' => EchidnaEcommerceConstants::CHECKOUT_STEP_BILLING_ADDRESS,
                        ],
                        'products' => $this->renderCartViewProducts(),
                    ],
                ]);

        return $EchidnaEcommerceTransfer->toArray();
    }

    /**
     * @return array
     */
    protected function renderCartViewProducts(): array
    {
        $products = [];
        $quoteTransfer = $this->getFactory()
            ->getCartClient()
            ->getQuote();

        foreach ($quoteTransfer->getItems() as $item) {
            $productDataAbstract = $this->getFactory()
                ->getProductStorageClient()
                ->findProductAbstractStorageData($item->getIdProductAbstract(), $this->getConfig()->getEchidnaEcommerceLocale());

            if ($productDataAbstract === null) {
                continue;
            }

            $productViewTransfer = (new ProductViewTransfer())->fromArray($productDataAbstract, true);
            $productViewTransfer->setPrice($item->getUnitPrice());
            $productViewTransfer->setQuantity($item->getQuantity());

            $products[] = $this->getFactory()
                ->getEchidnaEcommerceProductMapperPlugin()
                ->map($productViewTransfer)->toArray();
        }

        return $products;
    }
}
