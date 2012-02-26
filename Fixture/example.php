<?php
// Include the Genius config file
require_once dirname(dirname(__FILE__)) . '/Core/gosConfig.inc.php';

/**
 * The function that you want to test.
 */
function getThingFromDB($id) {
    $db = gosDB_Helper::getDBByName('main');

    return $db->getOne("SELECT s1 FROM fixture_test WHERE i1 = " . $id);
}

/**
 * In a nearby test file...
 */

// Include the Genius fixture configuration. This geneartes a database
// and applies the schema to it.  See fixtureTestConfig.inc.php
// for more details.
require_once(GOS_ROOT . 'Fixture/fixtureTestConfig.inc.php');

function testGetThingFromDB() {
    // Create a fixture
    $fixture = gosTest_Fixture_Controller::getByDBName('main');

    // Load the fixture into the database
    $fixture->parseFixtureFile(GOS_ROOT . 'Fixture/example_fixture.yaml');

    // Directly access the fixture, which is identical to what the database contains
    $idToGet = $fixture->get('fixtureName.fixture_test.row1.i1');

    // Pull the value we want directly from the fixture
    $fixtureThing = $fixture->get('fixtureName.fixture_test.row1.s1');
    // Pull the value we want from the DB via the function we're testing
    $thing = getThingFromDB($idToGet);

    echo "The fixture put $fixtureThing into the DB.\n";
    echo "Our function selected $thing from the DB.\n";
}

// Run the test
testGetThingFromDB();
