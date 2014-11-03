<?php
namespace ddliu\requery;

class Context implements \ArrayAccess {

    protected $content;

    public function __construct($content) {
        $this->content = $content;
    }

    public static function getEmptyContext() {
        static $context;
        if (null === $context) {
            $context = new self(null);
        }

        return $context;
    }

    public function extract($re = null) {
        if ($re === null) {
            return $this->getMatch();
        }


    }

    public function extractAll($re) {
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
                $result[] = $context;
            }
        }

        return new ContextCollection($result);
    }

    public function then($cb) {
        $cb($this);

        return $this;
    }

    public function getMatch() {
        return $this->content;
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