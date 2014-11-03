<?php
/**
 * requery
 * @author dong <ddliuhb@gmail.com>
 * @license MIT
 */

namespace ddliu\requery;

/**
 * The query context
 */
class Context implements \ArrayAccess {

    protected $content;

    /**
     * Constructor
     * @param string|array $content
     */
    public function __construct($content) {
        if (is_string($content)) {
            $content = array($content);
        }

        $this->content = $content;
    }

    /**
     * Get an empty context
     * @return Context
     */
    public static function getEmptyContext() {
        static $context;
        if (null === $context) {
            $context = new self(null);
        }

        return $context;
    }

    /**
     * Extract result parts of current query.  
     * @param  mixed $parts
     * @return mixed
     */
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

    /**
     * Find with regexp in current context
     * @param  string $re
     * @param  callable $filter
     * @return Context
     */
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

    /**
     * Find with assertion.
     * @param  string $re
     * @param  callable $filter
     * @return Context
     * @throws QueryException If nothing found.
     */
    public function mustFind($re, $filter = null) {
        $result = $this->find($re, $filter);
        if ($result->isEmpty()) {
            throw new QueryException('No match found for regexp: '.$re);
        }

        return $result;
    }

    /**
     * Find all matching data in the context.
     * @param  string $re
     * @param  callbale $filter
     * @return ContextCollection
     */
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

    /**
     * findAll with assertion.
     * @param  string $re
     * @param  callable $filter
     * @return ContextCollection
     * @throws QueryException If nothing found.
     */
    public function mustFindAll($re, $filter = null) {
        $result = $this->findAll($re, $filter);
        if ($result->count() == 0) {
            throw new QueryException('No match found for regexp: '.$re);
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
     * Get text of current context
     * @return string
     */
    public function toString() {
        if (is_array($this->content) && isset($this->content[0])) {
            return $this->content[0];
        }

        return '';
    }

    public function __toString() {
        return $this->toString();
    }

    /**
     * Check for empty context
     * @return boolean
     */
    public function isEmpty() {
        return $this->content === null;
    }

    /**
     * Implements \ArrayAccess
     */
    public function offsetGet($offset) {
        return (is_array($this->content) && isset($this->content[$offset]))?new self($this->content[$offset]):self::getEmptyContext();
    }

    /**
     * Implements \ArrayAccess
     */
    public function offsetExists($offset) {
        return is_array($this->content) && isset($this->content[$offset]);
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