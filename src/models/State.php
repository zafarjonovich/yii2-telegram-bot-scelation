<?php

namespace zafarjonovich\Yii2TelegramBotScelation\models;

/**
 * This is the model class for table "state".
 *
 * @property int $id
 * @property int $chat_id
 * @property string $state
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $status
*/
class State extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;

    const STATUS_DEACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'yii2_telegram_bot_scelation_state';
    }
}