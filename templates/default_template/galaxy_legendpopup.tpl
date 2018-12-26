<script>
var Popup    = '<table width="240">';
Popup       += '<tr><td class="c" colspan="2"><center>{Legend}</center></td></tr>';
Popup       += '<tr class="nopad"><td class="b" width="215">{User_NonActivatedText}</td><td class="b"><center style="color:yellow">{User_NonActivated}</center></td></tr>';
Popup       += '<tr class="nopad"><td class="b">{FleetBlockadeUser}</td><td class="b"><center class="orange">{Fleet_Blockade_Protected}</center></td></tr>';
Popup       += '<tr class="nopad"><td class="b">{Strong_player}</td><td class="b"><center class="strong">{strong_player_shortcut}</center></td></tr>';
Popup       += '<tr class="nopad"><td class="b">{Weak_player}</td><td class="b"><center class="noob">{weak_player_shortcut}</center></td></tr>';
Popup       += '<tr class="nopad"><td class="b">{New_player}</td><td class="b"><center style="color:#FFCC33"><b>{new_player_shortcut}</b></center></td></tr>';
Popup       += '<tr class="nopad"><td class="b">{Way_vacation}</td><td class="b"><center class="vacation">{vacation_shortcut}</center></td></tr>';
Popup       += '<tr class="nopad"><td class="b">{Pendent_user}</td><td class="b"><center class="banned">{banned_shortcut}</center></td></tr>';
Popup       += '<tr class="nopad"><td class="b">{Inactive_7_days}</td><td class="b"><center class="inactive">{inactif_7_shortcut}</center></td></tr>';
Popup       += '<tr class="nopad"><td class="b">{Inactive_28_days}</td><td class="b"><center class="longinactive">{inactif_28_shortcut}</center></td></tr>';
Popup       += '<tr class="nopad"><td class="b">Admin</td><td class="b"><center class="lime"><blink>A</blink></center></td></tr>';
Popup       += '</table>';

$(document).ready(function()
{
    $('#LegendPopup')
    .click(function(){return false;})
    .tipTip({content: Popup, delay: 0, maxWidth: '300px', defaultPosition: 'top'});
});
</script>
<a href="#" id="LegendPopup" style="cursor: pointer;">{Legend}</a>
