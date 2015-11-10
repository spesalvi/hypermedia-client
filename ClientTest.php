<?php

require_once 'src/Razorpay/Hypermedia/Client.php';
require_once 'src/Razorpay/Hypermedia/Paginator/Page.php';
require_once 'src/Razorpay/Hypermedia/Paginator/Paginator.php';
require_once 'vendor/autoload.php';


use \Razorpay\Hypermedia\Client as Client;

$client = new Client('https://api.github.com/');

$user = $client->user('kangax');

$repos = $user->repos();

$firstRepo = $repos[0];
$secondRepo = $repos[34];
$thirdRepo = $repos[61];

echo $firstRepo->name . "\t" . $firstRepo->full_name . "\n"; 
echo $secondRepo->name . "\t" . $secondRepo->full_name . "\n"; 
echo $thirdRepo->name . "\t" . $thirdRepo->full_name ."\n";


$client2 = new Client('https://api.github.com/');


$user2 = $client2->user('spesalvi');

$repos2 = $user2->repos();

$firstRepo2 = $repos2[0];

echo $firstRepo2->name . "\t" . $firstRepo2->full_name . "\n";

