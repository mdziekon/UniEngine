<tr>
    <th class="pad2" style="font-size: 8px;">{Filters_Activate}</th>
    <th colspan="2">&nbsp;</th>
    <th class="pad2" rowspan="4">
        <input style="width: 65px; margin-bottom: 2px;" class="spad doFilter" type="submit" value="{Submit}"/>
        <br />
        <input style="width: 65px;" class="spad doCleanFilter" type="reset" value="{Reset}"/>
    </th>
</tr>
<tr>
    <th class="pad2">
        <input type="checkbox" name="filter_time" {Set_filter_time_checked}/>
    </th>
    <th class="pad2" colspan="2">
        {From} <input class="spad activ_filter_time" type="text" size="2" name="from_hour" value="{FromHour}" maxlength="2"/>:<input class="spad activ_filter_time" type="text" size="2" name="from_min" value="{FromMin}" maxlength="2"/>:
        <input class="spad activ_filter_time" type="text" size="2" name="from_sec" value="{FromSec}" maxlength="2" style="margin-right: 30px;"/>
        {To} <input class="spad activ_filter_time" type="text" size="2" name="to_hour" value="{ToHour}" maxlength="2"/>:<input class="spad activ_filter_time" type="text" size="2" name="to_min" value="{ToMin}" maxlength="2"/>:
        <input class="spad activ_filter_time" type="text" size="2" name="to_sec" value="{ToSec}" maxlength="2"/>
    </th>
</tr>
<tr>
    <th class="pad2">
        <input type="checkbox" name="filter_place" {Set_filter_place_checked}/>
    </th>
    <th class="pad2" colspan="2">
        <div style="float: left; margin-left: 12px; width: 22%; text-align: right;">
            <label for="fpt_1">{Filters_Comparision}</label> <input id="fpt_1" class="activ_filter_place" type="radio" name="filter_place_type" value="1" style="vertical-align: middle; margin: 0;" {Set_filter_place_type_1_checked}/><br />
            <label for="fpt_2">{Filters_Exclusion}</label> <input id="fpt_2" class="activ_filter_place" type="radio" name="filter_place_type" value="2" style="vertical-align: middle; margin: 0;" {Set_filter_place_type_2_checked}/><br />
            <label for="fpt_3">{Filters_RegExp}</label> <input id="fpt_3" class="activ_filter_place" type="radio" name="filter_place_type" value="3" style="vertical-align: middle; margin: 0;" {Set_filter_place_type_3_checked}/><br />
        </div>
        <div style="float: left; width: 23%; text-align: right; visibility: hidden;">
            <label for="fpsg">{Filters_CompareArgs}</label> <input id="fpsg" type="checkbox" name="filter_place_searchget" style="vertical-align: middle; margin: 0;"/>
        </div>
        <div style="float: right; margin-right: 12px; width: 45%; text-align: left; padding: 4px 0px;">
            <label for="fpq">{Filters_Query}:</label><br /><input id="fpq" type="text" class="pad2 activ_filter_place" name="filter_place_query" style="width: 200px;" value="{Set_filter_place_query}"/>
        </div>
    </th>
</tr>
<tr>
    <th class="pad2">
        {Filters_Additional}
    </th>
    <th class="pad2" colspan="2">
        <input type="checkbox" name="autoExpandArr" id="autoExpandArr" style="vertical-align: middle; margin: 0;" {aEArrCheck}/>
        <label for="autoExpandArr">{AutoExpandArr}</label>
        <input type="checkbox" name="autoExpandAmp" id="autoExpandAmp" style="vertical-align: middle; margin: 0;" {aEAmpCheck}/>
        <label for="autoExpandAmp">{AutoExpandAmp}</label>
    </th>
</tr>
<tr>
    <th class="pad2" colspan="4">
        <input type="button" id="expAllArr" value="{ExpandAllArr}"/> <input type="button" id="colAllArr" value="{CollapseAllArr}"/>
        <span style="margin-left: 15px; margin-right: 15px">&amp;</span>
        <input type="button" id="expAllAmp" value="{ExpandAllAmp}"/> <input type="button" id="colAllAmp" value="{CollapseAllAmp}"/>
    </th>
</tr>
<tr style="visibility: hidden;">
    <th></th>
</tr>
<tr>
    <th class="c pad" colspan="4">{ShowingXofYRows}</th>
</tr>
{Pagination}
<tr>
    <th class="c pad" width="10%">{Time}</th>
    <th class="c pad" width="15%">{File}</th>
    <th class="c pad" width="60%">{Data}</th>
    <th class="c pad" width="15%">{Actions}</th>
</tr>
