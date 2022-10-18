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
                <?php //if(isset($quote_inventory_items)){ ?>
                <li><a id="inv_quote" href="#tab_quote">Quote</a></li>
                <?php //} ?>
            </ul>
            <div id="tab_inventory_detail">
                <?php $this->load->view('inventory_detail'); ?>
            </div>

            <?php //if(isset($quote_inventory_items)){ ?>
            <div id="tab_quote"></div>
            <?php //$this->load->view('quote_inventory_item'); ?>
            <?php //} ?>
        </div>

    </div>
    </div>
</div>
<?php $this->load->view('dashboard/footer'); ?>
<script type="text/javascript">
    $(document).ready(function (e) {
        var inventory_id = <?php echo uri_assoc('inventory_id');?>;

        if(inventory_id) {
            if($('#inv_history').length > '0'){

                $.ajax({
                    type: 'POST',
                    dataType: "json",
                    url: "<?php echo site_url('inventory/get_inventory_history'); ?>",
                    data: {
                        id: inventory_id,
                    },
                    beforeSend : function()    {  
                        $(document).find('#inv_history').html("<div class='loader' style='margin:0 0 20px 187px;'><img src='<?php echo base_url();?>assets/style/img/loading.gif' style='vertical-align:sub'> Loading history records..</div>");      
                    },
                    success: function(data) {
                        $(document).find('#inv_history').html('');
                        if(data && data.hasOwnProperty('history')){
                            $(document).find('#inv_history').html(data.history);
                        }
                    },
                    error:function(e){
                      console.log(e.responseText);
                    }
                });
            }

            $('#inv_quote').on('click', function(){
                if($(document).find('#tab_quote').is(':empty')){
                    $.ajax({
                        type: 'POST',
                        dataType: "json",
                        url: "<?php echo site_url('inventory/get_inventory_quotes'); ?>",
                        data: {
                            id: inventory_id,
                        },
                        beforeSend : function()    {  
                            $(document).find('#tab_quote').html("<div class='loader'><img src='<?php echo base_url();?>assets/style/img/loading.gif' style='vertical-align:sub'> Loading data..</div>");      
                        },
                        success: function(data) {
                            $(document).find('#tab_quote').html('');
                            if(data && data.hasOwnProperty('html')){
                                $(document).find('#tab_quote').html(data.html);
                            }
                        },
                        error:function(e){
                          console.log(e.responseText);
                        }
                    });
                }
            });
        }
        // $("#btn_submit").click(function () {
        //     $('#qtyErrorMsg').html('');
        //     var history_qty = document.getElementById("history_qty").value;
        //     if ((history_qty == '0') || (history_qty == '0.0') || (history_qty == '00.0') || (history_qty == '00')) {
        //         $('#qtyErrorMsg').append('This field can not be zero.');
        //         return false;
        //     }
        // });

        $("#inventory_form").on('submit', function () {
            $('#qtyErrorMsg').html('');
            let supplier_id = $('#supplier_id option:selected').text().trim();
            if(supplier_id == ''){
                $('#supplier_id option:selected').val('');
            }
            var history_qty = document.getElementById("history_qty");
            if(history_qty) {
                history_qty = history_qty.value;
                if ((history_qty == '0') || (history_qty == '0.0') || (history_qty == '00.0') || (history_qty == '00')) {
                    $('#qtyErrorMsg').append('This field can not be zero.');
                    return false;
                }
            }
            
        });
        
        $('#btn_save_continue').on('click',function(e){
            e.preventDefault();
            $('#action_type').val('continue');
            $('form').submit();
        });
    });


    $(document).ready(function () {
        $('#inventory_list_options').select2({
            ajax: {
                url: "<?php echo site_url('inventory/get_part_type_items'); ?>",
                type: "POST",
                dataType: 'json',
                delay: 250,
                cache: true,
                processResults: function (data) {
                    return {
                        results: $.map(data, function(item){
                            return {
                                id: item.inventory_id,
                                text: item.name,
                                use_length: item.use_length
                            }
                        })
                    };
                }
            },
            width: '270',
            minimumInputLength: 2,
            placeholder: 'Search inventory items',
            language: {
                inputTooShort: function() {
                    return 'Please enter 2 or more characters to get list of items to select.';
                }
            },
            templateSelection: function (data, container) {
                $(data.element).attr('data-custom-attribute', data.use_length);
                return data.text;
            }
        })

        $('#inventory_list_options').on('select2:select', function (event) {
            let data = event.params.data;
            if (data.use_length == '1') {
                $(document).find('.qty_inventory_item').attr('disabled', 'disabled');
                $(document).find('.qty_inventory_item').attr('value', '1');
            } else {
                $(document).find('.qty_inventory_item').removeAttr('disabled');
            }
        });

        $('#btn_cancel').on('click', function(){
            $('#inventory_list_options').val(null).trigger('change');
            $('#select2-inventory_list_options-container').html('<span class="select2-selection__placeholder">Search inventory items</span>');
            $('#inventory_qty').val('1');
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