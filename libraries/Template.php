<?php

namespace packages\email;

use packages\base\DB\DBObject;
use packages\base\Json;

class Template extends DBObject
{
    public const active = 1;
    public const deactive = 2;

    protected $dbTable = 'email_templates';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'name' => ['type' => 'text', 'required' => true],
        'lang' => ['type' => 'text', 'required' => true],
        'event' => ['type' => 'text'],
        'variables' => ['type' => 'text'],
        'render' => ['type' => 'text'],
        'subject' => ['type' => 'text', 'required' => true],
        'text' => ['type' => 'text'],
        'html' => ['type' => 'text'],
        'status' => ['type' => 'int', 'required' => true],
    ];
    private $recursionLevel = 0;
    protected $jsonFields = ['variables'];

    public function addObjectVariable($obj, $prefix = '')
    {
        if ($primaryKey = $obj->getPrimaryKey()) {
            $this->addVariable($prefix.'->'.$primaryKey);
        }
        foreach (array_keys($obj->getFields()) as $field) {
            $this->addVariable($prefix.'->'.$field);
        }
        foreach ($obj->getRelations() as $field => $relation) {
            if ('hasone' == strtolower($relation[0])) {
                $relation[1] = '\\'.$relation[1];
                $robj = new $relation[1]();
                if (is_a($robj, get_class($obj))) {
                    if ($this->recursionLevel > 0) {
                        continue;
                    }
                    ++$this->recursionLevel;
                }
                $this->addObjectVariable($robj, $prefix.'->'.$field);
            }
        }
    }

    public function addVariable($name)
    {
        if (false !== strpos($name, '\\') and is_subclass_of($name, DBObject::class)) {
            $obj = new $name();
            $name = explode('\\', $name);
            $name = $name[count($name) - 1];
            $this->addObjectVariable($obj, $name);

            return;
        }
        if (!$this->variables) {
            $this->variables = [];
        }
        if (!in_array($name, $this->variables)) {
            $variables = $this->variables;
            $variables[] = $name;
            $this->variables = $variables;
        }
    }

    public function render($params = [])
    {
        if (!$this->variables) {
            $this->variables = [];
        }
        if ($this->rander) {
            list($class, $method) = explode('@', $this->rander, 2);
            if (class_exists($class) and method_exists($class, $method)) {
                $obj = new $class($this);

                return $obj->$method($params);
            }
        } else {
            $keys = [];
            $values = [];
            $paramsKeys = array_keys($params);
            $paramsKeysLower = array_map('strtolower', $paramsKeys);
            foreach ($this->variables as $variable) {
                $variable = strtolower($variable);
                $keys[] = '['.$variable.']';
                $value = '';
                if (($key = array_search($variable, $paramsKeysLower)) !== false) {
                    $value = $params[$paramsKeys[$key]];
                } else {
                    $parts = explode('->', $variable);
                    $key = array_search($parts[0], $paramsKeysLower);
                    if (
                        false !== $key
                        and is_object($params[$paramsKeys[$key]])
                        and $params[$paramsKeys[$key]] instanceof DBObject
                    ) {
                        $obj = $params[$paramsKeys[$key]];
                        $len = count($parts);
                        for ($x = 1; $x < $len and is_object($obj) and $obj instanceof DBObject; ++$x) {
                            $part = $parts[$x];
                            $obj = $obj->$part;
                        }
                        if ($x == $len and !is_object($obj)) {
                            $value = $obj;
                        }
                    }
                }
                if (is_array($value) or is_object($value)) {
                    $value = Json\Encode($value);
                }
                $values[] = $value;
            }

            return [
                'text' => str_ireplace($keys, $values, $this->text),
                'html' => str_ireplace($keys, $values, $this->html),
                'subject' => str_ireplace($keys, $values, $this->subject),
            ];
        }
    }
}
