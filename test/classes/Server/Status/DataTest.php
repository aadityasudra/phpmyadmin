<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for PhpMyAdmin\Server\Status\Data class
 *
 * @package PhpMyAdmin-test
 */

namespace PhpMyAdmin\Tests\Server\Status;

use PhpMyAdmin\Core;
use PhpMyAdmin\Server\Status\Data;

/**
 * Test for PhpMyAdmin\Server\Status\Data class
 *
 * @package PhpMyAdmin-test
 */
class DataTest extends \PMATestCase
{
    /**
     * @access protected
     */
    protected $object;

    /**
     * Configures global environment.
     *
     * @return void
     */
    public function setup()
    {
        $GLOBALS['PMA_PHP_SELF'] = Core::getenv('PHP_SELF');
        $GLOBALS['cfg']['Server']['host'] = "::1";
        $GLOBALS['replication_info']['master']['status'] = true;
        $GLOBALS['replication_info']['slave']['status'] = true;
        $GLOBALS['replication_types'] = array();

        //Mock DBI
        $dbi = $this->getMockBuilder('PhpMyAdmin\DatabaseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        //this data is needed when PhpMyAdmin\Server\Status\Data constructs
        $server_status = array(
            "Aborted_clients" => "0",
            "Aborted_connects" => "0",
            "Com_delete_multi" => "0",
            "Com_create_function" => "0",
            "Com_empty_query" => 3,
            "Key_blocks_used" => 2,
            "Key_writes" => true,
            "Key_reads" => true,
            "Key_write_requests" => 5,
            "Key_read_requests" => 1,
            "Threads_created" => true,
            "Connections" => 2,
        );

        $server_variables = array(
            "auto_increment_increment" => "1",
            "auto_increment_offset" => "1",
            "automatic_sp_privileges" => "ON",
            "back_log" => "50",
            "big_tables" => "OFF",
            "key_buffer_size" => 10,
        );

        $fetchResult = array(
            array(
                "SHOW GLOBAL STATUS",
                0,
                1,
                null,
                0,
                $server_status
            ),
            array(
                "SHOW GLOBAL VARIABLES",
                0,
                1,
                null,
                0,
                $server_variables
            ),
            array(
                "SELECT concat('Com_', variable_name), variable_value "
                    . "FROM data_dictionary.GLOBAL_STATEMENTS",
                0,
                1,
                null,
                0,
                $server_status
            ),
        );

        $dbi->expects($this->any())->method('fetchResult')
            ->will($this->returnValueMap($fetchResult));

        $GLOBALS['dbi'] = $dbi;

        $this->object = new Data();
    }

    /**
     * tests getMenuHtml()
     *
     * @return void
     */
    public function testGetMenuHtml()
    {
        $html = $this->object->getMenuHtml();

        $this->assertContains('Server', $html);
        $this->assertContains('server_status.php', $html);

        $this->assertContains('Processes', $html);
        $this->assertContains('server_status_processes.php', $html);

        $this->assertContains('Query statistics', $html);
        $this->assertContains('server_status_queries.php', $html);

        $this->assertContains('All status variables', $html);
        $this->assertContains('server_status_variables.php', $html);

        $this->assertContains('Monitor', $html);
        $this->assertContains('server_status_monitor.php', $html);

        $this->assertContains('Advisor', $html);
        $this->assertContains('server_status_advisor.php', $html);
    }
}
