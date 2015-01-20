<?php
/**
 * @package   AllediaInstaller
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework\Joomla\Extension;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use stdClass;


/**
 * Generic extension helper class
 */
class Helper
{
    public static function getExtensionInfoFromElement($element)
    {
        $result = array(
            'type'      => null,
            'name'      => null,
            'group'     => null,
            'prefix'    => null,
            'namespace' => null
        );

        $types = array(
            'com' => 'component',
            'plg' => 'plugin',
            'mod' => 'module',
            'lib' => 'library',
            'tpl' => 'template'
        );

        $element = explode('_', $element);

        $result['prefix'] = $element[0];

        if (array_key_exists($result['prefix'], $types)) {
            $result['type']  = $types[$result['prefix']];

            if ($result['prefix'] === 'plg') {
                $result['group'] = $element[1];
                $result['name']  = $element[2];
            } else {
                $result['name']  = $element[1];
                $result['group'] = null;
            }
        }

        $result['namespace'] = preg_replace_callback(
            '/^(os[a-z])(.*)/i',
            function($matches) {
                return strtoupper($matches[1]) . $matches[2];
            },
            $result['name']
        );

        return $result;
    }

    public static function loadLibrary($element)
    {
        $info = static::getExtensionInfoFromElement($element);

        if (!empty($info['type']) && !empty($info['namespace'])) {
            $extension = Factory::getExtension($info['namespace'], $info['type'], $info['group']);
            $extension->loadLibrary();
        }
    }
}
