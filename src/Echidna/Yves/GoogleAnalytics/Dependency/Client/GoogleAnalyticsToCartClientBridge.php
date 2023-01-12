<?php


namespace Echidna\Yves\GoogleAnalytics\Dependency\Client;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Client\Cart\CartClientInterface;

class GoogleAnalyticsToCartClientBridge implements GoogleAnalyticsToCartClientInterface
{
    /**
     * @var \Spryker\Client\Cart\CartClientInterface
     */
    protected $cartClient;

    public function __construct(CartClientInterface $cartClient)
    {
        $this->cartClient = $cartClient;
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function getQuote(): QuoteTransfer
    {
        return $this->cartClient->getQuote();
    }
}
