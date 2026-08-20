<?php

use PHPUnit\Framework\TestCase;

final class AccessibilityTest extends TestCase {

    protected function tearDown(): void {
        $GLOBALS['navi_test_filters'] = array();
    }

    public function test_get_languages_returns_empty_array_when_wpml_absent() {
        $this->assertSame( array(), navi_a11y_get_languages() );
    }

    public function test_get_languages_returns_wpml_filter_result_when_present() {
        $languages = array(
            'fr' => array( 'translated_name' => 'Français', 'url' => 'https://example.com/fr/', 'active' => 1 ),
            'en' => array( 'translated_name' => 'English', 'url' => 'https://example.com/en/', 'active' => 0 ),
        );

        $GLOBALS['navi_test_filters']['wpml_active_languages'] = function ( $value, $args ) use ( $languages ) {
            return $languages;
        };

        $this->assertSame( $languages, navi_a11y_get_languages() );
    }

    public function test_get_languages_returns_empty_array_when_filter_result_is_not_an_array() {
        $GLOBALS['navi_test_filters']['wpml_active_languages'] = function ( $value, $args ) {
            return null;
        };

        $this->assertSame( array(), navi_a11y_get_languages() );
    }
}
