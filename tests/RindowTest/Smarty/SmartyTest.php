<?php
namespace RindowTest\Smarty\SmartyTest;

use PHPUnit\Framework\TestCase;
use Rindow\Container\ModuleManager;
//use Rindow\Web\Mvc\Context;
//use Rindow\Web\Mvc\PluginManager;
//use Rindow\Web\Mvc\Router;
//use Rindow\Web\Http\Request;
//use Rindow\Web\Http\Response;
use Rindow\Web\Form\Element\ElementCollection;
use Rindow\Web\Form\Element\Element;

// Test Target Classes
use Smarty;
//use Rindow\Module\Smarty\SmartyView; // on ModuleManager


class TestTranslator
{
    public function __construct($serviceManager=null)
    {
        $this->serviceManager = $serviceManager;
    }
    public function translate($message, $domain=null, $locale=null)
    {
        if($domain)
            $domain = ':'.$domain;
        else
            $domain = '';
        return '(translate:'.$message.$domain.')';
    }
}

class Test extends TestCase
{
    protected static $RINDOW_TEST_RESOURCES;
    public static function setUpBeforeClass()
    {
        self::$RINDOW_TEST_RESOURCES = __DIR__.'/resources';
    }

    public static function tearDownAfterClass()
    {
    }

    public function setUp()
    {
        $cache = new \Rindow\Stdlib\Cache\SimpleCache\FileCache(array('path'=>RINDOW_TEST_CACHE));
        $cache->clear();
    }

    public function testDummy()
    {
        $this->assertTrue(true);
    }

    public function testDefault()
    {
        $smarty = new Smarty();
        $smarty->template_dir = self::$RINDOW_TEST_RESOURCES.'/smarty/templates/';
        $smarty->config_dir   = self::$RINDOW_TEST_RESOURCES.'/smarty/configs/';
        $smarty->compile_dir  = RINDOW_TEST_CACHE.'/smarty/templates_c/';
        $smarty->cache_dir    = RINDOW_TEST_CACHE.'/smarty/cache/';

        $smarty->assign('name','Taro');
        //$smarty->debugging = true;

        $out = $smarty->fetch('index/index.tpl.html');
        $this->assertEquals("Hello Taro", $out);
    }

    public function testOnModule()
    {
        $config = array(
            'smarty' => array(
                'template_dir'=> self::$RINDOW_TEST_RESOURCES.'/smarty/templates/',
                'config_dir'  => self::$RINDOW_TEST_RESOURCES.'/smarty/configs/',
                'cache_dir'   => RINDOW_TEST_CACHE.'/smarty/',
                'compile_dir' => RINDOW_TEST_CACHE.'/smarty/templates_c/',
                'caching'     => 1,
            ),
            'module_manager' => array(
                'modules' => array(
                    'Rindow\Module\Smarty\Module' => true,
                ),
                'enableCache'=>false,
            ),

        );
        $moduleManager = new ModuleManager($config);
        $smarty = $moduleManager->getServiceLocator()->get('Smarty');

        $smarty->assign('name','Taro');

        $out = $smarty->fetch('index/index.tpl.html');
        $this->assertEquals("Hello Taro", $out);
    }

