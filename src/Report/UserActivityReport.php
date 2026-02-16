<?php

namespace App\Report;

use koolreport\KoolReport;

class UserActivityReport extends KoolReport
{
    protected function settings()
    {
        return array(
            "dataSources" => array(
                "data" => array(
                    "class" => "\koolreport\datasources\ArrayDataSource",
                    "data" => $this->params["data"],
                    "dataFormat" => "associate",
                )
            )
        );
    }

    protected function setup()
    {
        $this->src('data')
             ->pipe($this->dataStore('user_activity'));
    }
}
