<?php
/**
 * Valassis plugin for Craft CMS 3.x
 *
 * Attach Valassis coupons to Contact form requests
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\valassis\variables;

use superbig\valassis\models\CustomerModel;
use superbig\valassis\Valassis;

use Craft;

/**
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 */
class ValassisVariable
{
    // Public Methods
    // =========================================================================

    /**
     * @param string $email
     * @param string $name
     *
     * @return string
     * @throws \craft\errors\SiteNotFoundException
     */
    public function createCouponForCustomer($email = '', $name = '')
    {
        $customer         = new CustomerModel();
        $customer->email  = $email;
        $customer->name   = $name;
        $customer->siteId = Craft::$app->getSites()->getCurrentSite()->id;

        return Valassis::$plugin->coupons->createCouponForCustomer($customer);
    }
}
