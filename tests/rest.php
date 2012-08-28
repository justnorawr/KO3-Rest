<?php defined('SYSPATH') or die('No direct script access.');

class RestTest extends Kohana_Unittest_TestCase
{
	protected $signature = '$2a$10$THISISMYPRIVATEKEY$$$.oAd/1k9UJsGHBu5fMkWdj2jvhUbc.b.';

	public function __construct ()
	{
		$_SERVER['HTTP_ACCEPT'] = 'application//json';
		$_SERVER['REQUEST_METHOD'] = 'GET';
	}

	public function testConfig ()
	{
		$keys = array
		(
			'sign_request'	=>	FALSE,
			'debug'		=>	97.32,
			'profile'		=>	FALSE,
			'signature'	=>	array(
				'testkey' => 'testvalue'
			)
		);

		$signature = Kohana::$config->load('rest.signature');

		foreach ($keys AS $key => $value)
		{
			$Rest = Rest::instance('testConfig-'.$key);
			$Rest->setConfig(array($key => $value));
			$config = $Rest->config();
			
			if ($key == 'signature')
			{
				$this->assertEquals(ARR::merge($value, (array) $signature), $config[$key]);
			}
			else
			{
				$this->assertEquals($value, $config[$key]);	
			}
		}
	}

	public function testEmptyProcess ()
	{
		$methods = array(Rest::PUT, Rest::GET, Rest::DELETE, Rest::POST);

		// reset get and post
		$_GET = array(); $_POST = array();

		foreach ($methods AS $method)
		{
			// set request method
			$_SERVER['REQUEST_METHOD'] = $method;

			// process empty request without validation signature
			$Rest = Rest::instance('testEmptyProcess-'.$method);
			$Rest->setConfig(array('sign_request' => FALSE));
			$result = $Rest->process();
			$this->assertInstanceOf('Rest', $result);

			// process empty request with signature validation
			try
			{
				$Rest = Rest::instance('testEmptyProcess-nosign-'.$method);
				$Rest->setConfig(array('sign_request' => TRUE));
				$result = $Rest->process();
				// line above should throw exception if unit test is good
				$this->assertFalse(TRUE);
			}
			catch (Rest_Exception $e)
			{
				$this->assertInstanceOf('Rest_Exception', $e);
			}
		}
	}

	public function testProcess ()
	{
		// reset post data
		$_POST = array();

		// test with get request using signature verification
		// but passing a bad timestamp this time bad timestamp
		try
		{
			$Rest = Rest::instance('testProcess-2');
			$Rest->setConfig(array('sign_request' => TRUE));
			$result = $Rest->process();
			// line above should throw an exception because of the bad timestamp
			$this->assertFalse(TRUE);
		}
		catch (Rest_Exception $e)
		{
			$this->assertInstanceOf('Rest_Exception', $e);
		}

		// test get request with signature verification
		// but removing the signature from the request
		try
		{
			unset($_GET['signature']);
			$Rest = Rest::instance('testProcess-3');
			$Rest->setConfig(array('sign_request' => TRUE));
			$result = $Rest->process();
			/// if this assert is run then the line above did not throw exception as expected
			$this->assertFalse(TRUE);
		}
		catch (Rest_Exception $e)
		{
			$this->assertInstanceOf('Rest_Exception', $e);
		}
	}

	public function testProcessPut ()
	{
		$methods = array(Rest::PUT, Rest::POST);

		// reset get data
		$_GET = array();

		foreach ($methods AS $method)
		{
			$_SERVER['REQUEST_METHOD'] = $method;

			// test with get request using signature verification
			// but passing a bad timestamp this time bad timestamp
			try
			{
				$Rest = Rest::instance($method.'testProcessPut-2');
				$Rest->setConfig(array('sign_request' => TRUE));
				$Rest->setSignatureConfig(array('replaytimeout' => 1));
				$result = $Rest->process();
				// line above should throw an exception because of the bad timestamp
				$this->assertFalse(TRUE);
			}
			catch (Rest_Exception $e)
			{
				$this->assertInstanceOf('Rest_Exception', $e);
			}

			// test get request with signature verification
			// but removing the signature from the request
			try
			{
				$Rest = Rest::instance($method.'testProcessPut-3');
				unset($_POST['signature']);
				$result = $Rest->process();
				/// if this assert is run then the line above did not throw exception as expected
				$this->assertFalse(TRUE);
			}
			catch (Rest_Exception $e)
			{
				$this->assertInstanceOf('Rest_Exception', $e);
			}
		}
	}

	public function testProcessDelete ()
	{
		$_SERVER['REQUEST_METHOD'] = Rest::DELETE;
		$_GET = array(); $_POST = array();
		$Rest = Rest::instance('testProcessDelete');
		$Rest->setConfig(array('sign_request' => FALSE));
		$result = $Rest->process();
		$this->assertInstanceOf('Rest', $result);

		try
		{
			$_SERVER['REQUEST_METHOD'] = Rest::DELETE;
			$_GET = array(); $_POST = array();
			$Rest = Rest::instance('testProcessDelete');
			$Rest->setConfig(array('sign_request' => TRUE));
			$result = $Rest->process();
			$this->assertFalse(TRUE);
		}
		catch (Rest_Exception $e)
		{
			$this->assertInstanceOf('Rest_Exception', $e);
		}
	}

	public function testRequestMethod ()
	{
		$methods = array(Rest::PUT, Rest::GET, Rest::DELETE, Rest::POST);

		$_GET = array(); $_POST = array();

		foreach ($methods AS $method)
		{
			$_SERVER['REQUEST_METHOD'] = $method;
			$Rest = Rest::instance('testProcessDelete');
			$Rest->setConfig(array('sign_request' => FALSE));
			$result = $Rest->process();
			$this->assertEquals($method, $result->request_method());
		}
	}
}
