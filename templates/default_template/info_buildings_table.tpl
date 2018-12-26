<style>
.thisLevel > th {
    background-color: #455B87;
    border-color: #526EA3;
}
</style>
<br />
<table width="600">
    <tr>
        <td class="c center" colspan="4">{element_typ}</td>
    </tr>
    <tr>
        <th class="pad2" colspan="4">{nfo_techinfoabout} - <b class="orange">{name}</b></th>
    </tr>
    <tr>
        <th>
            <table>
                <tr>
                    <th style="border: 0px;" valign="top">
                        <img src="{skinpath}gebaeude/{image}.gif" align="top" border="1" height="120" width="120" style="border: 2px solid black;" />
                    </th>
                    <th style="border: 0px; text-align: left;">{description}</th>
                </tr>
            </table>
        </th>
    </tr>
    <tr>
        <th>
            <table align="center">
                {table_head}
                {table_data}
            </table>
        </th>
    </tr>
</table>
