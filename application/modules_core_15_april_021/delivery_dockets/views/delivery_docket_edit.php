<?php $this->load->view('dashboard/header', array('header_insert'=>'delivery_dockets/delivery_docket_edit_header')); ?>


<?php $this->load->view('dashboard/jquery_date_picker'); ?>

<script type="text/javascript">
	$(function(){
		$('#tabs').tabs({ selected: <?php echo $tab_index; ?> });
	});
</script>

<div class="grid_12" id="content_wrapper">

	<form method="post" action="<?php echo site_url($this->uri->uri_string()); ?>">

		<div class="section_wrapper">

			<div class="title_black">


				<div class="title_btns">
					<?php $this->load->view('dashboard/btn_add', array('btn_name'=>'btn_download_pdf', 'btn_value'=>$this->lang->line('pdf_download'))); ?>
					<?php $this->load->view('dashboard/btn_add', array('btn_name'=>'btn_download_pick_list_pdf', 'btn_value'=>$this->lang->line('pdf_download_pick_list'))); ?>
                                    <form method="post" action="<?php echo site_url('invoices/edit/invoice_id/'.$docket->invoice_id) ?>" style="display:inline">
                                        <input type="submit" style="float: right; margin-top: 10px; margin-right: 10px;" value="Back to Invoice" />
                                    </form>
				</div>

				<h3><?php echo $docket->client_name .
				' &ndash; ' . $this->lang->line('invoice_number') . ' ' . $docket->invoice_number .
				' &ndash; ' . $this->lang->line('delivery_docket_number') . ' ' . $docket->docket_number; ?>
				</h3>

				<p class="sub_title"><?php echo ($docket->project_id == 0 ? '' : $docket->project_name); ?></p>

			</div>

			<?php $this->load->view('dashboard/system_messages'); ?>

			<div class="content toggle">

				<div id="tabs">
					<ul>
						<li><a href="#tab_general"><?php echo $this->lang->line('summary'); ?></a></li>
						<li><a href="#tab_address"><?php echo $this->lang->line('delivery_address'); ?></a></li>
						<li><a href="#tab_items"><?php echo $this->lang->line('items'); ?></a></li>
                                                <li><a href="#tab_payment">Payment</a></li>

					</ul>
					<div id="tab_general">
						<?php $this->load->view('tab_general'); ?>
					</div>
					<div id="tab_address">
						<?php $this->load->view('tab_address'); ?>
					</div>
					<div id="tab_items">
						<?php $this->load->view('delivery_docket_item_grid'); ?>
					</div>

                                        <div id="tab_payment">
						<?php $this->load->view('delivery_docket_payment'); ?>
					</div>

                                        

				</div>

				<div style="clear: both;">&nbsp;</div>

			</div>

		</div>

	</form>

</div>

<?php $this->load->view('dashboard/footer'); ?>