<script>
var JSLang = {'SelectAllVisible': '{SelectAllVisible}', 'SelectAllFiltered': '{SelectAllFiltered}', 'SearchUserTip': '{SearchUserTip}', 'mEmail': '{mEmail}', 'xEmail': '{xEmail}', 'lastIP': '{lastIP}', 'regIP': '{regIP}', 'LoopupInfo': '{LoopupInfo}', 'ItsAllyOwner': '{ItsAllyOwner}', 'AllyRequested': '{AllyRequested}', 'PlayerIsBanned': '{PlayerIsBanned}', 'PlayerIsOnVacations': '{PlayerIsOnVacations}', 'Userlist_ConfirmDelete': '{Userlist_ConfirmDelete}', 'ToggleFilters': '{ToggleFilters}'};
var DefaultPerPage = {DefaultPerPage};
</script>
<script src="../dist/js/admin/userlist_body.cachebuster-1546739003831.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="../dist/css/admin/userlist_body.cachebuster-1546567692327.min.css" />
<br />
<table width="900" class="negmarg">
    <form id="searchForm" action="userlist.php" method="post" style="margin: 0px;">
        <input type="hidden" name="cmd" value="sort"/>
        <input type="hidden" name="type" value="{FormSortType}"/>
        <input type="hidden" name="mode" value="{FormSortMode}"/>
        <input type="hidden" name="page" value="{FormPage}" class="dontSaveOld"/>
        <input type="hidden" name="preserve" value="{FormPreserve}"/>
        <input type="hidden" name="deleteID" value=""/>
        <input type="hidden" name="massAction" value="" class="dontSaveOld"/>
        <input type="hidden" name="massActionIDs" value="" class="dontSaveOld"/>
        <input type="hidden" name="useAllFiltered" value="" class="dontSaveOld"/>
        <tr>
            <th class="c pad5" colspan="8">
                <span style="margin-top: 5px; margin-left: 20px;" class="fl {DontShowNoSort}">
                    <input type="checkbox" name="nosort" id="c_nosort"/> <label for="c_nosort">{NoSort}</label>
                </span>
                <input type="text" name="search_user" size="45" class="pad5" value="{search_user_val}"/>
                <select id="SBy" name="search_by" class="pad5">
                    <option value="name" {searchBySelect_name}>{SearchByName}</option>
                    <option value="uid" {searchBySelect_uid}>{SearchByUserID}</option>
                    <option value="ally" {searchBySelect_ally}>{SearchByAlly}</option>
                    <option value="aid" {searchBySelect_aid}>{SearchByAllyID}</option>
                    <option value="ip" {searchBySelect_ip}>{SearchByIP}</option>
                </select>
                <input type="submit" value="{Userlist_Submit}" class="pad5 fatBT lime aSearch" /> <input type="button" value="{ResetFilters}" id="reset" class="pad5 fatBT red" />
                <span class="fr">
                    <a id="toggleFilterOpt" class="button"/><img id="toggleFilterOptImg" src="../images/expand.png" style="position: relative; top: 4px;"/></a>
                </span>
            </th>
        </tr>
        <tbody id="FiltersOpt">
            <tr>
                <th class="c pad5" colspan="4" style="width: 50%;">
                    <table width="100%">
                        <tr>
                            <th class="opt">
                                <span title="{StrictSearchInfo}" id="SSInfo">
                                    <input class="checkBox" type="checkbox" id="c_strict" name="strict" {StrictChecked}/> <label for="c_strict">{StrictSearch}</label>
                                </span>
                            </th>
                            <th class="opt">
                                &nbsp;
                            </th>
                        </tr>
                        <tr>
                            <th class="opt">
                                <b class="fl {UsingOverviewShortcut}">{OnlineSearch}</b>
                                <b class="fr">
                                    <label for="cOnlineYes">{_Yes}</label> <input class="checkBox cOnline" type="checkbox" id="cOnlineYes" name="online_yes" {Online_Yes_Checked}/>
                                    <label for="cOnlineNo">{_No}</label> <input class="checkBox cOnline" type="checkbox" id="cOnlineNo" name="online_no" {Online_No_Checked}/>
                                </b>
                            </th>
                            <th class="opt">
                                <b class="fl">{OnVacationSearch}</b>
                                <b class="fr">
                                    <label for="cOnVacationYes">{_Yes}</label> <input class="checkBox cOnVacation" type="checkbox" id="cOnVacationYes" name="onvacation_yes" {OnVacation_Yes_Checked}/>
                                    <label for="cOnVacationNo">{_No}</label> <input class="checkBox cOnVacation" type="checkbox" id="cOnVacationNo" name="onvacation_no" {OnVacation_No_Checked}/>
                                </b>
                            </th>
                        </tr>
                        <tr>
                            <th class="opt">
                                <b class="fl">{IsBannedSearch}</b>
                                <b class="fr">
                                    <label for="cIsBannedYes">{_Yes}</label> <input class="checkBox cIsBanned" type="checkbox" id="cIsBannedYes" name="isbanned_yes" {IsBanned_Yes_Checked}/>
                                    <label for="cIsBannedNo">{_No}</label> <input class="checkBox cIsBanned" type="checkbox" id="cIsBannedNo" name="isbanned_no" {IsBanned_No_Checked}/>
                                </b>
                            </th>
                            <th class="opt">
                                <b class="fl">{IsAISearch}</b>
                                <b class="fr">
                                    <label for="cIsAiYes">{_Yes}</label> <input class="checkBox cIsAi" type="checkbox" id="cIsAiYes" name="isai_yes" {IsAi_Yes_Checked}/>
                                    <label for="cIsAiNo">{_No}</label> <input class="checkBox cIsAi" type="checkbox" id="cIsAiNo" name="isai_no" {IsAi_No_Checked}/>
                                </b>
                            </th>
                        </tr>
                        <tr>
                            <th class="opt">
                                <b class="fl">{IsDeletingSearch}</b>
                                <b class="fr">
                                    <label for="cIsDeletingYes">{_Yes}</label> <input class="checkBox cIsDeleting" type="checkbox" id="cIsDeletingYes" name="isdeleting_yes" {IsDeleting_Yes_Checked}/>
                                    <label for="cIsDeletingNo">{_No}</label> <input class="checkBox cIsDeleting" type="checkbox" id="cIsDeletingNo" name="isdeleting_no" {IsDeleting_No_Checked}/>
                                </b>
                            </th>
                            <th class="opt">
                                <b class="fl">{IsInAllySearch}</b>
                                <b class="fr">
                                    <label for="cIsInAllyYes">{_Yes}</label> <input class="checkBox cIsInAlly" type="checkbox" id="cIsInAllyYes" name="isinally_yes" {IsInAlly_Yes_Checked}/>
                                    <label for="cIsInAllyNo">{_No}</label> <input class="checkBox cIsInAlly" type="checkbox" id="cIsInAllyNo" name="isinally_no" {IsInAlly_No_Checked}/>
                                </b>
                            </th>
                        </tr>
                        <tr>
                            <th class="opt">
                                <b class="fl">{IsActiveSearch}</b>
                                <b class="fr">
                                    <label for="cIsActiveYes">{_Yes}</label> <input class="checkBox cIsActive" type="checkbox" id="cIsActiveYes" name="isactive_yes" {IsActive_Yes_Checked}/>
                                    <label for="cIsActiveNo">{_No}</label> <input class="checkBox cIsActive" type="checkbox" id="cIsActiveNo" name="isactive_no" {IsActive_No_Checked}/>
                                </b>
                            </th>
                            <th class="opt">
                                <b class="inv">&nbsp;</b>
                            </th>
                        </tr>
                    </table>
                </th>
                <th class="c pad5" colspan="4" style="width: 50%;">
                    <table width="100%">
                        <tr>
                            <th class="opt">
                                <b class="inv">&nbsp;</b>
                                <span title="{AnyIPInfo}" id="anyip_span" class="{AnyIPDisplay}">
                                    <input class="checkBox" type="checkbox" id="c_anyip" name="anyip" {AnyIPChecked}/> <label for="c_anyip">{AnyIP}</label>
                                </span>
                                <span class="allysearch {AllySearchTypeDisplay}">
                                    <b class="fl">{AllySearchType}</b>
                                    <b class="fr">
                                        <label for="cAllyTypeName">{_Names}</label> <input class="checkBoxOne cAllyType" type="checkbox" id="cAllyTypeName" name="allysearch_name" {AllySearch_name_Checked}/>
                                        <label for="cAllyTypeTag">{_Tags}</label> <input class="checkBoxOne cAllyType" type="checkbox" id="cAllyTypeTag" name="allysearch_tag" {AllySearch_tag_Checked}/>
                                    </b>
                                </span>
                            </th>
                            <th class="opt">
                                <b class="inv">&nbsp;</b>
                            </th>
                        </tr>
                        <tr>
                            <th class="opt">
                                <b class="inv">&nbsp;</b>
                                <span class="allysearch allyOnRequest {AllyInRequestDisplay}">
                                    <b class="fl">{AllyInRequest}</b>
                                    <b class="fr">
                                        <label for="cAllInRequestYes">{_Yes}</label> <input class="checkBox cAllInRequest" type="checkbox" id="cAllInRequestYes" name="allyinrequest_yes" {AllyInRequest_Yes_Checked}/>
                                        <label for="cAllInRequestNo">{_No}</label> <input class="checkBox cAllInRequest" type="checkbox" id="cAllInRequestNo" name="allyinrequest_no" {AllyInRequest_No_Checked}/>
                                    </b>
                                </span>
                            </th>
                            <th class="opt">
                                <b class="inv">&nbsp;</b>
                            </th>
                        </tr>
                        <tr>
                            <th class="opt">
                                <b class="inv">&nbsp;</b>
                            </th>
                            <th class="opt">
                                <b class="inv">&nbsp;</b>
                            </th>
                        </tr>
                        <tr>
                            <th class="opt">
                                <b class="inv">&nbsp;</b>
                            </th>
                            <th class="opt">
                                <b class="inv">&nbsp;</b>
                            </th>
                        </tr>
                        <tr>
                            <th class="opt">
                                <b class="inv">&nbsp;</b>
                            </th>
                            <th class="opt">
                                <b class="inv">&nbsp;</b>
                            </th>
                        </tr>
                    </table>
                </th>
            </tr>
            <tr>
                <th class="c pad5" colspan="8">
                    <label for="pp">{ShowPerPage}:</label> <input type="text" id="pp" name="pp" size="2"/>
                    <select id="PPList">
                        <option value="-" {perPageSelect_0}>-</option>
                        <option value="5" {perPageSelect_5}>5</option>
                        <option value="10" {perPageSelect_10}>10</option>
                        <option value="15" {perPageSelect_15}>15</option>
                        <option value="20" {perPageSelect_20}>20</option>
                        <option value="25" {perPageSelect_25}>25</option>
                        <option value="50" {perPageSelect_50}>50</option>
                    </select>
                </th>
            </tr>
        </tbody>
    </form>
    {pagination}
