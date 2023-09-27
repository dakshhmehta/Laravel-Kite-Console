<?php

namespace App\Console\Commands;

use App\Stock;
use Illuminate\Console\Command;

class CleanStocksDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean stocks data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $symbols = ['ADANIPORTS'];

        $data = Stock::select('id', 'date', 'symbol')->distinct()->whereIn('symbol', $symbols)->get();

        foreach ($data as &$row) {
            $row->cleanDuplicates();

            $this->info($row->symbol.' for '.$row->date->format('d-m-Y').' removed duplicates');
        }

        return Command::SUCCESS;
    }
}
