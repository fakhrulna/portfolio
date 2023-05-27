<?php

namespace helpers\Chart;

use Illuminate\Support\Collection;

class DatasetClass
{
    /**
     * Defines the name of the dataset.
     *
     * @var string
     */
    public $label = 'Undefined';

    /**
     * Stores the dataset values.
     *
     * @var array
     */
    public $data = [];

    /**
     * Creates a new dataset with the given values.
     *
     * @param $label
     * @param $data
     * @param null $color
     */
    public function __construct($label, $data)
    {
        $this->label = $label;
        $this->data = $data;

        return $this;
    }

    /**
     * Set the dataset values.
     *
     * @param array|Collection $values
     *
     * @return self
     */
    public function values($values)
    {
        if ($values instanceof Collection) {
            $values = $values->toArray();
        }

        $this->values = $values;

        return $this;
    }

    /**
     * Matches the values of the dataset with the given number.
     *
     * @param int $values
     * @param bool $strict
     *
     * @return void
     */
    public function matchValues($values, $strict = false)
    {
        while (count($this->values) < $values) {
            array_push($this->values, 0);
        }

        if ($strict) {
            $this->values = array_slice($this->values, 0, $values);
        }
    }
}