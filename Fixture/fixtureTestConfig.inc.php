<?php
/**
 * Configuration file for tests that utilize DB fixtures
 */

// Include the Genius Open Source test config file
require_once dirname(dirname(__FILE__)) .'/Core/testConfig.inc.php';

// Read the database connection information from YAML
gosUtility_Config_Reader::addConfigFile(GOS_ROOT . 'Fixture/fixtureConfig.yaml');

/**
 * Generate a database to use for this run of tests
 */
// Use the current time as part of the DB name
$dbCreator = new gosDB_AutoDBGenerator();
$time = str_replace('.', '_', microtime(true));
$dbName = $dbCreator->generateTestDBName('main',$time);

// Gather the database server connection parameters
$config = gosUtility_Config_Reader::getConfigEntry('testDB');
$mainConnParams = new gosDB_ConnParams($config['host'], $config['user'], $config['pass'], $dbName, $config['port']);

// The schema to apply is defined in fixtureConfig.yaml
$schemaDir = GOS_ROOT . gosUtility_Config_Reader::getConfigEntry('schemaDir');

// Create a new DB and apply the schema for this test run
$dbCreator->generateDBForSchema('main', $mainConnParams, $schemaDir);

// Add this database to the global connection parameters
$connParamsCollection = new gosDB_ConnParamsCollection();
$connParamsCollection->setParams('main', $mainConnParams);

gosDB_Helper::setDBConnParamsCollection($connParamsCollection);
