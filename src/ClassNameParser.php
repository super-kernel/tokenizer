<?php

declare(strict_types=1);

namespace SuperKernel\Tokenizer;

use CompileError;
use SuperKernel\Attribute\Provider;
use SuperKernel\Tokenizer\Contract\ClassNameParserInterface;

use function array_keys;
use function count;
use function in_array;
use function is_array;
use function sort;
use function token_get_all;
use function trim;

use const SORT_STRING;
use const T_CLASS;
use const T_COMMENT;
use const T_DOC_COMMENT;
use const T_ENUM;
use const T_INTERFACE;
use const T_NAME_QUALIFIED;
use const T_NAMESPACE;
use const T_NS_SEPARATOR;
use const T_STRING;
use const T_TRAIT;
use const T_WHITESPACE;
use const TOKEN_PARSE;

#[Provider(ClassNameParserInterface::class)]
final readonly class ClassNameParser implements ClassNameParserInterface
{
    public function parse(string $source): array|false
    {
        try {
            /** @var list<array{int, string, int}|string> $tokens */
            $tokens = token_get_all($source, TOKEN_PARSE);
        } catch (CompileError) {
            return false;
        }

        $namespace = '';
        $classNames = [];
        $tokenCount = count($tokens);

        for ($index = 0; $index < $tokenCount; ++$index) {
            $token = $tokens[$index];

            if (!is_array($token)) {
                continue;
            }

            if (T_NAMESPACE === $token[0]) {
                // Collect the declared name up to the statement terminator or the bracketed body.
                $namespace = '';

                for (++$index; $index < $tokenCount; ++$index) {
                    $namespaceToken = $tokens[$index];

                    if (';' === $namespaceToken || '{' === $namespaceToken) {
                        break;
                    }

                    if (!is_array($namespaceToken)) {
                        continue;
                    }

                    if (in_array($namespaceToken[0], [
                        T_STRING,
                        T_NAME_QUALIFIED,
                        T_NS_SEPARATOR,
                    ], true)) {
                        $namespace .= $namespaceToken[1];
                    }
                }

                $namespace = trim($namespace, '\\');

                continue;
            }

            if (!in_array($token[0], [
                T_CLASS,
                T_INTERFACE,
                T_TRAIT,
                T_ENUM,
            ], true)) {
                continue;
            }

            // The first meaningful token after the keyword names the declaration; anonymous classes have none.
            $name = null;

            for ($nameIndex = $index + 1; $nameIndex < $tokenCount; ++$nameIndex) {
                $nameToken = $tokens[$nameIndex];

                if (
                    is_array($nameToken)
                    && in_array($nameToken[0], [
                        T_WHITESPACE,
                        T_COMMENT,
                        T_DOC_COMMENT,
                    ], true)
                ) {
                    continue;
                }

                $name = is_array($nameToken) && T_STRING === $nameToken[0] ? $nameToken[1] : null;

                break;
            }

            if (null === $name) {
                continue;
            }

            $className = '' === $namespace ? $name : $namespace . '\\' . $name;
            $classNames[$className] = true;
        }

        if ([] === $classNames) {
            return false;
        }

        $classNames = array_keys($classNames);
        sort($classNames, SORT_STRING);

        return $classNames;
    }
}
