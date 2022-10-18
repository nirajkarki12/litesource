function resizing_header_js() {
    $('.slick-header-column').on('mouseover mousemove click mouseout', function () {
        var this_div = $(this);
        var divs = $(".slick-header-column");
        var col_id = divs.index(this_div);
        var col_width = $(this).context.style.width;
        col_width = col_width.replace('px', '');
        col_width = parseInt(parseInt(col_width) - 7);
        $('.c' + col_id).find('input').css('width', col_width);
        var next_div = document.getElementsByClassName('slick-header-column')[col_id + 1];
        
        
        if (  typeof ( next_div ) !== 'undefined') {
            var col_width2 = next_div.style.width;
            col_width2 = col_width2.replace('px', '');
            col_width2 = parseInt(parseInt(col_width2) - 7);
            $('.c' + (col_id + 1)).find('input').css('width', col_width2);
        }

        var prev_div = document.getElementsByClassName('slick-header-column')[col_id - 1];
        if ( typeof ( prev_div ) !== 'undefined') {
            var col_width1 = prev_div.style.width;
            col_width1 = col_width1.replace('px', '');
            col_width1 = parseInt(parseInt(col_width1) - 7);
            $('.c' + (col_id - 1)).find('input').css('width', col_width1);
        }
    });
}

function resizing_header_linktoproduct1_js() {
    $('#inventoryGrid .slick-header-column').on('mouseover mousemove click mouseout', function () {
        var this_div = $(this);
        var divs = $(".slick-header-column");
        var col_id = divs.index(this_div);
        var col_width = $(this).context.style.width;
        col_width = col_width.replace('px', '');
        col_width = parseInt(parseInt(col_width) - 7);
        $('#inventoryGrid .c' + col_id).find('input').css('width', col_width);
        var next_div = document.getElementsByClassName('slick-header-column')[col_id + 1];
        if ( typeof ( next_div ) !== 'undefined') {
            var col_width2 = next_div.style.width;
            col_width2 = col_width2.replace('px', '');
            col_width2 = parseInt(parseInt(col_width2) - 7);
            $('#inventoryGrid .c' + (col_id + 1)).find('input').css('width', col_width2);
        }

        var prev_div = document.getElementsByClassName('slick-header-column')[col_id - 1];
        if ( typeof ( prev_div ) != 'undefined') {
            var col_width1 = prev_div.style.width;
            col_width1 = col_width1.replace('px', '');
            col_width1 = parseInt(parseInt(col_width1) - 7);
            $('#inventoryGrid .c' + (col_id - 1)).find('input').css('width', col_width1);
        }
    });
}
        