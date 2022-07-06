<?php

/**
 * @package Duplicator
 */

namespace Duplicator\Libs\Snap;

use Duplicator\Libs\Snap\JsonSerialize\JsonSerialize;
use Exception;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

class SnapObjUtils
{
    const DEFAULY_OBJ_COPY_OPTIONS = [
        'addProps' => false,
        'addPrivate' => true,
        'keepDestObjects' => true,
        'skip' => []
    ];

    /**
     * Copy object to another
     *
     * @param array|object  $source  source object or array key = prop name, val prop val
     * @param string|object $dest    destination object or class name
     * @param array         $options copy options [
     *                               addProps => false, bool if true it adds the properties that exist in the source but not in the destination object
     *                               addPrivate => true, if false skip private methods
     *                               keepDestObjects => true if true keep destination objects, or overwrite
     *                               skip => [] list of props to skip,
     *                               ]
     *
     * @return object
     */
    public static function objCopy($source, $dest, $options = array())
    {
        $options = array_merge(self::DEFAULY_OBJ_COPY_OPTIONS, $options);

        if (is_string($dest)) {
            if (!class_exists($dest)) {
                throw new Exception('Classs ' . $dest . ' don\'t exists');
            }

            $classReflect = new ReflectionClass($dest);
            $dest         = $classReflect->newInstanceWithoutConstructor();
        }

        if ($source == null || is_scalar($source)) {
            // if is scalar do nothing
            return $dest;
        } elseif (is_array($source)) {
            $list = $source;
        } else {
            $rS   = new ReflectionObject($source);
            $list = $rS->getProperties();
        }
        $rD = new ReflectionObject($dest);

        foreach ($list as $key => $sProp) {
            if ($sProp instanceof ReflectionProperty) {
                $propName = $sProp->getName();
                $sProp->setAccessible(true);
                $propVal = $sProp->getValue($source);
            } else {
                $propName = $key;
                $propVal  = $sProp;
            }

            if (in_array($propName, $options['skip'])) {
                continue;
            }
            if (!$rD->hasProperty($propName)) {
                if (!$options['addProps']) {
                    continue;
                }
                $dest->{$propName} = null;
            }
            $dProp = $rD->getProperty($propName);
            if ($dProp->isStatic()) {
                continue;
            }
            if (!$options['addPrivate'] && !$dProp->isPublic()) {
                continue;
            }

            $dProp->setAccessible(true);
            $destVal = $dProp->getValue($dest);
            if ($options['keepDestObjects'] && is_object($destVal)) {
                self::objCopy($propVal, $destVal);
            } else {
                $dProp->setValue($dest, $propVal);
            }
        }
        return $dest;
    }

    /**
     * Copies an array to an objects array
     *
     * @param array  $sourceArray The source array
     * @param array  $destArray   The destination array in the class
     * @param object $className   The class name where the $destArray exists
     * @param array  $options     copy options [
     *                            addProps => false, bool if true it adds the properties that exist in the source but not in the destination object
     *                            addPrivate => true, if false skip private methods
     *                            skip => [] list of props to skip
     *                            ]
     *
     * @return null
     */
    public static function objectArrayCopy($sourceArray, &$destArray, $className, $options = array())
    {
        foreach ($sourceArray as $source) {
            $dest = new $className();
            self::objCopy($source, $dest, $options);
            array_push($destArray, $dest);
        }
    }

    /**
     *
     * @param object|array $source source data
     * @param object       $dest   destination object
     *
     * @return object return destination object
     */
    public static function recursiveObjectCopyToArray($source, $dest)
    {
        $sArray = json_decode(JsonSerialize::serialize($source, JsonSerialize::JSON_SERIALIZE_SKIP_CLASS_NAME), true);
        self::objCopy($sArray, $dest, ['keepDestObjects' => false]);
        return $dest;
    }
}
