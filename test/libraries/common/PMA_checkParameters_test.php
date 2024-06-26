<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 ** Test for PhpMyAdmin\Util::checkParameters from Util.php
 *
 * @package PhpMyAdmin-test
 * @group common.lib-tests
 */

/*
 * Include to test.
 */
use PhpMyAdmin\Core;
use PhpMyAdmin\Theme;

/**
 ** Test for PhpMyAdmin\Util::checkParameters from Util.php
 *
 * @package PhpMyAdmin-test
 * @group common.lib-tests
 */
class PMA_CheckParameters_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Set up
     *
     * @return void
     */
    public function setup()
    {
        $GLOBALS['PMA_Config'] = new PhpMyAdmin\Config();
        $GLOBALS['cfg'] = array('ServerDefault' => 1);
        $GLOBALS['text_dir'] = 'ltr';
    }

    /**
     * Test for checkParameters
     *
     * @return void
     */
    public function testCheckParameterMissing()
    {
        $GLOBALS['PMA_PHP_SELF'] = Core::getenv('PHP_SELF');
        $GLOBALS['pmaThemePath'] = $GLOBALS['PMA_Theme']->getPath();

        $this->expectOutputRegex("/Missing parameter: field/");

        PhpMyAdmin\Util::checkParameters(
            array('db', 'table', 'field')
        );
    }

    /**
     * Test for checkParameters
     *
     * @return void
     */
    public function testCheckParameter()
    {
        $GLOBALS['PMA_PHP_SELF'] = Core::getenv('PHP_SELF');
        $GLOBALS['pmaThemePath'] = $GLOBALS['PMA_Theme']->getPath();
        $GLOBALS['db'] = "dbDatabase";
        $GLOBALS['table'] = "tblTable";
        $GLOBALS['field'] = "test_field";
        $GLOBALS['sql_query'] = "SELECT * FROM tblTable;";

        $this->expectOutputString("");
        PhpMyAdmin\Util::checkParameters(
            array('db', 'table', 'field', 'sql_query')
        );
    }
}
