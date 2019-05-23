<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\controllers\base
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\controllers\base;

use lispa\amos\core\controllers\CrudController;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\record\Record;
use lispa\amos\organizzazioni\models\ProfiloSedi;
use lispa\amos\organizzazioni\models\ProfiloSediLegal;
use lispa\amos\organizzazioni\models\ProfiloSediOperative;
use lispa\amos\organizzazioni\Module;
use lispa\amos\organizzazioni\utility\OrganizzazioniUtility;
use Yii;
use yii\helpers\Url;

/**
 * Class ProfiloController
 * ProfiloController implements the CRUD actions for Profilo model.
 *
 * @property \lispa\amos\organizzazioni\models\Profilo $model
 * @property \lispa\amos\organizzazioni\models\search\ProfiloSearch $modelSearch
 *
 * @package lispa\amos\organizzazioni\controllers\base
 */
class ProfiloController extends CrudController
{
    /**
     * @var Module|null $organizzazioniModule
     */
    public $organizzazioniModule = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->organizzazioniModule = Yii::$app->getModule(Module::getModuleName());

        $model = $this->organizzazioniModule->createModel('Profilo');
        $modelSearch = $this->organizzazioniModule->createModel('ProfiloSearch');

        $this->setModelObj($model);
        $this->setModelSearch($modelSearch);

        $this->viewGrid = [
            'name' => 'grid',
            'label' => AmosIcons::show('view-list-alt') . Html::tag('p', Module::t('amoscore', 'Table')),
            'url' => '?currentView=grid'
        ];

//        $this->viewIcon = [
//            'name' => 'icon',
//            'label' => AmosIcons::show('grid') . Html::tag('p', Module::tHtml('amoscore', 'Icon')),
//            'url' => '?currentView=icon'
//        ];

        $defaultViews = [
            'grid' => $this->viewGrid,
//            'icon' => $this->viewIcon,
        ];
        $availableViews = [];
        foreach ($this->organizzazioniModule->defaultListViews as $view) {
            if (isset($defaultViews[$view])) {
                $availableViews[$view] = $defaultViews[$view];
            }
        }

        $this->setAvailableViews($availableViews);

