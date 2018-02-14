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
        if (isset($options['depth'])) {
            $depth = $options['depth'];
        } else {
            $depth = 0;
        }
        if (isset($options['wait'])) {
            $wait = $options['wait'];
        } else {
            $wait = 1;
        }
        if (isset($options['heartbeat'])) {
            $heartbeat = $options['heartbeat'];
        } else {
            $heartbeat = function ($result) use ($wait) {
                $return = false;
                if ($result) {
                    sleep($wait);
                    $return = true;
                }

                return $return;
            };
        }
        $initial = null;
        if (isset($options['reset']) && $options['reset']) {
            $initial = self::get();
        }
        foreach ($array as $value) {
            if (!is_array($value)) {
                if (!$heartbeat(self::set($value))) {
                    return false;
                }
            } elseif ($depth != 0) {
                if (!self::setArray($value, array('depth' => $depth - 1, 'heartbeat' => $heartbeat,))) {
                    return false;
                }
            }
        }
        if (isset($options['reset']) && $options['reset']) {
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
}
