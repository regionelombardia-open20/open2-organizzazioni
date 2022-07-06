<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\organizzazioni\controllers\base
 * @category   CategoryName
 */

namespace open20\amos\organizzazioni\controllers\base;

use open20\amos\admin\AmosAdmin;
use open20\amos\admin\models\UserProfile;
use open20\amos\core\controllers\CrudController;
use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\record\Record;
use open20\amos\dashboard\controllers\TabDashboardControllerTrait;
use open20\amos\organizzazioni\models\Profilo;
use open20\amos\organizzazioni\models\ProfiloSedi;
use open20\amos\organizzazioni\models\ProfiloSediLegal;
use open20\amos\organizzazioni\models\ProfiloSediOperative;
use open20\amos\organizzazioni\models\ProfiloUserMm;
use open20\amos\organizzazioni\Module;
use open20\amos\organizzazioni\utility\OrganizzazioniUtility;
use open20\amos\organizzazioni\widgets\icons\WidgetIconProfilo;
use Yii;
use yii\helpers\Url;
use yii\log\Logger;

/**
 * Class ProfiloController
 * ProfiloController implements the CRUD actions for Profilo model.
 *
 * @property \open20\amos\organizzazioni\models\Profilo $model
 * @property \open20\amos\organizzazioni\models\search\ProfiloSearch $modelSearch
 *
 * @package open20\amos\organizzazioni\controllers\base
 */
class ProfiloController extends CrudController
{
    /**
     * @var Module|null $organizzazioniModule
     */
    public $organizzazioniModule = null;
    
    /**
     * @var \open20\amos\cwh\AmosCwh|null $moduleCwh
     */
    public $moduleCwh = null;
    
    /**
     * @var array|null $scope
     */
    public $scope = null;
    
    /**
     * Trait used for initialize the news dashboard
     */
    use TabDashboardControllerTrait;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initDashboardTrait();
        $this->scope = null;
        $this->moduleCwh = Yii::$app->getModule('cwh');
        if (!empty($this->moduleCwh)) {
            $this->scope = $this->moduleCwh->getCwhScope();
        }
        
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
        
        $this->viewIcon = [
            'name' => 'icon',
            'label' => AmosIcons::show('grid') . Html::tag('p', Module::tHtml('amoscore', 'Icon')),
            'url' => '?currentView=icon'
        ];
        
