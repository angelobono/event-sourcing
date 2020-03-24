<?php
/**
 * @Author: Angelo Bono
 * @Date:   2019-11-15 20:46:33
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2020-03-04 05:24:57
 */
declare (strict_types = 1);

namespace Bono\EventSourcing;

class Person implements Api\AggregateRootInterface
{
    private $aggregateId;
    private $firstName;
    private $lastName;
    private $createdAt;
    private $eventStream;

    protected function __construct(
        string $aggregateId,
        string $firstName,
        string $lastName,
        string $createdAt,
        VersionableEventStream $eventStream
    ) {
        $this->eventStream = $eventStream;
        $this->aggregateId = $aggregateId;
        $this->createdAt = $createdAt;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /**
     * Creates a new Person
     */
    public static function create(string $firstName, string $lastName): Person
    {
        $person = new Person(
            uniqid('', true),
            $firstName,
            $lastName,
            date(\DateTimeInterface::ATOM),
            new VersionableEventStream(Person::class)
        );
        $person->eventStream->record(
            $person,
            Event::create('PersonWasCreated', $person->toArray())
        );
        return $person;
    }

    /**
     * Apply event "PersonWasCreated"
     */
    public function whenPersonWasCreated(Event $event)
    {
        $this->aggregateId = $event->metaParam('aggregate_id', '');
        $this->firstName = $event->param('first_name', '');
        $this->lastName = $event->param('last_name', '');
        $this->createdAt = $event->createdAt();
    }

    /**
     * Changes the persons name
     */
    public function changeName(string $firstName, string $lastName)
    {
        $this->eventStream->record($this, Event::create('PersonNameWasChanged', [
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]));
    }

    /**
     * Apply event "PersonNameWasChanged"
     */
    public function whenPersonNameWasChanged(Event $event)
    {
        $this->firstName = $event->param('first_name', $this->firstName());
        $this->lastName = $event->param('last_name', $this->lastName());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'aggregate_id' => $this->aggregateId(),
            'version' => $this->version(),
            'first_name' => $this->firstName(),
            'last_name' => $this->lastName(),
            'created_at' => $this->createdAt(),
        ];
    }

    /**
     * Returns the recorded event stream and
     * creates a new with the current version.
     */
    public function popEventStream(): EventStream
    {
        $eventStream = $this->eventStream;
        $this->eventStream = new VersionableEventStream(
            Person::class,
            $eventStream->version(),
            []
        );
        return $eventStream;
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    public function version(): int
    {
        return $this->eventStream->version();
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }
}
