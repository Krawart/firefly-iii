<?php

namespace FireflyIII\Shared\Toolkit;

use Carbon\Carbon;
use FireflyIII\Exception\FireflyException;

/**
 * Class Filter
 *
 * @package FireflyIII\Shared\Toolkit
 */
class Filter
{
    /**
     * Save Session::get('start') and Session::get('end') for other methods to use.
     */
    public function setSessionDateRange()
    {
        $range  = $this->setSessionRangeValue();
        $start  = \Session::has('start') ? \Session::get('start') : new Carbon;
        $start  = $this->updateStartDate($range, $start);
        $end    = $this->updateEndDate($range, $start);
        $period = $this->periodName($range, $start);
        $prev   = $this->previous($range, clone $start);
        $next   = $this->next($range, clone $start);

        \Session::put('start', $start);
        \Session::put('end', $end);
        \Session::put('range', $range);
        \Session::put('period', $period);
        \Session::put('prev', $this->periodName($range, $prev));
        \Session::put('next', $this->periodName($range, $next));

        return null;

    }

    /**
     * Checks and sets the currently set 'range' or defaults to a session
     * and if that fails, defaults to 1M. Always returns the final value.
     *
     * @return string
     */
    public function setSessionRangeValue()
    {
        if (!is_null(\Session::get('range'))) {
            $range = \Session::get('range');
        } else {
            /** @var \FireflyIII\Shared\Preferences\PreferencesInterface $preferences */
            $preferences = \App::make('FireflyIII\Shared\Preferences\PreferencesInterface');
            $viewRange   = $preferences->get('viewRange', '1M');

            // default range:
            $range = $viewRange->data;
            \Session::put('range', $range);
        }

        return $range;

    }

    /**
     * @param        $range
     * @param Carbon $start
     *
     * @return Carbon
     * @throws FireflyException
     */
    protected function updateStartDate($range, Carbon $start)
    {
        switch ($range) {
            default:
                throw new FireflyException('updateStartDate cannot handle $range ' . $range);
                break;
            case '1D':
                $start->startOfDay();
                break;
            case '1W':
                $start->startOfWeek();
                break;
            case '1M':
                $start->startOfMonth();
                break;
            case '3M':
                $start->firstOfQuarter();
                break;
            case '6M':
                if (intval($start->format('m')) >= 7) {
                    $start->startOfYear()->addMonths(6);
                } else {
                    $start->startOfYear();
                }
                break;
            case '1Y':
                $start->startOfYear();
                break;
        }

        return $start;

    }

    /**
     * @param        $range
     * @param Carbon $start
     *
     * @return Carbon
     * @throws FireflyException
     */
    protected function updateEndDate($range, Carbon $start)
    {
        $end = clone $start;
        switch ($range) {
            default:
                throw new FireflyException('updateEndDate cannot handle $range ' . $range);
                break;
            case '1D':
                $end->endOfDay();
                break;
            case '1W':
                $end->endOfWeek();
                break;
            case '1M':
                $end->endOfMonth();
                break;
            case '3M':
                $end->lastOfQuarter();
                break;
            case '6M':
                if (intval($start->format('m')) >= 7) {
                    $end->endOfYear();
                } else {
                    $end->startOfYear()->addMonths(6);
                }
                break;
            case '1Y':
                $end->endOfYear();
                break;

        }

        return $end;
    }

    /**
     * @param        $range
     * @param Carbon $date
     *
     * @return string
     * @throws FireflyException
     */
    protected function periodName($range, Carbon $date)
    {
        switch ($range) {
            default:
                throw new FireflyException('No _periodName() for range "' . $range . '"');
                break;
            case '1D':
                return $date->format('jS F Y');
                break;
            case '1W':
                return 'week ' . $date->format('W, Y');
                break;
            case '1M':
                return $date->format('F Y');
                break;
            case '3M':
                $month = intval($date->format('m'));

                return 'Q' . ceil(($month / 12) * 4) . ' ' . $date->format('Y');
                break;
            case '6M':
                $month    = intval($date->format('m'));
                $half     = ceil(($month / 12) * 2);
                $halfName = $half == 1 ? 'first' : 'second';

                return $halfName . ' half of ' . $date->format('d-m-Y');
                break;
            case '1Y':
                return $date->format('Y');
                break;


        }
    }

    /**
     * @param        $range
     * @param Carbon $date
     *
     * @return Carbon
     * @throws FireflyException
     */
    public function previous($range, Carbon $date)
    {
        switch ($range) {
            default:
                throw new FireflyException('Cannot do _previous() on ' . $range);
                break;
            case '1D':
                $date->startOfDay()->subDay();
                break;
            case '1W':
                $date->startOfWeek()->subWeek();
                break;
            case '1M':
                $date->startOfMonth()->subMonth();
                break;
            case '3M':
                $date->firstOfQuarter()->subMonths(3)->firstOfQuarter();
                break;
            case '6M':
                $month = intval($date->format('m'));
                if ($month <= 6) {
                    $date->startOfYear()->subMonths(6);
                } else {
                    $date->startOfYear();
                }
                break;
            case '1Y':
                $date->startOfYear()->subYear();
                break;

        }

        return $date;
    }

    /**
     * @param        $range
     * @param Carbon $date
     *
     * @return Carbon
     * @throws FireflyException
     */
    public function next($range, Carbon $date)
    {
        switch ($range) {
            case '1D':
                $date->endOfDay()->addDay();
                break;
            case '1W':
                $date->endOfWeek()->addDay()->startOfWeek();
                break;
            case '1M':
                $date->endOfMonth()->addDay()->startOfMonth();
                break;
            case '3M':
                $date->lastOfQuarter()->addDay();
                break;
            case '6M':
                if (intval($date->format('m')) >= 7) {
                    $date->startOfYear()->addYear();
                } else {
                    $date->startOfYear()->addMonths(6);
                }
                break;
            case '1Y':
                $date->startOfYear()->addYear();
                break;
            default:
                throw new FireflyException('Cannot do _next() on ' . $range);
                break;
        }

        return $date;
    }
} 
