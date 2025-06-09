<?php

namespace Tests\Unit;

use App\Models\Item;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /** @test */
    public function test_it_can_be_instantiated_with_properties(): void
    {
        $item = new Item();
        $item->name = 'Test Item';
        $item->is_checked = true;

        $this->assertSame('Test Item', $item->name);
        $this->assertTrue($item->is_checked);
        $this->assertNull($item->id);
    }
}
