<?php

namespace Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Newsletter;

use Echidna\Yves\GoogleAnalytics\ControllerEventHandler\ControllerEventHandlerInterface;
use Echidna\Yves\GoogleAnalytics\Session\GoogleAnalyticsSessionHandlerInterface;
use Generated\Shared\Transfer\GoogleAnalyticsNewsletterDataTransfer;
use Symfony\Component\HttpFoundation\Request;

class NewsletterSubscribeEventHandler implements ControllerEventHandlerInterface
{
    public const METHOD_NAME = 'submitAction';
    public const NEWSLETTER_SUBSCRIPTION_FORM = 'NewsletterSubscriptionForm';
    public const NEWSLETTER_SUBSCRIPTION_FORM_EMAIL = 'email';

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Session\GoogleAnalyticsSessionHandlerInterface
     */
    protected $sessionHandler;

    public function __construct(GoogleAnalyticsSessionHandlerInterface $sessionHandler)
    {
        $this->sessionHandler = $sessionHandler;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string|null $locale
     *
     * @return void
     */
    public function handle(Request $request, ?string $locale): void
    {
        $newsletterSubscriptionData = $request->get(static::NEWSLETTER_SUBSCRIPTION_FORM);

        if (!$newsletterSubscriptionData) {
            return;
        }

        if (!isset($newsletterSubscriptionData[static::NEWSLETTER_SUBSCRIPTION_FORM_EMAIL])) {
            return;
        }

        $email = $newsletterSubscriptionData[static::NEWSLETTER_SUBSCRIPTION_FORM_EMAIL];

        $GoogleAnalyticsNewsletterDataTransfer = new GoogleAnalyticsNewsletterDataTransfer();
        $GoogleAnalyticsNewsletterDataTransfer->setExternalIdHash(\sha1($email));

        $this->sessionHandler->setNewsletterData($GoogleAnalyticsNewsletterDataTransfer);
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return static::METHOD_NAME;
    }
}
