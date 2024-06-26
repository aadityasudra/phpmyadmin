<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * tests for PhpMyAdmin\Plugins\Export\ExportMediawiki class
 *
 * @package PhpMyAdmin-test
 */

namespace PhpMyAdmin\Tests\Plugins\Export;

use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Plugins\Export\ExportMediawiki;
use ReflectionMethod;
use ReflectionProperty;

require_once 'libraries/config.default.php';

/**
 * tests for PhpMyAdmin\Plugins\Export\ExportMediawiki class
 *
 * @package PhpMyAdmin-test
 * @group medium
 */
class ExportMediawikiTest extends \PMATestCase
{
    protected $object;

    /**
     * Configures global environment.
     *
     * @return void
     */
    public function setup()
    {
        $GLOBALS['server'] = 0;
        $GLOBALS['output_kanji_conversion'] = false;
        $GLOBALS['output_charset_conversion'] = false;
        $GLOBALS['buffer_needed'] = false;
        $GLOBALS['asfile'] = true;
        $GLOBALS['save_on_server'] = false;
        $this->object = new ExportMediawiki();
    }

    /**
     * tearDown for test cases
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->object);
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportMediawiki::setProperties
     *
     * @return void
     */
    public function testSetProperties()
    {
        $method = new ReflectionMethod('PhpMyAdmin\Plugins\Export\ExportMediawiki', 'setProperties');
        $method->setAccessible(true);
        $method->invoke($this->object, null);

        $attrProperties = new ReflectionProperty('PhpMyAdmin\Plugins\Export\ExportMediawiki', 'properties');
        $attrProperties->setAccessible(true);
        $properties = $attrProperties->getValue($this->object);

        $this->assertInstanceOf(
            'PhpMyAdmin\Properties\Plugins\ExportPluginProperties',
            $properties
        );

        $this->assertEquals(
            'MediaWiki Table',
            $properties->getText()
        );

        $this->assertEquals(
            'mediawiki',
            $properties->getExtension()
        );

        $this->assertEquals(
            'text/plain',
            $properties->getMimeType()
        );

        $this->assertEquals(
            'Options',
            $properties->getOptionsText()
        );

        $options = $properties->getOptions();

        $this->assertInstanceOf(
            'PhpMyAdmin\Properties\Options\Groups\OptionsPropertyRootGroup',
            $options
        );

        $this->assertEquals(
            'Format Specific Options',
            $options->getName()
        );

        $generalOptionsArray = $options->getProperties();
        $generalOptions = $generalOptionsArray[0];

        $this->assertInstanceOf(
            'PhpMyAdmin\Properties\Options\Groups\OptionsPropertyMainGroup',
            $generalOptions
        );

        $this->assertEquals(
            'general_opts',
            $generalOptions->getName()
        );

        $this->assertEquals(
            'Dump table',
            $generalOptions->getText()
        );

        $generalProperties = $generalOptions->getProperties();

        $property = array_shift($generalProperties);

        $this->assertInstanceOf(
            'PhpMyAdmin\Properties\Options\Groups\OptionsPropertySubgroup',
            $property
        );

        $this->assertEquals(
            'dump_table',
            $property->getName()
        );

        $this->assertEquals(
            'Dump table',
            $property->getText()
        );

        $sgHeader = $property->getSubGroupHeader();

        $this->assertInstanceOf(
            'PhpMyAdmin\Properties\Options\Items\RadioPropertyItem',
            $sgHeader
        );

        $this->assertEquals(
            'structure_or_data',
            $sgHeader->getName()
        );

        $this->assertEquals(
            array(
                'structure' => __('structure'),
                'data' => __('data'),
                'structure_and_data' => __('structure and data')
            ),
            $sgHeader->getValues()
        );

        $property = array_shift($generalProperties);

        $this->assertInstanceOf(
            'PhpMyAdmin\Properties\Options\Items\BoolPropertyItem',
            $property
        );

        $this->assertEquals(
            'caption',
            $property->getName()
        );

        $this->assertEquals(
            'Export table names',
            $property->getText()
        );

        $property = array_shift($generalProperties);

        $this->assertInstanceOf(
            'PhpMyAdmin\Properties\Options\Items\BoolPropertyItem',
            $property
        );

        $this->assertEquals(
            'headers',
            $property->getName()
        );

        $this->assertEquals(
            'Export table headers',
            $property->getText()
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportMediawiki::exportHeader
     *
     * @return void
     */
    public function testExportHeader()
    {
        $this->assertTrue(
            $this->object->exportHeader()
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportMediawiki::exportFooter
     *
     * @return void
     */
    public function testExportFooter()
    {
        $this->assertTrue(
            $this->object->exportFooter()
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportMediawiki::exportDBHeader
     *
     * @return void
     */
    public function testExportDBHeader()
    {
        $this->assertTrue(
            $this->object->exportDBHeader('testDB')
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportMediawiki::exportDBFooter
     *
     * @return void
     */
    public function testExportDBFooter()
    {
        $this->assertTrue(
            $this->object->exportDBFooter('testDB')
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportMediawiki::exportDBCreate
     *
     * @return void
     */
    public function testExportDBCreate()
    {
        $this->assertTrue(
            $this->object->exportDBCreate('testDB', 'database')
        );
    }

    /**
     * Test for ExportMediaWiki::exportStructure
     *
     * @return void
     */
    public function testExportStructure()
    {
        $dbi = $this->getMockBuilder('PhpMyAdmin\DatabaseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $columns = array(
            array(
                'Null' => 'Yes',
                'Field' => 'name1',
                'Key' => 'PRI',
                'Type' => 'set(abc)enum123',
                'Default' => '',
                'Extra' => ''
            ),
            array(
                'Null' => 'NO',
                'Field' => 'fields',
                'Key' => 'COMP',
                'Type' => '',
                'Default' => 'def',
                'Extra' => 'ext'
            )
        );

        $dbi->expects($this->at(0))
            ->method('getColumns')
            ->with('db', 'table')
            ->will($this->returnValue($columns));

        $GLOBALS['dbi'] = $dbi;
        $GLOBALS['mediawiki_caption'] = true;
        $GLOBALS['mediawiki_headers'] = true;

        ob_start();
        $this->assertTrue(
            $this->object->exportStructure(
                'db',
                'table',
                "\n",
                "example.com",
                "create_table",
                "test"
            )
        );
        $result = ob_get_clean();

        $this->assertEquals(
            "\n<!--\n" .
            "Table structure for `table`\n" .
            "-->\n" .
            "\n" .
            "{| class=\"wikitable\" style=\"text-align:center;\"\n" .
            "|+'''table'''\n" .
            "|- style=\"background:#ffdead;\"\n" .
            "! style=\"background:#ffffff\" | \n" .
            " | name1\n" .
            " | fields\n" .
            "|-\n" .
            "! Type\n" .
            " | set(abc)enum123\n" .
            " | \n" .
            "|-\n" .
            "! Null\n" .
            " | Yes\n" .
            " | NO\n" .
            "|-\n" .
            "! Default\n" .
            " | \n" .
            " | def\n" .
            "|-\n" .
            "! Extra\n" .
            " | \n" .
            " | ext\n" .
            "|}\n\n",
            $result
        );
    }
    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportMediawiki::exportData
     *
     * @return void
     */
    public function testExportData()
    {
        $dbi = $this->getMockBuilder('PhpMyAdmin\DatabaseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $dbi->expects($this->once())
            ->method('getColumnNames')
            ->with('db', 'table')
            ->will($this->returnValue(array('name1', 'fields')));

        $dbi->expects($this->once())
            ->method('query')
            ->with('SELECT', null, DatabaseInterface::QUERY_UNBUFFERED)
            ->will($this->returnValue(true));

        $dbi->expects($this->once())
            ->method('numFields')
            ->with(true)
            ->will($this->returnValue(2));

        $dbi->expects($this->at(3))
            ->method('fetchRow')
            ->with(true)
            ->will($this->returnValue(array('r1', 'r2')));

        $dbi->expects($this->at(4))
            ->method('fetchRow')
            ->with(true)
            ->will($this->returnValue(array('r3', '')));

        $dbi->expects($this->at(4))
            ->method('fetchRow')
            ->with(true)
            ->will($this->returnValue(null));

        $GLOBALS['dbi'] = $dbi;
        $GLOBALS['mediawiki_caption'] = true;
        $GLOBALS['mediawiki_headers'] = true;

        ob_start();
        $this->assertTrue(
            $this->object->exportData(
                'db',
                'table',
                "\n",
                "example.com",
                "SELECT"
            )
        );
        $result = ob_get_clean();

        $this->assertEquals(
            "\n<!--\n" .
            "Table data for `table`\n" .
            "-->\n" .
            "\n" .
            "{| class=\"wikitable sortable\" style=\"text-align:" .
            "center;\"\n" .
            "|+'''table'''\n" .
            "|-\n" .
            " ! name1\n" .
            " ! fields\n" .
            "|-\n" .
            " | r1\n" .
            " | r2\n" .
            "|-\n" .
            " | r3\n" .
            " | \n" .
            "|}\n\n",
            $result
        );
    }
}
