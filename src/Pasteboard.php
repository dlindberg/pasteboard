<?php

namespace Dlindberg\Pasteboard;

class Pasteboard
{
    private static $clipboard = "foo";
    private static $process;
    private static $pipes;

    /**
     * @param $value
     * @return bool|string
     */
    public static function set($value)
    {
        return self::action('pbcopy', $value);
    }

    /**
     * @return bool|string
     */
    public static function get()
    {
        return self::action('pbpaste');
    }

    /**
     * @param       $array
     * @param array $options
     * @return bool
     */
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

    /**
     * @param string $action
     * @param string $value
     * @return bool|string
     */
    private static function action($action, $value = null)
    {
        $output = false;
        if (self::openProcess($action)) {
            $output = self::doAction($action, $value);
            self::closeProcess();
        }

        return $output;
    }

    /**
     * @param $process
     * @return bool
     */
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

    /**
     * @return int
     */
    private static function closeProcess()
    {
        foreach (self::$pipes as $k => $v) {
            fclose(self::$pipes[$k]);
        }

        return proc_close(self::$process);
    }

    /**
     * @param $action
     * @param $value
     * @return bool|string
     */
    private static function doAction($action, $value)
    {
        $output = true;
        switch ($action) {
            case 'pbcopy':
                fwrite(self::$pipes[0], $value);
                break;
            case 'pbpaste':
                $output = stream_get_contents(self::$pipes[1]);
                if (mb_strlen($output) < 1) {
                    $output = false;
                }
                break;
        }

        return $output;
    }

    /**
     * @param string $do
     * @param bool   $test
     */
    private static function storedClipboard($do, $test = true)
    {
        if ($test) {
            self::$clipboard = self::action($do, self::$clipboard);
        }
    }

    /**
     * @param $options
     * @return mixed
     */
    private static function configureArray($options)
    {
        $config = array();
        $config['reset'] = self::setOption('reset', false, $options);
        $config['depth'] = self::setOption('depth', 0, $options);
        $config['wait'] = self::setOption('wait', 1, $options);
        $config['heartbeat'] = self::setOption('heartbeat', self::defaultHeartbeat($config['wait']), $options);

        return $config;
    }

    /**
     * @param $wait
     * @return \Closure
     */
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

    /**
     * @param string $name
     * @param        $default
     * @param        $requested
     * @return mixed
     */
    private static function setOption($name, $default, $requested)
    {
        if (isset($requested[$name])) {
            return $requested[$name];
        } else {
            return $default;
        }
    }

    /**
     * @param $value
     * @param $config
     * @return bool
     */
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
