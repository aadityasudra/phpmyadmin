<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * tests for PhpMyAdmin\Plugins\Auth\AuthenticationCookie class
 *
 * @package PhpMyAdmin-test
 */

namespace PhpMyAdmin\Tests\Plugins\Auth;

use PhpMyAdmin\Config;
use PhpMyAdmin\ErrorHandler;
use PhpMyAdmin\Footer;
use PhpMyAdmin\Header;
use PhpMyAdmin\Plugins\Auth\AuthenticationCookie;
use PhpMyAdmin\Theme;
use ReflectionMethod;

require_once 'libraries/config.default.php';

/**
 * tests for PhpMyAdmin\Plugins\Auth\AuthenticationCookie class
 *
 * @package PhpMyAdmin-test
 */
class AuthenticationCookieTest extends \PMATestCase
{
    /**
     * @var AuthenticationCookie
     */
    protected $object;

    /**
     * Configures global environment.
     *
     * @return void
     */
    public function setup()
    {
        $GLOBALS['PMA_Config'] = new Config();
        $GLOBALS['PMA_Config']->enableBc();
        $GLOBALS['server'] = 0;
        $GLOBALS['text_dir'] = 'ltr';
        $GLOBALS['db'] = 'db';
        $GLOBALS['table'] = 'table';
        $_REQUEST['pma_password'] = '';
        $this->object = new AuthenticationCookie();
        $GLOBALS['PMA_PHP_SELF'] = '/phpmyadmin/';
    }

