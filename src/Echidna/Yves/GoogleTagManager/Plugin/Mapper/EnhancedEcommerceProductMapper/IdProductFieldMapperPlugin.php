<?php

namespace Echidna\Yves\GoogleTagManager\Plugin\Mapper\EnhancedEcommerceProductMapper;

use Generated\Shared\Transfer\EnhancedEcommerceProductTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;

class IdProductFieldMapperPlugin implements ProductFieldMapperPluginInterface
{
    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     * @param \Generated\Shared\Transfer\EnhancedEcommerceProductTransfer $enhancedEcommerceProductTransfer
     * @param array $params
     *
     * @return void
     */
    public function map(ProductViewTransfer $productViewTransfer, EnhancedEcommerceProductTransfer $enhancedEcommerceProductTransfer, array $params): void
    {
        $sku = \str_replace('ABSTRACT-', '', strtoupper($productViewTransfer->getSku()));

        $enhancedEcommerceProductTransfer->setId($sku);
    }
}
