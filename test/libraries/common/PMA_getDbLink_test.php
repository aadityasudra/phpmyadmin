<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for PMA_getDbLink_test from Util.php
 *
 * @package PhpMyAdmin-test
 * @group common.lib-tests
 */

/**
 * Test for PMA_getDbLink_test from Util.php
 *
 * @package PhpMyAdmin-test
 * @group common.lib-tests
 */
class PMA_GetDbLink_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        global $cfg;
        include 'libraries/config.default.php';
        $GLOBALS['server'] = 99;
    }

    /**
     * Test for getDbLink
     *
     * @return void
     *
     * @group medium
     */
    public function testGetDbLinkEmpty()
    {
        $GLOBALS['db'] = null;
        $this->assertEmpty(PhpMyAdmin\Util::getDbLink());
    }

    /**
     * Test for getDbLink
     *
     * @return void
     *
     * @group medium
     */
    public function testGetDbLinkNull()
    {
        global $cfg;
        $GLOBALS['db'] = 'test_db';
        $database = $GLOBALS['db'];
        $this->assertEquals(
            '<a href="'
            . PhpMyAdmin\Util::getScriptNameForOption(
                $GLOBALS['cfg']['DefaultTabDatabase'],
                'database'
            )
            . '?db=' . $database
            . '&amp;server=99&amp;lang=en" '
            . 'title="Jump to database “'
            . htmlspecialchars($database) . '”.">'
            . htmlspecialchars($database) . '</a>',
            PhpMyAdmin\Util::getDbLink()
        );
    }

    /**
     * Test for getDbLink
     *
     * @return void
     */
    public function testGetDbLink()
    {
        global $cfg;
        $database = 'test_database';
        $this->assertEquals(
            '<a href="' . PhpMyAdmin\Util::getScriptNameForOption(
                $GLOBALS['cfg']['DefaultTabDatabase'],
                'database'
            )
            . '?db=' . $database
            . '&amp;server=99&amp;lang=en" title="Jump to database “'
            . htmlspecialchars($database) . '”.">'
            . htmlspecialchars($database) . '</a>',
            PhpMyAdmin\Util::getDbLink($database)
        );
    }

    /**
     * Test for getDbLink
     *
     * @return void
     */
    public function testGetDbLinkWithSpecialChars()
    {
        global $cfg;
        $database = 'test&data\'base';
        $this->assertEquals(
            '<a href="'
            . PhpMyAdmin\Util::getScriptNameForOption(
                $GLOBALS['cfg']['DefaultTabDatabase'],
                'database'
            )
            . '?db='
            . htmlspecialchars(urlencode($database))
            . '&amp;server=99&amp;lang=en" title="Jump to database “'
            . htmlspecialchars($database) . '”.">'
            . htmlspecialchars($database) . '</a>',
            PhpMyAdmin\Util::getDbLink($database)
        );
    }
}
