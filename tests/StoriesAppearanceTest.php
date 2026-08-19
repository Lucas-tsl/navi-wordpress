<?php

use PHPUnit\Framework\TestCase;

final class StoriesAppearanceTest extends TestCase {

    public function test_sanitize_story_border_width_clamps_to_range() {
        $this->assertSame( 0, navi_sanitize_story_border_width( -5 ) );
        $this->assertSame( 0, navi_sanitize_story_border_width( 0 ) );
        $this->assertSame( 10, navi_sanitize_story_border_width( 10 ) );
        $this->assertSame( 20, navi_sanitize_story_border_width( 20 ) );
        $this->assertSame( 20, navi_sanitize_story_border_width( 999 ) );
    }

    public function test_sanitize_story_phone_padding_clamps_to_range() {
        $this->assertSame( 0, navi_sanitize_story_phone_padding( -5 ) );
        $this->assertSame( NAVI_STORIES_MAX_PHONE_PADDING, navi_sanitize_story_phone_padding( 999 ) );
        $this->assertSame( 15, navi_sanitize_story_phone_padding( 15 ) );
    }

    public function test_sanitize_story_phone_width_clamps_to_range() {
        $this->assertSame( NAVI_STORIES_MIN_PHONE_WIDTH, navi_sanitize_story_phone_width( 0 ) );
        $this->assertSame( NAVI_STORIES_MAX_PHONE_WIDTH, navi_sanitize_story_phone_width( 9999 ) );
        $this->assertSame( 220, navi_sanitize_story_phone_width( 220 ) );
    }

    public function test_sanitize_story_bubble_size_clamps_to_range() {
        $this->assertSame( NAVI_STORIES_MIN_BUBBLE_SIZE, navi_sanitize_story_bubble_size( 0 ) );
        $this->assertSame( NAVI_STORIES_MAX_BUBBLE_SIZE, navi_sanitize_story_bubble_size( 9999 ) );
        $this->assertSame( 80, navi_sanitize_story_bubble_size( 80 ) );
    }

    public function test_sanitize_story_border_style_accepts_known_values() {
        $this->assertSame( 'solid', navi_sanitize_story_border_style( 'solid' ) );
        $this->assertSame( 'gradient', navi_sanitize_story_border_style( 'gradient' ) );
    }

    public function test_sanitize_story_border_style_defaults_on_unknown_value() {
        $this->assertSame( NAVI_STORIES_DEFAULT_BUBBLE_BORDER_STYLE, navi_sanitize_story_border_style( 'dashed' ) );
        $this->assertSame( NAVI_STORIES_DEFAULT_BUBBLE_BORDER_STYLE, navi_sanitize_story_border_style( '' ) );
    }

    public function test_sanitize_story_gradient_angle_clamps_to_range() {
        $this->assertSame( 0, navi_sanitize_story_gradient_angle( -45 ) );
        $this->assertSame( 360, navi_sanitize_story_gradient_angle( 720 ) );
        $this->assertSame( 180, navi_sanitize_story_gradient_angle( 180 ) );
    }

    public function test_sanitize_story_video_zoom_clamps_to_range() {
        $this->assertSame( NAVI_STORIES_MIN_VIDEO_ZOOM, navi_sanitize_story_video_zoom( 0 ) );
        $this->assertSame( NAVI_STORIES_MAX_VIDEO_ZOOM, navi_sanitize_story_video_zoom( 9999 ) );
        $this->assertSame( 120, navi_sanitize_story_video_zoom( 120 ) );
    }
}
