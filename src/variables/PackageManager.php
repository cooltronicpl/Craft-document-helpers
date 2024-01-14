<?php
/**
 *
 * Class PackageManager
 *
 * PDF Generator plugin for Craft CMS 3 or Craft CMS 4.
 *
 * @link      https://cooltronic.pl
 * @link      https://potacki.com
 * @license   https://github.com/cooltronicpl/Craft-document-helpers/blob/master/LICENSE.md
 * @copyright Copyright (c) 2024 CoolTRONIC.pl sp. z o.o. by Pawel Potacki
 */

namespace cooltronicpl\documenthelpers\variables;

use Craft;

/**
 * @author    CoolTRONIC.pl sp. z o.o. <github@cooltronic.pl>
 * @author    Pawel Potacki
 * @since     2.1.0
 */

class PackageManager
{

    public function isInstalledPackage($package)
    {
        $root = Craft::getAlias('@root');
        $plugin = Craft::$app->plugins->getPlugin('document-helpers');
        $settings = $plugin->getSettings()->toArray();
        $phpPath = $settings['phpPath'];
        if (isset($phpPath) && @defined($phpPath) && !empty($phpPath)) {
            $php = $phpPath;
        } else {
            $php = $this->getPHPExecutable();
        }
        $composer = Craft::getAlias('@document-helpers') . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'composer.phar';
        $command = escapeshellarg($php) . " " . escapeshellarg($composer) . " show " . escapeshellarg($package);
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($command, $descriptors, $pipes, $root);
        if (!is_resource($process)) {
            return false;
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        proc_close($process);
        if (!empty($stdout)) {
            return true;
        } else {
            return false;
        }
    }

    private function getPHPExecutable()
    {
        $craftVersion = Craft::$app->getVersion();
        if (version_compare($craftVersion, '5.0', '>=')) {
            exec("which php8.2", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
            exec("which php8.3", $out, $ret);
            if ($ret == 0) {
                return (is_array($out) ? implode('', $out) : $out);
            }
        } elseif (version_compare($craftVersion, '4.0', '>=')) {
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
}
