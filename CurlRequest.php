<?php

namespace CurlRequest;

use Exception;

class CurlRequest
{
	private $url = '';
	/**
	 * @var string where save cookie
	 */
	private $filenameCookie = '';
	/**
	 * @var string post | get
	 */
	private $method;
	/**
	 * @var array
	 */
	private $postFields;
	/**
	 * @var string
	 */
	private $proxy;
	/**
	 * @var string
	 */
	private $headerLines;
	/**
	 * @var string
	 */
	private $cookie;
	/**
	 * @var bool
	 */
	private $ssl = false;
	/**
	 * @var string
	 */
	private $userAgent;
	/**
	 * @var array
	 */
	private $headers;
	/**
	 * @var int
	 */
	private $timeOut = 20;

	private $options = [];

	public function __construct()
	{
	}

	public function setUrl(string $url)
	{
		$this->options[CURLOPT_URL] = $url;
		$this->url                  = $url;
	}

	public function setReturnTransfer(bool $bool = true)
	{
		$this->options[CURLOPT_RETURNTRANSFER] = $bool;
	}

	public function setEncoding(string $encoding = '')
	{
		$this->options[CURLOPT_ENCODING] = $encoding;
	}

	protected function setHeaderFunction()
	{
		$this->options[CURLOPT_HEADERFUNCTION] = array(&$this, 'handleHeaderLine');
	}

	public function setPostFields(array $postFields)
	{
		$this->postFields = $postFields;
	}

	public function setProxy($proxy)
	{
		$this->proxy = $proxy;
	}

	public function setSSLVerifypeer(bool $bool = false)
	{
		$this->ssl                             = $bool;
		$this->options[CURLOPT_SSL_VERIFYPEER] = $bool;
	}

	/**
	 * @return array|bool
	 */
	private function compileHeader()
	{
		$this->headers;

		if ($this->cookie) {
			$this->headers['cookie'] = 'cookie: ' . $this->cookie;
		}

		if ($this->userAgent) {
			$this->headers['user-agent'] = 'user-agent: ' . $this->userAgent;
		}

//		var_dump($this->headers);

		return ($this->headers) ? $this->headers : false;
	}

	/**
	 * @param array $headers - key name param
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = array_merge($this->headers, array_change_key_case($headers));

		if ($this->headers) {
			$this->options[CURLOPT_HTTPHEADER] = $this->headers;
		}

	}

	/**
	 * @param string $userAgent
	 */
	public function setUserAgent(string $userAgent = null)
	{
		if ($userAgent) {
			$this->userAgent = $userAgent;
		}

		$this->headers = array_merge($this->headers, array_change_key_case($headers));

		$options[CURLOPT_USERAGENT] = $userAgent;

	}

	public function setFilenameCookies($filenameCookie)
	{
		if ($filenameCookie && is_file($filenameCookie)) {
			$this->filenameCookie = $filenameCookie;
		} elseif ($filenameCookie) {
			var_dump('$filenameCookie is not file!');
		}
	}

	public function setMethod(string $method = 'get')
	{
		$this->method = $method;
	}

	public function setTimeOut(int $seconds = 20)
	{
		$this->timeOut                  = $seconds;
		$this->options[CURLOPT_TIMEOUT] = $seconds;

	}


	public function clearFileCooke()
	{
		if ($this->filenameCookie) {
			file_put_contents($this->filenameCookie, '');
		}
	}

	public function pull()
	{
		$this->setCookie();

		$this->headerLines = '';

		$data = $this->curl();

		$this->saveCookie();

		return $data;
	}

	public function getOnlyCookies($url)
	{
		// адрес для  удаленного подключения
		$ch = curl_init($url);
		// результат вернуть в переменную, а не на экран
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// вернуть заголовки
		curl_setopt($ch, CURLOPT_HEADER, 1);
		// выполнить запрос
		$result = curl_exec($ch);

		// получаем cookies, исходя из блока Set-Cookie заголовков
		return $this->parseCookie($result);
	}

