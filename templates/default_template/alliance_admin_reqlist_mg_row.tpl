<tr id="mark_{id}" class="mark">
    <th><b class="expand"></b></th>
    <th><a href="profile.php?uid={id}">{username}</a></th>
    <th>{StatPoints}</th>
    <th>{SendDate}</th>
</tr>
<tbody id="form_{id}" class="collapsed">
    <tr>
        <th colspan="4" class="pad5">{ally_request_text}</th>
    </tr>
    <tr>
        <th colspan="4">
            <form action="" method="post" class="noMrg">
                <input type="hidden" name="rq" value="{id}"/>
                <input type="radio" name="opt" value="1" id="opt1_{id}"/> <label for="opt1_{id}">{ADM_RL_Accept}</label> /
                <input type="radio" name="opt" value="2" id="opt2_{id}"/> <label for="opt2_{id}">{ADM_RL_Refuse}</label>
                <b class="rjr_{id}"><br /><br /><label for="rjr_{id}">{ADM_RL_RejectReason}</label><br /><textarea name="rjr" id="rjr_{id}"></textarea><br />(<span class="cntChars">0</span> / 1000 znaków)<br /></b>
                <b id="sub_{id}"><b id="subbr_{id}"><br /><br /></b><input type="submit" value="{ADM_RL_Save}" class="bold"/></b>
            </form>
        </th>
    </tr>
</tbody>
