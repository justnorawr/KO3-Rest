<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 *
 * @author		Nicholas Curtis		<nich.curtis@gmail.com>
 */
class Controller_Rest_Example extends Controller_Template_Twig
{
	/**
	 *
	 *
	 *
	 */
	public function before ()
	{
		parent::before();
	}

	public function action_index ()
	{
		
	}

	public function action_process ()
	{
		//form the url
		$url = BASEURL . $_POST['controller'] . '/' . $_POST['action'];

		$keys = $_POST['keys'];
		$method = $_POST['method'];

		$data = array();
		foreach ($keys AS $key => $name)
		{
			$data[$name] = $_POST['values'][$key];
		}

		$url = $url . '?json_print_pretty=yes';

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER	=>	TRUE,
			CURLOPT_HEADER		=>	TRUE
		));

		//do the magic here
		switch(strtoupper($method))
		{
			case 'GET':
				curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
				break;
			
			case 'POST':
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;
			
			case 'PUT':
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
				break;

			case 'DELETE':
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
				break;

			default:
				exit;
				break;
		}

		$response = curl_exec($ch);

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);

		echo json_encode(array('params' => print_r(array('GET' => $_GET, 'POST' => $_POST), true), 'response' => $body, 'headers' => $header));
		exit;
	}
	
	/**
	 *
	 *
	 *
	 */
	public function after ()
	{
		$this->template->set('base_url', BASEURL);

		if (Kohana::$profiling === TRUE) {
			$this->template->set('profiler', View::factory('profiler/stats'));
		}

		parent::after();
	}
}
