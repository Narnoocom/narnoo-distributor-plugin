<?php

class Atdwbuilder extends WebClient {

    public $url = 'http://connect.narnoo.com/atdw/';
    public $authen;

    public function __construct($authenticate) {

        $this->authen = $authenticate;
    }
    
    public function builder($atdw_id,$images=NULL,$brochure=NULL,$video=NULL,$description=NULL,$social=NULL,$album=NULL,$brochure_id=NULL,$video_id=NULL,$description_id=NULL) {

        $method = 'builder';
        
        $post_builder = 'id='.$atdw_id;
        if(!empty($images)){
        $post_builder .= '&images='.$images;
        }
        if(!empty($brochure)){
        $post_builder .= '&brochure='.$brochure;
        }
        if(!empty($video)){
        $post_builder .= '&video='.$video;
        }
        if(!empty($description)){
        $post_builder .= '&description='.$description;
        }
        if(!empty($social)){
        $post_builder .= '&social='.$social;
        }
        if(!empty($album)){
        $post_builder .= '&album='.$album;
        }
        if(!empty($brochure_id)){
        $post_builder .= '&brochure_id='.$brochure_id;
        }
        if(!empty($video_id)){
        $post_builder .= '&video_id='.$video_id;
        }
        if(!empty($description_id)){
        $post_builder .= '&description_id='.$description_id;
        }

        $this->setUrl($this->url . $method);
        $this->setPost( $post_builder );
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    
}

?>
