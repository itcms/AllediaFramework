<?php
/**
 * @package   AllediaFramework
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of AllediaFramework.
 *
 * AllediaFramework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * AllediaFramework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AllediaFramework.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Alledia\Framework;

use Alledia\Framework\Joomla\Extension\Helper as ExtensionHelper;
use ReflectionException;
use ReflectionMethod;

defined('_JEXEC') or die();

abstract class Helper
{
    /**
     * Return an array of Alledia extensions
     *
     * @todo Move this method for the class Alledia\Framework\Joomla\Extension\Helper, but keep as deprecated
     *
     * @param  string $license
     *
     * @return object[]
     */
    public static function getAllediaExtensions($license = '')
    {
        // Get the extensions ids
        $db    = \JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select(
                array(
                    $db->quoteName('extension_id'),
                    $db->quoteName('type'),
                    $db->quoteName('element'),
                    $db->quoteName('folder')
                )
            )
            ->from('#__extensions')
            ->where(
                array(
                    $db->quoteName('custom_data') . " LIKE '%\"author\":\"Alledia\"%'",
                    $db->quoteName('custom_data') . " LIKE '%\"author\":\"OSTraining\"%'",
                    $db->quoteName('custom_data') . " LIKE '%\"author\":\"Joomlashack\"%'",
                    $db->quoteName('manifest_cache') . " LIKE '%\"author\":\"Alledia\"%'",
                    $db->quoteName('manifest_cache') . " LIKE '%\"author\":\"OSTraining\"%'",
                    $db->quoteName('manifest_cache') . " LIKE '%\"author\":\"Joomlashack\"%'"
                ),
                'OR'
            )
            ->group($db->quoteName('extension_id'));

        $rows = $db->setQuery($query)->loadObjectList();

        $extensions = array();

        foreach ($rows as $row) {
            $fullElement = $row->element;

            // Fix the element for plugins
            if ($row->type === 'plugin') {
                $fullElement = ExtensionHelper::getFullElementFromInfo($row->type, $row->element, $row->folder);
            }

            $extensionInfo = ExtensionHelper::getExtensionInfoFromElement($fullElement);
            $extension     = new Joomla\Extension\Licensed($extensionInfo['namespace'], $row->type, $row->folder);

            if (!empty($license)) {
                if ($license === 'pro' && !$extension->isPro()) {
                    continue;

                } elseif ($license === 'free' && $extension->isPro()) {
                    continue;
                }
            }

            $extensions[$row->extension_id] = $extension;
        }

        return $extensions;
    }

    /**
     * @return string
     */
    public static function getJoomlaVersionCssClass()
    {
        return 'joomla' . (version_compare(JVERSION, '3.0', 'lt') ? '25' : '3x');
    }

    /**
     * @param string $className
     * @param string $methodName
     * @param array  $params
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function callMethod($className, $methodName, $params = array())
    {
        $result = true;

        if (method_exists($className, $methodName)) {
            $method = new ReflectionMethod($className, $methodName);

            if ($method->isStatic()) {
                $result = call_user_func_array("{$className}::{$methodName}", $params);

            } else {
                // Check if we have a singleton class
                if (method_exists($className, 'getInstance')) {
                    $instance = $className::getInstance();

                } else {
                    $instance = new $className;
                }

                $result = call_user_func_array(array($instance, $methodName), $params);
            }
        }

        return $result;
    }
}
