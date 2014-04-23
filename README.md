YACurL
======

Yet Another Curl Library - an open source curl library for PHP



Public Methods:
---------------

```
void __construct([array $config]);
string get(string $url);
string post(string $url, array $param, [int $type = 0], [bool $use_http_build_query = true]);

```
Note: [squared $param] are optional



Example 1: Set cookie prefix, set follow location, enable debug
---------------------------------------------------------------

```php
$config = array(
	'cookie_prefix' => 'mypref',
	'follow_location' => 'true',
	'debug' => 'true'
);
$curl = New YACurL($config);
$curl->get('www.mysite.com');
//post encoded as application/x-www-form-urlencoded
$curl->post('www.mysite.com', array('user' => 'myuser', 'pass' => 'mypass'));
//post encoded as multipart/form-data
$response = $curl->post('www.mysite.com', array('user' => 'myuser', 'pass' => 'mypass'), 1);
echo $response;

```


Example 2: Set delay before http requests
-----------------------------------------

```php
//static delay
$config = array('delay' => 5);
$curl = New YACurL($config);

//random delay from 1 to 5 seconds
$config = array(
	'delay' => array(1,5);
);
$curl = New YACurL($config);

```


Example 3: Load it in codeigniter
---------------------------------

```php
$this->load->library('yacurl', $config);
$this->yacurl->get('www.mysite.com');

```


Info about $use_http_build_query
--------------------------------

If **$use_http_build_query** is false, form param will be encoded by internal routine instead of **http_build_query()**.

This custom routine is similar to http_build_query() but ignores "\*" char.

Example: 
- http_build_query('abc\*def g') = **abc%2Aef%20g**
- Internal routine encodes it as **abc*def%20g**.



Configuration 
-------------

- _(array)_ header
- _(string)_ encoding
- _(string)_ cookie_prefix
- _(bool)_ auto_referer
- _(bool)_ return_transfer
- _(bool)_ follow_location
- _(int)_ delay
- _(bool)_ debug


Default:
 
```php
 $_curl_config = array(
 		'header' => array(
 			'User-Agent: Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)',
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
```