    public function testModuleView()
    {
        $config = array(
            'smarty' => array(
                'config_dir'  => self::$RINDOW_TEST_RESOURCES.'/smarty/configs/',
                'cache_dir'   => RINDOW_TEST_CACHE.'/smarty/',
                'compile_dir' => RINDOW_TEST_CACHE.'/smarty/templates_c/',
                'caching'     => 1,
            ),
            'module_manager' => array(
                'modules' => array(
                    'Rindow\Module\Smarty\Module' => true,
                ),
                'enableCache'=>false,
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $viewManager = $sm->get('Rindow\Module\Smarty\DefaultViewManager');

        $out = $viewManager->render('index/index',array('name'=>'Taro'),self::$RINDOW_TEST_RESOURCES.'/smarty/templates');
        $this->assertEquals("Hello Taro", $out);
    }

    public function testUrl()
    {
        $namespace = 'ABC';
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Rindow\Web\Mvc\Module' => true,
                    'Rindow\Web\Http\Module' => true,
                    'Rindow\Web\Router\Module' => true,
                    'Rindow\Module\Smarty\Module' => true,
                ),
                'enableCache'=>false,
            ),
            'container' => array(
                'aliases' => array(
                    'Rindow\\Web\\Mvc\\DefaultServerRequest' => 'Rindow\\Web\\Http\\Message\\TestModeServerRequest',
                ),
            ),
            'web' => array(
                'router' => array(
                    'routes' => array(
                        $namespace.'\home' => array(
                            'path' => '/test',
                            'defaults' => array(
                                'controller' => 'Index',
                                'action' => 'index',
                            ),
                            'type' => 'segment',
                            'parameters' => array('action', 'id'),
                            'namespace' => $namespace,
                        ),
                        $namespace.'\boo' => array(
                            'path' => '/boo',
                            'type' => 'literal',
                            'namespace' => $namespace,
                        ),
                    ),
                ),
            ),
            'smarty' => array(
                'template_dir'=> self::$RINDOW_TEST_RESOURCES.'/smarty/templates/',
                'config_dir'  => self::$RINDOW_TEST_RESOURCES.'/smarty/configs/',
                'cache_dir'   => RINDOW_TEST_CACHE.'/smarty/',
                'compile_dir' => RINDOW_TEST_CACHE.'/smarty/templates_c/',
                'caching'     => 1,
            ),
        );
        $mm = new ModuleManager($config);
        $sm = $mm->getServiceLocator();
        $env = $sm->get('Rindow\Web\Http\Message\TestModeEnvironment');
        $env->_SERVER['SCRIPT_NAME'] = '/app/web.php';
        $env->_SERVER['REQUEST_URI'] = '/app/web.php/boo';
        $urlGenerator = $sm->get('Rindow\Web\Mvc\DefaultUrlGenerator');
        $request = $sm->get('Rindow\Web\Mvc\DefaultServerRequest');
        $router = $sm->get('Rindow\Web\Mvc\DefaultRouter');
        $urlGenerator->setRequest($request);
        $urlGenerator->setRouteInfo($router->match($request,$urlGenerator->getPath()));

        $smartyView = $sm->get('Rindow\Module\Smarty\DefaultViewManager');

        $result = $smartyView->render('index/url',array(),array(self::$RINDOW_TEST_RESOURCES.'/smarty/templates/'));
        $answer = <<<EOD
/app/web.php/test
/app/web.php/test/act/id1
/app/web.php/test/act/id1?a=b
/app/web.php/abc?a=b
/app/web.php
/app
EOD;
        $answer = str_replace(array("\r","\n"), array("",""), $answer);
        $result = str_replace(array("\r","\n"), array("",""), $result);
        $this->assertEquals($answer,$result);
    }

    public function testForm()
    {
        $form = new ElementCollection();
        $form->type = 'form';

        $element = new Element();
        $element->type = 'text';
        $element->name = 'boo';
        $element->value = 'value';
        $element->label = 'LABEL';
        $form[$element->name] = $element;

        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Rindow\Web\Form\Module' => true,
                    'Rindow\Module\Smarty\Module' => true,
                    //'Rindow\Module\Monolog\Module' => true,
                ),
                'enableCache'=>false,
            ),
            'container' => array(
                //'debug' => true,
                'components' => array(
                    'Rindow\Web\Form\View\DefaultFormRenderer' => array(
                        'constructor_args' => array(
                            'translator' => array('ref' => __NAMESPACE__.'\TestTranslator'),
                        ),
                    ),
                    __NAMESPACE__.'\TestTranslator'=>array(),
                ),
            ),
            'smarty' => array(
                'template_dir'=> self::$RINDOW_TEST_RESOURCES.'/smarty/templates/',
                'config_dir'  => self::$RINDOW_TEST_RESOURCES.'/smarty/configs/',
                'cache_dir'   => RINDOW_TEST_CACHE.'/smarty/',
                'compile_dir' => RINDOW_TEST_CACHE.'/smarty/templates_c/',
                'caching'     => 1,
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $viewManager = $sm->get('Rindow\Module\Smarty\DefaultViewManager');

        $variables = array(
            'form' => $form,
        );
        $templateName = 'index/form';
        $templatePaths = array(self::$RINDOW_TEST_RESOURCES.'/smarty/templates');
        $result = <<<EOT
<label>(translate:LABEL)</label>
<input type="text" value="value" name="boo">
EOT;
        $result .= "\n";
        $result = str_replace("\r", "", $result);
        $content = $viewManager->render($templateName,$variables,$templatePaths);
        $this->assertEquals($result,$content);
    }
}
