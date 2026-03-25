<?php
declare(strict_types=1);

use Composer\Autoload\ClassLoader;
use SuperKernel\TokenParser\Reader\FullTokenReader;
use SuperKernel\TokenParser\TokenParser;

/**
 * @var ClassLoader $classLoader
 */
$classLoader = require __DIR__ . '/../vendor/autoload.php';

foreach ($classLoader->getClassMap() as $class => $path) {
	$streamReader = new FullTokenReader($path);

	$tokenParser = TokenParser::parse($streamReader);

	var_dump(
		[
			'class'     => $class,
			'path'      => $path,
			'valid'     => $tokenParser->isValid(),
			'name'      => $tokenParser->getName(),
			'namespace' => $tokenParser->getNamespace(),
			'className' => $tokenParser->getClassName(),
		],
	);
}


