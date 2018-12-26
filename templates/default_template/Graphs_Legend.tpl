<ul id="Graph_legend">
    {foreach from=$cx->modes item=v}
    {if $v.used == true}
    <li style="border-color: {$v.color}"><span class="Graph_legend_label">{$v.name}</span><br/><span class="Graph_legend_label">({$v.avg})</span></li>
    {/if}
    {/foreach}
</ul>
