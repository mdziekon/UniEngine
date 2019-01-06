<style>
.minw {
    min-width: 250px;
    display: inline-block;
}
.help {
    cursor: help;
    border-bottom: 1px dashed white;
}
</style>
<script type="text/javascript" src="{AdminBack}libs/jquery/jquery.min.js"></script>
<script type="text/javascript" src="{AdminBack}libs/jquery-tipTip/jquery.tipTip.min.js"></script>
<link rel="stylesheet" type="text/css" href="{AdminBack}libs/jquery-tipTip/jquery.tipTip.min.css" />
<script>
$(document).ready(function()
{
    $(".help").tipTip({delay: 0, maxWidth: 250, attribute: 'title'});
});
</script>
<br />
<table style="width: 950px;">
    <tr>
        <td class="c"><center>{LoginPage_Header}</center></td>
    </tr>
    <tr>
        <td class="b pad5">
            {LoginPage_Text}
        </td>
    </tr>
    <tr>
        <td class="c pad5">
            <form action="" method="get" style="margin: 0px;">
                <input type="submit" style="padding: 5px; width: 100%; font-weight: 700;" value="{LoginPage_Input}"/>
            </form>
        </td>
    </tr>
</table>
