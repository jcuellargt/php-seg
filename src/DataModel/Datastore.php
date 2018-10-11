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

use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Entity;

/**
 * Class Datastore implements the DataModel with a Google Data Store.
 */
class Datastore implements DataModelInterface
{
    private $datasetId;
    private $datastore;
    protected $columns = [
        'id'            => 'integer',
        'title'         => 'string',
        'author'        => 'string',
        'published_date' => 'string',
        'image_url'     => 'string',
        'description'   => 'string',
        'created_by'    => 'string',
        'created_by_id' => 'string',
    ];

    protected $employee_columns = [
        'id'                => 'integer',
        'primer_nombre'     => 'string',
        'segundo_nombre'    => 'string',
        'primer_apellido'   => 'string',
        'segundo_apellido'  => 'string',
        'fecha_nacimiento'  => 'string',
        'fecha_ingreso'     => 'string',
    ];

    public function __construct($projectId)
    {
        $this->datasetId = $projectId;
        $this->datastore = new DatastoreClient([
            'projectId' => $projectId,
        ]);
    }

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
// = = = > BOOKS
// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    public function listBooks($limit = 10, $cursor = null)
    {
        $query = $this->datastore->query()
            ->kind('Book')
            ->order('title')
            ->limit($limit)
            ->start($cursor);

        $results = $this->datastore->runQuery($query);

        $books = [];
        $nextPageCursor = null;
        foreach ($results as $entity) {
            $book = $entity->get();
            $book['id'] = $entity->key()->pathEndIdentifier();
            $books[] = $book;
            $nextPageCursor = $entity->cursor();
        }

        return [
            'books' => $books,
            'cursor' => $nextPageCursor,
        ];
    }

    public function create($book, $key = null)
    {
        $this->verifyBook($book);

        $key = $this->datastore->key('Book');
        $entity = $this->datastore->entity($key, $book);

        $this->datastore->insert($entity);

        // return the ID of the created datastore entity
        return $entity->key()->pathEndIdentifier();
    }

    public function read($id)
    {
        $key = $this->datastore->key('Book', $id);
        $entity = $this->datastore->lookup($key);

        if ($entity) {
            $book = $entity->get();
            $book['id'] = $id;
            return $book;
        }

        return false;
    }

    public function update($book)
    {
        $this->verifyBook($book);

        if (!isset($book['id'])) {
            throw new \InvalidArgumentException('Book must have an "id" attribute');
        }

        $transaction = $this->datastore->transaction();
        $key = $this->datastore->key('Book', $book['id']);
        $task = $transaction->lookup($key);
        unset($book['id']);
        $entity = $this->datastore->entity($key, $book);
        $transaction->upsert($entity);
        $transaction->commit();

        // return the number of updated rows
        return 1;
    }

    public function delete($id)
    {
        $key = $this->datastore->key('Book', $id);
        return $this->datastore->delete($key);
    }

    private function verifyBook($book)
    {
        if ($invalid = array_diff_key($book, $this->columns)) {
            throw new \InvalidArgumentException(sprintf(
                'unsupported book properties: "%s"',
                implode(', ', $invalid)
            ));
        }
    }

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
// = = = > EMPLOYEES
// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    public function listEmployees($limit = 10, $cursor = null)
    {
        $query = $this->datastore->query()
            ->kind('empleados')
            ->order('name')
            ->limit($limit)
            ->start($cursor);

        $results = $this->datastore->runQuery($query);

        $employees = [];
        $nextPageCursor = null;
        foreach ($results as $entity) {
            $employee = $entity->get();
            $employee['id'] = $entity->key()->pathEndIdentifier();
            $employees[] = $employee;
            $nextPageCursor = $entity->cursor();
        }

        return [
            'employees' => $employees,
            'cursor' => $nextPageCursor,
        ];
    }

    public function createEmployee($employee, $key = null)
    {
        $this->verifyEmployee($employee);

        $key = $this->datastore->key('empleados');
        $entity = $this->datastore->entity($key, $employee);

        $this->datastore->insert($entity);

        // return the ID of the created datastore entity
        return $entity->key()->pathEndIdentifier();
    }

    public function readEmployee($id)
    {
        $key = $this->datastore->key('empleados', $id);
        $entity = $this->datastore->lookup($key);

        if ($entity) {
            $employee = $entity->get();
            $employee['id'] = $id;
            return $employee;
        }

        return false;
    }

    public function updateEmployee($employee)
    {
        $this->verifyEmployee($employee);

        if (!isset($employee['id'])) {
            throw new \InvalidArgumentException('Employee must have an "id" attribute');
        }

        $transaction = $this->datastore->transaction();
        $key = $this->datastore->key('empleados', $employee['id']);
        $task = $transaction->lookup($key);
        unset($employee['id']);
        $entity = $this->datastore->entity($key, $employee);
        $transaction->upsert($entity);
        $transaction->commit();

        // return the number of updated rows
        return 1;
    }

    public function deleteEmployee($id)
    {
        $key = $this->datastore->key('empleados', $id);
        return $this->datastore->delete($key);
    }

    private function verifyEmployee($employee)
    {
        if ($invalid = array_diff_key($employee, $this->employee_columns)) {
            throw new \InvalidArgumentException(sprintf(
                'unsupported employee properties: "%s"',
                implode(', ', $invalid)
            ));
        }
    }
}
