<style>
.pagebut {
    padding: 4px;
    border: 1px solid #415680;
    background: #344566;
}
</style>
<br/>
<form action="search.php" method="get" style="margin: 0px;">
    <table width="650">
        <tr>
            <td class="c">{Search_in_all_game}</td>
        </tr>
        <tr>
            <th class="pad5">
                <select name="type" style="padding: 3px;">
                    <option value="playername"{type_playername}>{Player_name}</option>
                    <option value="allytag"{type_allytag}>{Alliance_tag}</option>
                    <option value="allyname"{type_allyname}>{Alliance_name}</option>
                </select>
                &nbsp;&nbsp;
                <input type="text" name="searchtext" style="width: 150px; padding: 3px;" value="{searchtext}"/>
                &nbsp;&nbsp;
                <input type="submit" style="padding: 3px; font-weight: 700; width: 100px;" value="{Search}" />
            </th>
        </tr>
    </table>
</form>
{pagination}
{search_results_count}
{search_results}
{pagination}
