<?php

namespace Database\Seeders;

use App\Instrument;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ISINSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $open = fopen(__DIR__ . "/equities.csv", "r");
        $array = [];
        $i = 0;
        while (($data = fgetcsv($open, 1000, ",")) !== FALSE) {
            if ($i++ == 0) continue;

            $array[] = $data;
        }
        fclose($open);

        foreach ($array as &$symbol) {
            $stock = Instrument::where('tradingsymbol', $symbol[0])->first();

            if ($stock) {
                $stock->isin_code = $symbol[6];
                $stock->save();

                $this->command->info($stock->tradingsymbol . ' is updated with ISIN ' . $symbol[6]);
            } else {
                $this->command->error($symbol[0] . ' not found');
            }
        }
    }
}
