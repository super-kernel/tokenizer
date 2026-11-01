<p align="right">
  <a href="README.md">English</a> | <strong>中文文档</strong>
</p>

# SuperKernel Tokenizer

`super-kernel/tokenizer` 从 PHP 源码中提取具名类级声明的完全限定名。它使用带解析器校验的 PHP 原生词法分析器，需要 `ext-tokenizer`。

```php
use SuperKernel\Tokenizer\ClassNameParser;

$source = file_get_contents('/srv/project/source.inc');
$classNames = false === $source ? false : (new ClassNameParser())->parse($source);

if ($classNames === false) {
    // The file is unreadable, or its source is parser-rejected or has no named class-like declaration.
}
```

`parse()` 接收字符串形式的 PHP 源码，返回一个已排序的非空列表，包含具名的类、接口、trait 和枚举；匿名类会被忽略。被 PHP 词法分析器的解析校验拒绝的源码，或不含具名类级声明的源码，都返回 `false`；这些输入属于普通的解析未命中，而不是异常控制流。解析校验仅限于 `token_get_all()` 在 `TOKEN_PARSE` 模式下报告的错误；组件不会另外执行完整的 PHP 编译或语法检查。

在 SuperKernel 容器中，注入 `SuperKernel\Tokenizer\Contract\ClassNameParserInterface` 即可获得 `ClassNameParser` 服务。

组件不读取文件、不扫描目录、不构建类图、不加载类，也不缓存结果。这些策略由调用方负责。
