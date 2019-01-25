<style>
    table tr.odd{
        background: #ccc;
    }
</style>
<table style="width: 100%;">
    <tbody><tr>
            <th style="width: 80%; text-align: left;">Note</th>
            <th style="width: 10%; text-align: left;">User</th>
            <th style="width: 10%; text-align: left;">Date</th>
            <th style="width: 10%; text-align: left;">Actions</th>
        </tr>
        <?php if(sizeof($internaldetail) > 0): ?>
        <?php $i = 0; foreach($internaldetail as $detail): ?>
        <tr class="<?php if($i%2 == 0) echo 'even'; else echo 'odd'; ?>">
            <td style="text-align: left;"><?php echo $detail->note; ?></td>
            <td style="text-align: left;"><?php echo $detail->username; ?></td>
            <td style="text-align: left;"><?php echo format_date(strtotime($detail->created_date)); ?></td>
            <td><a href="<?php echo site_url('invoices/deleteinternalnote/invoice_id/'.$invoice->invoice_id.'/internal_id/'.$detail->id); ?>" title="Delete" onclick="javascript:if(!confirm('Are you sure you want to delete this record?')) return false">
                    <img style="vertical-align:middle;" src="<?php echo base_url() ?>assets/style/img/icons/delete.png" alt=""></a></td>
        </tr>
        <?php $i++; endforeach; ?>
        <?php else: ?>
        <tr >
            <td colspan="3">No items.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<form action="<?php echo site_url('invoices/addinternalnote/invoice_id/' . $invoice->invoice_id); ?>" method="post">
    <h3 class="title_black"><?php echo $this->lang->line('internal'); ?></h3>
    <div class="content toggle">

        <dl>
            <dt><label><?php echo $this->lang->line('inventory_history_notes'); ?>: </label></dt>
            <dd><textarea class="big_textarea" name="internalnotes" id="internalnotes" value=""><?php echo isset($_POST['internalnotes']) ? $_POST['internalnotes'] : '' ?></textarea></dd>
        </dl>

        <input type="submit" id="btn_submit" name="btn_submit" value="<?php echo $this->lang->line('submit'); ?>" />
        <input type="submit" id="btn_cancel" name="btn_cancel" value="<?php echo $this->lang->line('cancel'); ?>" />
    </div>
</form>

