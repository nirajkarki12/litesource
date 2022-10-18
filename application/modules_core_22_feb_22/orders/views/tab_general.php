<div class="full_box">
    <dl>
        <dt><label><?php echo $this->lang->line('supplier'); ?>: </label></dt>		
        <dd>
            <?php echo anchor('clients/details/client_id/' . $order->client_id, $order->client_name); ?>
        </dd>
    </dl>
    <dl>
        <dt><label><?php echo $this->lang->line('order_number'); ?>: </label></dt>
        <dd><input type="text" name="order_number" value="<?php echo $order->order_number; ?>" /></dd>
    </dl>
    <dl>
        <dt><label><?php echo $this->lang->line('total') . '(' . $order->currency_code . ')'; ?>: </label></dt>		
        <dd>
            <?php echo $order->currency_symbol_left . $order->order_total . $order->currency_symbol_right; ?></td>
        </dd>
    </dl>
    <dl>
        <dt><label><?php echo $this->lang->line('contact'); ?>: </label></dt>		
        <dd>
            <select name="contact_id" id="contact_id" style="width:200px">
                <option value="0" <?php if ($order->contact_id == 0) { ?>selected="selected"<?php } ?>></option>
                <?php foreach ($contacts as $contact) { ?>
                    <option value="<?php echo $contact->contact_id; ?>" <?php if ($order->contact_id == $contact->contact_id) { ?>selected="selected"<?php } ?>><?php echo character_limiter($contact->contact_name, 100); ?></option>
                <?php } ?>
            </select>
        </dd>
    </dl>
    <dl>
        <dt><label>User: </label></dt>		
        <dd>
            <select name="custom_user_id" id="custom_user_id" style="width:240px">
                <?php foreach ($user_list as $user) { ?>
                    <option value="<?= $user->user_id?>" <?php if($order->custom_user_id > 0){$order->user_id = $order->custom_user_id;} if($order->user_id == $user->user_id){echo 'selected';} ?> ><?=$user->first_name.' '.$user->last_name; ?></option>
                <?php } ?>
            </select>
        </dd>
    </dl>
    <?php if (isset($order->invoice_id)) { ?>
        <dl>
            <dt><label><?php echo $this->lang->line('quote'); ?>: </label></dt>		
            <dd>
                <?php echo $order->invoice_number; ?>
            </dd>
        </dl>
        
        <dl>
            <dt><label>Invoice: </label></dt>		
            <dd>
                <input type="text" name="i_invoice_number" value="<?=$order->i_invoice_number?>" />
            </dd>
        </dl>
    
    <?php } ?>
    <dl>
        <dt><label><?php echo $this->lang->line('project'); ?>: </label></dt>		
        <dd>
            <?php if ($order->order_status_type == 3) { ?>
                <?php echo $order->project_name; ?>
                <input type="hidden" name="project_id" value="<?=$order->project_id?>" />
            <?php } else { ?>
                <select name="project_id">
                    <?php if ($order->project_id == 0) { ?>
                        <option value="0" selected="selected"></option>
                    <?php } else { ?>
                        <option value="0"></option>
                        <option value="<?php echo $order->project_id; ?>" selected="selected"><?php echo $order->project_name; ?></option>
                    <?php } ?>
                    <?php foreach ($projects as $project) {
                        if ($order->project_id != $project->project_id) { ?>
                            <option value="<?php echo $project->project_id; ?>" ><?php echo $project->project_name; ?></option>
        <?php }
    } ?>
                </select>
<?php } ?>	
        </dd>
    </dl>
    <dl>
        <dt><label><?php echo $this->lang->line('status'); ?>: </label></dt>
        <dd>
            <select name="order_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                    <?php if ($order_status->invoice_status_selectable) { ?>
                        <option value="<?php echo $order_status->invoice_status_id; ?>" <?php if ($order_status->invoice_status_id == $order->order_status_id) { ?>selected="selected"<?php } ?>><?php echo $order_status->invoice_status; ?></option>
    <?php } ?>
<?php } ?>
            </select>
        </dd>
    </dl>
    <dl>
        <dt><label><?php echo $this->lang->line('order_date'); ?>: </label></dt>
        <dd><input class="datepicker" type="text" name="order_date_entered" value="<?php echo format_date($order->order_date_entered); ?>" /></dd>
    </dl>
    <dl>
        <dt><label><?php echo $this->lang->line('notes'); ?>: </label></dt>
        <dd><textarea name="order_notes" id="order_notes" rows="5" cols="40"><?php echo $order->order_notes; ?></textarea></dd>
    </dl>
    <dl>
        <dt><label><?php echo $this->lang->line('supplier_invoice_number'); ?>: </label></dt>
        <dd><input type="text" name="order_supplier_invoice_number" value="<?php echo $order->order_supplier_invoice_number; ?>" /></dd>
    </dl>
    <dl>
        <dt><label><?php echo $this->lang->line('generate'); ?>: </label></dt>
        <dd>
            <a href="javascript:void(0)" class="order_output_link" id="<?php echo $order->order_id; ?>"><?php echo $this->lang->line('generate'); ?></a>
        </dd>
    </dl>
    
    <input type="submit" id="btn_submit" name="btn_submit_options_general" value="<?php echo $this->lang->line('save_options'); ?>" />
</div>
<div style="clear: both;">&nbsp;</div>