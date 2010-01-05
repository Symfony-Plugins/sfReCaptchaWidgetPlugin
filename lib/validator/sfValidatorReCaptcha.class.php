<?php

class sfValidatorReCaptcha extends sfValidatorBase
{
  const RECAPTCHA_API_VERIFY='http://api-verify.recaptcha.net/verify';

  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('privatekey');

    $this->setOption('required',false);
  }

  protected function getEmptyValue()
  {
    throw new sfValidatorError($this,'invalid',array('recaptcha'=>'incorrect-captcha-sol'));
  }

  protected function doClean($values)
  { 
    if (!(is_array($values)&&isset($values['challenge'])&&isset($values['response'])))
    {
      throw new sfValidatorError($this,'invalid',array('recaptcha'=>'verify-params-incorrect'));
    }

    $data=http_build_query(array(
      'privatekey'=>$this->getOption('privatekey'),
      'remoteip'=>$_SERVER['REMOTE_ADDR'],
      'challenge'=>$values['challenge'],
      'response'=>$values['response']
    ));
    $opts = array(
      'http'=>array(
        'method'=>"POST",
        'header'=>"Content-type: application/x-www-form-urlencoded\r\n".
                  "Content-length: " . strlen($data) . "\r\n".
                  "Connection: close\r\n",
        'content'=>$data
      )
    );

    $ctx=stream_context_create($opts);
    $ret=@file_get_contents(self::RECAPTCHA_API_VERIFY,0,$ctx);
    if ($ret===false)
    {
      $ret="false\nrecaptcha-not-reachable";
    }
    $resp=explode(chr(10),str_replace(chr(13),'',$ret));

    if ($resp[0]==='false')
    {
      throw new sfValidatorError($this,'invalid',array('recaptcha'=>$resp[1]));
    }
    else if ($resp[0]==='true')
    {
      return null;
    }
    else
    {
      throw new sfValidatorError($this,'invalid',array('recaptcha'=>'recaptcha-not-reachable'));
    }
  }
}

