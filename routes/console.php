<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('fetch:fuel-prices')
    ->everySixHours()
    ->timezone('Europe/Riga')
    ->withoutOverlapping();
