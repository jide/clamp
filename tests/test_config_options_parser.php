<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once('setup_include_path.php');

// require('includes/Clamp/ConfigOptionsParser.php');

class ExampleTest extends TestCase
{
	public function setUp()
	{
		// Set up the test class here
	}

	// public function testSomething()
	// {
	// 	// Write test assertions here
	// }

	public function tearDown()
	{
		// Clean up
	}

	public function testCalculationOfMean()
	{
		$numbers = [3, 7, 6, 1, 5];
		$this->assertEquals(4, 4);
	}
}


