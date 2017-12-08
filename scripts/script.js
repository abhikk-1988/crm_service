/**
 * @version 1.0
 * script.js 
 * main script file for common functionality in application
 * @author Abhishek Agrawal
 */


/**
 * Extending chartjs plugin configuration to add Text inside a pie chart 
 */

Chart.pluginService.register({
    beforeDraw: function (chart) {
        
        if (chart.options.centertext) {
            var width = chart.chart.width,
            height = chart.chart.height,
            ctx = chart.chart.ctx;

            ctx.restore();      
            ctx.font            = 'lighter 1.2em sans-serif';
            ctx.color           = '#F00';
            ctx.textBaseline    = "middle";
            var text            = chart.options.centertext, // "75%",
            textX               = Math.round((width - ctx.measureText(text).width) / 2),
            textY               = height / 2;
            ctx.fillStyle       = chart.options.fillstyle;
            var lineHeight      = ctx.measureText(text).width/1.2;
            ctx.fillText(text, textX, textY);
            ctx.save();
        }
        
        if (chart.config.options.showAllTooltips) {
            // create an array of tooltips
            // we can't use the chart tooltip because there is only one tooltip per chart
            chart.pluginTooltips = [];
            chart.config.data.datasets.forEach(function (dataset, i) {
                chart.getDatasetMeta(i).data.forEach(function (sector, j) {
                    chart.pluginTooltips.push(new Chart.Tooltip({
                        _chart: chart.chart,
                        _chartInstance: chart,
                        _data: chart.data,
                        _options: chart.options.tooltips,
                        _active: [sector]
                    }, chart));
                });
            });

            // turn off normal tooltips
            chart.options.tooltips.enabled = false;
        }
        
    },
    afterDraw: function (chart, easing) {
        
        if (chart.config.options.showAllTooltips) {
        // we don't want the permanent tooltips to animate, so don't do anything till the animation runs atleast once
        if (!chart.allTooltipsOnce) {
            if (easing !== 1)
                return;
            chart.allTooltipsOnce = true;
        }

        // turn on tooltips
        chart.options.tooltips.enabled = false;
        Chart.helpers.each(chart.pluginTooltips, function (tooltip) {
            tooltip.initialize();
            tooltip.update();
            // we don't actually need this since we are not animating tooltips
            tooltip.pivot();
            tooltip.transition(easing).draw();
        });
        
        chart.options.tooltips.enabled = false;
    } // End IF
    }
    
});