    /**
     * tearDown for test cases
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->object);
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::auth
     *
     * @return void
     * @group medium
     */
    public function testAuthErrorAJAX()
    {
        $mockResponse = $this->mockResponse();

        $mockResponse->expects($this->once())
            ->method('isAjax')
            ->with()
            ->will($this->returnValue(true));

        $mockResponse->expects($this->once())
            ->method('setRequestStatus')
            ->with(false);

        $mockResponse->expects($this->once())
            ->method('addJSON')
            ->with(
                'redirect_flag',
                '1'
            );

        $GLOBALS['conn_error'] = true;
        $this->assertTrue(
            $this->object->auth()
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::auth
     *
     * @return void
     * @group medium
     */
    public function testAuthError()
    {
        $mockResponse = $this->mockResponse();

        $mockResponse->expects($this->once())
            ->method('isAjax')
            ->with()
            ->will($this->returnValue(false));

        $_REQUEST['old_usr'] = '';
        $GLOBALS['cfg']['LoginCookieRecall'] = true;
        $GLOBALS['cfg']['blowfish_secret'] = 'secret';
        $GLOBALS['PHP_AUTH_USER'] = 'pmauser';
        $GLOBALS['pma_auth_server'] = 'localhost';

        // mock footer
        $mockFooter = $this->getMockBuilder('PhpMyAdmin\Footer')
            ->disableOriginalConstructor()
            ->setMethods(array('setMinimal'))
            ->getMock();

        $mockFooter->expects($this->once())
            ->method('setMinimal')
            ->with();

        // mock header

        $mockHeader = $this->getMockBuilder('PhpMyAdmin\Header')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'setBodyId',
                    'setTitle',
                    'disableMenuAndConsole',
                    'disableWarnings'
                )
            )
            ->getMock();

        $mockHeader->expects($this->once())
            ->method('setBodyId')
            ->with('loginform');

        $mockHeader->expects($this->once())
            ->method('setTitle')
            ->with('phpMyAdmin');

        $mockHeader->expects($this->once())
            ->method('disableMenuAndConsole')
            ->with();

        $mockHeader->expects($this->once())
            ->method('disableWarnings')
            ->with();

        // set mocked headers and footers

        $mockResponse->expects($this->once())
            ->method('getFooter')
            ->with()
            ->will($this->returnValue($mockFooter));

        $mockResponse->expects($this->once())
            ->method('getHeader')
            ->with()
            ->will($this->returnValue($mockHeader));

        $GLOBALS['pmaThemeImage'] = 'test';
        $GLOBALS['conn_error'] = true;
        $GLOBALS['cfg']['Lang'] = 'en';
        $GLOBALS['cfg']['AllowArbitraryServer'] = true;
        $GLOBALS['cfg']['Servers'] = array(1, 2);
        $GLOBALS['cfg']['CaptchaLoginPrivateKey'] = '';
        $GLOBALS['cfg']['CaptchaLoginPublicKey'] = '';
        $GLOBALS['target'] = 'testTarget';
        $GLOBALS['db'] = 'testDb';
        $GLOBALS['table'] = 'testTable';

        file_put_contents('testlogo_right.png', '');

        // mock error handler

        $mockErrorHandler = $this->getMockBuilder('PhpMyAdmin\ErrorHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('hasDisplayErrors', 'dispErrors'))
            ->getMock();

        $mockErrorHandler->expects($this->once())
            ->method('hasDisplayErrors')
            ->with()
            ->will($this->returnValue(true));

        $mockErrorHandler->expects($this->once())
            ->method('dispErrors')
            ->with();

        $GLOBALS['error_handler'] = $mockErrorHandler;

        ob_start();
        $this->object->auth();
        $result = ob_get_clean();

        // assertions

        $this->assertContains(
            '<img src="testlogo_right.png" id="imLogo"',
            $result
        );

        $this->assertContains(
            '<div class="error">',
            $result
        );

        $this->assertContains(
            '<form method="post" id="login_form" action="index.php" name="login_form" ' .
            'class="disableAjax login hide js-show">',
            $result
        );

        $this->assertContains(
            '<input type="text" name="pma_servername" id="input_servername" ' .
            'value="localhost"',
            $result
        );

        $this->assertContains(
            '<input type="text" name="pma_username" id="input_username" ' .
            'value="pmauser" size="24" class="textfield"/>',
            $result
        );

        $this->assertContains(
            '<input type="password" name="pma_password" id="input_password" ' .
            'value="" size="24" class="textfield" />',
            $result
        );

        $this->assertContains(
            '<select name="server" id="select_server" ' .
            'onchange="document.forms[\'login_form\'].' .
            'elements[\'pma_servername\'].value = \'\'" >',
            $result
        );

        $this->assertContains(
            '<input type="hidden" name="target" value="testTarget" />',
            $result
        );

        $this->assertContains(
            '<input type="hidden" name="db" value="testDb" />',
            $result
        );

        $this->assertContains(
            '<input type="hidden" name="table" value="testTable" />',
            $result
        );

        @unlink('testlogo_right.png');
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::auth
     *
     * @return void
     * @group medium
     */
    public function testAuthCaptcha()
    {
        $mockResponse = $this->mockResponse();

        $mockResponse->expects($this->once())
            ->method('isAjax')
            ->with()
            ->will($this->returnValue(false));

        $mockResponse->expects($this->once())
            ->method('getFooter')
            ->with()
            ->will($this->returnValue(new Footer()));

        $mockResponse->expects($this->once())
            ->method('getHeader')
            ->with()
            ->will($this->returnValue(new Header()));

        $_REQUEST['old_usr'] = '';
        $GLOBALS['cfg']['LoginCookieRecall'] = false;

        $GLOBALS['pmaThemeImage'] = 'test';
        $GLOBALS['cfg']['Lang'] = '';
        $GLOBALS['cfg']['AllowArbitraryServer'] = false;
        $GLOBALS['cfg']['Servers'] = array(1);
        $GLOBALS['cfg']['CaptchaLoginPrivateKey'] = 'testprivkey';
        $GLOBALS['cfg']['CaptchaLoginPublicKey'] = 'testpubkey';
        $GLOBALS['server'] = 0;

        $GLOBALS['error_handler'] = new ErrorHandler();

        ob_start();
        $this->object->auth();
        $result = ob_get_clean();

        // assertions

        $this->assertContains(
            '<img name="imLogo" id="imLogo" src="testpma_logo.png"',
            $result
        );

        // Check for language selection if locales are there
        $loc = LOCALE_PATH . '/cs/LC_MESSAGES/phpmyadmin.mo';
        if (is_readable($loc)) {
            $this->assertContains(
                '<select name="lang" class="autosubmit" lang="en" dir="ltr" ' .
                'id="sel-lang">',
                $result
            );
        }

        $this->assertContains(
            '<form method="post" id="login_form" action="index.php" name="login_form" ' .
            'autocomplete="off" class="disableAjax login hide js-show">',
            $result
        );

        $this->assertContains(
            '<input type="hidden" name="server" value="0" />',
            $result
        );

        $this->assertContains(
            '<script src="https://www.google.com/recaptcha/api.js?hl=en"'
            . ' async defer></script>',
            $result
        );

        $this->assertContains(
            '<input class="g-recaptcha" data-sitekey="testpubkey"'
            . ' data-callback="recaptchaCallback" value="Go" type="submit" id="input_go" />',
            $result
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::auth with headers
     *
     * @return void
     */
    public function testAuthHeader()
    {
        $GLOBALS['cfg']['LoginCookieDeleteAll'] = false;
        $GLOBALS['cfg']['Servers'] = array(1);

        $this->mockResponse('Location: https://example.com/logout');

        $GLOBALS['cfg']['Server']['LogoutURL'] = 'https://example.com/logout';
        $GLOBALS['cfg']['Server']['auth_type'] = 'cookie';

        $this->object->logOut();
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::auth with headers
     *
     * @return void
     */
    public function testAuthHeaderPartial()
    {
        $GLOBALS['cfg']['LoginCookieDeleteAll'] = false;
        $GLOBALS['cfg']['Servers'] = array(1, 2, 3);
        $GLOBALS['cfg']['Server']['LogoutURL'] = 'https://example.com/logout';
        $GLOBALS['cfg']['Server']['auth_type'] = 'cookie';
        $GLOBALS['collation_connection'] = 'utf-8';

        $_COOKIE['pmaAuth-2'] = '';

        $this->mockResponse('Location: /phpmyadmin/index.php?server=2&lang=en&collation_connection=utf-8');

        $this->object->logOut();
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::authCheck
     *
     * @return void
     */
    public function testAuthCheckCaptcha()
    {
        $GLOBALS['cfg']['CaptchaLoginPrivateKey'] = 'testprivkey';
        $GLOBALS['cfg']['CaptchaLoginPublicKey'] = 'testpubkey';
        $_POST["g-recaptcha-response"] = '';
        $_REQUEST['pma_username'] = 'testPMAUser';

        $this->assertFalse(
            $this->object->authCheck()
        );

        $this->assertEquals(
            'Missing reCAPTCHA verification, maybe it has been blocked by adblock?',
            $GLOBALS['conn_error']
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::authCheck
     *
     * @return void
     */
    public function testLogoutDelete()
    {
        $this->mockResponse('Location: /phpmyadmin/index.php');

        $GLOBALS['cfg']['CaptchaLoginPrivateKey'] = '';
        $GLOBALS['cfg']['CaptchaLoginPublicKey'] = '';
        $GLOBALS['cfg']['LoginCookieDeleteAll'] = true;
        $GLOBALS['PMA_Config']->set('PmaAbsoluteUri', '');
        $GLOBALS['cfg']['Servers'] = array(1);

        $_COOKIE['pmaAuth-0'] = 'test';

        $this->object->logOut();

        $this->assertFalse(
            isset($_COOKIE['pmaAuth-0'])
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::authCheck
     *
     * @return void
     */
    public function testLogout()
    {
        $this->mockResponse('Location: /phpmyadmin/index.php');

        $GLOBALS['cfg']['CaptchaLoginPrivateKey'] = '';
        $GLOBALS['cfg']['CaptchaLoginPublicKey'] = '';
        $GLOBALS['cfg']['LoginCookieDeleteAll'] = false;
        $GLOBALS['PMA_Config']->set('PmaAbsoluteUri', '');
        $GLOBALS['cfg']['Servers'] = array(1);
        $GLOBALS['server'] = 1;
        $GLOBALS['cfg']['Server'] = array('auth_type' => 'cookie');

        $_COOKIE['pmaAuth-1'] = 'test';

        $this->object->logOut();

        $this->assertFalse(
            isset($_COOKIE['pmaAuth-1'])
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::authCheck
     *
     * @return void
     */
    public function testAuthCheckArbitrary()
    {
        $GLOBALS['cfg']['CaptchaLoginPrivateKey'] = '';
        $GLOBALS['cfg']['CaptchaLoginPublicKey'] = '';
        $_REQUEST['old_usr'] = '';
        $_REQUEST['pma_username'] = 'testPMAUser';
        $_REQUEST['pma_servername'] = 'testPMAServer';
        $_REQUEST['pma_password'] = 'testPMAPSWD';
        $GLOBALS['cfg']['AllowArbitraryServer'] = true;

        $this->assertTrue(
            $this->object->authCheck()
        );

        $this->assertEquals(
            'testPMAUser',
            $GLOBALS['PHP_AUTH_USER']
        );

        $this->assertEquals(
            'testPMAPSWD',
            $GLOBALS['PHP_AUTH_PW']
        );

        $this->assertEquals(
            'testPMAServer',
            $GLOBALS['pma_auth_server']
        );

        $this->assertFalse(
            isset($_COOKIE['pmaAuth-1'])
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::authCheck
     *
     * @return void
     */
    public function testAuthCheckInvalidCookie()
    {
        $GLOBALS['cfg']['AllowArbitraryServer'] = true;
        $_REQUEST['pma_servername'] = 'testPMAServer';
        $_REQUEST['pma_password'] = 'testPMAPSWD';
        $_REQUEST['pma_username'] = '';
        $GLOBALS['server'] = 1;
        $_COOKIE['pmaUser-1'] = '';
        $_COOKIE['pma_iv-1'] = base64_encode('testiv09testiv09');

        $this->assertFalse(
            $this->object->authCheck()
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::authCheck
     *
     * @return void
     */
    public function testAuthCheckExpires()
    {
        $GLOBALS['server'] = 1;
        $_COOKIE['pmaServer-1'] = 'pmaServ1';
        $_COOKIE['pmaUser-1'] = 'pmaUser1';
        $_COOKIE['pma_iv-1'] = base64_encode('testiv09testiv09');
        $_COOKIE['pmaAuth-1'] = '';
        $GLOBALS['cfg']['blowfish_secret'] = 'secret';
        $_SESSION['last_access_time'] = time() - 1000;
        $GLOBALS['cfg']['LoginCookieValidity'] = 1440;

        $this->assertFalse(
            $this->object->authCheck()
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::authCheck (mock blowfish functions reqd)
     *
     * @return void
     */
    public function testAuthCheckDecryptUser()
    {
        $GLOBALS['server'] = 1;
        $_REQUEST['old_usr'] = '';
        $_REQUEST['pma_username'] = '';
        $_COOKIE['pmaServer-1'] = 'pmaServ1';
        $_COOKIE['pmaUser-1'] = 'pmaUser1';
        $_COOKIE['pma_iv-1'] = base64_encode('testiv09testiv09');
        $GLOBALS['cfg']['blowfish_secret'] = 'secret';
        $_SESSION['last_access_time'] = '';
        $GLOBALS['cfg']['CaptchaLoginPrivateKey'] = '';
        $GLOBALS['cfg']['CaptchaLoginPublicKey'] = '';

        // mock for blowfish function
        $this->object = $this->getMockBuilder('PhpMyAdmin\Plugins\Auth\AuthenticationCookie')
            ->disableOriginalConstructor()
            ->setMethods(array('cookieDecrypt'))
            ->getMock();

        $this->object->expects($this->once())
            ->method('cookieDecrypt')
            ->will($this->returnValue('testBF'));

        $this->assertFalse(
            $this->object->authCheck()
        );

        $this->assertEquals(
            'testBF',
            $GLOBALS['PHP_AUTH_USER']
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::authCheck (mocking blowfish functions)
     *
     * @return void
     */
    public function testAuthCheckDecryptPassword()
    {
        $GLOBALS['server'] = 1;
        $_REQUEST['old_usr'] = '';
        $_REQUEST['pma_username'] = '';
        $_COOKIE['pmaServer-1'] = 'pmaServ1';
        $_COOKIE['pmaUser-1'] = 'pmaUser1';
        $_COOKIE['pmaAuth-1'] = 'pmaAuth1';
        $_COOKIE['pma_iv-1'] = base64_encode('testiv09testiv09');
        $GLOBALS['cfg']['blowfish_secret'] = 'secret';
        $GLOBALS['cfg']['CaptchaLoginPrivateKey'] = '';
        $GLOBALS['cfg']['CaptchaLoginPublicKey'] = '';
        $_SESSION['browser_access_time']['default'] = time() - 1000;
        $GLOBALS['cfg']['LoginCookieValidity'] = 1440;

        // mock for blowfish function
        $this->object = $this->getMockBuilder('PhpMyAdmin\Plugins\Auth\AuthenticationCookie')
            ->disableOriginalConstructor()
            ->setMethods(array('cookieDecrypt'))
            ->getMock();

        $this->object->expects($this->at(1))
            ->method('cookieDecrypt')
            ->will($this->returnValue('{"password":""}'));

        $this->assertTrue(
            $this->object->authCheck()
        );

        $this->assertTrue(
            $GLOBALS['from_cookie']
        );

        $this->assertEquals(
            '',
            $GLOBALS['PHP_AUTH_PW']
        );

    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::authCheck (mocking the object itself)
     *
     * @return void
     */
    public function testAuthCheckAuthFails()
    {
        $GLOBALS['server'] = 1;
        $_REQUEST['old_usr'] = '';
        $_REQUEST['pma_username'] = '';
        $_COOKIE['pmaServer-1'] = 'pmaServ1';
        $_COOKIE['pmaUser-1'] = 'pmaUser1';
        $_COOKIE['pma_iv-1'] = base64_encode('testiv09testiv09');
        $GLOBALS['cfg']['blowfish_secret'] = 'secret';
        $_SESSION['last_access_time'] = 1;
        $GLOBALS['cfg']['CaptchaLoginPrivateKey'] = '';
        $GLOBALS['cfg']['CaptchaLoginPublicKey'] = '';
        $GLOBALS['cfg']['LoginCookieValidity'] = 0;
        $_SESSION['browser_access_time']['default'] = -1;
        // mock for blowfish function
        $this->object = $this->getMockBuilder('PhpMyAdmin\Plugins\Auth\AuthenticationCookie')
            ->disableOriginalConstructor()
            ->setMethods(array('authFails'))
            ->getMock();

        $this->object->expects($this->once())
            ->method('authFails');

        $this->assertFalse(
            $this->object->authCheck()
        );

        $this->assertTrue(
            $GLOBALS['no_activity']
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::authSetUser
     *
     * @return void
     */
    public function testAuthSetUser()
    {
        $GLOBALS['PHP_AUTH_USER'] = 'pmaUser2';
        $arr = array(
            'host' => 'a',
            'port' => 1,
            'socket' => true,
            'ssl' => true,
            'user' => 'pmaUser2'
        );

        $GLOBALS['cfg']['Server'] = $arr;
        $GLOBALS['cfg']['Server']['user'] = 'pmaUser';
        $GLOBALS['cfg']['Servers'][1] = $arr;
        $GLOBALS['cfg']['AllowArbitraryServer'] = true;
        $GLOBALS['pma_auth_server'] = 'b 2';
        $GLOBALS['PHP_AUTH_PW'] = $_SERVER['PHP_AUTH_PW'] = 'testPW';
        $GLOBALS['server'] = 2;
        $GLOBALS['cfg']['LoginCookieStore'] = true;
        $GLOBALS['from_cookie'] = true;

        $this->object->authSetUser();

        $this->assertFalse(
            isset($GLOBALS['PHP_AUTH_PW'])
        );

        $this->assertFalse(
            isset($_SERVER['PHP_AUTH_PW'])
        );

        $this->object->storeUserCredentials();

        $this->assertTrue(
            isset($_COOKIE['pmaUser-2'])
        );

        $this->assertTrue(
            isset($_COOKIE['pmaAuth-2'])
        );

        $arr['password'] = 'testPW';
        $arr['host'] = 'b';
        $arr['port'] = '2';
        $this->assertEquals(
            $arr,
            $GLOBALS['cfg']['Server']
        );

    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::authSetUser (check for headers redirect)
     *
     * @return void
     */
    public function testAuthSetUserWithHeaders()
    {
        $GLOBALS['PHP_AUTH_USER'] = 'pmaUser2';
        $arr = array(
            'host' => 'a',
            'port' => 1,
            'socket' => true,
            'ssl' => true,
            'user' => 'pmaUser2'
        );

        $GLOBALS['cfg']['Server'] = $arr;
        $GLOBALS['cfg']['Server']['host'] = 'b';
        $GLOBALS['cfg']['Server']['user'] = 'pmaUser';
        $GLOBALS['cfg']['Servers'][1] = $arr;
        $GLOBALS['cfg']['AllowArbitraryServer'] = true;
        $GLOBALS['pma_auth_server'] = 'b 2';
        $GLOBALS['PHP_AUTH_PW'] = $_SERVER['PHP_AUTH_PW'] = 'testPW';
        $GLOBALS['server'] = 2;
        $GLOBALS['cfg']['LoginCookieStore'] = true;
        $GLOBALS['from_cookie'] = false;
        $GLOBALS['collation_connection'] = 'utf-8';

        $this->mockResponse(
            $this->stringContains('&server=2&lang=en&collation_connection=utf-8')
        );

        $this->object->authSetUser();
        $this->object->storeUserCredentials();
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::authFails
     *
     * @return void
     */
    public function testAuthFailsNoPass()
    {
        $this->object = $this->getMockBuilder('PhpMyAdmin\Plugins\Auth\AuthenticationCookie')
            ->disableOriginalConstructor()
            ->setMethods(array('auth'))
            ->getMock();

        $GLOBALS['server'] = 2;
        $_COOKIE['pmaAuth-2'] = 'pass';

        $GLOBALS['login_without_password_is_forbidden'] = '1';

        $this->mockResponse(
            array('Cache-Control: no-store, no-cache, must-revalidate'),
            array('Pragma: no-cache')
        );
        $this->object->authFails();

        $this->assertEquals(
            $GLOBALS['conn_error'],
            'Login without a password is forbidden by configuration'
            . ' (see AllowNoPassword)'
        );

    }

    public function testAuthFailsDeny()
    {
        $this->object = $this->getMockBuilder('PhpMyAdmin\Plugins\Auth\AuthenticationCookie')
            ->disableOriginalConstructor()
            ->setMethods(array('auth'))
            ->getMock();

        $GLOBALS['server'] = 2;
        $_COOKIE['pmaAuth-2'] = 'pass';

        $GLOBALS['login_without_password_is_forbidden'] = '';
        $GLOBALS['allowDeny_forbidden'] = '1';

        $this->mockResponse(
            array('Cache-Control: no-store, no-cache, must-revalidate'),
            array('Pragma: no-cache')
        );
        $this->object->authFails();

        $this->assertEquals(
            $GLOBALS['conn_error'],
            'Access denied!'
        );
    }

    public function testAuthFailsActivity()
    {
        $this->object = $this->getMockBuilder('PhpMyAdmin\Plugins\Auth\AuthenticationCookie')
            ->disableOriginalConstructor()
            ->setMethods(array('auth'))
            ->getMock();

        $GLOBALS['server'] = 2;
        $_COOKIE['pmaAuth-2'] = 'pass';

        $GLOBALS['allowDeny_forbidden'] = '';
        $GLOBALS['no_activity'] = '1';
        $GLOBALS['cfg']['LoginCookieValidity'] = 10;

        $this->mockResponse(
            array('Cache-Control: no-store, no-cache, must-revalidate'),
            array('Pragma: no-cache')
        );
        $this->object->authFails();

        $this->assertEquals(
            $GLOBALS['conn_error'],
            'No activity within 10 seconds; please log in again.'
        );
    }

    public function testAuthFailsDBI()
    {
        $this->object = $this->getMockBuilder('PhpMyAdmin\Plugins\Auth\AuthenticationCookie')
            ->disableOriginalConstructor()
            ->setMethods(array('auth'))
            ->getMock();

        $GLOBALS['server'] = 2;
        $_COOKIE['pmaAuth-2'] = 'pass';

        $dbi = $this->getMockBuilder('PhpMyAdmin\DatabaseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $dbi->expects($this->at(0))
            ->method('getError')
            ->will($this->returnValue(false));

        $GLOBALS['dbi'] = $dbi;
        $GLOBALS['no_activity'] = '';
        $GLOBALS['errno'] = 42;

        $this->mockResponse(
            array('Cache-Control: no-store, no-cache, must-revalidate'),
            array('Pragma: no-cache')
        );
        $this->object->authFails();

        $this->assertEquals(
            $GLOBALS['conn_error'],
            '#42 Cannot log in to the MySQL server'
        );
    }

    public function testAuthFailsErrno()
    {
        $this->object = $this->getMockBuilder('PhpMyAdmin\Plugins\Auth\AuthenticationCookie')
            ->disableOriginalConstructor()
            ->setMethods(array('auth'))
            ->getMock();

        $dbi = $this->getMockBuilder('PhpMyAdmin\DatabaseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $dbi->expects($this->at(0))
            ->method('getError')
            ->will($this->returnValue(false));

        $GLOBALS['dbi'] = $dbi;
        $GLOBALS['server'] = 2;
        $_COOKIE['pmaAuth-2'] = 'pass';

        unset($GLOBALS['errno']);

        $this->mockResponse(
            array('Cache-Control: no-store, no-cache, must-revalidate'),
            array('Pragma: no-cache')
        );
        $this->object->authFails();

        $this->assertEquals(
            $GLOBALS['conn_error'],
            'Cannot log in to the MySQL server'
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::_getEncryptionSecret
     *
     * @return void
     */
    public function testGetEncryptionSecretEmpty()
    {
        $method = new ReflectionMethod(
            'PhpMyAdmin\Plugins\Auth\AuthenticationCookie',
            '_getEncryptionSecret'
        );
        $method->setAccessible(true);

        $GLOBALS['cfg']['blowfish_secret'] = '';
        $_SESSION['encryption_key'] = '';

        $result = $method->invoke($this->object, null);

        $this->assertEquals(
            $result,
            $_SESSION['encryption_key']
        );

        $this->assertEquals(
            32,
            strlen($result)
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::_getEncryptionSecret
     *
     * @return void
     */
    public function testGetEncryptionSecretConfigured()
    {
        $method = new ReflectionMethod(
            'PhpMyAdmin\Plugins\Auth\AuthenticationCookie',
            '_getEncryptionSecret'
        );
        $method->setAccessible(true);

        $GLOBALS['cfg']['blowfish_secret'] = 'notEmpty';

        $result = $method->invoke($this->object, null);

        $this->assertEquals(
            'notEmpty',
            $result
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::cookieEncrypt
     *
     * @return void
     */
    public function testCookieEncrypt()
    {
        $this->object->setIV('testiv09testiv09');
        // works with the openssl extension active or inactive
        $this->assertEquals(
            '{"iv":"dGVzdGl2MDl0ZXN0aXYwOQ==","mac":"347aa45ae1ade00c980f31129ec2defef18b2bfd","payload":"YDEaxOfP9nD9q\/2pC6hjfQ=="}',
            $this->object->cookieEncrypt('data123', 'sec321')
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::cookieEncrypt
     *
     * @return void
     */
    public function testCookieEncryptPHPSecLib()
    {
        $this->object->setUseOpenSSL(false);
        $this->testCookieEncrypt();
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::cookieEncrypt
     *
     * @return void
     */
    public function testCookieEncryptOpenSSL()
    {
        if (! function_exists('openssl_encrypt')) {
            $this->markTestSkipped('openssl not available');
        }
        $this->object->setUseOpenSSL(true);
        $this->testCookieEncrypt();
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::cookieDecrypt
     *
     * @return void
     */
    public function testCookieDecrypt()
    {
        // works with the openssl extension active or inactive
        $this->assertEquals(
            'data123',
            $this->object->cookieDecrypt(
                '{"iv":"dGVzdGl2MDl0ZXN0aXYwOQ==","mac":"347aa45ae1ade00c980f31129ec2defef18b2bfd","payload":"YDEaxOfP9nD9q\/2pC6hjfQ=="}',
                'sec321'
            )
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::cookieDecrypt
     *
     * @return void
     */
    public function testCookieDecryptPHPSecLib()
    {
        $this->object->setUseOpenSSL(false);
        $this->testCookieDecrypt();
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::cookieDecrypt
     *
     * @return void
     */
    public function testCookieDecryptOpenSSL()
    {
        if (! function_exists('openssl_encrypt')) {
            $this->markTestSkipped('openssl not available');
        }
        $this->object->setUseOpenSSL(true);
        $this->testCookieDecrypt();
    }

    /**
     * Test for PhpMyAdmin\Plugins\Auth\AuthenticationConfig::cookieDecrypt
     *
     * @return void
     */
    public function testCookieDecryptInvalid()
    {
        // works with the openssl extension active or inactive
        $this->assertEquals(
            false,
            $this->object->cookieDecrypt(
                '{"iv":0,"mac":0,"payload":0}',
                'sec321'
            )
        );
    }

    /**
     * Test for secret splitting using getAESSecret
     *
     * @return void
     *
     * @dataProvider secretsProvider
     */
    public function testMACSecretSplit($secret, $mac, $aes)
    {
        $this->assertEquals(
            $mac,
            $this->object->getMACSecret($secret)
        );
    }

    /**
     * Test for secret splitting using getMACSecret and getAESSecret
     *
     * @return void
     *
     * @dataProvider secretsProvider
     */
    public function testAESSecretSplit($secret, $mac, $aes)
    {
        $this->assertEquals(
            $aes,
            $this->object->getAESSecret($secret)
        );
    }

    public function testPasswordChange()
    {
        $newPassword = 'PMAPASSWD2';
        $GLOBALS['cfg']['AllowArbitraryServer'] = true;
        $GLOBALS['pma_auth_server'] = 'b 2';
        $_SESSION['encryption_key'] = '';
        $this->object->setIV('testiv09testiv09');

        $this->object->handlePasswordChange($newPassword);

        $payload = array(
            'password' => $newPassword,
            'server' => 'b 2'
        );
        $method = new ReflectionMethod(
            'PhpMyAdmin\Plugins\Auth\AuthenticationCookie',
            '_getSessionEncryptionSecret'
        );
        $method->setAccessible(true);

        $encryptedCookie = $this->object->cookieEncrypt(
            json_encode($payload),
            $method->invoke($this->object, null)
        );
        $this->assertEquals(
            $_COOKIE['pmaAuth-' . $GLOBALS['server']],
            $encryptedCookie
        );
    }
    /**
     * Data provider for secrets splitting.
     *
     * @return array
     */
    public function secretsProvider()
    {
        return array(
            // Optimal case
            array(
                '1234567890123456abcdefghijklmnop',
                '1234567890123456',
                'abcdefghijklmnop',
            ),
            // Overlapping secret
            array(
                '12345678901234567',
                '1234567890123456',
                '2345678901234567',
            ),
            // Short secret
            array(
                '1234567890123456',
                '1234567890123451',
                '2345678901234562',
            ),
            // Really short secret
            array(
                '12',
                '1111111111111111',
                '2222222222222222',
            ),
            // Too short secret
            array(
                '1',
                '1111111111111111',
                '1111111111111111',
            ),
        );
    }
}
