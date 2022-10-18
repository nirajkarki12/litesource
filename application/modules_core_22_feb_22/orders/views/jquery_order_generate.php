<script type="text/javascript">

	$(function() {

		$('#order_output_dialog').dialog({
			modal: true,
			draggable: false,
			resizable: false,
			autoOpen: false,
			width: '400px',
			title: '<?php echo $this->lang->line('generate_order'); ?>',
			buttons: {
				'<?php echo $this->lang->line('generate'); ?>': function() {
					$(this).dialog('close');
					generate_order();
				},
				'<?php echo $this->lang->line('cancel'); ?>': function() {
					$(this).dialog('close');
				}
			}
		});

		$('.order_output_link').click(function() {

			order_id = $(this).attr('id');

			$('#order_output_dialog').dialog('open');

		});

		function generate_order() {

			var order_output_type = $('#order_output_type').val();

			var order_template = $('#order_template').val();

			if (order_output_type != 'email') {

				download_url = '<?php echo site_url('orders/generate_'); ?>' + order_output_type + '/order_id/' + order_id + '/order_template/' + order_template;

				window.open(download_url);

			}

			else {

				var email_url = '<?php echo site_url('mailer/order_mailer/form/order_id'); ?>' + '/' + order_id + '/order_template/' + order_template;

				window.location = email_url;

			}

		}

	});

</script>

<div id="order_output_dialog">
	<table style="width: 100%;">
		<tr>
			<td><?php echo $this->lang->line('output_type'); ?>: </td>
			<td>
				<select name="order_output_type" id="order_output_type">
					<option value="pdf"><?php echo $this->lang->line('pdf'); ?></option>
					<option value="html"><?php echo $this->lang->line('html'); ?></option>
					<option value="email"><?php echo $this->lang->line('email'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $this->lang->line('template'); ?>: </td>
			<td>
				<select name="order_template" id="order_template">
					<?php foreach ($templates as $template) { ?>
						<option <?php if ($template == $default_order_template) { ?>selected="selected"<?php } ?>><?php echo $template; ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
	</table>
</div>