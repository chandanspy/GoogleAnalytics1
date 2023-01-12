<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\CategoryVariables;

use Generated\Shared\Transfer\GoogleAnalyticsCategoryTransfer;

class ProductSkuCategoryVariableBuilderPlugin implements CategoryVariableBuilderPluginInterface
{
    /**
     * @param \Generated\Shared\Transfer\GoogleAnalyticsCategoryTransfer $GoogleAnalyticsCategoryTransfer
     *
     * @return array
     */
    public function handle(GoogleAnalyticsCategoryTransfer $GoogleAnalyticsCategoryTransfer): array
    {
        $products = [];
        $skus = [];

        foreach ($GoogleAnalyticsCategoryTransfer->getCategoryProducts() as $product) {
            $sku = \str_replace('ABSTRACT-', '', strtoupper($product->getSku()));
            $product->setSku($sku);

            $skus[] = $sku;
            $products[] = $product->toArray();
        }

        return [
            'products' => $skus,
            'categoryProducts' => $products,
        ];
    }
}
