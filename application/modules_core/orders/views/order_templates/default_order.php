<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>
            <?php echo $this->lang->line('order_number'); ?>
            <?php echo $order->order_number; ?>
        </title>
        <link href="<?php echo base_url(); ?>assets/style/css/output.css" rel="stylesheet" type="text/css" media="all" />

    </head>
    <body>

        <!--mpdf
        
        <htmlpageheader name="litesource_firstpage_header">
        <?php $this->load->view('invoices/invoice_templates/default_firstpage_header', $user); ?>
        </htmlpageheader>
 
        <sethtmlpageheader name="litesource_firstpage_header" value="on" show-this-page="1"/>
        
        mpdf-->

        <!--mpdf
        
        <htmlpageheader name="litesource_header">
        <?php $this->load->view('invoices/invoice_templates/default_header', $user); ?>
        </htmlpageheader>
 
        <sethtmlpageheader name="litesource_header" page="OE" value="on" />
        
        mpdf-->

        <!--mpdf
        <htmlpagefooter name="litesource_footer">
        <?php $this->load->view('orders/order_templates/default_order_footer', $order); ?>
        </htmlpagefooter>
 
        <sethtmlpagefooter name="litesource_footer" value="on" />
        mpdf-->

        <table class="info_header">
            <tr>
                <td class="header">
                    <?php echo $this->lang->line('order_number'); ?>
                </td>
                <td class="data">
                    <?php echo $order->order_number; ?>
                </td>
            </tr>

        </table>

        <table class="info">

            <tr>
                <td class="header" width="12%">
                    <?php echo $this->lang->line('date') . ':'; ?>
                </td>
                <td class="data">
                    <?php echo date('d/m/Y', $order->order_date_entered); ?>
                </td>
                <td width="10%"></td>
                <td class="header" width="40%">
                    <?php echo $this->lang->line('deliver_to') . ':'; ?>
                </td>
            </tr>
            <tr>
                <td class="header">
                    <?php echo $this->lang->line('attention') . ':'; ?>
                </td>
                <td class="data">
                    <?php echo $order->contact_name; ?><br />
                    <?php echo $order->client_name; ?>
                </td>
                <td></td>
                <td rowspan="2" class="data delivery_address">
                    <?php echo format_delivery_address($address); ?>
                </td>
            </tr>			
            <tr>
                <td class="header">
                    <?php echo $this->lang->line('project') . ':'; ?>
                </td>
                <td class="data">
                    <?php echo $order->project_name; ?>
                </td>
            </tr>


            <tr><td><br/><br/></td></tr>

        </table>

        <table class="items">
            <thead>
                <tr>
                    <th class="item_header" width="6%" align="left">
                        <?php echo $this->lang->line('qty'); ?>
                    </th>
                    <th class="item_header" width="17%">
                        <?php echo $this->lang->line('catalog_number'); ?>
                    </th>
                    <th class="item_header" width="8%">
                        <?php echo $this->lang->line('item_type'); ?>
                    </th>
                    <th class="item_header">
                        <?php echo $this->lang->line('description'); ?>
                    </th>

                    <?php 
