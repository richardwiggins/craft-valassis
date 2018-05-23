<?php
/**
 * Valassis plugin for Craft CMS 3.x
 *
 * Attach Valassis coupons to Contact form requests
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\valassis\models;

use superbig\valassis\records\CouponRecord;
use superbig\valassis\Valassis;

use Craft;
use craft\base\Model;

/**
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 *
 * @property int    $id
 * @property int    $siteId
 * @property int    $customerId
 * @property int    $importId
 * @property string $couponPin
 * @property string $consumerId
 * @property array  $response
 */
class CouponModel extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $siteId;
    public $customerId;
    public $importId;
    public $couponPin;
    public $consumerId;
    public $response;

    public static function createFromRecord(CouponRecord $record)
    {
        $model = new static();
        $model->setAttributes($record->getAttributes());

        return $model;
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['couponPin', 'consumerId'], 'string'],
            [['couponPin', 'consumerId'], 'required'],
        ];
    }
}
