<tr>
    <th class="pad2">
        <a href="infos.php?gid={fleet_id}">{fleet_name}</a>
    </th>
    <th class="pad2">
        {fleet_max}
    </th>
    <th class="pad2">
        <input type="text" class="countInput" data-maxVal="{fleet_countmax}" name="ship_{fleet_id}" value="0"/>
    </th>
    <th class="pad2">
        <a class="setMax" data-ID="{fleet_id}">{fleet_setmax}</a> / <a class="setMin" data-ID="{fleet_id}">{fleet_setmin}</a>
    </th>
</tr>
