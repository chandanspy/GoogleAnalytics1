<?php


namespace Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper;

use Generated\Shared\Transfer\EchidnaEcommerceProductTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;

class CouponProductFieldMapperPlugin implements ProductFieldMapperPluginInterface
{
    public const FIELD_NAME = 'discountCodes';

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     * @param \Generated\Shared\Transfer\EchidnaEcommerceProductTransfer $EchidnaEcommerceProductTransfer
     * @param array $params
     *
     * @return void
     */
    public function map(ProductViewTransfer $productViewTransfer, EchidnaEcommerceProductTransfer $EchidnaEcommerceProductTransfer, array $params): void
    {
        if (!isset($params[static::FIELD_NAME])) {
            return;
        }

        if (!\is_array($params[static::FIELD_NAME])) {
            return;
        }

        $EchidnaEcommerceProductTransfer->setCoupon(\rtrim(\implode(',', $params[static::FIELD_NAME]), ','));
    }
}
