<style>
    .pad3 {
        padding: 3px;
    }

    {Insert_Styles}
</style>
<table style="width: 750px; margin-top: 5px;">
    <tr>
        <td
            class="c"
            colspan="3"
        >
            {CatList_Title}
        </td>
    </tr>
    <tr>
        <th style="width: 480px;">
            {CatList_Head_CatName}
        </th>
        <th style="width: 125px;">
            {CatList_Head_Unread}
        </th>
        <th
            style="width: 125px; cursor: help; {Insert_Hide_ThreadDisabled}"
            title="{CatList_Head_Total_Tip}"
        >
            <span style="border-bottom: 1px dashed white;">
                {CatList_Head_Total}
            </span>
        </th>
        <th style="width: 125px; {Insert_Hide_ThreadEnabled}">
            {CatList_Head_Total}
        </th>
    </tr>

    {Insert_CategoryList}
</table>
