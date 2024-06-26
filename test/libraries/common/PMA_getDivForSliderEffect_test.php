<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 ** Test for PhpMyAdmin\Util::getDivForSliderEffect from Util.php
 *
 * @package PhpMyAdmin-test
 * @group common.lib-tests
 */

/*
 * Include to test.
 */


/**
 ** Test for PhpMyAdmin\Util::getDivForSliderEffect from Util.php
 *
 * @package PhpMyAdmin-test
 * @group common.lib-tests
 */
class PMA_GetDivForSliderEffectTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test for getDivForSliderEffect
     *
     * @return void
     */
    public function testGetDivForSliderEffectTest()
    {
        global $cfg;
        $cfg['InitialSlidersState'] = 'undefined';

        $id = "test_id";
        $message = "test_message";

        $this->assertXmlStringEqualsXmlString(
            "<root>" . PhpMyAdmin\Util::getDivForSliderEffect($id, $message) . "</div></root>",
            "<root><div id=\"$id\" class=\"pma_auto_slider\"\ntitle=\"" . htmlspecialchars($message) . "\" >\n</div></root>"
        );
    }

    /**
     * Test for getDivForSliderEffect
     *
     * @return void
     */
    public function testGetDivForSliderEffectTestClosed()
    {
        global $cfg;
        $cfg['InitialSlidersState'] = 'closed';

        $id = "test_id";
        $message = "test_message";

        $this->assertXmlStringEqualsXmlString(
            "<root>" . PhpMyAdmin\Util::getDivForSliderEffect($id, $message) . "</div></root>",
            "<root><div id=\"$id\" style=\"display: none; overflow:auto;\" class=\"pma_auto_slider\"\ntitle=\"" . htmlspecialchars($message) . "\" >\n</div></root>"
        );

    }

    /**
     * Test for getDivForSliderEffect
     *
     * @return void
     */
    public function testGetDivForSliderEffectTestDisabled()
    {
        global $cfg;
        $cfg['InitialSlidersState'] = 'disabled';

        $id = "test_id";
        $message = "test_message";

        $this->assertXmlStringEqualsXmlString(
            "<root>" . PhpMyAdmin\Util::getDivForSliderEffect($id, $message) . "</div></root>",
            "<root><div id=\"$id\">\n</div></root>"
        );
    }
}
