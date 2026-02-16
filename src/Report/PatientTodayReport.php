<?php

namespace App\Report;

use koolreport\KoolReport;
use koolreport\processes\Group;
use koolreport\processes\Custom;

class PatientTodayReport extends KoolReport
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
        $translator = $this->params["translator"];

        $this->src('data')
             ->pipe(new Custom(function($row) {
                 $row["attended"] = $row["hasAttendance"] > 0 ? 1 : 0;
                 return $row;
             }))
             ->pipe($this->dataStore('patients'));

        // For the graph
        $this->src('data')
             ->pipe(new Custom(function($row) use ($translator) {
                 $row["status"] = $row["hasAttendance"] > 0
                 ? $translator->trans("Attended")
				: $translator->trans("Not Attended");
                 return $row;
             }))
             ->pipe(new Group(array(
                 "by" => "status",
                 "count" => "patientName"
             )))
             ->pipe($this->dataStore('summary'));
    }
}
