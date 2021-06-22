<?php

declare(strict_types=1);

namespace Nashgao\MySQL\QueryBuilder\Concerns;

use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Model;
use JetBrains\PhpStorm\Pure;
use Nashgao\MySQL\QueryBuilder\Bean\MySQLBean;

/**
 * @property Model $model
 * @method Model getModel()
 */
trait QueryBuilder
{
    /**
     * Get one value from the table, supports get specific column based on primary key
     * Normally it just returns a string or int
     * @param MySQLBean $bean
     * @return mixed
     */
    public function value(MySQLBean $bean): mixed
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
     * @param MySQLBean $bean
     * @return array
     */
    public function pluck(MySQLBean $bean):array
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
     * @param MySQLBean $bean
     * @return array|null
     */
    public function get(MySQLBean $bean):?array
    {
        $query = $this->getModel()::query();
        if ($bean->issetPrimaryKey()) {
            $query = $query->where($this->model->primaryKey, $bean->getPrimaryKey());
        }

        return $this->filterSingleResultByGet($query->get($this->getFirstKey($bean))->toArray());
    }


    /**
     * Get the result of multiple columns based on the primary key
     * @param MySQLBean $bean
     * @return array|null
     */
    public function getMulti(MySQLBean $bean):?array
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
        /** @var MySQLBean $bean */
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

    public function fieldExists(string $field, $value): bool
    {
        return $this->getModel()::query()->where($field, $value)->exists();
    }

    /**
     * @param int $paginate
     * @return LengthAwarePaginatorInterface
     */
    public function paginate(int $paginate = 10): LengthAwarePaginatorInterface
    {
        return $this->getModel()::query()->paginate($paginate);
    }

    /**
     * @param $primaryKey
     * @return Model|null
     */
    public function find($primaryKey):?static
    {
        return $this->getModel()::find($primaryKey);
    }

    /**
     * @param MySQLBean $bean
     * @return bool
     */
    public function insert(MySQLBean $bean):bool
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
                /** @var MySQLBean|array $bean */
                foreach ($beans as $bean) {
                    if ($bean instanceof MySQLBean) {
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
     * @param MySQLBean $bean
     * @return int
     */
    public function update(MySQLBean $bean): int
    {
        return $this->getModel()::query()
            ->where($this->model->primaryKey, $bean->getPrimaryKey())
            ->limit(1)
            ->update($this->getBeanWithoutPrimaryKey($bean)->toArray(null, $bean::FILTER_NOT_NULL));
    }

    /**
     * @param MySQLBean $bean
     * @return int
     */
    public function batchUpdate(MySQLBean $bean): int
    {
        return $this->getModel()::query()
            ->where($this->model->primaryKey, $bean->getPrimaryKey())
            ->update($this->getBeanWithoutPrimaryKey($bean)->toArray(null, $bean::FILTER_NOT_NULL));
    }


    /**
     * @param MySQLBean $bean
     * @return int
     */
    public function delete(MySQLBean $bean):int
    {
        return $this->getModel()::destroy($bean->getPrimaryKey());
    }

    /**
     * @param MySQLBean $bean
     * @return bool
     */
    public function exists(MySQLBean $bean): bool
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
        /** @var MySQLBean $bean */
        foreach ($beans as $bean) {
            if (! $bean instanceof MySQLBean) {
                continue;
            }
            $resultContainer[] = $this->getModel()::query()->where($this->model->primaryKey, $bean->getPrimaryKey())->delete();
        }
        return $resultContainer;
    }

    /**
     * @param MySQLBean $bean
     * @return array
     */
    protected function getArrayWithoutPrimaryKey(MySQLBean $bean):array
    {
        return filterBean($bean, [$this->model->primaryKey]);
    }

    /**
     * @param MySQLBean $bean
     * @return MySQLBean
     */
    protected function getBeanWithoutPrimaryKey(MySQLBean $bean):MySQLBean
    {
        return make(get_class($bean), [filterBean($bean, [$this->model->primaryKey])]);
    }


    /**
     * @param MySQLBean $bean
     * @return string
     */
    protected function getFirstKey(MySQLBean $bean):string
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
     * @param MySQLBean $bean
     * @return \Closure
     */
    protected function getValidKeys(MySQLBean $bean):\Closure
    {
        /**
         * @return array
         */
        return function () use ($bean) {
            return array_keys($bean->toArray(null, $bean::FILTER_NOT_NULL));
        };
    }


    /**
     * @param array $result
     * @return array|null
     */
    protected function filterSingleResultByGet(array $result):?array
    {
        return ! empty($result) ? $result[0] : null;
    }

    /**
     * @param array $result
     * @param string|null $column
     * @return mixed
     */
    #[Pure]
    protected function filterSingleColumnResultByGet(array $result, string $column = null): mixed
    {
        $result = $this->filterSingleResultByGet($result);
        if (! isset($result) or empty($result)) {
            return null;
        }

        if (array_key_exists($column, $result)) {
            return $result[$column];
        }

        return null;
    }
}