//                    echo '<pre>';
//                    print_r($order_items);
//                    die;
                    ?>
                    <?php $is_use_len = FALSE; ?>
                    <?php foreach ($order_items as $item2) { ?>
                    <?php if ($item2->is_length == '1') { 
                        $is_use_len = TRUE;
//                        break;
                    } ?>
                    <?php } ?>
                    <?php if($is_use_len == TRUE){ ?>
                        <th class="item_header" width="18%" align="right">
                            Length
                        </th>
                        <th class="item_header" width="10%" align="right">
                            Total Length
                        </th>
                    <?php } ?>
                    
                    
                    <th class="item_header" width="8%" align="right">
                        <?php echo $this->lang->line('price'); ?>
                    </th>
                    <th class="item_header" width="11%" align="right">
                        <?php echo $this->lang->line('total'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item) { ?>
                    <tr class="item">
                        <td >
                            <?= format_qty($item->qty); ?>
                        </td>
                        <td class="data" >
                            <?php
                            if ($order->is_inventory_supplier == '1') {
                                
                                if( ($item->item_length > '0') && (strpos($item->item_supplier_code, '{mm}')) ){    
                                    $item->item_supplier_code = mm_to_span($item->item_supplier_code, $item->item_length, 1000);
                                }
                                //echo $item->item_supplier_code;
                                $item->item_supplier_code = str_replace('<span>','',$item->item_supplier_code);
                                $item->item_supplier_code = str_replace('</span>','',$item->item_supplier_code);
                                echo wordwrap($item->item_supplier_code, 16, "\n", true);
                            } else {
                                echo wordwrap($item->item_name, 16, "\n", true);
                            }
                            ?>
                        </td>
                        <td class="item_type" >
                            <?php echo $item->item_type; ?>
                        </td>
                        <td >
                            <?php
                            if ($order->is_inventory_supplier == '1') {
                                if( ($item->item_length > '0') && (strpos($item->item_supplier_description, '{mm}')) ){    
                                    $item->item_supplier_description = mm_to_span($item->item_supplier_description, $item->item_length, 1000);
                                }
                                $item->item_supplier_description = str_replace('<span>','',$item->item_supplier_description);
                                $item->item_supplier_description = str_replace('</span>','',$item->item_supplier_description);
                                echo $item->item_supplier_description;
                            } else {
                                echo $item->item_description;
                            }
                            ?>
                        </td>
                        
                        
                        
                    <?php if($is_use_len == TRUE){ ?>
                        <td class="item_header" width="10%" align="right">
                            <?php if ($item->is_length == '1') { ?>
                            
                            <?php if($item->item_length == '1'){ ?>
                            Per Metre
                            <?php }else{ ?>
                            <?php echo ($item->item_length)*1000; ?>'mm'
                            <?php } ?>
                            <?php }else{ echo ''; } ?>
                        </td>
                        <td class="item_header" width="13%" align="right">
                            <?php if ($item->is_length == '1') { ?>
                            <?php if($item->item_length == '1'){ ?>
                            Per Metre
                            <?php }else{ ?>
                            <?= ($item->item_length)*1000*($item->qty) ?>mm
                            <?php } ?>
                            <?php }else{ echo ''; } ?>
                            
                        </td>
                    <?php    } ?>
                        
                        
                        <td align="right" >
                    <?php echo $order->currency_symbol_left . format_number($item->item_supplier_price, 2) . $order->currency_symbol_right; ?>
                        </td>
                        <td align="right" >
    <?php echo $order->currency_symbol_left . $item->item_subtotal . $order->currency_symbol_right; ?>
                        </td>
                    </tr>
<?php } ?>
            </tbody>
        </table>

        <br />

        <table class="summary">
            <thead>
                <tr>
                    <th width="5%" align="right"></th>
                    <th width="15%"></th>
                    <th></th>		
                    <th width="12%" align="right"></th>
                    <th width="13%" align="right"></th>
                </tr>
            </thead>
            <tbody>
                    <?php if ($order->tax_rate_percent > 0) { ?>
                    <tr>			
                        <td colspan="2"></td>
                        <td>
                        <?php echo $this->lang->line('exclusive_of') . ' ' . $order->tax_rate_name; ?>
                        </td>
                        <td></td>
                        <td>
                            <?php echo $order->currency_symbol_left . $order->order_sub_total . $order->currency_symbol_right; ?>

                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td>
                    <?php echo $order->tax_rate_percent_name; ?>
                        </td>
                        <td></td>
                        <td>
                            <?php echo $order->currency_symbol_left . $order->tax_total . $order->currency_symbol_right; ?>
                        </td>
                    </tr>
                    <?php } ?>
                <tr>
                    <td colspan="2"></td>
                    <td>
                    <?php echo $this->lang->line('total') . ' (' . $order->currency_code . ')'; ?>
                    </td>
                    <td></td>
                    <td>
                    <?php echo $order->currency_symbol_left . $order->order_total . $order->currency_symbol_right; ?>
                    </td>
                </tr>

                <?php if ($order->order_has_notes) { ?>
                    <tr class="divider">
                        <td colspan="5"></td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td colspan="3" class="default_notes"><?php echo nl2br($order->order_notes); ?></td>
                    </tr>
                <?php } ?>


            </tbody>

        </table>


    </body>
</html>