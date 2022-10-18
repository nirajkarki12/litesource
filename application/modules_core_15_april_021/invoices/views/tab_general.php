<script type="text/javascript">


    function client_change() {
        var client_id = $("#client_id").find(":selected").val();

        get_client_details(client_id);
    }

    function get_client_details(client_id) {
        $.post("<?php echo site_url('clients/ajax_get_contacts'); ?>", {
            client_id: client_id
        }, function (data) {
            if (data == 'session_expired') {
                window.location.reload();
            }

            var contacts = data.contacts;
            var contact_options = "<option value='0'>(Select Contact)</option>";


            for (var i = 0; i < contacts.length; i++) {
                contact_options += "<option value='" + contacts[i].contact_id + "'>" + contacts[i].contact_name + "</option>";

            }
            $("#contact_id").html(contact_options);

            $("#invoice_client_group_id").val(data.client_group_id);


        }, "json");

    }
    ;

    $(document).ready(function () {


        $("#client_id").bind('change', function () {
            client_change();

        });


    });

</script>

<div class="left_box">

    <?php if ($invoice->invoice_is_quote) { ?>
        <dl>
            <dt><label><?php echo $this->lang->line('quote_number'); ?>: </label></dt>
            <dd><input type="text" name="invoice_number" value="<?php echo $invoice->invoice_number; ?>" /></dd>
        </dl>
    <?php } else { ?>
        <dl>
            <dt><label><?php echo $this->lang->line('invoice_number'); ?>: </label></dt>
            <dd><input type="text" name="invoice_number" value="<?php echo $invoice->invoice_number; ?>" /></dd>
        </dl>
        <dl>
            <dt><label><?php echo $this->lang->line('client_order_number'); ?>: </label></dt>
            <dd><input type="text" name="client_order_number" value="<?php echo $invoice->invoice_client_order_number; ?>" /></dd>
        </dl>
    <?php } ?>	

    <dl>
        <dt><label><?php echo $this->lang->line('client'); ?>: </label></dt>		
        <dd>
            <?php if ($invoice->invoice_status_id == 3) { ?>
                <?php echo $invoice->client_name; ?>
            <?php } else { ?>
                <select name="client_id" id="client_id">

                    <option value="<?php echo $invoice->client_id; ?>" selected="selected"><?php echo $invoice->client_name; ?></option>

                    <?php foreach ($clients as $client) {
                        if ($invoice->client_id != $client->client_id) { ?>
                            <option value="<?php echo $client->client_id; ?>" ><?php echo $client->client_name; ?></option>
        <?php }
    } ?>

                </select>
<?php } ?>	


        </dd>
    </dl>

    <dl>
        <dt><label><?php echo $this->lang->line('pricing'); ?>: </label></dt>
        <dd>
            <select name="invoice_client_group_id" id="invoice_client_group_id">
                <?php foreach ($client_groups as $client_group) { ?>
                    <option value="<?php echo $client_group->client_group_id; ?>" <?php if ($invoice->invoice_client_group_id == $client_group->client_group_id) { ?>selected="selected"<?php } ?>><?php echo $client_group->client_group_name; ?></option>
<?php } ?>

            </select>
        </dd>
    </dl>

    <dl>
        <dt><label><?php echo $this->lang->line('project'); ?>: </label></dt>		
        <dd>
            <?php if ($invoice->invoice_status_id == 3) { ?>
    <?php echo $invoice->project_name; ?>
                <?php } else { ?>
                <select name="project_id">

                    <?php if ($invoice->project_id == 0) { ?>
                        <option value="0" selected="selected"></option>
                    <?php } else { ?>
                        <option value="0"></option>
                        <option value="<?php echo $invoice->project_id; ?>" selected="selected"><?php echo $invoice->project_name; ?></option>
                    <?php } ?>

                    <?php foreach ($projects as $project) {
                        if ($invoice->project_id != $project->project_id) { ?>
                            <option value="<?php echo $project->project_id; ?>" ><?php echo $project->project_name; ?></option>
                    <?php }
                } ?>

                </select>
<?php } ?>	

        </dd>
    </dl>

    <dl>
        <dt><label><?php echo $this->lang->line('project_specifier'); ?>: </label></dt>	
        <dd><input type="text" name="project_specifier" id="project_specifier" value="<?php echo $invoice->project_specifier; ?>" /></dd>
    </dl>
    <dl>
        <dt><label><?php echo $this->lang->line('contact'); ?>: </label></dt>		
        <dd>
