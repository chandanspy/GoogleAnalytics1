<?php


namespace Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper;

use Echidna\Shared\GoogleAnalytics\EchidnaEcommerceConstants;
use Generated\Shared\Transfer\EchidnaEcommerceProductTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;

class Dimension10ProductFieldMapperPlugin implements ProductFieldMapperPluginInterface
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
        $attributes = $productViewTransfer->getAttributes();

        if (!\is_array($attributes) || \count($attributes) === 0) {
            return;
        }

        if (isset($attributes[EchidnaEcommerceConstants::PRODUCT_FIELD_ATTRIBUTE_SIZE_UNTRANSLATED])) {
            $EchidnaEcommerceProductTransfer->setDimension10($attributes[EchidnaEcommerceConstants::PRODUCT_FIELD_ATTRIBUTE_SIZE_UNTRANSLATED]);

            return;
        }

        if (isset($attributes[EchidnaEcommerceConstants::PRODUCT_FIELD_ATTRIBUTE_SIZE])) {
            $EchidnaEcommerceProductTransfer->setDimension10($attributes[EchidnaEcommerceConstants::PRODUCT_FIELD_ATTRIBUTE_SIZE]);

            return;
        }
    }
}
