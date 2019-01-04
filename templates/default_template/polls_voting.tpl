<script src="dist/js/polls_voting.cachebuster-1545956361123.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/polls_voting.cachebuster-1546564327123.min.css" />
<br/>
<form action="?pid={PollID}" method="post">
    <input type="hidden" name="send" value="1"/>
    <table width="650">
        <tr>
            <td class="c" colspan="3" style="padding: 1px 5px;">
                <span class="fl">{Title}</span>
                <span class="fr">(<a href="polls.php" class="orange">&laquo; {GoBack}</a>)</span>
            </td>
        </tr>
        <tr><th class="pad5" colspan="3">{PollName}</th></tr>
        <tr{PollDescHide}><th class="pad5" colspan="3">{PollDesc}</th></tr>
        {PollAlert}
        <tr>
            <th style="width: 20px;">&nbsp;</th>
            <th class="pad5">{PollOption}</th>
            <th class="pad5" style="width: 150px;">{PollVotes}{ShowUsersLink}</th>
        </tr>
        {PollResultsInfo}
        {Insert_PollAnswers}
        <tr>
            <th class="pad5" colspan="3">
                <input type="submit" style="width: 80%; padding: 3px; font-weight: 700; color: {Insert_SubmitColor};" value="{Vote_or_change}" {Insert_SubmitDisable} />
            </th>
        </tr>
    </table>
</form>
