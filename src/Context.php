<?php
namespace ddliu\requery;

class Context implements \ArrayAccess {

    protected $content;

    public function __construct($content) {
        if (is_string($content)) {
            $content = array($content);
        }

        $this->content = $content;
    }

    public static function getEmptyContext() {
        static $context;
        if (null === $context) {
            $context = new self(null);
        }

        return $context;
    }

    public function extract($parts = null) {
        if ($this->isEmpty()) {
            return false;
        }

        if ($parts === null) {
            return $this->content;
        }

        if (is_array($parts)) {
            $result = array();
            foreach ($parts as $key) {
                if (isset($this->content[$key])) {
                    $result[$key] = $this->content[$key];
                }
            }

            return $result;
        }

        return isset($this->content[$parts])?$this->content[$parts]:false;
    }

    public function find($re, $filter = null) {
        if ($this->isEmpty()) {
            return self::getEmptyContext();
        }

        $result = null;

        if ($filter) {
            $this->findAll($re)->each(function($context) use ($filter, &$result) {
                if ($filter($context)) {
                    $result = $context;
                    return false;
                }
            });

            return $result?:self::getEmptyContext();
        }

        if (!preg_match($re, $this->toString(), $match)) {
            return self::getEmptyContext();
        }

        return new self($match);
    }

    public function mustFind($re, $filter = null) {
        $result = $this->find($re, $filter);
        if ($result->isEmpty()) {
            throw new QueryException('No match found for regexp: '.$re);
        }

        return $result;
    }

    public function findAll($re, $filter = null) {
        if ($this->isEmpty()) {
            return self::getEmptyContext();
        }

        if (!preg_match_all($re, $this->toString(), $matches, PREG_SET_ORDER)) {
            $matches = array();
        }

        $result = array();
        foreach ($matches as $match) {
            $context = new self($match);
            if (!$filter || $filter($context)) {
                $result[] = $match;
            }
        }

        return new ContextCollection($result);
    }

    public function mustFindAll($re, $filter = null) {
        $result = $this->findAll($re, $filter);
        if ($result->count() == 0) {
            throw new QueryException('No match found for regexp: '.$re);
        }

        return $result;
    }

    public function then($cb) {
        $cb($this);

        return $this;
    }

    public function toString() {
        if (is_string($this->content)) {
            return $this->content;
        }

        if (is_array($this->content) && isset($this->content[0])) {
            return $this->content[0];
        }

        return '';
    }

    public function __toString() {
        return $this->toString();
    }

    public function isEmpty() {
        return $this->content === null;
    }

    public function offsetGet($offset) {
        return (is_array($this->content) && isset($this->content[$offset]))?new self($this->content[$offset]):self::getEmptyContext();
    }

    public function offsetExists($offset) {
        return is_array($this->content) && isset($this->content[$offset]);
    }

    public function offsetSet($offset, $value) {}

    public function offsetUnset($offset) {}

}