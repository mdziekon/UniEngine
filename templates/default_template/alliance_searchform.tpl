<style>
.button {
    font-weight: 700;
}
.pagebut {
    padding: 4px;
    border: 1px solid #415680;
    background: #344566;
}
#results > tr:hover > th {
    border-color: #526EA3;
    background-color: #455B87;
}
</style>
<br/>
<table width="600">
    <tr>
        <td class="c" colspan="2">{AFind_Title}</td>
    </tr>
    <tr>
        <th class="pad2">{AFind_Search}</th>
        <th>
            <form action="?mode=search" method="post" class="pad2" style="margin: 0px;">
                <input class="pad5" type="text" name="searchtext" value="{searchtext}"/>
                <input class="pad5 button" type="submit" value="{AFind_Search}"/>
            </form>
        </th>
    </tr>
</table>
