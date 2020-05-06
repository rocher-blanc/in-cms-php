<?php

namespace App\Kernel\Back;

class Gallery extends \App\Kernel\Common\Gallery
{
    /* ************************************************** */
    /* ****************   VARIABLES   ******************* */
    /* ************************************************** */

    protected $image_size = 0;
    protected $order = [];
    protected $thumb = [];
    protected $cover = [];

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function setSize( $var )
    {
        $this->image_size = $var;
    }

    public function setOrder( $var )
    {
        $this->order = $var;
    }
    public function setThumb( $width , $height )
    {
        $this->thumb[ $width . "x" . $height ] = [
            'w' => $width,
            'h' => $height,
        ];
    }

    public function setCover( $width , $height )
    {
        $this->cover[ $width . "x" . $height ] = [
            'w' => $width,
            'h' => $height,
        ];
    }

    /* ************************************************** */
    /* ******************   GETTER   ******************** */
    /* ************************************************** */

    public function getSize()
    {
        return $this->formatBytes( $this->image_size ) ;
    }

    public function getOrder()
    {
        return $this->order ;
    }

    public function getThumb()
    {
        return $this->thumb ;
    }

    public function getCover()
    {
        return $this->cover ;
    }

    /* ************************************************** */
    /* *****************  FUNCTIONS  ******************** */
    /* ************************************************** */

    public function order()
    {
        $i = 1;
        if ( ! empty( $this->getOrder() ) )
        {
            foreach( $this->getOrder() as $row )
            {
                if ( ! empty( $row ) )
                {
                    $id = str_replace('gallery-' , '' , $row );

                    $gallery = \DB::for_table('gallery')
                        ->select('gallery_position')
                        ->select('gallery_id')
                        ->where_equal( 'gallery_id' , $id )
                        ->find_one();

                    if ( $gallery )
                    {
                        $gallery->gallery_position = $i;
                        $gallery->save();
                        $i++;
                    }
                }
            }
        }
    }

    public function createByUrl( array $images )
    {
        foreach( $images as $img )
        {
            if ( ! empty( $img ) )
            {
                $filename = end( explode( '/' , $img ) );
                $this->setImageName( $this->getNewFilename( $filename ) );

                if ( $this->copyFromUrl( $img ) )
                {
                    $this->genThumb( 100 , 100 );

                    if ( ! empty( $this->getThumb() ) )
                    {
                        foreach( $this->getThumb() as $thb )
                        {
                            $this->genThumb( $thb['w'] , $thb['h'] );
                        }
                    }

                    if ( ! empty( $this->getCover() ) )
                    {
                        foreach( $this->getCover() as $thb )
                        {
                            $this->genCover( $thb['w'] , $thb['h'] );
                        }
                    }

                    $gallery = \DB::for_table('gallery')->create();
                    $gallery->gallery_name = $this->getImageName();
                    $gallery->gallery_module_id = $this->getModuleId();
                    $gallery->gallery_element_id = $this->getElementId();
                    $gallery->gallery_field = $this->getField();
                    $gallery->gallery_position = 9999;
                    $gallery->save();
                }
            }
        }
    }

