<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>" style="display: inline;">
    <?php 
    if(isset($is_quote_invoice)){ echo '<input type="hidden" name="is_quote_invoice" value="'.$is_quote_invoice.'">'; }
    ?>
<input type="submit" name="<?php if (isset($btn_name)) { echo $btn_name; } else { ?>btn_add<?php } ?>" style="float: right; margin-top: 10px; margin-right: 10px;" value="<?php if (isset($btn_value)) { echo $btn_value; } else { echo $this->lang->line('add'); } ?>" />
</form>