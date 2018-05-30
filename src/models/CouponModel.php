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

use craft\helpers\UrlHelper;
use superbig\valassis\base\BaseModel;
use superbig\valassis\records\CouponRecord;
use superbig\valassis\Valassis;

use Craft;
use craft\base\Model;

/**
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 *
 * @property int                     $id
 * @property int                     $siteId
 * @property int                     $customerId
 * @property int                     $importId
 * @property string                  $couponPin
 * @property string                  $consumerId
 * @property string                  $url
 * @property mixed                   $cpEditUrl
 * @property null|\craft\models\Site $site
 * @property array                   $response
 */
class CouponModel extends BaseModel
{
    // Public Properties
    // =========================================================================

    public  $id;
    public  $siteId;
    public  $customerId;
    public  $importId;
    public  $couponPin;
    public  $consumerId;
    public  $response;
    private $_site;
    private $_customer;

    /**
     * @return string
     */
    public function getUrl()
    {
        return 'helo';
    }

    /**
     * @param CouponRecord $record
     *
     * @return static
     */
    public static function createFromRecord(CouponRecord $record)
    {
        $model = new static();
        $model->setAttributes($record->getAttributes(), false);

        return $model;
    }

    /**
     * @param $row
     *
     * @return static
     */
    public static function createFromImport($row)
    {
        $model = new static();
        //$model->siteId = Craft::$app->getSites()->getCurrentSite()->id;
        $model->setAttributes($row, false);

        return $model;
    }

    /**
     * @return CustomerModel
     */
    public function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = Valassis::$plugin->customers->getCustomerById($this->customerId);
        }

        return $this->_customer;
    }

    /**
     * @param CustomerModel $customer
     */
    public function setCustomer(CustomerModel $customer)
    {
        $this->_customer = $customer;
    }

    // Public Methods
    // =========================================================================

    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl('valassis/coupons/' . $this->id);
    }

    /**
     * @return \craft\models\Site|null
     */
    public function getSite()
    {
        if (!$this->_site) {
            return Craft::$app->getSites()->getSiteById($this->siteId) ?? null;
        }

        return $this->_site;
    }

    /**
     * @return array
     */
    public function getCouponPayload()
    {
        return [
            "consumerId"        => $this->consumerId,
            "remoteConsumerId"  => $this->consumerId,
            "CouponDescription" => $this->getCustomer()->name,
            "Barcode"           => "itf:{$this->couponPin},Ean13:ccode",
            "Type"              => "n",
            "returnUrl"         => Valassis::$plugin->getSettings()->returnUrl,
            "synSrc"            => "9Wq2ieCwsNA%3D",
            "theme"             => null,
        ];
    }

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
