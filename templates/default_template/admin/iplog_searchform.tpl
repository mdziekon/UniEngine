<table class="tpl">
    <form action="" method="post">
    <tr>
        <td class="c" colspan="3">{Search_Title}</td>
    </tr>
    <tr>
        <th style="width: 60%;" class="pad5">
            <input type="text" name="search" value="{Insert_Search_Value}" class="pad3"/>
        </th>
        <th style="width: 20%;" class="pad5">
            <select name="type" class="pad3">
                <option {Insert_Search_OptSel_uname}    value="uname">{Search_Opt_uname}</option>
                <option {Insert_Search_OptSel_uid}      value="uid">{Search_Opt_uid}</option>
                <option {Insert_Search_OptSel_ipstr}    value="ipstr">{Search_Opt_ipstr}</option>
                <option {Insert_Search_OptSel_ipid}     value="ipid">{Search_Opt_ipid}</option>
            </select>
        </th>
        <th style="width: 20%;" class="pad5">
            <input type="submit" value="{Search_Submit}" style="width: 100%; font-weight: 700;" class="pad3"/>
        </th>
    </tr>
    </form>
</table>
