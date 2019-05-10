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
            'SELECT `id`,`name` FROM user_table WHERE `id`=:_id_0',
            $table->read('id', 'name')->where(['id' => 1])->getString()
        );

        $this->assertEquals(
            'SELECT `id`,`name` FROM user_table WHERE `id`=:_id_1 LIMIT 0,10',
            $table->read('id', 'name')->where(['id' => 1])->page(1, 10)->getString()
        );

        $this->assertEquals(
            'SELECT `id`,`name` FROM user_table WHERE `id`=:_id_2 HAVING `name`=:_name_3 LIMIT 0,10',
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
            'UPDATE user_table SET `id`=:_id_7,`name`=:_name_8 WHERE `name` like :_name_6',
            $table->write('id', '1')->write('name', 'dxkite')->where(['name' => ['like','dxkite']])->getString()
        );

        $this->assertEquals(
            'INSERT INTO user_table (`id`,`name`) VALUES (:_id_9,:_name_10)',
            $table->write('id', 1)->write('name', 'dxkite')->getString()
        );

        $this->assertEquals(
            'INSERT INTO user_table (`id`,`name`) VALUES (:_id_11,:_name_12)',
            $table->write(['id' => 1, 'name' => 'dxkite'])->getString()
        );

        $this->assertEquals(
            'hello > :name',
            (new QueryStatement('hello > :name', ['name' => 'dxkite']))->getString()
        );

        $this->assertEquals(
            'DELETE FROM user_table WHERE `name` like :_name_14',
            $table->delete(['name' => ['like','dxkite']])->getString()
        );
        $this->assertEquals(
            'DELETE FROM user_table WHERE `id` > :_id_15',
            $table->delete(['id' => ['>', 10]])->getString()
        );

        $this->assertEquals(
            'hello > :_0_16 and hello < :_1_17',
            (new QueryStatement('hello > ? and hello < ?', 1, 3))->getString()
        );

        $this->assertEquals(
            'SELECT `id`,`name` FROM user_table WHERE id in (:_0_18,:_1_19) HAVING name like :_0_20 LIMIT 0,10',
            $table->read('id', 'name')->where('id in (?,?)', 1, 2)->page(1, 10)->having('name like ?', 'dxkite')->getString()
        );

        $this->assertEquals(
            'DELETE FROM user_table WHERE `id` is :_id_21',
            $table->delete(['id' => ['is', null]])->getString()
        );

        $whereIn = $table->delete('id in (:id)', [ 'id' => new ArrayObject([1, 3, 4])])->getString();
        $this->assertEquals(
            'DELETE FROM user_table WHERE id in (:_id_22,:_id_23,:_id_24)',
            $whereIn
        );

        $this->assertEquals(
            'SELECT `id`,`name` FROM user_table WHERE id in (:_0_25,:_0_26) HAVING name like :_0_27 LIMIT 0,10',
            $table->read('id', 'name')->where('id in (?)', new ArrayObject([ 1, 2]))->page(1, 10)->having('name like ?', 'dxkite')->getString()
        );

        $whereIn = $table->delete('id in (?)', new ArrayObject([1, 3, 4]));
        $this->assertEquals(
            'DELETE FROM user_table WHERE id in (:_0_28,:_0_29,:_0_30)',
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
