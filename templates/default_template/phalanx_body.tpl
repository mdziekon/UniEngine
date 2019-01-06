<style>
.deut {
    background-image: url('{skinpath}images/deuterium.gif');
    background-repeat: no-repeat;
    background-position: 10px center;
    text-align: left;
    padding: 6px;
    padding-left: 60px !important;
}
.link {
    cursor: pointer;
    color: #CDD7F8;
}
.fl {
    float: left;
}
.fr {
    float: right;
}
.pad8 {
    padding: 8px;
}
</style>
<script type="text/javascript" src="libs/jquery-qtip/jquery.qtip.pack.js"></script>
<link rel="stylesheet" type="text/css" href="libs/jquery-tipTip/jquery.tipTip.min.css" />
<link rel="stylesheet" type="text/css" href="libs/jquery-qtip/jquery.qtip.min.css" />
<br/>
<table style="width: 600px;">
    <tr>
        <td class="c" colspan="4">
            <span class="fl">
                {Table_Title1} {Table_Title2} {Insert_TargetName}
                <a class="link" onclick="opener.location = 'galaxy.php?mode=3&galaxy={Insert_Coord_Galaxy}&system={Insert_Coord_System}&planet={Insert_Coord_Planet}'; opener.focus();">[{Insert_Coord_Galaxy}:{Insert_Coord_System}:{Insert_Coord_Planet}]</a>
                {Insert_OwnerName} {Table_Title3} {Insert_MyMoonName}
                <a class="link" onclick="opener.location = 'galaxy.php?mode=3&galaxy={Insert_My_Galaxy}&system={Insert_My_System}'; opener.focus();">[{Insert_My_Galaxy}:{Insert_My_System}:{Insert_My_Planet}]</a>
            </span>
            <span class="fr">
                (<a href="">{Table_Refresh}</a> | <a href="#" onclick="self.close();">{Table_Close}</a>)
            </span>
        </td>
    </tr>
    {phl_fleets_table}
    <tr>
        <th colspan="4" class="deut">{Table_DeuteriumState}: <b class="{Insert_DeuteriumColor}">{Insert_DeuteriumAmount}</a></th>
    </tr>
</table>
