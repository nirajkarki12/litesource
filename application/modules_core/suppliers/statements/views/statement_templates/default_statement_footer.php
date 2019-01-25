<table class="footer">
    <tr>
        <td rowspan="3"><span class="bold"><?php echo $this->lang->line('contact').': '.$user->first_name.' '.$user->last_name; ?></span>
            <a href="mailto:<?php echo $user->email_address; ?>"><?php echo $user->email_address; ?></a>
            &nbsp;|&nbsp;<?php echo $user->mobile_number; ?><br /><br /><br />
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td valign="bottom" style="text-align: right;"><span class="bold">Page {PAGENO}</span></td>
    </tr>
</table>