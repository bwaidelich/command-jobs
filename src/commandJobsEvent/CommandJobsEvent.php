<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandJobsEvent;

use Stringable;

interface CommandJobsEvent extends Stringable
{
    public function severity(): CommandJobsEventSeverity;
}
