<?php

namespace Echidna\Yves\GoogleTagManager\Plugin\EnhancedEcommerce;

use Echidna\Shared\GoogleTagManager\EnhancedEcommerceConstants;
use Echidna\Yves\GoogleTagManager\Plugin\Mapper\EnhancedEcommerceProductMapper\CouponProductFieldMapperPlugin;
use Generated\Shared\Transfer\EnhancedEcommerceTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

/**
 * @method \Echidna\Yves\GoogleTagManager\GoogleTagManagerFactory getFactory()
 * @method \Echidna\Yves\GoogleTagManager\GoogleTagManagerConfig getConfig()()
 */
class EnhencedEcommercePurchasePlugin extends AbstractPlugin implements EnhancedEcommercePageTypePluginInterface
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
        /** @var \Generated\Shared\Transfer\OrderTransfer $orderTransfer */
        $orderTransfer = $params['order'];

        $enhancedEcommerceTransfer = (new EnhancedEcommerceTransfer())
            ->setEvent(EnhancedEcommerceConstants::EVENT_GENERIC)
            ->setEventCategory(EnhancedEcommerceConstants::EVENT_CATEGORY)
            ->setEventAction(EnhancedEcommerceConstants::EVENT_PURCHASE)
            ->setEventLabel('')
            ->setEcommerce([
                'currencyCode' => $orderTransfer->getCurrencyIsoCode(),
                EnhancedEcommerceConstants::EVENT_PURCHASE => [
                    'actionField' => [
                        'id' => $orderTransfer->getOrderReference(),
                        'affiliation' => $orderTransfer->getStore(),
                        'revenue' => (string)$orderTransfer->getTotals()->getGrandTotal() / 100,
                        'tax' => (string)$orderTransfer->getTotals()->getTaxTotal()->getAmount() / 100,
                        'shipping' => $this->getShipping() / 100,
                        'coupon' => $this->getDiscountCode($orderTransfer),
                    ],
                    'products' => \array_values($this->getProducts($orderTransfer)),
                ],
            ]);

        return $twig->render($this->getTemplate(), [
            'data' => [
                $enhancedEcommerceTransfer->toArray(),
            ],
        ]);
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return array
     */
    protected function getProducts(OrderTransfer $orderTransfer): array
    {
        $products = [];

        foreach ($orderTransfer->getItems() as $itemTransfer) {
            $discountCodes = [];

            if (isset($products[$itemTransfer->getSku()])) {
                $products[$itemTransfer->getSku()]['quantity']++;

                continue;
            }

            foreach ($itemTransfer->getCalculatedDiscounts() as $discountTransfer) {
                $discountCodes[] = $discountTransfer->getVoucherCode();
            }

            $productDataAbstract = $this->getFactory()
                ->getProductStorageClient()
                ->findProductAbstractStorageData(
                    $itemTransfer->getIdProductAbstract(),
                    $this->getConfig()->getEnhancedEcommerceLocale()
                );

            $productViewTransfer = (new ProductViewTransfer())->fromArray($productDataAbstract, true);
            $productViewTransfer->setPrice($itemTransfer->getUnitPrice());
            $productViewTransfer->setQuantity($itemTransfer->getQuantity());

            $products[$itemTransfer->getSku()] = $this->getFactory()
                ->getEnhancedEcommerceProductMapperPlugin()
                ->map($productViewTransfer, [CouponProductFieldMapperPlugin::FIELD_NAME => $discountCodes])
                ->toArray();
        }

        return $products;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @throws
     *
     * @return string
     */
    protected function getDiscountCode(OrderTransfer $orderTransfer): string
    {
        $voucherCodes = [];

        foreach ($orderTransfer->getCalculatedDiscounts() as $discountTransfer) {
            $voucherCodes[] = $discountTransfer->getVoucherCode();
        }

        return \implode(',', $voucherCodes);
    }

    /**
     * @return string
     */
    protected function getShipping(): string
    {
        $purchaseSession = $this->getFactory()
            ->createEnhancedEcommerceSessionHandler()
            ->getPurchase(true);

        if (isset($purchaseSession['shipment'])) {
            return $purchaseSession['shipment'];
        }

        return '0';
    }
}
