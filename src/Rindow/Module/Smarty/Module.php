<?php
namespace Rindow\Module\Smarty;

use Smarty;

class Module
{
    public function getConfig()
    {
        return array(
            'container' => array(
                'aliases' => array(
                    'Rindow\Web\Mvc\DefaultViewManager' => 'Rindow\Module\Smarty\DefaultViewManager',
                ),
                'components' => array(
                    'Smarty' => array(
                        'class' => 'Smarty',
                        'factory' => 'Rindow\Module\Smarty\SmartyFactory::factory',
                    ),
                    'Rindow\Module\Smarty\DefaultViewManager' => array(
                        'class' => 'Rindow\Module\Smarty\ViewManager',
                        'properties' => array(
                            'serviceLocator' => array('ref'=>'ServiceLocator'),
                        ),
                    ),
                    'Rindow\Module\Smarty\Plugin\DefaultForm' => array(
                        'class' => 'Rindow\Module\Smarty\Plugin\Form',
                        'properties' => array(
                            'serviceLocator' => array('ref'=>'ServiceLocator'),
                            'formRendererName' => array('value'=>'Rindow\Web\Form\View\DefaultFormRenderer'),
                        ),
                    ),
                    'Rindow\Module\Smarty\Plugin\DefaultUrl' => array(
                        'class' => 'Rindow\Module\Smarty\Plugin\Url',
                        'properties' => array(
                            'serviceLocator' => array('ref'=>'ServiceLocator'),
                            'urlGeneratorName' => array('value'=>'Rindow\Web\Mvc\DefaultUrlGenerator'),
                        ),
                    ),
                ),
            ),
            'smarty' => array(
                'plugins' => array(
                    'Form' => 'Rindow\Module\Smarty\Plugin\DefaultForm',
                    'Url'  => 'Rindow\Module\Smarty\Plugin\DefaultUrl',
                ),
                'escape_html'  => true,
            ),
        );
    }
}
