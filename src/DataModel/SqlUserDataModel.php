<?php
/*
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Bookshelf\DataModel;

use PDO;

/**
 * Class Sql implements the DataModelInterface with a mysql or postgres database.
 *
 */
class SqlUserDataModel implements UserDataModelInterface
{
    private $dsn;
    private $user;
    private $password;

    public function __construct($dsn, $user, $password)
    {
        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Creates a new PDO instance and sets error mode to exception.
     *
     * @return PDO
     */
    private function newConnection()
    {
        $pdo = new PDO($this->dsn, $this->user, $this->password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }


    public function readByEmail($email)
    {
        $pdo = $this->newConnection();
        $statement = $pdo->prepare('SELECT * FROM usuarios WHERE email = :email');
        $statement->bindValue('email', $email, PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function readRoles($userId)
    {
        $pdo = $this->newConnection();
        $statement = $pdo->prepare('SELECT r.role_name FROM roles r inner join usuario_role ur on r.id=ur.role_id WHERE ur.user_id = :userId');
        $statement->bindValue('userId', $userId, PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }


    public static function getMysqlDsn($dbName, $port, $connectionName = null)
    {
        if ($connectionName) {
            return sprintf('mysql:unix_socket=/cloudsql/%s;dbname=%s',
                $connectionName,
                $dbName);
        }

        return sprintf('mysql:host=127.0.0.1;port=%s;dbname=%s', $port, $dbName);
    }

    public static function getPostgresDsn($dbName, $port, $connectionName = null)
    {
        if ($connectionName) {
            return sprintf('pgsql:host=/cloudsql/%s;dbname=%s',
                $connectionName,
                $dbName);
        }

        return sprintf('pgsql:host=127.0.0.1;port=%s;dbname=%s', $port, $dbName);
    }
}
