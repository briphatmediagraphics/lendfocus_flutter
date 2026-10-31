<?php

namespace Tests\Unit;

use App\Models\Property;
use Tests\TestCase;

class PropertyImageTest extends TestCase
{
    public function test_storage_paths_are_normalized_for_property_images(): void
    {
        $property = new Property([
            'images' => ['storage/property_images/demo.jpg'],
        ]);

        $this->assertSame('/storage/property_images/demo.jpg', $property->coverImage());
        $this->assertSame(['/storage/property_images/demo.jpg'], $property->imageUrls());
    }

    public function test_property_image_storage_folder_uses_property_specific_directory(): void
    {
        $controller = new class extends \App\Http\Controllers\AdminController {
            public function resolveStoragePath(?string $propertyId = null): string
            {
                return $this->imageStorageDirectory($propertyId);
            }
        };

        $this->assertSame('property_images', $controller->resolveStoragePath());
        $this->assertSame('property_images/prop-123', $controller->resolveStoragePath('prop-123'));
    }
}
