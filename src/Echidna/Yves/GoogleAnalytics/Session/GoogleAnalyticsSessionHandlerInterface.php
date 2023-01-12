<?php

namespace Echidna\Yves\GoogleAnalytics\Session;

use Generated\Shared\Transfer\GoogleAnalyticsNewsletterDataTransfer;

interface GoogleAnalyticsSessionHandlerInterface
{
    /**
     * @param \Generated\Shared\Transfer\GoogleAnalyticsNewsletterDataTransfer $GoogleAnalyticsNewsletterDataTransfer
     *
     * @return void
     */
    public function setNewsletterData(GoogleAnalyticsNewsletterDataTransfer $GoogleAnalyticsNewsletterDataTransfer): void;

    /**
     * @return array
     */
    public function getNewsletterData(): array;

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key): void;
}
