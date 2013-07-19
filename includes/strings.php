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

function colorRed($Number)
{
	return "<font color=\"#ff0000\">{$Number}</font>";
}

function colorGreen($Number)
{
	return "<font color=\"#00ff00\">{$Number}</font>";
}

function colorOrange($Number)
{
	return "<span style=\"color: orange;\">{$Number}</span>";
}

?>