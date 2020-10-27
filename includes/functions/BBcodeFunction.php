<?php

function bbcodeChat($String)
{
    $patterns = array
    (
        '/\[b\](.*?)\[\/b\]/is',
        '/\[i\](.*?)\[\/i\]/is',
        '/\[u\](.*?)\[\/u\]/is',
        '/\[s\](.*?)\[\/s\]/is',
        '/\[url=(.*?)\](.*?)\[\/url\]/is',
        '/\[url\](.*?)\[\/url\]/is',
        '/\[color=(.*?)\](.*?)\[\/color\]/is',
        '/\[coord=([0-9]{1,3}):([0-9]{1,3}):([0-9]{1,3})\]/is',
    );

    $replacers = array
    (
        '<b>\1</b>',
        '<i>\1</i>',
        '<u>\1</u>',
        '<span style="text-decoration: line-through;">\1</span>',
        function ($matches) {
            return urlfix($matches[1], $matches[2]);
        },
        function ($matches) {
            return urlfix($matches[1], $matches[1]);
        },
        '<span style="color: \1;">\2</span>',
        '<a href="galaxy.php?mode=3&amp;galaxy=\1&amp;system=\2&amp;planet=\3">[\1:\2:\3]</a>',
    );

    $result = nl2br(htmlspecialchars(stripslashes($String)));

    foreach ($patterns as $idx => $pattern) {
        $replacer = $replacers[$idx];

        if (is_callable($replacer)) {
            $result = preg_replace_callback($pattern, $replacer, $result);
        } else {
            $result = preg_replace($pattern, $replacer, $result);
        }
    }

    return $result;
}

function bbcode($string)
{
    $patterns = array
    (
        '/\\n/',
        '/\\r/',
        '/\[list\](.*?)\[\/list\]/is',
        '/\[b\](.*?)\[\/b\]/is',
        '/\[strong\](.*?)\[\/strong\]/is',
        '/\[i\](.*?)\[\/i\]/is',
        '/\[u\](.*?)\[\/u\]/is',
        '/\[s\](.*?)\[\/s\]/is',
        '/\[del\](.*?)\[\/del\]/is',
        '/\[url=(.*?)\](.*?)\[\/url\]/is',
        '/\[url\](.*?)\[\/url\]/is',
        '/\[email=(.*?)\](.*?)\[\/email\]/is',
        '/\[img](.*?)\[\/img\]/is',
        '/\[color=(.*?)\](.*?)\[\/color\]/is',
        '/\[quote\](.*?)\[\/quote\]/is',
        '/\[code\](.*?)\[\/code\]/is',
        '/\[size=(.*?)\](.*?)\[\/size\]/is',
        '/\[background color=(.*?)\](.*?)\[\/background\]/is',
        '/\[coord=([0-9]{1,3}):([0-9]{1,3}):([0-9]{1,3})\]/is',
    );

    $replacers = array
    (
        '',
        '',
        function ($matches) {
            return sList($matches[1]);
        },
        '<b>\1</b>',
        '<strong>\1</strong>',
        '<i>\1</i>',
        '<span style="text-decoration: underline;">\1</span>',
        '<span style="text-decoration: line-through;">\1</span>',
        '<span style="text-decoration: line-through;">\1</span>',
        function ($matches) {
            return urlfix($matches[1], $matches[2]);
        },
        function ($matches) {
            return urlfix($matches[1], $matches[1]);
        },
        '<a href="mailto:\1" title="\1">\2</a>',
        function ($matches) {
            return imagefix($matches[1]);
        },
        '<span style="color: \1;">\2</span>',
        function ($matches) {
            // TODO: implement proper styling
            return $matches[1];
        },
        function ($matches) {
            return sCode($matches[1]);
        },
        '<font size="\1">\2</font>',
        '<div style="background-color: \1">\2</div>',
        '<a href="galaxy.php?mode=3&amp;galaxy=\1&amp;system=\2&amp;planet=\3">[\1:\2:\3]</a>',
    );

    $result = nl2br(htmlspecialchars(stripslashes($string)));

    foreach ($patterns as $idx => $pattern) {
        $replacer = $replacers[$idx];

        if (is_callable($replacer)) {
            $result = preg_replace_callback($pattern, $replacer, $result);
        } else {
            $result = preg_replace($pattern, $replacer, $result);
        }
    }

    return $result;
}

function image($string)
{
    $string = str_replace("&#39;", "'", $string);

    return $string;
}

function sCode($string)
{
    $pattern ='/\<img src=\\\"(.*?)img\/smilies\/(.*?).png\\\" alt=\\\"(.*?)\\\" \/>/s';
    $string = preg_replace($pattern, '\3', $string);
    return '<pre>' . trim($string) . '</pre>';
}

function sList($string)
{
    $tmp = explode('[*]', stripslashes($string));
    $out = null;
    foreach($tmp as $list)
    {
        if(strlen(str_replace('', '', $list)) > 0)
        {
            $out .= '<li>' . trim($list) . '</li>';
        }
    }
    return '<ul>' . $out . '</ul>';
}

function imagefix($img)
{
    if(substr($img, 0, 7) != 'http://')
    {
        $img = './images/'.$img;
    }
    return '<img src="'.$img.'" alt="'.$img.'" title="'.$img.'" />';
}

function urlfix($url, $title)
{
    $title = stripslashes($title);
    return "<a href=\"{$url}\" title=\"{$title}\" target=\"_blank\"><img src=\"images/url.png\" align=\"absmiddle\"> {$title}</a>";
}
?>
