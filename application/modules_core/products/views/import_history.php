<?php $this->load->view('dashboard/header'); ?>
<div class="section_wrapper">

    <h3 class="title_black">
        Product Import History
        <?php //if ($this->session->userdata('global_admin')) { ?>
		<?php $this->load->view('dashboard/btn_add', array('btn_name'=>'btn_add_product', 'btn_value'=>$this->lang->line('add_product'))); ?>
		<?php $this->load->view('dashboard/btn_add', array('btn_name'=>'btn_upload_products', 'btn_value'=>$this->lang->line('upload_product_file'))); ?>
        <?php //} ?>
    </h3>

    <div class="content toggle no_padding">
        <?php $this->load->view('dashboard/system_messages'); ?>
        <style>
            table {
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            td, th {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
            }

            tr:nth-child(even) {
                background-color: #dddddd;
            }
        </style>
        <body>
            <table>
                <tr>
                    <th>S.N</th>
                    <th>Import Date</th>
                    <th>Imported File</th>
                    <th>Action</th>
                </tr>
                <?php if (sizeof($import_history) > 0): ?>
                    <?php $i = 1; foreach ($import_history as $item): ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $item->imported_at; ?></td>
                            <td><?php echo $item->import_from_file_name; ?></td>
                            <td>
                                <!--  Roll back is allowed for latest item only-->
                                <?php if($i == 1): ?>
                                <a href="<?php echo site_url('products/dorollback/'.$item->id); ?>"  onclick="return confirm('Are you sure you want to rollback?');">Rollback</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php $i++;
                    endforeach; ?>
                <?php endif; ?>
            </table>
    </div>
</div>
<?php $this->load->view('dashboard/footer'); ?>