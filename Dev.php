<?php

/**
 * @package Dev
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\dev;

use gplcart\core\Module,
    gplcart\core\Library;

/**
 * Main class for Dev module
 */
class Dev extends Module
{

    /**
     * Library class instance
     * @var \gplcart\core\Library
     */
    protected $library;

    /**
     * @param Library $library
     */
    public function __construct(Library $library)
    {
        parent::__construct();

        $this->library = $library;
    }

    /**
     * Module info
     * @return array
     */
    public function info()
    {
        return array(
            'name' => 'Dev',
            'version' => '1.0.0-dev',
            'description' => 'A set of tools to make developing GPL Cart sites easier',
            'author' => 'Iurii Makukh ',
            'core' => '1.x',
            'license' => 'GPL-3.0+',
            'configure' => 'admin/module/settings/dev',
            'settings' => array('key' => '', 'status' => false)
        );
    }

    /**
     * Implements hook "construct"
     */
    public function hookConstruct()
    {
        // Load KINT library asap
        require __DIR__ . '/vendor/kint-php/kint/init.php';
    }

    /**
     * Implements hook "construct.controller"
     * @param \gplcart\core\Controller $object
     */
    public function hookConstructController($object)
    {
        $settings = $this->config->module('dev');
        if (!empty($settings['status']) && !empty($settings['key']) && !$object->isBackend()) {
            $object->setJsSettings('dev', array('key' => $settings['key']));
        }
    }

    /**
     * Implements hook "destruct"
     */
    public function hookDestruct()
    {
        $disabled = !empty($_POST)//
                || (isset($_SERVER['HTTP_X_REQUESTED_WITH'])//
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if (!$disabled && $this->config->module('dev', 'status')) {
            $this->outputToolbar();
        }
    }

    /**
     * Render and print dev toolbar
     */
    protected function outputToolbar()
    {
        /* @var $db \gplcart\core\Database */
        $db = $this->config->getDb();

        $queries = $db->getLogs();
        $time = microtime(true) - GC_START;
        $key = $this->config->module('dev', 'key');

        ob_start();
        include __DIR__ . '/templates/toolbar.php';
        echo ob_get_clean();
    }

    /**
     * Implements hook "library.list"
     * @param array $libraries
     */
    public function hookLibraryList(array &$libraries)
    {
        $libraries['kint'] = array(
            'name' => 'Kint',
            'description' => 'A powerful and modern PHP debugging tool',
            'url' => 'https://github.com/raveren/kint',
            'download' => 'https://github.com/kint-php/kint/archive/2.0-alpha4.zip',
            'type' => 'php',
            'version' => '2.0-alpha4',
            'module' => 'dev',
            'files' => array(
                'vendor/kint-php/kint/init.php'
            )
        );
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        $routes['admin/module/settings/dev'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\dev\\controllers\\Settings', 'editSettings')
            )
        );
    }

    /**
     * Implements hook "module.enable.after"
     */
    public function hookModuleEnableAfter()
    {
        $this->library->clearCache();
    }

    /**
     * Implements hook "module.disable.after"
     */
    public function hookModuleDisableAfter()
    {
        $this->library->clearCache();
    }

    /**
     * Implements hook "module.install.after"
     */
    public function hookModuleInstallAfter()
    {
        $this->library->clearCache();
    }

    /**
     * Implements hook "module.uninstall.after"
     */
    public function hookModuleUninstallAfter()
    {
        $this->library->clearCache();
    }

}
