<?php

declare(strict_types=1);

namespace SuperKernel\Tokenizer;

use Iterator;
use IteratorIterator;
use SuperKernel\Tokenizer\Contract\TokenReaderInterface;
use const T_ABSTRACT;
use const T_CLASS;
use const T_DOUBLE_COLON;
use const T_ENUM;
use const T_FINAL;
use const T_INTERFACE;
use const T_NAME_QUALIFIED;
use const T_NAMESPACE;
use const T_NS_SEPARATOR;
use const T_READONLY;
use const T_STRING;
use const T_TRAIT;

final readonly class TokenParser
{
	private function __construct(
		private ?string $namespace,
		private ?string $name,
		private bool    $valid,
	)
	{
	}

	public static function parse(TokenReaderInterface $tokenReader): TokenParser
	{
		$namespace = '';
		$name = null;
		$valid = false;

		$iterator = $tokenReader->getIterator();
		if (!$iterator instanceof Iterator) {
			$iterator = new IteratorIterator($iterator);
		}

		$iterator->rewind();
		$prevToken = null;

		while ($token = $iterator->current()) {
			if ($token->id === T_NAMESPACE) {
				while (true) {
					$iterator->next();
					if (!$iterator->valid()) {
						break;
					}

					$sub = $iterator->current();
					if ($sub->is([T_NAME_QUALIFIED, T_STRING, T_NS_SEPARATOR])) {
						$namespace .= $sub->text;
					} elseif ($sub->text === ';' || $sub->text === '{') {
						break;
					}
				}
				$prevToken = $token;
				continue;
			}

			if ($token->is([T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM])) {
				if ($prevToken?->id === T_DOUBLE_COLON) {
					$prevToken = $token;
					$iterator->next();
					continue;
				}

				$iterator->next();
				while ($iterator->valid()) {
					$nameToken = $iterator->current();

					if ($nameToken->is([T_FINAL, T_ABSTRACT, T_READONLY])) {
						$iterator->next();
						continue;
					}

					if ($nameToken->id === T_STRING) {
						$name = $nameToken->text;
						$valid = true;
					}
					break;
				}

				if ($valid) break;
			}

			$prevToken = $token;
			$iterator->next();
		}

		return new self($namespace ?: null, $name, $valid);
	}

	/**
	 * @return bool
	 */
	public function isValid(): bool
	{
		return $this->valid;
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @return string|null
	 */
	public function getNamespace(): ?string
	{
		return $this->namespace;
	}

	public function getClassName(): ?string
	{
		if (!$this->name) return null;
		return $this->namespace ? "$this->namespace\\$this->name" : $this->name;
	}
}