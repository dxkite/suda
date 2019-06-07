<?php


namespace suda\welcome\response;

use suda\application\Application;
use suda\application\processor\RequestProcessor;

use suda\database\exception\SQLException;
use suda\framework\Request;
use suda\framework\Response;
use suda\welcome\table\HelloTable;

class DbDemoResponse implements RequestProcessor
{

    /**
     * @param Application $application
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function onRequest(Application $application, Request $request, Response $response)
    {
        $template = $application->getTemplate('db', $request);

    //    $table = new HelloTable();

    //    $isSuccess = $table->write([
    //        'name' => '$isSuccess',
    //    ])->ok();

    //    $lastInsertId = $table->write([
    //        'name' => '$lastInsertId',
    //    ])->id();

    //    $effectRows = $table->write([
    //        'name' => '$effectRows',
    //    ])->id();


    //    try {
    //        $table = new HelloTable();
    //        $effectRows = $table->query('INSERT INTO _:hello ( `name`) SELECT name FROM _:hello')->rows();
    //        $application->debug()->info('effect rows {rows}', ['rows' => $effectRows]);
    //    } catch (\ReflectionException $e) {
    //    } catch (SQLException $e) {
    //    }

    //    try {
    //        $table = new HelloTable();
    //        $effectRows = $table->query('INSERT INTO _:hello ( `name`) VALUES (?),(?)', 'dxkite_1', 'dxkite_2')->rows();
    //        $application->debug()->info('effect rows {rows}', ['rows' => $effectRows]);
    //    } catch (\ReflectionException $e) {
    //    } catch (\suda\database\exception\SQLException $e) {
    //    }


    //    try {
    //        $table = new HelloTable();
    //        $effectRows = $table->query('INSERT INTO _:hello ( `name`) VALUES (:value1),(:value2)', [ 'value1' => 'dxkite_1_:value1', 'value2' => 'dxkite_1_:value2'])->rows();
    //        $application->debug()->info('effect rows {rows}', ['rows' => $effectRows]);
    //    } catch (\ReflectionException $e) {
    //    } catch (\suda\database\exception\SQLException $e) {
    //    }
    //    try {
    //        $table = new HelloTable();
    //        $data = $table->read(['id', 'name'])->where(['name' => '$isSuccess'])
    //            ->withKey('id')
    //            ->all();
    //    } catch (\ReflectionException $e) {
    //    } catch (\suda\database\exception\SQLException $e) {
    //    }

    //    try {
    //        $table = new HelloTable();
    //        $data = $table->read(['id', 'name'])->where(['name' => '$isSuccess'])
    //            ->withKeyCallback(function ($value) {
    //                return $value['name'].'-'.$value['id'];
    //            })
    //            ->all();
    //    } catch (\ReflectionException $e) {
    //    } catch (\suda\database\exception\SQLException $e) {
    //    }

    //    $table = new HelloTable();
    //    try {
    //        $effectRows = $table->write([
    //            'name' => 'update',
    //        ])->where(['id' => new \ArrayObject([1,2,3])])->rows();
    //    } catch (\ReflectionException $e) {
    //    } catch (SQLException $e) {
    //    }

    //     $table = new HelloTable();
    //     try {
    //         $effectRows = $table->delete(['id' => new \ArrayObject([1, 2, 3])])->rows();
    //     } catch (\ReflectionException $e) {
    //     } catch (SQLException $e) {
    //     }
        return $template;
    }
}
