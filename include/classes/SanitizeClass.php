<?php
class Sanitize{
    
    public static function sanitizeFormatEmail($inputEmail){
        /**
         * Validate Email 
         * 
         * @param email-address
         * @return email if valid
         * 
         */
        
        $inputEmail = strip_tags($inputEmail);
    
        $inputEmail = str_replace(" ","",$inputEmail);
        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/'; 
        return filter_var($inputEmail, FILTER_VALIDATE_EMAIL) && preg_match($regex, $inputEmail) ? $inputEmail : false;
    }

    public static function sanitizeFormatText($inputText){
        /**
         * Santize One Word (Name, Country etc..)
         * @param string 
         * 
         * @return string if valid && one word
         */

        $inputText = trim($inputText);
        
        $inputText = strip_tags($inputText);
        
        $reg = "/[a-zA-z]+/";
        
        preg_match($reg, $inputText,$matches);
        
        return $matches && $matches[0] === $inputText && count(explode(" ",$inputText)) == 1 ? $inputText : false;

    }

    public static function sanitizeInteger($inputNumber){
        /**
         * Sanitize Number
         * @param 
         * 
         * @return Valid Number 
         */

        return filter_var($inputNumber, FILTER_VALIDATE_INT) ? $inputNumber : 0;
    }

    public static function sanitizeFormatPassword($inputPassword){
        /**
         * Check Password Valid
         * @param password
         * 
         * @return password if valid from charchters
         */
        
        $inputPassword = trim($inputPassword);
       
        $inputPassword = strip_tags($inputPassword);

        $inputPassword = htmlentities($inputPassword, ENT_QUOTES, 'UTF-8');

        $reg = '/[\w@#\$*\!.%)(-\/_\s]+/';

        preg_match($reg, $inputPassword, $matches);

        return $matches && $matches[0] == $inputPassword && strlen($inputPassword) >= 8? $inputPassword : false;
    }

    public static function jsonValidator($inputJson){
        /**
         * Check Json Is Valid 
         * @param Json 
         * @return Json if exists 
         */

         $handle = json_decode($inputJson);
        
         return json_last_error() == JSON_ERROR_NONE ? json_decode($inputJson,true) : [];
    }


    public static function sanitizeFormatTitle($inputTitle){

        /**
         * Filter Tittle
         * @param Title
         * @return Sanitize Title
         * 
         */

        $inputText = trim($inputText);
        
        $inputText = strip_tags($inputText);
        
         
    }

    public static function sanitizeFormatDescription($inputDesc){
        /**
         * Filter description
         * @param Arr_Of_Description
         * @return Description if valid 
         */

         
        $inputText = trim($inputDesc);
        
        $inputText = strip_tags($inputDesc);

        return $inputText;
        

    }


    public static function imageValidator($inputImg){

        /**
         * Validate Img
         * @param Img
         * @return Is File Image
         * 
        */



        $validate = true;

        $allowedTypes = array('image/jpeg', 'image/jpg', 'image/png');

        if(is_array($inputImg['name'])){
            
            $x = 0;
            
            $extension = explode('.', $inputImg['name'][0])[1];
            
            while($x < count($inputImg['name'])){

                $validate = in_array($inputImg['type'][$x], $allowedTypes) && $inputImg['size'][$x] < 2097152 ? true : false ;        
                
                if(!$validate)
                    break;

                $x++;

                
             }
        }else{

            
            $validate = in_array($inputImg['type'], $allowedTypes) && $inputImg['size'] < 2097152 ? true : false;
            
            
        }


        return $validate;

    }
}
?>