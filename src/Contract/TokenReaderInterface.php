<?php
declare(strict_types=1);

namespace SuperKernel\Tokenizer\Contract;

use IteratorAggregate;
use PhpToken;
use Traversable;

interface TokenReaderInterface extends IteratorAggregate
{
	/**
	 * @return Traversable<PhpToken>
	 */
	public function getIterator(): Traversable;
}