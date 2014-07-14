<?php

namespace Cti\Di;

/**
 * Class Inspector
 * @package Cti\Di
 */
class Inspector
{
    /**
     * @inject
     * @var Manager
     */
    public $manager;

    /**
     * @param $class
     * @param $method
     * @return array
     */
    public function getMethodArguments($class, $method)
    {
        if($this->getCache()->contains(__METHOD__, func_get_args())) {
            return $this->getCache()->get(__METHOD__, func_get_args());
        }
        $arguments = array();
        $reflection = Reflection::getReflectionMethod($class, $method);
        foreach ($reflection->getParameters() as $parameter) {
            if ($parameter->getClass()) {
                $arguments[] = new Reference($parameter->getClass()->getName());
            } else {
                $arguments[] = $parameter->getName();
            }
        }
        $this->getCache()->set(__METHOD__, func_get_args(), $arguments);
        return $arguments;
    }

    /**
     * @param $class
     * @param $method
     * @return int
     */
    public function getMethodRequiredCount($class, $method)
    {
        if($this->getCache()->contains(__METHOD__, func_get_args())) {
            return $this->getCache()->get(__METHOD__, func_get_args());
        }
        $requiredCount = 0;
        $reflection = Reflection::getReflectionMethod($class, $method);
        foreach ($reflection->getParameters() as $parameter) {
            if (!$parameter->isDefaultValueAvailable()) {
                $requiredCount++;
            }
        }
        $this->getCache()->set(__METHOD__, func_get_args(), $requiredCount);
        return $requiredCount;
    }

    /**
     * get class properties hash
     * if property value is true, property is public
     * @param $class
     * @return array
     */
    public function getClassProperties($class)
    {
        if($this->getCache()->contains(__METHOD__, func_get_args())) {
            return $this->getCache()->get(__METHOD__, func_get_args());
        }
        $map = array();
        $reflectionClass = Reflection::getReflectionClass($class);
        foreach ($reflectionClass->getProperties() as $property) {
            $map[$property->getName()] = $property->isPublic();
        }
        $this->getCache()->set(__METHOD__, func_get_args(), $map);
        return $map;
    }

    /**
     * @param $class
     * @return array
     */
    public function getPublicMethods($class)
    {
        if($this->getCache()->contains(__METHOD__, func_get_args())) {
            return $this->getCache()->get(__METHOD__, func_get_args());
        }
        $result = array();
        foreach(Reflection::getReflectionClass($class)->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $result[] = $method->getName();
        }
        $this->getCache()->set(__METHOD__, func_get_args(), $result);
        return $result;
    }


    /**
     * @param $class
     * @return array
     */
    public function getClassInjection($class)
    {
        if($this->getCache()->contains(__METHOD__, func_get_args())) {
            return $this->getCache()->get(__METHOD__, func_get_args());
        }
        $injection = array();
        $reflectionClass = Reflection::getReflectionClass($class);
        foreach ($reflectionClass->getProperties() as $property) {
            if (stristr($property->getDocComment(), '@inject') || stristr($property->getDocComment(), '@new')) {
                foreach (explode("\n", $property->getDocComment()) as $line) {
                    if (!stristr($line, '@var')) {
                        continue;
                    }
                    foreach (explode(' ', substr($line, stripos($line, '@var') + 4)) as $item) {
                        if (strlen($item) > 0) {
                            $global = false;
                            if ($item[0] == '\\') {
                                $global = true;
                                $item = substr($item, 1);
                            }
                            $injected_class = trim(str_replace("\r", '', $item));

                            if (!$global) {

                                $aliases = array();

                                foreach(file($property->getDeclaringClass()->getFileName()) as $line) {
                                    if(strpos($line, 'use ') === 0) {
                                        $line = substr($line, 0, strpos($line, ';'));
                                        $chain = array_filter(explode(' ', $line), 'strlen');
                                        if(strpos($line, ' as ')) {
                                            $destination = $chain[1];
                                            $alias = $chain[3];
                                        } else {
                                            $destination = $chain[1];
                                            list($alias) = array_reverse(explode("\\", $chain[1]));
                                        }
                                        $aliases[$alias] = $destination;
                                    }
                                    if(strpos($line, 'class ') === 0) {
                                        break;
                                    }
                                }
                                if (isset($aliases[$injected_class])) {
                                    // imported with use statement
                                    $injected_class = $aliases[$injected_class];

                                } elseif(!strstr($injected_class, '\\')) {
                                    $injected_class = $reflectionClass->getNamespaceName() . '\\' . $injected_class;
                                } else {
                                    list($ns) = explode('\\', $injected_class);
                                    if(isset($aliases[$ns])) {
                                        $injected_class = $aliases[$ns] . substr($injected_class, strlen($ns));
                                    }
                                }
                            }
                            $injection[$property->getName()] = array(
                                'class' => $injected_class,
                                'new' => stristr($property->getDocComment(), '@new')
                            );
                            break;
                        }
                    }
                }
            }
        }
        $this->getCache()->set(__METHOD__, func_get_args(), $injection);
        return $injection;
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->getManager()->getCache();
    }
}