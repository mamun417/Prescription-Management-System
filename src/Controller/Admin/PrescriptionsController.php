<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Routing\Router;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;

/**
 * Prescriptions Controller
 *
 * @property \App\Model\Table\PrescriptionsTable $Prescriptions */
class PrescriptionsController extends AppController
{
    public $components = ['EmailHandler','Common'];

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $session = $this->request->session();
        if(isset($this->request->query['search']) and trim($this->request->query['search'])!='' ) {
            $session->write('prescriptions_search_query', $this->request->query['search']);

        }
        if($session->check('prescriptions_search_query')) {
            $search = $session->read('prescriptions_search_query');
        }else{
            $search = '';
        }

        $where = $this->__search();

        if($where){
            $query = $this->Prescriptions->find('All')->where($where);
        }else{
            $query = $this->Prescriptions;
        }

        $this->paginate = [
            'contain' => ['Users'],
            'limit' => 30,
            'order' => [
                'Prescriptions.id' => 'desc'
            ]
        ];


        $prescriptions = $this->paginate($query);

        if(count($prescriptions)==0){
            $this->Flash->adminWarning(__('No prescription found!')  ,['key' => 'admin_warning'], ['key' => 'admin_warning'] );
        }

        $this->set(compact('prescriptions', 'search' ));
        $this->set('_serialize', ['prescriptions']);

