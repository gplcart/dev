[![Build Status](https://scrutinizer-ci.com/g/gplcart/dev/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gplcart/dev/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/dev/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gplcart/dev/?branch=master)

Dev is a [GPL Cart](https://github.com/gplcart/gplcart) module that provides a set of tools for GPL Cart developers

**Features**

- Kint - a powerful and modern PHP debugging tool. Use `d()` instead of `print_r()`
- Page toolbar that shows:
  - SQL queries on the page
  - page execution time
  - results of [PageSpeed Insights](https://developers.google.com/speed/docs/insights/about) test
  - markup errors from W3C Validator

**Installation**

This module requires 3-d party library which should be downloaded separately. You have to use [Composer](https://getcomposer.org) to download all the dependencies.

1. From your web root directory: `composer require gplcart/dev`. If the module was downloaded and placed into `system/modules` manually, run `composer update` to make sure that all 3-d party files are presented in the `vendor` directory.
2. Go to `admin/module/list` end enable the module
3. Adjust settings on `admin/module/settings/dev`