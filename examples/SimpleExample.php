<?php
/**
 * @Author: Angelo Bonos
 * @Date:   2019-11-15 20:45:25
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2020-03-07 06:06:25
 */
declare(strict_types=1);

namespace Bono\EventSourcing;

chdir(__DIR__ . '/../');
require 'vendor/autoload.php';

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
