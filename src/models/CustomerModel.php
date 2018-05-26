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

use superbig\valassis\base\BaseModel;
use superbig\valassis\Valassis;

use Craft;
use craft\base\Model;

/**
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 *
 * @property int    $id
 * @property int    $siteId
 * @property string $name
 * @property string $email
 */
class CustomerModel extends BaseModel
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $id;
    public $siteId;
    public $name  = '';
    public $email = '';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['email', 'email'],
        ];
    }
}
