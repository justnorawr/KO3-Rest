KO3-Rest
========

Rest Module for Kohana Framework Version 3.2+

Configuration
=========


Initializing Library
=========

`$Rest = Rest::instance('default');`

Receiving Request
=========
    try
    {
      $Rest->process();
    }
    catch (Rest_Exception $e)
    {
      $Rest->respond($e->code, array('message', $e->message));
    }

Authentication
=========


Send Response
=========


License
=========

This is licensed under the same license as Kohana
http://kohanaframework.org/license
This project is not endorsed by the Kohana Framework project.