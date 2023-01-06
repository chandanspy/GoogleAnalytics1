<?php

namespace Echidna\Yves\GoogleTagManager\Dependency\Client;

use Generated\Shared\Transfer\QuoteTransfer;

interface GoogleTagManagerToCartClientInterface
{
    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function getQuote(): QuoteTransfer;
}
