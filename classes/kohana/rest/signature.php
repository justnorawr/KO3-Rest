<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @package		KO3-Rest
 * @subpackage	Signature
 * @author		Nicholas Curtis	<nich.curtis@gmail.com>
 */

abstract class Kohana_Rest_Signature
{
	protected $_config;
	
	protected $_public_key;

	protected $_private_key;

	/**
	 * Creates a new Rest_Signature object
	 * 
	 * @return		Rest_Signature
	 * @chainable
	 */
	public static function factory ($public_key, $private_key)
	{
		return new Rest_Signature($public_key, $private_key);
	}

	public function __construct ($private_key)
	{
		$this->_config = Kohana::$config->load('rest.signature');

		$this->_private_key = $private_key;
	}

	/**
	 * Returns signature for parameters passed
	 * 
	 * @param	string		$route			route name for request
	 * @param	array 		$data
	 * @param	string 		$method
	 * @return	string
	 */
	public function signature ($route, Array $data, $method=Rest::GET)
	{
		if ( ! $this->_validate_data($data) )
		{
			// @todo log debug profile
			return false;
		}

		return $this->_sign($route, $data, $method);
	}

	/**
	 * creates salt and hash and encypts information using php crypt()
	 *
	 * salt = private_key::method::route
	 * signature = method::public_key::private_key::timestamp:: md5(data)
	 * 
	 * @param	string		$route			route name for request
	 * @param	array 		$data
	 * @param	string 		$method
	 * @return	string
	 */
	protected function _sign ($route, $data, $method)
	{
		// create salt using private key, method, and route
		$salt = $this->_private_key.'::'.$method.'::'.$route;

		// get data and remove signature from it
		$requestData = $data;
		unset($requestData['signature']);

		$signature = $method . '::' . $data['public_key'] . '::' . $this->_private_key .
				'::' . $data['timestamp'] . '::' . md5($requestData);

		$encrypted = crypt($signature, $this->_config['salt'].$salt.'$');

		return $encrypted;
	}

	/**
	 * Verifies valid signature for parameters passed
	 *
	 * @param	string		$route			route name for request
	 * @param	array 		$data
	 * @param	string 		$method
	 * @return	bool
	 */
	public function verify ($route, Array $data, $method=Rest::GET)
	{
		if ( ! $this->_validate_data($data) )
		{
			// @todo log debug profile
			return false;
		}

		// make sure timestamp is within allowed range
		$high_timestamp = $data[$this->_config['timestamp']] + $this->_config['replaytime'];
		$low_timestamp = $data[$this->_config['timestamp']] - $this->_config['replaytime'];
		$current_time = time();
		
		if ($current_time < $low_timestamp OR $current_time > $high_stamp)
		{
			// @todo log debug profile
			return false;
		}

		$signature = $data[$this->_config['signature']];

		if ( $signature === $this->_sign($route, $data, $method) )
		{
			// @todo log debug profile
			return true;
		}
		else
		{
			// @todo log debug profile
			return false;
		}
	}

	/**
	 * Validates request data contains appropiate fields for signature validation
	 * 
	 * @param	array 		$data
	 * @return	bool
	 */
	protected function _validate_data (Array $data)
	{
		// return false if timestamp does not exist in data passed
		if ( ! array_key_exists($this->_config['timestamp'], $data))
		{
			// @todo log debug profile
			return false;
		}

		// return false if public_key does not exist in data passed
		if ( ! array_key_exists($this->_config['public_key'], $data))
		{
			// @todo log debug profile
			return false;
		}

		// return false if signature does not exist in data passed
		if ( ! array_key_exists($this->_config['signature'], $data))
		{
			// @todo log debug profile
			return false;
		}

		// get public key and verify it matches ours
		$public_key = $data['public_key'];

		if ($public_key != $this->_public_key)
		{
			// @todo log debug profile
			return false;
		}

		// data is good
		return true;
	}
}
