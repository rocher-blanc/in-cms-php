<?php

namespace App\Kernel\Factory;

use App\Kernel\Http;

class Url
{
    private $toreplace   = [
					"Ä","ä","Æ","æ","Ǽ","ǽ","Å","å","Ǻ","ǻ","À","Á","Â","Ã","à","á","â","ã","Ā","ā","Ă","ă","Ą","ą","Ǎ","ǎ","Ạ","Ạ","ạ","Ả","ả","Ấ","ấ","Ầ","ầ","Ẩ","ẩ","Ẫ","ẫ","Ậ","ậ","Ắ","ắ","Ằ","ằ","Ẳ","ẳ","Ẵ","ẵ","Ặ","ặ",
					"Ç","ç","Ć","ć","Ĉ","ĉ","Ċ","ċ","Č","č",
					"Ð","ð","Ď","ď","Đ","đ",
					"È","É","Ê","Ë","è","é","ê","ë","Ē","ē","Ĕ","ĕ","Ė","ė","Ę","ę","Ě","ě","Ẹ","ẹ","Ẻ","ẻ","Ẽ","Ế","ế","Ề","ề","Ể","ể","ễ","Ệ","ệ","Ə","ə",
					"ſ","ſ",
					"Ĝ","ĝ","Ğ","ğ","Ġ","ġ","Ģ","ģ",
					"Ĥ","ĥ","Ħ","ħ",
					"Ì","Í","Î","Ï","ì","í","î","ï","Ĩ","ĩ","Ī","ī","Ĭ","ĭ","Į","į","İ","ı","Ǐ","ǐ","Ỉ","ỉ","Ị","ị",
					"Ĳ","ĳ",
					"ﬁ","ﬂ",
					"Ĵ","ĵ",
					"Ķ","ķ","ĸ",
					"Ĺ","ĺ","Ļ","ļ","Ľ","ľ","Ŀ","ŀ","Ł","ł",
					"Ñ","ñ","Ń","ń","Ņ","Ň","ň","ŉ","Ŋ","ŋ",
					"Ö","ö","Ø","ø","Ǿ","ǿ","Ò","Ó","Ô","Õ","ò","ó","ô","õ","Ō","ō","Ŏ","ŏ","Ő","ő","Ǒ","ǒ","Ọ","ọ","Ỏ","ỏ","Ố","ố","Ồ","ồ","Ổ","ổ","Ỗ","ỗ","Ộ","ộ","Ớ","ớ","Ờ","ờ","Ở","ở","Ỡ","ỡ","Ợ","ợ","Ơ","ơ",
					"Œ","œ",
					"Ŕ","ŕ","Ŗ","ŗ","Ř","ř",
					"Ś","ś","Ŝ","Ş","ş","Š","š",
					"Ţ","ţ","Ť","ť","Ŧ","ŧ",
					"Ü","ü","Ù","Ú","Û","ù","ú","û","Ụ","ụ","Ủ","ủ","Ứ","ứ","Ừ","ừ","Ữ","ữ","Ự","ự","Ũ","ũ","Ū","ū","Ŭ","ŭ","Ů","ů","Ű","ű","Ų","ų","Ǔ","ǔ","ǖ","ǘ","Ǚ","ǚ","Ǜ","ǜ","Ư","ư",
					"Ŵ","ŵ","Ẁ","ẁ","Ẃ","ẃ","Ẅ","ẅ",
					"Ý","ý","ÿ","Ŷ","ŷ","Ÿ","Ỳ","ỳ","Ỵ","ỵ","Ỷ","ỷ","Ỹ","ỹ",
					"Þ","þ","ß",
					"Ź","ź","Ż","ż","Ž","ž",
                    "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж", "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С",
                    "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ы", "Ь", "Э", "Ю", "Я",
                    "а", "б", "в", "г", "д", "е", "ё", "ж", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с",
                    "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы", "ь", "э", "ю", "я",
		];
    private $replacement = [
					"ae","ae","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a",
					"c","c","c","c","c","c","c","c","c","c",
					"d","d","d","d","d","d",
					"e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e",
					"f","f",
					"g","g","g","g","g","g","g","g",
					"h","h","h","h",
					"i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i",
					"ij","ij",
					"fi","fl",
					"j","j",
					"k","k","k",
					"l","l","l","l","l","l","l","l","l","l",
					"n","n","n","n","n","n","n","n","n","n",
					"oe","oe","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o",
					"oe","oe",
					"r","r","r","r","r","r",
					"s","s","s","s","s","s","s",
					"t","t","t","t","t","t",
					"ue","ue","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u",
					"w","w","w","w","w","w","w","w",
					"y","y","y","y","y","y","y","y","y","y","y","y","y","y",
					"th","th","ss",
					"z","z","z","z","z","z",
                    "a", "b", "v", "g", "d", "e", "e", "zh", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s",
                    "t", "u", "f", "h", "ts", "ch", "sh", "sch", "", "y", "", "e", "yu", "ya",
                    "a", "b", "v", "g", "d", "e", "e", "zh", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s",
                    "t", "u", "f", "h", "ts", "ch", "sh", "sch", "", "y", "", "e", "yu", "ya",
        ];

