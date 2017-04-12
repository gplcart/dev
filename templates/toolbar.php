<?php
/**
 * @package Dev
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */
?>
<div id="dev-module-toolbar">
  <div class="summary">
    <div class="column queries">
      SQL queries: <b><?php echo count($queries); ?></b> Execution time: <b><?php echo $time; ?>sec</b>
    </div>
    <div class="column pagespeed"></div>
  </div>
  <div class="details">
    <div class="left column queries">
      <ul>
        <?php foreach ($queries as $query) { ?>
        <li class="small"><?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php } ?>
      </ul>
    </div>
    <div class="right column pagespeed">
      <div class="column resources"></div>
      <div class="column suggestions"></div>
    </div>
  </div>
</div>
<script>

    var callbacks = {};

    /**
     * Shows the PageSpeed score for the page being analyzed
     * @param {Object} result
     * @returns {undefined}
     */
    callbacks.displayPageSpeedScore = function (result) {
        var text, score = '--';
        if (result.score) {
            score = result.score;
        }
        text = GplCart.text('Google PageSpeed Score: <b>@score</b>', {'@score': score});
        $('#dev-module-toolbar .summary .pagespeed').append(text);
    };

    /**
     * Displays the names of the top PageSpeed suggestions for the page being analyzed, as an unordered list
     * @param {Object} result
     * @returns {undefined}
     */
    callbacks.displayTopPageSpeedSuggestions = function (result) {

        var ul, li, suggestions, text, results = [],
                ruleResults = result.formattedResults.ruleResults;

        for (var i in ruleResults) {
            if (ruleResults[i].ruleImpact >= 3.0) {
                results.push({name: ruleResults[i].localizedRuleName, impact: ruleResults[i].ruleImpact});
            }
        }

        results.sort(sortByImpact);
        ul = document.createElement('ul');

        for (var i = 0, len = results.length; i < len; ++i) {
            li = document.createElement('li');
            li.innerHTML = results[i].name;
            ul.insertBefore(li, null);
        }

        if (ul.hasChildNodes()) {
            suggestions = results.length;
            $('#dev-module-toolbar .details .pagespeed .suggestions').html(ul);
        } else {
            suggestions = GplCart.text('No high impact suggestions');
        }

        text = ' ' + GplCart.text('Suggestions: <b>@text</b>', {'@text': suggestions});
        $('#dev-module-toolbar .summary .pagespeed').append(text);
    };

    /**
     * Displays a pie chart that shows the resource size breakdown of the page being analyzed
     * @param {Object} result
     * @returns {undefined}
     */
    callbacks.displayResourceSizeBreakdown = function (result) {

        var field, val, stats = result.pageStats, labels = [],
                data = [], colors = [], totalBytes = 0, largestSingleCategory = 0, query, image;

        var resources = [
            {label: 'JavaScript', field: 'javascriptResponseBytes', color: 'e2192c'},
            {label: 'Images', field: 'imageResponseBytes', color: 'f3ed4a'},
            {label: 'CSS', field: 'cssResponseBytes', color: 'ff7008'},
            {label: 'HTML', field: 'htmlResponseBytes', color: '43c121'},
            {label: 'Flash', field: 'flashResponseBytes', color: 'f8ce44'},
            {label: 'Text', field: 'textResponseBytes', color: 'ad6bc5'},
            {label: 'Other', field: 'otherResponseBytes', color: '1051e8'},
        ];

        for (var i = 0, len = resources.length; i < len; ++i) {

            field = resources[i].field;

            if (field in stats) {
                val = Number(stats[field]);
                totalBytes += val;
                if (val > largestSingleCategory){
                    largestSingleCategory = val;
                }
                labels.push(resources[i].label);
                data.push(val);
                colors.push(resources[i].color);
            }
        }

        query = [
            'chs=300x140',
            'cht=p3',
            'chts=' + ['000000', 16].join(','),
            'chco=' + colors.join('|'),
            'chd=t:' + data.join(','),
            'chdl=' + labels.join('|'),
            'chdls=000000,14',
            'chp=1.6',
            'chds=0,' + largestSingleCategory,
        ].join('&');

        image = '<img src="http://chart.apis.google.com/chart?' + query + '">';
        $('#dev-module-toolbar .details .pagespeed .resources').html(image);
    };

    /**
     * Invokes the PageSpeed Insights API. The response will contain
     * JavaScript that invokes our callback with the PageSpeed results
     * @returns {undefined}
     */
    function runPagespeed() {
        var script, query;
        if (GplCart.settings.dev && GplCart.settings.dev.key) {
            script = document.createElement('script');
            script.type = 'text/javascript';
            script.async = true;
            query = [
                'url=' + 'https://developers.google.com/speed/pagespeed/insights/',
                'callback=runPagespeedCallbacks',
                'key=' + GplCart.settings.dev.key
            ].join('&');
            script.src = 'https://www.googleapis.com/pagespeedonline/v2/runPagespeed?' + query;
            document.head.insertBefore(script, null);
        }
    }

    /**
     * Our JSONP callback. Checks for errors, then invokes our callback handlers
     * @param {Object} result
     * @returns {undefined}
     */
    function runPagespeedCallbacks(result) {

        var errors, f, fn;

        if (result.error) {
            errors = result.error.errors;
            for (var i = 0, len = errors.length; i < len; ++i) {
                if (errors[i].reason === 'badRequest') {
                    $('#dev-module-toolbar .summary .pagespeed').append('PageSpeed Insights: please specify your Google API key');
                } else {
                    $('#dev-module-toolbar .summary .pagespeed').append('PageSpeed Insights: ' + errors[i].message);
                }
            }
        } else {
            for (fn in callbacks) {
                f = callbacks[fn];
                if (typeof f === 'function') {
                    callbacks[fn](result);
                }
            }
        }
    }

    /**
     * Helper function that sorts results in order of impact
     * @param {Object} a
     * @param {Object} b
     * @returns {Number}
     */
    function sortByImpact(a, b) {
        return b.impact - a.impact;
    }

    /**
     * Invoke functions when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        $('#dev-module-toolbar').click(function () {

            if ($(this).hasClass('slide-up')) {
                $(this).toggleClass('slide-down', 'slide-up');
            } else {
                $(this).toggleClass('slide-up', 'slide-down');
            }
        });

        runPagespeed();
    });
</script>
<style>
  #dev-module-toolbar {
      border-top: 1px solid #000;
      position: fixed;
      z-index: 10000;
      height: 300px;
      background-color: #fff;
      right: 0;
      left: 0;
      width: 100%;
      bottom: -270px;
      padding-right: 15px;
      padding-left: 15px;
      margin-right: auto;
      margin-left: auto;
  }

  #dev-module-toolbar .summary {
      line-height: 30px;
  }

  #dev-module-toolbar.slide-up {
      bottom: 0px !important;
  }

  #dev-module-toolbar.slide-down {
      bottom: -270px !important;
  }

  #dev-module-toolbar .summary,
  #dev-module-toolbar .details {
      clear: both;
  }

  #dev-module-toolbar .column {
      position: relative;
      min-height: 1px;
      padding-right: 15px;
      padding-left: 15px;
  }

  @media (min-width: 992px) {
      #dev-module-toolbar .column {
          width: 50%;
          float: left;
      }
  }

  #dev-module-toolbar .details .queries ul {
      list-style: none;
      padding: 0;
      margin: 0;
      height:250px;
      overflow:hidden;
      overflow-y:scroll;
  }
</style>

