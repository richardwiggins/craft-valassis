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
     * @param string $mode
     *
     * @return array|null
     */
    public function getAllCoupons($mode = 'all')
    {
        if (!$mode || $mode === 'used') {
            $coupons = CouponRecord::find()->where(['not', ['customerId' => null]])->all();
        }
        elseif ($mode === 'unused') {
            $coupons = CouponRecord::find()->where(['customerId' => null])->all();
        }
        else {
            $coupons = CouponRecord::find()->all();
        }

        if (!$coupons) {
            return null;
        }

        return array_map(function(CouponRecord $record) {
            return CouponModel::createFromRecord($record);
        }, $coupons);
    }

    /**
     * @param string $mode
     *
     * @return int
     */
    public function getCouponsCount($mode = 'all')
    {
        if (!$mode || $mode === 'used') {
            $count = CouponRecord::find()->where(['not', ['customerId' => null]])->count();
        }
        elseif ($mode === 'unused') {
            $count = CouponRecord::find()->where(['customerId' => null])->count();
        }
        else {
            $count = CouponRecord::find()->count();
        }

        return $count;
    }

    /**
     * @param CouponModel[] $coupons
     */
    public function saveCoupons($coupons = [])
    {
        foreach ($coupons as $coupon) {
            $this->saveCoupon($coupon);
        }
    }

    /*
     * @return mixed
     */
    public function saveCoupon(CouponModel $coupon)
    {
        if ($coupon->validate() === false) {
            return false;
        }

        if ($coupon->id) {
            $couponRecord = CouponRecord::findOne($coupon->id);

            if ($couponRecord === null) {
                return false;
            }
        }
        else {
            $couponRecord = new CouponRecord();
        }

        $couponRecord->setAttributes($coupon->getAttributes(), false);

        // Save import
        if ($couponRecord->save(false) === false) {
            return false;
        }

        // Update import model ID
        $coupon->id = $couponRecord->id;

        return true;
    }
}
