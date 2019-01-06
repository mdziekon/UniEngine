<br/>

<table width="519" border="0" cellpadding="0" cellspacing="1">
    <tr>
        <td class="c" height="20">{Your_shortcuts}</td>
    </tr>
    <tr>
        <th height="20">
            <br/>
            <input type="button" onclick="document.location = 'fleetshortcut.php?mode=add'" value="{add_new_shortcut}" /><br/><br/>
            {select_shortcut}<br/>
            <form action="" method="get">
                <input type="hidden" name="mode" id="mode"/>
                <select name="id">
                    {shortcuts_list}
                </select><br/><br/>
                <input type="button" value="{edit_shortcut}" onclick="document.getElementById('mode').value = 'edit'; submit();"/>
                <input type="button" value="{delete_shortcut}" onclick="document.getElementById('mode').value = 'delete'; if(confirm('{are_you_sure}')){ submit(); }"/>
            </form>
        </th>
    </tr>
    <tr>
        <td class="c" height="20"><a href="fleet.php">{goback}</a></td>
    </tr>
</table>
