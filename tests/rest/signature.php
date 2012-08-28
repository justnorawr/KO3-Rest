<?php defined('SYSPATH') or die('No direct script access.');

class Rest_SignatureTest extends Kohana_Unittest_TestCase
{
	protected $signature = '$2a$10$THISISMYPRIVATEKEY$$$.oAd/1k9UJsGHBu5fMkWdj2jvhUbc.b.';

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
				'timestamp'	=>	1344335276,
				'public_key'	=>	'THISISMYPUBLICKEY',
				'signature'	=>	$this->signature,
				'key1'		=>	'value1',
				'key2'		=>	'value2'
			),
			Rest::GET, // method
			'THISISMYPRIVATEKEY'// private key
		);
	}

	public function testSignature ()
	{
		list($route, $data, $method, $private_key) = $this->_getSignatureData();

		$signature = Rest_Signature::factory($private_key)
					->signature($route, $data, $method);
		
		$this->assertEquals($this->signature, $signature);
	}

	public function testSignatureBadPrivateKey ()
	{
		list($route, $data, $method, $private_key) = $this->_getSignatureData();

		// break the private key
		$private_key = 'FD16VS23A' . substr($private_key, 0, ( strlen($private_key) / 2) );

		$signature = Rest_Signature::factory($private_key)
					->signature($route, $data, $method);

		$this->assertNotEquals($this->signature, $signature);
	}

	public function testVerifyBadTimestamp ()
	{
		list($route, $data, $method, $private_key) = $this->_getSignatureData();

		$result = Rest_Signature::factory($private_key)
					->verify($route, $data, $method);

		$this->assertEquals(FALSE, $result);
	}

	public function testVerifyBadMethod ()
	{
		list($route, $data, $method, $private_key) = $this->_getSignatureData();

		// break method
		$method = Rest::DELETE;

		$result = Rest_Signature::factory($private_key)
					->verify($route, $data, $method);

		$this->assertEquals(FALSE, $result);
	}

	public function testVerifyBadData ()
	{
		list($route, $data, $method, $private_key) = $this->_getSignatureData();

		// add extra data, this should break the signature
		$data['key3'] = 'value3';
		$data['key4'] = 'value4';

		$result = Rest_Signature::factory($private_key)
					->verify($route, $data, $method);

		$this->assertEquals(FALSE, $result);
	}
}
