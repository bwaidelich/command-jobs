<?php

declare(strict_types=1);

namespace wwwision\commandJobs\commandJobsEvent;

enum CommandJobsEventSeverity: int
{
    case DEBUG = 1;
    case INFO = 2;
    case ERROR = 4;
}
