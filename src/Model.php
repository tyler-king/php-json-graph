<?php

namespace JSONGraph;

/**
 * Base model class that accepts json array to graph and array of constants to include 
 */

class Model
{
    private array $json;
    private array $constants = [];
    private array $found_values = [];
    private $full_graph;

    private const TYPE_KEY = '$type';
    private const VALUE_KEY = 'value';

    /**
     * Constructor
     *
     * @param array $json
     * @param array $constants
     */

    final public function __construct(array $json, array $constants = [])
    {
        $this->json = $json;
        $this->constants = array_replace(getenv(), $constants);
    }


    /**
     * Use to get the fullfilled json array
     *
     * @param array $keys
     * @return mixed
     */

    final public function get(array $keys = [])
    {
        $return = &$this->json;
        if (isset($this->full_graph)) {
            if (count($keys) == 0) {
                return $this->full_graph;
            } else {
                foreach ($keys as $key) {
                    $return = &$return[$key];
                }
            }
        } else {
            foreach ($keys as $key) {
                $key = $this->drill($key);
                $return = &$return[$key];
                $return = $this->drill($return);
            }
            if (is_array($return)) {
                $return = $this->recurse($return);
            }
            if (count($keys) == 0) {
                $this->full_graph = $return;
            }
        }
        return $return;
    }


    /**
     * Recursive check for types
     *
     * @param array $return
     * @return void
     */

    final private function recurse(array $return)
    {
        foreach ($return as &$r) {
            $r = $this->drill($r);
            if (is_array($r)) {
                $r = $this->recurse($r);
            }
        }
        return $return;
    }


    /**
     * Analyse a type
     *
     * @param mixed $return
     * @return mixed
     */

    private function drill($return)
    {
        $value = $return[self::VALUE_KEY] ?? null;
        switch ($return[self::TYPE_KEY] ?? null) {
            case 'ref':
                $ret = $this->found_values[serialize($value)] ?? $this->drill($this->get($value));
                $this->found_values[serialize($value)] = $ret;
                break;
            case 'env':
                $value = $this->drill($value);
                $ret = $this->constants[$value];
                break;
            case 'concat':
                foreach ($value as &$v) {
                    $v = $this->drill($v);
                }
                $ret = implode("", $value);
                break;
            case 'merge':
                foreach ($value as &$v) {
                    $v = $this->drill($v);
                }
                $ret = array_merge(...$value);
                break;
            case 'replace':
                foreach ($value as $i => &$v) {
                    $v = $this->drill($v);
                }
                $ret = array_replace(...$value);
                break;
            case 'set':
                $value[0] = $this->drill($value[0]);
                foreach ($value[1] as $v => $val) {
                    foreach ($val as $key => $v) {
                        $value[0] = $this->array_deep_key_set($value[0], explode(".", $key), $v);
                    }
                }
                $ret = $value[0];
                break;
            case 'atom':
                $ret = $value;
                break;
            default:
                //invalid or no $type
                $ret = $return;
        }
        return $ret;
    }


    /**
     * Helped function to set a deep key value
     *
     * @param array $array
     * @param array $path
     * @param mixed $value
     * @return array
     */

    private function array_deep_key_set(array $array, array $path = [], $value = null): array
    {
        $arr = &$array;
        foreach ($path as $key) {
            if (!is_array($arr)) {
                $arr = [$key => null];
            }
            $arr = &$arr[$key];
        }
        $arr = $value;
        return $array;
    }
}
