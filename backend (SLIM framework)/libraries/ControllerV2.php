<?php
namespace libraries;

use helpers\JWT;
use Illuminate\Database\Capsule\Manager as Capsule;
use libraries\Model as Model;
use middleware\ApiLog;
use models\Affiliate\AffiliateUserModel as AffiliateUser;
use models\Cms\CmsUserModel as CmsUser;
use models\Internal\InternalUserModel as InternalUser;
use Respect\Validation\Validator as v;

class ControllerV2
{
    protected $app;

    protected $config;

    public function __construct()
    {
        global $appConfig;
        $this->config = $appConfig;
        $this->app = new \Slim\App([
            "debug" => true,
            "settings" => [
                "determineRouteBeforeAppMiddleware" => true,
                "displayErrorDetails" => (ENVIRONMENT !== "PRODUCTION" ? true : false),
            ]
        ]);

        // CORS Setting And Preflight Handler
        $this->app->options('/{routes:.+}', function ($request, $response, $args) {
            return $response;
        });

        $this->app->add(function ($request, $response, $next) {
            $response = $next($request, $response);

            $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers',
            'X-Requested-With, Content-Type, Accept, Origin, Authorization, Process-Data')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('X-Version', '2.0');

            return $response;
        });

        // Detect if auth parameter is exist in url, so auth strategy will looking jwt token on url
        $this->app->add(function ($request, $response, $next) {
            $token = $request->getQueryParams();
            if (in_array('auth', $token) || !empty($token['auth'])) {
                $request = $request->withHeader("Authorization", "Bearer {$token['auth']}");
            }
            return $next($request, $response);
        });

        // Auto Redirect Trailing Slash
        $this->app->add(function ($request, $response, callable $next) {
            $uri = $request->getUri();
            $path = $uri->getPath();
            if ($path != '/' && substr($path, -1) == '/') {
                // recursively remove slashes when its more than 1 slash
                while (substr($path, -1) == '/') {
                    $path = substr($path, 0, -1);
                }

                // permanently redirect paths with a trailing slash
                // to their non-trailing counterpart
                $uri = $uri->withPath($path);

                if ($request->getMethod() == 'GET') {
                    return $response->withRedirect((string)$uri, 301);
                } else {
                    return $next($request->withUri($uri), $response);
                }
            }

            $response = $next($request, $response);
            return $response;
        });


    }

    public function routes()
    {
        $container = $this->app->getContainer();
        $container['appConfig'] = $this->config;

        // Eloquent Dependency Injection
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $this->config['DBHOST'],
            'database' => $this->config['DBNAME'],
            'username' => $this->config['DBUSERNAME'],
            'password' => $this->config['DBPASSWORD'],
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        $capsule->bootEloquent();
        $capsule->setAsGlobal();

        // PDO Connector Dependency Injection
        $container['db'] = function ($c) {
            $model = new Model;
            return $model->dbGetInstance();
        };

        // Logger Dependency Injection
        $container['logger'] = function ($c) {
            $logger = new \Monolog\Logger('CONTAINER');
            $file_handler = new \Monolog\Handler\StreamHandler("./storage/logs/app.log");
            $logger->pushHandler($file_handler);
            return $logger;
        };

        // Validator Dependency Injector
        $container['validator'] = function ($c) {
            return new \helpers\Validator\Validator($this->app);
        };

        v::with('helpers\\Validator\\Rules');

        // Twig Engine Dependency Injectior
        $container['view'] = function ($container) {
            $view = new \Slim\Views\Twig('./html/templates', [
                'cache' => false
            ]);

            // Instantiate and add Slim specific extension
            $router = $container->get('router');
            $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
            $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

            return $view;
        };

        // BUGSNAG ERROR TRACKER
        if(ENVIRONMENT !== "LOCAL") {
            $bugsnag = \Bugsnag\Client::make('386c3af03aa8d1cefaf4eb11ff66a899');
            \Bugsnag\Handler::register($bugsnag);
            $bugsnag->setReleaseStage(ENVIRONMENT);
        }

        $this->app->add(new ApiLog());

        return $this->app;
    }

    public function jwtMiddleWare()
    {
        return new \Slim\Middleware\JwtAuthentication([
            "attribute" => "decoded_token_data",
            "secret" => $this->config['JWT_SECRET'],
            "algorithm" => ["HS256"],
            "secure" => true,
            // "secure" => false,
            "relaxed" => ["localhost"],
            "callback" => function ($request, $response, $arguments) {
                // get bearer token from request request
                $token = JWT::getTokenFromRequest($request);
                $scope = (isset(JWT::decodeToken($token)->scope) ? JWT::decodeToken($token)->scope : null);
                $isValidToken = null;

                if ($scope == "cms") {
                    $isValidToken = CmsUser::where('jwt_token', $token)->exists();
                }


                if ($isValidToken) {
                    return true;
                }


                return false;

            },
            "error" => function ($request, $response, $arguments) {
                $data["success"] = false;
                $data["message"] = $arguments["message"];

                // remove jwt token from database if user token is expired
                if ($arguments["message"] == 'Expired token') {
                    JWT::removeToken($arguments["token"]);
                }

                // return error message if token is not belong to any user
                if ($arguments["message"] == 'Callback returned false') {
                    $data["message"] = "Session expired, please re-login";
                }

                return $response
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            }
        ]);
    }

}
