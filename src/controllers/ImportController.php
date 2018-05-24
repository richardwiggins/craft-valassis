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

use craft\helpers\UrlHelper;
use craft\web\UploadedFile;
use superbig\valassis\models\CouponModel;
use superbig\valassis\models\ImportModel;
use superbig\valassis\Valassis;

use Craft;
use craft\web\Controller;

/**
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 */
class ImportController extends Controller
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
        return $this->renderTemplate('valassis/import/index', [
            'imports' => Valassis::$plugin->imports->getAllImports(),
        ]);
    }

    /**
     * @return mixed
     */
    public function actionNew()
    {
        return $this->renderTemplate('valassis/import/new', [
            //'imports' => Valassis::$plugin->imports->getAllImports(),
        ]);
    }

    /**
     * @return mixed
     */
    public function actionDetails(int $id = null)
    {
        return $this->renderTemplate('valassis/import/details', [
            'import' => Valassis::$plugin->imports->getImportById($id),
        ]);
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpload()
    {
        $this->requirePostRequest();

        $import           = new ImportModel();
        $import->scenario = ImportModel::SCENARIO_UPLOAD;
        $import->file     = UploadedFile::getInstanceByName('couponFile');

        if (!$import->validate(['file'])) {
            return $this->asJson([
                'success' => false,
                'errors'  => $import->getErrors(),
            ]);
        }

        $tempPath = $import->file->saveAsTempFile();

        if (!$tempPath) {
            $import->addError('file', $import->file->error);

            return $this->asJson([
                'success' => false,
                'errors'  => $import->getErrors(),
            ]);
        }

        $csv = array_map(function($input) {
            return str_getcsv($input, "\t");
        }, file($tempPath));

        $csv = array_filter($csv, function($line) {
            return strlen($line[0]) === 25;
        });

        try {
            $html = Craft::$app->getView()->renderTemplate('valassis/import/response-table', ['data' => $csv]);
        } catch (\Exception $e) {
            $html = null;
        }

        return $this->asJson([
            'success' => true,
            'result'  => $csv,
            'html'    => $html,
        ]);
    }

    /**
     * @return mixed
     */
    public function actionSave()
    {
        $coupons          = Craft::$app->getRequest()->getRequiredParam('coupons');
        $import           = new ImportModel();
        $import->scenario = ImportModel::SCENARIO_IMPORT;
        $import->payload  = $coupons;
        $import->siteId   = Craft::$app->getSites()->getCurrentSite()->id;

        $imported = Valassis::$plugin->imports->saveImport($import);

        if ($imported) {
            $coupons = array_map(function($row) use ($import) {
                $coupon           = CouponModel::createFromImport($row);
                $coupon->importId = $import->id;

                return $coupon;
            }, $coupons);
        }

        Valassis::$plugin->coupons->saveCoupons($coupons);

        return $this->redirect(UrlHelper::cpUrl('valassis/imports'));

        return $this->redirect(UrlHelper::cpUrl('valassis/imports/' . $import->id));
    }
}
