<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @package		KO3-Rest
 * @subpackage	Core
 * @author		Nicholas Curtis	<nich.curtis@gmail.com>
 */

abstract class Kohana_Rest
{
	// available REST request methods
	const GET	=	'GET';
	const POST	=	'POST';
	const PUT	=	'PUT';
	const DELETE	=	'DELETE';

	/**
	 * holds instances of Kohana_Rest
	 * 
	 * @var		array
	 */
	protected static $instances = array();
	
	/**
	 * holds array of raw request
	 * 
	 * @var		array
	 */
	protected $request_data;
	
	/**
	 * 
	 * 
	 * @var		array
	 */
	protected $request_vars;
	
	/**
	 * holds decoded JSON object
	 * 
	 * @var		StdClass
	 */
	protected $data;
	
	/**
	 * holds http accept methods (application/xml, application/json)
	 * 
	 * @var		string
	 */
	protected $http_accept;
	
	/**
	 * holds request method (GET, PUT, POST, DELETE)
	 * 
	 * @var		string
	 */
	protected $method;

	/**
	 * holds current request URI
	 * 
	 * @var		string
	 */
	protected $route;

	/**
	 * 
	 * 
	 * @param	string		$name		// name of instance
	 * @return  Rest
	 * @uses    Kohana::config
	 */
	public static function instance ($name='default')
	{
		if ( ! isset(Rest::$instances[$name]))
		{
			// Create a new Rest instance
			Rest::$instances[$name] = new Rest();
		}

		return Rest::$instances[$name];
	}
	
	/**
	 * constructs object, protected method as object can not be constructed unless using Rest::instance()
	 * 
	 * @return		void
	 */
	protected function __construct ()
	{
		$this->_config = Kohana::$config->load('rest');

		$this->route		=	'Rest::Test';
		$this->request_vars	=	array();
		$this->data		=	null;
		$this->http_accept	=	($_SERVER['HTTP_ACCEPT'] == 'application/xml')
								? 'application/xml'
								: 'application/json';
		$this->method	=	Rest::GET;
	}

	/**
	 * updates configuration array for current instance
	 *
	 * @param	array 		$config	new config values
	 * @return	bool
	 */
	public function setConfig (Array $config)
	{
		if ( count($config) > 0)
		{
			// update config with new info
			$_config = (array) $this->_config;
			$this->_config = ARR::merge($_config, $config);
			return true;
		}

		return false;
	}

	/**
	 * updates configuration array for current instance
	 *
	 * @param	array 		$config	new config values
	 * @return	bool
	 */
	public function setSignatureConfig (Array $config)
	{
		if ( count($config) > 0)
		{
			// update signature config array with new info
			$this->_config['signature'] = ARR::merge($this->_config['signature'], $config);
			return true;
		}

		return false;
	}

	/**
	 * Returns configuration array for current instance
	 *
	 * @return 	array
	 */
	public function config ()
	{
		return $this->_config;
	}
	
	/**
	 *  checks request method and loads request data into object
	 *  if sign_request is true, will also verify signature
	 * 
	 * @return		Rest
	 * @chainable
	 * @throws		Rest_Exception
	 */
	public function process ()
	{
		// get request method from $_SERVER var
		$this->method = (array_key_exists('REQUEST_METHOD', $_SERVER)) ? $_SERVER['REQUEST_METHOD'] : Rest::GET ;
		
		switch ($this->method)
		{
			// if request method is GET then get data from $_GET
			case Rest::GET:
				$this->request_data = $_GET;
				break;
			
			// if request method is POST then get data from $_POST
			case Rest::POST:
				$this->request_data = $_POST;
				break;
			
			// if request method is PUT then get post data
			case Rest::PUT:
				$this->request_data = $_POST;
				break;
			
			// if request method is DELETE then we dont check for data
			case Rest::DELETE:
				$this->request_data = array();
				break;

			default:
				// @todo log debug profile
				throw new Rest_Exception('Invalid request method');
				break;
		}
		
		// check to see if there is data in the request_data gathered
		if (array_key_exists('data', $this->request_data))
		{
			$this->data = json_decode(urldecode($this->request_data['data']));	

			if ( $this->_config['sign_request'] === TRUE )
			{
				if ( ! array_key_exists('signature', $this->data))
				{
					// @todo log debug profile
					throw new Rest_Exception('Invalid Signature');
				}

				if ( ! empty($this->_private_key) )
				{
					$signature = Rest_Signature::factory($this->_private_key)->verify($this->route, $this->data, $this->method);

					if ($this->data['signature'] !== $signature)
					{
						// @todo log debug profile
						throw new Rest_Exception('Invalid Signature');
					}
					else
					{
						// everything is good, and signature checked out
						return $this;
					}
				}
				else
				{
					// @todo log debug profile
					throw new Rest_Exception('Invalid Private Key');
				}
			}
			else
			{
				// do not need to validate signature, all else is good
				return $this;
			}
		}

		// no data sent, if we are supposed to verify request
		// we know for sure it is invalid since it is empty
		if ( $this->_config['sign_request'] === TRUE )
		{
			// @todo log debug profile
			throw new Rest_Exception('Invalid Signature');
		}
		
		return $this;
	}
	
	/**
	 *
	 * returns string of request method gathered in $this->process
	 *
	 * @return		string
	 */
	public function request_method ()
	{
		return $this->method;
	}
	
	/**
	 * used to access properties in $this->data
	 * 
	 * @param	string		$name
	 * @return	void
	 * @throws	Rest_Exception
	 */
	public function __get ($name)
	{
		if ( ! isset($this->data->$name))
		{
			throw new Kohana_Exception('Invalid property - :prop_name ', array(':prop_name' => $name));
		}
		
		return $this->data->$name;
	}
	
	/**
	 * Sets kohana request headers and response body
	 * 
	 * @param	int		$status_code
	 * @param	array		$response_data
	 * @return	bool
	 * @throws	Rest_Exception
	 */
	public function respond ($status_code, $response_data=array())
	{
		// set status header from status code passed
		$status_message = Rest_Util::factory()->status_message($status_code);
		$status_header = 'HTTP/1.1 ' . $status_code . ' ' . $status_message;
		Request::$current->headers($status_header);
		
		// check http accept and set content type to what they will accept
		Request::$current->headers('Content-Type: '.$this->http_accept);
		
		// check what kind of data we are allowed to return
		switch ($this->http_accept)
		{
			// return xml data
			case 'application/xml':
				Request::$current->body
				('
					<response>
						<status>
							<code>501</code>
							<message>Not Implemented</message>
						</status>
					</response>
				');
				break;
			
			// return json data
			case 'application/json':
				$return_data = new StdClass;
				
				$return_data->status = array
				(
					'code'		=>	$status_code,
					'message'	=>	Rest_Util::factory()->status_message($status_code),
					'method'	=>	$this->method,
					'data'		=>	array('request' => $this->request_data, 'processed' => $this->data),
				);

				if ($this->_config['sign_request'] === TRUE)
				{
					$signature = Rest_Signature::factory($private_key)
									->signature($this->route, $this->data, $this->method);
					if ($signature)
					{
						$return_data['signature'] = $signature;
					}
					else
					{
						throw new Rest_Exception('Unable to sign request.');
					}
				}
				
				// add data if we have some
				if ( ! empty($response_data)) $return_data->body = $response_data;
				
				// output all return data as JSON formatted string
				Request::$current->body(urlencode(json_encode($return_data)));
				break;

			default:
				throw new Rest_Exception('Invalid HTTP Accept type');
				break;
		}

		return true;
	}
}
