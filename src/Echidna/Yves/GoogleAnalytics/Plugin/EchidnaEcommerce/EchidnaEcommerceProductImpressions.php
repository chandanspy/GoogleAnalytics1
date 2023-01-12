<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce;

use Generated\Shared\Transfer\EchidnaEcommerceTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 */
class EchidnaEcommerceProductImpressions extends AbstractPlugin implements EchidnaEcommercePageTypePluginInterface
{
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
        if (!isset($params['category']) || !isset($params['products'])) {
            return '';
        }

        $category = $params['category'];
        $products = $params['products'];
        $store = $this->getFactory()->getStore();
        $collection = [];
        $counter = 1;

        foreach ($products as $product) {
            $productViewTransfer = (new ProductViewTransfer())->fromArray($product, true);
            $productViewTransfer->setSku($product['abstract_sku']);
            $productViewTransfer->setPrice($product['price']);

            $EchidnaEcommerceProductTransfer = $this->getFactory()
                ->getEchidnaEcommerceProductMapperPlugin()
                ->map($productViewTransfer);

            $collection[] = \array_merge($EchidnaEcommerceProductTransfer->toArray(), [
                'list' => $category['category_key'],
                'position' => $counter++,
            ]);
        }

        $EchidnaEcommerceTransfer = new EchidnaEcommerceTransfer();
        $EchidnaEcommerceTransfer->setEvent('genericEvent');
        $EchidnaEcommerceTransfer->setEventCategory('ecommerce');
        $EchidnaEcommerceTransfer->setEventAction('productImpressions');
        $EchidnaEcommerceTransfer->setEventLabel('');
        $EchidnaEcommerceTransfer->setEcommerce([
            'currencyCode' => $store->getCurrencyIsoCode(),
            'impressions' => $this->stripEmptyValuesFromProductsArray($collection),
        ]);

        return $twig->render($this->getTemplate(), [
            'data' => $EchidnaEcommerceTransfer->toArray(),
        ]);
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return '@GoogleAnalytics/partials/echidna-ecommerce-impressions.twig';
    }

    /**
     * @param array $products
     *
     * @return array
     */
    protected function stripEmptyValuesFromProductsArray(array $products): array
    {
        foreach ($products as $index => $product) {
            foreach ($product as $key => $value) {
                if ($value !== 0 && !$value) {
                    unset($products[$index][$key]);
                }
            }
        }

        return $products;
    }
}
