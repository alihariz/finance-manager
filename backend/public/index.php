<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteCollectorProxy;

use App\db;
use App\Middleware\JwtMiddleware;
use App\Controllers\AuthController;
use App\Controllers\TransactionController;

// Initialize Slim app
$app = AppFactory::create();
$secretKey = "my-secret-key"; // Use env variable in production
$unprotectedRoutes = ['/api/auth/register', '/api/auth/login'];
$jwtMiddleware = new JwtMiddleware($secretKey, $unprotectedRoutes);
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);


$app->get('/hello', function ($request, $response) {
    $response->getBody()->write("Hello, world!");
    return $response;
});

// --- Unprotected Routes (Register & Login) ---
$app->post('/api/auth/register', function (Request $request, Response $response) use ($secretKey) {
    try {
        $registrationData = json_decode($request->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($registrationData)) {
            $errorBody = json_encode(['error' => 'Invalid JSON data provided.']);
            $response->getBody()->write($errorBody);
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $database = new db();
        $authController = new AuthController($database, $secretKey);
        $result = $authController->register($registrationData);
        $response->getBody()->write(json_encode($result));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    } catch (\Throwable $e) {
        $errorBody = json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        $response->getBody()->write($errorBody);
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->post('/api/auth/login', function (Request $request, Response $response) use ($secretKey) {
    try {
        $credentials = json_decode($request->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($credentials)) {
            $errorBody = json_encode(['error' => 'Invalid JSON data provided for login.']);
            $response->getBody()->write($errorBody);
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $database = new db();
        $authController = new AuthController($database, $secretKey);
        $result = $authController->login($credentials);
        $response->getBody()->write(json_encode($result));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    } catch (\Throwable $e) {
        $errorBody = json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        $response->getBody()->write($errorBody);
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

// --- Protected Routes (JWT required) ---
$app->group('/api', function (RouteCollectorProxy $group) {
    // --- Transaction routes ---
    $group->get('/transactions', function (Request $request, Response $response) {
        $database = new db();
        $controller = new TransactionController($database);
        $user = $request->getAttribute('user');
        $transactions = $controller->index($user->sub);
        $response->getBody()->write(json_encode($transactions));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    });

    $group->get('/transactions/{id}', function (Request $request, Response $response, array $args) {
        $database = new db();
        $controller = new TransactionController($database);
        $user = $request->getAttribute('user');
        $id = (int)$args['id'];
        $transaction = $controller->getById($user->sub, $id);
        if ($transaction) {
            $response->getBody()->write(json_encode($transaction));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['error' => 'Transaction not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    });

    $group->post('/transactions', function (Request $request, Response $response) {
        $database = new db();
        $controller = new TransactionController($database);
        $user = $request->getAttribute('user');
        $data = json_decode($request->getBody()->getContents(), true);
        $result = $controller->store($user->sub, $data);
        $response->getBody()->write(json_encode($result));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    });

    $group->put('/transactions/{id}', function (Request $request, Response $response, array $args) {
        $database = new db();
        $controller = new TransactionController($database);
        $user = $request->getAttribute('user');
        $id = (int)$args['id'];
        $data = json_decode($request->getBody()->getContents(), true);
        $result = $controller->update($user->sub, $id, $data);
        $response->getBody()->write(json_encode($result));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    });

    $group->delete('/transactions/{id}', function (Request $request, Response $response, array $args) {
        $database = new db();
        $controller = new TransactionController($database);
        $user = $request->getAttribute('user');
        $id = (int)$args['id'];
        $result = $controller->destroy($user->sub, $id);
        $response->getBody()->write(json_encode($result));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    });

})->add($jwtMiddleware);

// --- Global CORS Middleware ---
$app->add(function (Request $request, RequestHandler $handler): Response {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->run();