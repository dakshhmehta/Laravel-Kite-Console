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