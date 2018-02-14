<?php

namespace Dlindberg\Pasteboard;

class Pasteboard
{

    public static function set($value)
    {
        return self::action('pbcopy', $value);
    }

    public static function get()
    {
        return self::action('pbpaste');
    }

    public static function setArray($array, $options = array())
    {
        $config = self::configureArray($options);
        $initial = null;
        if ($config['reset']) {
            $initial = self::get();
        }
        foreach ($array as $value) {
            if (!self::setArrayValue($value,$config)) {
                return false;
            }
        }
        if ($config['reset']) {
            self::set($initial);
        }

        return true;
    }

    private static function action($action, $value = null)
    {
        $output = false;
        $do = proc_open(
            $action,
            array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("pipe", "w"),
            ),
            $pipes
        );
        if (is_resource($do)) {
            switch ($action) {
                case 'pbcopy':
                    if (isset($value)) {
                        fwrite($pipes[0], $value);
                    }
                    break;
                case 'pbpaste':
                    $output = stream_get_contents($pipes[1]);
                    break;
            }
            foreach ($pipes as $k => $v) {
                fclose($pipes[$k]);
            }
            $status = proc_close($do);
        }
        if (isset($status) && $status === 0 && $output === false) {
            $output = true;
        } elseif (isset($status) && $status === 0 && mb_strlen($output) === 0) {
            $output = false;
        }

        return $output;
    }

    private static function configureArray($initial)
    {
        $config = array(
            'reset' => false,
            'depth' => 0,
            'wait'  => 1,
        );
        if (isset($initial['reset'])) {
            $config['reset'] = $initial['reset'];
        }
        if (isset($initial['depth'])) {
            $config['depth'] = $initial['depth'];
        }
        if (isset($initial['wait'])) {
            $config['wait'] = $initial['wait'];
        }
        if (isset($initial['heartbeat'])) {
            $config['heartbeat'] = $initial['heartbeat'];
        } else {
            $config['heartbeat'] = function ($result) use ($config) {
                $return = false;
                if ($result) {
                    sleep($config['wait']);
                    $return = true;
                }

                return $return;
            };
        }

        return $config;
    }

    private static function setArrayValue($value, $config) {
        if (!is_array($value)) {
            $return = $config['heartbeat'](self::set($value));
        } elseif ($config['depth'] != 0) {
            $return = self::setArray($value, array('depth' => $config['depth'] - 1, 'heartbeat' => $config['heartbeat'],));
        } else {
            $return = true;
        }
        return $return;
    }
}