    public function getApp()
    {
        return \Slim\Slim::getInstance() ;
    }

    public function Factory()
    {
        return \App\Kernel\Factory::getInstance() ;
    }

    public function get( $url , $front = false )
    {
        $req = $this->getApp()->request();
        $base = Http::getInstance()->getUrl() . $req->getRootUri() ;
        if ( $front ) $base = str_replace( $this->getApp()->config('admin.url') , '' , $base ) ;
        return $base . '/' . ltrim($url, '/');
    }

    public function image( $path , $front = false )
    {
        $path = "/" . str_replace( WEB_PATH , '' , IMAGE_PATH ) . "/" . $path ;
        return $this->get( $path , $front );
    }

    public function uniqModule( $url , $idlang , $id = NULL , $modeTEST = NULL )
    {
        $url = $this->encode( $url ) ;
        $ct = \DB::for_table('module_lang')->where_equal(['module_lang_url' => $url, 'module_lang_lang_id' => $idlang]);

        if ( $id !== NULL )
        {
            $ct = $ct->where_not_equal('module_id',$id);
        }

        $ct = $ct->count();
        if ( $ct == 0 && $this->uniqSeo( $url , $idlang ) === true && $this->uniqPage( $url , $idlang ) === true )
        {
            return $url ;
        }
        else
        {
            return false ;
        }
    }

    public function uniqSeo( $url , $idlang )
    {

    }

    public function uniqPage( $url , $idlang )
    {

    }

    public function uniq( $url , $idlang )
    {
        $url = $this->encode( $url ) ;
        if ( $url == 'page' ) $url.= '1' ;
        $source = $url ;
        $exist = true ;
        $i = 1;
        while( $exist == true )
        {
            $ct = \DB::for_table('module_lang')->where_equal(['module_lang_url' => $url, 'module_lang_lang_id' => $idlang])->count();
            if ( $ct == 0 )
            {
                $ctModule = \DB::for_table('seo')->where_equal(['seo_url' => $url, 'seo_lang_id' => $idlang])->count();
                if ( $ctModule == 0 )
                {
                    $ctPage = \DB::for_table('page_lang')->where_equal(['page_lang_url' => $url, 'page_lang_lang_id' => $idlang])->count();
                    if ( $ctPage == 0 )
                    {
                        $exist = false ;
                    }
                    else
                    {
                        $url = $source . "-" . $i ;
                        $i++;
                    }
                }
                else
                {
                    $url = $source . "-" . $i ;
                    $i++;
                }
            }
            else
            {
                $url = $source . "-" . $i ;
                $i++;
            }
        }

        return $url ;
    }

    public function cutUrl( $offset = 1 )
    {
        $params = explode( "/" , $this->getApp()->request()->getPath() ) ;
        $url    = [] ;
        if ( $params )
        {
            $i = 0;
            foreach( $params as $row )
            {
                if ( !empty( $row ) && $i > $offset ) $url[] = $row ;
                $i++;
            }
        }

        return $url ;
    }

