<?php
namespace test\orm;

use ArrayObject;
use suda\application\database\creator\MySQLTableCreator;
use suda\orm\DataSource;
use suda\orm\TableAccess;
use suda\orm\struct\TableStruct;
use PHPUnit\Framework\TestCase;
use suda\orm\statement\QueryStatement;
use suda\orm\TableData;

class StatementTest extends TestCase
{
    public function testTableStruct()
    {
        $struct = new TableStruct('user_table');

        $struct->fields([
            $struct->field('id', 'bigint', 20)->auto()->primary(),
            $struct->field('name', 'varchar', 80),
        ]);

        $this->assertTrue($struct->hasField('name'));
        $this->assertTrue($struct->hasField('id'));
    }

    public function testMySQLBuilder()
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
            'UPDATE user_table SET `id`=:_7id,`name`=:_8name WHERE `name` like :_6name',
            $table->write('id', '1')->write('name', 'dxkite')->where(['name' => ['like','dxkite']])->getString()
        );

        $this->assertEquals(
            'INSERT INTO user_table (`id`,`name`) VALUES (:_9id,:_10name)',
            $table->write('id', 1)->write('name', 'dxkite')->getString()
        );

        $this->assertEquals(
            'INSERT INTO user_table (`id`,`name`) VALUES (:_11id,:_12name)',
            $table->write(['id' => 1, 'name' => 'dxkite'])->getString()
        );

        $this->assertEquals(
            'hello > :name',
            (new QueryStatement('hello > :name', ['name' => 'dxkite']))->getString()
        );

        $this->assertEquals(
            'DELETE FROM user_table WHERE `name` like :_14name',
            $table->delete(['name' => ['like','dxkite']])->getString()
        );
        $this->assertEquals(
            'DELETE FROM user_table WHERE `id` > :_15id',
            $table->delete(['id' => ['>', 10]])->getString()
        );

        $this->assertEquals(
            'hello > :_160 and hello < :_171',
            (new QueryStatement('hello > ? and hello < ?', 1, 3))->getString()
        );

        $this->assertEquals(
            'SELECT `id`,`name` FROM user_table WHERE id in (:_180,:_191) HAVING name like :_200 LIMIT 0,10',
            $table->read('id', 'name')->where('id in (?,?)', 1, 2)->page(1, 10)->having('name like ?', 'dxkite')->getString()
        );

        $this->assertEquals(
            'DELETE FROM user_table WHERE `id` is :_21id',
            $table->delete(['id' => ['is', null]])->getString()
        );

        $whereIn = $table->delete('id in (:id)', [ 'id' => new ArrayObject([1, 3, 4])])->getString();
        $this->assertEquals(
            'DELETE FROM user_table WHERE id in (:_22id,:_23id,:_24id)',
            $whereIn
        );

        $this->assertEquals(
            'SELECT `id`,`name` FROM user_table WHERE id in (:_250,:_260) HAVING name like :_270 LIMIT 0,10',
            $table->read('id', 'name')->where('id in (?)', new ArrayObject([ 1, 2]))->page(1, 10)->having('name like ?', 'dxkite')->getString()
        );

        $whereIn = $table->delete('id in (?)', new ArrayObject([1, 3, 4]));
        $this->assertEquals(
            'DELETE FROM user_table WHERE id in (:_280,:_290,:_300)',
            $whereIn->getString()
        );
    }

    public function testStuctSet()
    {
        $tableData = new TableData('user_table');
        $tableData->getStruct()->fields([
            $tableData->getStruct()->field('id', 'bigint', 20)->auto()->primary(),
            $tableData->getStruct()->field('name', 'varchar', 80),
        ]);
        $tableData->name = 'dxkite';
        $this->assertEquals('dxkite', $tableData->name);
    }

    public function testMySQLConnectionWindows()
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

        if (DIRECTORY_SEPARATOR === '\\') {
            $this->assertNotNull((new MySQLTableCreator($table->getSource()->write(), $struct))->create());

            $this->assertTrue($table->run($table->write(['name' => 'dxkite'])));

            $data = $table->run($table->read('name')->where(['id' => 1]));

            $this->assertEquals('dxkite', $data['name']);

            $data = $table->run($table->read('id', 'name')->where(['id' => 1])->withKey('id'));

            $this->assertEquals('dxkite', $data[1]['name']);
        }
    }
}
