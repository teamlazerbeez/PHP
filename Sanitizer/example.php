<?php
// Include the Genius config file
require_once 'Core/gosConfig.inc.php';

echo '<html>
<h1>Genius Sanitization Examples</h1>';

echo '<h2>HTML Content</h2>';
// Output an unsafe string, presumably user input
$xss = '<script>alert(\'oh snap\');</script>';
echo 'If your entered your name as ' . $xss . ', we\'d be in trouble.<br>' . "\n";

// Sanitize that string, and output it safely
$htmlContentContext = gosSanitizer::sanitizeForHTMLContent($xss);
echo 'But if we sanitize your name, ' . $htmlContentContext . ', then all is well.<br>' . "\n";

echo '<h2>HTML Attribute</h2>';
// We can also safely sanitize it for an HTML attribute context
$htmlAttributeContext = gosSanitizer::sanitizeForHTMLAttribute($xss);
echo 'Tainted strings can also be used in an
    <a href="http://google.com" title="' . $htmlAttributeContext . '">HTML attribute</a>
    context.<br>' . "\n";

echo '<h2>JavaScript string</h2>';
// And we can even make strings used in JavaScript safe
$jsString = '\';alert(1);var b =\'';
echo '<script type="text/javascript">
var a = \'' . $jsString . '\';
var aSafe = \'' . gosSanitizer::sanitizeForJS($jsString) . '\';
</script>';

echo '</html>' . "\n";
