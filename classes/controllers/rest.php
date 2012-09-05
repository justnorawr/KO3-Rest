<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 *
 * @author		Nicholas Curtis		<nich.curtis@gmail.com>
 */
class Controller_Rest extends Controller_Template_Twig
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

	public function action_index()
	{
		
	}

	public function action_process ()
	{
		echo json_encode( array('data' => $results) );
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
