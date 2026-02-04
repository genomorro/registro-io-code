<?php
use koolreport\widgets\google\ColumnChart;
use koolreport\widgets\koolphp\Table;
?>
<div class="report-content">
    <h1 class="text-center mb-4">Patients Today Report</h1>
    <div class="row">
        <div class="col-md-12">
            <?php
            ColumnChart::create(array(
                "dataStore" => $this->dataStore('summary'),
                "columns" => array(
                    "status",
                    "patientName" => array(
                        "label" => "Count",
                        "type" => "number"
                    )
                ),
                "options" => array(
                    "title" => "Patients Attendance Status",
                    "colors" => ["#3366cc", "#dc3912"]
                )
            ));
            ?>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <h3>Patient List</h3>
            <?php
            Table::create(array(
                "dataStore" => $this->dataStore('patients'),
                "columns" => array(
                    "patientName" => array(
                        "label" => "Patient Name"
                    ),
                    "attended" => array(
                        "label" => "Attended",
                        "type" => "number",
                        "formatValue" => function($value) {
                            return $value > 0 ? "<span class='badge bg-success'>Yes</span>" : "<span class='badge bg-danger'>No</span>";
                        }
                    )
                ),
                "cssClass" => array(
                    "table" => "table table-hover"
                )
            ));
            ?>
        </div>
    </div>
</div>