        parent::init();
    }

    /**
     * Lists all Profilo models.
     *
     * @param string|null $layout
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionIndex($layout = null)
    {
        Url::remember();
        $this->setDataProvider($this->getModelSearch()->search(Yii::$app->request->getQueryParams()));
        return parent::actionIndex();
    }

    /**
     * Displays a single Profilo model.
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionView($id)
    {
        $this->model = $this->findModel($id);
        return $this->render('view', ['model' => $this->model]);
    }

    /**
     * Creates a new Profilo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $this->setUpLayout('form');
        $this->model = $this->organizzazioniModule->createModel('Profilo');

        // Model for operative headquarter
        /** @var ProfiloSediOperative $mainOperativeHeadquarter */
        $mainOperativeHeadquarter = $this->organizzazioniModule->createModel('ProfiloSediOperative');
        $mainOperativeHeadquarter->setScenario(ProfiloSedi::SCENARIO_CREATE);
        $mainOperativeHeadquarter->is_main = 1;

        // Model for legal headquarter
        /** @var ProfiloSediLegal $mainLegalHeadquarter */
        $mainLegalHeadquarter = $this->organizzazioniModule->createModel('ProfiloSediLegal');
        $mainLegalHeadquarter->setScenario(ProfiloSedi::SCENARIO_CREATE);
        $mainLegalHeadquarter->is_main = 1;

        // Load and validate all form models
        $post = Yii::$app->request->post();
        $modelLoadValidate = $this->model->load($post) && $this->model->validate();
        if ($post && !$this->organizzazioniModule->oldStyleAddressEnabled) {
            // Copy Profilo model address values into respective operative and legal headquarter address fields.
            $mainOperativeHeadquarter->address = $this->model->mainOperativeHeadquarterAddress;
            $mainLegalHeadquarter->address = $this->model->mainLegalHeadquarterAddress;
        }
        $mainOperativeHeadquarterLoadValidate = $mainOperativeHeadquarter->load($post) && $mainOperativeHeadquarter->validate();
        if ($this->model->la_sede_legale_e_la_stessa_del) {
            $mainLegalHeadquarter = OrganizzazioniUtility::copyOperativeToLegalHeadquarterValues($mainOperativeHeadquarter, $mainLegalHeadquarter);
            $mainLegalHeadquarterLoadValidate = $mainLegalHeadquarter->validate();
        } else {
            $mainLegalHeadquarterLoadValidate = $mainLegalHeadquarter->load($post) && $mainLegalHeadquarter->validate();
        }

        if (
            $modelLoadValidate &&
            $mainLegalHeadquarterLoadValidate &&
            $mainOperativeHeadquarterLoadValidate
        ) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $ok = $this->model->save();
                if ($ok) {

                    // Save operative headquarter
                    $okMainSedeOperativa = $this->saveMainSede($mainOperativeHeadquarter, Module::t('amosorganizzazioni', 'Error while saving operative headquarter'));

                    // Save legal headquarter
                    $okMainSedeLegale = $this->saveMainSede($mainLegalHeadquarter, Module::t('amosorganizzazioni', 'Error while saving legal headquarter'));

                    if (
                        $okMainSedeOperativa &&
                        $okMainSedeLegale
                    ) {
                        $transaction->commit();
                        Yii::$app->getSession()->addFlash('success', Module::t('amoscore', 'Item created'));
                        return $this->redirect(['index']);
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    $transaction->rollBack();
                    Yii::$app->getSession()->addFlash('danger', Module::t('amoscore', 'Item not created, check data'));
                }
            } catch (\Exception $exception) {
                $transaction->rollBack();
            }
        }

        return $this->render('create', [
            'model' => $this->model,
            'mainLegalHeadquarter' => $mainLegalHeadquarter,
            'mainOperativeHeadquarter' => $mainOperativeHeadquarter,
            'fid' => null,
            'dataField' => null,
            'dataEntity' => null,
        ]);
    }

    /**
     * Creates a new Profilo model by ajax request.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @param string $fid
     * @param string $dataField
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreateAjax($fid, $dataField)
    {
        $this->setUpLayout('form');

        $this->model = $this->organizzazioniModule->createModel('Profilo');

        if (\Yii::$app->request->isAjax && $this->model->load(Yii::$app->request->post()) && $this->model->validate()) {
            if ($this->model->save()) {
                return json_encode($this->model->toArray());
            } else {
                return $this->renderAjax('_formAjax', [
                    'model' => $this->model,
                    'fid' => $fid,
                    'dataField' => $dataField
                ]);
            }
        }

        return $this->renderAjax('_formAjax', [
            'model' => $this->model,
            'fid' => $fid,
            'dataField' => $dataField
        ]);
    }

    /**
     * Updates an existing Profilo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $this->setUpLayout('form');

        $this->model = $this->findModel($id);

        // Model for operative headquarter
        $mainOperativeHeadquarter = $this->model->operativeHeadquarter;
        if (is_null($mainOperativeHeadquarter)) {
            /** @var ProfiloSediOperative $mainOperativeHeadquarter */
            $mainOperativeHeadquarter = $this->organizzazioniModule->createModel('ProfiloSediOperative');
            $mainOperativeHeadquarter->setScenario(ProfiloSedi::SCENARIO_CREATE);
            $mainOperativeHeadquarter->profilo_id = $this->model->id;
            $mainOperativeHeadquarter->is_main = 1;
        } elseif (!$this->organizzazioniModule->oldStyleAddressEnabled) {
            $this->model->mainOperativeHeadquarterAddress = $mainOperativeHeadquarter->address;
        }

        // Model for legal headquarter
        $mainLegalHeadquarter = $this->model->legalHeadquarter;
        if (is_null($mainLegalHeadquarter)) {
            /** @var ProfiloSediLegal $mainLegalHeadquarter */
            $mainLegalHeadquarter = $this->organizzazioniModule->createModel('ProfiloSediLegal');
            $mainLegalHeadquarter->setScenario(ProfiloSedi::SCENARIO_CREATE);
            $mainLegalHeadquarter->profilo_id = $this->model->id;
            $mainLegalHeadquarter->is_main = 1;
        } elseif (!$this->organizzazioniModule->oldStyleAddressEnabled) {
            $this->model->mainLegalHeadquarterAddress = $mainLegalHeadquarter->address;
        }

        // Load and validate all form models
        $post = Yii::$app->request->post();
        $modelLoadValidate = $this->model->load($post) && $this->model->validate();
        if ($post && !$this->organizzazioniModule->oldStyleAddressEnabled) {
            $mainOperativeHeadquarter->address = $this->model->mainOperativeHeadquarterAddress;
            $mainLegalHeadquarter->address = $this->model->mainLegalHeadquarterAddress;
        }
        $mainOperativeHeadquarterLoadValidate = $mainOperativeHeadquarter->load($post) && $mainOperativeHeadquarter->validate();
        if ($this->model->la_sede_legale_e_la_stessa_del) {
            $skipColumns = ['profilo_sedi_type_id', 'profilo_id', 'id'];
            $mainLegalHeadquarter = OrganizzazioniUtility::copyOperativeToLegalHeadquarterValues($mainOperativeHeadquarter, $mainLegalHeadquarter, $skipColumns);
            $mainLegalHeadquarterLoadValidate = $mainLegalHeadquarter->validate();
        } else {
            $mainLegalHeadquarterLoadValidate = $mainLegalHeadquarter->load($post) && $mainLegalHeadquarter->validate();
        }

        if (
            $modelLoadValidate &&
            $mainLegalHeadquarterLoadValidate &&
            $mainOperativeHeadquarterLoadValidate
        ) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $ok = $this->model->save();
                if ($ok) {

                    // Save operative headquarter
                    $okMainSedeOperativa = $this->saveMainSede($mainOperativeHeadquarter, Module::t('amosorganizzazioni', 'Error while saving operative headquarter'));

                    // Save legal headquarter
                    $okMainSedeLegale = $this->saveMainSede($mainLegalHeadquarter, Module::t('amosorganizzazioni', 'Error while saving legal headquarter'));

                    if (
                        $okMainSedeOperativa &&
                        $okMainSedeLegale
                    ) {
                        $transaction->commit();
                        Yii::$app->getSession()->addFlash('success', Module::t('amoscore', 'Item updated'));
                        return $this->redirect(['update', 'id' => $this->model->id]);
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    $transaction->rollBack();
                    Yii::$app->getSession()->addFlash('danger', Module::t('amoscore', 'Item not updated, check data'));
                }
            } catch (\Exception $exception) {
                $transaction->rollBack();
                Yii::$app->getSession()->addFlash('danger', Module::t('amosorganizzazioni', '#error_while_saving'));
            }
        }

        return $this->render('update', [
            'model' => $this->model,
            'mainLegalHeadquarter' => $mainLegalHeadquarter,
            'mainOperativeHeadquarter' => $mainOperativeHeadquarter,
            'fid' => null,
            'dataField' => null,
            'dataEntity' => null,
        ]);
    }

    /**
     * @param ProfiloSedi $mainSede
     * @param string $errorMsg
     * @return bool
     */
    protected function saveMainSede($mainSede, $errorMsg)
    {
        if ($mainSede->isNewRecord) {
            $mainSede->setScenario(Record::SCENARIO_DEFAULT);
            $mainSede->profilo_id = $this->model->id;
        }
        $mainSede->name = $this->model->name;
        $ok = $mainSede->save();
        if (!$ok) {
            Yii::$app->getSession()->addFlash('danger', $errorMsg);
        }
        return $ok;
    }

    /**
     * Deletes an existing Profilo model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->model = $this->findModel($id);
        if ($this->model) {
            $headquarters = $this->model->profiloSedi;
            $headquartersDeleteOk = true;
            $transaction = Yii::$app->db->beginTransaction();
            foreach ($headquarters as $headquarter) {
                $headquarter->delete();
                if ($headquarter->hasErrors()) {
                    $headquartersDeleteOk = false;
                    Yii::$app->getSession()->addFlash('danger', Module::t('amoscore', 'Error while deleting organization headquarter.'));
                    $transaction->rollBack();
                    break;
                }
            }
            if ($headquartersDeleteOk) {
                $this->model->delete();
                if (!$this->model->hasErrors()) {
                    $transaction->commit();
                    Yii::$app->getSession()->addFlash('success', Module::t('amoscore', 'Item deleted'));
                } else {
                    Yii::$app->getSession()->addFlash('danger', Module::t('amoscore', 'You are not authorized to delete this element.'));
                    $transaction->rollBack();
                }
            }
        } else {
            Yii::$app->getSession()->addFlash('danger', Module::t('amoscore', 'Item not found'));
        }
        return $this->redirect(['index']);
    }
}
