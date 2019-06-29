<html lang="en">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title>{PageTitle}</title>
        <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
        <link rel="icon" href="../favicon.ico" type="image/x-icon" />
        <link rel="stylesheet" type="text/css" href="../skins/epicblue/default.css" />
        <link rel="stylesheet" type="text/css" href="../skins/epicblue/formate.css" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    </head>
    <body>
        <style>
        th, td {
            padding: 5px;
        }

        a {
            color: skyblue;
        }

        div.lang_selector {
            padding: 5px;
        }

        .main_container {
            width: 100%;
            text-align: center;
        }

        .content {
            display: inline-block;
        }
        </style>

        <div class="main_container">
            <div class="content">
                <h1 style="font-size: 25pt;">{PageTitle}</h1>
                <table style="width: 800px;">
                    <tbody>
                        <tr>
                            <th colspan="3" class="info">
                                {SelectLang_infobox_combined}
                            </th>
                        </tr>
                        <tr style="visibility: hidden;">
                            <th>&nbsp;</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="info">
                                {LangOptions_combined}
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </body>
</html>
