# SuperKernel Tokenizer

`super-kernel/tokenizer` extracts fully qualified named class-like declarations from PHP source. It uses PHP's native
tokenizer with parser validation.

The component requires `super-kernel/attribute` to bind `ClassNameParser` through
`Provider(ClassNameParserInterface::class)`. PHP 8.4.27 or later in the 8.4.x series and `ext-tokenizer` are
execution-environment requirements. PHPUnit and PHP CS Fixer are development-only tools.

```php
use SuperKernel\Tokenizer\ClassNameParser;

$source = file_get_contents('/srv/project/source.inc');
$classNames = false === $source ? false : (new ClassNameParser())->parse($source);

if ($classNames === false) {
    // The file is unreadable, or its source is parser-rejected or has no named class-like declaration.
}
```

`parse()` takes the PHP source as a string. It returns a sorted non-empty list containing named classes, interfaces,
traits, and enums; anonymous classes are ignored. Source rejected by PHP's tokenizer parser validation or source
without a named class-like declaration returns `false`; these inputs are ordinary parse misses rather than exceptional
control flow. Parser validation is limited to errors surfaced by `token_get_all()` with `TOKEN_PARSE`; the component
does not run a separate full PHP compilation or lint pass.

The component does not read files, scan directories, build classmaps, load classes, or cache results. Those policies
belong to its callers.

## Development

Use a PHP 8.4 Composer in this package's checkout:

```bash
composer install
composer validate --strict
composer test
composer format:check
composer format
```
