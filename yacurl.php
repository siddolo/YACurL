<?php
/**
 * YACurL 0.1a
 * Yet Another Curl Library - an open source curl library for PHP
 *
 * @author 	Pasquale 'sid' Fiorillo
 * @copyright	Copyright (c) 2014, Pasquale Fiorillo.
 * @license	GPLv3
 * @link 	http://github.com/siddolo/yacurl
 * @version	0.1a
 *
 *
 * ---------------------------------------------------------------
 * Public Methods:
 * ---------------------------------------------------------------
 * void __construct([array $config]);
 * string get(string $url);
 * string post(string $url, array $param, [int $type = 0], [bool $use_http_build_query = true]);
 *
 * Note: [squared $param] are optional
 * 
 * ---------------------------------------------------------------
 * Example 1: Set cookie prefix, set follow location, enable debug
 * ---------------------------------------------------------------
 * $config = array(
 *	'cookie_prefix' => 'mypref',
 *	'follow_location' => 'true',
 *	'debug' => 'true'
 * );
 * $curl = New YACurL($config);
 * $curl->get('www.mysite.com');
 * //post encoded as application/x-www-form-urlencoded
 * $curl->post('www.mysite.com', array('user' => 'myuser', 'pass' => 'mypass'));
 * //post encoded as multipart/form-data
 * $response = $curl->post('www.mysite.com', array('user' => 'myuser', 'pass' => 'mypass'), 1);
 * echo $response;
 *
 * ---------------------------------------------------------------
 * Example 2: Set delay before http request
 * ---------------------------------------------------------------
 * //static delay
 * $config = array('delay' => 5);
 * $curl = New YACurL($config);
 * 
 * //random delay from 1 to 5 seconds
 * $config = array(
 * 	'delay' => array(1,5);
 * );
 * $curl = New YACurL($config);
 *
 * ---------------------------------------------------------------
 * Example 3: Load it in codeigniter
 * ---------------------------------------------------------------
 * $this->load->library('yacurl', $config);
 * $this->yacurl->get('www.mysite.com');
 *
 * ---------------------------------------------------------------
 * Info about $use_http_build_query = false in post() method
 * ---------------------------------------------------------------
 * If $use_http_build_query is false, form param will be encoded by internal
 * routine instead of http_build_query(). 
 * This custom routine is similar to http_build_query() but ignores "*" char.
 * Example: http_build_query('abc*def g') = 'abc%2Aef%20g
 * custom routine encodes it as 'abc*def%20'.
 * 
 * ---------------------------------------------------------------
 * Configuration
 * ---------------------------------------------------------------
 * array header
 * string encoding
 * string cookie_prefix
 * bool auto_referer
 * bool return_transfer
 * bool follow_location
 * int delay
 * bool debug
 *
 */

class YACurL {

	private $_ch;
	private $_curl_config = array(
		'header' => array(
			'User-Agent: Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)', // IE 9
			'Accept-Language: it-IT',
			'Accept-Encoding: gzip,deflate,sdch' 
		),
		'encoding' => 'gzip',
		'cookie_prefix' => '',
		'auto_referer' => true,
		'return_transfer' => true,
		'follow_location' => false,
		'delay' => 0,
		'debug' => true
		);


	public function __construct($config = false)
	{

		if (is_array($config))
			$this->configure($config);
		if ($this->_curl_config['debug'])
		{
			error_reporting(-1); 
	        ini_set('display_errors', TRUE);
	        set_time_limit(0);
	        ini_set('memory_limit', '-1');
		}


		$this->_ch = curl_init();
		curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $this->_curl_config['header']);
		curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, $this->_curl_config['follow_location']);
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, $this->_curl_config['return_transfer']);
		curl_setopt($this->_ch, CURLOPT_COOKIEJAR, tempnam(sys_get_temp_dir(), $this->_curl_config['cookie_prefix']));
		curl_setopt($this->_ch, CURLOPT_COOKIEFILE, tempnam(sys_get_temp_dir(), $this->_curl_config['cookie_prefix']));
		curl_setopt($this->_ch, CURLOPT_AUTOREFERER, $this->_curl_config['auto_referer']);
		curl_setopt($this->_ch, CURLOPT_ENCODING , $this->_curl_config['encoding']);
		if ($this->_curl_config['debug'])
		{
			curl_setopt($this->_ch, CURLOPT_HEADER, true); 	// Display headers
			curl_setopt($this->_ch, CURLOPT_VERBOSE, true); // Display communication with server
			curl_setopt($this->_ch, CURLINFO_HEADER_OUT, true);
		}
	}

	public function get($url)
	{
		$this->delay();
		curl_setopt($this->_ch, CURLOPT_URL, $url); 
		curl_setopt($this->_ch, CURLOPT_POST, false);
		$buffer = curl_exec($this->_ch);
		if ($buffer === false)
		{
			$this->debug('Curl: '. curl_error($this->_ch));
			return false;
		} else
			return $buffer;
	}
	
	public function post($url, $param, $type = 0, $use_http_build_query = true) 
	{
		$this->delay();

		curl_setopt($this->_ch, CURLOPT_URL, $url);
		curl_setopt($this->_ch, CURLOPT_POST, true);

		if (!$use_http_build_query)
		{
			$data_str = "";
			foreach ($param as $k => $v)
			{
				$data_str = $data_str . $k . "=" . rawurlencode($v) . "&";
			}
			rtrim($data_str, '&');
			$param = str_replace("%2A", "*", $data_str);
		}

		if ($type || !$use_http_build_query)
			curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $param);
		else
			curl_setopt($this->_ch, CURLOPT_POSTFIELDS, http_build_query($param));
		$buffer = curl_exec($this->_ch);
		if ($buffer === false)
		{
			$this->debug('Curl: '. curl_error($this->_ch));
			return false;
		} else
			return $buffer;
	}

	private function delay()
	{
		if ($this->_curl_config['delay'])
			if (!is_array($this->_curl_config['delay']))
				sleep($this->_curl_config['delay']);
			else
				sleep(rand($this->_curl_config['delay'][0], $this->_curl_config['delay'][1]));
	}

	private function debug($msg)
	{
		if (!$this->_curl_config['debug'])
			return false;

		openlog(get_class($this), LOG_PID | LOG_PERROR, LOG_USER);
		syslog(LOG_DEBUG, $msg);
		closelog();
	}


	private function configure($config = false)
	{
		if (!is_array($config))
			return false;

		foreach ($config as $k => $v) {
			if (array_key_exists($k, $this->_curl_config))
				$this->_curl_config[$k] = $v;
			else
				$this->debug($k . ': invalid configuration option');
		}

	}

}
?>
