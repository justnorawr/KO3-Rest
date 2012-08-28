<?php defined('SYSPATH') or die('No direct script access.');

class Rest_UtilTest extends Kohana_Unittest_TestCase
{
	public function _codes ()
	{
		return array
		(
			array
			(
				// $codes
				array
				(
					100, 101, 200, 201, 202, 203, 204, 205, 206, 300, 301, 302, 303, 304,
					305, 306, 307, 400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410,
					411, 412, 413, 414, 415, 416, 417, 500, 501, 502, 503, 504, 505
				)
			)
		);
	}

	/**
	 * @dataProvider	_codes
	 */
	public function testCodes ($codes)
	{
		foreach ($codes AS $value)
		{
			$message = Rest_Util::factory()->status_message($value);
			$result = ( strlen($message) >= 1 ) ? TRUE : FALSE ;
			$this->assertTrue($result) ;
		}

		try
		{
			$message = Rest_Util::factory()->status_message(701054165465);

			$this->assertFalse(TRUE);
		}
		catch (Exception $e)
		{
			$this->assertInstanceOf('Kohana_Rest_Exception', $e);
		}
	}
}