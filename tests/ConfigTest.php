<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// require_once('setup_include_path.php');

require_once realpath(__DIR__ . '/../vendor/autoload.php');
// require('ConfigOptionsParser.php');

use Clamp\ConfigOptionsParser;
use JsonPath\JsonPath;
// use ConsoleKit\DefaultOptionsParser;

class ConfigTest extends TestCase
{

	protected $cop;

	public function setUp()
	{
		// Set up the test class here
		$this->cop = new Clamp\ConfigOptionsParser();
		$this->cop->parse(['./clamp.json']);
	}

	// public function testSomething()
	// {
	// 	// Write test assertions here
	// }

	// public function tearDown()
	// {
	// 	// Clean up
	// }

	public function testGetConfig()
	{
		// $numbers = [3, 7, 6, 1, 5];
		// $this->assertEquals(4, 4);
		// $c = $this->cop;
		// $this->cop->getConfig('$.apache.commands.httpd');

		print("123 123 123");
		print($this->cop->getConfig());

		// $this->assertEquals(
		// 	$this->cop->getConfig(),
		// 	// ' ',
		// 	' abc 123 ');
	}
}


?>