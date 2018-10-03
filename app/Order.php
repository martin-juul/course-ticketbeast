<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 * @package App
 *
 * // Mixins
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 *
 * // Properties
 * @property int $id
 * @property int $concert_id
 * @property int $amount
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * // Relations
 * @property-read Concert $concert
 * @property-read Ticket $tickets
 */
class Order extends Model
{
    protected $guarded = [];

    public static function forTickets($tickets, $email, $amount)
    {
        $order = self::create([
            'email' => $email,
            'amount' => $amount,
        ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public static function fromReservation(Reservation $reservation)
    {
        $order = self::create([
            'email' => $reservation->email(),
            'amount' => $reservation->totalCost(),
        ]);

        $order->tickets()->saveMany($reservation->tickets());

        return $order;
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketQuantity()
    {
        if ($this->relationLoaded('tickets')) {
            return $this->tickets->count();
        }

        return $this->tickets()->count();
    }

    public function toArray()
    {
        return [
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'amount' => $this->amount,
        ];
    }
}
