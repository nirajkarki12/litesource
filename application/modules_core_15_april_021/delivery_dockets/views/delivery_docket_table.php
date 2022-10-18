<style>
    
    .delvery-tbl tbody tr th{
        font-size: 11px;
    }
    
    .delvery-tbl tbody tr td button{
        font-size: 10px;
        /* width: 130%; */
        padding: 0px;
        margin: 1%;
        width: 140%;
    }
    
    .dlvry-actn{
        padding-right: 20px;
    }
    
    .dlvry-actn table{
        width: 100%;
        padding: 0%;
        margin: 0%;
    }
    
    
    
    .dlvry-actn table td{
        padding-left: 0px;
    }
    
    #tab_dockets{
        padding: 0.5em;
    }
</style>

<table style="width: 100%;" class="normaltable delvery-tbl">

    <tr>
        <?php if (isset($sort_links)) { ?>
            <th width="10%" scope="col" class="first"><?php echo anchor('delivery_dockets/index/order_by/docket_number', $this->lang->line('docket_number')); ?></th>

            <th width="10%" scope="col">Invoice Date</th>

            <th width="10%" scope="col"><?php echo anchor('delivery_dockets/index/order_by/date', $this->lang->line('date')); ?></th>

            <th width="10%" scope="col">Due Days</th>

            <th width="20%" scope="col" class="client"><?php echo anchor('delivery_dockets/index/order_by/client', $this->lang->line('client')); ?></th>
            <th width="10%" scope="col"><?php echo anchor('delivery_dockets/index/order_by/invoice', $this->lang->line('invoice_number')); ?></th>
            <th scope="col"><?php echo anchor('delivery_dockets/index/order_by/project', $this->lang->line('project')); ?></th>
            <th scope="col">Status</th>
            <th width="30%" scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
        <?php } else { ?>
            <th width="10%" scope="col" class="first"><?php echo $this->lang->line('docket_number'); ?></th>

            <th width="10%" scope="col">Invoice Date</th>

            <th width="10%" scope="col">Date Created</th>

            <th width="10%" scope="col">Due Days</th>
            <th width="11%" scope="col">Owing Amount</th>

            <th width="15%" scope="col" class="client"><?php echo $this->lang->line('client'); ?></th>
            <th width="8%" scope="col"><?php echo $this->lang->line('invoice_number'); ?></th>
            <th scope="col" width="15%"><?php echo $this->lang->line('project'); ?></th>
            <th scope="col" width="9%">Status</th>
            <th scope="col">Delivery</th>
            <th width="10%" scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
        <?php } ?>
    </tr>

    <?php foreach ($dockets as $docket) { ?>

        <tr>
            <td class="first"><?php echo anchor('delivery_dockets/edit/docket_id/' . $docket->docket_id, $docket->docket_number); ?></td>

            <td><?php echo format_date($docket->invoice_date); ?></td>

            <td><?php echo format_date($docket->docket_date_entered); ?></td>

            <td><?= docket_due_days($docket); ?></td>
            
            <td><?= docket_owing_with_currency($docket); ?></td>
                
            <td class="client">
                <?php
                if ($docket->client_name) {
                    echo anchor('clients/details/client_id/' . $docket->client_id, character_limiter($docket->client_name, 25));
                }
                ?>
            </td>
            <td><?php echo anchor('invoices/edit/invoice_id/' . $docket->invoice_id, $docket->invoice_number); ?></td>
            <td>
                <?php if ($docket->project_name) { ?>
                    <?php echo anchor('projects/details/project_id/' . $docket->project_id, character_limiter($docket->project_name, 30)); ?>
                <?php } ?>
            </td>

            <td scope="col">
                <?php if (($docket->paid_status) == '1') { ?>
                    Paid
                <?php } else if (($docket->invoice_sent) == '1') { ?>
                    Sent
                <?php } else if (($docket->invoice_sent) == '0') { ?>
                    Not Sent
                <?php } ?>         
            </td>

    <!--<td scope="col"><?php echo ($docket->invoice_sent == '1') ? 'Sent' : 'Not Sent' ?></td>-->

            <td scope="col">
                <?php if (($docket->docket_delivery_status) == '0') { ?>
                    Undelivered
                <?php } else if (($docket->docket_delivery_status) == '1') { ?>
                    Delivered
                <?php } ?>         
            </td>


            <td class="dlvry-actn last" style="padding-right: 2px !important;">
                <table style="">
                    <tr>
                        <td style="">
                            <form action="<?php echo site_url('delivery_dockets/generatedocketinvoice/docket_id') . '/' . $docket->docket_id; ?>">
                                <button class="button btnopennewtab">
                                    Generate Invoice
                                </button>
                            </form>
                        </td>
                        <td style="">
                            <form action="<?php echo site_url('delivery_dockets/senddocketinvoice/docket_id') . '/' . $docket->docket_id; ?>">
                                <button class="button">
                                    Send invoice
                                </button>
                            </form>
                        </td>
                        
                        <td style="">
                            <?php if (($docket->invoice_sent) == '0') { ?>
                            <form action="<?php echo site_url('delivery_dockets/do_sent') . '/' . $docket->docket_id; ?>">
                                <button class="button">Mark as sent</button>
                            </form>

                            <?php } else if (($docket->invoice_sent) == '1') { ?>
                            <form action="<?php echo site_url('delivery_dockets/do_unsent') . '/' . $docket->docket_id; ?>">
                                <button class="button">
                                    Mark as unsent
                                </button>
                            </form>
                            <?php } ?>
                        </td>
                        
                    </tr>
                    
                    
                    <tr>
                        <td>
                            <?php if (($docket->docket_delivery_status) == '0' || $docket->docket_delivery_status == NULL) { ?>
                            <form action="<?php echo site_url('delivery_dockets/finalizedelivery/docket_id') . '/' . $docket->docket_id; ?>">
                                <button class="button">
                                    Finalise Delivery
                                </button>
                            </form>

                            <?php } else if (($docket->docket_delivery_status) == '1') { ?>
                            <form action="<?php echo site_url('delivery_dockets/canceldelivery/docket_id') . '/' . $docket->docket_id; ?>">
                                <button class="button">
                                    Cancel Delivery
                                </button>
                            </form>
                            <?php } ?>
                        </td>
                        
                        <td>
                                <?php if (($docket->paid_status) == '0' || $docket->docket_delivery_status == NULL) { ?>
                                <form action="<?php echo site_url('delivery_dockets/do_paid/docket_id') . '/' . $docket->docket_id; ?>">
                                    <button class="button">
                                        Mark as Paid
                                    </button>
                                </form>

                                <?php } else if (($docket->paid_status) == '1') { ?>
                                <form action="<?php echo site_url('delivery_dockets/do_unpaid/docket_id') . '/' . $docket->docket_id; ?>">
                                    <button class="button">
                                        Mark as Unpaid
                                    </button>
                                </form>
                                <?php } ?>
                        </td>
                    </tr>
                    
                </table>


                <a href="<?php echo site_url('delivery_dockets/edit/docket_id/' . $docket->docket_id); ?>" title="<?php echo $this->lang->line('edit'); ?>">
                    <?php echo icon('edit'); ?>
                </a>
                <a href="<?php echo site_url('delivery_dockets/generate_pdf/docket_id') . '/' . $docket->docket_id . '/docket_template/pick_list'; ?>">
                    <?php echo icon('picking'); ?>
                </a>
                <a href="<?php echo site_url('delivery_dockets/generate_pdf/docket_id') . '/' . $docket->docket_id; ?>">
                <?php echo icon('pdf'); ?>
                </a>

                       <?php if (!$this->mdl_mcb_data->setting('disable_delete_links')) { ?>
                    <a href="<?php echo site_url('delivery_dockets/delete/docket_id/' . $docket->docket_id); ?>" title="<?php echo $this->lang->line('delete'); ?>" onclick="javascript:if (!confirm('<?php echo $this->lang->line('confirm_delete'); ?>'))
                                return false">
        <?php echo icon('delete'); ?>
                    </a>
    <?php } ?>

            </td>
        </tr>

<?php } ?>
</table>

<?php if ($this->mdl_delivery_dockets->page_links) { ?>
    <div id="pagination">
    <?php echo $this->mdl_delivery_dockets->page_links; ?>
    </div>
<?php } ?>
<script type="text/javascript">
    $('.btnopennewtab').on('click', function (e) {
        e.preventDefault();
        var url = $(this).parent('form').attr('action');
        window.open(url, '_blank');
    });
</script>