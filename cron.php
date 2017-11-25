<?php
// this cron has to run as often as you want to fetch fresh data from the feeds
include('core/Aggregator.php');
// register desired services
Aggregator::register('RecipePuppyApi');
Aggregator::register('DogApi');

$db = mysqli_connect('127.0.0.1', 'test', 'test', 'mysql');

$aggregator = new Aggregator($db);
$aggregator->fetch();
