<?php
/**
 * @package Dev
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */
?>
<div id="dev-module-toolbar">
  <div class="row summary hidden-xs">
    <div class="col-md-6 queries">
      SQL queries: <b><?php echo count($queries); ?></b> Execution time: <b><?php echo $time; ?>sec</b>
    </div>
    <div class="col-md-3 validator">
      Validator errors: <span class="result error">0</span>, warnings: <span class="result warning">0</span>
    </div>
    <div class="col-md-3 pagespeed">
      <?php if (empty($key)) { ?>
      PageSpeed Insights is not configured
      <?php } ?>
      <span class="fa fa-bars toggler"></span>
    </div>
  </div>
  <div class="row details small hidden-xs">
    <div class="queries col-md-6">
      <ul class="scroll">
        <?php foreach ($queries as $query) { ?>
        <li class="small"><?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php } ?>
      </ul>
    </div>
    <div class="col-md-3 validator">
      <div class="results scroll">No results</div>
    </div>
    <div class="col-md-3 pagespeed">
      <div class="resources"></div>
      <div class="suggestions"></div>
    </div>
  </div>
</div>
<script id="dev-module-toolbar">
    var callbacks = {};

    /**
     * Shows the PageSpeed score for the page being analyzed
     * @param {Object} result
     * @returns {undefined}
     */
    callbacks.displayPageSpeedScore = function (result) {
        var text, score = '-';
        if (result.score) {
            score = result.score;
        }
        text = GplCart.text('PageSpeed Score: @score', {'@score': score});
        $('#dev-module-toolbar .summary .pagespeed').append(text);
    };

    /**
     * Displays the names of the top PageSpeed suggestions for the page being analyzed, as an unordered list
     * @param {Object} result
     * @returns {undefined}
     */
    callbacks.displayTopPageSpeedSuggestions = function (result) {

        var ul, li, suggestions, results = [],
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
                if (val > largestSingleCategory) {
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
     * Check the current page markup with https://validator.w3.org
     * @returns {undefined}
     */
    function checkMarkup() {

        var node, doctype, clone, data, message, type, messages = [], list = '';

        node = document.doctype;
        doctype = node ? "<!DOCTYPE "
                + node.name
                + (node.publicId ? ' PUBLIC "' + node.publicId + '"' : '')
                + (!node.publicId && node.systemId ? ' SYSTEM' : '')
                + (node.systemId ? ' "' + node.systemId + '"' : '')
                + '>\n' : '';

        // Remove all data added by this template
        clone = $('html').clone();
        clone.find('#dev-module-toolbar').remove();
        data = doctype + "<html>" + clone.html() + "</html>";

        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: 'https://validator.w3.org/nu/?out=json',
            data: data,
            cache: false,
            processData: false,
            contentType: 'text/html; charset=utf-8',
            success: function (data) {
                if (!data.messages) {
                    return;
                }

                for (var i in data.messages) {
                    type = data.messages[i].type;
                    message = escapeHTML(data.messages[i].message);
                    list += '<li class="result ' + type + '"><span class="text-muted">' + data.messages[i].lastLine + ':</span> ' + message + '</li>';
                    if (messages[type] === undefined) {
                        messages[type] = [];
                    }
                    messages[type][i] = message;
                }

                list = '<ol class="list-unstyled">' + list + '</ol>';
                $('div#dev-module-toolbar .details .validator .results').html(list);

                for(var type in messages){
                    $('div#dev-module-toolbar .summary .validator .' + type).text(messages[type].length);
                }
            },
            error: function (e) {
                console.warn(e.responseText);
            }
        });
    }

    /**
     * Escape HTML
     * @param {String} s
     * @returns {String}
     */
    function escapeHTML(s) {
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
    }

    /**
     * Invoke functions when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        $('#dev-module-toolbar .toggler').click(function () {
            var el = $('#dev-module-toolbar');
            if (el.hasClass('slide-up')) {
                el.toggleClass('slide-down', 'slide-up');
            } else {
                el.toggleClass('slide-up', 'slide-down');
            }
        });

        runPagespeed();
        checkMarkup();
    });

</script>
<style id="dev-module-toolbar">
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
      position: relative;
      border-bottom: 1px solid #ddd;
  }

  #dev-module-toolbar .summary .toggler {
      position: absolute;
      right: 15px;
      top: 5px;
  }

  #dev-module-toolbar.slide-up {
      bottom: 0px !important;
  }

  #dev-module-toolbar.slide-down {
      bottom: -270px !important;
  }

  #dev-module-toolbar .scroll {
      list-style: none;
      padding: 10px 0 0 0;
      margin: 0;
      height:250px;
      overflow-y:auto;
  }

  #dev-module-toolbar .result.error {
      color: red;
  }

  #dev-module-toolbar .result.warning {
      color: orange;
  }

  #dev-module-toolbar ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
  }

  #dev-module-toolbar ::-webkit-scrollbar-track {
      background: rgba(0, 0, 0, 0.1);
  }

  #dev-module-toolbar ::-webkit-scrollbar-thumb {
      background: rgba(0, 0, 0, 0.5);
  }
</style>

