<?php


namespace Echidna\Yves\GoogleTagManager\Session;

use Echidna\Shared\GoogleTagManager\GoogleTagManagerConstants;
use Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToSessionClientInterface;
use Generated\Shared\Transfer\GoogleTagManagerNewsletterDataTransfer;

class GoogleTagManagerSessionHandler implements GoogleTagManagerSessionHandlerInterface
{
    /**
     * @var \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToSessionClientInterface
     */
    protected $sessionClient;

    /**
     * @param \Echidna\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToSessionClientInterface $sessionClient
     */
    public function __construct(GoogleTagManagerToSessionClientInterface $sessionClient)
    {
        $this->sessionClient = $sessionClient;
    }

    /**
     * @param \Generated\Shared\Transfer\GoogleTagManagerNewsletterDataTransfer $googleTagManagerNewsletterDataTransfer
     *
     * @return void
     */
    public function setNewsletterData(GoogleTagManagerNewsletterDataTransfer $googleTagManagerNewsletterDataTransfer): void
    {
        $this->sessionClient->set(GoogleTagManagerConstants::SESSION_NEWSLETTER_DATA, $googleTagManagerNewsletterDataTransfer->toArray());
    }

    /**
     * @return array
     */
    public function getNewsletterData(): array
    {
        $newsletterDataArray = $this->sessionClient->get(GoogleTagManagerConstants::SESSION_NEWSLETTER_DATA);

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
