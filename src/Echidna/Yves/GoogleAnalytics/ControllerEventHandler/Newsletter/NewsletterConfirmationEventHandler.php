<?php

namespace Echidna\Yves\GoogleAnalytics\ControllerEventHandler\Newsletter;

use Echidna\Yves\GoogleAnalytics\ControllerEventHandler\ControllerEventHandlerInterface;
use Echidna\Yves\GoogleAnalytics\Session\GoogleAnalyticsSessionHandlerInterface;
use Generated\Shared\Transfer\GoogleAnalyticsNewsletterDataTransfer;
use Symfony\Component\HttpFoundation\Request;

class NewsletterConfirmationEventHandler implements ControllerEventHandlerInterface
{
    public const METHOD_NAME = 'confirmSubscriptionAction';
    public const TOKEN_GET_PARAMETER = 'token';

    /**
     * @var \Echidna\Yves\GoogleAnalytics\Session\GoogleAnalyticsSessionHandlerInterface
     */
    protected $sessionHandler;

    /**
     * @param \Echidna\Yves\GoogleAnalytics\Session\GoogleAnalyticsSessionHandlerInterface $sessionHandler
     */
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
        $externalIdHash = $request->get(static::TOKEN_GET_PARAMETER);

        if (!$externalIdHash) {
            return;
        }

        $GoogleAnalyticsNewsletterDataTransfer = new GoogleAnalyticsNewsletterDataTransfer();
        $GoogleAnalyticsNewsletterDataTransfer->setExternalIdHash($externalIdHash);

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
