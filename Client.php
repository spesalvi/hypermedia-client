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
		
		$this->_paginate = $this->willPaginate($response);
		if($this->_paginate)
			$this->_page_nums = $this->getNumOfPages($response);

		$this->_apis = json_decode($responseJson);
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
		return $this->_paginate && 
			$offset <= self::ITEMS_PER_PAGE * $this->_page_nums;
	}


	public function offsetUnset($offset)
	{
		throw new UnsupportedException(self::ACTION_UNSUPPORTED);	
	}

	public function offsetGet($offset)
	{
		if(!$this->_paginate)
			return null;
		if(isset($this->_apis[$offset]))
			return $this->_apis[$offset];
		while(count($this->_apis) < $offset)
		{
			$this->_apis = array_merge($this->_apis, $this->_fetchNextPage());
		}
		
		return $this->_apis[$offset];
	}

	private function willPaginate($response)
	{
		return $response->hasHeader('Link');	
	}

	private function getNumOfPages($response)
	{
		$linkHeader = $response->getHeader('Link');
		$this->_paginator = New \Paginatior($linkHeader[0]);
		return $this->_paginator->getNumOfPages();
	}

	private function _fetchNextPage()
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

echo $firstRepo->name . "\n";

echo $firstRepo->full_name . "\n";