<?php if ($invoice->invoice_status_id == 3) { ?>
                    <?php echo $invoice->contact_name; ?>
                <?php } else { ?>

                <select name="contact_id" id="contact_id">

                    <?php if ($invoice->contact_id == 0) { ?>
                        <option value="0" selected="selected">(Select Contact)</option>
                    <?php } else { ?>
                        <option value="0">(Select Contact)</option>
                        <option value="<?php echo $invoice->contact_id; ?>" selected="selected"><?php echo $invoice->contact_name; ?></option>
                    <?php } ?>

    <?php foreach ($contacts as $contact) {
        if ($invoice->contact_id != $contact->contact_id) { ?>
                            <option value="<?php echo $contact->contact_id; ?>" ><?php echo $contact->contact_name; ?></option>
        <?php }
    } ?>

                </select>			
                <input type="submit" name="btn_add_contact" value="<?php echo $this->lang->line('add_contact'); ?>" />
<?php } ?>

        </dd>
    </dl>


    <dl>
        <dt><label><?php echo $this->lang->line('user'); ?>: </label></dt>
        <dd>
            <select name="user_id">
<?php if (!$invoice->from_first_name) { ?>
                    <option value=""><?php echo $this->lang->line('unassigned'); ?></option>
<?php } ?>
<?php foreach ($users as $user) { ?>
                    <option value="<?php echo $user->user_id; ?>" <?php if ($invoice->user_id == $user->user_id) { ?>selected="selected"<?php } ?>><?php echo $user->first_name . ' ' . $user->last_name; ?></option>
<?php } ?>
            </select>
        </dd>
    </dl>

    
    <?php if($invoice->invoice_is_quote == '0'){ ?>
    <dl>
        <dt><label>Ignore smart status</label></dt>
        <dd><input type="checkbox" name="smart_status" <?php if($invoice->smart_status == '1'){ echo 'checked'; }  ?> value="1"></dd>
    </dl>
    <?php } ?>
    
    <dl>
        <dt><label><?php echo $this->lang->line('status'); ?>: </label></dt>
        <dd>
            <select name="invoice_status_id">
            <?php foreach ($invoice_statuses as $invoice_status) { ?>
              
                    <option value="<?= $invoice_status->invoice_status_id; ?>" <?php if (($invoice_status->invoice_status_id) == $invoice_status_check) {
                    echo 'selected';
                } ?> ><?= $invoice_status->invoice_status; ?></option>

<?php } ?>
            </select>
<?php // if($invoice->smart_status == '1'){     echo '(Force Closed)'; }  ?>
        </dd>
    </dl>

    <dl>
        <dt><label><?php echo $this->lang->line('payment_terms'); ?>: </label></dt>
        <dd><input type="text" name="invoice_payment_terms" value="<?php echo $invoice->invoice_payment_terms; ?>" /></dd>

    </dl>

    <dl>
        <dt><label><?php echo $this->lang->line('tax_rate'); ?>: </label></dt>
        <dd>
            <select name="invoice_tax_rate_id">
