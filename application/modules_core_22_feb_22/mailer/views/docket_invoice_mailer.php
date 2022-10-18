<?php $this->load->view('dashboard/header'); ?>
<?php $is_default = FALSE; foreach ($contacts as $d_contct){ 
        if($d_contct->is_default == '1'){ 
            $is_default = TRUE; 
            $default_name = $d_contct->contact_name;
            $default_email = $d_contct->email_address;
            break; } } ?>

<div class="grid_10" id="content_wrapper">

    <div class="section_wrapper">

        <h3 class="title_black"><?php echo $this->lang->line('send_email'); ?></h3>

        <?php $this->load->view('dashboard/system_messages'); ?>

        <div class="content toggle">

            <form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">
                <dl>
                    <dt><label><?php echo $this->lang->line('template'); ?>: </label></dt>
                    <dd>
                        <select name="invoice_template">
                            <?php foreach ($templates as $template) { ?>
                                <option <?php if ($this->mdl_invoices->form_value('invoice_template') == $template) { ?>selected="selected"<?php } ?>><?php echo $template; ?></option>
                            <?php } ?>
                        </select>
                    </dd>
                </dl>

                <dl>
                    <dt><label><?php echo $this->lang->line('from_name'); ?>: *</label></dt>
                    <dd><input type="text" name="email_from_name" value="<?php echo $this->mdl_invoices->form_value('email_from_name'); ?>" /></dd>
                </dl>
                <dl>
                    <dt><label><?php echo $this->lang->line('from_email'); ?>: *</label></dt>
                    <dd><input type="text" name="email_from_email" value="<?php echo $this->mdl_invoices->form_value('email_from_email'); ?>" /></dd>
                </dl>
                
                <dl>
                    <dt><label>Contact: </label></dt>
                    <dd>
                        <select name="default_to_contact" id="chng_contact">
                            <option value="">---select---</option>
                            <?php foreach($contacts as $contct){ ?>
                                <option data-c_name="<?=$contct->contact_name?>" data-c_email="<?=$contct->email_address?>" <?php if($contct->is_default == '1'){echo 'selected';} ?> value="<?=$contct->email_address?>">
                                    <?=$contct->email_address?><?php if($contct->is_default == '1'){echo '<span style="font-weight: bold;"> *</span>';} ?>
                                </option>
                            <?php } ?>
                        </select>
                    </dd>
                </dl>
                                
                <dl>
                    <dt><label><?php echo $this->lang->line('to_name'); ?>: </label></dt>
                    <dd><input id="c_to_name" type="text" name="email_to_name" value="<?php if($is_default == TRUE){ echo $default_name; }else{ echo $this->mdl_invoices->form_value('email_to_name');} ?>" /></dd>
                </dl>    
                
                <dl>
                    <dt><label><?php echo $this->lang->line('to_email'); ?>: *</label></dt>
                    <dd><input id="c_to_email" type="text" name="email_to_email" value="<?php if($is_default == TRUE){ echo $default_email; }else{ echo $this->mdl_invoices->form_value('email_to_email'); } ?>" /></dd>
                </dl>
                <dl>
                    <dt><label><?php echo $this->lang->line('cc'); ?>: </label></dt>
                    <dd><input id="email_cc" type="text" name="email_cc" value="<?php echo ($this->mdl_invoices->form_value('email_cc')) ? $this->mdl_invoices->form_value('email_cc') : $this->mdl_mcb_data->setting('default_cc'); ?>" /></dd>
                </dl>
                <dl>
                    <dt><label><?php echo $this->lang->line('bcc'); ?>: </label></dt>
                    <dd><input type="text" name="email_bcc" value="<?php echo ($this->mdl_invoices->form_value('email_bcc')) ? $this->mdl_invoices->form_value('email_bcc') : $this->mdl_mcb_data->setting('default_bcc'); ?>" /></dd>
                </dl>
                <dl>
                    <dt><label><?php echo $this->lang->line('subject'); ?>: *</label></dt>
                    <dd><input type="text" name="email_subject" value="<?php echo $this->mdl_invoices->form_value('email_subject'); ?>" /></dd>
                </dl>
                <dl>
                    <dt><label><?php echo $this->lang->line('body'); ?>: </label></dt>
                    <dd>
                        <textarea name="email_body" rows="10" cols="60"><?php echo $this->mdl_invoices->form_value('email_body'); ?></textarea>
                    </dd>
                </dl>
                <input type="hidden" name="docketid" value="<?php echo $docket['docket']->docket_id; ?>" />
                <input type="submit" id="btn_submit" name="btn_submit" value="<?php echo $this->lang->line('send_email'); ?>" />
                <input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />
            </form>
        </div>
    </div>
</div>
<?php $this->load->view('dashboard/footer'); ?>
<script type="text/javascript">

    $("#chng_contact").on('change', function(e){
        let $selectedOption = $(this).find("option:selected");

        var c_name = ($selectedOption.data('c_name'));
        var c_email = ($selectedOption.data('c_email'));

        if(c_name != undefined){
            document.getElementById("c_to_name").value = c_name;
            document.getElementById("c_to_email").value = c_email;
        }else{
            document.getElementById("c_to_name").value = '<?=$this->mdl_invoices->form_value('email_to_name')?>';
            document.getElementById("c_to_email").value = '<?=$this->mdl_invoices->form_value('email_to_email')?>';
        }

        if(e.target.selectedIndex == "1") {
            $('#email_cc').val($selectedOption.next().val());
            console.log($selectedOption.next());
        } else if(e.target.selectedIndex == "2") {
            $('#email_cc').val($selectedOption.prev().val());
            console.log($selectedOption.prev());
        } else{
            $('#email_cc').val('');
        }
    });
    $("#chng_contact").trigger('change');
    
</script>