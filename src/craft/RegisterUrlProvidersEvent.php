<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft;

use parallelogram\craftkit\urls\UrlProviderInterface;
use yii\base\Event;

/**
 * Raised when the URL providers are gathered. Add yours to `$providers`.
 *
 * @api
 */
final class RegisterUrlProvidersEvent extends Event
{
    /**
     * @var list<UrlProviderInterface>
     */
    public array $providers = [];
}
