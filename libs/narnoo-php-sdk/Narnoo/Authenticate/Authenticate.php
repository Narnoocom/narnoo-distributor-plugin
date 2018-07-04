<?

namespace Narnoo\Authenticate;
/**
*
*/
class Authenticate extends \Narnoo\Base
{
    /**************************************************
    *
    *                --- TOKEN ---
    *
    **************************************************/
    
    /**
    *   @title: Get Products
    *   @date: 25.06.2018
    *   @param: int business ID [optional]
    *   @result: JSON
    */
    public function getToken($access, $secret)
    {   
        $authn =  array(
            "API-KEY"           => $access,
            "API-SECRET-KEY"    => $secret
        );


        try{
            $url = "/authenticate/token";
            
            $response = $this->callNarnooAuthentication("get",$url,$authn);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }

    
    /**************************************************
    *
    *                --- .TOKEN ---
    *
    **************************************************/



}

?>