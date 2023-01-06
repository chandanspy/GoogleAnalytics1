<?php

namespace Echidna\Yves\GoogleTagManager\ControllerEventHandler\Newsletter;

use Echidna\Yves\GoogleTagManager\ControllerEventHandler\ControllerEventHandlerInterface;
use Echidna\Yves\GoogleTagManager\Session\GoogleTagManagerSessionHandlerInterface;
use Generated\Shared\Transfer\GoogleTagManagerNewsletterDataTransfer;
use Symfony\Component\HttpFoundation\Request;

class NewsletterConfirmationEventHandler implements ControllerEventHandlerInterface
{
    public const METHOD_NAME = 'confirmSubscriptionAction';
    public const TOKEN_GET_PARAMETER = 'token';

    /**
     * @var \Echidna\Yves\GoogleTagManager\Session\GoogleTagManagerSessionHandlerInterface
     */
    protected $sessionHandler;

    /**
     * @param \Echidna\Yves\GoogleTagManager\Session\GoogleTagManagerSessionHandlerInterface $sessionHandler
     */
    public function __construct(GoogleTagManagerSessionHandlerInterface $sessionHandler)
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

        $googleTagManagerNewsletterDataTransfer = new GoogleTagManagerNewsletterDataTransfer();
        $googleTagManagerNewsletterDataTransfer->setExternalIdHash($externalIdHash);

        $this->sessionHandler->setNewsletterData($googleTagManagerNewsletterDataTransfer);
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return static::METHOD_NAME;
    }
}
