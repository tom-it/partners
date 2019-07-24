<?php

namespace App\Nova\Metrics\Partition;

use App\App;
use App\Earning;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class EarningsPerApp extends Partition
{
    protected $resourceColumn = 'app_id';

    /**
     * Set resource column
     *
     * @param $column
     *
     * @return $this
     */
    public function resourceColumn($column)
    {
        $this->resourceColumn = $column;

        return $this;
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $model = Earning::query();

        if ($request->resourceId) {
            $model->where($this->resourceColumn, $request->resourceId);
        }

        $apps = (new App())->getApps();

        $partition = $this->sum($request, $model, 'amount', 'app_id');

        $partition->value = collect($apps)->mapWithKeys(function ($app) use ($partition) {
            return [$app['name'] => ($partition->value[$app['id']] ?? 0) / 100];
        })->toArray();

        return $partition;
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'earnings-per-app';
    }
}
