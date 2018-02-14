<?php

namespace Dlindberg\Pasteboard;

class Pasteboard
{
    private static $clipboard = "foo";
    private static $process;
    private static $pipes;

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
        self::storedClipboard('pbpaste', $config['reset']);
        foreach ($array as $value) {
            if (!self::setArrayValue($value, $config)) {
                return false;
            }
        }
        self::storedClipboard('pbcopy', $config['reset']);

        return true;
    }

    private static function action($action, $value = null)
    {
        $output = false;
        $status = null;
        if (self::openProcess($action)) {
            switch ($action) {
                case 'pbcopy':
                    if (isset($value)) {
                        fwrite(self::$pipes[0], $value);
                    }
                    break;
                case 'pbpaste':
                    $output = stream_get_contents(self::$pipes[1]);
                    break;
            }
            $status = self::closeProcess();
        }
        if ($status === 0 && $output === false) {
            $output = true;
        } elseif ($status === 0 && mb_strlen($output) === 0) {
            $output = false;
        }

        return $output;
    }

    private static function openProcess($process)
    {
        $return = false;
        self::$process = proc_open(
            $process,
            array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("pipe", "w"),
            ),
            self::$pipes
        );
        if (is_resource(self::$process)) {
            $return = true;
        }

        return $return;
    }

    private static function closeProcess()
    {
        foreach (self::$pipes as $k => $v) {
            fclose(self::$pipes[$k]);
        }

        return proc_close(self::$process);
    }

    private static function storedClipboard($do, $test = true)
    {
        if ($test) {
            self::$clipboard = self::action($do, self::$clipboard);
        }
    }

    private static function configureArray($options)
    {
        $config['reset'] = self::setOption('reset', false, $options);
        $config['depth'] = self::setOption('depth', 0, $options);
        $config['wait'] = self::setOption('wait', 1, $options);
        $config['heartbeat'] = self::setOption('heartbeat', self::defaultHeartbeat($config['wait']), $options);

        return $config;
    }

    private static function defaultHeartbeat($wait)
    {
        return function ($result) use ($wait) {
            $return = false;
            if ($result) {
                sleep($wait);
                $return = true;
            }

            return $return;
        };
    }

    private static function setOption($name, $default, $requested)
    {
        if (isset($requested[$name])) {
            return $requested[$name];
        } else {
            return $default;
        }
    }

    private static function setArrayValue($value, $config)
    {
        if (!is_array($value)) {
            $return = $config['heartbeat'](self::set($value));
        } elseif ($config['depth'] != 0) {
            $return = self::setArray(
                $value,
                array('depth' => $config['depth'] - 1, 'heartbeat' => $config['heartbeat'],)
            );
        } else {
            $return = true;
        }

        return $return;
    }
}
