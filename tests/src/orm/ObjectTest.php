<?php
namespace test\orm;

use ReflectionException;
use suda\orm\TableStruct;
use PHPUnit\Framework\TestCase;
use suda\framework\runnable\Runnable;
use suda\orm\struct\TableStructBuilder;
use suda\orm\struct\FieldModifierParser;
use suda\orm\struct\TableClassStructBuilder;

class ObjectTest extends TestCase
{
    public function testCreateStruct()
    {
        $struct = new TableStruct('user');
        $struct->fields([
            $struct->field('id', 'bigint', 20)->auto()->primary(),
            $struct->field('name', 'varchar', 80)->unique(),
            $struct->field('money', 'DECIMAL', [10,2])->key(),
            $struct->field('create_time', 'datetime')->alias('createTime'),
            $struct->field('content', 'text'),
        ]);
        $create = new TableStructBuilder(User::class);
        $this->assertEquals($struct, $create->createStruct());
    }

    public function testCreateClassStruct()
    {
        $struct = new TableStruct('user');
        $struct->fields([
            $struct->field('id', 'bigint', 20)->auto()->primary(),
            $struct->field('name', 'varchar', 80)->unique(),
            $struct->field('money', 'DECIMAL', [10,2])->key(),
            $struct->field('content', 'text'),
        ]);
        $create = new TableClassStructBuilder(User2::class);
        $this->assertEquals($struct, $create->createStruct());
    }

    public function testToken() {
        $modifier = (new FieldModifierParser)->parse('unique default(null) decimal(10,2) hello() default(0) comment("备\"注") #注释')->getModifier();
        $this->assertEquals([
            ['unique' , [],],
            ['default', [null],],
            ['decimal' , [10,2],],
            ['hello', [],],
            ['default' , [0]],
            ['comment' , ['备"注']],
        ], $modifier);
    }

    public function testCreateNameStruct()
    {
        $struct = new TableStruct('user');
        $struct->fields([
            $struct->field('id', 'bigint', 20),
            $struct->field('name', 'varchar', 80),
            $struct->field('money', 'DECIMAL', [10,2]),
            $struct->field('create_time', 'datetime')->alias('createTime'),
            $struct->field('content', 'text'),
        ]);
        $create = new TableStructBuilder(UserField::class);
        $this->assertEquals($struct, $create->createStruct());
    }


    /**
     * @dataProvider buildNameData
     * @param $expected
     * @param $value
     * @throws ReflectionException
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
