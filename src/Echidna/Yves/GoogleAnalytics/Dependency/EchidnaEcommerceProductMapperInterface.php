<?php


namespace Echidna\Yves\GoogleAnalytics\Dependency;

use Generated\Shared\Transfer\EchidnaEcommerceProductTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;

interface EchidnaEcommerceProductMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     * @param array $params
     *
     * @return \Generated\Shared\Transfer\EchidnaEcommerceProductTransfer
     */
    public function map(ProductViewTransfer $productViewTransfer, array $params = []): EchidnaEcommerceProductTransfer;
}
