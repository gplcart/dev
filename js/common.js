/**
 * @package Dev
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
(function (document, $, GplCart) {

    "use strict";

    GplCart.modules.dev = {callbacks: {}};

    /**
     * Shows the PageSpeed score for the page being analyzed
     * @param {Object} result
     * @returns {undefined}
     */
    GplCart.modules.dev.callbacks.displayPageSpeedScore = function (result) {
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
    GplCart.modules.dev.callbacks.displayTopPageSpeedSuggestions = function (result) {

        var i, len, ul, li, suggestions, results = [],
                ruleResults = result.formattedResults.ruleResults;

        for (i in ruleResults) {
            if (ruleResults[i].ruleImpact >= 3.0) {
                results.push({name: ruleResults[i].localizedRuleName, impact: ruleResults[i].ruleImpact});
            }
        }

        results.sort(sortByImpact);
        ul = document.createElement('ul');

        for (i = 0, len = results.length; i < len; ++i) {
            li = document.createElement('li');
            li.innerHTML = results[i].name;
            ul.insertBefore(li, null);
        }

        if (ul.hasChildNodes()) {
            suggestions = ul;
        } else {
            // noinspection JSCheckFunctionSignatures
            suggestions = GplCart.text('No high impact suggestions');
        }

        $('#dev-module-toolbar .details .pagespeed .suggestions').html(suggestions);
    };

    /**
     * Displays a pie chart that shows the resource size breakdown of the page being analyzed
     * @param {Object} result
     * @returns {undefined}
     */
    GplCart.modules.dev.callbacks.displayResourceSizeBreakdown = function (result) {

        var field, val, stats = result.pageStats, labels = [],
                data = [], colors = [], totalBytes = 0, largestSingleCategory = 0, query, image;

        var resources = [
            {label: 'JavaScript', field: 'javascriptResponseBytes', color: 'e2192c'},
            {label: 'Images', field: 'imageResponseBytes', color: 'f3ed4a'},
            {label: 'CSS', field: 'cssResponseBytes', color: 'ff7008'},
            {label: 'HTML', field: 'htmlResponseBytes', color: '43c121'},
            {label: 'Flash', field: 'flashResponseBytes', color: 'f8ce44'},
            {label: 'Text', field: 'textResponseBytes', color: 'ad6bc5'},
            {label: 'Other', field: 'otherResponseBytes', color: '1051e8'}
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
            'chds=0,' + largestSingleCategory
        ].join('&');

        image = '<img src="http://chart.apis.google.com/chart?' + query + '">';
        $('#dev-module-toolbar .details .pagespeed .resources').html(image);
    };

    /**
     * Invokes the PageSpeed Insights API. The response will contain
     * JavaScript that invokes our callback with the PageSpeed results
     * @returns {undefined}
     */
    var runPagespeed = function () {
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
    };


    /**
     * Helper function that sorts results in order of impact
     * @param {Object} a
     * @param {Object} b
     * @returns {Number}
     */
    var sortByImpact = function (a, b) {
        return b.impact - a.impact;
    };

    /**
     * Check the current page markup with https://validator.w3.org
     * @returns {undefined}
     */
    var checkMarkup = function () {

        var node, doctype, message, messages = [], list = '';

        node = document.doctype;
        doctype = node ? "<!DOCTYPE "
                + node.name
                + (node.publicId ? ' PUBLIC "' + node.publicId + '"' : '')
                + (!node.publicId && node.systemId ? ' SYSTEM' : '')
                + (node.systemId ? ' "' + node.systemId + '"' : '')
                + '>' : '';

        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: 'https://validator.w3.org/nu/?out=json',
            data: doctype + document.documentElement.outerHTML,
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

                for (var type in messages) {
                    $('div#dev-module-toolbar .summary .validator .' + type).text(messages[type].length);
                }
            }
        });
    };

    /**
     * Escape HTML
     * @param {String} s
     * @returns {String}
     */
    var escapeHTML = function (s) {
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
    };

    /**
     * Invoke functions when DOM is ready
     * @returns {undefined}
     */
    GplCart.onload.setupDevToolbar = function () {

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
    };

})(document, jQuery, GplCart);

/**
 * Our JSONP callback. Checks for errors, then invokes our callback handlers
 * @param {Object} result
 * @returns {undefined}
 */
function runPagespeedCallbacks(result) {

    /* global GplCart */

    var errors, f, fn;

    if (result.error) {
        errors = result.error.errors;
        for (var i = 0, len = errors.length; i < len; ++i) {
            if (errors[i].reason === 'badRequest') {
                $('#dev-module-toolbar .summary .pagespeed').append('PageSpeed Insights: wrong API key');
            } else {
                $('#dev-module-toolbar .summary .pagespeed').append('PageSpeed Insights: ' + errors[i].message);
            }
        }
    } else {
        for (fn in GplCart.modules.dev.callbacks) {
            f = GplCart.modules.dev.callbacks[fn];
            if (typeof f === 'function') {
                GplCart.modules.dev.callbacks[fn](result);
            }
        }
    }
}
