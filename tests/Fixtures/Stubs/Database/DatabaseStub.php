<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class Database
{
    public static function getInstance()
    {
        return new class {
            public function getConnection()
            {
                return new class {
                    public function query($sql)
                    {
                        return new class {
                            public function fetch($mode = null)
                            {
                                return ['total' => 0, 'completed' => 0];
                            }
                        };
                    }
                };
            }
        };
    }
}
