<?php
/**
 *
 * Class PluginInstallController
 *
 * PDF Generator plugin for Craft CMS 3 or Craft CMS 4
 *
 * @link      https://cooltronic.pl
 * @link      https://potacki.com
 * @license   https://github.com/cooltronicpl/Craft-document-helpers/blob/master/LICENSE.md
 * @copyright Copyright (c) 2023 CoolTRONIC.pl sp. z o.o. by Pawel Potacki
 */

namespace cooltronicpl\documenthelpers\controller;

use Craft;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;

/**
 * Class PluginInstallController
 * @author    CoolTRONIC.pl sp. z o.o. <github@cooltronic.pl>
 * @author    Pawel Potacki
 * @since     1.3.1
 * @since     0.4.1
 */

class PluginInstallController extends Controller
{

    public function actionInstallPackage()
    {
        // Check if the package parameter is set
        $package = Craft::$app->request->getParam('package');
        $version = Craft::$app->request->getParam('version');

        if ($package && $version) {
            $root = Craft::getAlias('@root');
            $composer = Craft::getAlias('@documenthelpers') . '/' . 'resources/composer.phar';
            $php = $this->getPHPExecutable();
            if (isset($_SERVER["WINDIR"])) {
                exec("cd " . StringHelper::toString($root) . " && " . StringHelper::toString($php) . " " . StringHelper::toString($composer) . " require " . StringHelper::toString($package) . ":^" . StringHelper::toString($version), $out, $retval);
                Craft::info("PDF Generator - Command Installer: " . "cd " . StringHelper::toString($php) . " " . StringHelper::toString($composer) . " require " . StringHelper::toString($package) . ":^" . StringHelper::toString($version) . " Output: " . StringHelper::toString($out) . " Returned value: " . StringHelper::toString($retval) . " for package: " . $package);
            } else {
                exec("cd " . StringHelper::toString($root) . " && chmod +x " . StringHelper::toString($composer) ." && " . StringHelper::toString($php) . " " . StringHelper::toString($composer) . " require " . StringHelper::toString($package) . ":^" . StringHelper::toString($version), $out, $retval);
                Craft::info("PDF Generator - Command Installer: " . "cd " . StringHelper::toString($root) . " && chmod +x " . StringHelper::toString($composer) ." && " . StringHelper::toString($php) . " " . StringHelper::toString($composer) . " require " . StringHelper::toString($package) . ":^" . StringHelper::toString($version));
            }
            if($retval == 0) {
                if (isset($_SERVER["WINDIR"]))
                    exec("cd " .  StringHelper::toString($root) . " && " . StringHelper::toString($php) . " " . StringHelper::toString($composer) . " show | findstr /C:\"$package\"", $out2, $retval2);
                else
                    exec("cd " .  StringHelper::toString($root) . ' && if grep -Ewq "' .  StringHelper::toString($package) . '" composer.json; then echo "true"; else echo "false"; fi', $out2, $retval2);
                if (StringHelper::toString($out2)=="true")
                    Craft::$app->session->setNotice(StringHelper::toString("Optional package $package is installed."));
                elseif (strpos(StringHelper::toString($out2), "$package") !== false)
                    Craft::$app->session->setNotice(StringHelper::toString("Optional package $package is installed."));
                else
                    Craft::$app->session->setError(StringHelper::toString("Error when installing $package: " . StringHelper::toString($out) . " Exec() retval: " . StringHelper::toString($retval) . "Out2 status: ". StringHelper::toString($out2) ." Exec() retval2: " . StringHelper::toString($retval2)));

            } else {
                Craft::$app->session->setError(StringHelper::toString("Error when installing $package: ".StringHelper::toString($out)." Exec() retval: " . StringHelper::toString($retval)));
            }
            return $this->redirect(UrlHelper::cpUrl('settings/plugins/documenthelpers'));
        }
    }

    private function getPHPExecutable()
    {
        $plugin = Craft::$app->plugins->getPlugin('documenthelpers');
        // Get the settings
        $settings = $plugin->getSettings();
        $settings = $settings->toArray();
        $phpPath = $settings['phpPath'];
        if (isset($phpPath) && @defined($phpPath) && !empty($phpPath) ) {
            return $phpPath;
        }

        exec("which php7.2", $out, $ret);
        if ($ret == 0) {
            return $out;
        }
        exec("which php7.3", $out, $ret);
        if ($ret == 0) {
            return $out;
        }
        exec("which php7.4", $out, $ret);
        if ($ret == 0) {
            return $out;
        }
        
        if (@defined(PHP_BINARY) && str_contains(PHP_BINARY, 'php')) {
            return PHP_BINARY;
        }

        if (isset($_SERVER["_"]) && str_contains($_SERVER["_"], 'php')) {
            return $_SERVER["_"];
        }

        $paths = explode(PATH_SEPARATOR, getenv('PATH'));
        foreach ($paths as $path) {
            // we need this for XAMPP (Windows)
            if (strstr($path, 'php.exe') && isset($_SERVER["WINDIR"]) && file_exists($path) && is_file($path)) {
                return $path;
            } else {
                $php_executable = $path . DIRECTORY_SEPARATOR . "php" . (isset($_SERVER["WINDIR"]) ? ".exe" : "");
                if (file_exists($php_executable) && is_file($php_executable)) {
                    return $php_executable;
                }
            }
        }
        if (php_ini_loaded_file() !== null) {
            $php_executable = dirname(php_ini_loaded_file()) . DIRECTORY_SEPARATOR . "php" . (isset($_SERVER["WINDIR"]) ? ".exe" : "");
            if (file_exists($php_executable) && is_file($php_executable)) {
                return $php_executable;
            }
        }

        return "php"; // not found
    }

}
