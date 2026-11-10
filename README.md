<p align="right">
  <strong>English</strong> | <a href="README.zh-CN.md">中文文档</a>
</p>

# SuperKernel Tokenizer

> [!WARNING]
> This package is deprecated and no longer maintained. Since SuperKernel 1.0.0-alpha.2, SKERNEL finds an application's classes itself when it builds the project, so applications no longer install this package.

`super-kernel/tokenizer` extracts fully qualified named class-like declarations from PHP source. It uses PHP's native tokenizer with parser validation and requires `ext-tokenizer`.

```php
use SuperKernel\Tokenizer\ClassNameParser;

$source = file_get_contents('/srv/project/source.inc');
$classNames = false === $source ? false : (new ClassNameParser())->parse($source);

if ($classNames === false) {
    // The file is unreadable, or its source is parser-rejected or has no named class-like declaration.
}
```

`parse()` takes the PHP source as a string. It returns a sorted non-empty list containing named classes, interfaces, traits, and enums; anonymous classes are ignored. Source rejected by PHP's tokenizer parser validation or source without a named class-like declaration returns `false`; these inputs are ordinary parse misses rather than exceptional control flow. Parser validation is limited to errors surfaced by `token_get_all()` with `TOKEN_PARSE`; the component does not run a separate full PHP compilation or lint pass.

In a SuperKernel container, inject `SuperKernel\Tokenizer\Contract\ClassNameParserInterface` to receive the `ClassNameParser` service.

The component does not read files, scan directories, build classmaps, load classes, or cache results. Those policies belong to its callers.
