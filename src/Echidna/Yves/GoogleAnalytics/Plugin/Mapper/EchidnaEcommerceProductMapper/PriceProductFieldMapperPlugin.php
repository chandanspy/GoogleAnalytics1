<?php


namespace Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper;

use DateTime;
use Exception;
use Generated\Shared\Transfer\EchidnaEcommerceProductTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;

/**
 * @method \Echidna\Yves\GoogleAnalytics\GoogleAnalyticsFactory getFactory()
 */
class PriceProductFieldMapperPlugin extends AbstractPlugin implements ProductFieldMapperPluginInterface
{
    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     * @param \Generated\Shared\Transfer\EchidnaEcommerceProductTransfer $EchidnaEcommerceProductTransfer
     * @param array $params
     *
     * @return void
     */
    public function map(
        ProductViewTransfer $productViewTransfer,
        EchidnaEcommerceProductTransfer $EchidnaEcommerceProductTransfer,
        array $params
    ): void {
        if ($this->hasValidSpecialPrice($productViewTransfer)) {
            $specialPrice = $productViewTransfer->getAttributes()['special_price'];

            $specialPrice = $this->getFactory()
                ->createMoneyPlugin()
                ->convertIntegerToDecimal($specialPrice);

            $EchidnaEcommerceProductTransfer->setPrice((string)$specialPrice);

            return;
        }

        if (!$productViewTransfer->getPrice()) {
            return;
        }

        $price = $this->getFactory()
            ->createMoneyPlugin()
            ->convertIntegerToDecimal($productViewTransfer->getPrice());

        $EchidnaEcommerceProductTransfer->setPrice((string)$price);
    }

    protected function hasValidSpecialPrice(ProductViewTransfer $productViewTransfer): bool
    {
        if (!isset($productViewTransfer->getAttributes()['special_price']) ||
            !isset($productViewTransfer->getAttributes()['special_price_from']) ||
            !\array_key_exists('special_price_to', $productViewTransfer->getAttributes())
        ) {
            return false;
        }

        try {
            $specialPriceFromDate = new DateTime($productViewTransfer->getAttributes()['special_price_from']);
        } catch (Exception $e) {
            return false;
        }

        if ($productViewTransfer->getAttributes()['special_price_to'] !== null) {
            try {
                $specialPriceToDate = new DateTime($productViewTransfer->getAttributes()['special_price_to']);
            } catch (Exception $e) {
                return false;
            }
        }

        $current = new DateTime();

        if ($specialPriceFromDate <= $current &&
            ($productViewTransfer->getAttributes()['special_price_to'] === null || $specialPriceToDate >= $current)
        ) {
            return true;
        }

        return false;
    }

    /*
     *
     *
     * {% set isOffer = (
    data.product.attributes.special_price is defined and data.product.attributes.special_price is not empty and (
        data.product.attributes.special_price_from is defined and
        data.product.attributes.special_price_from|date('Y-m-d') <=  "now"|date('Y-m-d')
    ) and (
        data.product.attributes.special_price_to is defined and
        data.product.attributes.special_price_to|date('Y-m-d') >=  "now"|date('Y-m-d')
    )) ? true : false
%}
     *
     *
     *
     */
}
