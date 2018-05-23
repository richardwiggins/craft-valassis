<?php
/**
 * Valassis plugin for Craft CMS 3.x
 *
 * Attach Valassis coupons to Contact form requests
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\valassis\controllers;

use superbig\supersearch\SuperSearch;
use superbig\valassis\Valassis;

use Craft;
use craft\web\Controller;

/**
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 */
class CouponController extends Controller
{

    // Protected Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->renderTemplate('valassis/coupons', [
            'coupons' => Valassis::$plugin->coupons->getAllCoupons(),
        ]);
    }

    /**
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'Welcome to the CouponController actionDoSomething() method';

        return $result;
    }
}
