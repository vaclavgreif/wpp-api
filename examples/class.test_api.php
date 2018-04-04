<?php
/**
 * Test API example
 */
namespace Test_API;
use WPProgramator\Api;

class TestMethod extends Api {

	/**
	 * TestMethod constructor.
	 * See https://developer.wordpress.org/reference/classes/WP_Http/request/ for the possible args
	 */
	function __construct() {
		// Set the endpoing
		$this->setApiUrl('https://myapiurl.com');

		// Set the API args

		$this->setArgs(array(
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array( 'username' => 'bob', 'password' => '1234xyz' ),
			'cookies' => array()
		));

		parent::__construct( );
	}

	/**
	 * Example function to get some data from example API
	 * @return array|bool|mixed|object|\WP_Error
	 */
	function get_some_data() {
		$args['body'] = [
			'some_param' => 'some_value',
			'another_param' => 'another_value'
		];

		$this->addArgs($args);
		$this->setEndpoint('some-endpoint');

		return $this->_get();
	}
}

$api_method = new TestMethod();
$result = $api_method->get_some_data();

// Do something with the result
