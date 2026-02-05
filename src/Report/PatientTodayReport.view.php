<?php
use koolreport\widgets\google\ColumnChart;
use koolreport\widgets\koolphp\Table;

$translator = $this->params["translator"];
?>
<div class="report-content">
    <h1 class="text-center mb-4"><?php echo $translator->trans("Patients Today Report"); ?></h1>
    <div class="row">
        <div class="col-md-12">
            <?php
            ColumnChart::create(array(
                "dataStore" => $this->dataStore('summary'),
                "columns" => array(
                    "status",
                    "patientName" => array(
                        "label" => $translator->trans("Count"),
                        "type" => "number"
                    )
                ),
                "options" => array(
                    "title" => $translator->trans("Patients Attendance Status"),
                    "colors" => ["#dc3545", "#198754"]
                )
            ));
            ?>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <h3><?php echo $translator->trans("Patient List"); ?></h3>
            <?php
            Table::create(array(
                "dataStore" => $this->dataStore('patients'),
                "columns" => array(
                    "patientName" => array(
                        "label" => $translator->trans("Patient Name")
                    ),
                    "attended" => array(
                        "label" => $translator->trans("Attended"),
                        "type" => "number",
                        "formatValue" => function($value) use ($translator) {
                            $label = $value > 0 ? $translator->trans("Yes") : $translator->trans("No");
                            $class = $value > 0 ? "bg-success" : "bg-danger";
                            return "<span class='badge $class'>$label</span>";
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
