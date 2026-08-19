<?php

use PHPUnit\Framework\TestCase;

final class StoriesDataTest extends TestCase {

    public function test_extract_youtube_id_from_watch_url() {
        $this->assertSame( 'dQw4w9WgXcQ', navi_extract_youtube_id( 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' ) );
    }

    public function test_extract_youtube_id_from_watch_url_with_extra_params() {
        $this->assertSame( 'dQw4w9WgXcQ', navi_extract_youtube_id( 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&list=RD123' ) );
    }

    public function test_extract_youtube_id_from_short_url() {
        $this->assertSame( 'dQw4w9WgXcQ', navi_extract_youtube_id( 'https://youtu.be/dQw4w9WgXcQ' ) );
    }

    public function test_extract_youtube_id_from_shorts_url() {
        $this->assertSame( 'dQw4w9WgXcQ', navi_extract_youtube_id( 'https://www.youtube.com/shorts/dQw4w9WgXcQ' ) );
    }

    public function test_extract_youtube_id_from_raw_id() {
        $this->assertSame( 'dQw4w9WgXcQ', navi_extract_youtube_id( 'dQw4w9WgXcQ' ) );
    }

    public function test_extract_youtube_id_trims_whitespace() {
        $this->assertSame( 'dQw4w9WgXcQ', navi_extract_youtube_id( '  dQw4w9WgXcQ  ' ) );
    }

    public function test_extract_youtube_id_returns_empty_for_empty_input() {
        $this->assertSame( '', navi_extract_youtube_id( '' ) );
    }

    public function test_extract_youtube_id_returns_empty_for_invalid_input() {
        $this->assertSame( '', navi_extract_youtube_id( 'not a youtube url or id' ) );
    }

    public function test_extract_youtube_id_returns_empty_for_wrong_length_id() {
        $this->assertSame( '', navi_extract_youtube_id( 'tooshort' ) );
    }

    public function test_extract_youtube_id_returns_empty_for_unrelated_url() {
        $this->assertSame( '', navi_extract_youtube_id( 'https://example.com/video/dQw4w9WgXcQ' ) );
    }
}