<?php foreach ($tax_rates as $tax_rate) { ?>
                    <option value="<?php echo $tax_rate->tax_rate_id; ?>" <?php if ($invoice->invoice_tax_rate_id == $tax_rate->tax_rate_id) { ?>selected="selected"<?php } ?>><?php echo $tax_rate->tax_rate_percent . '% - ' . $tax_rate->tax_rate_name; ?></option>
<?php } ?>

            </select>
        </dd>
    </dl>


    <dl>
        <dt><label><?php echo (!$invoice->invoice_is_quote ? $this->lang->line('invoice_date') : $this->lang->line('date')); ?>: </label></dt>
        <dd><input class="datepicker" type="text" name="invoice_date_entered" value="<?php echo format_date($invoice->invoice_date_entered); ?>" /></dd>
    </dl>

    <?php if (!$invoice->invoice_is_quote) { ?>
        <!--	<dl>
                        <dt><label><?php echo $this->lang->line('due_date'); ?>: </label></dt>
                        <dd><input class="datepicker" type="text" name="invoice_due_date" value="<?php echo format_date($invoice->invoice_due_date); ?>" /></dd>
                </dl>-->

        <?php if ($invoice->invoice_is_overdue and $invoice->invoice_days_overdue > 0) { ?>
            <!--	<dl>
                            <dt><label style="color: red; font-weight: bold;"><?php echo $this->lang->line('days_overdue'); ?>: </label></dt>
                            <dd><span style="color: red; font-weight: bold;"><?php echo $invoice->invoice_days_overdue; ?></span></dd>
                    </dl>-->
    <?php } elseif ($invoice->invoice_days_overdue <= 0) { ?>
            <!--	<dl>
                            <dt><label><?php echo $this->lang->line('days_until_due'); ?>: </label></dt>
                            <dd><?php echo ($invoice->invoice_days_overdue * -1); ?></dd>
                    </dl>-->
    <?php } ?>

<?php } ?>

    <dl>
        <dt><label><?php echo $this->lang->line('generate'); ?>: </label></dt>
        <dd>
            <a href="javascript:void(0)" class="output_link" id="<?php echo $invoice->invoice_id; ?>"><?php echo $this->lang->line('generate'); ?></a>
        </dd>
    </dl>

    <input type="submit" id="btn_submit" name="btn_submit_options_general" value="<?php echo $this->lang->line('save_options'); ?>" />

</div>

<div class="right_box">

    <dl>
        <dt><label><?php echo $this->lang->line('subtotal'); ?>: </label></dt>
        <dd><?php echo invoice_item_subtotal($invoice); ?></dd>
    </dl>

    <dl>
        <dt><label><?php echo $invoice->tax_rate_percent_name; ?>: </label></dt>
        <dd><?php echo invoice_tax_total($invoice); ?></dd>
    </dl>

    <dl>
        <dt><label><?php echo $this->lang->line('total'); ?>: </label></dt>
        <dd><?php echo invoice_total($invoice); ?></dd>
    </dl>

    
    <?php if (!$invoice->invoice_is_quote) { 
        $paid_amounts = round((-1)*$docket_payment_amount, 2);
        $paid_amount = $paid_amounts;
        if( $paid_amount < 0 ){
            $paid_amount = "-".display_currency((-1)*$paid_amount);
        } elseif($paid_amount == '0.00') {
            $paid_amount = display_currency(0.00);
        }else{
            $paid_amount = display_currency($paid_amount);
        }
        
        ?>
    <dl>
        <dt><label>Paid Amount: </label></dt>
        <dd><?= $paid_amount;  ?></dd>
    </dl>
    
    <dl>
        <dt><label>Final Amount: </label></dt>
        <dd><?= tot_invc_owing_amnt_wit_curr($invoice, $docket_payment_amount)?></dd>
    </dl>
    
    
    <?php } ?>
    
    <?php if (!$this->mdl_mcb_data->setting('disable_invoice_payments') && !$invoice->invoice_is_quote) { ?>
        <dl>
            <dt><label><?php echo $this->lang->line('paid'); ?>: </label></dt>
            <dd><?php echo invoice_paid($invoice); ?></dd>
        </dl>

        <dl>
            <dt><label><?php echo $this->lang->line('invoice_balance'); ?>: </label></dt>
            <dd><?php echo invoice_balance($invoice); ?></dd>
        </dl>
<?php } ?>

</div>

<div style="clear: both;">&nbsp;</div>