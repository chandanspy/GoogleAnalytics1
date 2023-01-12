<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\Mapper\EchidnaEcommerceProductMapper;

use Generated\Shared\Transfer\EchidnaEcommerceProductTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;

class IdProductFieldMapperPlugin implements ProductFieldMapperPluginInterface
{
    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     * @param \Generated\Shared\Transfer\EchidnaEcommerceProductTransfer $EchidnaEcommerceProductTransfer
     * @param array $params
     *
     * @return void
     */
    public function map(ProductViewTransfer $productViewTransfer, EchidnaEcommerceProductTransfer $EchidnaEcommerceProductTransfer, array $params): void
    {
        $sku = \str_replace('ABSTRACT-', '', strtoupper($productViewTransfer->getSku()));

        $EchidnaEcommerceProductTransfer->setId($sku);
    }
}
