<?php

namespace Winnipass;

use DateTime;
use Carbon\Carbon;
use Winnipass\Exceptions\InvalidPeriod;

class Period
{
    /** @var \DateTime */
    public $startDate;

    /** @var \DateTime */
    public $endDate;

    public static function create(DateTime $startDate, $endDate): Period
    {
        return new static($startDate, $endDate);
    }

    public static function days(int $numberOfDays): Period
    {
        $endDate = Carbon::today();

        $startDate = Carbon::today()->subDays($numberOfDays)->startOfDay();

        return new static($startDate, $endDate);
    }

    public function __construct(DateTime $startDate, DateTime $endDate)
    {
        if ($startDate > $endDate) {
            throw InvalidPeriod::startDateCannotBeAfterEndDate($startDate, $endDate);
        }

        $this->startDate = $startDate;

        $this->endDate = $endDate;
    }
}
