<?php
use koolreport\widgets\google\AreaChart;
use koolreport\widgets\koolphp\Table;

$translator = $this->params["translator"];
?>
<div class="report-content">
    <h1><?php echo $translator->trans("Activity by Hour Report"); ?></h1>
    <hr class="red">

    <div class="row">
        <div class="col-md-12">
            <h3><?php echo $translator->trans("Today's Activity"); ?></h3>
            <?php
            AreaChart::create(array(
                "dataStore" => $this->dataStore('activity_today'),
                "columns" => array(
                    "hour" => array(
                        "label" => $translator->trans("Hour"),
                        "type" => "string"
                    ),
                    "attendance" => array(
                        "label" => $translator->trans("Attendance"),
                        "type" => "number"
                    ),
                    "visitor" => array(
                        "label" => $translator->trans("Visitor"),
                        "type" => "number"
                    ),
                    "stakeholder" => array(
                        "label" => $translator->trans("Stakeholder"),
                        "type" => "number"
                    ),
                ),
                "options" => array(
                    "title" => $translator->trans("People present per hour (Today)"),
                )
            ));
            ?>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-12">
            <h3><?php echo $translator->trans("Historical Average Activity"); ?></h3>
            <?php
            AreaChart::create(array(
                "dataStore" => $this->dataStore('activity_historical'),
                "columns" => array(
                    "hour" => array(
                        "label" => $translator->trans("Hour"),
                        "type" => "string"
                    ),
                    "attendance" => array(
                        "label" => $translator->trans("Attendance"),
                        "type" => "number"
                    ),
                    "visitor" => array(
                        "label" => $translator->trans("Visitor"),
                        "type" => "number"
                    ),
                    "stakeholder" => array(
                        "label" => $translator->trans("Stakeholder"),
                        "type" => "number"
                    ),
                ),
                "options" => array(
                    "title" => $translator->trans("Average people present per hour (Historical)"),
                )
            ));
            ?>
        </div>
    </div>
</div>
