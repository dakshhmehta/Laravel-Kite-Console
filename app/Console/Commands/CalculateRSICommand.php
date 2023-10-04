<?php

namespace App\Console\Commands;

use App\Instrument;
use App\Models\DailyRSI;
use App\Models\MonthlyRSI;
use App\Models\WeeklyRSI;
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
    protected $signature = 'rsi:calculate {type=daily} {--symbols=all}';

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
        $type = $this->argument('type');

        if ($this->option('symbols') == 'all') {
            $symbols = Stock::getAllSymbols();
        } else {
            $symbols = explode(',', $this->option('symbols'));
        }

        if ($type == 'monthly') {
            $this->calculateMonthly($symbols);
        } elseif ($type == 'daily') {
            $this->calculateDaily($symbols);
        } elseif ($type == 'weekly') {
            $this->calculateWeekly($symbols);
        } else {
            $this->error('Invalid type argument.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function calculateDaily($symbols)
    {
        foreach ($symbols as &$symbol) {
            $odata = Stock::where('symbol', $symbol)->orderBy('date', 'ASC')->get();

            $period = 14;
            $previous = [];

            $this->error($symbol);

            for ($i = $period; $i < count($odata); $i++) {
                $data = $odata->pluck('close')->toArray();
                $data = array_splice($data, $i - $period, $period + 1);

                $rsi = rsi($data, $period, $previous);

                $dailyRSI = DailyRSI::firstOrNew([
                    'isin' => $odata[$i]->getInstrument()->isin_code,
                    'date' => $odata[$i]->date->format('Y-m-d'),
                ]);
                $dailyRSI->rsi = $rsi['rsi'];
                $dailyRSI->gain = $rsi['gain'];
                $dailyRSI->loss = $rsi['loss'];
                $dailyRSI->save();

                $this->info("RSI (14, " . $data[count($data) - 1] . ", " . $odata[$i]->date->format('d-m-Y') . "): " . $rsi['rsi']);
                $previous = $rsi;
            }
        }
    }

    public function calculateMonthly($symbols)
    {
        foreach ($symbols as &$symbol) {
            $instrument = Instrument::getBySymbol($symbol);
            $odata = collect(Stock::allMonthlyCandles($symbol));

            $period = 14;
            $previous = [];

            $this->error($symbol);

            for ($i = $period; $i < count($odata); $i++) {
                $data = $odata->pluck('close')->toArray();
                $data = array_splice($data, $i - $period, $period + 1);

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
                $this->warn($odata[$i]->open . ', ' . $odata[$i]->high . ', ' . $odata[$i]->low . ', ' . $odata[$i]->close);

                $previous = $rsi;
            }
        }
    }

    public function calculateWeekly($symbols)
    {
        foreach ($symbols as &$symbol) {
            $instrument = Instrument::getBySymbol($symbol);
            $odata = collect(Stock::allWeeklyCandles($symbol));

            $period = 14;
            $previous = [];

            $this->error($symbol);

            for ($i = $period; $i < count($odata); $i++) {
                $data = $odata->pluck('close')->toArray();
                $data = array_splice($data, $i - $period, $period + 1);

                $rsi = rsi($data, $period, $previous);

                $weeklyRSI = WeeklyRSI::firstOrNew([
                    'isin' => $instrument->isin_code,
                    'date' => $odata[$i]->date->format('Y-m-d'),
                ]);
                $weeklyRSI->rsi = $rsi['rsi'];
                $weeklyRSI->gain = $rsi['gain'];
                $weeklyRSI->loss = $rsi['loss'];
                $weeklyRSI->save();

                $this->info("RSI (14, " . $data[count($data) - 1] . ", " . $odata[$i]->date->format('d-m-Y') . "): " . $rsi['rsi']);
                $this->warn($odata[$i]->open . ', ' . $odata[$i]->high . ', ' . $odata[$i]->low . ', ' . $odata[$i]->close);

                $previous = $rsi;
            }
        }
    }
}
