<?php

namespace Rappasoft\Lockout\Tests;

/**
 * Class BladeTest
 *
 * @package Rappasoft\Lockout\Tests
 */
class BladeTest extends TestCase
{

    /** @test */
    public function the_readonly_directive_renders_the_correct_block_based_on_the_package_status() {
        config(['lockout.enabled' => false]);

        $this->assertEquals('app is not in read only mode', $this->renderView('readonly'));

        config(['lockout.enabled' => true]);

        $this->assertEquals('app is in read only mode', $this->renderView('readonly'));
    }
}
