<?php

namespace libraries;

use Illuminate\Database\Capsule\Manager as Capsule;
use libraries\Model as Model;
use Respect\Validation\Validator as v;
use models\V2\ApiLogModel;

class Controller
{
    private $app;

    public function __construct()
    {

    }

    public function routes()
    {
        global $appConfig;

        $this->app = new \Slim\App([
            "debug" => true,
            "settings" => [
                "determineRouteBeforeAppMiddleware" => true,
                "displayErrorDetails" => (ENVIRONMENT !== "PRODUCTION" ? true : false),
            ]
        ]);

        // CORS handler
        $this->app->options('/{routes:.+}', function ($request, $response, $args) {
            return $response;
        });

        $this->app->add(function ($request, $response, $next) {

            $response = $next($request, $response);
            $uri = $request->getUri();
            $apiLog = new ApiLogModel();
            $apiLog->api_url = $uri;
            $apiLog->ipclient = $_SERVER['REMOTE_ADDR'];
            $apiLog->request = json_encode($request->getParsedBody());
            $apiLog->header = json_encode($request->getHeaders());
            $apiLog->method = json_encode($request->getMethod());
            $apiLog->status_code = json_encode($response->getStatusCode());
            $responses = $response->getBody();
            $apiLog->response = $responses;
            $apiLog->save();
            $apiLog->save();
            $header = $response->getHeaders();
            if (isset($header['Content-Type'])) {

                if ($header['Content-Type'][0] != 'application/json') {
                    if ($header['Content-Type'][0] == 'text/html; charset=UTF-8') {
                        $response = $response->withHeader('Content-Type', 'application/json');
                    } else {
                        $response = $response->withHeader('Content-Type', $header['Content-Type'][0]);
                    }
                } else {
                    $response = $response->withHeader('Content-Type', 'application/json');
                }
            } else {
                $response = $response->withHeader('Content-Type', 'application/json');
            }

            $response = $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers',
                    'X-Requested-With, Content-Type, Accept, Origin, Authorization, Process-Data')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->withHeader('X-Version', '1.0');

            return $response;
        });

        $container = $this->app->getContainer();
        $container['appConfig'] = $appConfig;
        $capsule = new Capsule;

        // Setup Elequent ORM connection
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $appConfig['DBHOST'],
            'database' => $appConfig['DBNAME'],
            'username' => $appConfig['DBUSERNAME'],
            'password' => $appConfig['DBPASSWORD'],
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        $capsule->bootEloquent();
        $capsule->setAsGlobal();

        $container = $this->app->getContainer();
        $logger = new \Monolog\Logger('MAIN');
        $file_handler = new \Monolog\Handler\StreamHandler("./storage/logs/app.log");
        $logger->pushHandler($file_handler);
        //$logger->addInfo("Something interesting happened");

        $container['db'] = function ($c) {
            $model = new Model;
            $db = $model->dbGetInstance();
            return $db;
        };
        $container['logger'] = function ($c) {
            $logger = new \Monolog\Logger('CONTAINER');
            $file_handler = new \Monolog\Handler\StreamHandler("./storage/logs/app.log");
            $logger->pushHandler($file_handler);
            return $logger;
        };

        $this->app->logger = $logger;

        // Validator Injector
        $container['validator'] = function ($c) {
            return new \helpers\Validator\Validator($this->app);
        };
        v::with('helpers\\Validator\\Rules');

        // BUGSNAG ERROR TRACKER
        if(ENVIRONMENT !== "LOCAL") {
            $bugsnag = \Bugsnag\Client::make('386c3af03aa8d1cefaf4eb11ff66a899');
            \Bugsnag\Handler::register($bugsnag);
            $bugsnag->setReleaseStage(ENVIRONMENT);
        }

        return $this->app;
    }

    public function redirectTo($name)
    {
        $url = $this->app->urlFor($name);
        $this->app->redirect($url);
    }
}

?>
