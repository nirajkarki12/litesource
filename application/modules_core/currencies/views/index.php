<?php $this->load->view('dashboard/header'); ?>

<div class="grid_7" id="content_wrapper">

	<div class="section_wrapper">

		<h3 class="title_black"><?php echo $this->lang->line('currencies'); ?><?php $this->load->view('dashboard/btn_add', array('btn_value'=>$this->lang->line('add_currency'))); ?></h3>

		<?php $this->load->view('dashboard/system_messages'); ?>

		<div class="content toggle no_padding">

			<table>
				<tr>
					<th scope="col" class="first"><?php echo $this->lang->line('id'); ?></th>
					<th scope="col"><?php echo $this->lang->line('currency_name'); ?></th>
					<th scope="col"><?php echo $this->lang->line('currency_code'); ?></th>
					<th scope="col"><?php echo $this->lang->line('currency_symbol'); ?></th>
					<th scope="col" class="last"><?php echo $this->lang->line('actions'); ?></th>
				</tr>
				<?php foreach ($currencies as $currency) { ?>
				<tr>
					<td class="first"><?php echo $currency->currency_id; ?></td>
					<td><?php echo $currency->currency_name; ?></td>
					<td><?php echo $currency->currency_code; ?></td>
					<td><?php echo $currency->currency_symbol; ?></td>
					<td class="last">
						<a href="<?php echo site_url('currencies/form/currency_id/' . $currency->currency_id); ?>" title="<?php echo $this->lang->line('edit'); ?>">
							<?php echo icon('edit'); ?>
						</a>
						<a href="<?php echo site_url('currencies/delete/currency_id/' . $currency->currency_id); ?>" title="<?php echo $this->lang->line('delete'); ?>" onclick="javascript:if(!confirm('<?php echo $this->lang->line('confirm_delete'); ?>')) return false">
							<?php echo icon('delete'); ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</table>

			<?php if ($this->mdl_currencies->page_links) { ?>
			<div id="pagination">
				<?php echo $this->mdl_currencies->page_links; ?>
			</div>
			<?php } ?>

		</div>

	</div>

</div>

<?php $this->load->view('dashboard/sidebar', array('side_block'=>array('settings/sidebar'),'hide_quicklinks'=>TRUE)); ?>

<?php $this->load->view('dashboard/footer'); ?>