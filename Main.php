<?php

/**
 * @package Dev
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\dev;

use gplcart\core\Config;
use gplcart\core\Library;
use gplcart\core\Logger;
use gplcart\core\Module;
use RuntimeException;

/**
 * Main class for Dev module
 */
class Main
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * Library class instance
     * @var \gplcart\core\Library $library
     */
    protected $library;

    /**
     * Logger class instance
     * @var \gplcart\core\Logger $logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     * @param Config $config
     * @param Library $library
     * @param Module $module
     */
    public function __construct(Logger $logger, Config $config, Library $library, Module $module)
    {
        $this->module = $module;
        $this->logger = $logger;
        $this->library = $library;
        $this->db = $config->getDb();
    }

    /**
     * Implements hook "construct"
     */
    public function hookConstruct()
    {
        $this->setLogger();
        require_once $this->getKintFile();
    }

    /**
     * Implements hook "construct.controller"
     * @param \gplcart\core\Controller $controller
     */
    public function hookConstructController($controller)
    {
        $this->setModuleAssets($controller);
    }

    /**
     * Implements hook "template.output"
     * @param string $html
     * @param \gplcart\core\Controller $controller
     */
    public function hookTemplateOutput(&$html, $controller)
    {
        $this->setDevToolbar($html, $controller);
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
            'access' => GC_PERM_SUPERADMIN,
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

    /**
     * Returns a path to Kint's init file
     * @return string
     */
    public function getKintFile()
    {
        $file = __DIR__ . '/vendor/kint-php/kint/init.php';

        if (is_file($file)) {
            return $file;
        }

        throw new RuntimeException("Kint file $file not found");
    }

    /**
     * Sets module specific assets
     * @param \gplcart\core\Controller $controller
     */
    protected function setModuleAssets($controller)
    {
        if (!$controller->isInternalRoute()) {
            $settings = $this->module->getSettings('dev');
            if (!empty($settings['status'])) {
                $controller->setJsSettings('dev', array('key' => $settings['key']));
                $controller->setJs(__DIR__ . '/js/common.js', array('position' => 'bottom'));
                $controller->setCss(__DIR__ . '/css/common.css');
            }
        }
    }

    /**
     * Adds toolbar
     * @param string $html
     * @param \gplcart\core\Controller $controller
     */
    protected function setDevToolbar(&$html, $controller)
    {
        if (!$controller->isInternalRoute()) {
            $settings = $this->module->getSettings('dev');
            if (!empty($settings['status'])) {

                $data = array(
                    'key' => $settings['key'],
                    'time' => microtime(true) - GC_START,
                    'queries' => $this->db->getExecutedQueries()
                );

                $toolbar = $controller->render('dev|toolbar', $data);
                $html = substr_replace($html, $toolbar, strpos($html, '</body>'), 0);
            }
        }
    }

    /**
     * Configure system logger
     */
    protected function setLogger()
    {
        $settings = $this->module->getSettings('dev');

        $this->logger->printError(!empty($settings['print_error']))
            ->errorToException(!empty($settings['error_to_exception']))
            ->printBacktrace(!empty($settings['print_error_backtrace']));
    }

}
