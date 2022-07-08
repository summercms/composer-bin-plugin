<?php

declare(strict_types=1);

namespace Bamarni\Composer\Bin\Tests;

use Bamarni\Composer\Bin\BinInputFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;

/**
 * @covers \Bamarni\Composer\Bin\BinInputFactory
 */
final class BinInputFactoryTest extends TestCase
{
    /**
     * @dataProvider inputProvider
     */
    public function test_it_can_create_a_new_input(
        string $namespace,
        InputInterface $previousInput,
        InputInterface $expected
    ): void {
        $actual = BinInputFactory::createInput($namespace, $previousInput);

        self::assertEquals($expected, $actual);
    }

    public static function inputProvider(): iterable
    {
        yield [
            'foo-namespace',
            new StringInput('bin foo-namespace flex:update --prefer-lowest'),
            new StringInput('flex:update --prefer-lowest'),
        ];

        yield [
            'foo-namespace',
            new StringInput('bin foo-namespace flex:update --prefer-lowest --ansi'),
            new StringInput('flex:update --prefer-lowest --ansi'),
        ];

        // See https://github.com/bamarni/composer-bin-plugin/pull/23
        yield [
            'foo-namespace',
            new StringInput('bin --ansi foo-namespace flex:update --prefer-lowest'),
            new StringInput('--ansi flex:update --prefer-lowest'),
        ];

        // See https://github.com/bamarni/composer-bin-plugin/pull/73
        yield [
            'irrelevant',
            new StringInput('update --dry-run --no-plugins roave/security-advisories'),
            new StringInput('update --dry-run --no-plugins roave/security-advisories'),
        ];
    }

    /**
     * @dataProvider namespaceInputProvider
     */
    public function test_it_can_create_a_new_input_for_a_namespace(
        InputInterface $previousInput,
        InputInterface $expected
    ): void {
        $actual = BinInputFactory::createNamespaceInput($previousInput);

        self::assertEquals($expected, $actual);
    }

    public static function namespaceInputProvider(): iterable
    {
        yield [
            new StringInput('flex:update --prefer-lowest'),
            new StringInput('flex:update --prefer-lowest --working-dir=.'),
        ];

        yield [
            new StringInput('flex:update --prefer-lowest --ansi'),
            new StringInput('flex:update --prefer-lowest --ansi --working-dir=.'),
        ];
    }

    /**
     * @dataProvider forwardedCommandInputProvider
     */
    public function test_it_can_create_a_new_input_for_forwarded_command(
        InputInterface $previousInput,
        InputInterface $expected
    ): void {
        $actual = BinInputFactory::createForwardedCommandInput($previousInput);

        self::assertEquals($expected, $actual);
    }

    public static function forwardedCommandInputProvider(): iterable
    {
        yield [
            new StringInput('install --verbose'),
            new StringInput('bin all install --verbose'),
        ];

        yield [
            new StringInput('flex:update --prefer-lowest --ansi'),
            new StringInput('bin all flex:update --prefer-lowest --ansi'),
        ];
    }
}
