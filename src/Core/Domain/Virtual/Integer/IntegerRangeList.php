<?php
declare(strict_types=1);

namespace App\Core\Domain\Virtual\Integer;

use App\Core\Domain\Virtual\Range;

class IntegerRangeList
{
    private $list;

    public function __construct()
    {
        $this->list = new \ArrayObject();
    }

    public function add(Range $range) : void
    {
        $this->list->append($range);
    }

    public function get(int $index) : ?Range
    {
        if ($this->list->offsetExists($index)) {
            return $this->list->offsetGet($index);
        }
        return null;
    }

    public function set(Range $range, int $index) : void
    {
        if ($this->list->offsetExists($index)) {
            $this->list->offsetSet($index, $range);
        }
    }

    public function count() : int
    {
        return $this->list->count();
    }

    public function last() : ?Range
    {
        if ($this->list->count() > 0) {
            return $this->list->offsetGet($this->list->count() - 1);
        }
        return null;
    }

    public function getIterator()
    {
        return new IntegerRangeIterator( $this );
    }
}
