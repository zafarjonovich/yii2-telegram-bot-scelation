<?php

namespace zafarjonovich\Yii2TelegramBotScelation\calls;

use zafarjonovich\Telegram\helpers\Call;

/**
 * Class RouteManagerCall
 * @package zafarjonovich\Yii2TelegramBotScelation\calls
 *
 * @method mixed getUnique()
 * @method mixed getParams()
 */

class RouteManagerCall extends Call
{
    const ROUTE_PARAM_UNIQUE = 'u';
    const ROUTE_PARAM_PARAMS = 'p';

    public static function functionNameConstants()
    {
        return [
            'getUnique' => self::ROUTE_PARAM_UNIQUE,
            'getParams' => self::ROUTE_PARAM_PARAMS
        ];
    }

    public static function needPropertyKeys()
    {
        return [
            self::ROUTE_PARAM_PARAMS,
            self::ROUTE_PARAM_UNIQUE
        ];
    }
}