	/**
	 * Compile option for curl
	 */
	private function compileOption()
	{
		$options = [];

		$options[CURLOPT_URL]            = $this->url;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_ENCODING]       = '';
		$options[CURLOPT_HEADERFUNCTION] = array(&$this, 'handleHeaderLine');

		if ($this->timeOut) {
			$options[CURLOPT_TIMEOUT] = $this->timeOut;
		}

		if ($this->ssl) {
			$options[CURLOPT_SSL_VERIFYPEER] = true;
		}

		if ($header = $this->compileHeader()) {
			$options[CURLOPT_HTTPHEADER] = $header;
		}

		if ($this->proxy) {
			$options[CURLOPT_PROXY] = $this->proxy;
		}

		if ($this->userAgent) {
			$options[CURLOPT_USERAGENT] = $this->userAgent;
		}

		if ($this->method == 'post') {
			$options[CURLOPT_POST] = true;
		}

		if ($this->method == 'post' && $this->postFields) {
//			$options[CURLOPT_POSTFIELDS] = http_build_query($this->postFields);
			$options[CURLOPT_POSTFIELDS] = $this->postFields;
		}

		return $options;
	}


	private function curl()
	{
		$content = '';
		$options = $this->compileOption();
		try {
			$ch = curl_init();

			// Check if initialization had gone wrong*
			if ($ch === false) {
				throw new Exception('failed to initialize');
			}

//			curl_setopt($ch, CURLOPT_URL, 'http://example.com/');
//			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt_array($ch, $options);

			$content = curl_exec($ch);

			// Check the return value of curl_exec(), too
			if ($content === false) {
				throw new Exception(curl_error($ch), curl_errno($ch));
			}

			/* Process $content here */

			// Close curl handle
			curl_close($ch);
		} catch (Exception $e) {

//			trigger_error(sprintf(
//				'Curl failed with error #%d: %s',
//				$e->getCode(), $e->getMessage()),
//				E_USER_ERROR);

			print_r(sprintf(
				'Curl failed with error #%d: %s',
				$e->getCode(), $e->getMessage()));
		}


//		$curl = curl_init();
//		curl_setopt_array($curl, $options);
//		$data = curl_exec($curl);
//		curl_close($curl);
//		var_dump($content);

		return $content;
	}


	private function setCookie()
	{
		$cookiesArray = $this->loadCookieFromFile();

		if ($cookiesArray) {

			// add row cookie to header
			$rows = [];
			foreach ($cookiesArray as $index => $item) {
				$rows[] = $index . '=' . $item;
			}

			$this->cookie = implode('; ', $rows);

		}
	}

	private function loadCookieFromFile()
	{
		if ($this->filenameCookie) {
			$json = file_get_contents($this->filenameCookie);
			$data = json_decode($json, true);
		} else {
			$data = [];
		}

		return $data;
	}

	private function saveCookie()
	{
		if ($this->filenameCookie) {
			$cookies = $this->parseCookie($this->headerLines);
//			var_dump(array('parseCooke' => $cookies));
			$this->updateCookieFile($cookies);
		}
	}

	private function parseCookie($data)
	{
		// получаем cookies, исходя из блока Set-Cookie заголовков
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $data, $matches);
		$cookies = array();
		foreach ($matches[1] as $item) {
			parse_str($item, $cookie);
			$cookies = array_merge($cookies, $cookie);
		}

		return $cookies;
	}

	private function updateCookieFile($cookiesArray)
	{
		$json = file_get_contents($this->filenameCookie);

		$cookiesArrayOld = json_decode($json, true);


		foreach ($cookiesArray as $index => $item) {
			$cookiesArrayOld[$index] = $item;
		}

		file_put_contents($this->filenameCookie, json_encode($cookiesArrayOld));
	}

	public function getUserAgent()
	{
		return $this->userAgent;
	}


	private function handleHeaderLine($curl, $header_line)
	{
		$this->headerLines .= $header_line;

		return strlen($header_line);
	}
}
