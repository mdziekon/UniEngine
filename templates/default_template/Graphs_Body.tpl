<script src="{$RootPaht}libs/jquery-flot/jquery.flot.min.js"></script>
<link rel="stylesheet" type="text/css" href="{$RootPaht}dist/css/graphs.cachebuster-1546564327123.min.css"/>
<style>.Graph{literal}{{/literal}width:{$width}px; height:{$height}px;{literal}}{/literal}</style>
<script type="text/javascript">
{literal}
function Graph() {
    var Vendors = [{/literal}
    {foreach from=$cx->vendors item=v}
    {literal}{{/literal}
            name: "{$v.name}",
            vendor: "{$v.vendor}"
        {literal}}{/literal},
    {/foreach}{literal}
    ];

    var Modes = [{/literal}
    {foreach from=$cx->modes item=v}
    {literal}{{/literal}
            vendor: {$v.vendor},
            mode: "{$v.mode}",
            name: "{$v.name}",
            color: "{$v.color}"
        {literal}}{/literal},
    {/foreach}{literal}
    ];

    var Graphs = {{/literal}
    {foreach from=$graphs key=k item=v name=graphmap}
        {$k}: {literal}{{/literal}
        runs: [
        {foreach from=$v.graph->run_points key=gk item=stamp name=run_points}
            {literal}{{/literal}
                fullDate: "{$stamp|date_format:"{$v.graphdata.fullstamp}"}",
                shortDate: "{$stamp|date_format:"{$v.graphdata.shortstamp}"}",
                otherDate: "{$stamp|date_format:"{$v.graphdata.otherdate}"}",
            {literal}}{/literal}
            {if !$smarty.foreach.run_points.last},{/if}
        {/foreach}
        ],
        displays: [
        {foreach from=$v.graph->series key=sk item=sv name=series}
            {literal}{
                lines: { show: true },
                points: { show: true,
                          fillColor: "#FFF"
                        },
                borderWidth: 1.5,
                borderColor: "#FFFFFF",
                markingsLineWidth: .75,
                hoverable: true,
                clickable: true,
                shadowSize: 5,
                {/literal}
                color: Modes[{$sv.mode}].color,
                data: [
                {foreach from=$v.graph->runs key=gk item=gv name=runs}
                    {if isset($sv.scores[$gk])}[{$gk}, {$sv.scores[$gk]}]{if !$smarty.foreach.runs.last},{/if}{/if}

                {/foreach}
                ]
            {literal}}{/literal}
            {if !$smarty.foreach.series.last},{/if}
        {/foreach}
        ],
        series: [
        {foreach from=$v.graph->series key=sk item=sv name=series}
            {literal}{{/literal}
                mode: {$sv.mode},
            {literal}}{/literal}
            {if !$smarty.foreach.series.last},{/if}
        {/foreach}
        ]
        {literal}}{/literal}
        {if !$smarty.foreach.graphmap.last},{/if}
    {/foreach}{literal}
    };

    var previousPoint = null;

    function drawGraph(elt, graph, Units, tooltipGen) {
        var options = {
            yaxis: {
                min: 0,
                tickFormatter: function (v, axis) {
                    return v + Units;
                },
                invert: graph.direction == 1
            },
            xaxis: {
                tickFormatter: function (v, axis) {
                    v = Math.round(v);
                    if (!(v in graph.runs))
                        return '';
                    return graph.runs[v].shortDate;
                }
            },
            legend: { show: false },
            grid: {
                hoverable: true,
                clickable: true,
                backgroundColor: '#FFFFFF',
                borderColor: '#000000',
                borderWidth: 1,
            }
        };

        function showToolTip(x, y, contents) {
            var tipWidth = 165;
            var tipHeight = 75;
            var xOffset = 5;
            var yOffset = 5;
            var ie = document.all && !window.opera;
            var iebody = (document.compatMode == "CSS1Com[at")
                         ? document.documentElement
                         : document.body;
            var scrollLeft = ie ? iebody.scrollLeft : window.pageXOffset;
            var scrollTop = ie ? iebody.scrollTop : window.pageYOffset;
            var docWidth = ie ? iebody.clientWidth - 15 : window.innerWidth - 15;
            var docHeight = ie ? iebody.clientHeight - 15 : window.innerHeight - 8;
            y = (y + tipHeight - scrollTop > docHeight)
                ? y - tipHeight - 5 - (yOffset * 2)
                : y // account for bottom edge;

            // account for right edge
            if (x + tipWidth - scrollLeft > docWidth) {
                $('<div id="Graph_Tooltip">' + contents + '<\/div>').css( {
                    top: y + yOffset,
                    right: docWidth - x + xOffset,
                }).appendTo("body").fadeIn(200);
            } else {
                $('<div id="Graph_Tooltip">' + contents + '<\/div>').css( {
                    top: y + yOffset,
                    left: x + xOffset,
                }).appendTo("body").fadeIn(200);
            }
        }

        $.plot(elt, graph.displays, options);
        elt.bind("plothover", function (event, pos, item) {
            if (!item) {
                if($(this).data('tooltipClick') !== true){
                    $("#Graph_Tooltip").remove();
                    previousPoint = null;
                    return;
                }
            } else {

                if (previousPoint &&
                    (previousPoint[0] == item.datapoint[0]) &&
                    (previousPoint[1] == item.datapoint[1])) {
                    return;
                }

                previousPoint = item.datapoint;
                $("#Graph_Tooltip").remove();
                var x = item.datapoint[0];
                var y = item.datapoint[1];
                var text = '';

                var series = graph.series[item.seriesIndex];
                var mode = Modes[series.mode];
                var modeNo = series.mode;
                var vendor = Vendors[mode.vendor];

                {/literal}
                {if $OwnTooltipCode}{$OwnTooltipCode}{/if}
                {literal}

                var RunResult = y;
                var ModeName = mode.name;
                var FullDate = graph.runs[x].fullDate;

                var tooltipParams = {
                    RunResult: RunResult,
                    ModeName: ModeName,
                    UserID: UserID,
                    OtherDate: OtherDate,
                    Username: Username,
                    FullDate: FullDate
                };

                text = tooltipGen(tooltipParams);

                showToolTip(item.pageX, item.pageY, text);
            }
        });
        elt.bind('plotclick', function(event, post, item)
        {
            if($(this).data('tooltipClick') == true){
                $(this).data('tooltipClick', false);
                $("#Graph_Tooltip").remove();
                previousPoint = null;
            } else {
                $(this).data('tooltipClick', true);
            }
        });
    }

    $(document).ready(function () {
        {/literal}
        {foreach from=$graphs key=k item=v}
        drawGraph($("#{$k}"), Graphs.{$k}, '{$v.graphdata.units}', {$v.graphdata.tooltipGenFunction});
        {/foreach}
        {literal}
    });
}
Graph();
</script>
{/literal}
