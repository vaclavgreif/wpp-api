<?php
namespace WPProgramator;

class Api {
	/**
	 * API URL
	 * @var string
	 */
	protected $api_url = '';

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
	protected $json_encode_body = false;


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
	function set_api_url (string $url) {
		$this->api_url = $url;
	}

	/**
	 * Set the default args
	 * @param array $args
	 */
	function set_args(array $args) {
		$this->args = $args;
	}

	/**
	 * Add args
	 * @param array $args
	 */
	function add_args(array $args) {
		$args = $this->array_merge_recursive_distinct($this->args, $args);
		$this->set_args($args);
	}

	/**
	 * Set the request method
	 * @param string $method
	 */
	function set_method (string $method) {
		$this->method = $method;
	}

	/**
	 * Set the request method
	 * @param string $endpoint
	 */
	function set_endpoint (string $endpoint) {
		$this->endpoint = $endpoint;
	}

	/**
	 * Call the API with GET Method
	 * @return array|bool|mixed|object|\WP_Error
	 */
	function _get() {
		$this->set_method('get');
		return $this->_call();
	}

	/**
	 * Call the API with POST Method
	 * @return array|bool|mixed|object|\WP_Error
	 */
	function _post() {
		$this->set_method('post');
		return $this->_call();
	}

	/**
	 * Call the API with DELETE Method
	 * @return array|bool|mixed|object|\WP_Error
	 */
	function _delete() {
		$this->set_method('delete');
		return $this->_call();
	}

	/**
	 * Call the API with DELETE Method
	 * @return array|bool|mixed|object|\WP_Error
	 */
	function _put() {
		$this->set_method('put');
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
		if (isset($this->args['body']) && is_array($this->args['body']) && $this->json_encode_body) {
			$args['body'] = json_encode($this->args['body']);
		}

		// Set the endpoint
		$url = $this->get_request_url();

		$response = false;

		// Perform the request
		$this->response = wp_remote_request($url, $this->args);

		if (!$this->response)
			return new \WP_Error(400,'Error when sending request', $response);

		// Return error or success response
		if (!$this->response_is_success()) {
			return $this->prepare_response_error();
		}

		return $this->prepare_response_success();
	}

	/**
	 * Get the request URL
	 * @return string
	 */
	function get_request_url() {
		return $this->api_url. $this->endpoint;
	}

	/**
	 * Check if the response was successful
	 * To be overridden by sub-classes
	 * @return bool
	 */
	function response_is_success() {
		$response_code = wp_remote_retrieve_response_code($this->response);
		return  $response_code >= 200 && $response_code < 300;
	}

	/**
	 * Prepare the error response return
	 * To be overridden by sub-classes
	 * @return \WP_Error
	 */
	function prepare_response_error() {
		$errors = [];
		$body = $this->get_response_body();
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
	function prepare_response_success() {
		return $this->get_response_body();
	}

	/**
	 * Get the response body
	 * @return string
	 */
	function get_response_body() {
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
	protected function array_merge_recursive_distinct(array &$array1, array &$array2)
	{
		$merged = $array1;
		foreach ($array2 as $key => &$value) {
			if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
				$merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
			} else {
				$merged[$key] = $value;
			}
		}
		return $merged;
	}

}
