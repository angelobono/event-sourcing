
## A small and easy to use Event Store Solution for PHP

# Simple Event Stream Example:

```
<?php

// Create an event stream
$eventStream = new EventStream(EventStream::class, [
    Event::create('CreatePerson', [
        'first_name' => 'Max',
        'last_name' => 'Mustermann'
    ]),
    Event::create('ChangeName', ['first_name' => 'Max']),
    Event::create('ChangeName', ['last_name' => 'Mustermann']),
]);

// Append the recorded event stream to the event store
$eventStore = new EventStore(new Adapter\FilesystemAdapter());
$eventStore->append($eventStream);
$eventStore->flush();
```

# Aggregate based Example:

```
<?php

// Create an aggregate and record all changes in a versionable event stream.
$person = Person::create('Max', 'Mustermann');
$person->changeName('Moritz', 'Mustermann');

// Append the recorded event stream to the event store
$eventStore = new EventStore(new Adapter\FilesystemAdapter());
$eventStore->append($person->popEventStream());
$eventStore->flush();
```

# The Aggregate:

```
<?php

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
    
    ...
}
```
