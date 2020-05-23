<?php

/**
 * BinaryTree
 *
 * @author Petr Bednář (I18105) 
 */

/**
 * 
 * 
 * INTERFACE
 * 
 * 
 */
interface IStack {

    public function push($value): void;

    public function pop();

    public function clear(): void;

    public function size(): int;

    public function isEmpty(): bool;
}

interface IBinaryTree {

    public function add(string $key, $value): void;

    public function remove(string $key): void;

    public function search(string $key);

    public function destroy(): void;

    /**
     * 
     * Gives count of binary tree nodes.
     * 
     * @return int
     */
    public function size(): int;

    /**
     * Creates in order iterator for a current binary tree.
     * 
     * @return InOrderIterator
     */
    public function iterator(): \InOrderIterator;
}

/**
 * 
 * 
 * IMPLEMENTATION
 * 
 * 
 */
class Stack implements IStack {

    private $array;

    function __construct() {
        $this->array = array();
    }

    public function clear(): void {
        $this->array = array();
    }

    public function pop() {
        return array_pop($this->array);
    }

    public function push($value): void {

        if (is_null($value)) {
            return;
        }

        array_push($this->array, $value);
    }

    public function size(): int {
        return count(array_filter($this->array));
    }

    public function isEmpty(): bool {
        return $this->size() == 0 ? true : false;
    }

}

class InOrderIterator implements Iterator {

    private $root;
    private $stack;
    private $tracer;
    private $current;
    private $position;

    function __construct($root) {
        $this->stack = new Stack();
        $this->tracer = new Stack();
        $this->root = $root;
        $this->rewind();
    }

    public function current() {
        return $this->current;
    }

    public function key(): int {
        return $this->position;
    }

    public function next(): void {
        $this->current = $this->stack->pop();

        if (!$this->tracer->isEmpty()) {
            $ignored = $this->tracer->pop();

            if ($this->current == $ignored) {
                return;
            } else {
                $this->tracer->push($ignored);
            }
        }

        if ($this->current->getLeft() != null) {

            $this->tracer->push($this->current);
            $this->stack->push($this->current->getRight());
            $this->stack->push($this->current);
            $this->stack->push($this->current->getLeft());

            $this->next();
            return;
        }

        $this->stack->push($this->current->getRight());
    }

    public function rewind(): void {
        $this->stack->clear();
        $this->tracer->clear();
        $this->current = null;
        $this->position = 0;
        $this->stack->push($this->root);
    }

    public function valid(): bool {
        return $this->stack->size() > 0 ? true : false;
    }

}

class Node {

    private $key;
    private $value;
    private $left;
    private $right;

    function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }

    function getKey() {
        return $this->key;
    }

    function getValue() {
        return $this->value;
    }

    function getLeft() {
        return $this->left;
    }

    function getRight() {
        return $this->right;
    }

    function setKey($key) {
        $this->key = $key;
    }

    function setValue($value) {
        $this->value = $value;
    }

    function setLeft($left) {
        $this->left = $left;
    }

    function setRight($right) {
        $this->right = $right;
    }

}

class BinaryTree implements IBinaryTree {

    private $root;
    private $error;

    function __construct() {
        $this->root = new Node("m", "m");
    }

    private function recursiveAdd(string $key, $value, Node $current = null) {

        if ($current == null) {
            return new Node($key, $value);
        }

        $cmp = strcmp($key, $current->getKey());
        if ($cmp < 0) {
            $current->setLeft($this->recursiveAdd($key, $value, $current->getLeft()));
        } else if ($cmp > 0) {
            $current->setRight($this->recursiveAdd($key, $value, $current->getRight()));
        } else {
            $this->error = new Exception("Key Used");
        }

        return $current;
    }

    public function add(string $key, $value): void {
        $this->root = $this->recursiveAdd($key, $value, $this->root);
        if ($this->error != null) {
            $error = $this->error;
            $this->error = null;
            throw $error;
        }
    }

    private function findLowestLeft(Node $current = null) {
        return $current->setLeft() == null ? $current : $this->findLowestLeft($current->getLeft());
    }

    private function recursiveRemove(string $key, Node $current = null) {

        if ($current == null) {
            return null;
        }

        $cmp = strcmp($key, $current->getKey());
        if ($cmp == 0) {

            if ($current->getLeft() == null && $current->getRight() == null) {
                return null;
            }
            if ($current->getRight() == null) {
                return $current->getLeft();
            }

            if ($current->getLeft() == null) {
                return $current->getRight();
            }

            $lowestLeft = $this->findLowestLeft($current->getRight());
            $current->setKey($lowestLeft->getKey());
            $current->setValue($lowestLeft->getValue());
            $current->setRight($this->recursiveRemove($lowestLeft->getKey(), $current->getRight()));
            return current;
        }

        if ($cmp < 0) {
            $current->setLeft($this->recursiveRemove($key, $current->getLeft()));
        } else {
            $current->setRight($this->recursiveRemove($key, $current->getRight()));
        }
        return $current;
    }

    public function remove(string $key): void {
        $this->root = $this->recursiveRemove($key, $this->root);
    }

    public function destroy(): void {
        $this->root = null;
    }

    private function recursiveSearch(string $key, Node $current = null) {

        if ($current == null) {
            return null;
        }

        $cmp = strcmp($key, $current->getKey());
        if ($cmp == 0) {
            return $current->getValue();
        }

        if ($cmp < 0) {
            return $this->recursiveSearch($key, $current->getLeft());
        } else {
            return $this->recursiveSearch($key, $current->getRight());
        }
    }

    public function search(string $key) {
        return $this->recursiveSearch($key, $this->root);
    }

    public function size(): int {
        $it = $this->iterator();
        $count = 0;
        while ($it->valid()) {
            $it->next();
            $count++;
        }
        return $count;
    }

    public function iterator(): InOrderIterator {
        return new InOrderIterator($this->root);
    }

}
