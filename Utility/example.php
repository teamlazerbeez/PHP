<?php
// Include the Genius config file
require_once dirname(dirname(__FILE__)) . '/Core/gosConfig.inc.php';

/**
 * gosUtility_URLBuilder is a URL object that can be manipulated
 * piecemeal.
 */
// An absolute URL
$url = 'http://foo.com/path1/path2';
$builder = gosUtility_URLBuilder::createForAbsoluteURL($url);
// http://foo.com/path1/path2
echo $builder->toString() ."\n";

// Add query params
$builder->setQueryParam('version', '3');
$builder->setQueryParamsFromArray(array('foo' => 7, 'bar' => 'niner'));
// http://foo.com/path1/path2?version=3&foo=7&bar=niner
echo $builder->toString() ."\n";

// Escape an unsafe path
$url = 'http://foo.com/pa@th1/path2';
$builder = gosUtility_URLBuilder::createForAbsoluteUrl($url);
// http://foo.com/pa%40th1/path2
echo $builder->toString() ."\n";

/**
 * gosUtility_URLStitcher builds URLs from their compoonent pieces.
 */
$scheme = 'http';
$host = 'google.com';
$port = '80';
$user = '';
$pass = '';
$path = '/search';
$query = 'q=foo';
$fragment = '';
$url = gosUtility_URLStitcher::absolute($scheme, $host, $port, $user, $pass, $path, $query, $fragment);
// http://google.com:80/search?q=foo
echo $url . "\n";

/**
 * gosUtility_YamlLoader loads a YAML file, complaining if the file doesn't
 * exist and optionally complaining if the loaded file was empty.
 */
$parsedYaml = gosUtility_YamlLoader::loadFile(GOS_ROOT . 'Utility/test/gosUtility/_fixtures/nameMapping.yaml', false);
// Date-time
echo $parsedYaml['dataType']['datetime'] . "\n";

/**
 * gosUtility_Config_Reader pulls in a YAML file and allows you to easily
 * read keys from it.
 */
gosUtility_Config_Reader::addConfigFile(GOS_ROOT . 'Utility/test/gosUtility/Config/_helpers/shared.yaml');
// 1000
echo gosUtility_Config_Reader::getConfigEntry('a_large_number') . "\n";

/*
class Par extends gosUtility_Parallel {
    protected function doWorkChild() {
        $childNum = getmypid();

        global $run;
        while ($run) {
            print "Child $childNum doing work.\n";
            usleep(3000000);
        }
    }
}

$par = new Par(1);
$par->go();
*/
