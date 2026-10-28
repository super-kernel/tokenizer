<?php

declare(strict_types=1);

namespace SuperKernel\Tokenizer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SuperKernel\Tokenizer\ClassNameParser;

final class ClassNameParserTest extends TestCase
{
    public function testParsesEveryNamedClassLikeDeclarationAndIgnoresAnonymousClasses(): void
    {
        $this->assertSame(
            [
                'Acme\\Feature\\Behavior',
                'Acme\\Feature\\Contract',
                'Acme\\Feature\\Service',
                'Acme\\Feature\\State',
            ],
            new ClassNameParser()->parse(<<<'PHP'
                <?php

                namespace Acme\Feature;

                final class /* declaration comment */ Service {}
                interface Contract {}
                trait Behavior {}
                enum State {}

                $anonymous = new class {};
                $classConstant = Service::class;
                PHP),
        );
    }

    public function testSupportsBracketedAndGlobalNamespaces(): void
    {
        $this->assertSame(
            [
                'First\\Space\\One',
                'GlobalClass',
            ],
            new ClassNameParser()->parse(<<<'PHP'
                <?php

                namespace First\Space {
                    class One {}
                }

                namespace {
                    class GlobalClass {}
                }
                PHP),
        );
    }

    public function testTracksMultipleUnbracketedNamespacesAndDeduplicatesNames(): void
    {
        $this->assertSame(
            [
                'First\\Space\\Duplicate',
                'First\\Space\\Zed',
                'Second\\Space\\Alpha',
            ],
            new ClassNameParser()->parse(<<<'PHP'
                <?php

                namespace First\Space;

                namespace\helper();
                class Zed {}

                if (true) {
                    class Duplicate {}
                } else {
                    class Duplicate {}
                }

                namespace Second\Space;

                interface Alpha {}
                PHP),
        );
    }

    public function testReturnsFalseForEmptyOrDeclarationFreeSource(): void
    {
        $parser = new ClassNameParser();

        $this->assertFalse($parser->parse(''));
        $this->assertFalse($parser->parse('<?php function helper(): void {}'));
    }

    public function testReturnsFalseForParserValidationErrors(): void
    {
        $parser = new ClassNameParser();

        $this->assertFalse($parser->parse('<?php class'));
        $this->assertFalse($parser->parse('<?php class Broken { public public int $value; }'));
        $this->assertFalse($parser->parse('<?php abstract final class Broken {}'));
    }
}
