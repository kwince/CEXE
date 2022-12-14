<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\ResultPrinter;

use PHPUnit\Framework\TestResult;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class VoidPrinter implements ResultPrinter
{
    public function printResult(TestResult $result): void
    {
    }

    public function print(string $buffer): void
    {
    }
}
