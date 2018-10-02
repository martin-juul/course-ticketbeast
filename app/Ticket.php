<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Ticket
 * @package App
 *
 * // Mixins
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 *
 * // Properties
 * @property int $id
 * @property int $concert_id
 * @property int $order_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * // Relations
 * @property-read Concert $concert
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

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function getPriceAttribute()
    {
        return $this->concert->ticket_price;
    }
}
