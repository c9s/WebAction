<?php
namespace ActionKit;
use UniversalCache;
use Twig_Loader_Filesystem;
use Twig_Environment;
use ReflectionClass;

/**
 * Action Generator Synopsis
 *
 *    $generator = new ActionGenerator(array(
 *          'cache' => true,                 // this enables apc cache.
 *
 *
 *          // currently we only use APC
 *          'cache_dir' => 'phifty/cache',
 *          'template_dirs' => array( 'Resource/Templates' )
 *    ));
 *    $classFile = $generator->generate( 'Plugin\Action\TargetClassName', 'CreateRecordAction.template' , array( ));
 *    require $classFile;
 *
 *
 * Depends on Twig template engine
 *
 */
class ActionGenerator
{

    public $cacheDir;

    public $templateDirs = array();

    public $templates = array();

    public function __construct( $options = array() )
    {
        if ( isset($options['cache_dir']) ) {
            $this->cacheDir = $options['cache_dir'];
        } else {
            $this->cacheDir = __DIR__ . DIRECTORY_SEPARATOR . 'Cache';
            if ( ! file_exists($this->cacheDir) ) {
                mkdir($this->cacheDir, 0755, true);
            }
        }
    }

    public function addTemplateDir($path)
    {
        $this->templateDirs[] = $path;
    }


    public function generate($targetClassName, $template, $variables = array())
    {
        $parts = explode("\\",$targetClassName);
        $variables['target'] = array();
        $variables['target']['classname'] = array_pop($parts);
        $variables['target']['namespace'] = join("\\", $parts);

        $twig = $this->getTwig();
        return $twig->render($template, $variables);
    }

    public function getTwigLoader() {

        static $loader;
        if ( $loader ) {
            return $loader;
        }
        // add ActionKit built-in template path
        $loader = new Twig_Loader_Filesystem($this->templateDirs);
        $loader->addPath( __DIR__ . DIRECTORY_SEPARATOR . 'Templates', "ActionKit" );
        return $loader;
    }


    public function getTwig()
    {
        static $twig;
        if ( $twig ) {
            return $twig;
        }

        $loader = $this->getTwigLoader();
        $env = new Twig_Environment($loader, array(
            'cache' => $this->cacheDir ? $this->cacheDir : false,
        ));
        return $env;
    }



    /**
     * Given a model class name, split out the namespace and the model name.
     *
     * @param string $modelClass full-qualified model class name
     * @param string $type action type
     */
    public function generateClassCode( $modelClass , $type )
    {
        $ps = explode('\\', ltrim($modelClass) );
        $modelName = array_pop($ps);
        $ns = join("\\", $ps);
        return $this->generateClassCodeWithNamespace($ns, $modelName, $type);
    }

    /**
     * Generate record action class dynamically.
     *
     * generate( 'PluginName' , 'News' , 'Create' );
     * will generate:
     * PluginName\Action\CreateNews
     *
     * @param string $ns
     * @param string $modelName
     * @param string $type
     *
     * @return string class code
     */
    public function generateClassCodeWithNamespace( $modelNs , $modelName , $type )
    {
        $modelNs = ltrim($modelNs,'\\');
        $actionClass  = $type . $modelName;

        // here we translate App\Model\Book to App\Action\CreateBook or something
        $actionNs = str_replace('Model','Action', $modelNs);
        $actionFullClass = ltrim($actionNs . '\\' . $actionClass, '\\');

        // the original ns is the model namespace
        $recordClass  = ltrim($modelNs . '\\' . $modelName, '\\');
        $baseAction   = $type . 'RecordAction';

        $code =<<<CODE
namespace $actionNs {
    use ActionKit\\RecordAction\\$baseAction;
    class $actionClass extends $baseAction
    {
        public \$recordClass = '$recordClass';
    }
}
CODE;
        return (object) array(
            'action_class' => $actionFullClass,
            'code' => $code,
        );
    }


    public function generateActionClassCode($namespaceName,$actionName)
    {
        $actionNamespace = $namespaceName . '\\Action';
        $actionClass = $actionNamespace . '\\' . $actionName;
        $code =<<<CODE
namespace $actionNamespace {
use ActionKit\\Action;
class $actionName extends Action
{
    public function schema()
    {
    }

    public function run()
    {
        return \$this->success('Success!!');
    }

}
}
CODE;

        return (object) array(
            'action_class' => $actionClass,
            'code' => $code,
        );
    }

}
