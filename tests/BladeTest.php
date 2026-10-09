<?php

namespace Rappasoft\Lockout\Tests;

use PHPUnit\Framework\Attributes\Test;

/**
 * Class BladeTest.
 */
class BladeTest extends TestCase
{
    #[Test]
    public function the_readonly_directive_renders_the_correct_block_based_on_the_package_status()
    {
        config(['lockout.enabled' => false]);

        $this->assertEquals('app is not in read only mode', $this->renderView('readonly'));

        config(['lockout.enabled' => true]);

        $this->assertEquals('app is in read only mode', $this->renderView('readonly'));
    }
}
