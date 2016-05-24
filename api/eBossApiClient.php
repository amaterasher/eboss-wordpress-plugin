<?php
if (!isset($_SESSION)) {
		session_start();
}

require_once 'config.php';
require_once 'HttpHelper.php';
require_once 'ResumeParser.php';

/**
 *
 * eBoss V3 API Client
 *
 * Implementation of eBoss API version 3
 * @see http://demo.api.recruits-online.com/v3/docs/
 * @author winaum@me.com
 * @support contact@ebossrecruitment.com
 *
 * @todo refactor all methods, especially on CURL
 *
 */
class eBossApiClient
{
		//API base url
		const DEFAULT_API_BASE_URL  = 'http://demo.api.recruits-online.com/v3/';
		const DEFAULT_GRANT_TYPE    =  'password';
		const DEFAULT_PAGE_SIZE     = 20;

		//endpoints
		const OAUTH_TOKEN_ENDPOINT = 'oauth2/token';
		const JOB_TITLES_ENDPOINT  = 'job-titles';
		const JOB_TYPES_ENDPOINT   = 'job-types';
		const INDUSTRIES_ENDPOINT  = 'industries';
		const COUNTRIES_ENDPOINT   = 'countries';
		const REGIONS_ENDPOINT     = 'regions';
		const JOBS_ENDPOINT        = 'jobs';
		const CANDIDATES_ENDPOINT  = 'candidates';
		const PARSERS_ENDPOINT     = 'parsers';
		const FILES_ENDPOINT       = 'files';
		const XSTATUS_ENDPOINT     = 'xstatuses';
		const NEWS_ENDPOINT        = 'news';
		const CLIENTS_ENDPOINT     = 'companies'; //clients or companies
		const USERS_ENDPOINT       = 'users';
		const CONTACTS_ENDPOINT    = 'client-contacts';

		private $baseUrl;
		private $username;
		private $password;
		private $apiKey;
		private $apiSecret;
		private $accessToken = null;
		public $errors = array();

		public function __construct()
		{
				$this->baseUrl = defined('E_BOSS_API_URL') ? E_BOSS_API_URL : self::DEFAULT_API_BASE_URL;

				$this->username = defined('E_BOSS_USERNAME') ? E_BOSS_USERNAME : '';
				$this->password = defined('E_BOSS_PASSWORD') ? E_BOSS_PASSWORD : '';

				$this->apiKey       = defined('API_KEY') ? API_KEY : '';
				$this->apiSecret    = defined('API_SECRET') ? API_SECRET : '';

				//create access token on init
				//@todo remove session inside the class
				if (isset($_SESSION['access_token']) && !$this->accessToken) {
						$this->accessToken = $_SESSION['access_token'];
				} else {
						$_SESSION['access_token'] = $this->generateAccessToken();
				}
		}

		/**
		 * Set Access Token
		 *
		 * @param null $token
		 */
		public function setAccessToken($token = null)
		{
				if ($token) {
						$this->accessToken = $token;

				}
		}

		/**
		 * @param array $params
		 *
		 * @return array
		 */
		public static function escapeParameters($params = array())
		{
				if (!is_array($params)) return $params;

				foreach ($params as $key => $value) {
						if (!is_array($value)) {
								$params[$key] = addslashes(strip_tags(trim($value)));
						}
				}

				return $params;
		}

		/**
		 * @param null $endpoint
		 *
		 * @return string
		 */
		public function prepareRequestUrl($endpoint = null)
		{
				if (!$endpoint) return $this->baseUrl;

				return $this->baseUrl . $endpoint;
		}

