<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo application_title(); ?></title>
        <link href="<?php echo base_url(); ?>assets/style/css/styles.css" rel="stylesheet" type="text/css" media="screen" />
        <!--[if IE 6]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/style/css/ie6.css" /><![endif]-->
        <!--[if IE 7]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/style/css/ie7.css" /><![endif]-->
        <link type="text/css" href="<?php echo base_url(); ?>assets/jquery/ui-themes/myclientbase1/jquery-ui-1.8.4.custom.css" rel="stylesheet" />
        <link type="text/css" href="<?php echo base_url(); ?>assets/slick/slick.grid.css" rel="stylesheet" />
        
        <script type="text/javascript" src="<?= base_url(); ?>assets/jquery/jquery-1.7.min.js"></script>
        <script type="text/javascript" src="<?= base_url(); ?>assets/jquery/jquery-ui-1.8.4.custom.min.js"></script>
        <script src="<?= base_url(); ?>assets/jquery/jquery.maskedinput-1.2.2.min.js" type="text/javascript"></script>
        <script src="<?= base_url(); ?>assets/jquery/js-common.js" type="text/javascript"></script>

        <?php if (isset($header_insert)) {
            $this->load->view($header_insert);
        } ?>

        <script type="text/javascript">
            $(function () {
                if ($('#navigation li.selected ul'))
                {
                    $('#navigation li.selected ul').css('display', 'block');
                }

                $('#navigation li').hover(function () {
                    $('#navigation li ul').css('display', 'none');
                    $(this).find('ul').css('display', 'block');

                }, function () {
                    $(this).find('ul').css('display', 'block');
                });
            });
        </script>

    </head>
    <body>
<?php $clean = isset($_GET['clean']) ? $_GET['clean'] : '';

if ($clean != 1) {
?>
        <div id="header_wrapper">

            <div class="container_12" id="header_content">

                <h1><?php echo application_title(); ?></h1>

            </div>

        </div>

        <div id="navigation_wrapper">

            <ul class="container_12" id="navigation">

<?php echo modules::run('mcb_menu/header_menu/display', array('view' => 'dashboard/header_menu')); ?>

            </ul>

        </div>
<?php } ?>
        <div class="container_12" id="center_wrapper">