    public function add()
    {
        $path = IMAGE_PATH . '/' . $this->getFolder() ;

        $ct  = count( $_FILES[ $this->post('field') ]["name"] );
        $tab = [];

        $module = \DB::for_table('module')
            ->select('module_class_name')
            ->where(array('module_id' => $this->getModuleId()))
            ->find_one();

        for( $i = 0; $i <= $ct; $i ++ )
        {
            $this->setImageName( $this->getNewFilename( $_FILES[ $this->post('field') ]["name"][$i] ) );
            if ( $this->move( $_FILES[ $this->post('field') ]["tmp_name"][$i]) )
            {
                $this->genThumb( 100 , 100 );

                if ( ! empty( $this->getThumb() ) )
                {
                    foreach( $this->getThumb() as $thb )
                    {
                        $this->genThumb( $thb['w'] , $thb['h'] );
                    }
                }

                if ( ! empty( $this->getCover() ) )
                {
                    foreach( $this->getCover() as $cover )
                    {
                        $this->genCover( $cover['w'] , $cover['h'] );
                    }
                }

                $gallery = \DB::for_table('gallery')->create();
                $gallery->gallery_name = $this->getImageName();
                $gallery->gallery_type = $_FILES[ $this->post('field') ]['type'][$i];
                $gallery->gallery_size = $_FILES[ $this->post('field') ]['size'][$i];
                $gallery->gallery_module_id = $this->getModuleId();
                $gallery->gallery_element_id = $this->getElementId();
                $gallery->gallery_field = $this->getField();
                $gallery->gallery_position = 9999;
                $gallery->save();

                $this->setImageId( $gallery->gallery_id );
                $this->setSize( $_FILES['file']['size'] );

                $std             = new \stdClass;
                $std->id         = $gallery->gallery_id;
                $std->field      = $this->post('field');
                $std->name       = $this->getImageName();
                $std->url        = $this->Factory()->Url()->get('module/' . $module->module_class_name . '/deletegallery');
                $std->image100   = str_replace( WEB_PATH , \App\Kernel\Http::getInstance()->getUrl() , IMAGE_PATH ) . '/' . $this->getFolder() . '/' . $this->getMini( $gallery->gallery_name , 100 , 100 ) ;;

                $tab[] = $std ;
            }
        }

        return $tab ;
    }

