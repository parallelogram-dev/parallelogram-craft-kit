<?php

declare(strict_types=1);

namespace parallelogram\craftkit\craft;

use parallelogram\craftkit\urls\UrlProviderInterface;
use parallelogram\craftkit\urls\UrlProviderRegistry;
use yii\base\Event;

/**
 * Lets plugins offer URL providers without depending on each other: providers
 * register through {@see EVENT_REGISTER}, consumers call {@see collect()}.
 *
 * @api
 */
final class UrlProviders
{
    public const string EVENT_REGISTER = 'registerUrlProviders';

    public static function collect(): UrlProviderRegistry
    {
        $event = new RegisterUrlProvidersEvent();
        Event::trigger(self::class, self::EVENT_REGISTER, $event);

        $registry = new UrlProviderRegistry();

        foreach ($event->providers as $provider) {
            $registry->add($provider);
        }

        return $registry;
    }

    /**
     * Registers a provider for every later {@see collect()} call.
     */
    public static function register(UrlProviderInterface $provider): void
    {
        Event::on(
            self::class,
            self::EVENT_REGISTER,
            static function (RegisterUrlProvidersEvent $event) use ($provider): void {
                $event->providers[] = $provider;
            },
        );
    }
}
