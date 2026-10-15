<?php
/*
  ###########################################################
  # PRODUCT NAME: 	iRestora PLUS - Next Gen Restaurant POS
  ###########################################################
  # AUTHER:		Doorsoft
  ###########################################################
  # EMAIL:		info@doorsoft.co
  ###########################################################
  # COPYRIGHTS:		RESERVED BY Door Soft
  ###########################################################
  # WEBSITE:		http://www.doorsoft.co
  ###########################################################
  # This is Outlet Controller
  ###########################################################
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Outlet extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
        $this->load->model('Common_model');
        $this->load->model('Outlet_model');
        $this->load->model('Sale_model');
        $this->load->library('form_validation');
        $this->Common_model->setDefaultTimezone();
        $this->config->load('zatca_config', TRUE);

        if (!$this->session->has_userdata('user_id')) {
            redirect('Authentication/index');
        }


        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "67";
        $function = "";
       
        if($segment_2=="outlets"){
            $function = "view";
        }elseif($segment_2=="addEditOutlet" && $segment_3){
            $function = "update";
        }elseif($segment_2=="setOutletSession" && $segment_3){
            $function = "enter";
        }elseif($segment_2=="addEditOutlet"){
            $function = "add";
        }elseif($segment_2=="deleteOutlet"){
            $function = "delete";
        }else{
           
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }
        if(!checkAccess($controller,$function)){
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }
        //end check access function
    }

    /**
     * outlets info
     * @access public
     * @return void
     * @param no
     */
    public function outlets() {
        //unset outlet data
        $language_manifesto = $this->session->userdata('language_manifesto');

        if(str_rot13($language_manifesto)=="fgjgldkfg"){
            $outlet_id = $this->session->userdata('outlet_id');
            redirect("Outlet/addEditOutlet/".$outlet_id);
        }
        $data = array();
        $data['outlets'] = $this->Common_model->getAllOutlestByAssign();
        $data['main_content'] = $this->load->view('outlet/outlets', $data, TRUE);
        $this->load->view('userHome', $data);
    }
    /**
     * delete Outlet
     * @access public
     * @return void
     * @param int
     */
    public function deleteOutlet($id) {
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        $this->Common_model->deleteStatusChange($id, "tbl_outlets");

        $this->session->set_flashdata('exception',lang('delete_success'));
        redirect('Outlet/outlets');
    }
    /**
     * add/Edit Outlet
     * @access public
     * @return void
     * @param int
     */
    public function addEditOutlet($encrypted_id = "") {

        if(isServiceAccessOnly('sGmsJaFJE')){
            if($encrypted_id==''){
                if(!checkCreatePermissionOutlet()){
                    $data_c = getLanguageManifesto();
                    $this->session->set_flashdata('exception_1',lang('not_permission_outlet_create_error'));
                    redirect($data_c[1]);
                }
            }

        }
        $id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $company_id = $this->session->userdata('company_id');
        $language_manifesto = $this->session->userdata('language_manifesto');


        // Check if Connect ZATCA button was clicked
        $is_zatca_connect = htmlspecialcharscustom($this->input->post('connect_zatca'));
        
        // Check if Enable ZATCA dropdown is set to '1' (Enable)
        $is_zatca_enable_checked = $this->input->post('is_zatca_enable') == '1' ? true : false;
        
        // Always update is_zatca_enable status (even if validation fails)
        if ((htmlspecialcharscustom($this->input->post('submit')) || $is_zatca_connect) && $id != "") {
            $zatca_enable_status = $this->input->post('is_zatca_enable') == '1' ? 1 : 0;
            $this->Common_model->updateInformation(array('is_zatca_enable' => $zatca_enable_status), $id, "tbl_outlets");
        }
        
        if (htmlspecialcharscustom($this->input->post('submit')) || $is_zatca_connect) {
            
            // Regular outlet validation (always validate these)
            $this->form_validation->set_rules('outlet_name',lang('outlet_name'), 'required|max_length[50]');
            $this->form_validation->set_rules('address',lang('address'), 'required|max_length[200]');
            $this->form_validation->set_rules('phone', lang('phone'), 'required');
            if(str_rot13($language_manifesto)=="eriutoeri"):
                $this->form_validation->set_rules('default_waiter', lang('Default_Waiter'), 'max_length[11]');
            endif;
            
            // If Connect ZATCA button clicked OR Enable ZATCA checkbox is checked, validate ZATCA fields as required
            if ($is_zatca_connect || $is_zatca_enable_checked) {
                $this->form_validation->set_rules('zatca_outlet[legal_name_en]', 'Legal Name (English)', 'required|max_length[255]');
                $this->form_validation->set_rules('zatca_outlet[legal_name_ar]', 'Legal Name (Arabic)', 'required|max_length[255]');
                $this->form_validation->set_rules('zatca_outlet[vat_number]', 'VAT Number', 'required|exact_length[15]|numeric');
                $this->form_validation->set_rules('zatca_outlet[cr_number]', 'CR Number', 'required|max_length[100]|numeric');
                $this->form_validation->set_rules('zatca_outlet[postal_code]', 'Postal Code', 'required|max_length[20]');
                $this->form_validation->set_rules('zatca_outlet[address]', 'Address', 'required|max_length[500]');
            } else {
                // If checkbox unchecked and regular submit, ZATCA fields are optional - just validate format if provided
                $this->form_validation->set_rules('zatca_outlet[legal_name_en]', 'Legal Name (English)', 'max_length[255]');
                $this->form_validation->set_rules('zatca_outlet[legal_name_ar]', 'Legal Name (Arabic)', 'max_length[255]');
                $this->form_validation->set_rules('zatca_outlet[vat_number]', 'VAT Number', 'exact_length[15]|numeric');
                $this->form_validation->set_rules('zatca_outlet[cr_number]', 'CR Number', 'max_length[100]|numeric');
                $this->form_validation->set_rules('zatca_outlet[postal_code]', 'Postal Code', 'max_length[20]');
                $this->form_validation->set_rules('zatca_outlet[address]', 'Address', 'max_length[500]');
            }
            if ($this->form_validation->run() == TRUE) {
                $outlet_info = array();
                $outlet_info['outlet_name'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('outlet_name')));
                $c_address =htmlspecialcharscustom($this->input->post($this->security->xss_clean('address'))); #clean the address
                $outlet_info['address'] = preg_replace("/[\n\r]/"," ",$c_address); #remove new line from address
                $outlet_info['phone'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('phone')));
                $outlet_info['email'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('email')));
                $outlet_info['online_order_module'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('online_order_module')));
                if(str_rot13($language_manifesto)=="eriutoeri"):
                    $outlet_info['default_waiter'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('default_waiter')));
                    $outlet_info['active_status'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('active_status')));
                endif;
                
                // Handle ZATCA outlet data
                $zatca_result = $this->processZatcaConnection($is_zatca_connect, $id);
                
                if ($zatca_result['stop_execution']) {
                    $this->session->set_flashdata('exception_er', $zatca_result['error']);
                    redirect('Outlet/addEditOutlet/' . $encrypted_id);
                }

                $zatca_enable_status = $this->input->post('is_zatca_enable') == '1' ? 1 : 0;

                // Always update zatca_outlet (configuration data)
                $outlet_info['zatca_outlet'] = $zatca_result['zatca_outlet'];
                
                // Only update zatca_token when Connect ZATCA button is clicked (not regular Submit)
                if($is_zatca_connect){
                    $outlet_info['zatca_token'] = $zatca_result['zatca_token'];
                }
                
                $outlet_info['is_zatca_enable'] = $zatca_enable_status;
                $this->session->set_userdata($outlet_info);
                if ($id == "") {
                    $outlet_info['company_id'] = $this->session->userdata('company_id');
                    $outlet_info['created_date'] = date("Y-m-d");
                    if(str_rot13($language_manifesto)=="eriutoeri") {
                        $outlet_info['outlet_code'] = htmlspecialcharscustom($this->input->post($this->security->xss_clean('outlet_code')));
                    }
                }
                if ($id == "") {
                    $id = $this->Common_model->insertInformation($outlet_info, "tbl_outlets");
                    $this->session->set_flashdata('exception', $is_zatca_connect ? $zatca_result['success_message'] : lang('insertion_success'));

                    //update user
                    $user_id = $this->session->userdata('user_id');
                    $user_details = $this->Common_model->getDataById($user_id, "tbl_users");
                    $data_user = array();
                    $data_user['outlets'] = isset($user_details->outlets) && $user_details->outlets?$user_details->outlets.",".$id:$id;
                    $login_session['session_outlets'] = $data_user['outlets'];
                    $this->session->set_userdata($login_session);
                    //end update user

                    $this->Common_model->updateInformation($data_user, $user_id, "tbl_users");
                } else {
                    $this->Common_model->updateInformation($outlet_info, $id, "tbl_outlets");
                    $this->session->set_flashdata('exception', $is_zatca_connect ? $zatca_result['success_message'] : lang('update_success'));
                }
                $language_manifesto = $this->session->userdata('language_manifesto');
                if(str_rot13($language_manifesto)=="eriutoeri"):
                    $item_check =$this->input->post($this->security->xss_clean('item_check'));
                    if($item_check){
                        $main_arr = '';
                        $total_selected = sizeof($item_check);
                        $data_price_array = array();
                        $json_data = array();

                        for($i=0;$i<$total_selected;$i++){
                            $main_arr.=$item_check[$i];
                            if($i <= ($total_selected) -1){
                                $main_arr.=",";
                                $name_generate = "price_".$item_check[$i];
                                $price_ta_name_generate = "price_ta_".$item_check[$i];
                                $price_de_name_generate = "price_de_".$item_check[$i];
                                $data_price_array["tmp".$item_check[$i]] = htmlspecialcharscustom($this->input->post($this->security->xss_clean($name_generate)))."||".htmlspecialcharscustom($this->input->post($this->security->xss_clean($price_ta_name_generate)))."||".htmlspecialcharscustom($this->input->post($this->security->xss_clean($price_de_name_generate)));
                            }

                            $field_name = "sale_price_delivery_json".$item_check[$i];
                            $delivery_person_field_name = "delivery_person".$item_check[$i];
                            $del_price_total = $this->input->post($this->security->xss_clean($field_name));
                            $delivery_person_field_name_value = $this->input->post($this->security->xss_clean($delivery_person_field_name));

                            if(isset($del_price_total) && $del_price_total){
                                $tmp_array = array();
                                foreach ($del_price_total as $row => $value_1):
                                    $tmp_array["index_".$delivery_person_field_name_value[$row]] = $value_1;
                                endforeach;
                                $json_data["index_".$item_check[$i]] = json_encode($tmp_array);
                            }

                        }
                        //set food menu for this outlet
                        $data_food_menus['food_menus'] = $main_arr;
                        $data_food_menus['food_menu_prices'] = json_encode($data_price_array);
                        $data_food_menus['delivery_price'] = json_encode($json_data);
                        $this->Common_model->updateInformation($data_food_menus, $id, "tbl_outlets");
                    }
                endif;
                $data_c = getLanguageManifesto();
                redirect($data_c[1]);
            } else {
                if ($id == "") {
                    $data = array();
                    $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_outlets");
                    $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                    $data['items'] = $this->Common_model->getFoodMenuForOutlet($company_id, "tbl_food_menus");
                    $data['outlet_code'] = $this->Outlet_model->generateOutletCode();
                    $data['waiters'] = $this->Sale_model->getWaitersForThisCompanyForOutlet1($company_id,'tbl_users');
                    $data['main_content'] = $this->load->view('outlet/addOutlet', $data, TRUE);
                    $this->load->view('userHome', $data);
                } else {
                    $data = array();
                    $data['encrypted_id'] = $encrypted_id;
                    $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                    $data['items'] = $this->Common_model->getFoodMenuForOutlet($company_id, "tbl_food_menus");
                    $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_outlets");
                    $data['waiters'] = $this->Sale_model->getWaitersForThisCompanyForOutlet1($company_id,'tbl_users');
                    $data['main_content'] = $this->load->view('outlet/editOutlet', $data, TRUE);
                    $this->load->view('userHome', $data);
                }
            }
        } else {
            $language_manifesto = $this->session->userdata('language_manifesto');
            if(str_rot13($language_manifesto)=="fgjgldkfg"){
                $outlet_id = $this->session->userdata('outlet_id');
                if($outlet_id != $id){
                    redirect("Outlet/addEditOutlet/".$outlet_id);
                }
            }
            if ($id == "") {
                $data = array();
                $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_outlets");
                $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                $data['items'] = $this->Common_model->getFoodMenuForOutlet($company_id, "tbl_food_menus");
                $data['outlet_code'] = $this->Outlet_model->generateOutletCode();
                $data['waiters'] = $this->Sale_model->getWaitersForThisCompanyForOutlet1($company_id,'tbl_users');
                $data['main_content'] = $this->load->view('outlet/addOutlet', $data, TRUE);
                $this->load->view('userHome', $data);
            } else {
                $data = array();
                $data['encrypted_id'] = $encrypted_id;
                $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
                $data['items'] = $this->Common_model->getFoodMenuForOutlet($company_id, "tbl_food_menus");
                $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_outlets");
                $data['waiters'] = $this->Sale_model->getWaitersForThisCompanyForOutlet1($company_id,'tbl_users');
                $selected_modules =  explode(',',$data['outlet_information']->food_menus);
                $selected_modules_arr = array();
                foreach ($selected_modules as $value) {
                    $selected_modules_arr[] = $value;
                }
                $data['selected_modules_arr'] = $selected_modules_arr;
                $data['main_content'] = $this->load->view('outlet/editOutlet', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        }
    }
    /**
     * set Outlet Session
     * @access public
     * @return void
     * @param int
     */
    public function setOutletSession($encrypted_id) {
        $outlet_id = $this->custom->encrypt_decrypt($encrypted_id, 'decrypt');
        $language_manifesto = $this->session->userdata('language_manifesto');
        $outlet_details = $this->Common_model->getDataById($outlet_id, 'tbl_outlets');

        $outlet_session = array();
        $outlet_session['outlet_id'] = $outlet_details->id;
        $outlet_session['outlet_name'] = $outlet_details->outlet_name;
        $outlet_session['address'] = $outlet_details->address;
        $outlet_session['phone'] = $outlet_details->phone;
        $outlet_session['email'] = $outlet_details->email;
        $outlet_session['online_order_module'] = $outlet_details->online_order_module;

        if(str_rot13($language_manifesto)=="eriutoeri"):
            $outlet_session['default_waiter'] = $outlet_details->default_waiter;
        else:
            $setting = getCompanyInfo();
            $outlet_session['default_waiter'] = $setting->default_waiter;
        endif;
        $this->session->set_userdata($outlet_session);
        
        if (!$this->session->has_userdata('clicked_controller')) {
            
            if ($this->session->userdata('role') == 'Admin') {
                redirect('Dashboard/dashboard');
            } else if($this->session->userdata('role') == 'Chef') {
                redirect('Kitchen/kitchens');
            } else {
               redirect('POSChecker/posAndWaiterMiddleman');
            }
        } else {
            $clicked_controller = $this->session->userdata('clicked_controller');
            $clicked_method = $this->session->userdata('clicked_method');

            $this->session->unset_userdata('clicked_controller');
            $this->session->unset_userdata('clicked_method');
            if($clicked_method=="get_new_notifications_ajax"){
                redirect('POSChecker/posAndWaiterMiddleman');
            }else{
                redirect($clicked_controller . '/' . $clicked_method);
            }
            
        }
    }
    
    /**
     * Process ZATCA connection with key generation and API integration
     * @access private
     * @param bool $is_zatca_connect
     * @param int $outlet_id
     * @return array
     */
    private function processZatcaConnection($is_zatca_connect, $outlet_id) {
        $zatca_outlet = $this->input->post('zatca_outlet');
        
        // Initialize result
        $result = array(
            'zatca_outlet' => NULL,
            'zatca_token' => NULL,
            'success_message' => '',
            'error' => '',
            'stop_execution' => false
        );
        
        // If no ZATCA data, return
        if (!$zatca_outlet || !is_array($zatca_outlet)) {
            return $result;
        }
        
        // Sanitize ZATCA input fields
        $zatca_data = $this->sanitizeZatcaData($zatca_outlet);
        
        // If not connecting ZATCA (just regular Submit), merge with existing data to preserve keys/CSR
        if (!$is_zatca_connect) {
            // Get existing zatca_outlet from database
            if ($outlet_id) {
                $existing_outlet = $this->db->select('zatca_outlet')
                                             ->from('tbl_outlets')
                                             ->where('id', $outlet_id)
                                             ->get()
                                             ->row();
                
                if ($existing_outlet && $existing_outlet->zatca_outlet) {
                    $existing_data = json_decode($existing_outlet->zatca_outlet, true);
                    
                    // Merge: Update field values but preserve private_key, csr, status, etc.
                    $merged_data = array_merge(
                        $existing_data, // Keep existing keys, CSR, status
                        $zatca_data     // Update only field values
                    );
                    
                    $result['zatca_outlet'] = json_encode($merged_data);
                } else {
                    // No existing data, just save new data
                    $result['zatca_outlet'] = json_encode($zatca_data);
                }
            } else {
                // New outlet, just save new data
                $result['zatca_outlet'] = json_encode($zatca_data);
            }
            
            return $result;
        }
        
        // Step 1: Generate Private Key and CSR (only when Connect ZATCA button clicked)
        $key_generation = $this->generateZatcaPrivateKeyAndCSR($zatca_data);
        
        if (!$key_generation['success']) {
            $result['error'] = 'Key Generation Failed: ' . $key_generation['error'];
            $result['stop_execution'] = true;
            return $result;
        }
        
        // Add generated keys to data
        $zatca_data['private_key'] = $key_generation['private_key'];
        $zatca_data['csr'] = $key_generation['csr'];
        $zatca_data['generated_date'] = date('Y-m-d H:i:s');
        $zatca_data['zatca_serial_number'] = "1-{$zatca_data['vat_number']}|2-{$zatca_data['cr_number']}|3-" . date('YmdHis');
        $zatca_data['device_serial'] = date('YmdHis') . "-" . $outlet_id;
        
        // Step 2: Call ZATCA API
        $api_response = $this->callZatcaComplianceApi($zatca_data['csr']);
        
       if (!$api_response['success']) {
           $result['error'] = 'Key Generated Successfully but ZATCA API Connection Failed: ' . $api_response['error'];
           $result['stop_execution'] = true;
           return $result;
       }
        
        // Both operations successful
        $zatca_data['status'] = 'CONNECTED';
        $zatca_data['connected_date'] = date('Y-m-d H:i:s');
        
        $result['zatca_outlet'] = json_encode($zatca_data);
        $result['zatca_token'] = json_encode($api_response['data']);

        $result['success_message'] = 'ZATCA Connection Successful! Private Key Generated and Compliance Certificate Received.';
        
        return $result;
    }
    
    /**
     * Sanitize ZATCA input data
     * @access private
     * @param array $zatca_outlet
     * @return array
     */
    private function sanitizeZatcaData($zatca_outlet) {
        return array(
            'legal_name_en' => isset($zatca_outlet['legal_name_en']) ? htmlspecialcharscustom($this->security->xss_clean($zatca_outlet['legal_name_en'])) : '',
            'legal_name_ar' => isset($zatca_outlet['legal_name_ar']) ? htmlspecialcharscustom($this->security->xss_clean($zatca_outlet['legal_name_ar'])) : '',
            'vat_number' => isset($zatca_outlet['vat_number']) ? htmlspecialcharscustom($this->security->xss_clean($zatca_outlet['vat_number'])) : '',
            'cr_number' => isset($zatca_outlet['cr_number']) ? htmlspecialcharscustom($this->security->xss_clean($zatca_outlet['cr_number'])) : '',
            'postal_code' => isset($zatca_outlet['postal_code']) ? htmlspecialcharscustom($this->security->xss_clean($zatca_outlet['postal_code'])) : '',
            'address' => isset($zatca_outlet['address']) ? htmlspecialcharscustom($this->security->xss_clean($zatca_outlet['address'])) : ''
        );
    }
    
    /**
     * Call ZATCA API to get compliance certificate
     * @access private
     * @param string $csr_pem
     * @return array
     */
    private function callZatcaComplianceApi($csr_pem) {
        try {
            // Get API configuration
            $config = $this->getZatcaApiConfig();
            
            if (!$config['success']) {
                return $config;
            }
            
            // Prepare CSR for API
            $csr_base64 = $this->prepareCsrForApi($csr_pem);
            
            // Execute API call
            $response = $this->executeZatcaApiRequest($config['api_url'], $config['otp'], $csr_base64);
            
            if ($response['curl_error']) {
                return array(
                    'success' => false,
                    'error' => 'Network Error: ' . $response['curl_error']
                );
            }
            
            // Parse and validate response
            return $this->parseZatcaApiResponse($response['http_code'], $response['body'], $config['is_production']);
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Get ZATCA API configuration (uses centralized config)
     * @access private
     * @return array
     */
    private function getZatcaApiConfig() {
        $config = $this->config->item('zatca_config');
        
        // Get environment setting from centralized config
        $is_production = $config['zatca_is_production'];
        
        // Get API URL from centralized config
        $api_url = $is_production 
            ? $config['zatca_production_compliance_api']
            : $config['zatca_sandbox_compliance_api'];
        
        $company_info = getCompanyInfo();
        $otp = isset($company_info->zatca_otp) ? $company_info->zatca_otp : '';
        
        if (empty($otp)) {
            return array(
                'success' => false,
                'error' => 'ZATCA OTP not configured. Please add OTP in company settings.'
            );
        }
        
        return array(
            'success' => true,
            'api_url' => $api_url,
            'otp' => $otp,
            'is_production' => $is_production
        );
    }
    
    /**
     * Prepare CSR for ZATCA API
     * @access private
     * @param string $csr_pem
     * @return string
     */
    private function prepareCsrForApi($csr_pem) {
        $csr_clean = str_replace([
            '-----BEGIN CERTIFICATE REQUEST-----',
            '-----END CERTIFICATE REQUEST-----',
            "\n",
            "\r"
        ], '', $csr_pem);
        
        return trim($csr_clean);
    }
    
    /**
     * Execute ZATCA API request
     * @access private
     * @param string $api_url
     * @param string $otp
     * @param string $csr_base64
     * @return array
     */
    private function executeZatcaApiRequest($api_url, $otp, $csr_base64) {
        $headers = array(
            'Accept: application/json',
            'Accept-Language: en',
            'Accept-Version: V2',
            'Content-Type: application/json',
            'OTP: ' . $otp
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('csr' => $csr_base64)));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        return array(
            'http_code' => $http_code,
            'body' => $response,
            'curl_error' => $curl_error
        );
    }
    
    /**
     * Parse ZATCA API response
     * @access private
     * @param int $http_code
     * @param string $response_body
     * @param bool $is_production
     * @return array
     */
    private function parseZatcaApiResponse($http_code, $response_body, $is_production) {
        $response_data = json_decode($response_body, true);
        
        if ($http_code == 200 || $http_code == 201) {
            return array(
                'success' => true,
                'data' => array(
                    'request_id' => isset($response_data['requestID']) ? $response_data['requestID'] : '',
                    'disposition_message' => isset($response_data['dispositionMessage']) ? $response_data['dispositionMessage'] : '',
                    'binary_security_token' => isset($response_data['binarySecurityToken']) ? $response_data['binarySecurityToken'] : '',
                    'secret' => isset($response_data['secret']) ? $response_data['secret'] : '',
                    'environment' => $is_production ? 'production' : 'sandbox',
                    'response_date' => date('Y-m-d H:i:s'),
                    'http_code' => $http_code,
                    'environment' => $is_production ? 'production' : 'sandbox'
                )
            );
        }
        
        // Build error message
        $error_message = 'HTTP ' . $http_code;
        
        if (isset($response_data['errors']) && is_array($response_data['errors'])) {
            $error_details = array();
            foreach ($response_data['errors'] as $error) {
                $error_details[] = isset($error['message']) ? $error['message'] : json_encode($error);
            }
            $error_message .= ' - ' . implode(', ', $error_details);
        } elseif (isset($response_data['message'])) {
            $error_message .= ' - ' . $response_data['message'];
        }
        
        return array(
            'success' => false,
            'error' => $error_message
        );
    }
    
    /**
     * Generate ZATCA Private Key and CSR following Saudi ZATCA Phase-2 requirements
     * @access private
     * @param array $zatca_data
     * @return array
     */
    private function generateZatcaPrivateKeyAndCSR($zatca_data) {
        try {
            // Get ZATCA configuration
            $zatca_config = $this->config->item('zatca_config');
            
            // Configure OpenSSL for ZATCA compliance (uses centralized config)
            $config = array(
                "digest_alg" => $zatca_config['zatca_hash_algorithm'],
                "private_key_bits" => $zatca_config['zatca_rsa_key_size'],
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
                "encrypt_key" => false
            );
            
            // Generate private key
            $private_key_resource = openssl_pkey_new($config);
            if (!$private_key_resource) {
                return array(
                    'success' => false,
                    'error' => 'Failed to generate private key: ' . openssl_error_string()
                );
            }
            
            // Export private key to PEM format
            openssl_pkey_export($private_key_resource, $private_key_pem);
            
            // Prepare CSR distinguished name (DN) following ZATCA requirements (uses centralized config)
            // Sanitize fields to ensure only ASCII characters (OpenSSL requirement)
            // Note: OpenSSL only accepts ASCII characters, no special chars, no Arabic
            $legal_name_sanitized = preg_replace('/[^A-Za-z0-9\s\-\.]/', '', $zatca_data['legal_name_en']);
            $vat_number_sanitized = preg_replace('/[^0-9]/', '', $zatca_data['vat_number']);
            
            // Ensure fields are not empty after sanitization
            if (empty($legal_name_sanitized) || strlen($legal_name_sanitized) < 2) {
                return array(
                    'success' => false,
                    'error' => 'Legal Name (English) must contain at least 2 valid ASCII characters (letters, numbers, spaces, hyphens, or dots only)'
                );
            }
            
            if (empty($vat_number_sanitized) || strlen($vat_number_sanitized) != 15) {
                return array(
                    'success' => false,
                    'error' => 'VAT Number must be exactly 15 digits'
                );
            }
            
            $dn = array(
                "countryName" => $zatca_config['zatca_country_code'],          // C - Saudi Arabia (from config)
                "organizationName" => $legal_name_sanitized,                   // O - Legal Name (ASCII only)
                "organizationalUnitName" => $vat_number_sanitized,             // OU - VAT Number (numbers only)
                "commonName" => $legal_name_sanitized                          // CN - Common Name (ASCII only)
            );
            
            // Log DN values for debugging
            log_message('info', 'ZATCA CSR DN: ' . json_encode($dn));
            
            // Generate CSR
            $csr_resource = openssl_csr_new($dn, $private_key_resource, $config);
            
            if (!$csr_resource) {
                $openssl_error = openssl_error_string();
                log_message('error', 'ZATCA CSR Generation Failed: ' . $openssl_error);
                log_message('error', 'ZATCA DN Values: ' . json_encode($dn));
                
                return array(
                    'success' => false,
                    'error' => 'Failed to generate CSR: ' . $openssl_error . '. Please ensure Legal Name (English) contains only ASCII characters (A-Z, 0-9, space, hyphen, dot).'
                );
            }
            
            // Export CSR to PEM format
            openssl_csr_export($csr_resource, $csr_pem);
            
            // Clean up resources
            if (is_resource($private_key_resource)) {
                openssl_pkey_free($private_key_resource);
            }
            
            return array(
                'success' => true,
                'private_key' => $private_key_pem,
                'csr' => $csr_pem
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => 'OpenSSL Error: ' . $e->getMessage()
            );
        }
    }
}
