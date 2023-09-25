<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Instrument represents the entity of Zerodha Stock.
 *
 * All the seeded list of stocks, index etc fetched from the Zerodha's server
 * to persist in the database are represented as Instrument.
 *
 * @author Daksh Mehta <dakshhmehta@gmail.com>
 */
class Instrument extends Model
{
    protected $guarded = [];

    protected $dates = ['expiry'];

    /**
     * Returns the instrument given the symbol of the stock.
     *
     * @param  string $symbol
     * @return \App\Instrument
     */
    public static function getBySymbol($symbol)
    {
        return static::where('tradingsymbol', $symbol)->first();
    }
}
