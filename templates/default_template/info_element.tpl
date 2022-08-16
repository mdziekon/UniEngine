<style>
.rf_l {
    display: inline-block;
    width: 80%;
    float: left;
    text-align: right;
}
.rf_r {
    display: inline-block;
    width: 19%;
    float: right;
    text-align: left;
}
.techDet:hover > th {
    background-color: #455B87;
    border-color: #526EA3;
}
.thisLevel > th {
    background-color: #455B87;
    border-color: #526EA3;
}
</style>

<br/>
<table width="600">
    <tr>
        <td class="c center">{element_typ}</td>
    </tr>
    <tr>
        <th class="pad2">{nfo_techinfoabout} - <b class="orange">{name}</b></th>
    </tr>
    <tr>
        <th>
            <table>
                <tr>
                    <th style="border: 0px;" valign="top">
                        <img src="{skinpath}gebaeude/{image}.gif" height="120" width="120" style="border: 2px solid black;" />
                    </th>
                    <th style="border: 0px; text-align: left; width: 100%;">
                        {description}
                        <br/><br/>
                        <span style="width: 100%;">
                            {rf_info_to}{rf_info_fr}
                        </span>
                    </th>
                </tr>
            </table>
        </th>
    </tr>
    {AdditionalInfo}
</table>

{component_ProductionTable}
{component_UnitDetails}
