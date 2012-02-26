<?php
abstract class gosTest_Framework_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * setup each test
     * @param none
     * @return none
     */
    public final function setup()
    {
        set_time_limit(0);
        set_error_handler(array($this, 'phpErrorHandler'));

        $this->setupExtension();
    }

    /**
     * tear down each test
     * @param none
     * @return none
     */
    public final function teardown()
    {
        // Clean up any database fixtures every time around
        gosTest_Fixture_Controller::reset();

        $this->teardownExtension();

        restore_error_handler();
    }

    abstract public function setupExtension();
    abstract public function teardownExtension();

    public final function phpErrorHandler($errno, $errstr, $errfile, $errline, array $errcontext)
    {
        if (error_reporting() == 0 || strpos($errstr, 'Unknown:') !== false)
        {
            return;
        }
        $errorString = '--
PHP Error Encountered:
--
error: '. $errstr .'
on line: '. $errline .'
of file: '. $errfile .'
errno: '. $errno .'
context: '. print_r($errcontext, true);

        $this->fail($errorString);
    }

    /**
     * Deletes all data from a table
     * @param string $dbIdentifier
     * @param string $tableName
     */
    protected final function emptyTable($dbIdentifier, $tableName)
    {
        $dbConn = gosDB_Helper::getDBByName($dbIdentifier);
        $dbConn->execute("TRUNCATE TABLE " . $tableName);
    }

    /**
     * Create a proxy object of a given class that allows testing of protected class methods and interaction with protected variables
     * NOTE: Taken (with slight modification) from http://www.frontalaufprall.com/2007/11/16/testing-protected-methods-in-unit-tests/
     * @param string $superClassName The class from which to create the proxy object
     * @param array|null $constructorParams Parameters for the class' contructor
     * @return object Proxy object that is an extension of the $superClassName class with additional methods
     */
    public static function getProxy($superClassName, array $params = null)
    {
        $proxyClassName = self::getProxyClassName($superClassName);

        if (!empty($params))
        {
            // Create an instance using Reflection, because constructor has parameters
            $class = new ReflectionClass($proxyClassName);
            try
            {
                $instance = $class->newInstanceArgs($params);
            }
            catch (ReflectionException $e)
            {
                if (!is_array($params))
                {
                    $params = array();
                }
                $evalCommand = '$instance = '. $proxyClassName .'::staticConstruct($params);';
                eval($evalCommand);
            }
        }
        else
        {
            $evalCommand = '$instance = '. $proxyClassName .'::staticConstruct(array());';
            eval($evalCommand);
        }
        return $instance;
    }

    /**
     * Create a proxy class that allows testing of protected class methods and interaction with protected variables
     * NOTE: Taken (with slight modification) from http://www.frontalaufprall.com/2007/11/16/testing-protected-methods-in-unit-tests/
     * @param string $superClassName The class from which to create the proxy object
     * @return String The name of the proxy class that has been created
     */
    public static function getProxyClassName($superClassName)
    {
        $proxyClassName = $superClassName .'_Proxy';

        if (!class_exists($proxyClassName, false))
        {
            $class = '
                class '. $proxyClassName .' extends '. $superClassName .'
                {
                    public static function staticConstruct($args=array())
                    {
                        $argString = "";
                        for ($i = 0; $i < count($args); ++$i)
                        {
                            $argString .= \'$args[\'. $i .\'],\';
                        }
                        $argString = substr($argString, 0, -1);
                        eval(\'$obj = new '. $proxyClassName .'(\'. $argString .\');\');
                        return $obj;
                    }

                    public function __call($function, $args)
                    {
                        $function = str_replace("PROTECTED_", "", $function);
                        if (method_exists($this,$function))
                        {
                            return call_user_func_array(array(&$this, $function), $args);
                        }
                        else
                        {
                            throw new gosException_InvalidArgument("Method ". $function ." in class ". get_class($this) ." does not exist", get_defined_vars());
                        }
                    }

                    public function setNonPublicVariable($name, $value)
                    {
                        $this->$name = $value;
                    }

                    public function getNonPublicVariable($name)
                    {
                        return $this->$name;
                    }
                    ';
            // the following block of code makes it so we can call protected
            // static functions using $className->PROTECTED_STATIC_function() in
            // lieu of className::function()
            $reflectionClass = new ReflectionClass($superClassName);
            $methods = $reflectionClass->getMethods();
            foreach ($methods as $method)
            {
                if ($method->isStatic() && $method->isProtected())
                {
                    $params = $method->getParameters();
                    $paramListing = array();
                    foreach ($params as $param)
                    {
                        $paramListing[] = '$'.$param->getName();
                    }
                    $paramListing = implode(', ', $paramListing);
                    $class .= '
                    public static function PROXY_'. $method->getName() .'('. $paramListing . ')
                    {
                        return self::'. $method->getName() .'('. $paramListing .');
                    }

                    ';
                }
            }
            // The following allows getting and setting non-public static variables
            $class .= '
                    /**
                     * @var string $name The name of the variable to set
                     * @var string $value
                     */
                    public function setNonPublicStaticVariable($name, $value)
                    {
                        self::$$name = $value;
                    }

                    /**
                     * @var string $name The name of the variable to get
                     * @return mixed The value of the variable
                     */
                    public function getNonPublicStaticVariable($name)
                    {
                        return self::$$name;
                    }

                }';
            eval($class);
        }

        return $proxyClassName;
    }

    /**
     * Return a mock object for a proxy class
     * NOTE: This is closely based on the getProxy() and PHPUnit_Framework_TestCase::getMock() methods. If they change,
     *   this method should be updated accordingly!
     *
     * @param string $className The name of the class to proxy & mock
     * @param array $constructorParams The parameters for the constructor
     * @param array $functionsToOverride A list of functions that the mock should override. These will return null until
     *     you set them up (using PHPUnit mock syntax) to do otherwise.
     * @return A mock of the proxy class for the className that is passed in
     */
    protected function getMoxy($className, array $constructorParams = array(), array $functionsToOverride = array())
    {
        $arguments = $constructorParams;
        $methods = $functionsToOverride;
        $className = self::getProxyClassName($className);
        $mockClassName = '';
        $callOriginalConstructor = false;
        $callOriginalClone = true;
        $callAutoload = true;

        if (!is_string($className) || !is_string($mockClassName)) {
            throw new InvalidArgumentException;
        }

        if (!is_array($methods) && !is_null($methods)) {
            throw new InvalidArgumentException;
        }

        $mock = PHPUnit_Framework_MockObject_Mock::generate(
          $className,
          $methods,
          $mockClassName,
          $callOriginalConstructor,
          $callOriginalClone,
          $callAutoload
        );

///// START OVERRIDE
        if (empty($arguments))
        {
            $evalCommand = '$mockObject = '. $mock->mockClassName .'::staticConstruct(array());';
            eval($evalCommand);
        }
        else
        {
            // Create an instance using Reflection, because constructor has parameters
            $class = new ReflectionClass($mock->mockClassName);
            try
            {
                $mockObject = $class->newInstanceArgs($arguments);
            }
            catch (ReflectionException $e)
            {
                if (!is_array($arguments))
                {
                    $arguments = array();
                }
                $evalCommand = '$mockObject = '. $mock->mockClassName .'::staticConstruct($arguments);';
                eval($evalCommand);
            }
        }
///// END OVERRIDE
        $this->mockObjects[] = $mockObject;

        return $mockObject;
    }

////////////////////////////// CUSTOM ASSERTIONS //////////////////////////////
    /**
     * The following functions are all for assertions.  It may be useful to
     * wrap these into something more similar to PHPUnit/Framework/Assert.php
     * at some point. -AK 2008/11/21
     */

    /**
     * Fails if $haystack does not contain each item contained within $needle.
     * This function is particularly useful for testing UI output to make sure
     * it contains key strings.
     * @see PHPUnit_Framework_Assert::assertContains()
     * @param array $needle An array of mixed, all of which must be contained
     * within $haystack
     * @param mixed $haystack
     * @return nothing
     */
    public function assertContainsMany(array $needles, $haystack)
    {
        foreach ($needles as $needle)
        {
            $this->assertContains($needle, $haystack);
        }
    }

    /**
     * Fails if $haystack does contain any item contained within $needle.
     * @see self::assertContainsMany()
     */
    public function assertNotContainsMany(array $needles, $haystack)
    {
        foreach ($needles as $needle)
        {
            $this->assertNotContains($needle, $haystack);
        }
    }

    /**
     * Asserts that a number is between two values, inclusive
     *
     * @param numeric $minValue
     * @param numeric $maxValue
     * @param numeric $actual
     */
    public function assertBetweenInclusive($minValue, $maxValue, $actual)
    {
        $this->assertGreaterThanOrEqual($minValue, $actual);
        $this->assertLessThanOrEqual($maxValue, $actual);
    }

    /**
     * Check if two times match nearly match each other, within a specified
     * number of seconds.  MySQL and SF datetime formats, as specified in
     * gosUtility_Time, are both acceptable.
     *
     * @param   string  $expected    The time you expect to find
     * @param   string  $actual      The actual time that you got
     * @param   integer $fuzzyAmount The amount of fuzziness
     *
     * @return  nothing
     */
    public function assertTimeFuzzy($expected, $actual, $fuzzyAmount=2)
    {
        $this->addToAssertionCount(1);

        // Let me preface this by saying PHP's DateTime support is lacking.
        if ($expected == $actual)
        {
            return;
        }

        // Find how much difference there is in the times
        $expectedTimePieces = explode(':', $expected);
        $actualTimePieces = explode(':', $actual);

        // If the times differ by hours, fail
        if ($expectedTimePieces[0] != $actualTimePieces[0])
        {
            $this->fail("Expected time ". $expected ." didn't fuzzily match actual time:". $actual);
        }

        $expMin = $expectedTimePieces[1];
        $actMin = $actualTimePieces[1];
        if ($expMin == $actMin)
        {
            // Hooray, they are only seconds apart!
            $secondsDifference = ((int)$expectedTimePieces[2]) - ((int)$actualTimePieces[2]);
        }
        elseif (abs($expMin - $actMin) <= 1 && $expMin > $actMin)
        {
            // Add 1 minute (60 seconds) to expected
            $secondsDifference = (((int)$expectedTimePieces[2]) + 60) - ((int)$actualTimePieces[2]);
        }
        elseif (abs($expMin - $actMin) <= 1 && $expMin < $actMin)
        {
            // Add 1 minute (60 seconds) to actual
            $secondsDifference = ((int)$expectedTimePieces[2]) - (((int)$actualTimePieces[2]) + 60);
        }
        else
        {
            // The minutes differ by more than one
            $this->fail("Expected time ". $expected ." didn't fuzzily match actual time:". $actual);
        }

        // Is it too many seconds apart?
        if (abs($secondsDifference) <= $fuzzyAmount)
        {
            return;
        }

        $this->fail("Expected time ". $expected ." didn't fuzzily match actual time:". $actual);
    }

    /**
     * Asserts that the structure of two arrays is the same.  For instance,
     * if you have a function that returns:
     * array(
     *     'button1' => array('name' => 'Deal another card', 'value' => 'hit'),
     *     'button2' => array('name' => 'I like it how it is', 'value' => 'stay')
     * )
     * but you don't want to test if the actual names or values of the array
     * are equal, you can pass in an expected array of
     * array(
     *     'button1' => array('name' => null, 'value' => 'hit'),
     *     'button2' => array('name' => null, 'value' => 'stay')
     * )
     * This will ensure that the structure of the two arrays are the same.
     * That is, it checks to see that all the keys are the same.
     * Additionally, it will ensure that the 'value' entries of each table are
     * the same because the expected array's value is non-null.
     *
     * Note: there is no way to ensure that a certain key's value in the
     * actual array is null with this.
     *
     * @param [array] $expected
     * @param [array] $actual
     * @return none
     */
    public function assertArrayStructureEquals($expected, $actual)
    {
        $this->addToAssertionCount(1);
        if (!is_array($expected))
        {
            $this->fail('The expected array is not actually an array.');
        }
        if (!is_array($actual))
        {
            $this->fail('The actual array is not actually an array.');
        }
        // see comments below about how these functions work
        $this->assertArrayStructureEqualsExpectedFirst($expected, $actual, '[root]');
        $this->assertArrayStructureEqualsActualFirst($actual, $expected, '[root]');
    }

    /**
     * These next two functions could easily be combined into one, but I
     * tried that and it gets really confusing to follow the logic.  Thus, I'm
     * going to leave them separate.  This does lead to very similar code
     * between these two functions, so if you change one, make sure to change
     * the corresponding part in the other.  Comments in the second function are
     * more concise, so look at the first function if you want something
     * explained better.
     *
     * We want to show that there is a bijection between the two arrays.
     * The first function shows it's one-to-one; the second shows it's onto.
     */

    /**
     * This is just a helper for self::assertArrayStructureEquals()
     * @param array $expected The expected array
     * @param array $actual The actual array
     * @param string $trace The sequence of keys that has led to this array
     * @return none
     */
    protected function assertArrayStructureEqualsExpectedFirst(array $expected, array $actual, $trace)
    {
        // iterate through every element of $expected
        foreach ($expected as $key => $val)
        {
            // we always want the keys to be the same between the two arrays,
            // so we check this first.
            // Make sure the current element also exists in $actual
            if (!array_key_exists($key, $actual))
            {
                $this->fail('Array key "'. $key .'" is set in expected result, but not the actual result (Trace: '. $trace .').');
            }

            // If we don't care what the actual value is in $actual (we just
            // care that the key exists), we set $expected[$key] to null.
            // So if it's null, we don't need to test any values, so we're done.
            if ($val === null)
            {
                continue;
            }

            // Since $expected[$key] is not null, we want to make sure that the
            // values for each array are equal
            if (is_array($val))
            {
                // if this element in $expected is an array, make sure
                // that element is also an array in $actual...
                if (!is_array($actual[$key]))
                {
                    $this->fail('Array key "'. $key .'" is an array in expected result, but not the actual result (Trace: '. $trace .').');
                }
                // ...then recursively assert same structure
                $this->assertArrayStructureEqualsExpectedFirst($val, $actual[$key], $trace .'.'. $key);
            }
            else
            {
                // This is our base case: an individual element.  We just
                // check to see that the two values are equal
                if ($actual[$key] != $val)
                {
                    $this->fail('Expected value for key "'. $key .'" is:'."\n". $val ."\n".'but actual is:'."\n". $actual[$key] ."\n".'(Trace: '. $trace .').');
                }
            }
        }
    }

    /**
     * This is just a helper for self::assertArrayStructureEquals()
     * @param array $actual The actual array
     * @param array $expected The expected array
     * @param string $trace The sequence of keys that has led to this array
     * @return none
     */
    protected function assertArrayStructureEqualsActualFirst(array $actual, array $expected, $trace)
    {
        // iterate through every element of $actual
        foreach ($actual as $key => $val)
        {
            // make sure that element also exists in $expected
            if (!array_key_exists($key, $expected))
            {
                $this->fail('Array key "'. $key .'" is is set in actual result, but not the expected result (Trace: '. $trace .').');
            }
            // expected is null; we don't care if values are equal
            if ($expected[$key] === null)
            {
                continue;
            }
            // check values
            if (is_array($val))
            {
                // Make sure expected is also an array
                if (!is_array($expected[$key]))
                {
                    $this->fail('Array key "'. $key .'" is an array in actual result, but not the expected result (Trace: "'. $trace .'").');
                }
                // Recurse
                $this->assertArrayStructureEqualsActualFirst($val, $expected[$key], $trace .'.'. $key);
            }
            else
            {
                // Base case
                if ($expected[$key] != $val)
                {
                    $this->fail('Expected value for key "'. $key .'" is "'. $val .'", but actual value is "'. $expected[$key] .'" (Trace: "'. $trace .'").');
                }
            }
        }
    }

    /**
     * Asserts that two arrays contain the same values.  For instance,
     * $expected = array(
     *     'first element',
     *     'second element',
     *     'third element',
     * );
     * and
     * $actual = array(
     *     'first element',
     *     'third element',
     *     'second element',
     * );
     * are considered equal, since they have the same values, despite the fact
     * that they are in different order.
     *
     * @param array $expected
     * @param array $actual
     * @see PHPUnit_Framework_Assert::assertEquals()
     */
    protected function assertArraysContainSameValues(array $expected, array $actual)
    {
        $expected = sort($expected);
        $actual = sort($actual);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Asserts that two arrays contain the same keys to values.  For instance,
     * $expected = array(
     *     'first' => 'first element',
     *     'second' => 'second element',
     *     'third' => 'third element',
     * );
     * and
     *     'first' => 'first element',
     *     'third' => 'third element',
     *     'second' => 'second element',
     * );
     * are considered equal, since they have the same keys mapping to the same
     * values, despite the fact that they are in different order.
     *
     * @param array $expected
     * @param array $actual
     * @see PHPUnit_Framework_Assert::assertEquals()
     */
    protected function assertArraysContainSameKeysToValues(array $expected, array $actual)
    {
        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }
}
