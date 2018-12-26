<form action="galaxy.php?mode=1" method="post" id="galaxy_form" style="margin-bottom: 4px; margin-top: 5px;">
    <input type="hidden" id="auto" value="dr" />
    <table border="0">
        <tr>
            <td class="a">
                <table>
                    <tr>
                        <td class="c center" colspan="3">{Lang_Galaxy}</td>
                    </tr>
                    <tr>
                        <th class="selTH">
                            <input class="selInput" name="galaxyLeft" value="&#171;" onclick="galaxy_submit('galaxyLeft')" type="button" />
                        </th>
                        <th class="selTH">
                            <input class="selInput" name="galaxy" value="{Input_Galaxy}" size="5" maxlength="3" tabindex="1" type="text" autocomplete="off" />
                        </th>
                        <th class="selTH">
                            <input class="selInput" name="galaxyRight" value="&#187;" onclick="galaxy_submit('galaxyRight')" type="button" />
                        </th>
                    </tr>
                </table>
            </td>
            <td class="a">
                <table>
                    <tr>
                        <td class="c center" colspan="3">{Lang_System}</td>
                    </tr>
                    <tr>
                        <th class="selTH">
                            <input class="selInput" name="systemLeft" value="&#171;" onclick="galaxy_submit('systemLeft')" type="button" />
                        </th>
                        <th class="selTH">
                            <input class="selInput" name="system" value="{Input_System}" size="5" maxlength="3" tabindex="2" type="text" autocomplete="off" />
                        </th>
                        <th class="selTH">
                            <input class="selInput" name="systemRight" value="&#187;" onclick="galaxy_submit('systemRight')" type="button" />
                        </th>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="l" colspan="2" align="center">
                <input class="selSub" tabindex="3" value="{Lang_Submit}" type="submit" />
            </td>
        </tr>
    </table>
</form>
