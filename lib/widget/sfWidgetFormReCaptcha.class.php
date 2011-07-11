<?php

class sfWidgetFormReCaptcha extends sfWidgetForm
{
  const RECAPTCHA_API_SERVER = 'http://www.google.com/recaptcha/api';
  const RECAPTCHA_API_SECURE_SERVER = 'https://www.google.com/recaptcha/api';

  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('ssl', true);
    $this->addRequiredOption('publickey');
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $error = null)
  {
    $server=$this->getOption('ssl')?self::RECAPTCHA_API_SECURE_SERVER:self::RECAPTCHA_API_SERVER;

    $errorpart='';
    if (!is_null($error)&&($error instanceof sfValidatorError))
    {
      $args=$error->getArguments(true);
      if (isset($args['recaptcha']))
        $errorpart='&amp;error='.$args['recaptcha'];
    }
    return '<script type="text/javascript" src="'.$server.'/challenge?k='.$this->getOption('publickey').$errorpart.'"></script>
    <script>
    document.observe("dom:loaded", function() {
      var old_set_challenge=Recaptcha._set_challenge;
      Recaptcha._set_challenge=function(new_challenge, type) {
        old_set_challenge(new_challenge,type);
        $("recaptcha_response_field").name="'.$name.'[response]";
        $("recaptcha_challenge_field").name="'.$name.'[challenge]";
      };
      $("recaptcha_challenge_field").name="'.$name.'[challenge]";
      $("recaptcha_response_field").name="'.$name.'[response]";
    });
    </script>
    <noscript>
      <iframe src="'.$server.'/noscript?k='.$this->getOption('publickey').$errorpart.'" height="300" width="500" frameborder="0"></iframe><br/>
      <textarea name="'.$name.'[challenge]" rows="3" cols="40"></textarea>
      <input type="hidden" name="'.$name.'[response]" value="manual_challenge"/>
    </noscript>';
  }
}

