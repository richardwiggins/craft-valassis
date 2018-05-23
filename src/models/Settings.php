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

use craft\base\Model;

/**
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $username = '';

    /**
     * @var string
     */
    public $password = '';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'string'],
            [['username', 'password'], 'required'],
        ];
    }

    public function isEnabled()
    {
        return !empty($this->username) && !empty($this->password);
    }
}
