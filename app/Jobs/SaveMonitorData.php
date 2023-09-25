<?php

namespace App\Jobs;

use App\Instrument;
use App\Stock;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SaveMonitorData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $s, $from, $to;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($s, $from, $to)
    {
        $this->s = $s;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $kite    = app('kite');
        $this->s = Instrument::where('instrument_token', $this->s)->first();
        $data = $kite->getHistoricalData($this->s->instrument_token, 'day', $this->from, $this->to);

        foreach ($data as &$d) {
            $stock = new Stock(
                [
                    'symbol' => trim($this->s->tradingsymbol),
                    'date'   => Carbon::parse($d->date),
                    'open'   => $d->open,
                    'high'   => $d->high,
                    'low'    => $d->low,
                    'close'  => $d->close,
                ]
            );

            $stock->save();
        }
    }
}
