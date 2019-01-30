<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once('setup_include_path.php');

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use Clamp\ConfigOptionsParser;
use JsonPath\JsonPath;

class ConfigTest extends TestCase
{

	protected $cop;

	public function setUp()
	{
		// Set up the test class here
		$this->cop = new Clamp\ConfigOptionsParser();
		$this->cop->parse(['./clamp.json']);
	}

	public function testGetConfigPath()
	{
		$this->assertEquals(
			$this->cop->getConfig('$.apache.commands.httpd'),
			'httpd');
	}

	public function testGetConfigPath2()
	{
		$this->assertEquals(
			# TODO: figure out why the reset works/is needed
			reset($this->cop->getConfig("$.host.options")),
			'localhost');
	}
}

?>