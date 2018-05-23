<?php
/**
 * Valassis plugin for Craft CMS 3.x
 *
 * Attach Valassis coupons to Contact form requests
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\valassis\services;

use superbig\valassis\models\CouponModel;
use superbig\valassis\records\CouponRecord;
use superbig\valassis\Valassis;

use Craft;
use craft\base\Component;

/**
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 */
class Coupons extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * @return array|null
     */
    public function getAllCoupons()
    {
        $coupons = CouponRecord::find()->all();

        if (!$coupons) {
            return null;
        }

        return array_map(function(CouponRecord $record) {
            return CouponModel::createFromRecord($record);
        }, $coupons);
    }

    /*
     * @return mixed
     */
    public function saveCoupon()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (Valassis::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }
}
