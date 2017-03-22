<?php

namespace Colis;

use Inutils\Storage\NormalizedArrayCollection;

class Headers extends NormalizedArrayCollection
{
    /** Convert header string value to array */
    public static function format(string $value)
    {
        $value = explode(',', $value);
        if (!is_array($value)) {
            $value = [$value];
        }
        return $value;
    }

    public static function unformat(array $value)
    {
        return implode(',', $value);
    }

    /** Set or Override a Value */
    public function set(string $key, $value)
    {
        $value = static::format($value);
        parent::set($key, $value);
    }

    public function getLine(string $key, $default = null)
    {
        return $this->has($key) ? static::unformat($this->get($key)) : $default;
    }

    public static function createFromServer(array $server)
    {
        $data = [];
        foreach ($server as $key => $value) {
            $key = strtoupper($key);
            if (Validator::isSpecialServerKey($key) || strpos($key, 'HTTP_') === 0) {
                if ($key !== 'HTTP_CONTENT_LENGTH') {
                    if (strpos($key, 'HTTP_') === 0) {
                        $key = substr($key, 5);
                    }
                    $data[$key] = $value;
                }
            }
        }

        return new static($data);
    }

    public function getContentType()
    {
        $type = $this->getLine('Content-Type');
        if ($type) {
            list($type) = explode(';', $type, 2);
        }
        return $type;
    }

    public function __toString()
    {
        $text = '';
        foreach ($this->getAll() as $name => $values) {
            $text .= $name . ": " . implode(", ", $values)."\n";
        }
        return $text;
    }
}
