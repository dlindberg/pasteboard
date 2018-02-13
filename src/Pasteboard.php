<?php

namespace Dlindberg\Pasteboard;

class Pasteboard
{

    public static function set($value)
    {
        $r = false;
        $set = proc_open(
            "pbcopy",
            array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("pipe", "w"),
            ),
            $pipes
        );
        if (is_resource($set)) {
            fwrite($pipes[0], $value);
            fclose($pipes[0]);
            $return = proc_close($set);
            if ($return == 0) {
                $r = true;
            }
        }

        return $r;

    }

    public static function get()
    {
        $return = null;
        $output = false;
        $get = proc_open(
            "pbpaste",
            array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("pipe", "w"),
            ),
            $pipes
        );
        if (is_resource($get)) {
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $return = proc_close($get);
        }

        if (isset($return, $output) && $return == 0 && mb_strlen($output) > 0) {
            return $output;
        }

        return false;

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
            $heartbeat = function ($result) use ($wait)
            {
                $r = false;
                if ($result) {
                    sleep($wait);
                    $r = true;
                }
                return $r;
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

}
