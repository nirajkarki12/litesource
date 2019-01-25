<?php foreach ($result_clients as $client) { ?>

<h2><?php echo $client->client_name; ?></h2>

<table style="width: 100%;">
	<tr>
		<td width="50%">
			<table style="width: 100%;">
				<tr>
					<td width="25%" nowrap><?php echo $this->lang->line('street_address'); ?></td>
					<td width="75%"><?php echo $client->client_address; ?>&nbsp;</td>
				</tr>
				<tr>
					<td width="25%" nowrap><?php echo $this->lang->line('city'); ?></td>
					<td width="75%"><?php echo $client->client_city; ?>&nbsp;</td>
				</tr>
				<tr>
					<td width="25%" nowrap><?php echo $this->lang->line('state'); ?></td>
					<td width="75%"><?php echo $client->client_state; ?>&nbsp;</td>
				</tr>
				<tr>
					<td width="25%" nowrap><?php echo $this->lang->line('zip'); ?></td>
					<td width="75%"><?php echo $client->client_zip; ?>&nbsp;</td>
				</tr>
				<tr>
					<td width="25%" nowrap><?php echo $this->lang->line('phone_number'); ?></td>
					<td width="75%"><?php echo $client->client_phone_number; ?>&nbsp;</td>
				</tr>
			</table>
		</td>
		<td width="50%">
			<table style="width: 100%;">
				<tr>
					<td width="25%" nowrap><?php echo $this->lang->line('fax_number'); ?></td>
					<td width="75%"><?php echo $client->client_fax_number; ?>&nbsp;</td>
				</tr>
				<tr>
					<td width="25%" nowrap><?php echo $this->lang->line('mobile_number'); ?></td>
					<td width="75%"><?php echo $client->client_mobile_number; ?>&nbsp;</td>
				</tr>
				<tr>
					<td width="25%" nowrap><?php echo $this->lang->line('web_address'); ?></td>
					<td width="75%"><?php echo auto_link($client->client_web_address, 'both', TRUE); ?>&nbsp;</td>
				</tr>
				<tr>
					<td width="25%" nowrap><?php echo $this->lang->line('email_address'); ?></td>
					<td width="75%"><?php echo auto_link($client->client_email_address, 'both', TRUE); ?>&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<hr style="clear: both;"/>

<?php } ?>