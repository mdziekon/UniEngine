<?php

function prettyNumber($Number)
{
    return number_format(floor($Number), 0, ',', '.');
}

function colorNumber($Number, $ZeroToOrange = false)
{
    if($Number > 0)
    {
        return colorGreen($Number);
    }
    else if($Number < 0)
    {
        return colorRed($Number);
    }
    else
    {
        if($ZeroToOrange)
        {
            return colorOrange($Number);
        }
        return $Number;
    }
}

function prettyColorNumber($Number, $ZeroToOrange = false)
{
    if($Number > 0)
    {
        return colorGreen(prettyNumber($Number));
    }
    else if($Number < 0)
    {
        return colorRed(prettyNumber($Number));
    }
    else
    {
        if($ZeroToOrange)
        {
            return colorOrange(prettyNumber($Number));
        }
        return prettyNumber($Number);
    }
}

function getColorHTMLValues() {
    return [
        'red' => '#ff0000',
        'green' => '#00ff00',
        'orange' => 'orange'
    ];
}

function getColorHTMLValue($colorName) {
    $values = getColorHTMLValues();

    return $values[$colorName];
}

function colorizeString($content, $colorName) {
    $colorValue = getColorHTMLValue($colorName);

    return buildDOMElementHTML([
        'tagName' => 'span',
        'contentHTML' => $content,
        'attrs' => [
            'style' => "color: {$colorValue};"
        ]
    ]);
}

function colorRed($Number) {
    $colorValue = getColorHTMLValue('red');
    return "<font color=\"{$colorValue}\">{$Number}</font>";
}

function colorGreen($Number) {
    $colorValue = getColorHTMLValue('green');
    return "<font color=\"{$colorValue}\">{$Number}</font>";
}

function colorOrange($Number) {
    $colorValue = getColorHTMLValue('orange');
    return "<span style=\"color: {$colorValue};\">{$Number}</span>";
}

?>
