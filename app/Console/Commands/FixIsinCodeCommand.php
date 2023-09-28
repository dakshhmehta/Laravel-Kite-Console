<?php

namespace App\Console\Commands;

use App\Instrument;
use App\Stock;
use Illuminate\Console\Command;

class FixIsinCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isin:fix-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the stocks data for the missing ISIN codes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $stocks = Stock::select('symbol')->whereNull('isin')->distinct()->get();

        foreach($stocks as &$s){
            $instrument = Instrument::getBySymbol($s->symbol);

            Stock::whereNull('isin')->where('symbol', $s)->update(['isin' => $instrument->isin_code]);

            $this->info($s->symbol.' updated');
        }

        return Command::SUCCESS;
    }
}
