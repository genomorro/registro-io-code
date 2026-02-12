<?php
use koolreport\widgets\koolphp\Table;

$translator = $this->params["translator"];
?>
<div class="report-content">
    <h1 class="text-center mb-4"><?php echo $translator->trans("User Activity Report"); ?></h1>
    <div class="row mt-4">
        <div class="col-md-12">
            <?php
            Table::create(array(
                "dataStore" => $this->dataStore('user_activity'),
                "columns" => array(
                    "userName" => array(
                        "label" => $translator->trans("User Name")
                    ),
                    "attendanceCheckInToday" => array(
                        "label" => $translator->trans("Attendance Check-ins Today"),
                        "type" => "number"
                    ),
                    "attendanceCheckOutToday" => array(
                        "label" => $translator->trans("Attendance Check-outs Today"),
                        "type" => "number"
                    ),
                    "attendanceCheckInTotal" => array(
                        "label" => $translator->trans("Attendance Check-ins Total"),
                        "type" => "number"
                    ),
                    "attendanceCheckOutTotal" => array(
                        "label" => $translator->trans("Attendance Check-outs Total"),
                        "type" => "number"
                    ),
                    "visitorCheckInToday" => array(
                        "label" => $translator->trans("Visitor Check-ins Today"),
                        "type" => "number"
                    ),
                    "visitorCheckOutToday" => array(
                        "label" => $translator->trans("Visitor Check-outs Today"),
                        "type" => "number"
                    ),
                    "visitorCheckInTotal" => array(
                        "label" => $translator->trans("Visitor Check-ins Total"),
                        "type" => "number"
                    ),
                    "visitorCheckOutTotal" => array(
                        "label" => $translator->trans("Visitor Check-outs Total"),
                        "type" => "number"
                    ),
                    "stakeholderCheckInToday" => array(
                        "label" => $translator->trans("Stakeholder Check-ins Today"),
                        "type" => "number"
                    ),
                    "stakeholderCheckOutToday" => array(
                        "label" => $translator->trans("Stakeholder Check-outs Today"),
                        "type" => "number"
                    ),
                    "stakeholderCheckInTotal" => array(
                        "label" => $translator->trans("Stakeholder Check-ins Total"),
                        "type" => "number"
                    ),
                    "stakeholderCheckOutTotal" => array(
                        "label" => $translator->trans("Stakeholder Check-outs Total"),
                        "type" => "number"
                    ),
                ),
                "cssClass" => array(
                    "table" => "table table-hover table-bordered table-striped"
                )
            ));
            ?>
        </div>
    </div>
</div>
