<?php
/**
 * Valassis plugin for Craft CMS 3.x
 *
 * Attach Valassis coupons to Contact form requests
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\valassis\records;

use superbig\valassis\Valassis;

use Craft;
use craft\db\ActiveRecord;

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
 * @property mixed  $customer
 * @property mixed  $import
 * @property array  $response
 */
class CouponRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%valassis_coupons}}';
    }

    public function getImport()
    {
        return $this->hasOne(ImportRecord::class, ['id' => 'importId']);
    }

    public function getCustomer()
    {
        return $this->hasOne(CustomerRecord::class, ['id' => 'customerId']);

    }
}
