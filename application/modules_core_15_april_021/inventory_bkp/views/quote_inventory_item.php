<?php
//echo '<pre>';
//print_r($quote_inventory_items);
//echo '</pre>';
//die;
//?>

<?php if($quote_inventory_items) {?>
<table id="sortable_invoice_items" style="width: 100%;">

    <tr>
        <th scope="col" class="first" style="width: 25%;"><?php echo $this->lang->line('quote_number'); ?></th>
        <th scope="col" style="width: 10%;"><?php echo $this->lang->line('count_cat'); ?></th>
    </tr>

    <tbody class="content">
    <?php foreach ($quote_inventory_items as $quote_item) {
        if(!uri_assoc('invoice_item_id', 4) OR uri_assoc('invoice_item_id', 4) <> $invoice_item->invoice_item_id) { ?>
            <tr>
                <td class="first"><a href="<?php echo site_url('invoices/edit/invoice_id/' . $quote_item->invoice_id); ?>"> <?php echo $quote_item->invoice_number; ?></a></td>
                <td><?php echo $quote_item->item_qty; ?></td>
            </tr>
        <?php } } ?>

    </tbody>
</table>
<?php }else {?>
<p><?php echo $this->lang->line('no_records_found'); ?>.</p><br />
<?php }?>