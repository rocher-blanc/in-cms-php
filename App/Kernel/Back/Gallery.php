<?php

namespace App\Kernel\Back;

class Gallery extends \App\Kernel\Common\Gallery
{
    public function add()
    {
        $this->setImageName( $this->getNewFilename() );
        if ( $this->move() )
        {
            $this->genThumb( 100 , 100 );

            $gallery = \DB::for_table('gallery')->create();
            $gallery->gallery_name = $this->getImageName();
            $gallery->gallery_type = $_FILES['file']['type'];
            $gallery->gallery_size = $_FILES['file']['size'];
            $gallery->gallery_module_id = $this->getModuleId();
            $gallery->gallery_element_id = $this->getElementId();
            $gallery->gallery_field = $this->getField();
            $gallery->save();

            return true;
        }
        else
        {
            return false;
        }

    }

    protected function move()
    {
        return move_uploaded_file( $_FILES['file']['tmp_name'] , IMAGE_PATH . '/' . $this->getFolder() . '/' . $this->getImageName() );
    }

    public function genThumb( $width , $height )
    {
        $path = IMAGE_PATH . '/' . $this->getFolder() . '/' ;
        $img  = $path . $this->getImageName() ;

        try {
            $miniName = $this->updateName( $this->getImageName() , $width . "x" . $height ) ;
            $file = $path . "t/" . $miniName ;

            if ( ! file_exists( $file ) )
            {
                $tmpImg = new \abeautifulsite\SimpleImage( $img );
                $tmpImg->best_fit( $width , $height );

                $destImg = new \abeautifulsite\SimpleImage(null, $width, $height, "#FFF");
                $destImg->overlay($tmpImg)->save($file);
            }

            return $miniName ;
        } catch(Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    private function updateName( $name , $addStr = "" )
    {
        $exp 	= explode( "." , $name ) ;
        $ext 	= end( $exp ) ;
        $extlen = ( strlen( $ext ) + 1 ) * -1 ;
        if ( $addStr != "" ) $addStr = "-" . $addStr ;

        $name = substr( $name , 0 , $extlen ) ;
        $name = $this->Factory()->Url()->encode( $name . $addStr ) . "." . $ext ;

        return $name ;
    }

    protected function getNewFilename()
    {
        $filename = $this->updateName( $_FILES['file']['name'] ) ;
        $path     = IMAGE_PATH . '/' . $this->getFolder() . '/' ;
        $img      = $path . '/' . $filename ;

        if ( ! file_exists( $img ) )
        {
            return $filename ;
        }
        else
        {
            $exist = true ;
            $i = 1;
            while( $exist == true )
            {
                $newname = $i . "-" . $filename ;
                if ( file_exists( $path . "/" . $newname ) )
                {
                    $exist = false ;
                    return $newname ;
                }
                else
                {
                    $i++;
                }
            }
        }
    }
}