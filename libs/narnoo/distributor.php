<?php

class Distributor extends WebClient {

    public $distributor_url = 'https://connect.narnoo.com/distributor_dev/';
    public $authen;

    public function __construct($authenticate) {

        $this->authen = $authenticate;
    }
    
    public function getAccount() {

        $method = 'account';

        $this->setUrl($this->distributor_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    public function getImages($page=NULL) {

        $method = 'images';
        if(!empty($page)){
            $this->setUrl($this->distributor_url . $method.'?page='.$page);
        }else {
            $this->setUrl($this->distributor_url . $method);
        }

        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function getVideos($page=NULL) {

        $method = 'videos';

        $this->setUrl($this->distributor_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function getBrochures($page=NULL) {

        $method = 'brochures';

        $this->setUrl($this->distributor_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function getBrochureDetails($bro_id) {

        $method = 'brochure_details';
        

        $this->setUrl($this->distributor_url . $method .'/' .$bro_id);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function getAlbums($page=NULL) {

        $method = 'albums';

        $this->setUrl($this->distributor_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    public function getAlbumImages($album_id,$page=NULL) {

        $method = 'album_images';
        

        $this->setUrl($this->distributor_url . $method .'/' .urlencode($album_id));
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
     public function getLogos($dst_id) {

        $method = 'logos';
        

        $this->setUrl($this->distributor_url . $method .'/'. $dst_id );
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    /****************
    * 
    * POST REQUESTS
    * 
    ***************/

    
    public function albumCreate($album_name) {

        $method = 'album_create';
        

        $this->setUrl($this->distributor_url . $method);
        $this->setPost( "name=".trim($album_name) );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function albumDelete($dst_id,$album_id) {

        $method = 'album_destroy';
        

        $this->setUrl($this->distributor_url . $method);
        $this->setPost( "id=".$dst_id."&album_id=".$album_id );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function albumRemoveImage($image_id,$album_id) {

        $method = 'album_remove_image';
        

        $this->setUrl($this->distributor_url . $method);
        $this->setPost( "album_id=".$album_id."&image_id=".$image_id );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }

       
    }
    
    public function editBrochureCaption($dst_id,$bro_id,$caption) {

        $method = 'edit_brochure_caption';
        

        $this->setUrl($this->distributor_url . $method);
        $this->setPost( "id=".$dst_id."&brochure_id=".$bro_id."&caption=".$caption );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
       
    public function editVideoCaption($dst_id,$video_id,$caption) {

        $method = 'edit_video_caption';
        

        $this->setUrl($this->distributor_url . $method);
        $this->setPost( "id=".$dst_id."&video_id=".$video_id."&caption=".$caption );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function editImageCaption($dst_id,$image_id,$caption) {

        $method = 'edit_image_caption';
        

        $this->setUrl($this->distributor_url . $method);
        $this->setPost( "id=".$dst_id."&image_id=".$image_id."&caption=".$caption );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    
    
    public function deleteImage($dst_id,$image_id) {

        $method = 'delete_image';

        $this->setUrl($this->distributor_url.$method);
        $this->setPost( "id=".$dst_id."&image_id=".$image_id );
        try {
            $response = json_decode( $this->getResponse($this->authen),TRUE);
            return $response;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function getVideoDetails($video_id) {

        $method = 'video_details';
        

        $this->setUrl($this->distributor_url . $method .'/'. $video_id );
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    /****************
    * 
    * POST REQUESTS
    * 
    ***************/

    public function albumAddImage($image_id,$album_id) {

        $method = 'album_add_image';
        

       $this->setUrl($this->distributor_url . $method);
        $this->setPost( "id=".$dst_id."&album_id=".$album_id."&image_id=".$image_id );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }

    }
    //$type, $media_id=NULL, $location=NULL, $category=NULL, $subcategory=NULL, $keywords=NULL, $privilege=NULL
    public function searchMedia($type, $media_id=NULL, $keywords=NULL, $page=1) {

        $method = 'search_media';
        
        $post_search = 'type='.$type;
        
        if(!empty($media_id)){
        $post_search .= '&id='.$media_id;
        }
       /* if(!empty($location)){
        $post_search .= '&location='.$location;
        }
        if(!empty($category)){
        $post_search .= '&category='.$category;
        }
        if(!empty($subcategory)){
        $post_search .= '&subCategory='.$subcategory;
        }
        if(!empty($privilege)){
        $post_search .= '&privilege='.$privilege;
        }*/

        if(!empty($keywords)){
        $post_search .= '&keywords='.$keywords;
        }

        $post_search .= '&page='.$page;

        
        
        

        $this->setUrl($this->distributor_url . $method);
        $this->setPost( $post_search );
        try {
           // return $post_search;
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }

        //return json_encode( $post_search );
    }


    public function downloadImage($imageId) {

        $method = 'download_image';
        

        $this->setUrl($this->distributor_url. $method. '/' .$imageId);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    
    
}

?>
