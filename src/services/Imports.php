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

use superbig\valassis\models\CouponModel;
use superbig\valassis\models\ImportModel;
use superbig\valassis\records\CouponRecord;
use superbig\valassis\records\ImportRecord;
use superbig\valassis\Valassis;

use Craft;
use craft\base\Component;

/**
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 */
class Imports extends Component
{
    // Public Methods
    // =========================================================================

    public function getAllImports()
    {
        $importRecords = ImportRecord::find()->with('coupons')->all();

        return ImportModel::populateModels($importRecords, false);
    }

    public function getImportById($id = null)
    {
        /** @var ImportRecord $importRecord */
        $importRecord = ImportRecord::find()->with('coupons')->where(['id' => $id])->limit(1)->one();

        /** @var ImportModel $model */
        $model          = ImportModel::populateModel($importRecord, false);
        $model->coupons = array_map(function(CouponRecord $coupon) {
            return CouponModel::createFromRecord($coupon);
        }, $importRecord->coupons);

        return $model;
    }

    /**
     * @param $id
     *
     * @return int
     */
    public function deleteById($id)
    {
        $importRecord = ImportRecord::deleteAll(['id' => $id]);

        return $importRecord;
    }

    /*
     * @return mixed
     */
    public function saveImport(ImportModel &$import)
    {
        if ($import->validate() === false) {
            return false;
        }

        if ($import->id) {
            $importRecord = ImportRecord::findOne($import->id);

            if ($importRecord === null) {
                return false;
            }
        }
        else {
            $importRecord = new ImportRecord();
        }

        $importRecord->setAttributes($import->getAttributes(), false);

        // Save import
        if ($importRecord->save(false) === false) {
            return false;
        }

        // Update import model ID
        $import->id = $importRecord->id;

        return true;
    }
}
