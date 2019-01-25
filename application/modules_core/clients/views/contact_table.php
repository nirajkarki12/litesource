<table style="width: 100%;">
    <tr>
        <th scope="col"><?php echo $this->lang->line('name'); ?></th>
        <th width="40%"scope="col"><?php echo $this->lang->line('email'); ?>
        <th width="20%"scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
    </tr>
    
    <?php $is_default = FALSE; foreach ($contacts as $contct){ 
        if($contct->is_default == '1'){ $is_default = TRUE; break; } } ?>
    
    <?php foreach ($contacts as $contact) { ?>
        <tr>
            <td class="first" nowrap="nowrap">
                <?php echo anchor('clients/contacts/details/client_id/' . $contact->client_id . '/contact_id/' . $contact->contact_id, $contact->contact_name, 'class="entity_' . $contact->contact_active . '"'); ?>
            </td>
            <td><?php echo $contact->email_address; ?></td>
            <td class="last" style="width: 15%;">
                <a href="<?php echo site_url('clients/contacts/details/client_id/' . $contact->client_id . '/contact_id/' . $contact->contact_id); ?>" title="<?php echo $this->lang->line('view'); ?>">
                    <?php echo icon('edit'); ?>
                </a>
                <?php if (!$this->mdl_mcb_data->setting('disable_delete_links')) { ?>
                    <a href="<?php echo site_url('clients/contacts/delete/client_id/' . $contact->client_id . '/contact_id/' . $contact->contact_id); ?>" title="<?php echo $this->lang->line('delete'); ?>" onclick="javascript:if (!confirm('<?php echo $this->lang->line('confirm_delete'); ?>'))
                            return false">
                        <?php echo icon('delete'); ?>
                    </a>
                <?php } ?>
                
                <?php if($is_default == TRUE){ ?>
                <?php if($contact->is_default == '1'){ ?>
                <a style="font-size: 12px;" href="<?= site_url('clients/remove_default_contact_email')?>?client_id=<?=$contact->client_id?>&contact_id=<?=$contact->contact_id?>&redirect_url=<?= site_url('clients/details/client_id/'.$contact->client_id)?>">Remove Default</a>
                <?php } } else { ?>
                    <a style="font-size: 12px;" href="<?= site_url('clients/make_default_contact_email')?>?client_id=<?=$contact->client_id?>&contact_id=<?=$contact->contact_id?>&redirect_url=<?= site_url('clients/details/client_id/'.$contact->client_id)?>">Make Default</a>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
</table>