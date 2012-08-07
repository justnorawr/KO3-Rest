<?php defined('SYSPATH') or die('No direct script access.');

class RestTest extends Kohana_Unittest_TestCase
{
	public function __construct ()
	{
		parent::__construct();

		$this->_config = Kohana::$config->load('rest');
	}

	protected function _getSignatureData()
	{
		return array
		(
			'testSignatureVerification', // route
			array// data array
			(
				'timestamp'	=>	time(),
				'public_key'	=>	'',
				'signature'	=>	'',
				'key1'		=>	'value1',
				'ke`y2'		=>	'value2'
			),
			Rest::GET, // method
			''// private key
		);
	}

	public function testSignature ()
	{
		$expected_signature = 'asdfasdfasdfasdfasdfasdfasdf';

		list($route, $data, $method, $private_key) = $this->_getSignatureData();

		$signature = Rest_Signature::factory($private_key)
					->signature($route, $data, $method);

		$this->assertEquals($signature, $expected_signature);
	}

	public function testSignaturePrivateKey ()
	{
		$expected_signature = '';

		list($route, $data, $method, $private_key) = $this->_getSignatureData();

		// break the private key
		$private_key = 'FD16VS23A' . substr($private_key, 0, ( strlen($private_key) / 2) );

		$signature = Rest_Signature::factory($private_key)
					->signature($route, $data, $method);

		$this->assertEquals($signature, $expected_signature);
	}

	public function testSignatureVerification ()
	{
		list($route, $data, $method, $private_key) = $this->_getSignatureData();

		$result = Rest_Signature::factory($private_key)
					->verify($route, $data, $method);

		$this->assertEquals(TRUE, $result);
	}

	public function testSignatureVerificationTimestamp ()
	{
		list($route, $data, $method, $private_key) = $this->_getSignatureData();

		// break timestamp by adding 2 times the config time limit
		$data['timestamp'] = time() + ($this->_config['signature']['replaytimeout'] * 2);

		$result = Rest_Signature::factory($private_key)
					->verify($route, $data, $method);

		$this->assertEquals(FALSE, $result);
	}
}
