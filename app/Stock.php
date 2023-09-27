<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * When a stock is being saved to database.
 * 
 * @author Daksh Mehta <dakshhmehta@gmail.com>
 */
class Stock extends Model
{
    protected $guarded = [];
    protected $dates   = ['date'];

    const DAY_CANDLES   = 10;
    const WEEK_CANDLES  = 8;
    const MONTH_CANDLES = 12;

    protected $casts = [
        'date' => 'date',
        'intraday_trend' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(
            function ($stock) {
                if (is_numeric($stock->id)) {
                    static::where('symbol', $stock->symbol)
                        ->where('date', $stock->date->format('Y-m-d'))->where('id', '!=', $stock->id)->delete();
                }

                return true;
            }
        );
    }

    public function cleanDuplicates(){
        return static::where('symbol', $this->symbol)
            ->where('date', $this->date->format('Y-m-d'))->where('id', '!=', $this->id)->delete();
    }

    /**
     * Cache key used to fast up the execution of other functions
     * of this class.
     *
     * <p>A cache key gets invalidated and refreshed when any data is refreshed throughout the day.</p>
     *
     * @return string.
     *
     * @author Daksh Mehta <dakshhmehta@gmail.com>
     */
    public function getCacheKey()
    {
        return $this->id . $this->symbol . $this->updated_at;
    }

    /**
     * Returns all the valid symbols we have in database for each operations to operate on.
     * @return array
     */
    public static function getAllSymbols()
    {
        return static::select('symbol')->distinct()
            ->orderBy('symbol', 'ASC')->get()->pluck('symbol')->toArray();
    }

    /**
     * Return the most new (latest by date) stock representation of given symbol code.
     *
     * @param  string $code NSE symbol of the stock. For example, Infosys is INFY.
     * @return \App\Stock
     */
    public static function getBySymbol($code)
    {
        return static::where('symbol', $code)->orderBy('date', 'DESC')->orderBy('updated_at', 'DESC')->first();
    }
}
