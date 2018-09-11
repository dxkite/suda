<?php
namespace suda\cli;

abstract class Command {
    abstract public static function exec(array $argv);
}
