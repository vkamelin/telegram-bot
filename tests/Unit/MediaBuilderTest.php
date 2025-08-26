<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\MediaBuilder;
use PHPUnit\Framework\TestCase;

final class MediaBuilderTest extends TestCase
{
    public function testParseModeNullNotAdded(): void
    {
        $media = MediaBuilder::buildInputMedia('photo', 'https://example.com/a.jpg', [
            'caption' => 'One',
            'parse_mode' => null,
        ]);

        $this->assertArrayHasKey('caption', $media);
        $this->assertArrayNotHasKey('parse_mode', $media);
    }

    public function testPrepareMediaDataFiltersNull(): void
    {
        $data = MediaBuilder::prepareMediaData(123, 'video', 'https://example.com/b.mp4', 'Video', [
            'width' => 640,
            'height' => null,
            'supports_streaming' => null,
        ]);

        $this->assertArrayHasKey('width', $data);
        $this->assertArrayNotHasKey('height', $data);
        $this->assertArrayNotHasKey('supports_streaming', $data);
    }
}
