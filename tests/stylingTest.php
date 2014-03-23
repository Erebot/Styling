<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

class   StylingTest
extends PHPUnit_Framework_TestCase
{
    protected $_translator = NULL;

    public function setUp()
    {
        parent::setUp();
        $this->_translator = $this->getMock('\\Erebot\\I18N\\I18NInterface', array(), array('', ''), '', FALSE, FALSE);
        $this->_translator
            ->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue('en-US'));
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers \Erebot\Styling\Main
     */
    public function testArrayWithOnlyOneElement()
    {
        $source = '<for from="names" item="name"><var name="name"/></for>';
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $vars   = array('names' => array('Clicky'));
        $result = addcslashes($tpl->render($source, $vars), "\000..\037");
        $expected = "Clicky";
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers \Erebot\Styling\Main
     */
    public function testBeatlesTest()
    {
        $source =   'The Beatles: <for from="Beatles" item="Beatle">'.
                    '<u><var name="Beatle"/></u></for>.';

        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $vars   = array('Beatles' => array('George', 'John', 'Paul', 'Ringo'));
        $result = $tpl->render($source, $vars);
        $result = addcslashes($tpl->render($source, $vars), "\000..\037");
        $expected   =   "The Beatles: \\037George\\037, \\037John\\037, ".
                        "\\037Paul\\037 & \\037Ringo\\037.";
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers \Erebot\Styling\Main
     */
    public function testScoreTest()
    {
        $source =   '<b>Scores</b>: <for item="score" key="nick" '.
                    'from="scores" separator=", " last_separator=" &amp; ">'.
                    '<b><u><color fg="green"><var name="nick"/></color></u>: '.
                    '<var name="score"/></b></for>';

        $tpl    =   new \Erebot\Styling\Main($this->_translator);
        $scores =   array(
                        'Clicky' => 42,
                        'Looksup' => 23,
                        'MiSsInGnO' => 16
                    );
        $result = $tpl->render($source, array('scores' => $scores));
        $result = addcslashes($result, "\000..\037");
        $expected   =   "\\002Scores\\002: \\002\\037\\00303Clicky\\037: ".
                        "42\\002, \\002\\037\\00303Looksup\\037: 23\\002 & ".
                        "\\002\\037\\00303MiSsInGnO\\037: 16\\002";
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests whether a \<color\> tag without any "fg"
     * or "bg" attribute is correctly marked as invalid.
     *
     * @expectedException   \InvalidArgumentException
     * @covers              \Erebot\Styling\Main
     */
    public function testColorMissingAttributes()
    {
        $path   = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $msg    =  'The "fg" attribute or the "bg" attribute or both must ' .
                    'be supplied when using the <color> tag.';

        if (DIRECTORY_SEPARATOR == "/")
            $xmlPath = $path;
        else {
            // Under Windows, libxml2 adds a "file:///" prefix
            // and puts the volume's letter in lowercase.
            $pos = strpos($path, ':');
            $xmlPath = "file:///". strtolower(substr($path, 0, $pos)) .
                str_replace(DIRECTORY_SEPARATOR, '/', substr($path, $pos));
        }

        $this->setExpectedLogs(<<<LOGS
ERROR:Array
(
    [0] => LibXMLError Object
        (
            [level] => 2
            [code] => 0
            [column] => 0
            [message] => $msg
            [file] => $xmlPath
            [line] => 0
            [path] => /msg[1]/color[1]
        )
)
LOGS
        );

        $tpl = new \Erebot\Styling\Main($this->_translator);
        $tpl->render('<color>foo</color>');
    }

    /**
     * @covers \Erebot\Styling\Main
     */
    public function testPlural()
    {
        /* We use special characters in the sentence {, }, ' and #
         * to test how the styling API deals with ICU's meta-characters. */
        $source =   "<plural var='foo'><case form='one'>there's <var ".
                    "name='foo'/> file</case><case form='other'>there ".
                    "are #{''<var name='foo'/>''}# files</case></plural>";
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $result = $tpl->render($source, array('foo' => 0));
        $this->assertEquals("there are #{''0''}# files", $result);
        $result = $tpl->render($source, array('foo' => 1));
        $this->assertEquals("there's 1 file", $result);
        $result = $tpl->render($source, array('foo' => 42));
        $this->assertEquals("there are #{''42''}# files", $result);
    }

    public function testInteger()
    {
        $source = "<var name='foo'/>";
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $result = $tpl->render($source, array('foo' => 12345));
        $this->assertEquals('12345', $result);

        $result = $tpl->render(
            $source,
            array('foo' => new \Erebot\Styling\Variables\Integer(12345))
        );
        $this->assertEquals('12345', $result);
    }

    public function testFloat()
    {
        $source = "<var name='foo'/>";
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $result = $tpl->render($source, array('foo' => 12345.67891));
        $this->assertEquals('12,345.67891', $result);

        $result = $tpl->render(
            $source,
            array('foo' => new \Erebot\Styling\Variables\Float(12345.67891))
        );
        $this->assertEquals('12,345.67891', $result);
    }

    public function testCurrency()
    {
        $source = "<var name='foo'/>";
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $result = $tpl->render(
            $source,
            array('foo' => new \Erebot\Styling\Variables\Currency(12345.67891, 'EUR'))
        );
        // Monetary values are rounded.
        $this->assertEquals('€12,345.68', $result);
    }

    public function testDateTime()
    {
        $source = "<var name='foo'/>";
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        // 28 Nov 1985, 14:10:00 +0100.
        $date = 502031400;
        $formatter  = new \Erebot\Styling\Variables\DateTime(
            $date,
            IntlDateFormatter::FULL,
            IntlDateFormatter::LONG,
            'Europe/Paris'
        );
        $result     = $tpl->render($source, array('foo' => $formatter));
        $expected   = 'Thursday, November 28, 1985 2:10:00 PM GMT+01:00';
        $this->assertEquals($expected, $result);
    }

    public function testDuration()
    {
        $source = "<var name='foo'/>";
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $values = array(
            0       => "0 seconds",
            1       => "1 second",
            2       => "2 seconds",
            694861  => "1 week, 1 day, 1 hour, 1 minute, 1 second",
            1389722 => "2 weeks, 2 days, 2 hours, 2 minutes, 2 seconds",
        );

        foreach ($values as $duration => $spellout) {
            $result = $tpl->render(
                $source,
                array('foo' => new \Erebot\Styling\Variables\Duration($duration))
            );
            $this->assertEquals($spellout, $result);
        }
    }

    public function testCount()
    {
        $source = "<var name='#foo'/>";
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $values = range(1, 10);
        $result = $tpl->render($source, array('foo' => $values));
        $this->assertEquals('10', $result);
    }

    public function testAddition1()
    {
        $source = "<var name='41+1'/>";
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $result = $tpl->render($source, array());
        $this->assertEquals('42', $result);
    }

    public function testAddition2()
    {
        $source = "<var name='#foo+#bar'/>";
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $foo    = range(1, 10);
        $bar    = range(1, 4);
        $result = $tpl->render($source, array('foo' => $foo, 'bar' => $bar));
        $this->assertEquals('14', $result);
    }

    public function testSubtraction1()
    {
        $source = "<var name='43-1'/>";
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $result = $tpl->render($source, array());
        $this->assertEquals('42', $result);
    }

    public function testSubtraction2()
    {
        $source = "<var name='#foo-#bar'/>";
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $foo    = range(1, 10);
        $bar    = range(1, 4);
        $result = $tpl->render($source, array('foo' => $foo, 'bar' => $bar));
        $this->assertEquals('6', $result);
    }

    public function testPluralWithComputation()
    {
        $source = 'There <plural var="boys + girls">'.
                    '<case form="one">is one person</case>'.
                    '<case form="other">are <var name="boys + girls"/> '.
                        'persons</case>'.
                    '</plural> in this room.';
        $tpl    = new \Erebot\Styling\Main($this->_translator);

        $result = $tpl->render($source, array('boys' => 2, 'girls' => 0));
        $this->assertEquals('There are 2 persons in this room.', $result);

        $result = $tpl->render($source, array('boys' => 0, 'girls' => 0));
        $this->assertEquals('There are 0 persons in this room.', $result);

        $result = $tpl->render($source, array('boys' => 0, 'girls' => 1));
        $this->assertEquals('There is one person in this room.', $result);
    }

    public function testComplexComputation()
    {
        // This "variable" uses both addition on arrays
        // and counting on a virtual variable.
        $source = "<var name='#(foo+bar)'/>";
        $tpl    = new \Erebot\Styling\Main($this->_translator);
        $foo    = range(1, 10);
        $bar    = range(1, 4);
        $result = $tpl->render($source, array('foo' => $foo, 'bar' => $bar));
        $this->assertEquals('14', $result);
    }

    private $expectedLogs = NULL;
    private $logStream = NULL;

    public function getExpectedLogs()
    {
        return $this->expectedLogs;
    }

    public function addExpectedLog($logLine)
    {
        if (!is_string($logLine)) {
            throw new Exception();
        }

        if ($this->expectedLogs === NULL) {
            $this->expectedLogs = array();
        }

        $this->expectedLogs[] = $logLine;
    }

    public function setExpectedLogs($logLines)
    {
        if (is_string($logLines)) {
            $logLines = trim($logLines);
            $logLines = preg_split('/\\r?\\n/', $logLines);
        }

        if ($logLines === NULL) {
            return;
        }

        if (!is_array($logLines)) {
            throw new Exception();
        }

        $this->expectedLogs = array();
        foreach ($logLines as $logLine) {
            $this->addExpectedLog($logLine);
        }
    }

    protected function setExpectedLogsFromAnnotations()
    {
        try {
            $expectedLogs = self::getExpectedLogs2(
                get_class($this),
                $this->getName()
            );

            $this->setExpectedLogs($expectedLogs);
        }

        catch (ReflectionException $e) {
        }
    }

    protected function runTest()
    {
        $result = parent::runTest();

        if ($this->logStream !== NULL) {
            $this->addToAssertionCount(1);
            fseek($this->logStream, 0);
            $actualLogs = stream_get_contents($this->logStream);
            fclose($this->logStream);
            $actualLogs = array_map('rtrim', explode("\n", $actualLogs));
            $actualLogs = array_values(array_filter($actualLogs, 'strlen'));

            if ($this->expectedLogs !== NULL) {
                if (count($this->expectedLogs)) {
                    $this->assertEquals($this->expectedLogs, $actualLogs);
                }

                else if (count($actualLogs)) {
                    $this->fail(
                        "No logs expected, but we received:\n" .
                        var_export($actualLogs, TRUE)
                    );
                }
            }
        }

        return $result;
    }

    public function run(PHPUnit_Framework_TestResult $result = NULL)
    {
        $this->setExpectedLogsFromAnnotations();

        $this->logStream = NULL;
        if (class_exists('Plop', TRUE)) {
            $logging            = Plop::getInstance();
            $this->logStream    = fopen('php://temp', 'a+');

            $handlers   = new Plop_HandlersCollection();
            $handler    = new Plop_Handler_Stream($this->logStream);
            $handler->setFormatter(
                new Plop_Formatter('%(levelname)s:%(message)s')
            );
            $handlers[] = $handler;
            $logging->getLogger()->setHandlers($handlers);
        }

        return parent::run($result);
    }

    public static function getExpectedLogs2($className, $methodName)
    {
        $reflector  = new ReflectionMethod($className, $methodName);
        $docComment = $reflector->getDocComment();
        $logLines   = array();

        if (strpos($docComment, '@noExpectedLogs') !== FALSE) {
            return array();
        }

        if ($count = preg_match_all('/@expectedLog\\s+(?P<line>.+)\\s*$/m', $docComment, $matches)) {
            for ($i = 0; $i < $count; $i++) {
                $logLines[] = $matches['line'][$i];
            }
        }

        if (!count($logLines)) {
            return NULL;
        }

        return $logLines;
    }
}
