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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use superbig\valassis\models\CouponModel;
use superbig\valassis\models\CustomerModel;
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
     * @param null   $offset
     * @param int    $limit
     *
     * @return array|null
     */
    public function getAllCoupons($mode = 'all', $offset = null, $limit = 20)
    {
        $query = CouponRecord::find();

        if ($offset !== null) {
            $query = $query
                ->offset($offset)
                ->limit($limit);
        }

        if (!$mode || $mode === 'used') {
            $coupons = $query->where(['not', ['customerId' => null]])->all();
        }
        elseif ($mode === 'unused') {
            $coupons = $query->where(['customerId' => null])->all();
        }
        else {
            $coupons = $query->all();
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
     * @param null $id
     *
     * @return CouponModel
     */
    public function getCouponById($id = null)
    {
        /** @var CouponRecord $couponRecord */
        $couponRecord = CouponRecord::find()->with('customer')->where(['id' => $id])->limit(1)->one();

        /** @var CouponModel $model */
        $model = CouponModel::populateModel($couponRecord, false);

        return $model;
    }

    public function getFreeCoupon($siteId = null)
    {
        $record = CouponRecord::find()->where([
            'customerId' => null,
            'siteId'     => $siteId,
        ])->limit(1)->one();

        /** @var CouponModel $model */
        $model = CouponModel::populateModel($record, false);

        return $model;
    }

    /**
     * @param CustomerModel $customer
     * @param null          $siteId
     *
     * @return bool|CouponModel
     * @throws \craft\errors\SiteNotFoundException
     */
    public function createCouponForCustomer(CustomerModel $customer, $siteId = null)
    {
        $existingCustomer = Valassis::$plugin->customers->getCustomerByEmail($customer->email);

        if (!$siteId) {
            $siteId = Craft::$app->getSites()->getCurrentSite()->id;
        }

        if ($existingCustomer) {
            return false;
        }

        $nextAvailableCoupon = $this->getFreeCoupon($siteId);

        if (!$nextAvailableCoupon) {
            // @todo log/notify about no available coupons for site?
            return false;
        }

        $nextAvailableCoupon->setCustomer($customer);
        $response = $this->getBarcodeFromValassis($nextAvailableCoupon);

        if ($response) {
            Valassis::$plugin->customers->saveCustomer($customer);

            $nextAvailableCoupon->customerId = $customer->id;

            $this->saveCoupon($nextAvailableCoupon);
        }

        return $nextAvailableCoupon;
    }

    /**
     * @param CouponModel $coupon
     *
     * @return bool
     */
    public function getBarcodeFromValassis(CouponModel &$coupon)
    {
        $settings = Valassis::$plugin->getSettings();
        $client   = new Client();

        try {
            $response = $client->post($settings->printUrl, [
                'auth' => $settings->getAuthPair(),
                'json' => [$coupon->getCouponPayload()],
            ]);

            $coupon->response = json_decode((string)$response->getBody(), true);
        } catch (RequestException $e) {
            return false;
        }

        return true;
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
