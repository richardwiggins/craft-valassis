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

use craft\web\UploadedFile;
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
    public function actionImport()
    {

        return $result;
    }
}
