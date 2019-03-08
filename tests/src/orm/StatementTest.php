<?php
namespace test\orm;

use SQLite3;
use suda\orm\DataSource;
use suda\orm\TableAccess;
use suda\orm\TableStruct;
use PHPUnit\Framework\TestCase;
use suda\orm\statement\Statement;

class StatementTest extends TestCase
{
    public function testTableStruct()
    {
        $struct = new TableStruct('user_table');

        $struct->fields([
            $struct->field('id', 'bigint', 20)->auto()->primary(),
            $struct->field('name', 'varchar', 80),
        ]);

        $this->assertTrue($struct->getFields()->hasField('name'));
        $this->assertTrue($struct->getFields()->hasField('id'));
    }

    public function testMySQLConnection()
    {
        $source = new DataSource;
        $struct = new TableStruct('user_table');

        $struct->fields([
            $struct->field('id', 'bigint', 20)->auto()->primary(),
            $struct->field('name', 'varchar', 80),
        ]);

     
         
        $source->add(DataSource::new('mysql', [
            'host' => 'localhost',
            'name' => 'test',
            'user' => 'root',
            'password' => DIRECTORY_SEPARATOR === '/' ?'':'root',
        ]));
     

        $table = new TableAccess($struct, $source);

        

        $this->assertEquals(
            'SELECT `id`,`name` FROM user_table',
            $table->read('id', 'name')->getString()
        );

        $this->assertEquals(
            'SELECT `id`,`name` FROM user_table WHERE `id`=:_0id',
            $table->read('id', 'name')->where(['id' => 1])->getString()
        );

        $this->assertEquals(
            'SELECT `id`,`name` FROM user_table WHERE `id`=:_1id LIMIT 0,10',
            $table->read('id', 'name')->where(['id' => 1])->page(1, 10)->getString()
        );

        $this->assertEquals(
            'SELECT `id`,`name` FROM user_table WHERE `id`=:_2id HAVING `name`=:_3name LIMIT 0,10',
            $table->read('id', 'name')->where(['id' => 1])->page(1, 10)->having(['name' => 'dxkite'])->getString()
        );

        $this->assertEquals(
            'SELECT `id`,`name` FROM user_table WHERE name like :name ORDER BY id ASC LIMIT 0,10',
            $table->read('id', 'name')->where('name like :name', ['name' => 'dxkite'])->page(1, 10)->orderBy('id', 'ASC')->getString()
        );

        $this->assertEquals(
            'SELECT DISTINCT `id`,`name` FROM user_table WHERE name like :name ORDER BY id ASC LIMIT 0,10',
            $table->read('id', 'name')->distinct()->where('name like :name', ['name' => 'dxkite'])->page(1, 10)->orderBy('id', 'ASC')->getString()
        );

        $this->assertEquals(
            'UPDATE user_table SET `id`=:_5id,`name`=:_6name WHERE name like :name',
            $table->write('id', '1')->write('name', 'dxkite')->where('name like :name', ['name' => 'dxkite'])->getString()
        );

        $this->assertEquals(
            'INSERT INTO user_table (`id`,`name`) VALUES (:_7id,:_8name)',
            $table->write('id', 1)->write('name', 'dxkite')->getString()
        );

        $this->assertEquals(
            'INSERT INTO user_table (`id`,`name`) VALUES (:_9id,:_10name)',
            $table->write(['id' => 1, 'name' => 'dxkite'])->getString()
        );

        $this->assertEquals(
            'hello > :name',
            (new Statement('hello > :name', ['name' => 'dxkite']))->getString()
        );

        if (DIRECTORY_SEPARATOR === '/') {
            $this->assertTrue($table->getSource()->write()->createTable($struct->getFields()));
            
            $this->assertTrue($table->run($table->write(['name' => 'dxkite'])));

            $data = $table->run($table->read('name')->where(['id' => 1]));
    
            $this->assertEquals('dxkite', $data['name']);
    
            $data = $table->run($table->read('id', 'name')->where(['id' => 1])->withKey('id'));
    
            $this->assertEquals('dxkite', $data[1]['name']);
        }
    }
}
