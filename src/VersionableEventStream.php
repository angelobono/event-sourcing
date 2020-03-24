<?php
/**
 * @Author: Angelo Bono
 * @Date:   2019-11-15 21:28:49
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2019-11-16 01:55:12
 */
declare(strict_types=1);

namespace Bono\EventSourcing;

class VersionableEventStream extends EventStream
{
    /**
     * @var int
     */
    private $version;

    /**
     * Creates a versionable event stream
     */
    public function __construct(string $topic, int $version = 0, array $events = [])
    {
        parent::__construct($topic, $events);
        $this->version = $version;
    }

    /**
     * @return int
     */
    public function version(): int
    {
        return $this->version;
    }

    /**
     * @param Api\AggregateRootInterface &$aggregate
     * @param Event $event
     */
    public function record(Api\AggregateRootInterface &$aggregate, Event $event)
    {
        $this->version += 1;
        $this->push([$event->withAddedMetadata([
            'aggregate_id' => $aggregate->aggregateId(),
            'aggregate_version' => $this->version(),
            'aggregate_type' => get_class($aggregate),
        ])]);
        $this->apply($aggregate, $event);
    }

    /**
     * @param Api\AggregateRootInterface &$aggregate
     * @param Event $event
     *
     * @throws \BadMethodCallException if the event handler was not found!
     */
    protected function apply(Api\AggregateRootInterface &$aggregate, Event $event)
    {
        $handler = 'when' . ucfirst($event->eventType());

        if (! method_exists($aggregate, $handler)) {
            throw new \BadMethodCallException(
                'Event handler "' . $event->eventType() . '" was not found in ' .
                get_class($aggregate)
            );
        }
        $aggregate->$handler($event);
    }

    /**
     * @param Api\AggregateRootInterface &$aggregate
     * @param EventStream $eventSteam
     */
    protected function replay(Api\AggregateRootInterface &$aggregate, EventStream $eventStream)
    {
        while ($event = $eventStream->pop()) {
            $this->version = $event->metadata()['aggregate_version'] ?? ($this->version() + 1);
            $aggregate->apply($event);
        }
    }
}
