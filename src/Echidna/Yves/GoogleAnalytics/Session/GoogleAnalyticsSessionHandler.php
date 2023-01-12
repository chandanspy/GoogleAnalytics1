<?php


namespace Echidna\Yves\GoogleAnalytics\Session;

use Echidna\Shared\GoogleAnalytics\GoogleAnalyticsConstants;
use Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientInterface;
use Generated\Shared\Transfer\GoogleAnalyticsNewsletterDataTransfer;

class GoogleAnalyticsSessionHandler implements GoogleAnalyticsSessionHandlerInterface
{
    /**
     * @var \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientInterface
     */
    protected $sessionClient;

    /**
     * @param \Echidna\Yves\GoogleAnalytics\Dependency\Client\GoogleAnalyticsToSessionClientInterface $sessionClient
     */
    public function __construct(GoogleAnalyticsToSessionClientInterface $sessionClient)
    {
        $this->sessionClient = $sessionClient;
    }

    /**
     * @param \Generated\Shared\Transfer\GoogleAnalyticsNewsletterDataTransfer $GoogleAnalyticsNewsletterDataTransfer
     *
     * @return void
     */
    public function setNewsletterData(GoogleAnalyticsNewsletterDataTransfer $GoogleAnalyticsNewsletterDataTransfer): void
    {
        $this->sessionClient->set(GoogleAnalyticsConstants::SESSION_NEWSLETTER_DATA, $GoogleAnalyticsNewsletterDataTransfer->toArray());
    }

    /**
     * @return array
     */
    public function getNewsletterData(): array
    {
        $newsletterDataArray = $this->sessionClient->get(GoogleAnalyticsConstants::SESSION_NEWSLETTER_DATA);

        if (\is_array($newsletterDataArray)) {
            return $newsletterDataArray;
        }

        return [];
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key): void
    {
        $this->sessionClient->remove($key);
    }
}
