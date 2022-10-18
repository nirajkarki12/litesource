<?php

# Include the Autoloader (see "Libraries" for install instructions)
require '/var/www/html/application/third_party/vendor/autoload.php';
use Mailgun\Mailgun;
# Instantiate the client.
$mgClient = new Mailgun('95f9d5f8b915bb9f9522a0521b946399-9ce9335e-307af33d');
$domain = "litesource.com.au";
# Make the call to the client.
$result = $mgClient->sendMessage($domain, array(
	'from'	=> 'Litesource and Control <info@litesource.com.au>',
	'to'	=> 'Manoj Roka <dinesubedi@gmail.com>',
	'subject' => 'Hello',
	'text'	=> 'Testing some Mailgun awesomness!'
));

echo '<pre>'; print_r( $result ); echo '<pre>'; die;
