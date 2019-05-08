<?php
namespace Rindow\Module\Smarty\Plugin;

use Rindow\Module\Smarty\PluginInterface;

class Url implements PluginInterface
{
    protected $serviceLocator;
    protected $url;

    public function __construct($serviceLocator=null)
    {
        if($serviceLocator)
            $this->setServiceLocator($serviceLocator);
    }

    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function setUrlGeneratorName($urlGeneratorName)
    {
        $this->urlGeneratorName = $urlGeneratorName;
    }

    public function setUrlGenerator($urlGenerator)
    {
        $this->url = $urlGenerator;
    }

    protected function getUrl()
    {
        if($this->url)
            return $this->url;
        if($this->serviceLocator==null)
            throw new Exception\DomainException("plugin manager is not specified.");
        if(!$this->serviceLocator->has($this->urlGeneratorName))
            throw new Exception\DomainException("urlGenerator is not specified.");
        return $this->url = $this->serviceLocator->get($this->urlGeneratorName);
    }

    public function getName()
    {
        return 'url';
    }

    public function getFunctions()
    {
        return array(
            'url'          => array('type'=>'function','callback' => array($this,'fromRoute')),
            'url_frompath' => array('type'=>'function','callback' => array($this,'fromPath')),
            'url_root'     => array('type'=>'function','callback' => array($this,'rootPath')),
            'url_prefix'   => array('type'=>'function','callback' => array($this,'prefix')),
        );
    }

    public function fromRoute(array $params, $smarty)
    {
    	$routeName=null;
    	$routeParams=array();
    	$options=array();
    	if(isset($params['name']))
    		$routeName=$params['name'];
    	if(isset($params['params']))
    		$routeParams=$params['params'];
    	if(isset($params['options']))
    		$options=$params['options'];
        return $this->getUrl()->fromRoute($routeName,$routeParams,$options);
    }

    public function fromPath(array $params, $smarty)
    {
    	$path=null;
    	$options=array();
    	if(isset($params['path']))
    		$path=$params['path'];
    	if(isset($params['options']))
    		$options=$params['options'];
        return $this->getUrl()->fromPath($path,$options);
    }

    public function rootPath(array $params, $smarty)
    {
        return $this->getUrl()->rootPath();
    }

    public function prefix(array $params, $smarty)
    {
        return $this->getUrl()->prefix();
    }
}