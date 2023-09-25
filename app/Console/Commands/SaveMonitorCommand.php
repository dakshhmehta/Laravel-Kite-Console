<?php

namespace App\Console\Commands;

use App\Monitor;
use App\Notifications\SendSMS;
use App\Stock;
use App\User;
use Illuminate\Console\Command;

/**
 * This command is used to fetch the End of the day data for all the stocks
 * for https://udtsmonitor.com and https://intraday.udtsmonitor.com
 *
 * The command is executed by cronjob on the server every day from Monday to Friday at 07:30 PM.
 *
 * <p>Command is <b>php artisan monitor:save</b></p>
 *
 * <p>In case if a stock shows incorrect value on UDTSMonitor for day/week/month, It is possible to fix the same
 * with this command.
 *
 * For example, if INFY shows wrong data and needs a correction, we can pull up the data again anytime for the same<br/>
 * <B>php artisan monitor:save --symbols=INFY,HDFCBANK</b></p>
 *
 * <p>It is also possible to fetch data of past days for combinations of symbols by combining <b>--days</b> input.<Br/>
 * If we extend the example above,<br/>
 *
 * <b>php artisan monitor:save --symbols=INFY,HDFCBANK --days=30</b><br/>
 *
 * <p>The above command will refresh the data of last 30 days for Infosys and HDFC Bank.</p>
 *
 * @author Daksh Mehta <dakshhmehta@gmail.com>
 */
class SaveMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:save {--days=1} {--symbols=all} {--limit=-1} {--offset=-1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save monitored stocks to database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getSymbols($option)
    {
        $data['nifty50'] = 'ICICIBANK,CIPLA,RELIANCE,MARUTI,IOC,TATASTEEL,IBULHSGFIN,BRITANNIA,TATAMOTORS,JSWSTEEL,VEDL,GRASIM,EICHERMOT,BAJFINANCE,POWERGRID,DRREDDY,M&M,HINDALCO,ASIANPAINT,BAJAJFINSV,GAIL,ZEEL,TCS,SBIN,LT,YESBANK,KOTAKBANK,TITAN,NTPC,ONGC,INFRATEL,INDUSINDBK,HINDUNILVR,WIPRO,ULTRACEMCO,BAJAJ-AUTO,HCLTECH,UPL,HDFCBANK,HDFC,BPCL,SUNPHARMA,TECHM,ITC,BHARTIARTL,HEROMOTOCO,INFY,ADANIPORTS,COALINDIA,AXISBANK';

        $symbols = $option;

        if (isset($data[$option])) {
            $symbols = $data[$option];
        }


        return (($option == 'all') ? [] : explode(',', $symbols));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $symbols = $this->getSymbols($this->option('symbols'));

        $monitor = new Monitor($this->option('days'), $symbols, $this->option('limit'), $this->option('offset'));

        $monitor->run();

        config()->set(['cache.default' => 'array']);
        $counter = 0;

        $monitor->getStocks();
        // foreach ($monitor->getStocks() as &$stock) {
        //     $this->info('Saving ' . $stock->symbol . ' for ' . $stock->date->format('d M, Y'));
        //     $stock->save();
        //     Stock::persistTrend($stock->symbol);
        //     $counter++;
        // }

        // $admin = User::admins()->first();
        // $admin->notify(new SendSMS("Monitor ran successfully."));
    }
}
