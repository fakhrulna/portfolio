<?php

namespace helpers\Chart;

use Illuminate\Support\Collection;

class BaseChart
{
    /**
     * Stores the chart datasets.
     *
     * @var array
     */
    public $datasets = [];

    /**
     * Stores the dataset class to be used.
     *
     * @var object
     */
    protected $dataset = DatasetClass::class;

    /**
     * Stores the chart labels.
     *
     * @var array
     */
    public $labels = [];

    /**
     * Adds a new dataset to the chart.
     *
     * @param string $name
     * @param array|Collection $data
     * @param null $additionalData
     * @return mixed
     */
    public function dataset($name, $data, $additionalData = null)
    {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        $dataset = new $this->dataset($name, $data);

        array_push($this->datasets, $dataset);

        if ($additionalData) {
            foreach ($additionalData as $key => $value) {
                $dataset->$key = $value;
            }
        }

        return $dataset;

    }

    /**
     * Set the chart labels.
     *
     * @param array|Collection $labels
     *
     * @return self
     */
    public function labels($labels)
    {
        if ($labels instanceof Collection) {
            $labels = $labels->toArray();
        }

        $this->labels = $labels;

        return $this;
    }
}