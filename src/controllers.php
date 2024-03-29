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

namespace Google\Cloud\Samples\Bookshelf;

/*
 * Adds all the controllers to $app.  Follows Silex Skeleton pattern.
 */
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Google\Cloud\Samples\Bookshelf\DataModel\DataModelInterface;

$app->get('/', function (Request $request) use ($app) {
    return $app->redirect('/index/');
});

// [START index]
$app->get('/index/', function (Request $request) use ($app) {
    $twig = $app['twig'];
    return $twig->render('index.html.twig', array(
        'last_username' => 'hello',
        'error'         => ''
    ));
});
// [END index]

// [START login]
$app->post('/index/', function (Request $request) use ($app) {
    $model = $app['user.model'];
    $twig = $app['twig'];
    $user = $request->request->all();
    $email = $user['_email'];
    $password = $user['_password'];
    $userInfo = $model->readByEmail($email, $password);
    $session = $app['session'];
    if (!$userInfo) {
      error_log("user not found",0);
      return $twig->render('index.html.twig', array(
          'last_username' => 'hello',
          'error'         => 'Invalid Login'
      ));
    }

    $userId = $userInfo['id'];
    error_log("User ID ".$userId,0);
    $role = $model->readRoles($userId);
    $session->set('user', [
            'id'      => $userInfo['id'],
            'email'   => $userInfo['email'],
            'role'    => $role
        ]);
    error_log(" User found !  ",0);
    return $app->redirect("/home/");
});
// [END login]

# [START logout]
$app->get('/logout', function () use ($app) {
    /** @var Symfony\Component\HttpFoundation\Session\Session $session */
    $session = $app['session'];
    $session->remove('user');
    return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
})->bind('logout');
# [END logout]

// [START books]
$app->get('/home/', function (Request $request) use ($app) {
    /** @var DataModelInterface $model */
    $model = $app['bookshelf.model'];
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];
    $token = $request->query->get('page_token');
    $bookList = $model->listBooks($app['bookshelf.page_size'], $token);

    return $twig->render('list.html.twig', array(
        'books' => $bookList['books'],
        'next_page_token' => $bookList['cursor'],
    ));
});

$app->get('/books/', function (Request $request) use ($app) {
    /** @var DataModelInterface $model */
    $model = $app['bookshelf.model'];
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];
    $token = $request->query->get('page_token');
    $bookList = $model->listBooks($app['bookshelf.page_size'], $token);

    return $twig->render('list.html.twig', array(
        'books' => $bookList['books'],
        'next_page_token' => $bookList['cursor'],
    ));
});
// [END books]

// [START add]
$app->get('/books/add', function () use ($app) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    return $twig->render('form.html.twig', array(
        'action' => 'Add',
        'book' => array(),
    ));
});

$app->post('/books/add', function (Request $request) use ($app) {
    /** @var DataModelInterface $model */
    $model = $app['bookshelf.model'];
    $book = $request->request->all();
    $id = $model->create($book);

    return $app->redirect("/books/$id");
});
// [END add]

// [START show]
$app->get('/books/{id}', function ($id) use ($app) {
    /** @var DataModelInterface $model */
    $model = $app['bookshelf.model'];
    $book = $model->read($id);
    if (!$book) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    return $twig->render('view.html.twig', array('book' => $book));
});
// [END show]

// [START edit]
$app->get('/books/{id}/edit', function ($id) use ($app) {
    /** @var DataModelInterface $model */
    $model = $app['bookshelf.model'];
    $book = $model->read($id);
    if (!$book) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    return $twig->render('form.html.twig', array(
        'action' => 'Edit',
        'book' => $book,
    ));
});

$app->post('/books/{id}/edit', function (Request $request, $id) use ($app) {
    $book = $request->request->all();
    $book['id'] = $id;
    /** @var DataModelInterface $model */
    $model = $app['bookshelf.model'];
    if (!$model->read($id)) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    if ($model->update($book)) {
        return $app->redirect("/books/$id");
    }

    return new Response('Could not update book');
});
// [END edit]

// [START delete]
$app->post('/books/{id}/delete', function ($id) use ($app) {
    /** @var DataModelInterface $model */
    $model = $app['bookshelf.model'];
    $book = $model->read($id);
    if ($book) {
        $model->delete($id);

        return $app->redirect('/books/', Response::HTTP_SEE_OTHER);
    }

    return new Response('', Response::HTTP_NOT_FOUND);
});
// [END delete]

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
// = = = > EMPLOYEES
// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

