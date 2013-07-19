{ChronoAppletScript}
<tr>
	<th class="pad2" style="font-size: 15px;" id="bxxQueueFirstTimer" colspan="2">{EndTimer}</th>
</tr>
<tr>
	<th class="pad2" colspan="2">
		<table class="w100p">
			<tr>
				<td valign="top" style="width: 70px;" class="center">
					<a href="?cp={PlanetID}&amp;mode=research&amp;re=0"><img src="{SkinPath}planeten/small/s_{PlanetImg}.jpg" width="48" height="48" class="buildImg"/></a>
				</td>
				<td style="text-align: left;">
					<b>{Queue_ResearchOn}:</b><br /><a class="{PlanetLabColor}" href="?cp={PlanetID}&amp;mode=research&amp;re=0">{PlanetName} [{PlanetCoords}]</a>
				</td>
			</tr>
		</table>
	</th>
</tr>
<tr>
	<th class="pad2" colspan="2">
		<table class="w100p">
			<tr>
				<td valign="top" style="width: 70px;">
					<a href="infos.php?gid={ElementID}"><img src="{SkinPath}gebaeude/{ElementID}.gif" width="64" height="64" class="buildImg"/></a>
				</td>
				<td style="text-align: left;" valign="top">
					<a href="infos.php?gid={ElementID}"><b>{Name}</b></a><br />
					<b>{LevelText} {Level}<br /><br />
					<b>{EndText}:</b> <b class="lime endDate" title="<center>{EndTitleBeg} {EndDateExpand}<br/>{EndTitleHour} {EndTimeExpand}</center>">{EndDate}</b>
				</td>
			</tr>
		</table>
	</th>
</tr>
<tr>
	<th class="pad2" colspan="2">
		<a id="QueueCancel" href="buildings.php?mode=research&amp;cmd=cancel" class="cancelQueue {PremBlock}">{CancelText}</a>
	</th>
</tr>