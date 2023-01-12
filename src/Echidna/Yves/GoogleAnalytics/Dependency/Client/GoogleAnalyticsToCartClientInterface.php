<?php

namespace Echidna\Yves\GoogleAnalytics\Dependency\Client;

use Generated\Shared\Transfer\QuoteTransfer;

interface GoogleAnalyticsToCartClientInterface
{
    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function getQuote(): QuoteTransfer;
}
