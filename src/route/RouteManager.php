<?php


namespace zafarjonovich\Yii2TelegramBotScelation\route;


use yii\helpers\ArrayHelper;

class RouteManager
{
    private $routes = [];

    const ROUTE_PARAM_UNIQUE = 'u';
    const ROUTE_PARAM_PARAMS = 'p';

    /**
     * @param $key
     * @param $action
     * @param $method
     */
    public function add($key,$action,$method)
    {
        $this->routes[$key] = [
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
    public function getShortedRoute($key,$params = null)
    {
        if(!$this->hasRoute($key)) {
            throw new \Exception('Route not found');
        }

        $route = $this->routes[$key];

        return [
            self::ROUTE_PARAM_UNIQUE => $route['unique'],
            self::ROUTE_PARAM_PARAMS => $params
        ];
    }

    /**
     * @param $route
     * @return bool
     */
    public function validRoute($route)
    {
        return !isset($route[self::ROUTE_PARAM_UNIQUE],$route[self::ROUTE_PARAM_PARAMS]) ||
            $route[self::ROUTE_PARAM_UNIQUE]  >= count($this->routes);
    }

    /**
     * @param $unique
     * @return mixed|null
     */
    public function getRouteByUnique($unique)
    {
        $routes = ArrayHelper::map($routes,'unique',function ($route) {
            return $route;
        });

        return $routes[$unique] ?? null;
    }

    /**
     * @param $route
     * @return Route
     * @throws \Exception
     */
    public function initRoute($route)
    {
        if(!$this->validRoute($route)) {
            throw new \Exception('Invalid route');
        }

        $routeConfiguration = $this->getRouteByUnique($route[self::ROUTE_PARAM_UNIQUE]);

        return new Route($routeConfiguration,$route[self::ROUTE_PARAM_PARAMS]);
    }
}