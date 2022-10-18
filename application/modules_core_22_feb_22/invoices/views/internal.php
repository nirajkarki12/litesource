<style>
    table tr.odd{
        background: #ccc;
    }
</style>

<button class="btn btn-primary" id="export_invoice_internal_notes" style="margin: 0;float: right;padding: 1px 10px;">CSV »</button>

<table style="width: 100%;" id="export_invoice_internal_notes_table">
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

<script type="text/javascript">
    
    jQuery(document).on('click','#export_invoice_internal_notes',function(e){
        
        e.preventDefault();
        let has_data = jQuery('#export_invoice_internal_notes_table tbody tr td').length;
        if( has_data <'2' ){
            alert('No data is available.');
            return false;
        }
        let csv = '';
        if(  has_data > '1'  ){    
            csv += "SN,Note,User,Date";
            csv += "\n";
            jQuery('#export_invoice_internal_notes_table > tbody  > tr').each(function(row, tr) { 
                if( row>'0' ){
                    let this_tr = jQuery(this);
                    let sn = row;
                    let notes = this_tr.find('td').get(0).textContent;
                    let users = this_tr.find('td').get(1).textContent;
                    let date = this_tr.find('td').get(2).textContent;
                    csv +=  row+","+notes + "," + users + "," + date + ",";
                    csv += "\n";
                }
            });
        }
        
        let dt = new Date();
        let lpf_xprt_time = dt.getHours() + "_" + dt.getMinutes() + "_" + dt.getSeconds();
        let link = document.createElement('a');
        link.id = 'download-csv'
        link.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(csv));
        link.setAttribute('download', 'internal_notes_' + lpf_xprt_time + '.csv');
        document.body.appendChild(link)
        document.querySelector('#download-csv').click();
    });
    
</script>