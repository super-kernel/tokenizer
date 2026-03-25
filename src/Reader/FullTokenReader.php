<?php

declare(strict_types=1);

namespace SuperKernel\Tokenizer\Reader;

use PhpToken;
use RuntimeException;
use ArrayIterator;
use SuperKernel\Tokenizer\Contract\TokenReaderInterface;
use function file_get_contents;

final class FullTokenReader implements TokenReaderInterface
{
	/** @var PhpToken[] */
	private array $tokens = [];

	public function __construct(string $filePath)
	{
		$content = @file_get_contents($filePath);
		if (false === $content) {
			throw new RuntimeException("Failed to read file: $filePath");
		}

		$allTokens = PhpToken::tokenize($content);

		foreach ($allTokens as $token) {
			if (!$token->isIgnorable()) {
				$this->tokens[] = $token;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->tokens);
	}
}