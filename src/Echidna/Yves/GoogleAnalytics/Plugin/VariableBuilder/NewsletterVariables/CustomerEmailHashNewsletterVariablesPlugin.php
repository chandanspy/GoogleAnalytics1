<?php

namespace Echidna\Yves\GoogleAnalytics\Plugin\VariableBuilder\NewsletterVariables;

use Echidna\Shared\GoogleAnalytics\GoogleAnalyticsConstants;
use Echidna\Yves\GoogleAnalytics\Session\GoogleAnalyticsSessionHandlerInterface;

class CustomerEmailHashNewsletterVariablesPlugin implements NewsletterVariablesPluginInterface
{
    public const EMAIL = 'email';
    public const EXTERNAL_ID_HASH = 'external_id_hash';

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
     * @param array $variables
     * @param array $params
     *
     * @return array
     */
    public function handle(array $variables, array $params = []): array
    {
        $GoogleAnalyticsNewsletterDataArray = $this->sessionHandler->getNewsletterData();

        if (!isset($GoogleAnalyticsNewsletterDataArray[static::EXTERNAL_ID_HASH])) {
            return [];
        }

        $this->sessionHandler->remove(GoogleAnalyticsConstants::SESSION_NEWSLETTER_DATA);

        return [
            'externalIdHash' => $GoogleAnalyticsNewsletterDataArray[static::EXTERNAL_ID_HASH],
        ];
    }
}
