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
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsConfig getConfig()
 */
class EchidnaEcommerceCartPlugin extends AbstractPlugin implements EchidnaEcommercePageTypePluginInterface
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
                $this->getAddedProductsEvent(),
                $this->getRemovedProductsEvent(),
            ],
        ]);
    }

    /**
     * @return array
     */
    protected function getAddedProductsEvent(): array
    {
        $addedProductsData = $this->getFactory()
            ->createEchidnaEcommerceSessionHandler()
            ->getAddedProducts(true);

        if (\count($addedProductsData) === 0) {
            return [];
        }

        $addedProducts = $this->getFactory()
            ->createEchidnaEcommerceProductArrayBuilder()
            ->handle($addedProductsData);

        $skuList = $this->getSkuListFromProducts($addedProducts);

        $EchidnaEcommerceTransfer = (new EchidnaEcommerceTransfer())
            ->setEvent(EchidnaEcommerceConstants::EVENT_GENERIC)
            ->setEventCategory(EchidnaEcommerceConstants::EVENT_CATEGORY)
            ->setEventAction(EchidnaEcommerceConstants::EVENT_PRODUCT_ADD)
            ->setEventLabel(\implode(',', $skuList))
            ->setEcommerce([
                'add' => [
                    'actionField' => [],
                    'products' => $addedProducts,
                ],
            ]);

        return $EchidnaEcommerceTransfer->toArray();
    }

    /**
     * @param array $products
     *
     * @return array
     */
    protected function getSkuListFromProducts(array $products): array
    {
        $skuList = [];

        foreach ($products as $product) {
            if (!isset($product['id'])) {
                continue;
            }

            \array_push($skuList, $product['id']);
        }

        return $skuList;
    }

    /**
     * @return array
     */
    protected function getRemovedProductsEvent(): array
    {
        $removedProducts = $this->getRemovedProducts();

        if (\count($removedProducts) === 0) {
            return [];
        }

        $skuList = $this->getSkuListFromProducts($removedProducts);

        $EchidnaEcommerceTransfer = (new EchidnaEcommerceTransfer())
            ->setEvent(EchidnaEcommerceConstants::EVENT_GENERIC)
            ->setEventCategory(EchidnaEcommerceConstants::EVENT_CATEGORY)
            ->setEventAction(EchidnaEcommerceConstants::EVENT_PRODUCT_REMOVE)
            ->setEventLabel(\implode(',', $skuList))
            ->setEcommerce([
                    'remove' => [
                        'actionField' => [],
                        'products' => $removedProducts,
                    ],
                ]);

        return $EchidnaEcommerceTransfer->toArray();
    }

    /**
     * @return array
     */
    protected function getRemovedProducts(): array
    {
        $removedProducts = $this->getFactory()
            ->createEchidnaEcommerceSessionHandler()
            ->getRemovedProducts(true);

        if (\count($removedProducts) === 0) {
            return [];
        }

        $products = [];

        foreach ($removedProducts as $productArray) {
            if (!isset($productArray[EchidnaEcommerceConstants::PRODUCT_FIELD_PRODUCT_ABSTRACT_ID])) {
                continue;
            }

            if (!isset($productArray[EchidnaEcommerceConstants::PRODUCT_FIELD_SKU])) {
                continue;
            }

            if (!isset($productArray[EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY])) {
                continue;
            }

            if (!isset($productArray[EchidnaEcommerceConstants::PRODUCT_FIELD_PRICE])) {
                continue;
            }

            $productAbstractData = $this->getFactory()
                ->getProductStorageClient()
                ->findProductAbstractStorageData(
                    $productArray[EchidnaEcommerceConstants::PRODUCT_FIELD_PRODUCT_ABSTRACT_ID],
                    $this->getConfig()->getEchidnaEcommerceLocale()
                );

            $productViewTransfer = (new ProductViewTransfer())->fromArray($productAbstractData, true);
            $productViewTransfer->setPrice($productArray[EchidnaEcommerceConstants::PRODUCT_FIELD_PRICE]);
            $productViewTransfer->setQuantity($productArray[EchidnaEcommerceConstants::PRODUCT_FIELD_QUANTITY]);

            $products[] = $this->getFactory()
                ->getEchidnaEcommerceProductMapperPlugin()
                ->map($productViewTransfer)->toArray();
        }

        return $products;
    }
}
