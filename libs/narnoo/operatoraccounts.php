<?php

class Operatoraccount extends WebClient {

    public $url = 'http://connect.narnoo.com/accounts/';
    public $authen;

    public function __construct($api_settings) {

        $this->authen = $api_settings;
    }
    
    public function businessSearch($name) {

        $method = 'business_search';

        $this->setUrl($this->url.$method.'/'.$name);
        $this->setGet();
        try {
            $response = json_decode( $this->getResponse($this->authen),TRUE);
            return $response;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function businessList() {

        $method = 'operator_list';

        $this->setUrl($this->url.$method);
        $this->setGet();
        try {
            $response = json_decode( $this->getResponse($this->authen),TRUE);
            return $response;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function operatorCheck($email) {

        $method = 'user_check';

        $this->setUrl($this->url.$method);
        $this->setPost('email='.$email);
        try {
            $response = json_decode( $this->getResponse($this->authen),TRUE);
            return $response;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function operatorSearch($name) {

        $method = 'search';

        $this->setUrl($this->url.$method);
        $this->setPost('name='.$name);
        try {
            $response = json_decode( $this->getResponse($this->authen),TRUE);
            return $response;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function featureSearch($feature) {

        $method = 'single_feature_search';

        $this->setUrl($this->url.$method.'/'.$feature);
        $this->setGet();
        try {
            $response = json_decode( $this->getResponse($this->authen),TRUE);
            return $response;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
        
    

}

?>
