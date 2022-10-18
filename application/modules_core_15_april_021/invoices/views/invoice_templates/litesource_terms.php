
<?php if ($terms_and_condition->setting_value != '') { ?>

<style>
    li p {
        margin-bottom: 1px;margin-top: 2px;
    }
    li strong{
        margin-bottom: 1px;
    }
    
</style>

    <!--<div class="terms_conditions">-->
    <h2 style="margin-bottom: -1px">Terms and Conditions</h2>
        <!--<ol class="terms_level_01">-->

        <div style="font-size: 8px">
            <?= $terms_and_condition->setting_value ?>
        </div>

        <!--</ol>-->
    <!--</div>-->
<?php } ?>
