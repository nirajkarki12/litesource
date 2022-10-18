<?php if (validation_errors()) { ?>
<?php echo validation_errors(); ?>
<?php } ?>

<?php if ($this->session->flashdata('success_save')) { ?>
<div class="success"><?php echo $this->lang->line('this_item_has_been_saved'); ?></div>
<?php } ?>

<?php if ($this->session->flashdata('success_delete')) { ?>
<div class="warning"><?php echo $this->lang->line('this_item_has_been_deleted'); ?>.</div>
<?php } ?>

<?php if ($this->session->flashdata('custom_warning')) { ?>
<div class="warning"><?php echo $this->session->flashdata('custom_warning'); ?></div>
<?php } ?>

<?php if ($this->session->flashdata('custom_error')) { ?>
<div class="error"><?php echo $this->session->flashdata('custom_error'); ?></div>
<?php } ?>

<?php if ($this->session->flashdata('custom_success')) { ?>
<div class="success"><?php echo $this->session->flashdata('custom_success'); ?></div>
<?php } ?>

<?php if ($this->session->flashdata('order_error')) {
        foreach ($this->session->flashdata('order_error') as $val) { ?>
        <div class="error"><?php echo $val ?></div>
<?php
        }
} ?>

<?php if ($this->session->flashdata('order_warning')) {
        foreach ($this->session->flashdata('order_warning') as $val) { ?>
        <div class="warning"><?php echo $val ?></div>
<?php
        }
} ?>



<?php if (isset($static_error) and $static_error) { ?>
<div class="error"><?php echo $static_error; ?></div>
<?php } ?>
