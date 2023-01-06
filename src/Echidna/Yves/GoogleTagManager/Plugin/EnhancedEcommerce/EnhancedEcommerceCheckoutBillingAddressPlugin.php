<?php


namespace Echidna\Yves\GoogleTagManager\Plugin\EnhancedEcommerce;

use Echidna\Shared\GoogleTagManager\EnhancedEcommerceConstants;
use Generated\Shared\Transfer\EnhancedEcommerceTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

/**
 * @method \Echidna\Yves\GoogleTagManager\GoogleTagManagerFactory getFactory()
 * @method \Echidna\Yves\GoogleTagManager\GoogleTagManagerConfig getConfig()()
 */
class EnhancedEcommerceCheckoutBillingAddressPlugin extends AbstractPlugin implements EnhancedEcommercePageTypePluginInterface
{
    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return '@GoogleTagManager/partials/enhanced-ecommerce-default.twig';
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
        $enhancedEcommerceTransfer = (new EnhancedEcommerceTransfer())
            ->setEvent(EnhancedEcommerceConstants::EVENT_GENERIC)
            ->setEventCategory(EnhancedEcommerceConstants::EVENT_CATEGORY)
            ->setEventAction(EnhancedEcommerceConstants::EVENT_CHECKOUT)
            ->setEventLabel(EnhancedEcommerceConstants::CHECKOUT_STEP_BILLING_ADDRESS)
            ->setEcommerce([
                    EnhancedEcommerceConstants::EVENT_CHECKOUT => [
                        'actionField' => [
                            'step' => EnhancedEcommerceConstants::CHECKOUT_STEP_BILLING_ADDRESS,
                        ],
                        'products' => $this->renderCartViewProducts(),
                    ],
                ]);

        return $enhancedEcommerceTransfer->toArray();
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
                ->findProductAbstractStorageData($item->getIdProductAbstract(), $this->getConfig()->getEnhancedEcommerceLocale());

            if ($productDataAbstract === null) {
                continue;
            }

            $productViewTransfer = (new ProductViewTransfer())->fromArray($productDataAbstract, true);
            $productViewTransfer->setPrice($item->getUnitPrice());
            $productViewTransfer->setQuantity($item->getQuantity());

            $products[] = $this->getFactory()
                ->getEnhancedEcommerceProductMapperPlugin()
                ->map($productViewTransfer)->toArray();
        }

        return $products;
    }
}
