<?php


namespace Echidna\Yves\GoogleAnalytics\Dependency\Client;

use Generated\Shared\Transfer\ProductViewTransfer;

interface GoogleAnalyticsToProductStorageClientInterface
{
    /**
     * @param int $idProductAbstract
     * @param string $localeName
     *
     * @return array|null
     */
    public function findProductAbstractStorageData(int $idProductAbstract, string $localeName): ?array;

    /**
     * @param array $data
     * @param $localeName
     * @param array $selectedAttributes
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer
     */
    public function mapProductStorageData(array $data, $localeName, array $selectedAttributes = []): ProductViewTransfer;
}
