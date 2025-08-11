<?php

namespace App\Controllers;

use App\Models\Partners_model;
use App\Models\Users_model;
use PDO;

/**
 * Class Auth
 *
 * @property Ion_auth|Ion_auth_model $ion_auth      The ION Auth spark
 * @package  CodeIgniter-Ion-Auth
 * @author   Ben Edmunds <ben.edmunds@gmail.com>
 * @author   Benoit VRIGNAUD <benoit.vrignaud@zaclys.net>
 * @license  https://opensource.org/licenses/MIT	MIT License
 */
class Auth extends BaseController
{
    /**
     *
     * @var array
     */
    protected bool $is_loggedin = false; 

    public $data = [];
    /**
     * Configuration
     *
     * @var \IonAuth\Config\IonAuth
     */
    protected $configIonAuth;
    /**
     * IonAuth library
     *
     * var \IonAuth\Libraries\IonAuth
     */
    /**
     * Session
     *
     * @var \CodeIgniter\Session\Session
     */
    protected $session;
    /**
     * Validation library
     *
     * @var \CodeIgniter\Validation\Validation
     */
    protected $validation;
    /**
     * Validation list template.
     *
     * @var string
     * @see https://bcit-ci.github.io/CodeIgniter4/libraries/validation.html#configuration
     */
    protected $validationListTemplate = 'list';
    /**
     * Views folder
     * Set it to 'auth' if your views files are in the standard application/Views/auth
     *
     * @var string
     */
    protected $viewsFolder = '/frontend/retro';
    /**
     * Constructor
     *
     * @return void
     */
    public $partner_model;
    public function __construct()
    {
        $this->data['admin'] = $this->userIsAdmin;
        $this->validation = \Config\Services::validation();
        helper(['form', 'url']);
        $this->configIonAuth = config('IonAuth');
        $this->session       = \Config\Services::session();
        $this->partner_model =  new Partners_model();
        if (!empty($this->configIonAuth->templates['errors']['list'])) {
            $this->validationListTemplate = $this->configIonAuth->templates['errors']['list'];
        }
    }
    /**
     * Redirect if needed, otherwise display the user list
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        if (!$this->ionAuth->loggedIn()) {
            // redirect them to the login page
            return redirect()->to('/auth/login/');
        } else {
            if ($this->ionAuth->isAdmin()) {
                return redirect()->to('/admin/dashboard');
            } else {
                return redirect()->to('/partner/dashboard');
            }
        }
    }
    /**
     * Log the user in
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function login()
    {
        $settings = get_settings('general_settings', true);
        $this->data['data'] = $settings;
        $app_name = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand Services";
        $this->data['admin'] = ($this->isLoggedIn && $this->userIsAdmin)  ? true : false;
        if ($this->ionAuth->loggedIn()) {
            if ($this->ionAuth->isAdmin()) {
                return redirect()->to('/admin/dashboard')->withCookies();
            } else {
                return redirect()->to('/partner/dashboard')->withCookies();
            }
        } else {
            $this->data['title'] = lang('Auth.login_heading');
            // validate form input
            // $this->validation->setRule('identity', str_replace(':', '', lang('Auth.login_identity_label')), 'required');
            $this->validation->setRule('password', str_replace(':', '', lang('Auth.login_password_label')), 'required');
            if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
                // check to see if the user is logging in
                // check for "remember me"
                $remember = (bool)$this->request->getVar('remember');
                $data = $this->ionAuth->login($this->request->getVar('identity'), $this->request->getVar('password'), $remember, $this->request->getVar('country_code'));
                // if ($this->ionAuth->login($this->request->getVar('identity'), $this->request->getVar('password'), $remember)) {
                if (!empty($data)) {
                    $this->is_loggedin = true;
                    $this->session->setFlashdata('message', $this->ionAuth->messages());
                    $this->data['title'] = "Dashboard | $app_name - Get Services On Demand";
                    if (exists(["phone" => $this->request->getVar('identity')], 'users')) {
                        $this->session->setFlashdata('message',);
                    }
                    $this->data['main_page'] = "auth/index";
                    // if ($this->ionAuth->isAdmin()) {
                    //     return redirect()->to('/admin/dashboard')->withCookies();
                    // } else {
                    //     // echo "dsbfs";
                    //     // die;
                    //     return redirect()->to('/partner/dashboard')->withCookies();
                    // }
                    if ($data->group_id == 1) {
                        return redirect()->to('/admin/dashboard')->withCookies();
                    } else if ($data->group_id == 3) {
                        // 
                        return redirect()->to('/partner/dashboard')->withCookies();
                    }
                } else {
                    // if the login was un-successful
                    // redirect them back to the login page
                    $this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
                    // use redirects instead of loading views for compatibility with MY_Controller libraries
                    return redirect()->back()->withInput();
                }
            } else {
                // the user is not logging in so display the login page
                // set the flash data error message if there is one
                $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
                $this->data['identity'] = [
                    'name'  => 'identity',
                    'id'    => 'identity',
                    'type'  => 'text',
                    'value' => set_value('identity'),
                ];
                $this->data['password'] = [
                    'name' => 'password',
                    'id'   => 'password',
                    'type' => 'password',
                ];
                $this->data['title'] = "Login &mdash; $app_name - Get Services On Demand";
                $this->data['main_page'] = "login";
                $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
                $this->data['meta_description'] = "Login to $app_name. $app_name is one of the leading ";
                return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
            }
        }
    }
    /**
     * Log the user out
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
    {
        $this->data['title'] = 'Logout';
        $this->session->setFlashdata('logout_msg', "<ul class='mb-0'><li>Logged Out Successfully</li><ul>");
        $this->is_loggedin = false;
        if ($this->ionAuth->isAdmin()) {
            $this->ionAuth->logout();
            return redirect()->to('/admin/login')->withCookies();
        } else {
            $this->ionAuth->logout();
            return redirect()->to('/partner/login')->withCookies();
        }
    }
    /**
     * Change password
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function change_password()
    {
        $settings = get_settings('general_settings', true);
        $app_name = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
        if (!$this->ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }
        $this->validation->setRule('old', lang('Auth.change_password_validation_old_password_label'), 'required');
        $this->validation->setRule('new', lang('Auth.change_password_validation_new_password_label'), 'required|min_length[' . $this->configIonAuth->minPasswordLength . ']|matches[new_confirm]');
        $this->validation->setRule('new_confirm', lang('Auth.change_password_validation_new_password_confirm_label'), 'required');
        $user = $this->ionAuth->user()->row();
        if (!$this->request->getPost() || $this->validation->withRequest($this->request)->run() === false) {
            // display the form
            // set the flash data error message if there is one
            $this->data['message'] = ($this->validation->getErrors()) ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
            $this->data['minPasswordLength'] = $this->configIonAuth->minPasswordLength;
            $this->data['old_password'] = [
                'name' => 'old',
                'id'   => 'old',
                'type' => 'password',
            ];
            $this->data['new_password'] = [
                'name'    => 'new',
                'id'      => 'new',
                'type'    => 'password',
                'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
            ];
            $this->data['new_password_confirm'] = [
                'name'    => 'new_confirm',
                'id'      => 'new_confirm',
                'type'    => 'password',
                'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
            ];
            $this->data['user_id'] = [
                'name'  => 'user_id',
                'id'    => 'user_id',
                'type'  => 'hidden',
                'value' => $user->id,
            ];
            // render
            $this->data['title'] = "Change Password | $app_name - Get Services On Demand";
            $this->data['main_page'] = "auth/change_password";
            $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
            $this->data['meta_description'] = "Change to $app_name. $app_name is one of the leading ";
            return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
        } else {
            $identity = $this->session->get('identity');
            $change = $this->ionAuth->changePassword($identity, $this->request->getPost('old'), $this->request->getPost('new'), $this->userId);
            if ($change) {
                //if the password was successfully changed
                $this->session->setFlashdata('message', $this->ionAuth->messages());
                return $this->logout();
            } else {
                $this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
                return redirect()->to('/auth/change_password');
            }
        }
    }
    /**
     * Forgot password
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function forgot_password()
    {
        $settings = get_settings('general_settings', true);
        $app_name = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
        $this->data['data'] = $settings;
        $this->data['title'] = lang('Auth.forgot_password_heading');
        // setting validation rules by checking whether identity is username or email
        if ($this->configIonAuth->identity !== 'email') {
            $this->validation->setRule('identity', lang('Auth.forgot_password_identity_label'), 'required');
        } else {
            $this->validation->setRule('identity', lang('Auth.forgot_password_validation_email_label'), 'required|valid_email');
        }
        if (!($this->request->getPost() && $this->validation->withRequest($this->request)->run())) {
            $this->data['type'] = $this->configIonAuth->identity;
            // setup the input
            $this->data['identity'] = [
                'name' => 'identity',
                'id'   => 'identity',
            ];
            if ($this->configIonAuth->identity !== 'email') {
                $this->data['identity_label'] = lang('Auth.forgot_password_identity_label');
            } else {
                $this->data['identity_label'] = lang('Auth.forgot_password_email_identity_label');
            }
            // set any errors and display the form
            $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
            $this->data['title'] = "Reset Password | $app_name - Get Services On Demand";
            $this->data['main_page'] = "forgot_password";
            $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
            $this->data['meta_description'] = "Resetting forgot password of $app_name. $app_name is one of the leading ";
            return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
        } else {
            // $identityColumn = $this->configIonAuth->identity;
            $identity = $this->ionAuth->where("email", $this->request->getPost('identity'))->users()->row();
            $check = (array)$identity;
            if (empty($check)) {
                $this->session->setFlashdata('no_id', '<div class="alert alert-danger" id="infoMessage"><p class="mb-0">User does not Exists</p></div>');
                return redirect()->to('/auth/forgot-password');
            }
            if (empty($identity)) {
                $this->ionAuth->setError('Auth.forgot_password_email_not_found');
                // if ($this->configIonAuth->identity !== 'email') {
                //     $this->ionAuth->setError('Auth.forgot_password_identity_not_found');
                // } else {
                // }
                $this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
                return redirect()->to('/auth/forgot-password');
            }
            // run the forgotten password method to email an activation code to the user
            $forgotten = $this->ionAuth->forgottenPassword($identity->{$this->configIonAuth->identity});
            if ($forgotten) {
                // if there were no errors
                $this->session->setFlashdata('message', $this->ionAuth->messages());
                return redirect()->to('/auth/login'); //we should display a confirmation page here instead of the login page
            } else {
                $this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
                return redirect()->to('/auth/forgot-password');
            }
        }
    }
    /**
     * Reset password - final step for forgotten password
     *
     * @param string|null $code The reset code
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function reset_password($code = null)
    {
        // $settings = get_settings('general_settings', true);
        // $app_name = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
        // if (!$code) {
        //     throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        // }
        // $this->data['title'] = lang('Auth.reset_password_heading');
        // $user = $this->ionAuth->forgottenPasswordCheck($code);
        // if ($user) {
        //     // if the code is valid then display the password reset form
        //     $this->validation->setRule('new', lang('Auth.reset_password_validation_new_password_label'), 'required|min_length[' . $this->configIonAuth->minPasswordLength . ']|matches[new_confirm]');
        //     $this->validation->setRule('new_confirm', lang('Auth.reset_password_validation_new_password_confirm_label'), 'required');
        //     if (!$this->request->getPost() || $this->validation->withRequest($this->request)->run() === false) {
        //         // display the form
        //         // set the flash data error message if there is one
        //         $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
        //         $this->data['minPasswordLength'] = $this->configIonAuth->minPasswordLength;
        //         $this->data['new_password'] = [
        //             'name'    => 'new',
        //             'id'      => 'new',
        //             'type'    => 'password',
        //             'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
        //         ];
        //         $this->data['new_password_confirm'] = [
        //             'name'    => 'new_confirm',
        //             'id'      => 'new_confirm',
        //             'type'    => 'password',
        //             'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
        //         ];
        //         $this->data['user_id'] = [
        //             'name'  => 'user_id',
        //             'id'    => 'user_id',
        //             'type'  => 'hidden',
        //             'value' => $user->id,
        //         ];
        //         $this->data['code'] = $code;
        //         // render
        //         $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
        //         $this->data['title'] = "Reset password | $app_name - Get Services On Demand";
        //         $this->data['main_page'] = "auth/reset_password";
        //         $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
        //         $this->data['meta_description'] = "Resetting forgot password of $app_name. $app_name is one of the leading ";
        //         return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
        //     } else {
        //         $identity = $user->{$this->configIonAuth->identity};
        //         // do we have a valid request?
        //         if ($user->id != $this->request->getPost('user_id')) {
        //             // something fishy might be up
        //             $this->ionAuth->clearForgottenPasswordCode($identity);
        //             throw new \Exception(lang('Auth.error_security'));
        //         } else {
        //             // finally change the password
        //             $change = $this->ionAuth->resetPassword($identity, $this->request->getPost('new'));
        //             if ($change) {
        //                 // if the password was successfully changed
        //                 $this->session->setFlashdata('message', $this->ionAuth->messages());
        //                 return redirect()->to('/auth/login');
        //             } else {
        //                 $this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
        //                 return redirect()->to('/auth/reset-password/' . $code);
        //             }
        //         }
        //     }
        // } else {
        //     // if the code is invalid then send them back to the forgot password page
        //     $this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
        //     return redirect()->to('/auth/forgot-password');
        // }
        $validation =  \Config\Services::validation();
        $validation->setRules(
            [
                'new_password' => 'required',
                'mobile_number' => 'required',
                'country_code' => 'required',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => []
            ];
            return $this->response->setJSON($response);
        }
        $identity = $this->request->getPost('mobile_number');
        $user_data = fetch_details('users', ['phone' => $identity]);
        if (empty($user_data)) {
            return $this->response->setJSON([
                'error' => false,
                'message' => "User does not exist",
                "data" => $_POST,
            ]);
        }
        if ((($user_data[0]['country_code'] == null) || ($user_data[0]['country_code'] == $this->request->getPost('country_code'))) && (($user_data[0]['phone'] == $identity))) {
            $change = $this->ionAuth->resetPassword($identity, $this->request->getPost('new_password'));
            if ($change) {
                $this->ionAuth->logout();
                return $this->response->setJSON([
                    'error' => false,
                    'message' => "Forgot Password  successfully",
                    "data" => $_POST,
                ]);
            } else {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => $this->ionAuth->errors($this->validationListTemplate),
                    "data" => $_POST,
                ]);
            }
            $change = $this->ionAuth->resetPassword($identity, $this->request->getPost('new_password'));
            if ($change) {
                $this->ionAuth->logout();
                return $this->response->setJSON([
                    'error' => false,
                    'message' => "Forgot Password  successfully",
                    "data" => $_POST,
                ]);
            } else {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => $this->ionAuth->errors($this->validationListTemplate),
                    "data" => $_POST,
                ]);
            }
        } else {
            return $this->response->setJSON([
                'error' => true,
                'message' => "Faorgot Password Failed",
                "data" => $_POST,
            ]);
        }
    }
    /**
     * Activate the user
     *
     * @param integer $id   The user ID
     * @param string  $code The activation code
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function activate(int $id, string $code = ''): \CodeIgniter\HTTP\RedirectResponse
    {
        $activation = false;
        if ($code) {
            $activation = $this->ionAuth->activate($id, $code);
        } else if ($this->ionAuth->isAdmin()) {
            $activation = $this->ionAuth->activate($id);
        }
        if ($activation) {
            // redirect them to the auth page
            $this->session->setFlashdata('message', $this->ionAuth->messages());
            return redirect()->to('/auth');
        } else {
            // redirect them to the forgot password page
            $this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
            return redirect()->to('/auth/forgot_password');
        }
    }
    /**
     * Deactivate the user
     *
     * @param integer $id The user ID
     *
     * @throw Exception
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function deactivate(int $id = 0)
    {

        
        $settings = get_settings('general_settings', true);
        $app_name = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
        if (!$this->ionAuth->loggedIn() || !$this->ionAuth->isAdmin()) {
            // redirect them to the home page because they must be an administrator to view this
            throw new \Exception('You must be an administrator to view this page.');
            // TODO : I think it could be nice to have a dedicated exception like '\IonAuth\Exception\NotAllowed
        }
        $this->validation->setRule('confirm', lang('Auth.deactivate_validation_confirm_label'), 'required');
        $this->validation->setRule('id', lang('Auth.deactivate_validation_user_id_label'), 'required|integer');
        if (!$this->validation->withRequest($this->request)->run()) {
            $this->data['user'] = $this->ionAuth->user($id)->row();
            $this->data['title'] = "Deactivate User | $app_name - Get Services On Demand";
            $this->data['main_page'] = "auth/deactivate_user";
            $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
            $this->data['meta_description'] = "Deactivate $app_name account$app_name. $app_name is one of the leading ";
            return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
        } else {
            // do we really want to deactivate?
            if ($this->request->getPost('confirm') === 'yes') {
                // do we have a valid request?
                if ($id !== $this->request->getPost('id', FILTER_VALIDATE_INT)) {
                    throw new \Exception(lang('Auth.error_security'));
                }
                // do we have the right userlevel?
                if ($this->ionAuth->loggedIn() && $this->ionAuth->isAdmin()) {
                    $message = $this->ionAuth->deactivate($id) ? $this->ionAuth->messages() : $this->ionAuth->errors($this->validationListTemplate);
                    $this->session->setFlashdata('message', $message);
                }
            }
            // redirect them back to the auth page
            return redirect()->to('/auth');
        }
    }
    /**
     * Create a new user
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function create_user()
    {
        $settings = get_settings('general_settings', true);
        $app_name = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
        $this->data['title'] = lang('Auth.create_user_heading');
        if ($this->ionAuth->loggedIn()) {
            return redirect()->to('/auth');
        }
        $tables                        = $this->configIonAuth->tables;
        $identityColumn                = $this->configIonAuth->identity;
        $this->data['identity_column'] = $identityColumn;
        // validate form input
        $this->validation->setRule('first_name', 'Enter First Name', 'required');
        $this->validation->setRule('last_name', 'Enter Last Name', 'required');
        $this->validation->setRule('email', 'Enter Email Address', 'required|valid_email|is_unique[' . $tables['users'] . '.email]');
        $this->validation->setRule('phone', 'Enter Mobile Number', 'required|is_unique[' . $tables['users'] . '.phone]');
        $this->validation->setRule('password', 'Enter Password', 'required||matches[password_confirm]');
        $this->validation->setRule('password_confirm', 'Confirm Password', 'required');
        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            $email    = strtolower($this->request->getPost('email'));
            $identity = $email;
            $password = $this->request->getPost('password');
            $additionalData = [
                'first_name' => $this->request->getPost('first_name'),
                'last_name'  => $this->request->getPost('last_name'),
                'phone'      => $this->request->getPost('phone'),
                'country_code' => $this->request->getPost('country_code'),
            ];
        }
        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run() && $this->ionAuth->register($identity, $password, $email, $additionalData)) {
            // check to see if we are creating the user
            // redirect them back to the admin page
            $this->session->setFlashdata('message', $this->ionAuth->messages());
            return redirect()->to('/auth');
        } else {
            // display the create user form
            // set the flash data error message if there is one
            $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->ionAuth->errors($this->validationListTemplate) ? $this->ionAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));
            $this->data['title'] = "Registration | $app_name - On Demand Services";
            $this->data['main_page'] = "register";
            $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
            $this->data['meta_description'] = "Register to $app_name. $app_name is one of the leading ";
            return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
        }
    }
    /**
     * Redirect a user checking if is admin
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function redirectUser()
    {
        if ($this->ionAuth->isAdmin()) {
            return redirect()->to('/auth');
        }
        return redirect()->to('/');
    }
    /**
     * Edit a user
     *
     * @param integer $id User id
     *
     * @return string string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function edit_user(int $id)
    {
        $settings = get_settings('general_settings', true);
        $app_name = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
        $this->data['title'] = lang('Auth.edit_user_heading');
        if (!$this->ionAuth->loggedIn() || (!$this->ionAuth->isAdmin() && !($this->ionAuth->user()->row()->id == $id))) {
            return redirect()->to('/auth');
        }
        $user          = $this->ionAuth->user($id)->row();
        $groups        = $this->ionAuth->groups()->resultArray();
        $currentGroups = $this->ionAuth->getUsersGroups($id)->getResult();
        if (!empty($_POST)) {
            // validate form input
            $this->validation->setRule('first_name', lang('Auth.edit_user_validation_fname_label'), 'required');
            $this->validation->setRule('last_name', lang('Auth.edit_user_validation_lname_label'), 'required');
            $this->validation->setRule('phone', lang('Auth.edit_user_validation_phone_label'), 'required');
            $this->validation->setRule('company', lang('Auth.edit_user_validation_company_label'), 'required');
            // do we have a valid request?
            if ($id !== $this->request->getPost('id', FILTER_VALIDATE_INT)) {
                throw new \Exception(lang('Auth.error_security'));
            }
            // update the password if it was posted
            if ($this->request->getPost('password')) {
                $this->validation->setRule('password', lang('Auth.edit_user_validation_password_label'), 'required|min_length[' . $this->configIonAuth->minPasswordLength . ']|matches[password_confirm]');
                $this->validation->setRule('password_confirm', lang('Auth.edit_user_validation_password_confirm_label'), 'required');
            }
            if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
                $data = [
                    'first_name' => $this->request->getPost('first_name'),
                    'last_name'  => $this->request->getPost('last_name'),
                    'company'    => $this->request->getPost('company'),
                    'phone'      => $this->request->getPost('phone'),
                ];
                // update the password if it was posted
                if ($this->request->getPost('password')) {
                    $data['password'] = $this->request->getPost('password');
                }
                // Only allow updating groups if user is admin
                if ($this->ionAuth->isAdmin()) {
                    // Update the groups user belongs to
                    $groupData = $this->request->getPost('groups');
                    if (!empty($groupData)) {
                        $this->ionAuth->removeFromGroup('', $id);
                        foreach ($groupData as $grp) {
                            $this->ionAuth->addToGroup($grp, $id);
                        }
                    }
                }
                // check to see if we are updating the user
                if ($this->ionAuth->update($user->id, $data)) {
                    $this->session->setFlashdata('message', $this->ionAuth->messages());
                } else {
                    $this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
                }
                // redirect them back to the admin page if admin, or to the base url if non admin
                return $this->redirectUser();
            }
        }
        // display the edit user form
        // set the flash data error message if there is one
        $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->ionAuth->errors($this->validationListTemplate) ? $this->ionAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));
        // pass the user to the view
        $this->data['user']          = $user;
        $this->data['groups']        = $groups;
        $this->data['currentGroups'] = $currentGroups;
        $this->data['first_name'] = [
            'name'  => 'first_name',
            'id'    => 'first_name',
            'type'  => 'text',
            'value' => set_value('first_name', $user->first_name ?: ''),
        ];
        $this->data['last_name'] = [
            'name'  => 'last_name',
            'id'    => 'last_name',
            'type'  => 'text',
            'value' => set_value('last_name', $user->last_name ?: ''),
        ];
        $this->data['company'] = [
            'name'  => 'company',
            'id'    => 'company',
            'type'  => 'text',
            'value' => set_value('company', empty($user->company) ? '' : $user->company),
        ];
        $this->data['phone'] = [
            'name'  => 'phone',
            'id'    => 'phone',
            'type'  => 'text',
            'value' => set_value('phone', empty($user->phone) ? '' : $user->phone),
        ];
        $this->data['password'] = [
            'name' => 'password',
            'id'   => 'password',
            'type' => 'password',
        ];
        $this->data['password_confirm'] = [
            'name' => 'password_confirm',
            'id'   => 'password_confirm',
            'type' => 'password',
        ];
        $this->data['ionAuth'] = $this->ionAuth;
        $this->data['title'] = "Edit User | $app_name - Get Services On Demand";
        $this->data['main_page'] = "auth/edit_user";
        $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
        $this->data['meta_description'] = "Edit $app_name. $app_name is one of the leading ";
        return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
    }
    /**
     * Create a new group
     *
     * @return string string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function create_group()
    {
        $settings = get_settings('general_settings', true);
        $app_name = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
        $this->data['title'] = lang('Auth.create_group_title');
        if (!$this->ionAuth->loggedIn() || !$this->ionAuth->isAdmin()) {
            return redirect()->to('/auth');
        }
        // validate form input
        $this->validation->setRule('group_name', lang('Auth.create_group_validation_name_label'), 'required|alpha_dash');
        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            $newGroupId = $this->ionAuth->createGroup($this->request->getPost('group_name'), $this->request->getPost('description'));
            if ($newGroupId) {
                // check to see if we are creating the group
                // redirect them back to the admin page
                $this->session->setFlashdata('message', $this->ionAuth->messages());
                return redirect()->to('/auth');
            }
        } else {
            // display the create group form
            // set the flash data error message if there is one
            $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->ionAuth->errors($this->validationListTemplate) ? $this->ionAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));
            $this->data['group_name'] = [
                'name'  => 'group_name',
                'id'    => 'group_name',
                'type'  => 'text',
                'value' => set_value('group_name'),
            ];
            $this->data['description'] = [
                'name'  => 'description',
                'id'    => 'description',
                'type'  => 'text',
                'value' => set_value('description'),
            ];
            // render
            $this->data['title'] = "Create new user group | $app_name - Get Services On Demand";
            $this->data['main_page'] = "auth/create_group";
            $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
            $this->data['meta_description'] = "Create new group for $app_name. $app_name is one of the leading ";
            return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
        }
    }
    /**
     * Edit a group
     *
     * @param integer $id Group id
     *
     * @return string|CodeIgniter\Http\Response
     */
    public function edit_group(int $id = 0)
    {
        $settings = get_settings('general_settings', true);
        $app_name = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
        // bail if no group id given
        if (!$id) {
            return redirect()->to('/auth');
        }
        $this->data['title'] = lang('Auth.edit_group_title');
        if (!$this->ionAuth->loggedIn() || !$this->ionAuth->isAdmin()) {
            return redirect()->to('/auth');
        }
        $group = $this->ionAuth->group($id)->row();
        // validate form input
        $this->validation->setRule('group_name', lang('Auth.edit_group_validation_name_label'), 'required|alpha_dash');
        if ($this->request->getPost()) {
            if ($this->validation->withRequest($this->request)->run()) {
                $groupUpdate = $this->ionAuth->updateGroup($id, $this->request->getPost('group_name'), ['description' => $this->request->getPost('group_description')]);
                if ($groupUpdate) {
                    $this->session->setFlashdata('message', lang('Auth.edit_group_saved'));
                } else {
                    $this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
                }
                return redirect()->to('/auth');
            }
        }
        // set the flash data error message if there is one
        $this->data['message'] = $this->validation->listErrors($this->validationListTemplate) ?: ($this->ionAuth->errors($this->validationListTemplate) ?: $this->session->getFlashdata('message'));
        // pass the user to the view
        $this->data['group'] = $group;
        $readonly = $this->configIonAuth->adminGroup === $group->name ? 'readonly' : '';
        $this->data['group_name']        = [
            'name'    => 'group_name',
            'id'      => 'group_name',
            'type'    => 'text',
            'value'   => set_value('group_name', $group->name),
            $readonly => $readonly,
        ];
        $this->data['group_description'] = [
            'name'  => 'group_description',
            'id'    => 'group_description',
            'type'  => 'text',
            'value' => set_value('group_description', $group->description),
        ];
        $this->data['title'] = "Edit Group | $app_name - Get Services On Demand";
        $this->data['main_page'] = "auth/edit_group";
        $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
        $this->data['meta_description'] = "Edit to $app_name. $app_name is one of the leading ";
        return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
    }
    /**
     *  THIS WILL CREATE PARTNERS
     */
    // public function create_partner()
    // {
    //     // print_r($this->request->getPost('store_country_code'));
    //     // die;
    //     $this->user = new Users_model();
    //     $settings = get_settings('general_settings', true);
    //     $app_name = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
    //     $this->data['title'] = lang('Auth.create_user_heading');
    //     if ($this->ionAuth->loggedIn()) {
    //         return redirect()->to('/auth');
    //     }
    //     $tables                        = $this->configIonAuth->tables;
    //     $identityColumn                = $this->configIonAuth->identity;
    //     $this->data['identity_column'] = $identityColumn;
    //     // validate form input
    //     $this->validation->setRule('username', 'Enter First Name', 'required');
    //     $this->validation->setRule('email', 'Enter Email Address', 'required|valid_email|is_unique[' . $tables['users'] . '.email]');
    //     $this->validation->setRule('phone', 'Enter Mobile Number', 'required');
    //     $this->validation->setRule('password', 'Enter Password', 'required|matches[password_confirm]');
    //     $this->validation->setRule('password_confirm', 'Confirm Password', 'required');
    //     $this->validation->setRule('company_name', 'Enter Company Name', 'required');
    //     $additionalData = [];
    //     $company = '';
    //     $phone = '';
    //     $user_name = '';
    //     if ($this->validation->withRequest($this->request)->run()) {
    //         $email    = strtolower($this->request->getPost('email'));
    //         $identity = $email;
    //         $password = $this->request->getPost('password');
    //         $company = $this->request->getPost('company_name');
    //         $phone = $this->request->getPost('phone');
    //         $user_name = $this->request->getPost('first_name');
    //         $additionalData = [
    //             'username' => $user_name,
    //             'phone'      => $this->request->getPost('phone'),
    //             'company' => $company,
    //             'country_code' =>$this->request->getPost('store_country_code'),
    //         ];
    //     }
    //     $group_id = [
    //         'id' => 3
    //     ];
    //     $rules = [
    //         'email' => [
    //             "rules" => 'required|valid_email|is_unique[' . $tables['users'] . '.email]',
    //             "errors" => [
    //                 "required" => "Please enter username"
    //             ]
    //         ],
    //         'phone' => [
    //             "rules" => 'required',
    //             "errors" => [
    //                 "required" => "Enter Mobile Number"
    //             ]
    //         ],
    //         'password' => [
    //             "rules" => 'required|matches[password_confirm]',
    //             "errors" => [
    //                 "required" => "Enter Password"
    //             ]
    //         ],
    //         'password_confirm' => [
    //             "rules" => 'required',
    //             "errors" => [
    //                 "required" => "Enter Confirm Password"
    //             ]
    //         ],
    //         'company_name' => [
    //             "rules" => 'required',
    //             "errors" => [
    //                 "required" => "Enter Company Name"
    //             ]
    //         ],
    //     ];
    //     $mobile_data  = fetch_details('users', ['phone' => $phone]);
    //     if (!empty($mobile_data) && $mobile_data[0]['phone']) {
    //         $response['error'] = true;
    //         $response['message'] = "Phone number already exists please use another one";
    //         $response['csrfName'] = csrf_token();
    //         $response['csrfHash'] = csrf_hash();
    //         $response['data'] = [];
    //         return $this->response->setJSON($response);
    //     }
    //     if (!empty($email_data) && $email_data[0]['email']) {
    //         $response['error'] = true;
    //         $response['message'] = "Email already exists please use another one";
    //         $response['csrfName'] = csrf_token();
    //         $response['csrfHash'] = csrf_hash();
    //         $response['data'] = [];
    //         return $this->response->setJSON($response);
    //     }
    //     $this->validation->setRules($rules);
    //     if (!$this->validation->withRequest($this->request)->run()) {
    //         $errors  = $this->validation->getErrors();
    //         $response['error'] = true;
    //         $response['message'] = $errors;
    //         $response['csrfName'] = csrf_token();
    //         $response['csrfHash'] = csrf_hash();
    //         $response['data'] = [];
    //         return $this->response->setJSON($response);
    //     }
    //     $id =  $this->ionAuth->register($identity, $password, $email, $additionalData, $group_id);
    //     $email    = strtolower($this->request->getPost('email'));
    //     $identity = $email;
    //     $password = $this->request->getPost('password');
    //     $company = $this->request->getPost('company_name');
    //     $phone = $this->request->getPost('phone');
    //     $user_name = $this->request->getPost('first_name');
    //     $additionalData = [
    //         'username' => $user_name,
    //         'phone'      => $this->request->getPost('phone'),
    //         'company' => $company,
    //         'country_code'      => $this->request->getPost('store_country_code'),
    //     ];
    //     $data['phone'] = $phone;
    //     $data['username'] = $user_name;
    //     $data['country_code']=$this->request->getPost('store_country_code');
    //     $this->user->update($id, $data);
    //     $partner_data = [
    //         'partner_id' => $id,
    //         'company_name' => $company,
    //         'is_approved' => 0
    //     ];
    //     $data = $this->partner_model->save($partner_data);
    //     if ($data) {
    //         $response['error'] = false;
    //         $response['message'] = "Successfully login";
    //         $response['csrfName'] = csrf_token();
    //         $response['csrfHash'] = csrf_hash();
    //         $response['data'] = [];
    //         return $this->response->setJSON($response);
    //         // $this->session->setFlashdata('message', $this->ionAuth->messages());
    //         // return redirect()->to('/partner/login');
    //     }
    //     // } 
    //     else {
    //         // display the create user form
    //         // set the flash data error message if there is one
    //         $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->ionAuth->errors($this->validationListTemplate) ? $this->ionAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));
    //         $this->data['title'] = "Registration | $app_name - On Demand Services";
    //         $this->data['main_page'] = "register";
    //         $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
    //         $this->data['meta_description'] = "Register to $app_name. $app_name is one of the leading ";
    //         return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
    //     }
    // }
    public function create_partner()
    {
        // print_r($this->request->getPost('store_country_code'));
        // die;
        $this->user = new Users_model();
        $settings = get_settings('general_settings', true);
        $app_name = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
        $this->data['title'] = lang('Auth.create_user_heading');
        if ($this->ionAuth->loggedIn()) {
            return redirect()->to('/auth');
        }
        $tables                        = $this->configIonAuth->tables;
        $identityColumn                = $this->configIonAuth->identity;
        $this->data['identity_column'] = $identityColumn;
        // validate form input
        $this->validation->setRule('username', 'Enter First Name', 'required');
        $this->validation->setRule('email', 'Enter Email Address', 'required|valid_email|is_unique[' . $tables['users'] . '.email]');
        $this->validation->setRule('phone', 'Enter Mobile Number', 'required');
        $this->validation->setRule('password', 'Enter Password', 'required|matches[password_confirm]');
        $this->validation->setRule('password_confirm', 'Confirm Password', 'required');
        $this->validation->setRule('company_name', 'Enter Company Name', 'required');
        $additionalData = [];
        $company = '';
        $phone = '';
        $user_name = '';
        if ($this->validation->withRequest($this->request)->run()) {
            $email    = strtolower($this->request->getPost('email'));
            $identity = $email;
            $password = $this->request->getPost('password');
            $company = $this->request->getPost('company_name');
            $phone = $this->request->getPost('phone');
            $user_name = $this->request->getPost('username');
            $additionalData = [
                'username' => $user_name,
                'phone'      => $this->request->getPost('phone'),
                'company' => $company,
                'country_code' => $this->request->getPost('store_country_code'),
            ];
        }
        $group_id = [
            'id' => 3
        ];
        $rules = [
            'email' => [
                "rules" => 'required|valid_email|is_unique[' . $tables['users'] . '.email]',
                "errors" => [
                    "required" => "Please enter username"
                ]
            ],
            'phone' => [
                "rules" => 'required',
                "errors" => [
                    "required" => "Enter Mobile Number"
                ]
            ],
            'password' => [
                "rules" => 'required|matches[password_confirm]',
                "errors" => [
                    "required" => "Enter Password",
                    'matches' => 'The password confirmation does not match the password'
                ]
            ],
            'password_confirm' => [
                "rules" => 'required',
                "errors" => [
                    "required" => "Enter Confirm Password"
                ]
            ],
            'company_name' => [
                "rules" => 'required',
                "errors" => [
                    "required" => "Enter Company Name"
                ]
            ],
        ];
        // $mobile_data  = fetch_details('users', ['phone' => $phone]);
        $db      = \Config\Database::connect();
        $builder = $db->table('users u');
        $builder->select('u.*,ug.group_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', 3)
            ->where(['phone' =>  $phone]);
        $mobile_data = $builder->get()->getResultArray();
        if (!empty($mobile_data) && $mobile_data[0]['phone']) {
            $response['error'] = true;
            $response['message'] = "Phone number already exists please use another one";
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            $response['data'] = [];
            return $this->response->setJSON($response);
        }
        if (!empty($email_data) && $email_data[0]['email']) {
            $response['error'] = true;
            $response['message'] = "Email already exists please use another one";
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            $response['data'] = [];
            return $this->response->setJSON($response);
        }
        $this->validation->setRules($rules);
        if (!$this->validation->withRequest($this->request)->run()) {
            // $errors  = $this->validation->getErrors();
            // $response['error'] = true;
            // $response['message'] = $errors;
            // $response['csrfName'] = csrf_token();
            // $response['csrfHash'] = csrf_hash();
            // $response['data'] = [];
            // return $this->response->setJSON($response);
            $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->ionAuth->errors($this->validationListTemplate) ? $this->ionAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));
            $this->data['title'] = "Registration | $app_name - On Demand Services";
            $this->data['main_page'] = "register";
            $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
            $this->data['meta_description'] = "Register to $app_name. $app_name is one of the leading ";
            return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
        }
        $id =  $this->ionAuth->register($identity, $password, $email, $additionalData, $group_id);
        $email    = strtolower($this->request->getPost('email'));
        $identity = $email;
        $password = $this->request->getPost('password');
        $company = $this->request->getPost('company_name');
        $phone = $this->request->getPost('phone');
        $user_name = $this->request->getPost('username');
        $additionalData = [
            'username' => $user_name,
            'phone'      => $this->request->getPost('phone'),
            'company' => $company,
            'country_code'      => $this->request->getPost('store_country_code'),
        ];
        $data['phone'] = $phone;
        $data['username'] = $user_name;
        $data['country_code'] = $this->request->getPost('store_country_code');
        $this->user->update($id, $data);
        $partner_data = [
            'partner_id' => $id,
            'company_name' => $company,
            'is_approved' => 0
        ];
        $tempRowDaysIsOpen = array(0, 0, 0, 0, 0, 0, 0);
        $rowsDays = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        $tempRowDays = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        $tempRowStartTime = array('00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00');
        $tempRowEndTime = array('00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00');
        for ($i = 0; $i < count($tempRowStartTime); $i++) {
            $partner_timing = [];
            $partner_timing['day'] = $tempRowDays[$i];
            if (isset($tempRowStartTime[$i])) {
                $partner_timing['opening_time'] = $tempRowStartTime[$i];
            }
            if (isset($tempRowEndTime[$i])) {
                $partner_timing['closing_time'] = $tempRowEndTime[$i];
            }
            $partner_timing['is_open'] = $tempRowDaysIsOpen[$i];
            $partner_timing['partner_id'] = $id;
            insert_details($partner_timing, 'partner_timings');
        }
        $data = $this->partner_model->save($partner_data);
        if ($data) {
            $response['error'] = false;
            $response['message'] = "Successfully login";
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            $response['data'] = [];
            // return $this->response->setJSON($response);
            $this->session->setFlashdata('message', $this->ionAuth->messages());
            return redirect()->to('/partner/login');
        }
        // } 
        else {
            // display the create user form
            // set the flash data error message if there is one
            $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->ionAuth->errors($this->validationListTemplate) ? $this->ionAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));
            $this->data['title'] = "Registration | $app_name - On Demand Services";
            $this->data['main_page'] = "register";
            $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
            $this->data['meta_description'] = "Register to $app_name. $app_name is one of the leading ";
            return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
        }
    }
    public function create_admin()
    {
        $settings = get_settings('general_settings', true);
        $app_name = (isset($settings['company_title']) && $settings['company_title'] != "") ? $settings['company_title'] : "eDemand";
        $this->data['title'] = lang('Auth.create_user_heading');
        $tables                        = $this->configIonAuth->tables;
        $identityColumn                = $this->configIonAuth->identity;
        $this->data['identity_column'] = $identityColumn;
        // validate form input
        $email    = strtolower('mail@mail.com');
        $identity = $email;
        $password = "12345678";
        $phone = "1234567800";
        $user_name = "test_test";
        $additionalData = [
            'username' => $user_name,
            'phone'      => $phone,
        ];
        $group_id = [
            'id' => 1
        ];
        $id =  $this->ionAuth->register($identity, $password, $email, $additionalData, $group_id);
        if ($id) {
            update_details(
                ['phone' => $phone, 'username' => $user_name],
                ['id' => $id],
                'users'
            );
            return $id;
        } else {
            // display the create user form
            // set the flash data error message if there is one
            $this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->ionAuth->errors($this->validationListTemplate) ? $this->ionAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));
            $this->data['title'] = "Registration | $app_name - On Demand Services";
            $this->data['main_page'] = "register";
            $this->data['meta_keywords'] = "On Demand, Services,On Demand Services, Service Provider";
            $this->data['meta_description'] = "Register to $app_name. $app_name is one of the leading ";
            return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'template', $this->data);
        }
    }
    /**
     * Render the specified view
     *
     * @param string     $view       The name of the file to load
     * @param array|null $data       An array of key/value pairs to make available within the view.
     * @param boolean    $returnHtml If true return html string
     *
     * @return string|void
     */
    protected function renderPage(string $view, $data = null, bool $returnHtml = true): string
    {
        $viewdata = $data ?: $this->data;
        $viewHtml = view($view, $viewdata);
        if ($returnHtml) {
            return $viewHtml;
        } else {
            echo $viewHtml;
        }
    }
    public function check_number()
    {
        $this->validation = \Config\Services::validation();
        $request = \Config\Services::request();
        $number = $request->getPost('number');
        $db      = \Config\Database::connect();
        $builder = $db->table('users u');
        $builder->select('u.*,ug.group_id')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', 3)
            ->where(['phone' =>  $number]);
        $mobile_data = $builder->get()->getResultArray();
        $authentication_mode = get_settings('general_settings', true);
        if (!empty($mobile_data) && $mobile_data[0]['phone']) {
            $response['error'] = true;
            $response['message'] = "Phone number already exists please use another one";
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            $response['data'] = ['authentication_mode' => $authentication_mode['authentication_mode']];
            return $this->response->setJSON($response);
        } else {
            $error = false;
            $message = "number not exist";
            $response['error'] = $error;
            $response['message'] = $message;
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            $response['data'] = ['authentication_mode' => $authentication_mode['authentication_mode']];
            return $this->response->setJSON($response);
        }
    }
    public function partner_privacy_policy()
    {
        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'Partner Privacy Policy | ' . $settings['company_title'];
        $this->data['meta_description'] = 'Privacy Policy | ' . $settings['company_title'];
        $this->data['privacy_policy'] = get_settings('privacy_policy', true);
        $this->data['settings'] =  $settings;
        return view('view_partner_privacy_policy.php', $this->data);
    }
    public function customer_privacy_policy()
    {
        $settings = get_settings('general_settings', true);
        $this->data['title'] = 'Customer Privacy Policy | ' . $settings['company_title'];
        $this->data['meta_description'] = 'Privacy Policy | ' . $settings['company_title'];
        $this->data['customer_privacy_policy'] = get_settings('customer_privacy_policy', true);
        $this->data['settings'] =  $settings;
        return view('view_customer_privacy_policy.php', $this->data);
    }
    public function check_number_for_forgot_password()
    {
        $this->validation = \Config\Services::validation();
        $request = \Config\Services::request();
        $number = $request->getPost('number');
        $mobile_data  = fetch_details('users', ['phone' => $number]);
        $authentication_mode = get_settings('general_settings', true);
        if (!empty($mobile_data) && $mobile_data[0]['phone']) {
            $response['error'] = false;
            $response['message'] = "Phone number already exists please use another one";
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            $response['data'] = ['authentication_mode' => $authentication_mode['authentication_mode']];
            return $this->response->setJSON($response);
        } else {
            $error = true;
            $message = "number not exist";
            $response['error'] = $error;
            $response['message'] = $message;
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            $response['data'] = ['authentication_mode' => $authentication_mode['authentication_mode']];
            return $this->response->setJSON($response);
        }
    }
    public function reset_password_otp()
    {
        $validation =  \Config\Services::validation();
        $validation->setRules(
            [
                'password' => 'required',
                'phone' => 'required',
                'store_country_code' => 'required',
            ]
        );
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $response = [
                'error' => true,
                'message' => $errors,
                'data' => []
            ];
            return $this->response->setJSON($response);
        }
        $identity = $this->request->getPost('phone');
        // $user_data = fetch_details('users', ['phone' => $identity]);
        if ($_POST['userType'] == "admin") {
            $db      = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', 1)
                ->where(['phone' => $identity]);
            $user_data = $builder->get()->getResultArray();
        } else if ($_POST['userType'] == "partner") {
            $db      = \Config\Database::connect();
            $builder = $db->table('users u');
            $builder->select('u.*,ug.group_id')
                ->join('users_groups ug', 'ug.user_id = u.id')
                ->where('ug.group_id', 3)
                ->where(['phone' => $identity]);
            $user_data = $builder->get()->getResultArray();
        } else {
            return $this->response->setJSON([
                'error' => false,
                'message' => "User does not exist",
                "data" => $_POST,
            ]);
        }
        if (empty($user_data)) {
            return $this->response->setJSON([
                'error' => false,
                'message' => "User does not exist",
                "data" => $_POST,
            ]);
        }
        if ((($user_data[0]['country_code'] == null) || ($user_data[0]['country_code'] == $this->request->getPost('store_country_code'))) && (($user_data[0]['phone'] == $identity))) {
            $change = $this->ionAuth->resetPassword($identity, $this->request->getPost('password'), $user_data[0]['id']);
            if ($change) {
                $this->ionAuth->logout();
                return $this->response->setJSON([
                    'error' => false,
                    'message' => "Forgot Password  successfully",
                    "data" => $_POST,
                ]);
            } else {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => $this->ionAuth->errors($this->validationListTemplate),
                    "data" => $_POST,
                ]);
            }
            $change = $this->ionAuth->resetPassword($identity, $this->request->getPost('password'), $user_data[0]['id']);
            if ($change) {
                $this->ionAuth->logout();
                return $this->response->setJSON([
                    'error' => false,
                    'message' => "Forgot Password  successfully",
                    "data" => $_POST,
                ]);
            } else {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => $this->ionAuth->errors($this->validationListTemplate),
                    "data" => $_POST,
                ]);
            }
        } else {
            return $this->response->setJSON([
                'error' => true,
                'message' => "Faorgot Password Failed",
                "data" => $_POST,
            ]);
        }
    }
    public function send_sms_otp()
    {

        $mobile = $_POST['number'];

        $is_exist = fetch_details('otps', ['mobile' => $mobile]);

        if (isset($_POST['number']) &&  empty($is_exist)) {
            $mobile_data = array(
                'mobile' => $_POST['number']
            );
            insert_details($mobile_data, 'otps');
        }
        $otp = random_int(100000, 999999);
        $mobile = $_POST['number'];
        $data = set_user_otp($mobile, $otp,$mobile);

    

        if ($data['error']==false) {   
            $response['error'] = false;
            $response['message'] = "OTP send successfully";
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            $response['data'] = [];
            return $this->response->setJSON($response);
        } else {

            $error = true;
            $response['error'] = $error;
            $response['message'] = $data['message'];
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            $response['data'] = [];
            return $this->response->setJSON($response);
        }
    }

    function verify_sms_otp(){
        $mobile=$_POST['number'];
        $otp=$_POST['code'];
        $data=fetch_details('otps',['mobile'=>$mobile,'otp'=>$otp]);
        if(!empty($data)){
            $response['error'] = false;
            $response['message'] = "OTP verified";
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            $response['data'] = [];
            return $this->response->setJSON($response);
        }else{
            $response['error'] = true;
            $response['message'] = "OTP not verified";
            $response['csrfName'] = csrf_token();
            $response['csrfHash'] = csrf_hash();
            $response['data'] = [];
            return $this->response->setJSON($response);
        }

    }

    public function providerDetails(){
        $uri = service('uri');
        $slug = $uri->getSegments()[1];
        
        $general_settings=get_settings('general_settings',true);
        

        $customerPlayStoreUrl=$general_settings['customer_playstore_url'];
        $customerAppStoreUrl=$general_settings['customer_appstore_url'];
        $providerPlayStoreUrl=$general_settings['provider_playstore_url'];
        $providerAppStoreUrl=$general_settings['provider_appstore_url'];
        $appName=$general_settings['schema_for_deeplink'];
        
        $this->data['customerPlaystoreUrl'] = $customerPlayStoreUrl;
        $this->data['customerAppStoreUrl'] = $customerAppStoreUrl;
        $this->data['providerPlayStoreUrl'] = $providerPlayStoreUrl;
        $this->data['providerAppStoreUrl'] = $providerAppStoreUrl;
        $this->data['appName'] = $appName;


       
        return view('backend/admin/pages/deep_link.php', $this->data);

      
    }
}
