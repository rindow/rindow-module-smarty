<?php
namespace Rindow\Module\Smarty;

/*use Rindow\Container\ServiceLocator;*/
use Rindow\Module\Smarty\Exception;
use Smarty;

class SmartyFactory
{
    public static function factory(/*ServiceLocator*/ $serviceManager)
    {
        $loadedPlugins = array();

        $smarty = new Smarty();
        $config = $serviceManager->get('config');
        if(!isset($config['smarty']))
            return $smarty;
        $config = $config['smarty'];
        foreach ($config as $key => $value) {
            if($key === 'postfix' || $key === 'plugins')
                continue;
            $smarty->$key = $value;
        }
        if(!isset($config['plugins']))
            return $smarty;
        foreach ($config['plugins'] as $plugin) {
            if(!$plugin)
                continue;
            if(isset($loadedPlugins[$plugin]))
                continue;
            if(!$serviceManager->has($plugin))
                throw new Exception\DomainException('plugin not found.: '.$plugin);
            $pluginInstance = $serviceManager->get($plugin);
            foreach ($pluginInstance->getFunctions() as $name => $attributes) {
                $cacheable=true;
                $cache_attrs=null;
                if(!isset($attributes['type']))
                    throw new Exception\DomainException('plugin type not specified.: '.$plugin.'::'.$name);
                if(!isset($attributes['callback']))
                    throw new Exception\DomainException('plugin function not specified.: '.$plugin.'::'.$name);
                if(array_key_exists('cacheable', $attributes))
                    $cacheable = $attributes['cacheable'];
                if(array_key_exists('cache_attrs', $attributes))
                    $cache_attrs = $attributes['cache_attrs'];
                $smarty->registerPlugin($attributes['type'],$name,$attributes['callback'],$cacheable,$cache_attrs); 
            }
            $loadedPlugins[$plugin] = true;
        }
        return $smarty;
    }
}
