<?php
/**
 * @Author: Angelo Bono
 * @Date:   2019-11-15 20:46:33s
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2019-11-16 01:56:24
 */
declare(strict_types=1);

namespace Bono\EventSourcing;

chdir(__DIR__ . '/../');
require 'vendor/autoload.php';
require 'Person.php';

// Create an aggregate,
// which records all changes in a versionable event stream.
$person = Person::create('Max', 'Mustermann');
$person->changeName('Moritz', 'Mustermann');

// Append the recorded event stream to the event store
$eventStore = new EventStore(new Adapter\FilesystemAdapter());
$eventStore->append($person->popEventStream());
$eventStore->flush();
