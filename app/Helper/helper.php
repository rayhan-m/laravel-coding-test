<?php

use Carbon\Carbon;

function dateFormat($date)
{
    return Carbon::parse($date)->format('d-M-Y');
}