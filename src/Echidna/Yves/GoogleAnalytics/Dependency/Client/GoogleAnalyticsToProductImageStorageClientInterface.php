<?php


namespace Echidna\Yves\GoogleAnalytics\Dependency\Client;

use Spryker\Client\ProductImageStorage\Storage\ProductAbstractImageStorageReaderInterface;

interface GoogleAnalyticsToProductImageStorageClientInterface
{
    /**
     * @return \Spryker\Client\ProductImageStorage\Storage\ProductAbstractImageStorageReaderInterface
     */
    public function getProductAbstractImageStorageReader(): ProductAbstractImageStorageReaderInterface;
}
