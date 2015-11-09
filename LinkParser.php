<?php

class Page
{
	private $link;
	private $relation;

	public function __construct($link, $relation)
	{
		$link = trim($link);
		$this->link = substr($link, 1, -1);
		$this->relation = $relation;
	}

	public function isFirst()
	{
		return trim($this->relation) == 'rel="first"';
	}

	public function isLast()
	{
		return trim($this->relation) == 'rel="last"';
	}

	public function isNext()
	{
		return trim($this->relation) == 'rel="next"';
	}

	public function isPrev()
	{
		return trim($this->relation) == 'rel="prev"';
	}
	
	public function getLink()
	{
		return $this->link;
	}


}

class Paginatior
{
	private $prev;
	private $next;
	private $first;
	private $last;

	public function __construct($header)
	{
		$links = explode(',', $header);

		foreach($links as $link)
		{
			list($url, $rel) = explode(';', $link);	
			$page = new Page($url, $rel);

			if($page->isFirst())
			{
				$this->first = $page;
			}
			else if($page->isNext())
			{
				$this->next = $page;
			}
			else if($page->isPrev())
			{
				$this->prev = $page;
			}
			else if($page->isLast())
			{
				$this->last = $page;
			}

		}
	}

	public function getNextLink()
	{
		return $this->next->getLink();
	}

	public function getNumOfPages()
	{	
		$query_string = parse_url($this->last->getLink(), PHP_URL_QUERY);	
		$query_params = explode('&', $query_string);
		foreach($query_params as $param)
		{
			$param = explode('=', $param);
			if($param[0] == 'page')
				return $param[1];
		}
		return 0;
	}
}
