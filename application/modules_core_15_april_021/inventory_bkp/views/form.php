<?php $this->load->view('dashboard/header'); ?>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.autogrow-textarea.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>


<!--<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.event.drag-2.0.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jquery/jquery.autogrow-textarea.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.core.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.autotooltips.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/plugins/slick.rowselectionmodel.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.editors.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.grid.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/slick.dataview.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/slick/json2.js"></script>-->



<script type="text/javascript">

    var suppliers = <?php echo $suppliers_json; ?>;
    var price_label_prefix = '<?php echo $this->lang->line('supplier_price'); ?>';
    function supplier_change() {

        var i = $("#supplier_id").attr("selectedIndex");
        //
        var supplier = suppliers[i]

        $('#price_label').html('<label>' + price_label_prefix + ' (' + supplier.currency_code + ')</label>');
    }


    $(document).ready(function () {

        $('#supplier_description').autogrow();
        $('#description').autogrow();
        $("#inventory_type").trigger("change", ['onload']);

    });

    function show(obj) {

        if (obj == 1) {
            $("#part").hide();
            $("#supplier_description_row").hide();
            $("#supplier_price_row").hide();
            $("#supplier_cat_row").hide();
        } else {
            $("#part").show();
            $("#supplier_description_row").show();
            $("#supplier_price_row").show();
            $("#supplier_cat_row").show();

        }

    }
</script>
<script>
    $( function() {
        $( "#tabs" ).tabs();
    } );
</script>

<div class="grid_12" id="content_wrapper">
    <div class="section_wrapper">
        <div class="content toggle">
        <div id="tabs">
            <ul>
                <li><a href="#tab_inventory_detail">Inventory Details</a></li>
                <?php if(isset($quote_inventory_items)){ ?>
                <li><a href="#tab_quote">Quote</a></li>
                <?php } ?>
            </ul>
            <div id="tab_inventory_detail">
                <?php $this->load->view('inventory_detail'); ?>
            </div>

            <?php if(isset($quote_inventory_items)){ ?>
            <div id="tab_quote">
                <?php $this->load->view('quote_inventory_item'); ?>
            </div>
            <?php } ?>
        </div>

    </div>
    </div>
</div>
<?php $this->load->view('dashboard/footer'); ?>
<script type="text/javascript">

    $(document).ready(function (e) {
        $("#btn_submit").click(function () {
            $('#qtyErrorMsg').html('');
            var history_qty = document.getElementById("history_qty").value;
            if ((history_qty == '0') || (history_qty == '0.0') || (history_qty == '00.0') || (history_qty == '00')) {
                $('#qtyErrorMsg').append('This field can not be zero.');
                return false;
            }
        });
        
        $('#btn_save_continue').on('click',function(e){
            e.preventDefault();
            $('#action_type').val('continue');
            $('form').submit();
        });
    });


    $(document).ready(function () {
//        $('#inventory_list_options').select2();

        $('#inventory_list_options').on('change keypress keyup blur input', function (event) {
            var inventoryId = $('#inventory_list_options').val();
            var lnChk = $('#inLn_' + inventoryId).attr('data-len');
            if ((lnChk == '1')) {
                $('.qty_inventory_item').attr('disabled', 'disabled');
                $('.qty_inventory_item').attr('value', '1');
            } else {
                $('.qty_inventory_item').removeAttr('disabled');
            }
        });

    });

    document.querySelector('input[list]').addEventListener('input', function(e) {
        var input = e.target,
            list = input.getAttribute('list'),
            options = document.querySelectorAll('#' + list + ' option'),
            hiddenInput = document.getElementById(input.getAttribute('id') + '-hidden'),
            inputValue = input.value;

        hiddenInput.value = inputValue;
        for(var i = 0; i < options.length; i++) {
            var option = options[i];
            var text = option.innerText;
           if(text.trim() === inputValue) {

                hiddenInput.value = option.getAttribute('data-value');
                break;
            }
        }
    });

</script>