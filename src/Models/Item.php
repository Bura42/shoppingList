<?php

namespace App\Models;

/**
 * Single shopping list item.
 * This is a simple Data Transfer Object (DTO).
 * Its only job is to hold data.
 */
class Item
{
    public ?int $id = null;
    public string $name;
    public bool $is_checked = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;
}
