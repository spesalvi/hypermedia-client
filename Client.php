<?php

namespace Razorpay;
require_once 'vendor/autoload.php';
require_once 'LinkParser.php';

use Guzzle\Http;
class Client implements \ArrayAccess
{

	const ACTION_UNSUPPORTED = 'This action is not supported';
	const ITEMS_PER_PAGE = 30;

	private $_apis;
	private $_paginate = false;
	private $_page_nums = 0;
	private $_paginator;

	public function __construct($api_endpoint)
	{
		if(!$api_endpoint)
			return;	
		$http_client = new \GuzzleHttp\Client([
			'base_uri' => $api_endpoint
		]);
		$response = $http_client->request('GET', '');
	        $responseJson =	$response->getBody()
				->getContents();

		$this->_apis = json_decode($responseJson);
		
		$this->_paginate = $this->willPaginate($response);
		if($this->_paginate)
			$this->_page_nums = $this->getNumOfPages($response);

	}
	
	public function __get($var)
	{
		if(isset($this->_apis[$var]))
			return $this->_apis[$var];
		return null;
	}

	public function __call($name, $arguments)
	{
		$url = $this->_apis->{$name . '_url'};
		if($url == null)
		{
			return;
		}

		foreach($arguments as $argument)
		{
			$url = preg_replace('/{[a-z]+}/', $argument, $url, 1);
		}
		return new Client($url);

	}

	public function offsetSet($offset, $value)
	{
		throw new UnsupportedException(self::ACTION_UNSUPPORTED);
	}

	public function offsetExists($offset)
	{
		if($this->_paginate)
			return $offset <= self::ITEMS_PER_PAGE * $this->_page_nums;
		return false;
	}


	public function offsetUnset($offset)
	{
		throw new UnsupportedException(self::ACTION_UNSUPPORTED);	
	}

	public function offsetGet($offset)
	{
		if(!$this->_paginate)
			return null;
		while(count($this->_apis) < $offset)
		{
			$next = $this->fetchNextPage();
			$this->_apis = array_merge($this->_apis, $next);
		}
		
		return $this->_apis[$offset];
	}

	private function willPaginate($response)
	{
		return $response->hasHeader('Link') || 
			is_array($this->_apis);	
	}

	private function getNumOfPages($response)
	{
		$linkHeader = $response->getHeader('Link');
		if(!$linkHeader && is_array($this->_apis))
			return 1;

		$this->_paginator = New \Paginatior($linkHeader[0]);
		return $this->_paginator->getNumOfPages();
	}

	private function fetchNextPage()
	{
		$http_client = new \GuzzleHttp\Client([
			'base_uri' => $this->_paginator->getNextLink()
		]);
		$response = $http_client->request('GET', '');
	        $responseJson =	$response->getBody()
				->getContents();
		return json_decode($responseJson);
	}
}


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
