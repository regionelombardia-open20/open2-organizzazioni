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
use lispa\amos\organizzazioni\models\search\ProfiloSearch;
use lispa\amos\organizzazioni\Module;
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
        $model = Module::instance()->createModel('Profilo');

        $this->setModelObj($model);
        $this->setModelSearch(new ProfiloSearch());

        $this->organizzazioniModule = Yii::$app->getModule(Module::getModuleName());

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

        if ($this->model->load(Yii::$app->request->post()) && $this->model->save()) {
            return $this->redirect(['view', 'id' => $this->model->id]);
        } else {
            return $this->render('view', ['model' => $this->model]);
        }
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
        $this->model = Module::instance()->createModel('Profilo');

        // Model for operative headquarter
        $mainSedeOperativa = new ProfiloSediOperative();
        $mainSedeOperativa->setScenario(ProfiloSedi::SCENARIO_CREATE);
        $mainSedeOperativa->is_main = 1;

        // Model for legal headquarter
        $mainSedeLegale = new ProfiloSediLegal();
        $mainSedeLegale->setScenario(ProfiloSedi::SCENARIO_CREATE);
        $mainSedeLegale->is_main = 1;

        // Load and validate all form models
        $post = Yii::$app->request->post();
        $modelLoadValidate = $this->model->load($post) && $this->model->validate();
        if ($post) {
            $mainSedeOperativa->address = $this->model->mainOperativeHeadquarterAddress;
            $mainSedeLegale->address = $this->model->mainLegalHeadquarterAddress;
        }
        $mainSedeOperativaLoadValidate = $mainSedeOperativa->load($post) && $mainSedeOperativa->validate();
        if ($this->model->la_sede_legale_e_la_stessa_del) {
            $skipColumns = ['profilo_sedi_type_id', 'id'];
            $sedeColumns = $mainSedeOperativa->attributes();
            foreach ($sedeColumns as $sedeColumn) {
                if (!in_array($sedeColumn, $skipColumns)) {
                    $mainSedeLegale->{$sedeColumn} = $mainSedeOperativa->{$sedeColumn};
                }
            }
            $mainSedeLegaleLoadValidate = $mainSedeLegale->validate();
        } else {
            $mainSedeLegaleLoadValidate = $mainSedeLegale->load($post) && $mainSedeLegale->validate();
        }

        if (
            $modelLoadValidate &&
            $mainSedeLegaleLoadValidate &&
            $mainSedeOperativaLoadValidate
        ) {
            $ok = $this->model->save();
            if ($ok) {

                // Save operative headquarter
                $okMainSedeOperativa = $this->saveMainSede($mainSedeOperativa, 'Error while saving operative headquarter');

                // Save legal headquarter
                $okMainSedeLegale = $this->saveMainSede($mainSedeLegale, 'Error while saving legal headquarter');

                if (
                    $okMainSedeOperativa &&
                    $okMainSedeLegale
                ) {
                    Yii::$app->getSession()->addFlash('success', Module::t('amoscore', 'Item created'));
                    return $this->redirect(['index']);
                }
            } else {
                Yii::$app->getSession()->addFlash('danger', Module::t('amoscore', 'Item not created, check data'));
            }
        }

        return $this->render('create', [
            'model' => $this->model,
            'mainSedeLegale' => $mainSedeLegale,
            'mainSedeOperativa' => $mainSedeOperativa,
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

        $this->model = Module::instance()->createModel('Profilo');

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
        $mainSedeOperativa = $this->model->operativeHeadquarter;
        if (!is_null($mainSedeOperativa)) {
            $this->model->mainOperativeHeadquarterAddress = $mainSedeOperativa->address;
        } else {
            $mainSedeOperativa = new ProfiloSediOperative();
            $mainSedeOperativa->setScenario(ProfiloSedi::SCENARIO_CREATE);
            $mainSedeOperativa->profilo_id = $this->model->id;
            $mainSedeOperativa->is_main = 1;
        }

        // Model for legal headquarter
        $mainSedeLegale = $this->model->legalHeadquarter;
        if (!is_null($mainSedeLegale)) {
            $this->model->mainLegalHeadquarterAddress = $mainSedeLegale->address;
        } else {
            $mainSedeLegale = new ProfiloSediLegal();
            $mainSedeLegale->setScenario(ProfiloSedi::SCENARIO_CREATE);
            $mainSedeLegale->profilo_id = $this->model->id;
            $mainSedeLegale->is_main = 1;
        }

        // Load and validate all form models
        $post = Yii::$app->request->post();
        $modelLoadValidate = $this->model->load($post) && $this->model->validate();
        if ($post) {
            $mainSedeOperativa->address = $this->model->mainOperativeHeadquarterAddress;
            $mainSedeLegale->address = $this->model->mainLegalHeadquarterAddress;
        }
        $mainSedeOperativaLoadValidate = $mainSedeOperativa->load($post) && $mainSedeOperativa->validate();
        if ($this->model->la_sede_legale_e_la_stessa_del) {
            $skipColumns = [
                'profilo_sedi_type_id',
                'profilo_id',
                'id'
            ];
            $sedeColumns = $mainSedeOperativa->attributes();
            foreach ($sedeColumns as $sedeColumn) {
                if (!in_array($sedeColumn, $skipColumns)) {
                    $mainSedeLegale->{$sedeColumn} = $mainSedeOperativa->{$sedeColumn};
                }
            }
            $mainSedeLegaleLoadValidate = $mainSedeLegale->validate();
        } else {
            $mainSedeLegaleLoadValidate = $mainSedeLegale->load($post) && $mainSedeLegale->validate();
        }

        if (
            $modelLoadValidate &&
            $mainSedeLegaleLoadValidate &&
            $mainSedeOperativaLoadValidate
        ) {
            $ok = $this->model->save();
            if ($ok) {

                // Save operative headquarter
                $okMainSedeOperativa = $this->saveMainSede($mainSedeOperativa, 'Error while saving operative headquarter');

                // Save legal headquarter
                $okMainSedeLegale = $this->saveMainSede($mainSedeLegale, 'Error while saving legal headquarter');

                if (
                    $okMainSedeOperativa &&
                    $okMainSedeLegale
                ) {
                    Yii::$app->getSession()->addFlash('success', Module::t('amoscore', 'Item updated'));
                    return $this->redirect(['update', 'id' => $this->model->id]);
                }
            } else {
                Yii::$app->getSession()->addFlash('danger', Module::t('amoscore', 'Item not updated, check data'));
            }
        }

        return $this->render('update', [
            'model' => $this->model,
            'mainSedeLegale' => $mainSedeLegale,
            'mainSedeOperativa' => $mainSedeOperativa,
            'fid' => null,
            'dataField' => null,
            'dataEntity' => null,
        ]);
    }

    /**
     * @param ProfiloSedi $mainSede
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
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->model = $this->findModel($id);
        if ($this->model) {
            $headquarters = $this->model->profiloSedi;
            $headquartersDeleteOk = true;
            foreach ($headquarters as $headquarter) {
                $headquarter->delete();
                if ($headquarter->hasErrors()) {
                    $headquartersDeleteOk = false;
                    Yii::$app->getSession()->addFlash('danger', Module::t('amoscore', 'Error while deleting organization headquarter.'));
                    break;
                }
            }
            if ($headquartersDeleteOk) {
                $this->model->delete();
                if (!$this->model->hasErrors()) {
                    Yii::$app->getSession()->addFlash('success', Module::t('amoscore', 'Item deleted'));
                } else {
                    Yii::$app->getSession()->addFlash('danger', Module::t('amoscore', 'You are not authorized to delete this element.'));
                }
            }
        } else {
            Yii::$app->getSession()->addFlash('danger', Module::t('amoscore', 'Item not found'));
        }
        return $this->redirect(['index']);
    }
}
