<?php

declare(strict_types=1);


namespace Nashgao\MySQL\QueryBuilder\Bean;

abstract class SplBean extends \EasySwoole\Spl\SplBean implements SplBeanInterface
{
    /**
     * @param array|null $columns
     * @param null $filter
     * @return array
     */
    public function toArray(array $columns = null, $filter = null): array
    {
        $array = parent::toArray($columns, $filter);
        array_walk_recursive($array, function (&$item, $key) {
            if (! is_scalar($item) and $item instanceof SplBeanInterface) {
                $item = $item->toArray();
            }
        });
        return array_filter(
            $array,
            function ($item) {
                return !is_null($item);
            }
        );
    }

    /**
     * @param array|null $columns
     * @param null $filter
     * @return array
     */
    public function toArrayWithOneDimension(array $columns = null, $filter = null): array
    {
        return parent::toArray($columns, $filter);
    }
}
