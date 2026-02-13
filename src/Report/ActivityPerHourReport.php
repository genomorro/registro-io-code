<?php

namespace App\Report;

use koolreport\KoolReport;

class ActivityPerHourReport extends KoolReport
{
    protected function settings()
    {
        return array(
            "dataSources" => array(
                "today" => array(
                    "class" => "\koolreport\datasources\ArrayDataSource",
                    "data" => $this->params["todayData"],
                    "dataFormat" => "associate",
                ),
                "historical" => array(
                    "class" => "\koolreport\datasources\ArrayDataSource",
                    "data" => $this->params["historicalData"],
                    "dataFormat" => "associate",
                )
            )
        );
    }

    protected function setup()
    {
        $this->src('today')
             ->pipe($this->dataStore('activity_today'));

        $this->src('historical')
             ->pipe($this->dataStore('activity_historical'));
    }
}
