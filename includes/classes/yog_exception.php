<?php
  /**
  * @desc YogException
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogException extends Exception
  {
    const WRONG_REQUEST   = 1;
    const GLOBAL_ERROR    = 2;
    const NO_SYSYEM_LINK  = 3;
    
    /**
    * @desc Determine human readable error
    * 
    * @param void
    * @return string
    */
    public function determineError()
    {
      switch ($this->code)
      {
        case self::WRONG_REQUEST:
          return 'Signature does not match a local secret, did you use the wrong secret?';
          break;
        case self::NO_SYSYEM_LINK:
          return 'Yes-co Open is not (yet) activated on this blog.';
          break;
        case self::GLOBAL_ERROR:
          return 'Server error';
          break;
      }
    }
    
    /**
    * @desc Get json object
    * 
    * @param void
    * @return string
    */
    public function toJson()
    {
	    $response = array('status' => 'error',
	                      'errorcode' => $this->code,
                        'error'     => $this->determineError()
                        );
      
	    return json_encode($response);
    }
  }
?>
