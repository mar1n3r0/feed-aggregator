<?php
include('core/Aggregator.php');

$db = mysqli_connect('127.0.0.1', 'test', 'test', 'mysql');

$aggregator = new Aggregator($db);

// get latest 30 items
$allItems = $aggregator->query();
var_dump($allItems);

/** customized aggregation by service, date and limit using method chaining
/*  The concept is similar to any ORM and uses the concept of Laravel's Eloquent
*/
$aggregatorFiltered = new Aggregator($db);

$filteredItems = $aggregatorFiltered
            ->service('dogs')
            ->from_date('2017-09-23')
            ->to_date('2017-09-25')
            ->limit(5)
            ->query();
var_dump($filteredItems);

// count aggregated items, can be customized as above
$aggregatorCount = new Aggregator($db);

$dogsCount = $aggregatorCount
              ->service('dogs')
              ->count()
              ->query();
var_dump($dogsCount);
