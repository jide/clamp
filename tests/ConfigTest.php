<?php
declare(strict_types=1);

// bring in non-namespaced things
require_once('setup_include_path.php');
// bring in namespaced deps
require_once realpath(__DIR__ . '/../vendor/autoload.php');

use PHPUnit\Framework\TestCase;

use Clamp\ConfigOptionsParser;
use JsonPath\JsonPath;

class ConfigTest extends TestCase
{

	protected $cop;

	public function setUp()
	{
		// Set up the test class here
		$this->cop = new Clamp\ConfigOptionsParser();
		# TODO: don't use relative path
		$this->cop->parse(['./clamp.defaults.json']);
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