// [START employees]
$app->get('/employees/', function (Request $request) use ($app) {
    /** @var DataModelInterface $model */
    $model = $app['employee.model'];
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('RRHH',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    $token = $request->query->get('page_token');
    $employeeList = $model->listEmployees($app['bookshelf.page_size'], $token);

    return $twig->render('employees_list.html.twig', array(
        'employees' => $employeeList['employees'],
        'next_page_token' => $employeeList['cursor'],
    ));
});

// [END employees]

// [START add]
$app->get('/employees/add', function () use ($app) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('RRHH',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    return $twig->render('employees_form.html.twig', array(
        'action' => 'Add',
        'employee' => array(),
    ));
});

$app->post('/employees/add', function (Request $request) use ($app) {
    /** @var DataModelInterface $model */
    $model = $app['employee.model'];
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('RRHH',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    $employee = $request->request->all();
    $id = $model->create($employee);

    return $app->redirect("/employees/$id");
});
// [END add]

// [START show]
$app->get('/employees/{id}', function ($id) use ($app) {
    /** @var DataModelInterface $model */
    $model = $app['employee.model'];
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('RRHH',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    $employee = $model->read($id);
    if (!$employee) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    return $twig->render('employees_view.html.twig', array('employee' => $employee));
});
// [END show]

// [START edit]
$app->get('/employees/{id}/edit', function ($id) use ($app) {
    /** @var DataModelInterface $model */
    $model = $app['employee.model'];
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('RRHH',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    $employee = $model->read($id);
    if (!$employee) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    return $twig->render('employees_form.html.twig', array(
        'action' => 'Edit',
        'employee' => $employee,
    ));
});

$app->post('/employees/{id}/edit', function (Request $request, $id) use ($app) {
    $employee = $request->request->all();
    $employee['id'] = $id;
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('RRHH',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    /** @var DataModelInterface $model */
    $model = $app['employee.model'];
    if (!$model->read($id)) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    if ($model->update($employee)) {
        return $app->redirect("/employees/$id");
    }

    return new Response('Could not update Employee');
});
// [END edit]

// [START delete]
$app->post('/employees/{id}/delete', function ($id) use ($app) {
    /** @var DataModelInterface $model */
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('RRHH',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    $model = $app['employee.model'];
    $employee = $model->read($id);
    if ($employee) {
        $model->delete($id);

        return $app->redirect('/employees/', Response::HTTP_SEE_OTHER);
    }

    return new Response('', Response::HTTP_NOT_FOUND);
});
// [END delete]

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
// = = = > PROVIDERS
// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

// [START providers]
$app->get('/providers/', function (Request $request) use ($app) {
    /** @var DataModelInterface $model */
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('COMPRAS',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    $model = $app['provider.model'];
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];
    $token = $request->query->get('page_token');
    $providerList = $model->listProviders($app['bookshelf.page_size'], $token);

    return $twig->render('providers_list.html.twig', array(
        'providers' => $providerList['providers'],
        'next_page_token' => $providerList['cursor'],
    ));
});
// [END providers]

// [START add]
$app->get('/providers/add', function () use ($app) {
    /** @var Twig_Environment $twig */
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('COMPRAS',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    $twig = $app['twig'];

    return $twig->render('providers_form.html.twig', array(
        'action' => 'Add',
        'provider' => array(),
    ));
});

$app->post('/providers/add', function (Request $request) use ($app) {
    /** @var DataModelInterface $model */
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('COMPRAS',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    $model = $app['provider.model'];
    $provider = $request->request->all();
    $id = $model->create($provider);

    return $app->redirect("/providers/$id");
});
// [END add]

// [START show]
$app->get('/providers/{id}', function ($id) use ($app) {
    /** @var DataModelInterface $model */
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('COMPRAS',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    $model = $app['provider.model'];
    $provider = $model->read($id);
    if (!$provider) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    return $twig->render('providers_view.html.twig', array('provider' => $provider));
});
// [END show]

// [START edit]
$app->get('/providers/{id}/edit', function ($id) use ($app) {
    /** @var DataModelInterface $model */
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('COMPRAS',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    $model = $app['provider.model'];
    $provider = $model->read($id);
    if (!$provider) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    return $twig->render('providers_form.html.twig', array(
        'action' => 'Edit',
        'provider' => $provider,
    ));
});

$app->post('/providers/{id}/edit', function (Request $request, $id) use ($app) {
    $provider = $request->request->all();
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('COMPRAS',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    $provider['id'] = $id;
    /** @var DataModelInterface $model */
    $model = $app['provider.model'];
    if (!$model->read($id)) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    if ($model->update($provider)) {
        return $app->redirect("/providers/$id");
    }

    return new Response('Could not update Provider');
});
// [END edit]

// [START delete]
$app->post('/providers/{id}/delete', function ($id) use ($app) {
    /** @var DataModelInterface $model */
    $sessionUser = $app['session']->get('user');
    if(!$sessionUser) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/']);
    }
    if(!in_array('COMPRAS',$sessionUser['role'])) {
      return new Response('', Response::HTTP_FOUND, ['Location' => '/home/']);
    }
    $model = $app['provider.model'];
    $provider = $model->read($id);
    if ($provider) {
        $model->delete($id);

        return $app->redirect('/providers/', Response::HTTP_SEE_OTHER);
    }

    return new Response('', Response::HTTP_NOT_FOUND);
});
// [END delete]
