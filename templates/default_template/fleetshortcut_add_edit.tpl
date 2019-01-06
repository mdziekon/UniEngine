<br/>

<table width="519" border="0" cellpadding="0" cellspacing="1">
    <tr>
        <td class="c" height="20" colspan="2">{Action_shortcut}</td>
    </tr>
    <form action="?mode={post_action}&amp;id={edit_id}" method="post">
    <input type="hidden" name="action" value="{post_action}"/>
    <tr>
        <th>{Name}</th>
        <th><input type="text" name="name" value="{set_name}"/></th>
    </tr>
    <tr>
        <th>{Coordinates}</th>
        <th>
            <input type="text" name="galaxy" value="{set_galaxy}" size="3"/>:<input type="text" name="system" value="{set_system}" size="3"/>:<input type="text" name="planet" value="{set_planet}" size="3"/>
            <select name="type">
                <option value="1" {planet_selected}>{list_planet}</option>
                <option value="2" {debris_selected}>{list_debris}</option>
                <option value="3" {moon_selected}>{list_moon}</option>
            </select>
        </th>
    </tr>
    <tr>
        <th colspan="2"><input type="submit" value="{Action}"/></th>
    </tr>
    </form>
    <tr>
        <td class="c" height="20" colspan="2"><a href="fleetshortcut.php">{goback}</a></td>
    </tr>
</table>
