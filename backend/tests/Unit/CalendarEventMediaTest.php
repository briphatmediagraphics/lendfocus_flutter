<?php

namespace Tests\Unit;

use App\Models\CalendarEvent;
use Tests\TestCase;

class CalendarEventMediaTest extends TestCase
{
    public function test_media_url_normalization_and_gallery_type_detection(): void
    {
        $event = new CalendarEvent([
            'media' => [
                'storage/event_media/event_1.jpg',
                '/storage/event_media/event_2.png',
                'https://example.com/uploads/event_3.mp4',
                'event_media/event_4.mov',
                null,
            ],
        ]);

        $urls = $event->mediaUrls();
        $this->assertCount(4, $urls);
        $this->assertSame('/storage/event_media/event_1.jpg', $urls[0]);
        $this->assertSame('/storage/event_media/event_2.png', $urls[1]);
        $this->assertSame('https://example.com/uploads/event_3.mp4', $urls[2]);
        $this->assertSame('/storage/event_media/event_4.mov', $urls[3]);

        $gallery = $event->mediaGallery();
        $this->assertCount(4, $gallery);
        $this->assertSame('image', $gallery[0]['type']);
        $this->assertSame('video', $gallery[2]['type']);
    }
}
