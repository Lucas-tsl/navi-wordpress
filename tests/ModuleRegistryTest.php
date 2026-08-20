<?php

use PHPUnit\Framework\TestCase;

/**
 * Navi_Module_Registry::$modules est un tableau statique partagé par tout le
 * process PHPUnit : chaque test enregistre un identifiant de module unique
 * (test-registry-*) pour ne jamais lire l'état laissé par un autre test.
 */
final class ModuleRegistryTest extends TestCase {

    public function test_get_returns_null_for_unregistered_module() {
        $this->assertNull( Navi_Module_Registry::get( 'test-registry-unknown' ) );
    }

    public function test_register_applies_defaults_for_omitted_args() {
        Navi_Module_Registry::register( 'test-registry-defaults', array() );
        $module = Navi_Module_Registry::get( 'test-registry-defaults' );

        $this->assertSame( 'test-registry-defaults', $module['label'] );
        $this->assertSame( 'navi_module_active_test-registry-defaults', $module['option_name'] );
        $this->assertTrue( $module['default_active'] );
        $this->assertTrue( $module['available'] );
        $this->assertSame( '', $module['visibility_selector'] );
    }

    public function test_register_keeps_explicit_args_over_defaults() {
        Navi_Module_Registry::register( 'test-registry-explicit', array(
            'label'          => 'Module de test',
            'default_active' => false,
            'available'      => false,
        ) );
        $module = Navi_Module_Registry::get( 'test-registry-explicit' );

        $this->assertSame( 'Module de test', $module['label'] );
        $this->assertFalse( $module['default_active'] );
        $this->assertFalse( $module['available'] );
    }

    public function test_all_includes_every_registered_module() {
        Navi_Module_Registry::register( 'test-registry-all-a', array() );
        Navi_Module_Registry::register( 'test-registry-all-b', array() );

        $all = Navi_Module_Registry::all();

        $this->assertArrayHasKey( 'test-registry-all-a', $all );
        $this->assertArrayHasKey( 'test-registry-all-b', $all );
    }
}