        //$this->render('view');
    }

    /**
     * View method
     *
     * @param string|null $id Prescription id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $prescription = $this->Prescriptions->get($id, [
            'contain' => ['Users', 'Medicines', 'Tests']
        ]);
        $this->set('prescription', $prescription);
        $this->set('_serialize', ['prescription']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $prescription = $this->Prescriptions->newEntity();

        if ($this->request->is('post')) {
            $medicines = $this->request->data['medicines'];
            unset($this->request->data['medicines']);
            $session = $this->request->session();
            $doctor_id = $session->read('Auth.User.id');

            $prescription->doctor_id = $doctor_id;
            $prescription = $this->Prescriptions->patchEntity($prescription, $this->request->data);
            $prescription = $this->Prescriptions->save($prescription);

            if ($prescription) {
                $this->savePrescriptionMedicines($medicines, $prescription->id);

                $this->Flash->adminSuccess('The prescription has been saved.', ['key' => 'admin_success']);
                return $this->redirect(['action' => 'index']);
            } else {
                $error_message = __('The prescription could not be save. Please, try again.');
                $this->Flash->adminError($error_message, ['key' => 'admin_error']);
            }
            return $this->redirect(['action' => 'index']);
        }
        //$users = $this->Prescriptions->Users->find('list', ['limit' => 200]);
        $get_users = $this->Prescriptions->Users->find('All')->where(['role_id' => 3]);//role_id =>3 that's mean patient
        foreach($get_users as $get_user){
            $users[$get_user->id] = $get_user->first_name." ".$get_user->last_name;
        }


        $medicines = $this->Prescriptions->Medicines->find('list', ['limit' => 200]);
        $tests = $this->Prescriptions->Tests->find('list', ['limit' => 200]);
        $this->set(compact('prescription', 'users', 'medicines', 'tests'));
        $this->set('_serialize', ['prescription']);
    }

    function savePrescriptionMedicines($medicines, $prescription_id){
        // Start: Prescriptions medicines
        $this->loadModel('PrescriptionMedicines');
        $prescriptions_medicines = $this->prepareMedicine($medicines, $prescription_id);
        if($prescriptions_medicines){
            foreach($prescriptions_medicines as $prescriptions_medicine){
                $prescription_medicine = $this->PrescriptionMedicines->newEntity();
                $prescription_medicine = $this->PrescriptionMedicines->patchEntity($prescription_medicine, $prescriptions_medicine );
                if(!$this->PrescriptionMedicines->save($prescription_medicine)){
                    $this->log('PrescriptionMedicines could not save ');
                }
            }
        }
        // End: Prescriptions medicines
    }

    function prepareMedicine($medicines,$prescription_id){
        if($medicines){
            $new_medicines = [];
            foreach($medicines['medicine_id'] as $key => $val) {
                $new_medicines[$key]['prescription_id'] = $prescription_id;
                $new_medicines[$key]['medicine_id'] = $val;
                $new_medicines[$key]['rule'] = $medicines['rule'][$key];
            }


            return $new_medicines;
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Prescription id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $prescription = $this->Prescriptions->get($id, [
            'contain' => ['Medicines', 'Tests']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $prescription = $this->Prescriptions->patchEntity($prescription, $this->request->data);
            if ($this->Prescriptions->save($prescription)) {
                $success_message = __('The prescription has been edited.');
                $this->Flash->adminSuccess($success_message, ['key' => 'admin_success']);
            } else {
                $error_message = __('The prescription could not be edit. Please, try again.');
                $this->Flash->adminError($error_message, ['key' => 'admin_error']);
            }
            return $this->redirect(['action' => 'index']);
        }

        //$users = $this->Prescriptions->Users->find('list', ['limit' => 200]);

        $get_users = $this->Prescriptions->Users->find('All');
        foreach($get_users as $get_user){
            $users[$get_user->id] = $get_user->first_name." ".$get_user->last_name;
        }

        $medicines = $this->Prescriptions->Medicines->find('list', ['limit' => 200]);
        $tests = $this->Prescriptions->Tests->find('list', ['limit' => 200]);
        $this->set(compact('prescription', 'users', 'medicines', 'tests'));
        $this->set('_serialize', ['prescription']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Prescription id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $prescription = $this->Prescriptions->get($id);
        if ($this->Prescriptions->delete($prescription)) {
            $success_message = __('The prescription has been deleted.');
            $this->Flash->adminSuccess($success_message, ['key' => 'admin_success']);
        } else {
            $error_message = __('The prescription could not be delete. Please, try again.');
            $this->Flash->adminError($error_message, ['key' => 'admin_error']);
        }
        return $this->redirect(['action' => 'index']);
    }

    public function setPatient(){

        $session = $this->request->session();
        $session->write('set_patient_id', $this->request->query['user_id']);

        return $this->redirect(['action' => 'index']);
    }

    function __search(){

        $session = $this->request->session();

        $patients_prescription = '';
        if($session->check('set_patient_id')){
            $patient_id = $session->read('set_patient_id');
            $patients_prescription = ['prescriptions.user_id' => $patient_id];
        }

        if($session->check('prescriptions_search_query')){
            $search = $session->read('prescriptions_search_query');
            $where = [
                $patients_prescription,
                'OR' => ["(
                    prescriptions.diagnosis LIKE '%$search%' OR
                    CONCAT( users.first_name, ' ', users.last_name ) LIKE '%$search%' OR
                    users.phone LIKE '%$search%'
                    )"]
            ];

        }else{
            $where =  $patients_prescription;
        }
        return $where;
    }

    function reset(){
        $session = $this->request->session();
        $session->delete('prescriptions_search_query');
        $session->delete('set_patient_id');
        $this->redirect(['action' => 'index']);
    }
    function patientIdReset(){
        $session = $this->request->session();
        $session->delete('set_patient_id');
        $this->redirect(['action' => 'index']);
    }

    function sendPrescriptionEmail($id = null){
        // data $prescription_id

        $prescription_info = $this->Prescriptions->get($id);
        $patient_id = $prescription_info['user_id'];
        $patient_info = $this->Prescriptions->Users->get($patient_id);

        $file_path = 'uploads/pdf/prescription-2345678.pdf';
        $file_name =  substr($file_path, strrpos($file_path, '/') + 1);

        $info = array(
            'to'                => $patient_info['email'],
            'subject'           => 'Prescription pdf',
            'template'          => 'prescription_pdf',
            'data'              => array('User' => $patient_info),
            'attach'            => array('file_name' => $file_name, 'file_path' => $file_path )
        );
        //pr($info);die;
        $this->EmailHandler->sendEmail($info);

        $success_message = __('A pdf file has been sent to your patient email address.');
        $this->Flash->adminSuccess($success_message, ['key' => 'admin_success']);

        $this->redirect(['action' => 'index']);
    }
}