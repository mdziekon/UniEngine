<form action="" method="post">
    <input type="hidden" name="uniSelect" value="{PHP_InsertUniCode}"/>
    <table width="500" style="margin-top: 100px;">
        <tr>
            <td class="c center" colspan="2">{Page_Title}</td>
        </tr>
        <tr>
            <th class="pad5" style="width: 200px;"><label for="uname">{Body_Username}:</label></th>
            <th class="pad5"><input id="uname" name="username" value="" type="text" class="pad5" style="width: 200px;"/></th>
        </tr>
        <tr>
            <th class="pad5" style="width: 200px;"><label for="upass">{Body_Password}:</label></th>
            <th class="pad5"><input id="upass" name="password" value="" type="password" class="pad5" style="width: 200px;"/></th>
        </tr>
        <tr>
            <th colspan="2" class="pad5"><input type="{type}" name="submit" value="{LoginButton}" style="width: 100%; font-weight: bold;" class="pad5 lime"/></th>
        </tr>
        <tr style="visibility: hidden;"><td style="font-size: 5px;">&nbsp;</td></tr>
        <tr>
            <th colspan="2" class="pad5"><a href="lostpassword.php">{Body_LostPass}</a><br /><a href="lostcode.php">{Body_LostCode}</a></th>
        </tr>
        <tr>
            <th colspan="2" class="pad5">
                <a href="reg_mainpage.php">{Body_Register}</a> | <a href="contact.php">{Body_Contact}</a>
            </th>
        </tr>
        <tr style="visibility: hidden;"><td style="font-size: 5px;">&nbsp;</td></tr>
        <tr>
            <th colspan="2" class="pad5">
                {PHP_Insert_LangSelectors}
            </th>
        </tr>
        <tr style="visibility: hidden;"><td style="font-size: 5px;">&nbsp;</td></tr>
        <tr>
            <th colspan="2" class="pad5">
                <a href="https://github.com/mdziekon/UniEngine" target="_blank" class="skyblue">Powered by UniEngine</a>
            </th>
        </tr>
    </table>
</form>
