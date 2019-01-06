<script>$(document).ready(function(){ $('.DoDecline').click(function(){ return confirm('{AcceptBox_Sure}'); }); });</script>
<table style="width: 100%; position: fixed; left: 0px; top: 0px;" cellspacing="0">
    <tr>
        <td class="b Acc_1">{AcceptBox_Info}</td>
    </tr>
    <tr>
        <th class="Acc_2">
            <a href="?cmd=accept" class="AcceptBox DoAccept lime">{AcceptBox_Option_Accept}</a>
            <a href="?cmd=decline" class="AcceptBox DoDecline red">{AcceptBox_Option_Decline}</a>
            {AcceptBox_InsertDeleteTime}
        </th>
    </tr>
    <tr>
        <td class="c Acc_3">&nbsp;</td>
    </tr>
</table>
<table style="width: 100%; visibility: hidden;" cellspacing="0">
    <tr>
        <td class="b Acc_1">{AcceptBox_Info}</td>
    </tr>
    <tr>
        <th class="Acc_2">
            <a class="AcceptBox">a</a>
            <a class="AcceptBox">a</a>
            {AcceptBox_InsertDeleteTime}
        </th>
    </tr>
    <tr>
        <td class="c Acc_3">&nbsp;</td>
    </tr>
</table>
