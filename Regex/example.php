<?php
// Include the Genius config file
require_once 'Core/gosConfig.inc.php';

// The gosRegex functions can be used exactly as their preg_* counterparts
gosRegex::match('/foo (bar)/', 'foo foo bar foo baz foo', $matches);
print_r($matches);

// But if you do something that causes an error, the gosRegex functions let you know
try {
    // Example from http://us.php.net/preg_last_error
    gosRegex::match('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar');
} catch (gosException_RegularExpression $e) {
    print "Got a regex error: " . $e->getMessage() . "\n";
}
