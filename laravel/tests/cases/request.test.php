<?php

use Symfony\Component\HttpFoundation\LaravelRequest as RequestFoundation;

class SessionPayloadTokenStub {

	public function token() { return 'Taylor'; }

}

class RequestTest extends PHPUnit_Framework_TestCase {

	/**
	 * Tear down the test environment.
	 */
	public function tearDown()
	{
		$_POST = array();
		$_SERVER = array();
		Request::$route = null;
		Session::$instance = null;
	}

	/**
	 * Set one of the $_SERVER variables.
	 *
	 * @param string  $key
	 * @param string  $value
	 */
	protected function setServerVar($key, $value)
	{
		$_SERVER[$key] = $value;

		$this->restartRequest();
	}

	/**
	 * Set one of the $_POST variables.
	 *
	 * @param string  $key
	 * @param string  $value
	 */
	protected function setPostVar($key, $value)
	{
		$_POST[$key] = $value;

		$this->restartRequest();
	}

	/**
	 * Reinitialize the global request.
	 * 
	 * @return void
	 */
	protected function restartRequest()
	{
		// FIXME: Ugly hack, but old contents from previous requests seem to
		// trip up the Foundation class.
		$_FILES = array();

		Request::$foundation = RequestFoundation::createFromGlobals();
	}

	/**
	 * Test the Request::method method.
	 *
	 * @group laravel
	 */
	public function testMethodReturnsTheHTTPRequestMethod()
	{
		$this->setServerVar('REQUEST_METHOD', 'POST');

		$this->assertEquals('POST', Request::method());

		$this->setPostVar(Request::spoofer, 'PUT');

		$this->assertEquals('PUT', Request::method());
	}

	/**
	 * Test the Request::server method.
	 *
	 * @group laravel
	 */
	public function testServerMethodReturnsFromServerArray()
	{
		$this->setServerVar('TEST', 'something');
		$this->setServerVar('USER', array('NAME' => 'taylor'));

		$this->assertEquals('something', Request::server('test'));
		$this->assertEquals('taylor', Request::server('user.name'));
	}

	/**
	 * Test the Request::ip method.
	 *
	 * @group laravel
	 */
	public function testIPMethodReturnsClientIPAddress()
	{
		$this->setServerVar('REMOTE_ADDR', 'something');
		$this->assertEquals('something', Request::ip());

		$this->setServerVar('HTTP_CLIENT_IP', 'something');
		$this->assertEquals('something', Request::ip());

		$this->setServerVar('HTTP_CLIENT_IP', 'something');
		$this->assertEquals('something', Request::ip());

		$_SERVER = array();
		$this->restartRequest();
		$this->assertEquals('0.0.0.0', Request::ip());
	}

	/**
	 * Test the Request::secure method.
	 *
	 * @group laravel
	 */
	public function testSecureMethodsIndicatesIfHTTPS()
	{
		$this->setServerVar('HTTPS', 'on');
		
		$this->assertTrue(Request::secure());

		$this->setServerVar('HTTPS', 'off');

		$this->assertFalse(Request::secure());
	}

	/**
	 * Test the Request::ajax method.
	 *
	 * @group laravel
	 */
	public function testAjaxMethodIndicatesWhenAjax()
	{
		$this->assertFalse(Request::ajax());

		$this->setServerVar('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest');

		$this->assertTrue(Request::ajax());
	}

	/**
	 * Test the Request::forged method.
	 *
	 * @group laravel
	 */
	public function testForgedMethodIndicatesIfRequestWasForged()
	{
		Session::$instance = new SessionPayloadTokenStub;

		$input = array(Session::csrf_token => 'Foo');
		Request::foundation()->request->add($input);

		$this->assertTrue(Request::forged());

		$input = array(Session::csrf_token => 'Taylor');
		Request::foundation()->request->add($input);
		
		$this->assertFalse(Request::forged());
	}

	/**
	 * Test the Request::route method.
	 *
	 * @group laravel
	 */
	public function testRouteMethodReturnsStaticRoute()
	{
		Request::$route = 'Taylor';

		$this->assertEquals('Taylor', Request::route());
	}

	/**
	 * Test the Request::set_header method.
	 *
	 * @group laravel
	 */
	public function testSetHeader()
	{
		Request::set_header('accept', 'application/json');
		Request::set_header('content-type', 'application/json');
		$headers = Request::headers();

		$this->assertEquals('application/json', $headers['accept'][0]);
		$this->assertEquals('application/json', $headers['content-type'][0]);
	}

	/**
	 * Test the Request::accept method.
	 *
	 * @group laravel
	 */
	public function testRequestAccept()
	{
		Request::set_header('accept', 'application/json');
		$accept = Request::accept();

		$this->assertEquals('application/json', $accept[0]);

		Request::set_header('accept', 'text/plain');
		$accept = Request::accept();

		$this->assertEquals('text/plain', $accept[0]);
	}

	/**
	 * Test the Request::format method.
	 *
	 * @group laravel
	 */
	public function testRequestFormat()
	{
		Request::set_header('accept', 'application/json');

		$this->assertEquals('json', Request::format());

		Request::set_header('accept', 'text/plain');
		$headers = Request::headers();

		$this->assertEquals('txt', Request::format());
	}

}