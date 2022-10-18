<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Mdl_Invoice_Amounts extends CI_Model {
    /**
     * TABLE: mcb_invoice_items
     * invoice_item_id
     * invoice_id
     * item_name
     * item_qty
     * item_price
     * tax_rate_id
     * item_is_taxable
     */
    /**
     * TABLE: mcb_invoice_item_amounts
     * invoice_item_amount_id
     * invoice_item_id
     * item_subtotal
     * item_tax
     * item_total
     */
    /**
     * TABLE: mcb_tax_rates
     * tax_rate_id
     * tax_rate_name
     * tax_rate_percent
     */
    /**
     * TABLE: mcb_invoice_tax_rates	Global taxes
     * invoice_tax_rate_id
     * invoice_id
     * tax_rate_id
     * tax_rate_option				1 = Normal, 2 = After last tax
     * tax_amount					Calculated amt of tax
     * taxed_amount					Current invoice total
     */

    /**
     * TABLE: mcb_invoice_amounts
     * invoice_amount_id
     * invoice_id
     * invoice_item_subtotal	Sum of mcb_invoice_item_amounts.item_total
     * invoice_item_taxable		Sum of mcb_invoice_item_amounts.item_total where is_taxable = 1
     * invoice_item_tax			Sum of mcb_invoice_item_amounts.item_tax
     * invoice_subtotal			(invoice_item_subtotal + invoice_item_tax)
     * invoice_tax				Sum of global invoice tax amounts
     * invoice_shipping			invoice_shipping
     * invoice_discount			invoice_discount
     * invoice_paid				Sum of mcb_payments.payment_amount
     * invoice_total			invoice_subtotal + invoice_tax + invoice_shipping - invoice_discount
     * invoice_balance			invoice_total - invoice_paid
     */
    function adjust($invoice_id = NULL) {

        if ($invoice_id) {
            
            
            /* Adjust a single invoice */
            $this->_adjust($invoice_id);
        } else {

            /* Adjust all invoices */
            $this->db->select('invoice_id, client_id');

            $invoices = $this->db->get('mcb_invoices')->result();

            foreach ($invoices as $invoice) {

                $this->_adjust($invoice->invoice_id);
            }
        }
    }

    function _adjust($invoice_id) {

        //$this->db->join('mcb_tax_rates', 'mcb_tax_rates.tax_rate_id = mcb_invoice_items.tax_rate_id', 'left');
        $this->db->join('mcb_invoices', 'mcb_invoices.invoice_id = mcb_invoice_items.invoice_id', 'left');
        $this->db->join('mcb_tax_rates', 'mcb_tax_rates.tax_rate_id = mcb_invoices.invoice_tax_rate_id', 'left');

        $this->db->where('mcb_invoice_items.invoice_id', $invoice_id);
        $this->db->order_by('item_index');

        $invoice_items = $this->db->get('mcb_invoice_items')->result();

        $rst = 0;

        foreach ($invoice_items as $item) {

            $rst = $this->_update_invoice_item_amounts($item, $rst);
        }

        $this->_update_invoice_amounts($invoice_id);
        


        //$this->_update_invoice_taxes($invoice_id);
        // TICKET 9: invoice status will be changed manually - not based on whether the invoice has been paid or not
        //$this->_update_invoice_status($invoice_id);
    }

    function delete_invoice_item_amounts($invoice_id) {

        $this->db->where('invoice_id', $invoice_id);

        $this->db->delete('mcb_invoice_item_amounts');

        $ret = $this->db->affected_rows();

        if ($ret > 0) {
            log_message('debug', 'Deleted ' . $ret . ' invoice_item_amounts from invoice: ' . $invoice_id);
        }
    }

    function _update_invoice_item_amounts($item, $rst) {

        /* Calculations for mcb_invoice_item_amounts table */
        $item_subtotal = $item->item_qty * $item->item_price;

        // rolling subtotal row
        if ($item->item_qty == -1) {
            //why this?
            $item_subtotal = $rst;
            $rst = 0;
        } else {

            $rst += $item_subtotal;
        }

        $db_array = array(
            'invoice_id' => $item->invoice_id,
            'invoice_item_id' => $item->invoice_item_id,
            'item_subtotal' => $item_subtotal
        );

        if ($item->tax_rate_percent) {

            $db_array['item_tax'] = $db_array['item_subtotal'] * ($item->tax_rate_percent / 100);
        } else {

            $db_array['item_tax'] = '0.00';
        }

        $db_array['item_total'] = $db_array['item_subtotal'] + $db_array['item_tax'];

        $this->db->where('invoice_item_id', $item->invoice_item_id);

        if ($this->db->get('mcb_invoice_item_amounts')->num_rows()) {

            $this->db->where('invoice_item_id', $item->invoice_item_id);

            $this->db->update('mcb_invoice_item_amounts', $db_array);
        } else {

            $this->db->insert('mcb_invoice_item_amounts', $db_array);
        }

        return $rst;
    }

    function _update_invoice_amounts($invoice_id) {

        /* Calculations for mcb_invoice_amounts table */

        /**
         * TABLE: mcb_invoice_item_amounts
         * invoice_item_amount_id
         * invoice_item_id
         * item_subtotal
         * item_tax
         * item_total
         */
        /**
         * invoice_amount_id
         * invoice_id
         * invoice_item_subtotal	Sum of mcb_invoice_item_amounts.item_subtotal
         * invoice_item_tax			Sum of mcb_invoice_item_amounts.item_tax
         * invoice_subtotal			(invoice_item_subtotal + invoice_item_tax)
         * invoice_tax				Sum of global invoice tax amounts
         * invoice_shipping			invoice_shipping
         * invoice_discount			invoice_discount
         * invoice_paid				Sum of mcb_payments.payment_amount
         * invoice_total			invoice_subtotal + invoice_tax + invoice_shipping - invoice_discount
         * invoice_balance			invoice_total - invoice_paid
         */
        /*
          $this->db->select(
          'IFNULL(SUM(item_subtotal),0.00) AS invoice_item_subtotal, ' .
          'IFNULL(SUM(IF(is_taxable = 1, item_subtotal, 0.00)),0.00) AS invoice_item_taxable, ' .
          'IFNULL(SUM(item_tax),0.00) AS invoice_item_tax, ' .
          'IFNULL((SELECT SUM(payment_amount) FROM mcb_payments WHERE invoice_id = ' . $invoice_id . '),0.00) AS invoice_paid',
          FALSE
          );
         */

        /*
         *   
         */
        
        $this->db->select(
                'IFNULL(SUM(item_subtotal),0.00) AS invoice_item_subtotal, (select mcb_tax_rates.tax_rate_percent from mcb_invoices left join mcb_tax_rates on mcb_invoices.invoice_tax_rate_id = mcb_tax_rates.tax_rate_id where mcb_invoices.invoice_id = "'.$invoice_id.'" ) as tax_rate_percent, ' .
                'IFNULL(SUM(item_tax),0.00) AS invoice_item_tax, ' .
                'IFNULL((SELECT SUM(payment_amount) FROM mcb_payments WHERE invoice_id = ' . $invoice_id . '),0.00) AS invoice_paid', FALSE
        );

        $this->db->join('mcb_invoice_items AS i', 'i.invoice_item_id = mcb_invoice_item_amounts.invoice_item_id');
        
        $this->db->where('i.invoice_id', $invoice_id);
        $this->db->where('i.item_qty >', '0.00');

        $invoice_amounts = $this->db->get('mcb_invoice_item_amounts')->result();
        
        
        //there seems to be a problem due to rounding off, going to get the invoice item tax from tax percentage of  $invoice_amount->invoice_item_subtotal
        
        foreach ($invoice_amounts as $invoice_amount) {
            
            $db_array = array(
                'invoice_id' => $invoice_id,
                'invoice_item_subtotal' => $invoice_amount->invoice_item_subtotal,
                //'invoice_item_taxable'		=>	$invoice_amount->invoice_item_taxable,
                'invoice_item_tax' => ($invoice_amount->tax_rate_percent*$invoice_amount->invoice_item_subtotal/100),
                'invoice_subtotal' => ($invoice_amount->invoice_item_subtotal + ($invoice_amount->tax_rate_percent*$invoice_amount->invoice_item_subtotal/100)),
                'invoice_paid' => $invoice_amount->invoice_paid
            );

            $this->db->where('invoice_id', $invoice_id);

            if ($this->db->get('mcb_invoice_amounts')->num_rows()) {

                $this->db->where('invoice_id', $invoice_id);

                $this->db->update('mcb_invoice_amounts', $db_array);
            } else {

                $this->db->insert('mcb_invoice_amounts', $db_array);
            }
        }


        $this->db->set('invoice_total', 'invoice_subtotal - invoice_discount + invoice_shipping', FALSE);

        $this->db->set('invoice_balance', 'invoice_total - invoice_paid', FALSE);

        $this->db->update('mcb_invoice_amounts');
    }

    function _update_invoice_status($invoice_id) {

        $this->load->model('invoices/mdl_invoices');

        $params = array(
            'where' => array(
                'mcb_invoices.invoice_id' => $invoice_id
            )
        );

        $invoice = $this->mdl_invoices->get($params);

        if ($invoice->invoice_balance > 0) {

            /* This invoice has a balance */

            if ($invoice->invoice_status_type == 3) {

                /* This invoice currently has a closed status. Update it. */

                $db_array = array(
                    'invoice_status_id' => $this->mdl_mcb_data->setting('default_open_status_id')
                );

                $this->db->where('invoice_id', $invoice_id);

                $this->db->update('mcb_invoices', $db_array);
            }
        } else {

            /* This invoice has no balance */

            if ($invoice->invoice_status_type <> 3 and $invoice->invoice_total > 0) {

                /* This invoice needs a closed status */

                $db_array = array(
                    'invoice_status_id' => $this->mdl_mcb_data->setting('default_closed_status_id')
                );

                $this->db->where('invoice_id', $invoice_id);

                $this->db->update('mcb_invoices', $db_array);
            }
        }
    }

    function _update_invoice_taxes($invoice_id) {

        /**
         * invoice_tax				Sum of global invoice tax amounts
         * invoice_total			invoice_subtotal + invoice_tax + invoice_shipping - invoice_discount
         * invoice_balance			invoice_total - invoice_paid
         */
        /**
         * invoice_amount_id
         * invoice_id
         * invoice_item_subtotal	Sum of mcb_invoice_item_amounts.item_total
         * invoice_item_tax			Sum of mcb_invoice_item_amounts.item_tax
         * invoice_subtotal			(invoice_item_subtotal + invoice_item_tax)
         * invoice_tax				Sum of global invoice tax amounts
         * invoice_shipping			invoice_shipping
         * invoice_discount			invoice_discount
         * invoice_paid				Sum of mcb_payments.payment_amount
         * invoice_total			invoice_subtotal + invoice_tax + invoice_shipping - invoice_discount
         * invoice_balance			invoice_total - invoice_paid
         */
        $this->db->join('mcb_invoice_amounts', 'mcb_invoice_amounts.invoice_id = mcb_invoice_tax_rates.invoice_id');

        $this->db->join('mcb_tax_rates', 'mcb_tax_rates.tax_rate_id = mcb_invoice_tax_rates.tax_rate_id');

        $this->db->where('mcb_invoice_tax_rates.invoice_id', $invoice_id);

        $invoice_tax_rates = $this->db->get('mcb_invoice_tax_rates')->result();

        foreach ($invoice_tax_rates as $rate) {

            if ($rate->tax_rate_option == 1) {

                /* Calculate w/o item taxes */

                $db_array = array(
                    'tax_amount' => $rate->invoice_item_taxable * ($rate->tax_rate_percent / 100)
                );

                $this->db->where('invoice_tax_rate_id', $rate->invoice_tax_rate_id);

                $this->db->update('mcb_invoice_tax_rates', $db_array);
            } elseif ($rate->tax_rate_option == 2) {

                /* Calculate with item taxes */

                $db_array = array(
                    'tax_amount' => $rate->invoice_subtotal * ($rate->tax_rate_percent / 100)
                );

                $this->db->where('invoice_tax_rate_id', $rate->invoice_tax_rate_id);

                $this->db->update('mcb_invoice_tax_rates', $db_array);
            }

            $this->db->select('SUM(tax_amount) AS invoice_tax');

            $this->db->where('invoice_id', $invoice_id);

            $invoice_tax = $this->db->get('mcb_invoice_tax_rates')->row()->invoice_tax;

            $this->db->where('invoice_id', $invoice_id);

            $this->db->set('invoice_tax', $invoice_tax);

            $this->db->set('invoice_total', 'invoice_subtotal + invoice_tax - invoice_discount + invoice_shipping', FALSE);

            $this->db->set('invoice_balance', 'invoice_total - invoice_paid', FALSE);

            $this->db->update('mcb_invoice_amounts');

            $this->db->where('invoice_balance <', '0.00');

            $db_array = array(
                'invoice_balance' => '0.00'
            );

            $this->db->update('mcb_invoice_amounts', $db_array);
        }
    }

}

?>