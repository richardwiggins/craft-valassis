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

use JasonGrimes\Paginator;
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
        $mode         = Craft::$app->getRequest()->getParam('mode', 'all');
        $currentPage  = Craft::$app->getRequest()->getParam('page', 1);
        $count        = [
            'all'    => Valassis::$plugin->coupons->getCouponsCount('all'),
            'unused' => Valassis::$plugin->coupons->getCouponsCount('unused'),
            'used'   => Valassis::$plugin->coupons->getCouponsCount('used'),
        ];
        $itemsPerPage = 30;
        $offset       = ($currentPage - 1) * $itemsPerPage;
        $urlPattern   = '/admin/valassis/coupons?page=(:num)&mode=' . $mode;
        $paginator    = new Paginator($count[ $mode ], $itemsPerPage, $currentPage, $urlPattern);

        return $this->renderTemplate('valassis/coupons/index', [
            'mode'      => $mode,
            'count'     => $count,
            'paginator' => $paginator,
            'coupons'   => Valassis::$plugin->coupons->getAllCoupons($mode, $offset, $itemsPerPage),
        ]);
    }

    /**
     * @param int|null $id
     *
     * @return mixed
     */
    public function actionDetails(int $id = null)
    {
        return $this->renderTemplate('valassis/coupons/details', [
            'coupon' => Valassis::$plugin->coupons->getCouponById($id),
        ]);
    }
}
