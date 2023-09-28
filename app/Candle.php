<?php

namespace App;

/**
 * A candle class represents the candle with OPEN, HIGH, LOW and CLOSE.
 *
 * If open is given as array of candles, It will combine those candles
 * and will form a single candle.
 *
 * This is useful in creating combining candles of days to form a week candle
 * and same to form a month candle.
 *
 * @author Daksh Mehta <dakshhmehta@gmail.com>
 */
class Candle
{
    /**
     * Open value
     * @var float
     */
    public $open;

    /**
     * High value
     * @var float
     */
    public $high;

    /**
     * Low value
     * @var float
     */
    public $low;

    /**
     * Close value
     * @var float
     */
    public $close;

    /**
     * Date for which this candle represents.
     * @internal
     * @var \Carbon\Carbon
     */
    public $date;

    /**
     * Intialize the candle with data.
     * @param float|\App\Candle[] $open  Float value or the array of candles to form combined candle.
     * @param float $high
     * @param float $low
     * @param float $close
     * @param float $date
     */
    public function __construct($open, $high = null, $low = null, $close = null, $date = null)
    {
        if (is_array($open)) {
            $this->open  = $open[0]['open'];
            $this->close = $open[count($open) - 1]['close'];

            $this->high = $this->open;
            $this->low  = $this->close;

            foreach ($open as &$rate) {
                if ($this->high < $rate['high']) {
                    $this->high = $rate['high'];
                }

                if ($this->low > $rate['low']) {
                    $this->low = $rate['low'];
                }
            }

            $this->date = $high;
        } else {
            $this->open  = $open;
            $this->high  = $high;
            $this->low   = $low;
            $this->close = $close;

            $this->date = $date;
        }
    }

    /**
     * Check whatever candle is bullish.
     * A doji is also considered as bullish for sake of simplicity.
     *
     * @return boolean
     */
    public function isBullish(): bool
    {
        return $this->close >= $this->open;
    }

    /**
     * Check whatever candle is bearish.
     * @return boolean
     */
    public function isBearish(): bool
    {
        return $this->close < $this->open;
    }
}
