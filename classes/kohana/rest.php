<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * 
 * @package		Kohana
 * @subpackage	Rest
 * @author		Nicholas Curtis			<nich.curtis@gmail.com>		<http://www.nichcurtis.me> 
 */

abstract class Kohana_Rest
{
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
		$this->request_vars	=	array();
		$this->data		=	null;
		$this->http_accept	=	($_SERVER['HTTP_ACCEPT'] == 'application/xml')
								? 'application/xml'
								: 'application/json';
		$this->method	=	'GET';
	}
	
	/**
	 * 
	 * 
	 * @return		Rest
	 * @chainable
	 */
	public function process ()
	{
		// get request method from $_SERVER var
		$this->method = (array_key_exists('REQUEST_METHOD', $_SERVER)) ? $_SERVER['REQUEST_METHOD'] : 'GET' ;
		
		switch ($this->method)
		{
			// if request method is GET then get data from $_GET
			case 'GET':
				$this->request_data = $_GET;
				break;
			
			// if request method is POST then get data from $_POST
			case 'POST':
				$this->request_data = $_POST;
				break;
			
			// if request method is PUT then get post data
			case 'PUT':
				$this->request_data = $_POST;
				break;
			
			// if request method is DELETE then we dont check for data
			case 'DELETE':
				$this->request_data = array();
				break;
		}
		
		// check to see if there is data in the request_data gathered
		if (array_key_exists('data', $this->request_data))
		{
			$this->data = json_decode(urldecode($this->request_data['data']));	
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
	 * @param		string		$name
	 * @return		void
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
	 * 
	 * 
	 * @param		int		$status_code
	 * @param		array		$response_data
	 * @return		bool
	 */
	public function respond ($status_code, $response_data=array())
	{
		// set status header from status code passed
		$status_message = Rest_Util::factory()->status_message($status_code);
		$status_header = 'HTTP/1.1 ' . $status_code . ' ' . $status_message;
		header($status_header);
		
		// check http accept and set content type to what they will accept
		header('Content-Type: '.$this->http_accept);
		
		// check what kind of data we are allowed to return
		switch ($this->http_accept)
		{
			// return xml data
			case 'application/xml':
				echo '<response><status><code>501</code><message>Not Implemented</message></status></response>'; exit;
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
				
				// add data if we have some
				if ( ! empty($response_data)) $return_data->body = $response_data;
				
				// output all return data asa JSON formatted string
				echo urlencode(json_encode($return_data)); exit;
				break;
		}
	}
}
