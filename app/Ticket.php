<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Ticket
 * @package App
 *
 * // Properties
 * @property int $id
 * @property int $concert_id
 * @property int $order_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * // Relations
 *
 * @mixin \Eloquent
 */
class Ticket extends Model
{
    protected $guarded = [];

    public function scopeAvailable($query)
    {
        return $query->whereNull('order_id');
    }

    public function release()
    {
        $this->update(['order_id' => null]);
    }
}
