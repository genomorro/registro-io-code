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
                    "title" => $translator->trans("Activity by hour (Today)"),
                )
            ));
            ?>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12 table-responsive">
            <?php
            Table::create(array(
                "dataStore" => $this->dataStore('activity_today'),
                "columns" => array(
                    "hour" => array(
                        "label" => $translator->trans("Hour"),
                    ),
                    "attendance" => array(
                        "label" => $translator->trans("Attendance"),
                        "type" => "number",
                        "decimals" => 2
                    ),
                    "visitor" => array(
                        "label" => $translator->trans("Visitor"),
                        "type" => "number",
                        "decimals" => 2
                    ),
                    "stakeholder" => array(
                        "label" => $translator->trans("Stakeholder"),
                        "type" => "number",
                        "decimals" => 2
                    ),
                ),
                "cssClass" => array(
                    "table" => "table table-hover"
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
                    "title" => $translator->trans("Average activity by hour (Historical)"),
                )
            ));
            ?>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12 table-responsive">
            <?php
            Table::create(array(
                "dataStore" => $this->dataStore('activity_historical'),
                "columns" => array(
                    "hour" => array(
                        "label" => $translator->trans("Hour"),
                    ),
                    "attendance" => array(
                        "label" => $translator->trans("Attendance"),
                        "type" => "number",
                        "decimals" => 2
                    ),
                    "visitor" => array(
                        "label" => $translator->trans("Visitor"),
                        "type" => "number",
                        "decimals" => 2
                    ),
                    "stakeholder" => array(
                        "label" => $translator->trans("Stakeholder"),
                        "type" => "number",
                        "decimals" => 2
                    ),
                ),
                "cssClass" => array(
                    "table" => "table table-hover"
                )
            ));
            ?>
        </div>
    </div>
</div>
