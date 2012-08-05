<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @package		KO3-Rest
 * @subpackage	Utility
 * @author		Nicholas Curtis	<nich.curtis@gmail.com>
 */

abstract class Kohana_Rest_Util
{
	/**
	 * holds list of http status codes
	 * 
	 * @var		array
	 */
	protected $codes = array
	(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => '(Unused)',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported'
	);
	
	/**
	 * Creates a new Rest_Util object
	 * 
	 * @return		Rest_Util
	 * @chainable
	 */
	public static function factory ()
	{
		return new Rest_Util();
	}
	
	/**
	 * returns status message that corresponds to status code passed
	 * 
	 * @param		int			$status_code
	 * @return		string
	 */
	public function status_message ($status_code)
	{
		if ( ! array_key_exists($status_code, $this->codes))
		{
			throw new Kohana_Exception('Status code :code is invalid', array(':code' => $status_code));
		}
		
		return $this->codes[$status_code];
	}
}