    public function duplicate( $id )
    {
        $rst = \DB::for_table('gallery')
            ->where_equal( 'gallery_module_id' , $this->getModuleId() )
            ->where_equal( 'gallery_element_id' , $this->getElementId() )
            ->where_equal( 'gallery_field' , $this->getField() )
            ->find_many();

        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $gallery = \DB::for_table('gallery')->create();
                $gallery->gallery_name = $row->gallery_name;
                $gallery->gallery_type = $row->gallery_type;
                $gallery->gallery_size = $row->gallery_size;
                $gallery->gallery_module_id = $row->gallery_module_id;
                $gallery->gallery_element_id = $id;
                $gallery->gallery_field = $row->gallery_field;
                $gallery->gallery_position = $row->gallery_position;
                $gallery->save();
            }
        }
    }

    public function deleteElement()
    {
        $rst = \DB::for_table('gallery')
            ->select('gallery_name')
            ->select('gallery_id')
            ->where_equal( 'gallery_module_id' , $this->getModuleId() )
            ->where_equal( 'gallery_element_id' , $this->getElementId() )
            ->where_equal( 'gallery_field' , $this->getField() )
            ->find_many();

        $path = IMAGE_PATH . '/' . $this->getFolder() . '/' ;

        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $ct = \DB::for_table('gallery')
                    ->where_equal( 'gallery_name' , $row->gallery_name )
                    ->where_equal( 'gallery_module_id' , $row->gallery_module_id )
                    ->count();

                if ( $ct == 1 )
                {
                    if ( ! empty( $this->getThumb() ) )
                    {
                        foreach( $this->getThumb() as $thb )
                        {
                            if ( file_exists( $path . $this->getMini( $row->gallery_name , $thb['w'] , $thb['h'] ) ) )
                            {
                                unlink( $path . $this->getMini( $row->gallery_name , $thb['w'] , $thb['h'] ) );
                            }
                        }
                    }

                    if ( file_exists( $path . $this->getMini( $row->gallery_name , 100 , 100 ) ) ) unlink( $path . $this->getMini( $row->gallery_name , 100 , 100 ) ) ;
                    if ( file_exists( $path . $row->gallery_name ) ) unlink( $path . $row->gallery_name ) ;
                }

                $row->delete();
            }
        }
    }

    public function delete()
    {
        $rst = \DB::for_table('gallery')
            ->where_equal( 'gallery_id' , $this->getImageId() )
            ->find_one();

        $ct = \DB::for_table('gallery')
            ->where_equal( 'gallery_name' , $rst->gallery_name )
            ->where_equal( 'gallery_module_id' , $rst->gallery_module_id )
            ->count();

        if ( $ct == 1 )
        {
            $img = $this->getById();

            foreach( $img as $key => $row )
            {
                if ( file_exists( $row ) )
                {
                    unlink( $row );
                }
            }
        }

        $rst->delete();
    }

    public function getById()
    {
        $row = \DB::for_table('gallery')
            ->select('gallery_name')
            ->select('gallery_id')
            ->where_equal( 'gallery_id' , $this->getImageId() )
            ->find_one();

        $tab = [];

        $tab['source']  = IMAGE_PATH . '/' . $this->getFolder() . '/' . $row->gallery_name ;
        $tab['100x100'] = IMAGE_PATH . '/' . $this->getFolder() . '/' . $this->getMini( $row->gallery_name , 100 , 100 ) ;

        return $tab ;
    }

    protected function move( $tmp_name )
    {
        return move_uploaded_file( $tmp_name , IMAGE_PATH . '/' . $this->getFolder() . '/' . $this->getImageName() );
    }

    protected function copyFromUrl( $url )
    {
        $fgc = file_get_contents( $url ) ;

        if ( $fgc !== false )
        {
            return file_put_contents( IMAGE_PATH . '/' . $this->getFolder() . '/' . $this->getImageName() , $fgc );
        }

        return $fgc ;
    }

    protected function genDefaultCrop( $width , $height )
    {
        return $this->genThumb( $width , $height , true ) ;
    }

    protected function getNewFilename( $name )
    {
        $filename = $this->updateName( $name ) ;
        $path     = IMAGE_PATH . '/' . $this->getFolder() . '/' ;
        $img      = $path . $filename ;

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
                if ( ! file_exists( $path . $newname ) )
                {
                    $exist = false ;
                }
                else
                {
                    $i++;
                }
            }

            return $newname ;
        }
    }

    public function updateZero()
    {
        $rst = \DB::for_table('gallery')
            ->select('gallery_id')
            ->select('gallery_element_id')
            ->where_equal( 'gallery_module_id' , $this->getModuleId() )
            ->where_equal( 'gallery_element_id' , -1 )
            ->where_equal( 'gallery_field' , $this->getField() )
            ->order_by_asc('gallery_position')
            ->find_many();

        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $row->gallery_element_id = $this->getElementId() ;
                $row->save();
            }
        }
    }

    public function getAllByField()
    {
        $rst = \DB::for_table('gallery')
            ->select('gallery_name')
            ->select('gallery_size')
            ->select('gallery_id')
            ->where_equal( 'gallery_module_id' , $this->getModuleId() )
            ->where_equal( 'gallery_element_id' , $this->getElementId() )
            ->where_equal( 'gallery_field' , $this->getField() )
            ->order_by_asc('gallery_position')
            ->find_many();

        $tab = [];

        if ( $rst )
        {
            foreach( $rst as $row )
            {
                $tab[ $row->gallery_id ]['size']    = $this->formatBytes( $row->gallery_size ) ;
                $tab[ $row->gallery_id ]['name']    = $row->gallery_name ;
                $tab[ $row->gallery_id ]['source']  = $row->gallery_name ;
                $tab[ $row->gallery_id ]['100x100'] = str_replace( WEB_PATH , \App\Kernel\Http::getInstance()->getUrl() , IMAGE_PATH ) . '/' . $this->getFolder() . '/' . $this->getMini( $row->gallery_name , 100 , 100 ) ;
            }
        }

        return $tab ;
    }

    public function count()
    {
        $ct = \DB::for_table('gallery')
            ->where_equal( 'gallery_module_id' , $this->getModuleId() )
            ->where_equal( 'gallery_element_id' , $this->getElementId() )
            ->where_equal( 'gallery_field' , $this->getField() )
            ->count();

        return $ct ;
    }
}