</table>
<table width="900" class="negmarg">
    <tr>
        <th class="cBox"><input type="checkbox" class="help" id="usrBoxSelect_AllFiltered"/></th>
        <th class="pad4" colspan="8"><span class="fl marg10">{FoundUsers_Count}</span><span class="fr marg10">{Filtering_Time} | {Loading_Time}</span></th>
    </tr>
    {AdditionalInfoBox}
    <tr class="usr">
        <th class="cBox"><input type="checkbox" class="help" id="usrBoxSelect_AllVisible"/></th>
        <th><a class="sortLink" href="?cmd=sort&type=id&mode={sort_mode}">{Userlist_ID}</a></th>
        <th><a class="sortLink" href="?cmd=sort&type=username&mode={sort_mode}">{Userlist_Username}</a></th>
        <th>{Ally_name}</th>
        <th><a class="sortLink" href="?cmd=sort&type=email&mode={sort_mode}">{Userlist_Email}</a></th>
        <th><a class="sortLink" href="?cmd=sort&type=ip_at_reg&mode={sort_mode}">{Userlist_RegIP}</a></th>
        <th><a class="sortLink" href="?cmd=sort&type=user_lastip&mode={sort_mode}">{Userlist_IP}</a></th>
        <th><a class="sortLink" href="?cmd=sort&type=register_time&mode={sort_mode}">{Register_time}</a></th>
        <th><a class="sortLink" href="?cmd=sort&type=onlinetime&mode={sort_mode}">{Last_Activity}</a></th>
    </tr>
    {adm_ul_table}
</table>
<table width="900" class="negmarg">
    {pagination}
</table>
<br/>
