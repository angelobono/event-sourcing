<?php
/**
 * @Author: angelobono
 * @Date:   2019-11-15 20:40:31
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2019-11-16 01:29:30
 */
declare(strict_types=1);

namespace Bono\EventSourcing;

class Event
{
    private $eventId;
    private $eventType;
    private $payload;
    private $metadata;
    private $createdAt;

    public function __construct(
        string $eventId,
        string $eventType,
        array $payload,
        array $metadata,
        string $createdAt
    ) {
        $this->eventId = $eventId;
        $this->eventType = $eventType;
        $this->payload = $payload;
        $this->metadata = $metadata;
        $this->createdAt = $createdAt;
    }

    public static function create(
        string $eventType,
        array $payload = [],
        array $metadata = []
    ) {
        return new self(
            uniqid('', true),
            $eventType,
            $payload,
            $metadata,
            date(\DateTimeInterface::ATOM)
        );
    }

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId(),
            'event_type' => $this->eventType(),
            'payload' => $this->payload(),
            'metadata' => $this->metadata(),
            'created_at' => $this->createdAt(),
        ];
    }

    public function withAddedMetadata(array $metadata): Event
    {
        $this->metadata = array_merge($this->metadata(), $metadata);
        return $this;
    }

    public function eventId(): string
    {
        return $this->eventId;
    }

    public function eventType(): string
    {
        return $this->eventType;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function param(string $name, $default = null)
    {
        return $this->payload()[$name] ?? $default;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function metaParam(string $name, $default = null)
    {
        return $this->payload()[$name] ?? $default;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }
}
