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
                [$namespace, $index] = self::readNamespace($tokens, $index + 1);

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

            $name = self::readDeclarationName($tokens, $index + 1);

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

    /**
     * @param list<array{int, string, int}|string> $tokens
     *
     * @return array{string, int}
     */
    private static function readNamespace(array $tokens, int $index): array
    {
        $namespace = '';
        $tokenCount = count($tokens);

        for (; $index < $tokenCount; ++$index) {
            $token = $tokens[$index];

            if (';' === $token || '{' === $token) {
                break;
            }

            if (!is_array($token)) {
                continue;
            }

            if (in_array($token[0], [
                T_STRING,
                T_NAME_QUALIFIED,
                T_NS_SEPARATOR,
            ], true)) {
                $namespace .= $token[1];
            }
        }

        return [
            trim($namespace, '\\'),
            $index,
        ];
    }

    /**
     * @param list<array{int, string, int}|string> $tokens
     */
    private static function readDeclarationName(array $tokens, int $index): ?string
    {
        $tokenCount = count($tokens);

        for (; $index < $tokenCount; ++$index) {
            $token = $tokens[$index];

            if (
                is_array($token)
                && in_array($token[0], [
                    T_WHITESPACE,
                    T_COMMENT,
                    T_DOC_COMMENT,
                ], true)
            ) {
                continue;
            }

            return is_array($token) && T_STRING === $token[0] ? $token[1] : null;
        }

        return null;
    }
}
