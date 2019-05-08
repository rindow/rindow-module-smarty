<?php
namespace Rindow\Module\Smarty;

/*use Rindow\Web\Mvc\ViewManager as ViewManagerInterface;*/

class ViewManager /* implements ViewManagerInterface */
{
    protected $serviceLocator;
    protected $config;
    protected $currentTemplatePaths;

    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function setStream($stream)
    {
    }

    public function setCurrentTemplatePaths($currentTemplatePaths)
    {
        $this->currentTemplatePaths = $currentTemplatePaths;
    }

    protected function getPostfix()
    {
        if(isset($this->config['postfix']))
            return $this->config['postfix'];
        else
            return '.tpl.html';
    }

    protected function getSmarty()
    {
        return SmartyFactory::factory($this->serviceLocator);
    }

    public function render($templateName,array $variables=null,$templatePaths=null)
    {
        $smarty = $this->getSmarty();
        if($templatePaths==null)
            $templatePaths = $this->currentTemplatePaths;
        if(isset($this->config['layout'])) {
            $layout = $this->config['layout'];
        } else {
            $layout = 'layout/layout';
        }
        $variables['layout'] = $layout.$this->getPostfix();

        $smarty->template_dir = $templatePaths;
        $smarty->clearAllAssign();
        foreach($variables as $name => $value) {
            $smarty->assign($name,$value);
        }
        return $smarty->fetch($templateName.$this->getPostfix());
    }
}
