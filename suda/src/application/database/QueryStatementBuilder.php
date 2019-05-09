<?php


namespace suda\application\database;

use suda\application\exception\ConfigurationException;
use suda\orm\statement\QueryAccess;
use suda\orm\struct\QueryStatement;

/**
 * 语句读取
 * 从模块资源文件中读取SQL语句构建查询对象
 * @package suda\application\database
 */
class QueryStatementBuilder
{
    /**
     * 查询配置
     * @var array
     */
    protected $config;

    /**
     * 查询操作
     * @var QueryAccess
     */
    protected $access;

    /**
     * QueryStatementBuilder constructor.
     * @param QueryAccess $access
     * @param array $config
     */
    public function __construct(QueryAccess $access, array $config = [])
    {
        $this->config = $config;
        $this->access = $access;
    }

    /**
     * @return QueryStatement
     */
    public function build():QueryStatement
    {
        $query = new QueryStatement($this->access, $this->getQuery());
        return $this->modifyQueryStatement($query);
    }

    /**
     * 获取查询
     * @return string
     */
    protected function getQuery()
    {
        if (array_key_exists('query', $this->config)) {
            $query = $this->config['query'];
            if (is_array($query)) {
                $type = $this->access->getConnection()->getType();
                if (array_key_exists($type, $query)) {
                    return $query[$type];
                }
                throw new ConfigurationException(
                    sprintf("missing config query:%s", $type),
                    ConfigurationException::ERR_MISSING_CONFIG
                );
            }
            return $query;
        }
        throw new ConfigurationException('missing config query', ConfigurationException::ERR_MISSING_CONFIG);
    }

    /**
     * 修饰查询
     * @param QueryStatement $statement
     * @return QueryStatement
     */
    protected function modifyQueryStatement(QueryStatement $statement):QueryStatement
    {
        $type = $this->config['type'] ?? 'read';
        if (in_array($type, ['read','write'])) {
            $statement->setType($type === 'read'?QueryStatement::READ:QueryStatement::WRITE);
        }
        if (array_key_exists('return-type', $this->config)) {
            $statement->setReturnType($this->config['return-type']);
        }
        if (array_key_exists('with-key', $this->config)) {
            $statement->withKey($this->config['with-key']);
        }
        if (array_key_exists('scroll', $this->config)) {
            $statement->setScroll($this->config['scroll']);
        }
        return $statement;
    }
}
