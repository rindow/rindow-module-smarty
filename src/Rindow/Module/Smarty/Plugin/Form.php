<?php
namespace Rindow\Module\Smarty\Plugin;

use Rindow\Module\Smarty\PluginInterface;
use Rindow\Module\Smarty\Exception;
/*use Rindow\Container\ServiceLocator;*/
use Rindow\Web\Form\View\FormRenderer;

class Form implements PluginInterface
{
    protected $renderer;
    protected $theme;
    protected $translator;
    protected $textDomain;
    protected $serviceManagerOrRenderer;
    protected $formRendererName;

    public function __construct($serviceManagerOrRenderer=null,$theme=null,$translator=null,$textDomain=null)
    {
        $this->serviceManagerOrRenderer = $serviceManagerOrRenderer;
        $this->theme = $theme;
        $this->translator = $translator;
        $this->textDomain = $textDomain;
    }

    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    public function setServiceLocator($serviceLocator)
    {
        $this->serviceManagerOrRenderer = $serviceLocator;
    }

    public function setFormRendererName($formRendererName)
    {
        $this->formRendererName = $formRendererName;
    }

    public function getRenderer()
    {
        if($this->renderer)
            return $this->renderer;
        if($this->serviceManagerOrRenderer) {
            if($this->serviceManagerOrRenderer instanceof FormRenderer) {
                $this->renderer = $this->serviceManagerOrRenderer;
                $this->serviceManagerOrRenderer = null;
            } else {
                $this->renderer = $this->serviceManagerOrRenderer->get($this->formRendererName);
            }
        } else {
            $this->renderer = new FormRenderer($this->theme,$this->translator,$this->textDomain);
        }
        return $this->renderer;
    }

    protected function getArguments(array $params,array $requires,array $defaults,$func)
    {
        $arguments = array();
        foreach ($requires as $key => $value) {
            if(!array_key_exists($key, $params))
                throw new Exception\InvalidArgumentException('a parameter "'.$key.'" is not specified for the function "'.$func.'" in the template of Smarty.');
        }
        foreach ($params as $key => $value) {
            if(!array_key_exists($key, $requires) && !array_key_exists($key, $defaults))
                throw new Exception\InvalidArgumentException('"'.$key.'" is a unknown parameter for the function "'.$func.'" in the template of Smarty.');
            $arguments[$key] = $value;
        }
        foreach ($defaults as $key => $value) {
            if(!array_key_exists($key, $arguments))
                $arguments[$key] = $value;
        }
        return $arguments;
    }

    public function getName()
    {
        return 'form';
    }

    public function getFunctions()
    {
        return array(
            'form_open'   => array('type'=>'function','callback' => array($this,'open')),
            'form_close'  => array('type'=>'function','callback' => array($this,'close')),
            'form_widget' => array('type'=>'function','callback' => array($this,'widget')),
            'form_label'  => array('type'=>'function','callback' => array($this,'label')),
            'form_errors' => array('type'=>'function','callback' => array($this,'errors')),
            'form_raw'    => array('type'=>'function','callback' => array($this,'raw')),
            'form_theme'  => array('type'=>'function','callback' => array($this,'setTheme')),
            'form_add'    => array('type'=>'function','callback' => array($this,'addElement')),
        );
    }

    public function open(array $params, $smarty)
    {
        $requires = array('element'=>true);
        $defaults = array('attributes'=>array());
        extract($this->getArguments($params,$requires,$defaults,'form_open'));
        return $this->getRenderer()->open($element,$attributes);
    }

    public function close(array $params, $smarty)
    {
        $requires = array('element'=>true);
        $defaults = array('attributes'=>array());
        extract($this->getArguments($params,$requires,$defaults,'form_close'));
        return $this->getRenderer()->close($element,$attributes);
    }

    public function label(array $params, $smarty)
    {
        $requires = array('element'=>true);
        $defaults = array('attributes'=>array());
        extract($this->getArguments($params,$requires,$defaults,'form_label'));
        return $this->getRenderer()->label($element,$attributes);
    }

    public function widget(array $params, $smarty)
    {
        $requires = array('element'=>true);
        $defaults = array('attributes'=>array());
        extract($this->getArguments($params,$requires,$defaults,'form_widget'));
        return $this->getRenderer()->widget($element,$attributes);
    }

    public function errors(array $params, $smarty)
    {
        $requires = array('element'=>true);
        $defaults = array('attributes'=>array());
        extract($this->getArguments($params,$requires,$defaults,'form_errors'));
        return $this->getRenderer()->errors($element,$attributes);
    }

    public function raw(array $params, $smarty)
    {
        $requires = array('element'=>true);
        $defaults = array('attributes'=>array());
        extract($this->getArguments($params,$requires,$defaults,'form_raw'));
        return $this->getRenderer()->raw($element,$attributes);
    }

    public function setTheme(array $params, $smarty)
    {
        $requires = array('theme'=>true);
        $defaults = array();
        extract($this->getArguments($params,$requires,$defaults,'form_theme'));
        return $this->getRenderer()->setTheme($theme);
    }

    public function addElement(array $params, $smarty)
    {
        $requires = array('element'=>true,'type'=>true,'name'=>true);
        $defaults = array('value'=>null,'label'=>null,'attributes'=>null);
        extract($this->getArguments($params,$requires,$defaults,'form_add'));
        return $this->getRenderer()->addElement($element,$type,$name,$value,$label,$attributes);
    }
}
