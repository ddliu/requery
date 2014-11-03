<?php
namespace ddliu\requery;

class ContextCollection implements \ArrayAccess, \Iterator {
    protected $data;
    public function __construct($data) {
        $this->data = $data;
    }

    public function count() {
        return count($this->data);
    }

    public function find($re, $filter = null) {
        $result = null;
        $this->each(function($context) use ($re, $filter, &$result) {
            $r = $context->find($re, $filter);
            if (!$r->isEmpty()) {
                $result = $r;
                return false;
            }
        });

        return $result?:Context::getEmptyContext();
    }

    public function mustFind($re, $filter = null) {
        $result = $this->find($re, $filter);
        if ($result->isEmpty()) {
            throw new QueryException('No match for regexp: '.$re);
        }

        return $result;
    }

    public function findAll($re, $filter = null) {
        $result = array();
        $this->each(function($context) use ($re, $filter, &$result) {
            $context->findAll($re, $filter)->each(function($context) use (&$result) {
                $result[] = $context;
            });
        });

        return new self($result);
    }

    public function mustFindAll($re, $filter = null) {
        $result = $this->findAll($re, $filter);
        if ($result->count() == 0) {
            throw new QueryException('No match for regexp: '.$re);
        }

        return $result;
    }

    public function then($cb) {
        $cb($this);
        return $this;
    }

    public function extract($parts = null) {
        if ($parts === null) {
            return $this->data;
        }

        if (is_array($parts)) {
            $result = array();

            foreach ($this->data as $match) {
                $row = array();
                foreach ($parts as $key) {
                    if (isset($match[$key])) {
                        $row[$key] = isset($match[$key])?$match[$key]:null;
                    }
                }

                $result[] = $row;
            }

            return $result;
        }

        $result = array();
        foreach ($this->data as $match) {
            $result[] = isset($match[$parts])?$match[$parts]:null;
        }

        return $result;
    }

    public function each($cb) {
        foreach ($this->data as $context) {
            if (!is_object($context)) {
                $context = new Context($context);
            }

            if (false === $cb($context)) {
                break;
            }
        }

        return $this;
    }

    /**
     * Implements \Iterator
     */
    public function rewind() {
        rewind($this->data);
    }

    /**
     * Implements \Iterator
     */
    public function current() {
        return current($this->data);
    }

    /**
     * Implements \Iterator
     */
    public function key() {
        return key($this->data);
    }

    /**
     * Implements \Iterator
     */
    public function next() {
        next($this->data);
    }

    /**
     * Implements \Iterator
     */
    public function valid() {
        return key($this->data) !== null;
    }

    /**
     * Implements \ArrayAccess
     */
    public function offsetGet($offset) {
        return isset($this->data[$offset])?$this->data[$offset]:null;
    }

    /**
     * Implements \ArrayAccess
     */
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    /**
     * Implements \ArrayAccess
     */
    public function offsetSet($offset, $value) {}

    /**
     * Implements \ArrayAccess
     */
    public function offsetUnset($offset) {}
}