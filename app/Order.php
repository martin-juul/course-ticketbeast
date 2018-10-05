<?php

namespace App;

use App\Facades\OrderConfirmationNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
 * @property string $confirmation_number
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

    public static function forTickets(Collection $tickets, string $email, Charge $charge)
    {
        $order = self::create([
            'confirmation_number' => OrderConfirmationNumber::generate(),
            'email' => $email,
            'amount' => $charge->amount(),
            'card_last_four' => $charge->cardLastFour(),
        ]);

        $tickets->each->claimFor($order);

        return $order;
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public static function findByConfirmationNumber(string $confirmationNumber)
    {
        return self::where('confirmation_number', $confirmationNumber)->firstOrFail();
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function ticketQuantity()
    {
        return $this->tickets()->count();
    }

    public function toArray()
    {
        return [
            'confirmation_number' => $this->confirmation_number,
            'email' => $this->email,
            'amount' => $this->amount,
            'tickets' => $this->tickets->map(function (Ticket $ticket) {
                return ['code' => $ticket->code];
            })->all(),
        ];
    }
}
