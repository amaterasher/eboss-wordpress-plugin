<?php

/**
 *
 * Http Helper
 * Curl
 *
 */
class HttpHelper
{

		/**
		 * @var string
		 * Holds response data right after sending a request.
		 */
		public $response = null;


		/**
		 * @var integer HTTP-Status Code
		 * This value will hold HTTP-Status Code. False if request was not successful.
		 */
		public $responseCode = null;

		/**
		 * @var string HTTP Response Header
		 */
		public $header = null;


		/**
		 * @var array HTTP-Status Code
		 * Custom options holder
		 */
		private $_options = array();


		/**
		 * @var array default curl options
		 * Default curl options
		 */
		private $_defaultOptions = array(
			CURLOPT_USERAGENT      => 'Curl-Agent',
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => false,
		);

		/**
		 *
		 * Start performing GET-HTTP-Request
		 *
		 * @param           $url
		 * @param bool|true $raw if response body contains JSON and should be decoded
		 *
		 * @return mixed
		 * @throws Exception
		 */
		public function get($url, $raw = true)
		{
				return $this->_httpRequest('GET', $url, $raw);
		}

		/**
		 *
		 * Start performing HEAD-HTTP-Request
		 *
		 * @param $url
		 *
		 * @return mixed
		 * @throws Exception
		 */
		public function head($url)
		{
				return $this->_httpRequest('HEAD', $url);
		}


		/**
		 *
		 * Start performing POST-HTTP-Request
		 *
		 * @param           $url
		 * @param bool|true $raw if response body contains JSON and should be decoded
		 *
		 * @return mixed
		 * @throws Exception
		 */
		public function post($url, $raw = true)
		{
				return $this->_httpRequest('POST', $url, $raw);
		}

		/**
		 *
		 * Start performing PUT-HTTP-Request
		 *
		 * @param           $url
		 * @param bool|true $raw if response body contains JSON and should be decoded
		 *
		 * @return mixed
		 * @throws Exception
		 */
		public function put($url, $raw = true)
		{
				return $this->_httpRequest('PUT', $url, $raw);
		}

		/**
		 *
		 * Start performing PATCH-HTTP-Request
		 *
		 * @param           $url
		 * @param bool|true $raw $raw if response body contains JSON and should be decoded
		 *
		 * @return mixed
		 * @throws Exception
		 */
		public function patch($url, $raw = true)
		{
				return $this->_httpRequest('PATCH', $url, $raw);
		}


		/**
		 *
		 * Start performing DELETE-HTTP-Request
		 *
		 * @param           $url
		 * @param bool|true $raw  if response body contains JSON and should be decoded
		 *
		 * @return mixed
		 * @throws Exception
		 */
		public function delete($url, $raw = true)
		{
				return $this->_httpRequest('DELETE', $url, $raw);
		}


		/**
		 * Set curl option
		 *
		 * @param string $key
		 * @param mixed  $value
		 *
		 * @return $this
		 */
		public function setOption($key, $value)
		{
				//set value
				$this->_options[$key] = $value;

				//return self
				return $this;
		}


		/**
		 * Unset a single curl option
		 *
		 * @param string $key
		 *
		 * @return $this
		 */
		public function unsetOption($key)
		{
				//reset a single option if its set already
				if (isset($this->_options[$key])) {
						unset($this->_options[$key]);
				}

				return $this;
		}


		/**
		 * Unset all curl option, excluding default options.
		 *
		 * @return $this
		 */
		public function unsetOptions()
		{
				//reset all options
				if (isset($this->_options)) {
						$this->_options = array();
				}

				return $this;
		}


		/**
		 * Total reset of options, responses, etc.
		 *
		 * @return $this
		 */
		public function reset()
		{
				//reset all options
				if (isset($this->_options)) {
						$this->_options = array();
				}

				//reset response & status code
				$this->response = null;
				$this->responseCode = null;

				return $this;
		}


		/**
		 * Return a single option
		 *
		 * @return mixed // false if option is not set.
		 */
		public function getOption($key)
		{
				//get merged options depends on default and user options
				$mergesOptions = $this->getOptions();

				//return value or false if key is not set.
				return isset($mergesOptions[$key]) ? $mergesOptions[$key] : false;
		}


		/**
		 * Return merged curl options and keep keys!
		 *
		 * @return array
		 */
		public function getOptions()
		{
				return $this->_options + $this->_defaultOptions;
		}

		/**
		 * Performs HTTP request
		 *
		 * @param string  $method
		 * @param string  $url
		 * @param boolean $raw if response body contains JSON and should be decoded -> helper.
		 *
		 * @throws Exception if request failed
		 * @throws HttpException
		 *
		 * @return mixed
		 */
		private function _httpRequest($method, $url, $raw = false)
		{
				//Init
				$body = '';

				//set request type and writer function
				$this->setOption(CURLOPT_CUSTOMREQUEST, strtoupper($method));

				//check if method is head and set no body
				if ($method === 'HEAD') {
						$this->setOption(CURLOPT_NOBODY, true);
						$this->unsetOption(CURLOPT_WRITEFUNCTION);
				} else {
						$this->setOption(CURLOPT_WRITEFUNCTION, function ($curl, $data) use (&$body) {
								$body .= $data;
								return mb_strlen($data, '8bit');
						});
				}

				//get header
				$this->setOption(CURLOPT_HEADER, true);
				$this->setOption(CURLOPT_TIMEOUT, 0);
				$this->setOption(CURLOPT_CONNECTTIMEOUT, 0);

				/**
				 * proceed curl
				 */
				$curl = curl_init($url);
				curl_setopt_array($curl, $this->getOptions());

				$body = curl_exec($curl);

				//check if curl was successful
				if ($body === false) {
						throw new Exception('curl request failed: ' . curl_error($curl) , curl_errno($curl));
				}

				//retrieve response code
				$this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

				$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
				$this->header = substr($body, 0, $headerSize);
				$this->header = self::convertResponseHeaderToArray($this->header);
				$this->response = substr($body, $headerSize);


				//stop curl
				curl_close($curl);

				//check responseCode and return data/status
				if ($this->responseCode >= 200 && $this->responseCode < 300) { // all between 200 && 300 is successful
						if ($this->getOption(CURLOPT_CUSTOMREQUEST) === 'HEAD') {
								return true;
						} else {
								$this->response = $raw ? $this->response : json_decode($this->response, true);
								return $this->response;
						}
				} elseif ($this->responseCode >= 400 && $this->responseCode <= 500) { // client and server errors return false.
						$this->response = $raw ? $this->response : json_decode($this->response, true);
						return $this->response;

				} else {
						//any other status code or custom codes
						return false;
				}
		}

		/**
		 *
		 * Get Response Header in array
		 * @param $header
		 *
		 * @return array
		 */
		public static function convertResponseHeaderToArray($header)
		{
				$header = explode("\n",trim($header));
				unset($header[0]);

				$aHeaders = array();
				foreach ($header as $line) {
						list($key, $val) = explode(':', $line, 2);
						$aHeaders[strtolower($key)] = trim($val);
				}

				return $aHeaders;
		}

}