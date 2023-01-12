<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce;

use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper\CouponProductFieldMapperPlugin;
use Generated\Shared\Transfer\EchidnaEcommerceTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig getConfig()()
 */
class EchidnaEcommercePurchasePlugin extends AbstractPlugin implements EchidnaEcommercePageTypePluginInterface
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
     * @param Request $request
     * @param array|null $params
     *
     * @throws
     *
     * @return string
     */
    public function handle(Twig_Environment $twig, Request $request, ?array $params = []): string
    {
        /** @var OrderTransfer $orderTransfer */
        $orderTransfer = $params['order'];

        $EchidnaEcommerceTransfer = (new EchidnaEcommerceTransfer())
            ->setEvent(EchidnaEcommerceConstants::EVENT_GENERIC)
            ->setEventCategory(EchidnaEcommerceConstants::EVENT_CATEGORY)
            ->setEventAction(EchidnaEcommerceConstants::EVENT_PURCHASE)
            ->setEventLabel('')
            ->setEcommerce([
                'currencyCode' => $orderTransfer->getCurrencyIsoCode(),
                EchidnaEcommerceConstants::EVENT_PURCHASE => [
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
                $EchidnaEcommerceTransfer->toArray(),
            ],
        ]);
    }

    /**
     * @param OrderTransfer $orderTransfer
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
                    $this->getConfig()->getEchidnaEcommerceLocale()
                );

            $productViewTransfer = (new ProductViewTransfer())->fromArray($productDataAbstract, true);
            $productViewTransfer->setPrice($itemTransfer->getUnitPrice());
            $productViewTransfer->setQuantity($itemTransfer->getQuantity());

            $products[$itemTransfer->getSku()] = $this->getFactory()
                ->getEchidnaEcommerceProductMapperPlugin()
                ->map($productViewTransfer, [CouponProductFieldMapperPlugin::FIELD_NAME => $discountCodes])
                ->toArray();
        }

        return $products;
    }

    /**
     * @param QuoteTransfer $quoteTransfer
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
            ->createEchidnaEcommerceSessionHandler()
            ->getPurchase(true);

        if (isset($purchaseSession['shipment'])) {
            return $purchaseSession['shipment'];
        }

        return '0';
    }
}
