<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 ** Test for PhpMyAdmin\Util::showPHPDocu from Util.php
 *
 * @package PhpMyAdmin-test
 * @group common.lib-tests
 */

/*
 * Include to test.
 */
use PhpMyAdmin\Theme;

/**
 ** Test for PhpMyAdmin\Util::showPHPDocu from Util.php
 *
 * @package PhpMyAdmin-test
 * @group common.lib-tests
 */
class PMA_ShowPHPDocu_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Set up
     *
     * @return void
     */
    public function setup()
    {
        $GLOBALS['server'] = 99;
        $GLOBALS['cfg']['ServerDefault'] = 0;
    }

    /**
     * Test for showPHPDocu
     *
     * @return void
     */
    public function testShowPHPDocu()
    {
        $target = "docu";
        $lang = _pgettext('PHP documentation language', 'en');
        $expected = '<a href="./url.php?url=https%3A%2F%2Fsecure.php.net%2Fmanual%2F' . $lang
            . '%2F' . $target . '" target="documentation">'
            . '<img src="themes/dot.gif" title="' . __('Documentation') . '" alt="'
            . __('Documentation') . '" class="icon ic_b_help" /></a>';

        $this->assertEquals(
            $expected,
            PhpMyAdmin\Util::showPHPDocu($target)
        );
    }
}
