<?php

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase {

    public function test_sanitize_checkbox_empty_values_return_zero() {
        $this->assertSame( 0, navi_sanitize_checkbox( '' ) );
        $this->assertSame( 0, navi_sanitize_checkbox( null ) );
        $this->assertSame( 0, navi_sanitize_checkbox( 0 ) );
        $this->assertSame( 0, navi_sanitize_checkbox( '0' ) );
    }

    public function test_sanitize_checkbox_truthy_values_return_one() {
        $this->assertSame( 1, navi_sanitize_checkbox( '1' ) );
        $this->assertSame( 1, navi_sanitize_checkbox( 1 ) );
        $this->assertSame( 1, navi_sanitize_checkbox( 'yes' ) );
    }

    public function test_sanitize_fab_position_accepts_left() {
        $this->assertSame( 'left', navi_sanitize_fab_position( 'left' ) );
    }

    public function test_sanitize_fab_position_defaults_to_right() {
        $this->assertSame( 'right', navi_sanitize_fab_position( 'right' ) );
        $this->assertSame( 'right', navi_sanitize_fab_position( 'top' ) );
        $this->assertSame( 'right', navi_sanitize_fab_position( '' ) );
        $this->assertSame( 'right', navi_sanitize_fab_position( null ) );
    }

    public function test_hex_to_rgb_six_digit_hex() {
        $this->assertSame( '26, 26, 26', navi_hex_to_rgb( '#1a1a1a' ) );
    }

    public function test_hex_to_rgb_three_digit_hex() {
        $this->assertSame( '255, 0, 0', navi_hex_to_rgb( '#f00' ) );
    }

    public function test_hex_to_rgb_without_hash_prefix() {
        $this->assertSame( '26, 26, 26', navi_hex_to_rgb( '1a1a1a' ) );
    }

    public function test_hex_to_rgb_returns_empty_for_invalid_hex() {
        $this->assertSame( '', navi_hex_to_rgb( 'not-a-color' ) );
        $this->assertSame( '', navi_hex_to_rgb( '#12345' ) );
        $this->assertSame( '', navi_hex_to_rgb( '' ) );
    }

    public function test_sanitize_radius_clamps_to_range() {
        $this->assertSame( 0, navi_sanitize_radius( -10 ) );
        $this->assertSame( 0, navi_sanitize_radius( 0 ) );
        $this->assertSame( 25, navi_sanitize_radius( 25 ) );
        $this->assertSame( 50, navi_sanitize_radius( 50 ) );
        $this->assertSame( 50, navi_sanitize_radius( 999 ) );
    }

    public function test_sanitize_radius_casts_non_numeric_to_zero() {
        $this->assertSame( 0, navi_sanitize_radius( 'abc' ) );
    }
}
