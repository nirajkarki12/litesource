<table style="width: 100%;" class="normaltable">

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
            <th width="8%" scope="col">Amount</th>

            <th width="15%" scope="col" class="client"><?php echo $this->lang->line('client'); ?></th>
            <th width="8%" scope="col"><?php echo $this->lang->line('invoice_number'); ?></th>
            <th scope="col"><?php echo $this->lang->line('project'); ?></th>
            <th scope="col">Status</th>
            <th scope="col">Delivery</th>
            <th width="10%" scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
        <?php } ?>
    </tr>

    <?php foreach ($dockets as $docket) { ?>

        <tr>
            <td class="first"><?php echo anchor('delivery_dockets/edit/docket_id/' . $docket->docket_id, $docket->docket_number); ?></td>

            <td><?php echo format_date($docket->invoice_date); ?></td>

            <td><?php echo format_date($docket->docket_date_entered); ?></td>

            <td>
                <?php
                $docket_date = $docket->docket_date_entered;
                $due_date = $docket->docket_due_date;
                $current_date = time();
                if (($docket->paid_status) == '1') {
                    echo "-";
                } else {


//                     $days=60*60*24;

// echo "<br>";
// echo round($docket_date/$days);
// echo "<br>";
// echo round($docket_date/($days*12*30));
// echo "<br>";
// echo $due_date;
// echo "<br>";
// echo round($due_date/$days);
// echo "<br>";
// echo $current_date;
// echo "<br>";
// echo round($current_date/$days);
// echo "<br>";
// $day=round(($due_date-$current_date)/(60*60*24));
// echo $day;

//                   $remaining_date=31*60*60*24;
// if($docket_date/(60*60*24)!=31)
// {
//      $due_date=($remaining_date-$docket_date)/(60*60*24)+$remaining_date;
// }
                   // $remaining_date=31;
                   // if($docket_date!=$remaining_date)
                   // {
                   //  $due_date=($remaining_date-$docket_date)+$remaining_date;
                   // }

                   
echo  "docket entry date:".date("Y-m-d",$docket_date);
echo "<br>";
echo $docket_date;
echo "<br>";
$due_date=date("Y-m-t",($due_date));
echo "due-date:".$due_date;
echo "<br>";
echo strtotime($due_date);
$due_date=strtotime($due_date);
echo "<br>";



                    if ((round(($due_date- $current_date) / (60 * 60 * 24))) >= 1) {
                        echo round(($due_date - $current_date) / (60 * 60 * 24)) . ' <p style="color:green;">days still remaning for your paytime.Sorry No Due Yet</p><br>';
                    } elseif (round(($current_date - $due_date) / (60 * 60 * 24)) >= 1) {
                        if ($invoice->smart_status == '1') {
                            $fc = '<br>(Force Closed)';
                        } else {
                            $fc = '';
                        }

                        echo '<p style="color:red;">Overdue:<br>' . round(($current_date - $due_date) / (60 * 60 * 24)) . ' days', '' . $fc . '</p>';
                    } else {
                        echo '<p style="color:red;">Last day</p>';
                    }
              
                }
                ?>
            </td>

            <?php if( $docket->with_tax_owing_amount != '' ){ ?>
            
                <?php if ($docket->with_tax_owing_amount < 0) { ?>
                <td><?= '-' . display_currency(trim($docket->with_tax_owing_amount, '-')) ?></td>
                <?php } else { ?>
                <td><?= display_currency($docket->with_tax_owing_amount) ?></td>
                <?php } ?>
            <?php }else{ ?>
                <?php if ($docket->price_with_tax < 0) { ?>
                <td><?= '-' . display_currency(trim($docket->price_with_tax, '-')) ?></td>
                <?php } else { ?>
                <td><?= display_currency($docket->price_with_tax) ?></td>
                <?php } ?>
            <?php } ?>

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


            <td class="last" style="line-height: 0px;padding: 0px 0px 0px 0px !important;font-size: 12px;">
                <table style="margin: -7px -10px 0px 0px !important;width: 250px;">
                    <tr>
                        <td style="height: 0px; padding: 0px">
                            <form action="<?php echo site_url('delivery_dockets/generatedocketinvoice/docket_id') . '/' . $docket->docket_id; ?>">
                                <button class="button btnopennewtab">
                                    Generate Invoice
                                </button>
                            </form>
                        </td>
                        <td style="height: 0px">
                            <form action="<?php echo site_url('delivery_dockets/senddocketinvoice/docket_id') . '/' . $docket->docket_id; ?>">
                                <button class="button">
                                    Send invoice
                                </button>
                            </form>
                        </td>
                        
                        <td style="height: 0px; padding: 0px">
                            <?php if (($docket->invoice_sent) == '0') { ?>
                            <form action="<?php echo site_url('delivery_dockets/do_sent') . '/' . $docket->docket_id; ?>">
                                <button class="button">
                                    Mark as sent
                                </button>
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
                    <br>
                    
                    <tr>
                        <td style="height: 0px; padding: 0px">
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
                        
                        <td style="height: 0px">
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