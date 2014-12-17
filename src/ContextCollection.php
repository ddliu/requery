<?php
/**
 * requery
 * @author dong <ddliuhb@gmail.com>
 * @license MIT
 */

namespace ddliu\requery;

/**
 * Context collection
 */
class ContextCollection implements \ArrayAccess, \Iterator {
    protected $data;

    /**
     * Constructor, you don't have to call the constructor directly, 
     * use Context::__construct instead
     * @param array $data
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Get the collection size
     * @return int
     */
    public function count() {
        return count($this->data);
    }

    /**
     * Find the first matching context in the collection
     * @param  string $re
     * @param  callable $filter
     * @return Context
     */
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

    /**
     * Find with assertion
     * @param  string $re
     * @param  callable $filter
     * @return Context
     * @throws QueryException If nothing found.
     */
    public function mustFind($re, $filter = null) {
        $result = $this->find($re, $filter);
        if ($result->isEmpty()) {
            throw new QueryException('No match for regexp: '.$re);
        }

        return $result;
    }

    /**
     * Find all matching contexts in the collection
     * @param  string $re
     * @param  callable $filter
     * @return ContextCollection
     */
    public function findAll($re, $filter = null) {
        $result = array();
        $this->each(function($context) use ($re, $filter, &$result) {
            $context->findAll($re, $filter)->each(
                function($context) use (&$result) {
                    $result[] = $context;
                }
            );
        });

        return new self($result);
    }

    /**
     * Find all with assertion
     * @param  string $re
     * @param  callable $filter
     * @return ContextCollection
     * @throws QueryException If nothing found.
     */
    public function mustFindAll($re, $filter = null) {
        $result = $this->findAll($re, $filter);
        if ($result->count() == 0) {
            throw new QueryException('No match for regexp: '.$re);
        }

        return $result;
    }

    /**
     * Call a function in current context.
     * @param  callable $cb
     * @return Context
     */
    public function then($cb) {
        $cb($this);
        return $this;
    }

    /**
     * Extract result parts of the collection.  
     * @param  mixed $parts
     * @return mixed
     */
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

    /**
     * Walk through the collection
     * @param  callable $cb
     * @return ContextCollection
     */
    public function each($cb) {
        foreach ($this->data as $context) {
            $context = new Context($context);

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
        if (!isset($this->data[$offset])) {
            return null;
        }

        $context = $this->data[$offset];
        return new Context($context);
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