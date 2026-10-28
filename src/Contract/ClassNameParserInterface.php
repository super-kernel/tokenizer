<?php

declare(strict_types=1);

namespace SuperKernel\Tokenizer\Contract;

/**
 * Extracts fully qualified named class-like declarations from PHP source.
 */
interface ClassNameParserInterface
{
    /**
     * Parses named classes, interfaces, traits, and enums from PHP source.
     *
     * Parser validation covers errors surfaced by PHP's tokenizer and is not a full compilation pass.
     *
     * @return non-empty-list<class-string>|false Sorted names, or false for parser-rejected or declaration-free
     *                                            source
     */
    public function parse(string $source): array|false;
}
