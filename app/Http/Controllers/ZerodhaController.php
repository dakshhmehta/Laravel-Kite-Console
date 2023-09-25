<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use KiteConnect;

/**
 * A controller class that is used to display the dashboard / console on intraday monitor.
 *
 * This class is also responsible for handling the kite authentication and persist the access token.
 *
 * @see \get_ztoken
 *
 * @author Daksh Mehta <dakshhmehta@gmail.com>
 */
class ZerodhaController extends Controller
{
    protected $kite;

    public function __construct()
    {
        $this->kite = new KiteConnect(env('ZERODHA_API_KEY'), get_ztoken());
    }

    /**
     * Displays the console to user.
     */
    public function getConsole()
    {
        $lastTradedDay = Stock::select('updated_at')->where('intraday_power', '>', 0)->orderBy('id', 'DESC')->first();
        $nifty = Stock::getBySymbol('NIFTY 50');

        return view('intraday::dashboard', compact('lastTradedDay', 'nifty'));
    }

    /**
     * Redirects the admin to zerodha.com for authentication.
     */
    public function login()
    {
        return redirect()->to($this->kite->getLoginURL());
    }

    /**
     * Receive and persist the zerodha access token for use throughout the day.
     */
    public function handle(Request $request)
    {
        if ($request->status != 'success') {
            return abort(404);
        }

        $user = $this->kite->generateSession($request->request_token, env('ZERODHA_API_SECRET'));
        \Cache::put('zUser', $user, Carbon::now()->addHours(12));

        // phpcs:disable
        dd(\Cache::get('zUser'));
        // phpcs:enable
    }
}
