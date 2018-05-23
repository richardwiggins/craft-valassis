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

/**
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 *
 * @property int   $id
 * @property int   $siteId
 * @property array $payload
 */
class ImportModel extends BaseModel
{
    // Constants
    // =========================================================================

    const SCENARIO_UPLOAD = 'upload';
    const SCENARIO_IMPORT = 'import';

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $file;
    public $id;
    public $siteId;
    public $payload;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file', 'payload'], 'required'],
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv, txt'],
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_UPLOAD => ['file'],
            self::SCENARIO_IMPORT => ['payload'],
        ];
    }
}
