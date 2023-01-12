<?php

namespace Echidna\Yves\GoogleAnalytics\Session;

use Generated\Shared\Transfer\EchidnaEcommerceProductDataTransfer;

interface EchidnaEcommerceSessionHandlerInterface
{
    /**
     * @param bool $removeFromSession
     *
     * @return array
     */
    public function getAddedProducts($removeFromSession = false): array;

    /**
     * @param bool $removeFromSession
     *
     * @return array
     */
    public function getRemovedProducts($removeFromSession = false): array;

    /**
     * @param bool $removeFromSessionAfterOutput
     *
     * @return array
     */
    public function getChangeProductQuantityEventArray(bool $removeFromSessionAfterOutput = false): array;

    /**
     * @param \Generated\Shared\Transfer\EchidnaEcommerceProductDataTransfer $productDataTransfer
     *
     * @return void
     */
    public function addProduct(EchidnaEcommerceProductDataTransfer $productDataTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\EchidnaEcommerceProductDataTransfer $ecommerceProductDataTransfer
     *
     * @return void
     */
    public function changeProductQuantity(EchidnaEcommerceProductDataTransfer $ecommerceProductDataTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\EchidnaEcommerceProductDataTransfer $productDataTransfer
     *
     * @return void
     */
    public function removeProduct(EchidnaEcommerceProductDataTransfer $productDataTransfer): void;

    /**
     * @param array $params
     *
     * @return void
     */
    public function setPurchase(array $params): void;

    /**
     * @param bool $removeFromSessionAfterOutput
     *
     * @return array
     */
    public function getPurchase($removeFromSessionAfterOutput = false): array;
}
