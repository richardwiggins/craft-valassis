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
 * @property int   $id
 * @property int   $siteId
 * @property array $payload
 * @property array $coupons
 */
class ImportRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    public $relationships = ['coupons'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%valassis_imports}}';
    }

    public function getCoupons()
    {
        return $this->hasMany(CouponRecord::class, ['importId' => 'id']);
    }
}
