<?php


namespace Echidna\Yves\GoogleAnalytics\Plugin\EchidnaEcommerce;

use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Generated\Shared\Transfer\EchidnaEcommerceTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 */
class EchidnaEcommerceProductDetailPlugin extends AbstractPlugin implements EchidnaEcommercePageTypePluginInterface
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
        $productViewTransfer = $params['product'];

        $products[] = $this->getFactory()
            ->getEchidnaEcommerceProductMapperPlugin()
            ->map($productViewTransfer)->toArray();

        return $twig->render($this->getTemplate(), [
            'data' => [
                $this->renderProductDetail($products),
            ],
        ]);
    }

    /**
     * @param array $products
     *
     * @return array
     */
    protected function renderProductDetail(array $products): array
    {
        $products = $this->stripEmptyValuesFromProductsArray($products);

        $EchidnaEcommerceTransfer = (new EchidnaEcommerceTransfer())
            ->setEvent(EchidnaEcommerceConstants::EVENT_GENERIC)
            ->setEventCategory(EchidnaEcommerceConstants::EVENT_CATEGORY)
            ->setEventAction(EchidnaEcommerceConstants::EVENT_PRODUCT_DETAIL)
            ->setEventLabel($products[0]['id'])
            ->setEcommerce([
                'detail' => [
                    'actionField' => [],
                    'products' => $products,
                ],
            ]);

        return $EchidnaEcommerceTransfer->toArray();
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

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return '@GoogleAnalytics/partials/echidna-ecommerce-default.twig';
    }
}
