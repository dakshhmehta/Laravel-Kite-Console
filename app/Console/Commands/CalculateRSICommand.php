<?php

namespace App\Console\Commands;

use App\Stock;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateRSICommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsi:calculate {month=current}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate RSI for the month';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $symbols = Stock::getAllSymbols();
        $symbols = ['ADANIPORTS'];

        foreach ($symbols as &$symbol) {
            $odata = Stock::where('symbol', $symbol)->orderBy('date', 'ASC')->get();

            $period = 14;
            $previous = [];

            $this->error($symbol);

            for ($i = $period; $i < count($odata); $i++) {
                $data = $odata->pluck('close')->toArray();
                $data = array_splice($data, $i - $period, $period + 1);
                $rsi = rsi($data, $period, $previous);

                $this->info("RSI (14, " . $data[count($data) - 1] . ", ".$odata[$i]->date->format('d-m-Y')."): " . $rsi['rsi']);
                $previous = $rsi;
            }
        }

        return Command::SUCCESS;
    }
}
