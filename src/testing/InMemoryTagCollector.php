<?php

declare(strict_types=1);

namespace parallelogram\craftkit\testing;

use LogicException;
use Override;
use parallelogram\craftkit\cache\CollectedTags;
use parallelogram\craftkit\cache\TagCollectorInterface;

use function array_keys;
use function array_pop;
use function array_replace;
use function array_values;

/**
 * A tag collector for tests that nests and merges exactly as Craft's does,
 * without needing a Craft application.
 *
 * @api
 */
final class InMemoryTagCollector implements TagCollectorInterface
{
    /**
     * @var array<string, true>|null
     */
    private ?array $current = null;

    /**
     * @var list<array<string, true>>
     */
    private array $outer = [];

    #[Override]
    public function collect(array $tags): void
    {
        if (null === $this->current) {
            return;
        }

        foreach ($tags as $tag) {
            $this->current[$tag] = true;
        }
    }

    #[Override]
    public function isCollecting(): bool
    {
        return null !== $this->current;
    }

    #[Override]
    public function start(): void
    {
        if (null !== $this->current) {
            $this->outer[] = $this->current;
        }

        $this->current = [];
    }

    /**
     * @throws LogicException When no collection is open.
     */
    #[Override]
    public function stop(): CollectedTags
    {
        if (null === $this->current) {
            throw new LogicException('No tag collection is open.');
        }

        $collected     = $this->current;
        $parent        = array_pop($this->outer);
        $this->current = null === $parent ? null : array_replace($parent, $collected);

        return new CollectedTags(array_values(array_keys($collected)));
    }
}
