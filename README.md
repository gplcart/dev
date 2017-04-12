[![Build Status](https://scrutinizer-ci.com/g/gplcart/dev/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gplcart/dev/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/dev/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gplcart/dev/?branch=master)

Dev is a [GPL Cart](https://github.com/gplcart/gplcart) module that provides a set of tools for GPL Cart developers

**Features**

- Kint - a powerful and modern PHP debugging tool
- Page toolbar that shows SQL queries on the page, script execution time and  results of [PageSpeed Insights](https://developers.google.com/speed/docs/insights/about) test

**Installation**

1. Download and extract to `system/modules` manually or using composer `composer require gplcart/dev`. IMPORTANT: If you downloaded the module manually, be sure that the name of extracted module folder doesn't contain a branch/version suffix, e.g `-master`. Rename if needed.
2. Go to `admin/module/list` end enable the module
3. Adjust settings on `admin/module/settings/dev`