<?php if ($address) { ?>
<dl>
	<dt><?php echo $this->lang->line('contact_name'); ?>: </dt>
	<dd><?php echo $address->address_contact_name; ?></dd>
</dl>

<dl>
	<dt><?php echo $this->lang->line('street_address'); ?>: </dt>
	<dd><?php echo $address->address_street_address; ?><?php if ($address->address_street_address_2) { ?><br /><?php echo $address->address_street_address_2;} ?></dd>
</dl>

<dl>
	<dt><?php echo $this->lang->line('city'); ?>: </dt>
	<dd><?php echo $address->address_city; ?></dd>
</dl>

<dl>
	<dt><?php echo $this->lang->line('state'); ?>: </dt>
	<dd><?php echo $address->address_state; ?></dd>
</dl>

<dl>
	<dt><?php echo $this->lang->line('postcode'); ?>: </dt>
	<dd><?php echo $address->address_postcode; ?></dd>
</dl>

<dl>
	<dt><?php echo $this->lang->line('country'); ?>: </dt>
	<dd><?php echo $address->address_country; ?></dd>
</dl>
<?php } ?>
