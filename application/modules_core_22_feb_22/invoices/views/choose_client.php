<?php $this->load->view('dashboard/header'); ?>

<?php $this->load->view('dashboard/jquery_date_picker'); ?>

<script type="text/javascript">


    function client_change() {
        var selectedValue = $("#client_id").find(":selected").val();

        get_client_contacts(selectedValue);
    }

    function get_client_contacts(client_id) {
        $.post("<?php echo site_url('clients/ajax_get_contacts'); ?>", {
            client_id: client_id
        }, function (data) {
            if(data == 'session_expired'){
                            window.location.reload();
                        }

            $("#client_group_id").val(data.client_group_id);
            $("#contact_name").autocomplete({source: data.contacts});

        }, "json");

    }
    ;

    $(document).ready(function () {


        $("#client_id").bind('change', function () {
            client_change();

        });
        client_change();

        $("#project_name").autocomplete({
            minLength: 3,
            source: function (req, resp) {
                $.ajax({
                    url: "<?php echo site_url('projects/ajax_project_autocomplete'); ?>",
                    dataType: 'json',
                    type: 'POST',
                    data: req,
                    success: function (data) {
                        if(data == 'session_expired'){
                            window.location.reload();
                        }
                        resp(data.search_results);
                    }
                })
            }

        });


    });

</script>

<div class="grid_7" id="content_wrapper">

    <div class="section_wrapper">

        <h3 class="title_black"><?php echo uri_seg_is('invoices') ? $this->lang->line('create_invoice') : $this->lang->line('create_quote'); ?></h3>

        <div class="content toggle">

            <form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">

                <dl>
                    <dt><label><?php echo $this->lang->line('date'); ?>: </label></dt>
                    <dd><input id="datepicker" type="text" name="invoice_date_entered" value="<?php echo date($this->mdl_mcb_data->setting('default_date_format')); ?>" /></dd>
                </dl>
                <dl>
                    <dt><label><?php echo $this->lang->line('project'); ?>: </label></dt>
                    <dd>
                        <input name="project_name" type="text" id="project_name"/>

                    </dd>
                </dl>
                <dl>
                    <dt><label><?php echo $this->lang->line('client'); ?>: </label></dt>
                    <dd>
                        <select name="client_id" id="client_id">
                            <?php foreach ($clients as $client) { ?>
                                <option value="<?php echo $client->client_id; ?>" <?php if ($this->mdl_invoices->form_value('client_id') == $client->client_id) { ?>selected="selected"<?php } ?>><?php echo $client->client_name; ?></option>
                            <?php } ?>
                        </select>
                        <a href="<?php echo site_url('/clients/form'); ?>">Add Client</a>
                    </dd>
                </dl>

                <dl>
                    <dt><label><?php echo $this->lang->line('pricing'); ?>: </label></dt>
                    <dd>
                        <select name="client_group_id" id="client_group_id">
                            <?php foreach ($client_groups as $client_group) { ?>
                                <option value="<?php echo $client_group->client_group_id; ?>"><?php echo $client_group->client_group_name; ?></option>
                            <?php } ?>
                        </select>

                    </dd>
                </dl>

                <dl>
                    <dt><label><?php echo $this->lang->line('contact'); ?>: </label></dt>
                    <dd>
                        <input name="contact_name" type="text" id="contact_name"/>

                    </dd>

                </dl>
                <?php /* ?>
                  <dl>
                  <dt><label><?php echo $this->lang->line('invoice_group'); ?>: </label></dt>
                  <dd>
                  <select name="invoice_group_id" id="invoice_group_id">
                  <?php foreach ($invoice_groups as $invoice_group) { ?>
                  <option value="<?php echo $invoice_group->invoice_group_id; ?>" <?php if ($this->mdl_mcb_data->setting('default_invoice_group_id') == $invoice_group->invoice_group_id) { ?>selected="selected"<?php } ?>><?php echo $invoice_group->invoice_group_name; ?></option>
                  <?php } ?>
                  </select>
                  </dd>
                  </dl>
                  <?php */ ?>

                <?php if (uri_seg_is('quotes')) { ?>
                    <input id="invoice_is_quote" type="hidden" name="invoice_is_quote" value="1" />
                <?php } ?>


                <input type="submit" id="btn_submit" name="btn_submit" value="<?php echo uri_seg_is('invoices') ? $this->lang->line('create_invoice') : $this->lang->line('create_quote'); ?>" />
                <input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />

            </form>

        </div>

    </div>

</div>

<?php //$this->load->view('dashboard/sidebar', array('side_block'=>'invoices/sidebar')); ?>

<?php $this->load->view('dashboard/footer'); ?>