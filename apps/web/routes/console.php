<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('research:drafts:prune')->daily()->withoutOverlapping();
