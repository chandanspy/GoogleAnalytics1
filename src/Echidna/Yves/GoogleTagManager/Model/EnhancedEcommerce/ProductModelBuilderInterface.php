<?php


namespace Echidna\Yves\GoogleTagManager\Model\EnhancedEcommerce;

interface ProductModelBuilderInterface
{
    /**
     * @param array $productsArray
     *
     * @return array
     */
    public function handle(array $productsArray): array;
}
