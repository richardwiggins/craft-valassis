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

use superbig\valassis\models\CustomerModel;
use superbig\valassis\records\CustomerRecord;
use superbig\valassis\Valassis;

use Craft;
use craft\base\Component;

/**
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 */
class Customers extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * @param null $id
     *
     * @return CustomerModel
     */
    public function getCustomerById($id = null)
    {
        /** @var CustomerRecord $customerRecord */
        $customerRecord = CustomerRecord::find()->with('coupons')->where(['id' => $id])->limit(1)->one();

        /** @var CustomerModel $model */
        $model = CustomerModel::populateModel($customerRecord, false);

        return $model;
    }

    /**
     * @param null $email
     *
     * @return CustomerModel
     */
    public function getCustomerByEmail($email = null)
    {
        /** @var CustomerRecord $customerRecord */
        $customerRecord = CustomerRecord::find()->where(['email' => $email])->limit(1)->one();

        if (!$customerRecord) {
            return null;
        }

        /** @var CustomerModel $model */
        $model = CustomerModel::populateModel($customerRecord, false);

        return $model;
    }

    /*
     * @return mixed
     */
    public function saveCustomer(CustomerModel $customer)
    {
        if ($customer->validate() === false) {
            return false;
        }

        if ($customer->id) {
            $customerRecord = CustomerRecord::findOne($customer->id);

            if ($customerRecord === null) {
                return false;
            }
        }
        else {
            $customerRecord = new CustomerRecord();
        }

        $customerRecord->setAttributes($customer->getAttributes(), false);

        // Save import
        if ($customerRecord->save(false) === false) {
            return false;
        }

        // Update import model ID
        $customer->id = $customerRecord->id;

        return true;
    }
}
