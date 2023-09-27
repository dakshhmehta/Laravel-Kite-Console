<?php

namespace App;

use App\Instrument;
use App\Jobs\SaveMonitorData;
use App\Stock;
use Carbon\Carbon;

/**
 * A heart and very first class created when project was started.
 * This class manages to fetch the data to be saved at the end of the day.
 *
 * @see \App\Console\Commands\SaveIntradayCommand
 *
 * @author Daksh Mehta <dakshhmehta@gmail.com>
 */
class Monitor
{
    /**
     * Kite Client
     * @var \KiteConnect
     */
    private $kite;

    /**
     * Days to which receives the data.
     * @var integer
     */
    private $days;

    /**
     * List of symbols.
     * @var array
     */
    private $symbols;

    protected $limit = -1;
    protected $offset = -1;

    protected $result = [];

    /**
     * Intializes the monitor.
     * @param integer $days
     * @param array   $symbols
     */
    public function __construct($days = 1, $symbols = [], $limit, $offset)
    {
        $this->kite    = app('kite');
        $this->days    = $days;
        $this->symbols = $symbols;

        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function run()
    {
        if (is_array($this->symbols) && count($this->symbols) > 0) {
            $stocks = $this->symbols;
        } else {
            $stocks = Stock::getAllSymbols();
        }

        $this->result = Instrument::where(function ($q) {
            $q->where('instrument_type', 'EQ');
            $q->orWhere('segment', 'INDICES');
        })
            ->select('instrument_token', 'tradingsymbol')
            ->where('exchange', 'NSE')->whereIn('tradingsymbol', $stocks)
            ->orderBy('tradingsymbol', 'ASC');

        if ($this->limit != -1) {
            $this->result = $this->result->take($this->limit);
        }

        if ($this->offset != -1) {
            $this->result = $this->result->skip($this->offset);
        }

        $this->result = $this->result->get();

        return $this->result;
    }

    /**
     * Fetches the data using KiteConnect and prepares it in the form of candle and stock.
     *
     * @see  \App\Candle
     * @return \App\Stock[]
     */
    public function getStocks()
    {
        $stocks = collect([]);

        
        foreach ($this->result as &$s) {
            $fromDate = Carbon::parse('1993-01-01');
            $tillDate = (clone $fromDate)->endOfYear();

            echo 'Queued: ' . $s->tradingsymbol . "\n";
            while($fromDate->lte(now())){
                echo 'Storing for '.$fromDate->format('Y-m-d').' '.$tillDate->format('Y-m-d')."\n";

                dispatch(new SaveMonitorData($s->instrument_token, $fromDate, $tillDate));
                $fromDate->addYears(1);
                $tillDate->addYears(1);
            }

            //            sleep(5);
        }

        return $stocks;
    }
}