        $defaultViews = [
            'grid' => $this->viewGrid,
            'icon' => $this->viewIcon,
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
     * Override this to do operations before saving model and other.
     */
    public function beforeSaveOperations()
    {
        
    }
    
    /**
     * Override this to do operations after saving model and other.
     */
    public function afterSaveOperations()
    {
        
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
        Yii::$app->view->params['textHelp']['filename'] = 'organizzazioni_dashboard_description';
        if ($this->organizzazioniModule->enableWorkflow && !Yii::$app->user->can('AMMINISTRATORE_ORGANIZZAZIONI')) {
            $this->setDataProvider($this->modelSearch->searchAll(Yii::$app->request->getQueryParams()));
        } else {
            $this->setDataProvider($this->modelSearch->search(Yii::$app->request->getQueryParams()));
        }
        $this->view->params['currentDashboard'] = $this->getCurrentDashboard();
        $this->child_of = WidgetIconProfilo::className();
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
        $this->view->params['textHelp']['filename'] = 'organizzazioni_dashboard_description';
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
        $this->view->params['textHelp']['filename'] = 'organizzazioni_dashboard_description';
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
            $this->beforeSaveOperations();
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                $ok = true;
                $validateOnSave = true;
                
                // Change statuses if the workflow is enabled
                if ($this->organizzazioniModule->enableWorkflow) {
                    if ($this->model->status == Profilo::PROFILO_WORKFLOW_STATUS_TOVALIDATE) {
                        $this->model->setInitialStatus();
                        $ok = $this->model->save();
                        if ($ok) {
                            $this->model->status = Profilo::PROFILO_WORKFLOW_STATUS_TOVALIDATE;
                            $validateOnSave = false;
                        }
                    } elseif ($this->model->status == Profilo::PROFILO_WORKFLOW_STATUS_VALIDATED) {
                        $this->model->setInitialStatus();
                        $ok = $this->model->save();
                        if ($ok) {
                            $this->model->status = Profilo::PROFILO_WORKFLOW_STATUS_VALIDATED;
                            $validateOnSave = false;
                        }
                    }
                }
                
                // Save after workflow statuses checks
                if ($ok) {
                    $ok = $this->model->save($validateOnSave);
                }
                
                if ($ok) {
                    
                    // Save operative headquarter
                    $okMainSedeOperativa = $this->saveMainSede($mainOperativeHeadquarter, Module::t('amosorganizzazioni', 'Error while saving operative headquarter'));
                    
                    // Save legal headquarter
                    $okMainSedeLegale = $this->saveMainSede($mainLegalHeadquarter, Module::t('amosorganizzazioni', 'Error while saving legal headquarter'));
                    
                    // Rappresentante Legale / Referente Operativo presente?
                    $this->addOrganizationToLegalOrOperative($this->model->rappresentante_legale, $this->model->id);
                    $this->addOrganizationToLegalOrOperative($this->model->referente_operativo, $this->model->id);
                    
                    if (
                        $okMainSedeOperativa &&
                        $okMainSedeLegale
                    ) {
                        $transaction->commit();
                        $this->afterSaveOperations();
                        Yii::$app->getSession()->addFlash('success', Module::t('amoscore', 'Item created'));
                        return $this->redirect(['update', 'id' => $this->model->id]);
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    $transaction->rollBack();
                    Yii::$app->getSession()->addFlash('danger', Module::t('amoscore', 'Item not created, check data'));
                }
            } catch (\Exception $exception) {
                \Yii::getLogger()->log($exception->getMessage(), Logger::LEVEL_ERROR);
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
            'moduleCwh' => $this->moduleCwh,
            'scope' => $this->scope
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
            }
        }
        
        return $this->renderAjax('_formAjax', [
            'model' => $this->model,
            'fid' => $fid,
            'dataField' => $dataField,
            'moduleCwh' => $this->moduleCwh,
            'scope' => $this->scope
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
        
        $this->view->params['textHelp']['filename'] = 'organizzazioni_dashboard_description';
        
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
            $this->beforeSaveOperations();
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $ok = $this->model->save();
                if ($ok) {
                    
                    // Save operative headquarter
                    $okMainSedeOperativa = $this->saveMainSede($mainOperativeHeadquarter, Module::t('amosorganizzazioni', 'Error while saving operative headquarter'));
                    
                    // Save legal headquarter
                    $okMainSedeLegale = $this->saveMainSede($mainLegalHeadquarter, Module::t('amosorganizzazioni', 'Error while saving legal headquarter'));
                    
                    // Rappresentante Legale / Referente Operativo presente?
                    $this->addOrganizationToLegalOrOperative($this->model->rappresentante_legale, $this->model->id);
                    $this->addOrganizationToLegalOrOperative($this->model->referente_operativo, $this->model->id);
                    
                    // There is a community?
                    if (($this->model->community_id) && (!empty($this->organizzazioniModule->communityModule))) {
                        OrganizzazioniUtility::updateCommunity($this->model, $this->organizzazioniModule->communityModule);
                    }
                    
                    if (
                        $okMainSedeOperativa &&
                        $okMainSedeLegale
                    ) {
                        $transaction->commit();
                        $this->afterSaveOperations();
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
            'moduleCwh' => $this->moduleCwh,
            'scope' => $this->scope
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
    
    /**
     * @param int|null $user_id
     * @param int|null $organization_id
     * @throws \yii\base\InvalidConfigException
     */
    public function addOrganizationToLegalOrOperative($user_id = null, $organization_id = null)
    {
        /** @var Profilo $profiloModel */
        $profiloModel = $this->organizzazioniModule->createModel('Profilo');
        $organization = $profiloModel::findOne($organization_id);
        
        /** @var UserProfile $userProfileModel */
        $userProfileModel = AmosAdmin::instance()->createModel('UserProfile');
        $userProfile = $userProfileModel::findOne(['user_id' => $user_id]);
        
        if (!is_null($userProfile) && !is_null($organization)) {
            /** @var ProfiloUserMm $profiloUserMmModel */
            $profiloUserMmModel = $this->organizzazioniModule->createModel('ProfiloUserMm');
            $found = $profiloUserMmModel::findOne(['user_id' => $user_id, 'profilo_id' => $organization_id]);
            
            if (empty($found)) {
                /** @var ProfiloUserMm $orgUserMm */
                $orgUserMm = $this->organizzazioniModule->createModel('ProfiloUserMm');
                $orgUserMm->user_id = $userProfile->user_id;
                $orgUserMm->profilo_id = $organization->id;
                $orgUserMm->status = ProfiloUserMm::STATUS_ACTIVE;
                $orgUserMm->save();
            }
        }
    }
}
