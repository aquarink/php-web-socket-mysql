<?php

declare(strict_types=1);

namespace Arrayy;

/**
 * @template TKey of array-key
 * @template T
 * @template-extends \ArrayIterator<TKey,T>
 */
class ArrayyIterator extends \ArrayIterator
{
    /**
     * @var string
     */
    private $class;

    /**
     * @param array<mixed,mixed> $array
     * @param int                $flags
     * @param string             $class
     *
     * @psalm-param array<TKey,T> $array
     */
    public function __construct(array $array = [], int $flags = 0, string $class = '')
    {
        $this->class = $class;

        parent::__construct($array, $flags);
    }

    /**
     * @return Arrayy|mixed will return a "Arrayy"-object instead of an array
     */
    public function current()
    {
        $value = parent::current();

        if (\is_array($value)) {
            return \call_user_func([$this->class, 'create'], $value);
        }

        return $value;
    }

    /**
     * @param string $offset
     *
     * @return Arrayy|mixed
     *                      <p>Will return a "Arrayy"-object instead of an array.</p>
     *
     * @psalm-param TKey $offset
     * @param-return Arrayy<TKey,T>|mixed
     */
    public function offsetGet($offset)
    {
        $value = parent::offsetGet($offset);

        if (\is_array($value)) {
            $value = \call_user_func([$this->class, 'create'], $value);
        }

        return $value;
    }
}
