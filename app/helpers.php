<?php

function get_ztoken()
{
    if ((config('app.env') == 'local' || config('app.env') == 'testing') && env('Z_TOKEN', null) != null) {
        return env('Z_TOKEN');
    }

    $accessToken = null;

    if ($user = \Cache::get('zUser', false)) {
        $accessToken = $user->access_token;
    }

    return $accessToken;
}

function rsi($rsiSourceInput, $rsiLengthInput, $previousData)
{
    $up = 0;
    $down = 0;

    $down = calculateDown($rsiSourceInput);
    $up = calculateUp($rsiSourceInput);

    if (count($previousData) == 0) {
        $up = ta_sma($up, $rsiLengthInput);
        $down = ta_sma($down, $rsiLengthInput);
    } else {
        // dd($previousData);
        $up = (($previousData['gain'] * ($rsiLengthInput-1)) + $up[count($up)-1] ) / $rsiLengthInput;
        $down = (($previousData['loss'] * ($rsiLengthInput-1)) + $down[count($down)-1] ) / $rsiLengthInput;

        // $up = sprintf("%.2f", $up);
        // $down = sprintf("%.2f", $down);

    }

    if ($down == 0) {
        $rsi = 100;
    } elseif ($up == 0) {
        $rsi = 0;
    } else {
        $rs = sprintf("%.4f", ($up / $down));
        $rsi = sprintf("%.2f", 100 - (100 / (1 + $rs)));
    }

    return ['gain' => $up, 'loss' => $down, 'rsi' => $rsi];
}

function ta_sma($input, $length)
{
    $sum = 0;

    for ($i = 0; $i < $length; $i++) {
        $sum += (float) $input[$i];
    }

    $avg = sprintf("%.2f", $sum / $length);

    return $avg;
}

function calculateDown($rsiSourceInput)
{
    $changes = [];
    for ($i = 1; $i < count($rsiSourceInput); $i++) {
        $changes[] = sprintf("%.2f", -min($rsiSourceInput[$i] - $rsiSourceInput[$i - 1], 0));
    }

    return $changes;
}

function calculateUp(array $rsiSourceInput)
{
    $changes = [];
    for ($i = 1; $i < count($rsiSourceInput); $i++) {
        $changes[] = sprintf("%.2f", max($rsiSourceInput[$i] - $rsiSourceInput[$i - 1], 0));
    }

    return $changes;
}