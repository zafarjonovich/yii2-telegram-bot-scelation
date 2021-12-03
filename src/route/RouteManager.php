<?php


namespace zafarjonovich\Yii2TelegramBotScelation\route;


use yii\helpers\ArrayHelper;
use zafarjonovich\Yii2TelegramBotScelation\calls\RouteManagerCall;

class RouteManager
{
    private $routes = [];

    public static function getKey($action,$method)
    {
        return sprintf('%s::%s',$action,$method);
    }

    /**
     * @param $key
     * @param $action
     * @param $method
     */
    public function add($action,$method)
    {
        $this->routes[self::getKey($action,$method)] = [
            'unique' => count($this->routes),
            'action' => $action,
            'method' => $method
        ];
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasRoute($key)
    {
        return isset($this->routes[$key]);
    }

    /**
     * @param $key
     * @param null $params
     * @return array
     * @throws \Exception
     */
    public function getShortedRoute($action,$method,$params = null)
    {
        if(!$this->hasRoute(self::getKey($action,$method))) {
            throw new \Exception('Route not found');
        }

        $route = $this->routes[self::getKey($action,$method)];

        return new RouteManagerCall([
            RouteManagerCall::ROUTE_PARAM_UNIQUE => $route['unique'],
            RouteManagerCall::ROUTE_PARAM_PARAMS => $params
        ]);
    }

    /**
     * @param $unique
     * @return mixed|null
     */
    public function getRouteByUnique($unique)
    {
        $routes = ArrayHelper::map($this->routes,'unique',function ($route) {
            return $route;
        });

        return $routes[$unique] ?? null;
    }

    /**
     * @param $route
     * @return Route
     * @throws \Exception
     */
    public function initRoute(RouteManagerCall $call)
    {
        $routeConfiguration = $this->getRouteByUnique($call->getUnique());

        return new Route($routeConfiguration,$call->getParams());
    }
}