<?php


namespace Echidna\Yves\GoogleAnalytics\Model\EchidnaEcommerce;

interface ProductModelBuilderInterface
{
    /**
     * @param array $productsArray
     *
     * @return array
     */
    public function handle(array $productsArray): array;
}
