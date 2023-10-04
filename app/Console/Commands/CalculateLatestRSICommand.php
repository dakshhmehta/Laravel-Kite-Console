<?php

namespace App\Console\Commands;

use App\Instrument;
use App\Models\DailyRSI;
use App\Models\MonthlyRSI;
use App\Models\WeeklyRSI;
use App\Stock;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateLatestRSICommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsi:calculate-latest {type=daily} {--symbols=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate RSI for the latest data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type');

        if ($this->option('symbols') == 'all') {
            $symbols = Stock::getAllSymbols();
        } else {
            $symbols = explode(',', $this->option('symbols'));
        }

        if ($type == 'monthly') {
            $this->calculateMonthly($symbols);
        } elseif ($type == 'daily') {
            $this->error('Yet to be developed.');
            // $this->calculateDaily($symbols);
        } elseif ($type == 'weekly') {
            $this->error('Yet to be  developed.');
            $this->calculateWeekly($symbols);
        } else {
            $this->error('Invalid type argument.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function calculateDaily($symbols)
    {
    }

    public function calculateMonthly($symbols)
    {
        foreach ($symbols as &$symbol) {
            $instrument = Instrument::getBySymbol($symbol);
            $odata = collect(Stock::getBySymbol($symbol)->getMonthlyCandles(15));

            $period = 14;
            $previous = [];

            $this->error($symbol);

            $i = $odata->count() - 1;

            $data = $odata->pluck('close')->toArray();
            $lastCandle = $odata[$odata->count() - 2];
            $previousRSI = MonthlyRSI::where('date', $lastCandle->date)->first();
            if(! $previousRSI){
                $this->error('No previous month RSI found for '.$symbol);
                continue;
            }

            $previous = [
                'rsi' => $previousRSI->rsi,
                'gain' => $previousRSI->gain,
                'loss' => $previousRSI->loss,
            ];

            $rsi = rsi($data, $period, $previous);

            $monthlyRSI = MonthlyRSI::firstOrNew([
                'isin' => $instrument->isin_code,
                'date' => $odata[$i]->date->format('Y-m-d'),
            ]);
            $monthlyRSI->rsi = $rsi['rsi'];
            $monthlyRSI->gain = $rsi['gain'];
            $monthlyRSI->loss = $rsi['loss'];
            $monthlyRSI->save();

            $this->info("RSI (14, " . $data[count($data) - 1] . ", " . $odata[$i]->date->format('d-m-Y') . "): " . $rsi['rsi']);
        }
    }

    public function calculateWeekly($symbols)
    {
    }
}
