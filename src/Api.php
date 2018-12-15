<?php
namespace WPProgramator;

class Api {
	/**
	 * API URL
	 * @var string
	 */
	protected $apiUrl = '';

	/**
	 * Request args
	 * @var array
	 */
	protected $args = [];

	/**
	 * Request response
	 * @var
	 */
	protected $response;

	/**
	 * Request method
	 * @var
	 */
	protected $method;

	/**
	 * Request endpoint
	 * @var
	 */
	protected $endpoint;

	/**
	 * JSON encode body in the request
	 * @var bool
	 */
	protected $jsonEncodeBody = false;


	/**
	 * Api constructor.
	 */
	function __construct()
	{
	}

	/**
	 * Set the API URL
	 * @param $url
	 */
	function setApiUrl (string $url) {
		$this->apiUrl = $url;
	}

	/**
	 * Set the default args
	 * @param array $args
	 */
	function setArgs(array $args) {
		$this->args = $args;
	}

	/**
	 * Add args
	 * @param array $args
	 */
	function addArgs(array $args) {
		$args = $this->arrayMergeRecursiveDistinct($this->args, $args);
		$this->setArgs($args);
	}

	/**
	 * Set the request method
	 * @param string $method
	 */
	function setMethod (string $method) {
		$this->method = $method;
	}

	/**
	 * Set the request method
	 * @param string $endpoint
	 */
	function setEndpoint (string $endpoint) {
		$this->endpoint = $endpoint;
	}

	/**
	 * Call the API with GET Method
	 * @return array|bool|mixed|object|\WP_Error
	 */
	function _get() {
		$this->setMethod('GET');
		return $this->_call();
	}

	/**
	 * Call the API with POST Method
	 * @return array|bool|mixed|object|\WP_Error
	 */
	function _post() {
		$this->setMethod('POST');
		return $this->_call();
	}

	/**
	 * Call the API with DELETE Method
	 * @return array|bool|mixed|object|\WP_Error
	 */
	function _delete() {
		$this->setMethod('DELETE');
		return $this->_call();
	}

	/**
	 * Call the API with DELETE Method
	 * @return array|bool|mixed|object|\WP_Error
	 */
	function _put() {
		$this->setMethod('PUT');
		return $this->_call();
	}

	/**
	 * Call the API with PATCH Method
	 * @return array|bool|mixed|object|\WP_Error
	 */
	function _patch() {
		$this->setMethod('PATCH');
		return $this->_call();
	}

	/**
	 * Call the API
	 *
	 * @return array|mixed|object|\WP_Error
	 */
	function _call()
	{
		// Set the request method
		$this->args['method'] = $this->method;

		// JSON Encode the body if asked to
		if (isset($this->args['body']) && is_array($this->args['body']) && $this->jsonEncodeBody) {
			$this->args['body'] = json_encode($this->args['body']);
		}

		// Set the endpoint
		$url = $this->getRequestUrl();

		$response = false;

		// Perform the request
		$this->response = wp_remote_request($url, $this->args);

		if (!$this->response)
			return new \WP_Error(400,'Error when sending request', $response);
		// Return error or success response
		if (!$this->responseIsSuccess()) {
			return $this->prepareResponseError();
		}

		return $this->prepareResponseSuccess();
	}

	/**
	 * Get the request URL
	 * @return string
	 */
	function getRequestUrl() {
		return $this->apiUrl. $this->endpoint;
	}

	/**
	 * Check if the response was successful
	 * To be overridden by sub-classes
	 * @return bool
	 */
	function responseIsSuccess() {
		$response_code = wp_remote_retrieve_response_code($this->response);
		return  $response_code >= 200 && $response_code < 300;
	}

	/**
	 * Prepare the error response return
	 * To be overridden by sub-classes
	 * @return \WP_Error
	 */
	function prepareResponseError() {
		$errors = [];
		$body = $this->getResponseBody();
		foreach ($body->error as $error) {
			$errors[] = $error;
		}

		return new \WP_Error(wp_remote_retrieve_response_code($this->response),implode(',',$errors), $body);
	}


	/**
	 * Prepare the success response return
	 * To be overridden by sub-classes
	 * @return string
	 */
	function prepareResponseSuccess() {
		return $this->getResponseBody();
	}

	/**
	 * Get the response body
	 * @return string
	 */
	function getResponseBody() {
		return wp_remote_retrieve_body($this->response);
	}

	/**
	 * Parameters are passed by reference, though only for performance reasons. They're not
	 * altered by this function.
	 *
	 * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
	 * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array
	 */
	protected function arrayMergeRecursiveDistinct(array &$array1, array &$array2)
	{
		$merged = $array1;
		foreach ($array2 as $key => &$value) {
			if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
				$merged[$key] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
			} else {
				$merged[$key] = $value;
			}
		}
		return $merged;
	}

}
