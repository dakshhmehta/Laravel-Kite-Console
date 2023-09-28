<?php

namespace App;

use Carbon\Carbon;
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

    public function cleanDuplicates()
    {
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

    public function getInstrument()
    {
        $i = Instrument::where('instrument_type', 'EQ')
            ->where(function ($q) {
                $q->whereIn('segment', ['NSE', 'INDICES']);
            })->where('tradingsymbol', $this->symbol)
            ->orderBy('tradingsymbol', 'ASC')
            ->first();

        try {
            return $i;
        } catch (\Exception $e) {
            throw new \Exception("Instrument ID not found for " . $this->symbol);
        }
    }

    public function getIsinAttribute($val)
    {
        if ($val) {
            return $val;
        }

        $instrument = $this->getInstrument();
        $this->isin = $instrument->isin_code;
        $this->save();

        return $instrument->isin_code;
    }

    public function getMonthlyCandles($count = 1)
    {
        $key = md5(__METHOD__ . $this->getCacheKey() . $count);

        if ($candles = \Cache::get($key, false)) {
            return $candles;
        }

        if (Carbon::now()->endOfMonth()->isToday()) {
            $previousMonth = Carbon::now()->startOfMonth();
        } else {
            $previousMonth = Carbon::now()->subMonths(1)->startOfMonth();
        }
        $candles = collect([]);


        while ($count > 0) {
            $date  = clone $previousMonth;
            $rates = static::where('symbol', $this->symbol)
                ->where('date', '>=', $date->startOfMonth()->format('Y-m-d'))
                ->where('date', '<=', $date->endOfMonth()->format('Y-m-d'))
                ->orderBy('date', 'ASC')->get();

            if ($rates->count() == 0) {
                break;
            }

            $c = [];
            foreach ($rates as &$rate) {
                $c[] = ['open' => $rate->open, 'high' => $rate->high, 'low' => $rate->low, 'close' => $rate->close];
            }

            $candles[] = new Candle($c, $date->startOfMonth());

            $count--;
            $previousMonth = $previousMonth->subMonths(1);
        }

        $candles = array_values($candles->reverse()->toArray());

        \Cache::put($key, $candles, config('app.cache_ttl'));

        return $candles;
    }

    public static function allMonthlyCandles($symbol)
    {
        $data = static::getBySymbol($symbol);

        $fromDate = Carbon::parse(config('app.from_date'));
        $tillDate = Carbon::now();

        $candles = $tillDate->diffInMonths($fromDate);

        return $data->getMonthlyCandles($candles);
    }

    public function getWeeklyCandles($count = 1)
    {
        $key = md5(__METHOD__ . $this->getCacheKey() . $count);
        if ($candles = \Cache::get($key, false)) {
            return $candles;
        }

        if (Carbon::now()->isWeekend() || (Carbon::now()->isFriday() and Carbon::now()->hour > 15)) {
            $previousWeek = Carbon::now()->startOfWeek();
        } else {
            $previousWeek = Carbon::now()->subWeeks(1)->startOfWeek();
        }

        $candles = collect([]);

        while ($count > 0) {
            $date  = clone $previousWeek;
            $rates = static::where('symbol', $this->symbol)
                ->where('date', '>=', $date->startOfWeek()->format('Y-m-d'))
                ->where('date', '<=', $date->endOfWeek()->format('Y-m-d'))
                ->orderBy('date', 'ASC')->get();

            if ($rates->count() == 0) {
                break;
            }

            $c = [];
            foreach ($rates as &$rate) {
                $c[] = ['open' => $rate->open, 'high' => $rate->high, 'low' => $rate->low, 'close' => $rate->close];
            }

            $candles[] = new Candle($c, $date->startOfWeek());

            $count--;
            $previousWeek = $previousWeek->subWeeks(1);
        }

        $candles = array_values($candles->reverse()->toArray());
        \Cache::put($key, $candles, config('app.cache_ttl'));

        return $candles;
    }

    public static function allWeeklyCandles($symbol)
    {
        $data = static::getBySymbol($symbol);

        $fromDate = Carbon::parse(config('app.from_date'));
        $tillDate = Carbon::now();

        $candles = $tillDate->diffInWeeks($fromDate);

        return $data->getWeeklyCandles($candles);
    }
}
