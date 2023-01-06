<?php

namespace Echidna\Yves\GoogleTagManager\Dependency\Client;

use Echidna\Client\ProductImageStorage\ProductImageStorageClientInterface;
use Spryker\Client\ProductImageStorage\Storage\ProductAbstractImageStorageReaderInterface;

class GoogleTagManagerToProductImageStorageClientBridge implements GoogleTagManagerToProductImageStorageClientInterface
{
    /**
     * @var \Spryker\Client\ProductImageStorage\ProductImageStorageClientInterface
     */
    protected $productImageStorageClient;

    /**
     * CartPageToProductImageStorageBridge constructor.
     *
     * @param \Echidna\Client\ProductImageStorage\ProductImageStorageClientInterface $productImageStorageClient
     */
    public function __construct(ProductImageStorageClientInterface $productImageStorageClient)
    {
        $this->productImageStorageClient = $productImageStorageClient;
    }

    public function getProductAbstractImageStorageReader(): ProductAbstractImageStorageReaderInterface
    {
        return $this->productImageStorageClient->getProductAbstractImageStorageReader();
    }
}
