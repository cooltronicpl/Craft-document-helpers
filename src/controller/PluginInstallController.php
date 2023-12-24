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
use craft\web\Controller;
use yii\web\Response;

/**
 * Class PluginInstallController
 * @author    CoolTRONIC.pl sp. z o.o. <github@cooltronic.pl>
 * @author    Pawel Potacki
 * @since     1.3.1
 * @since     0.4.1
 */

class PluginInstallController extends Controller
{

    public function actionTogglePackage()
    {
        $plugin = Craft::$app->plugins->getPlugin('document-helpers');
        $settings = $plugin->getSettings()->toArray();
        $phpPath = $settings['phpPath'];
        if (isset($phpPath) && @defined($phpPath) && !empty($phpPath)) {
            $php = $phpPath;
        } else {
            $php = $this->getPHPExecutable();
        }
        $composer = Craft::getAlias('@document-helpers') . '/' . 'resources/composer.phar';
        $installed = Craft::$app->request->getParam('installed');
        $package = Craft::$app->request->getParam('package');
        $version = Craft::$app->request->getParam('version');
        $outputContent = '';
        $success = false;
        $outputChmod = '';
        if (isset($package) && isset($version) && isset($installed)) {
            $command = ($installed == '0') ? 'require' : 'remove';
            if ($command == "require") {
                $composerCommand = sprintf(
                    '%s %s %s %s:^%s',
                    escapeshellarg($php),
                    escapeshellarg($composer),
                    escapeshellarg($command),
                    escapeshellarg($package),
                    escapeshellarg($version)
                );
            } elseif ($command == "remove") {
                $composerCommand = sprintf(
                    '%s %s %s %s',
                    escapeshellarg($php),
                    escapeshellarg($composer),
                    escapeshellarg($command),
                    escapeshellarg($package)
                );
            }
            if (!isset($_SERVER["WINDIR"])) {
                $outputChmod = $this->processCommand("chmod +x " . escapeshellarg($composer));
            }
            $outputContent = $this->processCommand($composerCommand);

        } else {
            $response = Craft::$app->getResponse();
            $response->format = Response::FORMAT_JSON;
            $response->data = ['success' => false, 'installed' => ($installed == 0), 'message' => "Bad input parameters for $package!"];
            return $response;
        }
        Craft::info("PDF Generator - PluginInstall Command: " . $composerCommand . " Output: " . json_encode($outputContent) . " Returned value for package: " . (is_array($package) ? implode('', $package) : $package));

        if (isset($_SERVER["WINDIR"])) {
            $out2 = $this->processCommand(escapeshellarg($php) . " " . escapeshellarg($composer) . " show | findstr /C:\"" . escapeshellarg($package) . "\"");
        } else {
            $out2 = $this->processCommand(escapeshellarg($php) . " " . escapeshellarg($composer) . " show | grep " . escapeshellarg($package));
        }
        $success = ($installed == '0' && !empty($out2)) || ($installed == '1' && empty($out2));

        $response = Craft::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data = [
            'success' => $success,
            'installed' => ($installed == 0),
            'message' => $success
            ? "Package $package has been " . (($installed == 0) ? "installed " : "uninstalled ") . "successfully. \n $outputContent"
            : "Package " . (($installed == 0) ? "install " : "uninstall ") . "unsuccessful. Try to do it manually, more info in the Plugin Documentation. \n $outputContent",
        ];
        Craft::debug('PDF Generator - Debugging Info:');
        Craft::debug('$installed: ' . json_encode($installed));
        Craft::debug('$out2: ' . json_encode($out2));
        Craft::debug('$outputContent: ' . json_encode($outputContent));
        Craft::debug('$outputChmod: ' . json_encode($outputChmod));
        Craft::debug('$success: ' . json_encode($success));
        return $response;
    }

    private function getPHPExecutable()
    {

        $craftVersion = Craft::$app->getVersion();
        if (version_compare($craftVersion, '4.0', '>=')) {
            exec("which php8.0", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
            exec("which php8.1", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
            exec("which php8.2", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
            exec("which php8.3", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
        } elseif (version_compare($craftVersion, '3.0', '>=')) {
            exec("which php7.2", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
            exec("which php7.3", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
            exec("which php7.4", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
            exec("which php8.0", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
            exec("which php8.1", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
            exec("which php8.2", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
            exec("which php8.3", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
        }

        if (@defined(PHP_BINARY) && str_contains(PHP_BINARY, 'php')) {
            return (is_array(PHP_BINARY) ? implode('', PHP_BINARY) : PHP_BINARY);
        }

        if (isset($_SERVER["_"]) && str_contains($_SERVER["_"], 'php')) {
            return (is_array($_SERVER["_"]) ? implode('', $_SERVER["_"]) : $_SERVER["_"]);
        }

        $paths = explode(PATH_SEPARATOR, getenv('PATH'));
        foreach ($paths as $path) {
            if (strstr($path, 'php.exe') && isset($_SERVER["WINDIR"]) && file_exists($path) && is_file($path)) {
                return (is_array($path) ? implode('', $path) : $path);
            } else {
                $php_executable = $path . DIRECTORY_SEPARATOR . "php" . (isset($_SERVER["WINDIR"]) ? ".exe" : "");
                if (file_exists($php_executable) && is_file($php_executable)) {
                    return (is_array($php_executable) ? implode('', $php_executable) : $php_executable);
                }
            }
        }
        if (php_ini_loaded_file() !== null) {
            $php_executable = dirname(php_ini_loaded_file()) . DIRECTORY_SEPARATOR . "php" . (isset($_SERVER["WINDIR"]) ? ".exe" : "");
            if (file_exists($php_executable) && is_file($php_executable)) {
                return (is_array($php_executable) ? implode('', $php_executable) : $php_executable);
            }
        }

        return "php";
    }

    private function processCommand($command)
    {
        $root = Craft::getAlias('@root');
    
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
    
        $process = proc_open($command, $descriptors, $pipes, $root);
    
        if (!is_resource($process)) {
            return ['output' => '', 'returnValue' => false];
        }
    
        fclose($pipes[0]);
    
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
    
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $return="";

        $returnValue = proc_close($process);
        if (!empty($stdout))
        {
            $return = $stdout; 
        } elseif (!empty($stderr))
        {
            $return = $stderr; 
        }
        return $return;
    }

}
