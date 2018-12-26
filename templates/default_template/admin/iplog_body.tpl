<style>
.tbl {
    width: 900px;
}
.pad3 {
    padding: 3px;
}
.pad2 {
    padding: 2px;
}
.w100 {
    width: 100%;
}
.inv {
    visibility: hidden;
}
.hide {
    display: none;
}
.look {
    background: url('../images/search.png') no-repeat 0pt 0pt;
}
.left {
    text-align: left;
}
.right {
    text-align: right;
}
.noProxy {
    background: url('../images/ip.png') no-repeat 0pt 0pt;
}
.isProxy {
    background: url('../images/servers.png') no-repeat 0pt 0pt;
}
.proxySet, .sortimg {
    cursor: pointer;
}
.sortimgasc {
    background: url('../images/collapse.png') no-repeat 0pt 0pt;
}
.sortimgdesc {
    background: url('../images/expand.png') no-repeat 0pt 0pt;
}
.sortimg, .sortimgasc, .sortimgdesc, .look, .proxySet {
    padding-left: 16px;
}
th.even {
    background-color: #40547C;
    border-color: #4C6798;
}
th.break {
    background-color: #27344D;
    border-color: #344769;
}
</style>
<script>
var JSLang =
{
    'info_SetAsProxy': '{info_SetAsProxy}',
    'info_SetAsNonProxy': '{info_SetAsNonProxy}'
};
</script>
<script>
$(document).ready(function()
{
    var ThisForm = $('#thisForm');
    var DoSearch = false;

    var qtipStyles =
    {
        style:
        {
            classes: 'tiptip_content'
        },
        show:
        {
            delay: 150,
            effect: function()
            {
                $(this).fadeIn(150);
            }
        },
        hide:
        {
            effect: function()
            {
                $(this).fadeOut(150);
            }
        },
        position:
        {
            my: 'top center',
            at: 'bottom center'
        }
    };

    $('.isProxy').qtip($.extend(qtipStyles, {content: JSLang['info_SetAsNonProxy']}));
    $('.noProxy').qtip($.extend(qtipStyles, {content: JSLang['info_SetAsProxy']}));

    $('.even').find('th').addClass('even');
    $('[id^="doSort_"]').click(function()
    {
        $('input[name="sort"]').val($(this).attr('id').replace('doSort_', ''));
        if($(this).hasClass('sortimgasc'))
        {
            $('input[name="mode"]').val('desc');
        }
        else
        {
            $('input[name="mode"]').val('asc');
        }
        ThisForm.submit();
    });
    $('.proxySet').click(function()
    {
        $('[name="proxyEdit"]').val($(this).attr('data-ipid'));
        ThisForm.submit();
    });

    $('#FormSubmit').click(function()
    {
        DoSearch = true;
    });

    ThisForm.submit(function()
    {
        if(DoSearch === true)
        {
            $('[name="uid"],[name="ipid"]').val('');
        }
    });
});
</script>
<br />
<table class="tbl">
    <form action="iplog.php" method="post" id="thisForm">
        <input type="hidden" name="uid" value="{Insert_Found_UID}"/>
        <input type="hidden" name="ipid" value="{Insert_Found_IPID}"/>
        <input type="hidden" name="sort" value="{Insert_SortType}"/>
        <input type="hidden" name="mode" value="{Insert_SortMode}"/>
        <input type="hidden" name="proxyEdit" value=""/>
        <tr>
            <td class="c" colspan="3">{Search_Title}</td>
        </tr>
        <tr>
            <th style="width: 60%;" class="pad5">
                <input type="text" name="search" value="{Insert_Search_Value}" class="pad3 w100"/>
            </th>
            <th style="width: 20%;" class="pad5">
                <select name="type" class="pad3 w100">
                    <option {Insert_Search_OptSel_uname}    value="uname">{Search_Opt_uname}</option>
                    <option {Insert_Search_OptSel_uid}      value="uid">{Search_Opt_uid}</option>
                    <option {Insert_Search_OptSel_ipstr}    value="ipstr">{Search_Opt_ipstr}</option>
                    <option {Insert_Search_OptSel_ipid}     value="ipid">{Search_Opt_ipid}</option>
                </select>
            </th>
            <th style="width: 20%;" class="pad5">
                <input type="submit" id="FormSubmit" value="{Search_Submit}" style="font-weight: 700;" class="pad3 w100"/>
            </th>
        </tr>
    </form>
    <tbody class="{Insert_InfoBox_Hide}">
        <tr class="inv"><td></td></tr>
        <tr>
            <th colspan="3" class="pad3">{InfoBox_Msg}</th>
        </tr>
    </tbody>
</table>
<table class="tbl">
    {Headers}
    {Results}
</table>
