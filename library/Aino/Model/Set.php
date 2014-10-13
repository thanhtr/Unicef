<?php
namespace Aino\Model;
use Aino\Model;

class Set implements \ArrayAccess, \Countable, \SeekableIterator
{

    protected $items = array();

    protected $pointer = 0;

    protected $count = 0;

    public function addItem(Model $item)
    {
        $this->items[] = $item;
        $this->count = $this->count();

        return $this;
    }

    public function prependItem(Model $item)
    {
        array_unshift($this->items, $item);
        $this->count = $this->count();
        $this->rewind();

        return $this;
    }

    public function toJson()
    {
        return \Zend_Json::encode($this->toArray());
    }

    public function toArray()
    {
        $items = array();
        foreach ($this->items as $item) {
            $items[] = $item->toArray();
        }
        return $items;
    }

    public function hasItem($id) {
        return (bool) $this->findById($id);
    }

    public function findById($id)
    {
        foreach ($this->items as $item) {
            if ($item->getId() == $id) {
                return $item;
            }
        }

        return null;
    }

    public function findBy($property, $value)
    {
        foreach ($this->items as $item) {
            $method = 'get' . ucfirst($property);

            if (method_exists($item, $method) && $item->$method() == $value) {
                return $item;
            }
        }

        return null;
    }

    public function clearAll()
    {
        $this->items = array();
        $this->count = $this->count();
        $this->pointer = 0;
    }

    public function removeById($id)
    {
        foreach ($this->items as $offset => $item) {
            if ($item->getId() == $id) {
                $this->offsetUnset($offset);
                return true;
            }
        }

        return null;
    }

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
        $this->count = $this->count();
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
        $this->items = array_values($this->items);
        $this->count = $this->count();
    }

    public function valid()
    {
        return ($this->pointer) >= 0 &&
               ($this->pointer < $this->count);
    }

    public function rewind()
    {
        $this->pointer = 0;
    }

    public function next()
    {
        ++$this->pointer;
    }

    public function key()
    {
        return $this->pointer;
    }

    public function current()
    {
        return $this->items[$this->pointer];
    }

    public function seek($position)
    {
        return $this->items[$position];
    }

    public function count()
    {
        return count($this->items);
    }
}