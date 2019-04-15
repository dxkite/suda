<?php
namespace test\orm;

use test\orm\UserField;
use suda\orm\TableStruct;
use PHPUnit\Framework\TestCase;
use suda\orm\TableStructBuilder;
use suda\framework\runnable\Runnable;

class ObjectTest extends TestCase
{
    // public function testCreateStruct()
    // {
    //     $struct = new TableStruct('user');

    //     $struct->fields([
    //         $struct->field('id', 'bigint', 20)->auto()->primary(),
    //         $struct->field('name', 'varchar', 80)->unique(),
    //     ]);

    //     $create = new TableStructBuilder(User::class);
    //     $this->assertEquals($struct, $create->createStruct());
    // }

    public function testCreateNameStruct()
    {
        $struct = new TableStruct('user');
        $struct->fields([
            $struct->field('id', 'bigint', 20),
            $struct->field('name', 'varchar', 80),
            $struct->field('money', 'DECIMAL', [10,2]),
            $struct->field('create_time', 'datetime'),
            $struct->field('content', 'text'),
        ]);
        $create = new TableStructBuilder(UserField::class);
        $this->assertEquals($struct, $create->createStruct());
    }

  

    /**
     * @dataProvider buildNameData
     */
    public function testName($expected, $value)
    {
        $func = new Runnable([new TableStructBuilder(User::class), 'createName']);
        $this->assertEquals($expected, $func($value));
    }

    public function buildNameData()
    {
        return [
            'table' => ['table', 'Table' ],
            'table_name' => ['table_name', 'TableName'],
            'table_long_name' => ['table_long_name', '_tableLong__NAME'],
        ];
    }
}