		/**
		 * Generate access token
		 * @return null
		 */
		protected function generateAccessToken()
		{
				//$params = [];
				$params = array (
					'grant_type' => self::DEFAULT_GRANT_TYPE,
					'username' => $this->username,
					'password' => $this->password,
					'client_id' => $this->apiKey,
					'client_secret' => $this->apiSecret
				);

				$endPointUri = $this->prepareRequestUrl(self::OAUTH_TOKEN_ENDPOINT);

				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_POSTFIELDS, json_encode($params));
				$curl->setOption(CURLOPT_HTTPHEADER,[
					"Content-Type: application/json",
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);

				$response = $curl->post($endPointUri, false);
				if (($curl->responseCode >= 400 && $curl->responseCode <= 510)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}

						return null;
				}

				if (!isset($response['access_token'])) {
						$this->errors[] = "Unable to create access token.";
						return null;
				}

				$this->accessToken = $response['access_token'];

				return $this->accessToken;
		}

		/**
		 *
		 * Create Job Application
		 * @param null $params
		 *
		 * @return bool|mixed|null
		 */
		public function createJobApplication($params = null)
		{
				if (empty($params)) return false;

				$endPointUri = $this->prepareRequestUrl(self::XSTATUS_ENDPOINT);

				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_POSTFIELDS, json_encode($params));
				$curl->setOption(CURLOPT_HTTPHEADER, [
					"Content-Type: application/json",
					"Authorization: Bearer " . $this->accessToken
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);

				$response = $curl->post($endPointUri, false);
				if (($curl->responseCode >= 400 && $curl->responseCode <= 510)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return null;
				}

				return $response;

		}

		/**
		 * Add New Client / Company
		 * @param array $params
		 *
		 * @return bool|mixed|null
		 */
		public function addClient($params = array())
		{
				if (empty($params)) return false;

				$params = self::escapeParameters($params);

				$endPointUri = $this->prepareRequestUrl(self::CLIENTS_ENDPOINT);
				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_POSTFIELDS, json_encode($params));
				$curl->setOption(CURLOPT_HTTPHEADER,[
					"Authorization: Bearer " . $this->accessToken,
					"Content-Type: application/json"
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);
				$response = $curl->post($endPointUri, false);
				if (($curl->responseCode >= 400 && $curl->responseCode < 500)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return null;
				} elseif ($curl->responseCode >= 500) {
						$this->errors = array(array("message" =>  "Unable to save new client. Please try again later."));
						return null;
				}

				return $response;
		}

		/**
		 *
		 * Add new client contacts
		 *
		 * @param array $params
		 *
		 * @return bool|mixed|null
		 */
		public  function addClientContact($params = array())
		{
				if (empty($params)) return false;

				$params = self::escapeParameters($params);

				$endPointUri = $this->prepareRequestUrl(self::CONTACTS_ENDPOINT);
				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_POSTFIELDS, json_encode($params));
				$curl->setOption(CURLOPT_HTTPHEADER,[
					"Authorization: Bearer " . $this->accessToken,
					"Content-Type: application/json"
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);
				$response = $curl->post($endPointUri, false);
				if (($curl->responseCode >= 400 && $curl->responseCode < 500)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return null;
				} elseif ($curl->responseCode >= 500) {
						$this->errors = array(array("message" =>  "Unable to save new client. Please try again later."));
						return null;
				}

				return $response;

		}

		/**
		 *  Create New Candidate
		 * @param array $params
		 *
		 * @return bool|mixed|null
		 */
		public function addCandidate($params = array())
		{
				if (empty($params)) return false;

				$params = self::escapeParameters($params);

				$endPointUri = $this->prepareRequestUrl(self::CANDIDATES_ENDPOINT);
				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_POSTFIELDS, json_encode($params));
				$curl->setOption(CURLOPT_HTTPHEADER,[
						"Authorization: Bearer " . $this->accessToken,
						"Content-Type: application/json"
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);
				$response = $curl->post($endPointUri, false);
				if (($curl->responseCode >= 400 && $curl->responseCode < 500)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return null;
				} elseif ($curl->responseCode >= 500) {
						$this->errors = array(array("message" =>  "Unable to save new candidate. Please try again later."));
						return null;
				}

				return $response;
		}

		/**
		 *
		 * @todo refactor, simplified and put other logic in HttpHelper
		 *
		 * @param mixed $file
		 * @param int  $candidateId
		 * @param String $fileName
		 *
		 * @return bool|mixed|null
		 */
		public function uploadFile($file = null, $candidateId = 0, $fileName = "")
		{
				if (!$file || !$candidateId) return false;
				$filePath = $file;
				$fileSize = 0;

				if (is_array($file)) {
						$filePath = $file["tmp_name"];
						$fileSize = $file["size"];
						$fileName = $file["name"];
				}

				$endPointUri = $this->prepareRequestUrl(self::FILES_ENDPOINT);
				$ext = pathinfo($filePath, PATHINFO_EXTENSION);

				$data =  "@" . $filePath;
				if (class_exists('CURLFile')) {
						$data = new CURLFile($filePath,$ext,$fileName);
				}

				$headers = array(
					"Content-Type:multipart/form-data",
					"Authorization: Bearer " . $this->accessToken
				); // cURL headers for file uploading


				$params = array(
					"data" => $data,
					"filename" => $filePath,
					'candidate_id' => $candidateId
				);


				$ch      = curl_init();
				$options = array(
					CURLOPT_URL            => $endPointUri,
					CURLOPT_HEADER         => true,
					CURLOPT_POST           => 1,
					CURLOPT_HTTPHEADER     => $headers,
					CURLOPT_POSTFIELDS     => $params,
					CURLOPT_RETURNTRANSFER => true
				);

				if ($fileSize) {
						$options[CURLOPT_INFILESIZE] = $fileSize;
				}

				curl_setopt_array($ch, $options);
				$response = curl_exec($ch);
				if (curl_errno($ch)) {
						$this->errors = array("message" => curl_error($ch));
						return null;
				}

				curl_close($ch);

				return json_decode($response, true);;
		}

		/**
		 *
		 * Parse Document
		 *
		 * @param null   $file
		 * @param string $filename
		 *
		 * @return array|null
		 */
		public function parseCvDocument($file = null, $filename = '')
		{
				if (empty($file)) return array();
				$parsedData = array();

				$endPointUri = $this->prepareRequestUrl(self::PARSERS_ENDPOINT);
				$ext = pathinfo($file["tmp_name"], PATHINFO_EXTENSION);

				$data =  "@" . $file["tmp_name"];
				if (class_exists('CURLFile')) {
						$data = new CURLFile($file["tmp_name"],$ext,$file["name"]);
				}

				$headers = array(
					"Content-Type:multipart/form-data",
					"Authorization: Bearer " . $this->accessToken
				); // cURL headers for file uploading


				$params = array(
					"data" => $data,
					"filename" => ($filename) ? $filename : $file["tmp_name"],
				);

				$ch      = curl_init();
				$options = array(
					CURLOPT_URL            => $endPointUri,
					CURLOPT_POST           => 1,
					CURLOPT_HTTPHEADER     => $headers,
					CURLOPT_POSTFIELDS     => $params,
					CURLOPT_INFILESIZE     => $file["size"],
					CURLOPT_RETURNTRANSFER => true,
				); // cURL options

				curl_setopt_array($ch, $options);
				$response = curl_exec($ch);

				if (curl_errno($ch)) {
						$this->errors = array("message" => curl_error($ch));
						return null;
				}
				curl_close($ch);

				$parsedData = ResumeParser::convertXmlToArray($response);

				return $parsedData;
		}

		/**
		 * @return string
		 */
		public function getAccessToken()
		{
				return $this->accessToken;
		}

		/**
		 *
		 * Getter Methods
		 * List
		 * =======================================================
		 */

		/**
		 * @return mixed|null
		 */
		public function getJobTitles()
		{
				$endPointUri = $this->prepareRequestUrl(self::JOB_TITLES_ENDPOINT) . '?sort=+type&page_size=500';

				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_HTTPHEADER,[
					"Authorization: Bearer " . $this->accessToken
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);
				$response = $curl->get($endPointUri, false);

				if (($curl->responseCode >= 400 && $curl->responseCode <= 510)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return null;
				}

				return $response;
		}

		/**
		 * Get Industries
		 * @return mixed|null
		 */
		public function getIndustries()
		{
				$endPointUri = $this->prepareRequestUrl(self::INDUSTRIES_ENDPOINT) . '?sort=+area&page_size=500';

				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_HTTPHEADER,[
					"Authorization: Bearer " . $this->accessToken
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);
				$response = $curl->get($endPointUri, false);

				if (($curl->responseCode >= 400 && $curl->responseCode <= 510)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return null;
				}

				return $response;
		}

		/**
		 * Get Countries
		 * @return mixed|null
		 */
		public function getCountries()
		{
				$endPointUri = $this->prepareRequestUrl(self::COUNTRIES_ENDPOINT) . '?page_size=500';

				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_HTTPHEADER,[
					"Authorization: Bearer " . $this->accessToken
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);
				$response = $curl->get($endPointUri, false);

				if (($curl->responseCode >= 400 && $curl->responseCode <= 510)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return null;
				}

				return $response;
		}

		/**
		 *
		 * Get Regions
		 *
		 * @param array $params
		 * @param boolean $search
		 * @return mixed|null
		 */
		public function getRegions($params = array(), $search = false)
		{
				if (!isset($params['page_size'])) {
						$params['page_size'] = self::DEFAULT_PAGE_SIZE;
				}

				$queryString = http_build_query($params);

				$endPointUri = $this->prepareRequestUrl(self::REGIONS_ENDPOINT);
				if ($search) {
						$endPointUri .= "/search";
				}

				if ($queryString) {
						$endPointUri .= '?' . $queryString;
				}


				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_HTTPHEADER,[
					"Authorization: Bearer " . $this->accessToken
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);
				$response = $curl->get($endPointUri, false);

				if (($curl->responseCode >= 400 && $curl->responseCode <= 510)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return null;
				}

				return $response;
		}

		/**
		 * Get Job Types
		 * @param mixed $params
		 * @return mixed|null
		 */
		public function getJobTypes($params = array())
		{
				if (!isset($params['page_size'])) {
						$params['page_size'] = self::DEFAULT_PAGE_SIZE;
				}

				$queryString = http_build_query($params);
				$endPointUri = $this->prepareRequestUrl(self::JOB_TYPES_ENDPOINT);

				if ($queryString) {
						$endPointUri .= '?' . $queryString;
				}

				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_HTTPHEADER,[
					"Authorization: Bearer " . $this->accessToken
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);
				$response = $curl->get($endPointUri, false);

				if (($curl->responseCode >= 400 && $curl->responseCode <= 510)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return null;
				}

				return $response;
		}

		/**
		 * Get News
		 * @param mixed $params
		 * @return mixed|null
		 */
		public function getNews($params = array())
		{
				if (!isset($params['page_size'])) {
						$params['page_size'] = self::DEFAULT_PAGE_SIZE;
				}

				$queryString = http_build_query($params);
				$endPointUri = $this->prepareRequestUrl(self::JOB_TYPES_ENDPOINT);

				if ($queryString) {
						$endPointUri .= '?' . $queryString;
				}

				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_HTTPHEADER,[
					"Authorization: Bearer " . $this->accessToken
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);
				$response = $curl->get($endPointUri, false);

				if (($curl->responseCode >= 400 && $curl->responseCode <= 510)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return null;
				}

				return $response;

		}

		/**
		 *
		 * Candidate Login
		 *
		 * @param array      $params
		 * @param bool|false $search
		 *
		 * @return mixed|null
		 */
		public function candidateLogin($params = array(), $search = false)
		{
				$params = array_merge($params, array('gid' => 3)); //candidate only

				$queryString = http_build_query($params);

				$endPointUri = $this->prepareRequestUrl(self::USERS_ENDPOINT);
				if ($search) {
						$endPointUri .= '/search';
				}

				if ($queryString) {
						$endPointUri .= '?' . $queryString;
				}

				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_HTTPHEADER,[
					"Authorization: Bearer " . $this->accessToken
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);
				$response = $curl->get($endPointUri, false);

				if (($curl->responseCode >= 400 && $curl->responseCode <= 510)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return null;
				}

				return $response;

		}

		public function getCandidates($params = array(), $search = false)
		{
				$candidateId = 0;
				if (isset($params['id'])) {
						$candidateId = $params['id'];
						unset($params['id']);
				}

				$queryString = http_build_query($params);

				$endPointUri = $this->prepareRequestUrl(self::CANDIDATES_ENDPOINT);
				if ($search && !$candidateId) {
						$endPointUri .= '/search';
				} elseif ($candidateId) {
						$endPointUri .= '/' . $candidateId; //get single candidate by id
				}

				if ($queryString) {
						$endPointUri .= '?' . $queryString;
				}

				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_HTTPHEADER,[
					"Authorization: Bearer " . $this->accessToken
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);
				$response = $curl->get($endPointUri, false);

				if (($curl->responseCode >= 400 && $curl->responseCode <= 510)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return null;
				}

				return $response;

		}

		/**
		 *
		 * Get Jobs
		 *
		 * @param array      $params
		 * @param bool|false $search
		 *
		 * @return mixed|null
		 */
		public function getJobs($params = array(), $search = false)
		{

				if (!isset($params['page_size'])) {
						$params['page_size'] = self::DEFAULT_PAGE_SIZE;
				}

				if (isset($params['keywords'])) {
						$params['title'] =  $params['keywords'];
						$params['detail'] = $params['keywords'];
				}

				$requestParams = $params;

				//expand results
				$expands = array('job-title','region','country','company');
				$params['expand'] = implode(",",$expands);

				$queryString = http_build_query($params);

				$endPointUri = $this->prepareRequestUrl(self::JOBS_ENDPOINT);

				if ($search && !empty($requestParams)) {
						$endPointUri = $this->prepareRequestUrl(self::JOBS_ENDPOINT . '/search');
				}

				if ($queryString) {
						$endPointUri .= '?' . $queryString;
				}

				//is id exist?
				if (isset($requestParams['id'])) {
						unset($params['id']);

						$queryString = http_build_query($params);
						$endPointUri = $this->prepareRequestUrl(self::JOBS_ENDPOINT) . '/' . $requestParams['id'] . '?' . $queryString;
				}

				$curl = new HttpHelper();
				$curl->setOption(CURLOPT_HTTPHEADER,[
					"Authorization: Bearer " . $this->accessToken,
				]);

				$curl->setOption(CURLOPT_TIMEOUT, 0);
				$curl->setOption(CURLOPT_CONNECTTIMEOUT, 0);

				$response = $curl->get($endPointUri,false);

				if (($curl->responseCode >= 400 && $curl->responseCode <= 510)) {
						if (is_array($curl->response)) {
								$this->errors = $curl->response;
						} else {
								$this->errors = json_decode($curl->response,true);
						}
						return array();
				}


				return $response;

		}





		/**
		 *
		 * STATIC FUNCTIONS
		 * ===================================================================================
		 */

		/**
		 * Slug Text
		 *
		 * @param $text
		 *
		 * @return mixed|string
		 */
		public static function sluggify($text = "")
		{
				$text = preg_replace('~[^\\pL\d]+~u', '-', $text); // replace non letter or digits by -
				$text = trim($text, '-'); // trim
				$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);   // transliterate
				$text = strtolower($text);  // lowercase
				$text = preg_replace('~[^-\w]+~', '', $text);  // remove unwanted characters
				if (empty($text)) {
						return 'n-a';
				}
				return $text;

		}
}