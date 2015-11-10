<?php

require_once 'src/Razorpay/Hypermedia/Client.php';
require_once 'src/Razorpay/Hypermedia/Paginator/Page.php';
require_once 'src/Razorpay/Hypermedia/Paginator/Paginator.php';
require_once 'vendor/autoload.php';


use \Razorpay\Hypermedia\Client as Client;

$client = new Client('https://api.github.com/');

$user = $client->user('nzakas');

$repos = $user->repos();

$firstRepo = $repos[34];

echo $firstRepo->name . "\n" . $firstRepo->full_name . "\n"; 


$client2 = new Client('https://api.github.com/');


$user2 = $client2->user('spesalvi');

$repos2 = $user2->repos();

$firstRepo2 = $repos2[0];

echo $firstRepo2->name . "\t" . $firstRepo2->full_name . "\n";
