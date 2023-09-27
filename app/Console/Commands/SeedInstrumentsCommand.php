<?php

namespace App\Console\Commands;

use App\Instrument;
use Illuminate\Console\Command;

class SeedInstrumentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:instruments {market} {--symbols=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed all instruments of the market.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $kite = app('kite');

        $instruments = collect($kite->getInstruments($this->argument('market')));


        $symbols = explode(',', $this->option('symbols'));

        foreach ($instruments as &$i) {
            dd($i);
            $instrument = Instrument::where('instrument_token', $i->instrument_token)->first();

            if ($instrument != null) { // Skip, already exist.
                continue;
            }

            if ($symbols[0] != 'all') {
                if (! in_array($i->tradingsymbol, $symbols)) {
                    continue;
                }
            }
            try {
                $instrument = Instrument::create([
                    'instrument_token' => $i->instrument_token,
                    'tradingsymbol'    => $i->tradingsymbol,
                    'exchange_token'   => $i->exchange_token,
                ]);

                $data = (array) $i;
                if ($data['expiry'] == '') {
                    $data['expiry'] = null;
                }

                $instrument->fill($data);
                $instrument->save();
                $this->info($i->tradingsymbol . ' saved');
            } catch (\Exception $e) {
                $this->error('Error processing ' . $i->tradingsymbol);
                $this->error($e->getMessage());
            }
        }
    }
}
