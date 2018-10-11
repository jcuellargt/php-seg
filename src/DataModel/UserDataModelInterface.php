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

/**
 * The common model implemented by Google Datastore, mysql, etc.
 */
interface UserDataModelInterface
{
    /**
     * Reads an user from the data model.
     *
     * @param $id  The email of the user to read.
     *
     * @return mixed An associative array representing the user if found.
     *               Otherwise, a false value.
     */
    public function readByEmail($email);

    /**
     * Reads the roles that belongs to the user from the data model.
     *
     * @param $userId  The id of the user.
     *
     * @return mixed An associative array representing the roles if found.
     *               Otherwise, a false value.
     */
    public function readRoles($userId);

}
