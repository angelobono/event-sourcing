<?php
/**
 * @Author: Angelo Bono
 * @Date:   2019-11-15 20:41:01
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2020-03-04 05:32:08
 */
declare (strict_types = 1);

namespace Bono\EventSourcing\Adapter;

use Bono\EventSourcing\Api;
use Bono\EventSourcing\Event;
use Bono\EventSourcing\EventStream;

/**
 * Class FilesystemAdapter
 */
final class FilesystemAdapter implements Api\EventStoreAdapterInterface
{
    private $storage;

    /**
     * @param string $topic
     *
     * @return EventStream
     *
     * @throws \Exception
     */
    public function read(string $topic): EventStream
    {
        $eventStream = new EventStream($topic);
        $topic = str_replace('\\', '_', $topic);
        $filename = $this->storage() . DIRECTORY_SEPARATOR . $topic;

        if (!file_exists($filename)) {
            file_put_contents($filename, '');
        }
        if (!is_readable($filename)) {
            error_log(
                'Event Store (FilesystemAdapter): ' .
                'The requested event stream "' . $topic . '" is not readable!'
            );
        }

        foreach (file($filename, FILE_SKIP_EMPTY_LINES) as $key => $event) {
            $data = json_decode($event, true);
            $eventStream->push([new Event(
                $data['event_id'] ?? '',
                $data['event_type'] ?? Event::class,
                $data['payload'] ?? [],
                $data['metadata'] ?? [],
                $data['created_at'] ?? date(\DateTimeInterface::ATOM),
            )]);
        }
        // $eventStream->replay($events);
        return $eventStream;
    }

    /**
     * @param EventStream $events
     * @throws \Exception
     */
    public function write(EventStream $eventStream)
    {
        $topic = str_replace('\\', '_', $eventStream->topic());
        $events = $eventStream->events();

        while (!$events->isEmpty()) {
            $event = $events->pop();

            file_put_contents(
                $this->storage() . DIRECTORY_SEPARATOR . $topic,
                json_encode($event->toArray()) . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    /**
     * @param string $topic
     * @param array $criteria
     * @param int $limit
     * @param int $offset
     *
     * @return EventStream
     *
     * @throws \Exception
     */
    public function query(string $topic, array $criteria, int $limit = 1000, int $offset = 0): array
    {
        $topic = str_replace('\\', '_', $topic);
        $filename = $this->storage() . DIRECTORY_SEPARATOR . $topic;

        if (!file_exists($filename)) {
            throw new \DomainException('The requested topic was not found!', 404);
        }

        $handle = fopen($filename, 'r');
        if (!$handle) {
            throw new \RuntimeException('Invalid event stream on requested topic!', 500);
        }

        $result = [];
        $eventCount = 0;

        while (($line = fgets($handle)) !== false) {
            foreach ($criteria as $field => $value) {
                preg_match_all('/"' . $field . '":"' . $value . '"/m', $line, $matches);
                $matches = $matches[0][0] ?? null;
                if (!$matches) {
                    continue;
                }
                $eventCount++;
                if ($eventCount >= $offset && $eventCount <= $limit) {
                    $data = json_decode($line, true);
                    $result[] = new Event(
                        $data['event_id'] ?? '',
                        $data['event_type'] ?? Event::class,
                        $data['payload'] ?? [],
                        $data['metadata'] ?? [],
                        $data['created_at'] ?? date(\DateTimeInterface::ATOM),
                    );
                }
            }
        }
        fclose($handle);
        return $result;
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    private function storage()
    {
        if (!$this->storage) {
            if (file_exists('./data')) {
                $this->storage = './data/event-streams';
            } else {
                error_log(
                    'The Filesystem Adapter uses a fallback directory "/tmp/event-streams"!',
                    E_USER_NOTICE
                );
                $this->storage = '/tmp/event-streams';
            }
            if (!file_exists($this->storage)) {
                mkdir($this->storage);
            }
            if (!is_writable($this->storage)) {
                throw new \Exception('Event stream is not writeable!');
            }
        }
        return $this->storage;
    }
}