    public function getFullUrl()
    {
        return $this->getApp()->request()->getPath() ;
    }

    public function encode( $alias, $tolower = true )
	{
		$alias = str_replace( $this->toreplace, $this->replacement, $alias);
		if ( $tolower == true ) $alias = strtolower( $alias );

		$alias = preg_replace("/[^\w-]+/", "-", $alias);
		$alias = trim( $alias, '-' );
        while( strpos( $alias , '--' ) !== false )
        {
            $alias = str_replace( '--' , '-' , $alias ) ;
        }

        $alias = preg_replace( '/[^A-Za-z0-9\-]/', '', $alias );
		return $alias;
	}

    public function page( $id , $urlFull = false )
    {
        $cLang = \DB::for_table('page_lang')
            ->select('page_default')
            ->select('page_domain_id')
            ->select('page_lang_url')
            ->join( 'page', ['page_id', '=', 'page_lang_page_id'] )
            ->where(['page_lang_page_id' => $id, 'page_lang_lang_id' => \App\Kernel\Lang::getInstance()->getActive()->id])
            ->find_one();

        $url = "" ;

        if ( $cLang->page_domain_id != 0 )
        {
            $domain = \DB::for_table('domain')
                ->select('domain_name')
                ->where_equal('domain_id' , $cLang->page_domain_id)
                ->find_one();

            if ( $domain )
            {
                $protocol = 'http' ;
                if ( $_SERVER['HTTPS'] == 'on' ) $protocol = 'https' ;

                $url = $protocol . '://' . $domain->domain_name . '/' ;
            }
        }
        else
        {
            $url = Http::getInstance()->getUrl() . "/" ;
        }

//        if ( $urlFull ){ dump($url); die; }

        if ( $cLang ) 	return ( $urlFull ? $url : '' ) . ( \App\Kernel\Lang::getInstance()->count() > 1 ? \App\Kernel\Lang::getInstance()->getActive()->url . "/" : '' ) . ( $cLang->page_default == 1 ? '' : $cLang->page_lang_url ) ;
        else			return "#" ;
    }

    public function module( $id , $domain = NULL )
    {
        $cLang = \DB::for_table('module_lang')
            ->select('module_lang_url')
            ->where(['module_lang_module_id' => $id, 'module_lang_lang_id' => \App\Kernel\Lang::getInstance()->getActive()->id])
            ->find_one();

        if ( $cLang ) 	$url = ( \App\Kernel\Lang::getInstance()->count() > 1 ? \App\Kernel\Lang::getInstance()->getActive()->url . "/" : '' ) . $cLang->module_lang_url ;
        else			$url = "#" ;

        if ( $domain !== NULL )
        {
            $resultDomain = $this->domain( $domain ) ;

            if ( $resultDomain !== false )
            {
                $url = $resultDomain . "/" . $url ;
            }
        }
        else
        {
            $url = Http::getInstance()->getUrl() . '/' . $url ;
        }

        return $url ;
    }

    public function domain( $id )
    {
        $rst = \DB::for_table('domain')
            ->select('domain_id')
            ->select('domain_name')
            ->where_equal('domain_id' , $id )
            ->find_one();

        if ( $rst )
        {
            return $rst->domain_name ;
        }
        else
        {
            return false ;
        }
    }

	public function route( $module , $type = '' , $parent = '' , $id = NULL , $token = NULL )
	{
		$route = '' ;
		if ( !empty( $type ) )
		{
			$route.= '/' . $type ;
			if ( $parent != '' )
			{
				if ( substr( $parent , 0 , 1 ) != '/' )
				{
					$route.= '/' ;
				}
				$route.= $parent ;
			}
			if ( $id !== NULL ) $route.= '/id/' . $id ;
			if ( $token !== NULL ) $route.= '/' . $token ;
		}

		return $this->get( '/module/' . $module . $route ) ;
	}

	public function depedencyRoute( $module , $action , $id , $id_module , $id_element = NULL )
	{
		return $this->get( '/module/' . $module . '/' . $action . '/id/' . $id . '/depedency/' . $id_module . ( $id_element != NULL ? '/' . $id_element : '' ) ) ;
	}
}