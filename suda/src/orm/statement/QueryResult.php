<?php
namespace suda\orm\statement;

use function method_exists;
use PDO;
use ReflectionClass;
use suda\orm\statement\Statement;
use suda\orm\connection\Connection;
use suda\orm\middleware\Middleware;
use suda\orm\middleware\NullMiddleware;

class QueryResult
{
    /**
     * 数据源
     *
     * @var Connection
     */
    protected $connection;

    /**
     * 中间件
     *
     * @var Middleware
     */
    protected $middleware;

    /**
     * 创建运行器
     *
     * @param Connection $connection
     * @param Middleware $middleware
     */
    public function __construct(Connection $connection, Middleware $middleware)
    {
        $this->connection = $connection;
        $this->middleware = $middleware;
    }
    
    /**
     * 获取运行结果
     *
     * @param Statement $statement
     * @return mixed
     */
    public function createResult(Statement $statement)
    {
        if ($statement->isWrite()) {
            if ($statement->getReturnType() === Statement::RET_ROWS) {
                return $statement->getStatement()->rowCount();
            }
            if ($statement->getReturnType() === Statement::RET_LAST_INSERT_ID) {
                return $this->connection->getPdo()->lastInsertId();
            }
            return $statement->getStatement()->rowCount() > 0;
        } elseif ($statement->isFetch()) {
            return $this->fetchResult($statement);
        }
    }

    /**
     * 取结果
     *
     * @param \suda\orm\statement\Statement $statement
     * @param string|null $class
     * @param array $ctor_args
     * @return mixed
     */
    protected function fetchResult(Statement $statement, ?string $class = null, array $ctor_args = [])
    {
        if ($class !== null) {
            $statement->setFetchType($class, $ctor_args);
        }
        if ($statement->isFetchOne()) {
            $data = $statement->getStatement()->fetch(PDO::FETCH_ASSOC) ?: null;
            if ($data !== null) {
                return $this->fetchOneProccess($statement, $data);
            }
            return $data;
        } elseif ($statement->isFetchAll()) {
            $data = $statement->getStatement()->fetchAll(PDO::FETCH_ASSOC);
            return $this->fetchAllProccess($statement, $data);
        }
    }

    /**
     * 处理一行数据
     *
     * 由于PDO的构造函数在设置值之后才会被调用，所以需要一个创建对象的方法
     *
     * @param ReadStatement|QueryStatement $statement
     * @param array $data
     * @return mixed
     */
    protected function fetchOneProccess($statement, array $data)
    {
        if ($statement->getFetchClass() !== null) {
            $reflectClass = new ReflectionClass($statement->getFetchClass());
            $object = $reflectClass->newInstanceArgs($statement->getFetchClassArgs());
            if (method_exists($object, '__set')) {
                $this->setValueWithMagicSet($object, $data);
            } else {
                $this->setValueWithReflection($reflectClass, $object, $data);
            }
            return $object;
        }
        return $this->fetchOneProccessArray($data);
    }

    /**
     * 通过反射方法设置值
     *
     * @param ReflectionClass $reflectClass
     * @param mixed $object
     * @param array $data
     * @return void
     */
    protected function setValueWithReflection(ReflectionClass $reflectClass, $object, array $data)
    {
        foreach ($data as $name => $value) {
            $value = $this->middleware->output($name, $value);
            $propertyName = $this->middleware->outputName($name);
            if ($reflectClass->hasProperty($propertyName)) {
                $property = $reflectClass->getProperty($propertyName);
                $property->setAccessible(true);
                $property->setValue($object, $value);
            } else {
                // 属性不存在则尝试直接赋值
                $object->$propertyName = $value;
            }
        }
    }

    /**
     * 通过魔术方法设置值
     *
     * @param ReflectionClass $reflectClass
     * @param mixed $object
     * @param array $data
     * @return void
     */
    protected function setValueWithMagicSet($object, array $data)
    {
        foreach ($data as $name => $value) {
            $value = $this->middleware->output($name, $value);
            $propertyName = $this->middleware->outputName($name); 
            $object->__set($propertyName, $value);
        }
    }

    /**
     * 处理多行数据
     *
     * @param ReadStatement|QueryStatement $statement
     * @param array $data
     * @return array
     */
    protected function fetchAllProccess($statement, array $data): array
    {
        foreach ($data as $index => $row) {
            $row = $this->fetchOneProccess($statement, $row);
            $row = $this->middleware->outputRow($row);
            $data[$index] = $row;
        }
        $withKey = $statement->getWithKey();
        if ($withKey !== null) {
            $target = [];
            foreach ($data as $key => $value) {
                $target[$value[$withKey]] = $value;
            }
            return $target;
        }
        return $data;
    }
    
    /**
     * 处理一行数据
     *
     * @param array $data
     * @return array
     */
    protected function fetchOneProccessArray($data)
    {
        if ($this->middleware !== null) {
            foreach ($data as $name => $value) {
                $data[$name] = $this->middleware->output($name, $value);
            }
        }
        return $data;
    }
}
