<?php
namespace WebAction;

use Pimple\Container;
use WebAction\ActionGenerator;
use WebAction\ActionLoader;
use WebAction\ActionRunner;
use WebAction\Csrf\CsrfTokenProvider;
use WebAction\Csrf\CsrfToken;
use WebAction\Csrf\CsrfSessionStorage;
use WebAction\Csrf\CsrfStorage;
use WebAction\Csrf\CsrfTokenRegister;
use Phifty\MessagePool;
use Twig_Loader_Filesystem;
use ReflectionClass;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Provided services:
 *
 *    generator:  WebAction\ActionGenerator
 *    cache_dir string
 *
 * Usage:
 *
 *    $container = new DefaultConfigurations;
 *    $generator = $container['generator'];
 *
 */
class DefaultConfigurations extends Container
{
    public function __construct()
    {
        parent::__construct();
        $this->preset();
    }

    protected function preset()
    {
        $self = $this;

        // the default parameter
        $this['locale'] = 'en';

        // the default cache dir
        $this['cache_dir'] = __DIR__ . DIRECTORY_SEPARATOR . 'Cache';

        $this['message_directory'] = __DIR__ . DIRECTORY_SEPARATOR . 'Messages';

        $this['message_pool'] = function ($c) {
            return new MessagePool($c['locale'], $c['message_directory']);
        };

        $this['csrf'] = function ($c) {
            return new CsrfTokenProvider(new CsrfSessionStorage('__csrf_token'));
        };

        // This factory will always generate new csrf token
        $this['csrf_token_new'] = $this->factory(function ($c) {
            return $c['csrf']->loadCurrentToken($refresh = true);
        });

        // Create csrf token on demain
        $this['csrf_token'] = $this->factory(function ($c) {
            $provider = $c['csrf'];
            // try to load csrf token in the current session
            $token = $provider->loadCurrentToken();
            if ($token == null || $token->isExpired($_SERVER['REQUEST_TIME'])) {
                // generate a new token
                return $provider->loadCurrentToken(true);
            }
            return $token;
        });

        // The default twig loader
        $this['twig_loader'] = function ($c) {
            $refClass = new ReflectionClass(ActionGenerator::class);
            $templateDirectory = dirname($refClass->getFilename()) . DIRECTORY_SEPARATOR . 'Templates';

            // add WebAction built-in template path
            $loader = new Twig_Loader_Filesystem([]);
            $loader->addPath($templateDirectory, 'WebAction');
            return $loader;
        };

        $this['log_level'] = Logger::INFO;

        // $this['log_dir'] = __DIR__ . DIRECTORY_SEPARATOR . 'Logs';

        $this['logger'] = function($c) {
            $logger = new Logger('webaction');

            if (isset($this['log_dir'])) {
                $logger->pushHandler(new StreamHandler($this['log_dir'] . DIRECTORY_SEPARATOR . 'access_log', $c['log_level']));
            }
            return $logger;
        };


        $this['generator'] = function ($c) {
            return new ActionGenerator;
        };

        $this['loader'] = function($c) {
            return new ActionLoader($c['generator'], $c['cache_dir']);
        };


        $this['runner'] = function($c) {
            return new ActionRunner($c['loader'], $c);
        };
    }
}
