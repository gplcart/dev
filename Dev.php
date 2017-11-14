<?php

/**
 * @package Dev
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\dev;

use gplcart\core\Module,
    gplcart\core\Config;

/**
 * Main class for Dev module
 */
class Dev extends Module
{

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);
    }

    /**
     * Implements hook "module.install.before"
     * @param null|string $result
     */
    public function hookModuleInstallBefore(&$result)
    {
        if (!is_file($this->getKintFile())) {
            $result = $this->getLanguage()->text('Kint file not found');
        }
    }

    /**
     * Implements hook "construct"
     */
    public function hookConstruct()
    {
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
            'access' => '__superadmin',
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
        $this->getLibrary()->clearCache();
    }

    /**
     * Implements hook "module.disable.after"
     */
    public function hookModuleDisableAfter()
    {
        $this->getLibrary()->clearCache();
    }

    /**
     * Implements hook "module.install.after"
     */
    public function hookModuleInstallAfter()
    {
        $this->getLibrary()->clearCache();
    }

    /**
     * Implements hook "module.uninstall.after"
     */
    public function hookModuleUninstallAfter()
    {
        $this->getLibrary()->clearCache();
    }

    /**
     * Returns a path to Kint's init file
     * @return string
     */
    public function getKintFile()
    {
        return __DIR__ . '/vendor/kint-php/kint/init.php';
    }

    /**
     * Sets module specific assets
     * @param \gplcart\core\Controller $controller
     */
    protected function setModuleAssets($controller)
    {
        if (!$controller->isInternalRoute()) {
            $settings = $this->config->getFromModule('dev');
            if (!empty($settings['status'])) {
                $controller->setJsSettings('dev', array('key' => $settings['key']));
                $controller->setJs($this->getAsset('dev', 'common.js'), array('position' => 'bottom'));
                $controller->setCss($this->getAsset('dev', 'common.css'));
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
            $settings = $this->config->getFromModule('dev');
            if (!empty($settings['status'])) {

                $data = array(
                    'key' => $settings['key'],
                    'time' => microtime(true) - GC_START,
                    'queries' => $this->config->getDb()->getLogs()
                );

                $toolbar = $controller->render('dev|toolbar', $data);
                $html = substr_replace($html, $toolbar, strpos($html, '</body>'), 0);
            }
        }
    }

}
