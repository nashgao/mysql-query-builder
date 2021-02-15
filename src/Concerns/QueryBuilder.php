<?php

declare(strict_types=1);

namespace Nashgao\MySQL\QueryBuilder\Concerns;

use Hyperf\Database\Model\Model;
use Nashgao\MySQL\QueryBuilder\Bean\MySQLBean;
use Nashgao\MySQL\QueryBuilder\Bean\SplBean;

/**
 * @property Model $model
 */
trait QueryBuilder
{
    /**
     * Get one value from the table, supports get specific column based on primary key
     * Normally it just returns a string or int
     * @param SplBean $bean
     * @return mixed
     */
    public function value(SplBean $bean)
    {
        $query = $this->getModel()::query();
        // check if the primary key exists
        if ($bean->issetPrimaryKey()) {
            $query = $query->where($this->model->primaryKey, $bean->getPrimaryKey());
        }
        return $query->value($this->getFirstKey($bean));
    }


    /**
     * Select specific column
     * It returns a set of values with numeric key in the array
     * @param SplBean $bean
     * @return array
     */
    public function pluck(SplBean $bean):array
    {
        return $this->getModel()::query()->pluck($this->getFirstKey($bean))->toArray();
    }

    /**
     * return first value (with limit 1)
     * @return array
     */
    public function first():array
    {
        return $this->getModel()::query()->first()->toArray();
    }

    /**
     * Select the result from a specific column (supports select on primary key), equivalent to 'select column where primary key = value '
     * @param SplBean $bean
     * @return array|null
     */
    public function get(SplBean $bean):?array
    {
        $query = $this->getModel()::query();
        if ($bean->issetPrimaryKey()) {
            $query = $query->where($this->model->primaryKey, $bean->getPrimaryKey());
        }

        return $this->filterSingleResultByGet($query->get($this->getFirstKey($bean))->toArray());
    }


    /**
     * Get the result of multiple columns based on the primary key
     * @param SplBean $bean
     * @return array|null
     */
    public function getMulti(SplBean $bean):?array
    {
        $query = $this->getModel()::query();

        if ($bean->issetPrimaryKey()) {
            $query = $query->where($this->model->primaryKey, $bean->getPrimaryKey());
        }

        return $this->filterSingleResultByGet($query->get($this->getValidKeys($bean)())->toArray());
    }


    /**
     * @param array $beans
     * @return array
     */
    public function getFromMulti(array $beans):array
    {
        $emptyPrimaryKey = true;
        $selectQuery = $this->getModel()::query();
        /** @var SplBean $bean */
        foreach ($beans as $bean) {
            if ($bean->issetPrimaryKey()) {
                $selectQuery->orWhere($this->model->primaryKey, $bean->getPrimaryKey());
                if ($emptyPrimaryKey === true) {
                    $emptyPrimaryKey = false;
                }
            }
        }

        // if there's no primary key, do not return anything
        if ($emptyPrimaryKey) {
            return [];
        }
        $result = $selectQuery->get();
        return isset($result) ? $result->toArray() : [];
    }

    /**
     * Get all records in the database
     * @return array
     */
    public function getAll(): array
    {
        return $this->getModel()::query()->get()->toArray();
    }

    /**
     * @param $primaryKey
     * @return Model|null
     */
    public function find($primaryKey):?Model
    {
        return $this->getModel()::find($primaryKey);
    }

    /**
     * @param SplBean $bean
     * @return bool
     */
    public function insert(SplBean $bean):bool
    {
        return $this->getModel()::query()->insert($bean->toArray());
    }

    /**
     * @param array $beans
     * @return bool
     */
    public function batchInsert(array $beans):bool
    {
        return $this->getModel()::query()->insert(
            (function () use ($beans) {
                $resultContainer = [];
                /** @var SplBean|Entity|array $bean */
                foreach ($beans as $bean) {
                    if ($bean instanceof SplBeanInterface) {
                        $resultContainer[] = $bean->toArray(null, $bean::FILTER_NOT_NULL);
                    }
                }
                return $resultContainer;
            })()
        );
    }

    /**
     * @param array $beans
     * @return bool
     */
    public function safeBatchInsert(array $beans): bool
    {
        $query = $this->getModel()::query();
        /**
         * @var MySQLBean $bean
         */
        foreach ($beans as $bean) {
            $query->where($this->model->primaryKey, $bean->getPrimaryKey());
        }

        $existence = $query->get()->toArray();

        $updatedContainer = [];
        foreach ($beans as $bean) {
            if (in_array($bean->getPrimaryKey(), $existence)) {
                $updatedContainer[] = $bean;
            }
        }

        return $this->batchInsert($updatedContainer);
    }

    /**
     * update single field in mysql for the client
     * @param SplBean $bean
     * @return int
     */
    public function update(SplBean $bean): int
    {
        return $this->getModel()::query()
            ->where($this->model->primaryKey, $bean->getPrimaryKey())
            ->limit(1)
            ->update($this->getBeanWithoutPrimaryKey($bean)->toArray(null, $bean::FILTER_NOT_NULL));
    }

    /**
     * @param SplBean $bean
     * @return int
     */
    public function batchUpdate(SplBean $bean): int
    {
        return $this->getModel()::query()
            ->where($this->model->primaryKey, $bean->getPrimaryKey())
            ->update($this->getBeanWithoutPrimaryKey($bean)->toArray(null, $bean::FILTER_NOT_NULL));
    }


    /**
     * @param SplBean $bean
     * @return int
     */
    public function delete(SplBean $bean):int
    {
        return $this->getModel()::destroy($bean->getPrimaryKey());
    }

    /**
     * @param SplBean $bean
     * @return bool
     */
    public function exists(SplBean $bean): bool
    {
        return $this->getModel()::query()->where($this->model->primaryKey, $bean->getPrimaryKey())->exists();
    }

    /**
     * @param array $beans
     * @return array
     */
    public function batchDelete(array $beans):array
    {
        $resultContainer = [];
        /** @var SplBean $bean */
        foreach ($beans as $bean) {
            if (! $bean instanceof SplBeanInterface) {
                continue;
            }
            $resultContainer[] = $this->getModel()::query()->where($this->model->primaryKey, $bean->getPrimaryKey())->delete();
        }
        return $resultContainer;
    }

    /**
     * @param SplBean $bean
     * @return array
     */
    protected function getArrayWithoutPrimaryKey(SplBean $bean):array
    {
        return filterBean($bean, [$this->model->primaryKey]);
    }

    /**
     * @param SplBean $bean
     * @return SplBean
     */
    protected function getBeanWithoutPrimaryKey(SplBean $bean):SplBean
    {
        return make(get_class($bean), [filterBean($bean, [$this->model->primaryKey])]);
    }


    /**
     * @param SplBean $bean
     * @return string
     */
    protected function getFirstKey(SplBean $bean):string
    {
        $keys = $this->getValidKeys($bean)();
        foreach ($keys as $key => $value) {
            if ($this->model->primaryKey === $value) {
                unset($keys[$key]);
            }
            break;
        }
        return array_values($keys)[0];
    }


    /**
     * @param SplBean $bean
     * @return \Closure
     */
    protected function getValidKeys(SplBean $bean):\Closure
    {
        /**
         * @return array
         */
        return function () use ($bean) {
            return array_keys($bean->toArray(null, $bean::FILTER_NOT_NULL));
        };
    }


    /**
     * @param array $resultFromDatabase
     * @return array|null
     */
    protected function filterSingleResultByGet(array $resultFromDatabase):?array
    {
        return ! empty($resultFromDatabase) ? $resultFromDatabase[0] : null;
    }
}
