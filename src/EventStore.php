<?php
/**
 * @Author: Angelo Bono
 * @Date:   2019-11-15 20:41:01
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2020-03-13 19:24:09
 */
declare(strict_types=1);

namespace Bono\EventSourcing;

class EventStore
{
    private $isInTransaction = false;
    private $eventStoreAdapter;
    private $eventStreams;

    public function __construct(Api\EventStoreAdapterInterface $eventStoreAdapter)
    {
        $this->eventStoreAdapter = $eventStoreAdapter;
        $this->eventStreams = [];
    }

    public function load(string $topic): EventStream
    {
        return $this->eventStoreAdapter->read($topic);
    }

    public function append(EventStream $eventStream)
    {
        if (isset($this->eventStreams[$eventStream->topic()])) {
            $this->eventStreams[$eventStream->topic()]->push($eventStream->events());
            return;
        }
        $this->eventStreams[$eventStream->topic()] = $eventStream;
    }

    /**
     * Write event data to storage
     */
    public function flush()
    {
        foreach ($this->eventStreams as $topic => $eventStream) {
            $this->eventStoreAdapter->write($eventStream);
            $this->eventStreams[$topic] = [];
        }
    }
}
