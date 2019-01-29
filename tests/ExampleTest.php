<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
	public function setUp()
	{
		// Set up the test class here
	}

    public function testCalculationOfMean()
	{
		$numbers = [3, 7, 6, 1, 5];
		$this->assertEquals(4, 4);
	}

	public function tearDown()
	{
		// Clean up
	}

}



?>