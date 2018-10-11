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
interface ProviderDataModelInterface
{
    /**
     * Lists all the providers in the data model.
     * Cannot simply be called 'list' due to PHP keyword collision.
     *
     * @param int  $limit  How many providers will we fetch at most?
     * @param null $cursor Returned by an earlier call to listproviders().
     *
     * @return array ['providers' => array of associative arrays mapping column
     *               name to column value,
     *               'cursor' => pass to next call to listproviders() to fetch
     *               more providers]
     */
    public function listProviders($limit = 10, $cursor = null);

    /**
     * Creates a new provider in the data model.
     *
     * @param $provider array  An associative array.
     * @param null $id integer  The id, if known.
     *
     * @return mixed The id of the new provider.
     */
    public function create($provider, $id = null);

    /**
     * Reads a provider from the data model.
     *
     * @param $id  The id of the provider to read.
     *
     * @return mixed An associative array representing the provider if found.
     *               Otherwise, a false value.
     */
    public function read($id);

    /**
     * Updates a provider in the data model.
     *
     * @param $provider array  An associative array representing the provider.
     * @param null $id The old id of the provider.
     *
     * @return int The number of providers updated.
     */
    public function update($provider);

    /**
     * Deletes a provider from the data model.
     *
     * @param $id  The provider id.
     *
     * @return int The number of providers deleted.
     */
    public function delete($id);
}
