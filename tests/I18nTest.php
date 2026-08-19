<?php

use PHPUnit\Framework\TestCase;

final class I18nTest extends TestCase {

    public function test_sanitize_language_accepts_known_codes() {
        $this->assertSame( 'auto', navi_sanitize_language( 'auto' ) );
        $this->assertSame( 'fr', navi_sanitize_language( 'fr' ) );
        $this->assertSame( 'en', navi_sanitize_language( 'en' ) );
    }

    public function test_sanitize_language_defaults_to_auto_on_unknown_code() {
        $this->assertSame( 'auto', navi_sanitize_language( 'de' ) );
        $this->assertSame( 'auto', navi_sanitize_language( '' ) );
        $this->assertSame( 'auto', navi_sanitize_language( null ) );
    }
}
