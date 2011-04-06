<?php

/**
 * Observer
 *
 * Observe / Notify events
 */
final class Observer
{
    private static $events = array(); // events callback

    /**
     * Observe event
     *
     * <code>
     * Observer::observe('system.shutdown', array('Profiler', 'display'));
     * </code>
     *
     * @param string
     * @param mixed
     */
    public static function observe($name, $callback)
    {
        if (!isset(self::$events[$name]))
        {
            self::$events[$name] = array();
        }
        self::$events[$name][] = $callback;
    }

    /**
     * Detach a callback to an event queue.
     */
    public static function clear($name, $callback=false)
    {
        if ( ! $callback)
        {
            self::$events[$name] = array();
        }
        else if (isset(self::$events[$name]))
        {
            foreach (self::$events[$name] as $i => $event_callback)
            {
                if ($callback === $event_callback)
                {
                    unset(self::$events[$name][$i]);
                }
            }
        }
    }

    public static function get($name)
    {
        return empty(self::$events[$name]) ? array(): self::$events[$name];
    }

    /**
     * Notify event
     *
     * <code>
     * Observer::notify('system.execute');
     * </code>
     *
     * @param string
     */
    public static function notify($name)
    {
        // removing event name from the arguments
        $args = func_num_args() > 1 ? array_slice(func_get_args(), 1): array();

        foreach (self::get($name) as $callback)
        {
            call_user_func_array($callback, $args);
        }
    }
}
/**
 * Action controller
 *
 * @package MVC
 */
abstract class Controller
{
    protected $application;
    protected $layout = false;
    protected $layout_vars = array();

    /**
     * New Controller
     *
     * @param string $application application name
     */
    public function __construct()
    {
        $parts = explode('\\', get_called_class());
        $this->application = $parts[0];
    }

    /**
     * Execute action
     *
     * @param string $action name
     * @param array $params
     * @param boolean $layoutEnabled
     */
    public function execute($action, $params, $layoutEnabled = true)
    {
        $method = $action . 'Action';
        // it's a private method of the class or action is not a method of the class
        if (substr($method, 0, 1) == '_' || !method_exists($this, $method))
        {
            throw new Exception("Action '{$action}' is not valid!");
        }
        call_user_func_array(array($this, $method), $params);
    }

    /**
     * Set layout file
     *
     * @param string $layout
     */
    public function setLayout($layout)
    {
        if(substr($layout, -4) != '.php')
        {
            $layout = Shozu::getInstance()->project_root . 'applications'
                                          . DIRECTORY_SEPARATOR . $this->application
                                          . DIRECTORY_SEPARATOR . 'views'
                                          . DIRECTORY_SEPARATOR . $layout . '.php';
        }
        $this->layout = $layout;
    }

    /**
     * Disable layout
     */
    public function disableLayout()
    {
        $this->layout = false;
    }

    /**
     * Assign variable to layout
     *
     * @param string $var var name
     * @param mixed $value
     */
    public function assignToLayout($var, $value)
    {
        if (is_array($var))
        {
            array_merge($this->layout_vars, $var);
        }
        else
        {
            $this->layout_vars[$var] = $value;
        }
    }

    /**
     * Render a View
     *
     * <code>
     * // render view scripts in current application views path
     * $rendered = $controller->render('index');
     * $rendered = $controller->render('user/form');
     * // render view script with absolute path
     * $rendered = $controller->render('/var/www/templates/index.php');
     * </code>
     *
     * @param string view name or path to view script
     * @param array $vars assigned variables
     * @return View
     */
    public function render($view, $vars = array())
    {
        if(substr($view, -4) != '.php')
        {
            $view = Shozu::getInstance()->project_root . 'applications' . DIRECTORY_SEPARATOR
                                         . $this->application . DIRECTORY_SEPARATOR
                                         . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
        }
        if($this->layout)
        {
            $this->layout_vars['content_for_layout'] = new View($view, $vars);
            return new View($this->layout, $this->layout_vars);
        }
        else
        {
            return new View($view, $vars);
        }
    }

    /**
     * display (echoes) rendered view
     *
     * @param string $view view name
     * @param array $vars assigned vars
     * @param boolean $exit die after echo
     */
    public function display($view, $vars = array(), $exit = false)
    {
        echo $this->render($view, $vars);
        if($exit)
        {
            exit;
        }
    }

    /**
     * Get request param value from _GET or _POST.
     *
     * Will return default value if param doesn't exist.
     *
     * @param string $name param name
     * @param mixed $default default value
     */
    public function getParam($name, $default = null)
    {
        if(isset($_GET[$name]))
        {
            return $_GET[$name];
        }
        if(isset($_POST[$name]))
        {
            return $_POST[$name];
        }
        return $default;
    }

    /**
     * Get request method ("post" or "get")
     *
     * @return string
     */
    public function getRequestMethod()
    {
        if(count($_POST) > 0)
        {
            return 'post';
        }
        return 'get';
    }
}
/**
 * View
 *
 * @package MVC
 */
class View
{
    /**
     *  String of template file
     */
    private $file;
    /**
     * Array of template variables
     */
    private $vars = array();

    /**
     * Assign the template path
     *
     * @param string $file Template path (absolute path or path relative to the templates dir)
     * @param array $vars assigned variables
     */
    public function __construct($file, $vars = false)
    {
        $this->file = $file;
        if (!file_exists($this->file))
        {
            throw new Exception("View '{$this->file}' not found!");
        }
        if ($vars !== false)
        {
            $this->vars = $vars;
        }
    }

    /**
     * Assign specific variable to the template
     *
     * <code>
     * // assign single var
     * $view->assign('varname', 'varvalue');
     * // assign array of vars
     * $view->assign(array('varname1' => 'varvalue1', 'varname2' => 'varvalue2'));
     * </code>
     *
     * @param mixed $name Variable name
     * @param mixed $value Variable value
     */
    public function assign($name, $value = null)
    {
        if (is_array($name))
        {
            array_merge($this->vars, $name);
        }
        else
        {
            $this->vars[$name] = $value;
        }
    }


    /**
     * Display template and return output as string
     *
     * @return string content of compiled view template
     */
    public function render()
    {
        ob_start();
        extract($this->vars, EXTR_SKIP);
        include $this->file;
        $content = ob_get_clean();
        return $content;
    }

    /**
     * Display (echoes) the rendered template
     */
    public function display()
    {
        echo $this->render();
    }

    /**
     * Render the content and return it
     *
     * <code>
     * echo new View('blog', array('title' => 'My title'));
     * </code>
     *
     * @return string content of the view
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Render action in view
     *
     * @param string $application application name
     * @param string $controller controller name
     * @param string $action action name
     * @param array $params request parameters
     * @return string
     */
    public function action($application, $controller, $action, $params = array())
    {
        return Dispatcher::render($application, $controller, $action, $params);
    }

    /**
     * Escape HTML special chars
     *
     * @param string
     * @return string
     */
    public function escape($string)
    {
        return htmlspecialchars($string);
    }

    /**
     * Translate helper
     *
     * @param string
     * @return string
     */
    public function T($string)
    {
        if(!Shozu::getInstance()->use_i18n)
        {
            return $string;
        }
        $translations = Shozu::getInstance()->translations;
        if(isset($translations[$string]))
        {
            return $translations[$string];
        }
        else
        {
            if(Shozu::getInstance()->debug)
            {
                return '***' . $string;
            }
            return $string;
        }
    }
}
/**
 * Put benchmark points.
 *
 * Forked from Green framework by Philippe Archambault
 */
final class Benchmark
{
    public static $marks = array();

	/*
	   Method: Start
	   Set a benchmark start point.
	*/
    public static function start($name)
    {
        if(!Shozu::getInstance()->benchmark)
        {
            return false;
        }
        if (!isset(self::$marks[$name]))
        {
            self::$marks[$name] = array
                (
                'start'        => microtime(true),
                'stop'         => false,
                'memory_start' => function_exists('memory_get_usage') ? memory_get_usage() : 0,
                'memory_stop'  => false
            );
        }
        return true;
    }

	/*
	   Method: stop
	   Set a benchmark stop point.
	*/
    public static function stop($name)
    {
        if(!Shozu::getInstance()->benchmark)
        {
            return false;
        }
        if (isset(self::$marks[$name]))
        {
            self::$marks[$name]['stop'] = microtime(true);
            self::$marks[$name]['memory_stop'] = function_exists('memory_get_usage') ? memory_get_usage() : 0;
        }
        return true;
    }

	/*
	   Method: get
	   Get the elapsed time between a start and stop of a mark name, TRUE for all.
	*/
    public static function get($name, $decimals = 4)
    {
        if(!Shozu::getInstance()->benchmark)
        {
            return false;
        }
        if ($name === true)
        {
            $times = array();

            foreach(array_keys(self::$marks) as $name)
            {
                $times[$name] = self::get($name, $decimals);
            }

            return $times;
        }

        if (!isset(self::$marks[$name]))
        {
            return false;
        }

        if (self::$marks[$name]['stop'] === false)
        {
            self::stop($name);
        }

        return array
        (
        'time'   => number_format(self::$marks[$name]['stop'] - self::$marks[$name]['start'], $decimals),
        'memory' => self::convert_size(self::$marks[$name]['memory_stop'] - self::$marks[$name]['memory_start'])
        );
    }

    public static function convert_size($num)
    {
        if ($num >= 1073741824)
        {
            $num = round($num / 1073741824 * 100) / 100 .' gb';
        }
        else if ($num >= 1048576)
            {
                $num = round($num / 1048576 * 100) / 100 .' mb';
            }
            else if ($num >= 1024)
                {
                    $num = round($num / 1024 * 100) / 100 .' kb';
                }
                else
                {
                    $num .= ' b';
                }
        return $num;
    }

    public static function htmlReport()
    {
        if(!Shozu::getInstance()->benchmark)
        {
            return '';
        }
        $html = '<div  style="font-size:14px;font-family:monospace;"><ol>';
        foreach(self::get(true) as $key => $val)
        {
            $html .= '<li><strong>' . htmlspecialchars($key) . '</strong><br/>time&nbsp;&nbsp;: ' . $val['time'] . '<br/>memory: ' . $val['memory'] . '</li>';
        }
        return $html . '</ol></div>';
    }

    public static function cliReport()
    {
        $output = '';
        if(!Shozu::getInstance()->benchmark)
        {
            return $output;
        }
        $points = self::get(true);
        if(!empty($points))
        {
            $output .= "\n#### Benchmark ####\n";
            foreach($points as $key => $val)
            {
                $output .= "\n[ " . $key . " ]\n        time: " . $val['time'] . "\n        memory: " . $val['memory'] . "\n";
            }
        }
        return $output;
    }

}
/**
 * Dispatcher
 *
 * @package MVC
 */
final class Dispatcher
{
    private static $routes = array();
    private static $params = array();
    private static $status = array();
    private static $requested_url = '';

    /**
     * Add route
     *
     * '/' => 'page/index',
     * '/about' => 'page/about,
     * '/blog/:num' => 'blog/post/$1',
     * '/blog/:num/comment/:num/delete' => 'blog/deleteComment/$1/$2'
     *
     */
    public static function addRoute($route, $destination = null)
    {
        if ($destination != null && !is_array($route))
        {
            $route = array($route => $destination);
        }
        self::$routes = array_merge(self::$routes, $route);
    }

    public static function splitUrl($url)
    {
        return preg_split('/\//', $url, -1, PREG_SPLIT_NO_EMPTY);
    }

    public static function dispatch($requested_url = null, $default = null)
    {
    //Flash::init();
    // If no url passed, we will get the first key from the _GET array
    // that way, index.php?/application/controller/action/var1&email=example@example.com
    // requested_url will be equal to: /application/controller/action/var1
        if ($requested_url === null)
        {
            $pos = strpos($_SERVER['QUERY_STRING'], '&');
            if ($pos !== false)
            {
                $requested_url = substr($_SERVER['QUERY_STRING'], 0, $pos);
            }
            else
            {
                $requested_url = $_SERVER['QUERY_STRING'];
            }
        }
        // If no URL is requested (due to someone accessing admin section for the first time)
        // AND $default is setAllow for a default tab
        if ($requested_url == null && $default != null)
        {
            $requested_url = $default;
        }
        // Requested url MUST start with a slash (for route convention)
        if (strpos($requested_url, '/') !== 0)
        {
            $requested_url = '/' . $requested_url;
        }
        self::$requested_url = $requested_url;
        // This is only trace for debugging
        self::$status['requested_url'] = $requested_url;
        // Make the first split of the current requested_url
        self::$params = self::splitUrl($requested_url);
        // Do we even have any custom routing to deal with?
        if (count(self::$routes) === 0)
        {
            return self::executeAction(self::getApplication(),
                                       self::getController(),
                                       self::getAction(),
                                       self::getParams());
        }
        // Is there a literal match? If so we're done
        if (isset(self::$routes[$requested_url]))
        {
            self::$params = self::splitUrl(self::$routes[$requested_url]);
            return self::executeAction(self::getApplication(),
                                       self::getController(),
                                       self::getAction(),
                                       self::getParams());
        }
        // Loop through the route array looking for wildcards
        foreach (self::$routes as $route => $uri)
        {
        // Convert wildcards to regex
            if (strpos($route, ':') !== false)
            {
                $route = str_replace(':any', '(.+)', str_replace(':num', '([0-9]+)', $route));
            }
            // Does the regex match?
            if (preg_match('#^' . $route . '$#', $requested_url))
            {
            // Do we have a back-reference?
                if (strpos($uri, '$') !== false && strpos($route, '(') !== false)
                {
                    $uri = preg_replace('#^' . $route . '$#', $uri, $requested_url);
                }
                self::$params = self::splitUrl($uri);
                // We found it, so we can break the loop now!
                break;
            }
        }
        return self::executeAction(self::getApplication(),
                                   self::getController(),
                                   self::getAction(),
                                   self::getParams());
    }

    public static function getCurrentUrl()
    {
        return self::$requested_url;
    }

    public static function getApplication()
    {
        return isset(self::$params[0]) ? self::$params[0] : Shozu::getInstance()->default_application;
    }

    public static function getController()
    {
        return isset(self::$params[1]) ? self::$params[1] : Shozu::getInstance()->default_controller;
    }

    public static function getAction()
    {
        return isset(self::$params[2]) ? self::$params[2] : Shozu::getInstance()->default_action;
    }

    public static function getParams()
    {
        return array_slice(self::$params, 3);
    }

    public static function getStatus($key = null)
    {
        return($key === null) ? self::$status : (isset(self::$status[$key]) ? self::$status[$key] : null);
    }

    public static function executeAction($application,
                                         $controller,
                                         $action,
                                         $params,
                                         $layoutEnabled = false)
    {
        self::$status['application'] = $application;
        self::$status['controller'] = $controller;
        self::$status['action'] = $action;
        self::$status['params'] = implode(', ', $params);
        $controller_class = self::$status['application'] . '\\controllers\\' . Inflector::camelize($controller);
        $old = ini_set('error_reporting', 0);
        $class_exists = class_exists($controller_class, true);
        ini_set('error_reporting', $old);
        if ($class_exists)
        {
            $old = ini_set('error_reporting', 0);
            include_once(\Shozu::getInstance()->project_root . 'applications' . DIRECTORY_SEPARATOR
                                             . $application . DIRECTORY_SEPARATOR . 'AppInit.php');
            ini_set('error_reporting', $old);
            $controller = new $controller_class;
            if (!$controller instanceof \Controller)
            {
                throw new Exception("Class '{$controller_class}' does not extends Controller class!");
            }
            // Execute the action
            $controller->execute($action, $params, $layoutEnabled);
        }
        else
        {
            throw new Exception('not found', 404);
        }
    }

    public static function render($application, $controller, $action, $params = array(), $layoutEnabled = false)
    {
        ob_start();
        self::executeAction($application, $controller, $action, $params, $layoutEnabled);
        $content = ob_get_clean();
        return $content;
    }
}
/**
 * string conversions
 *
 * @package MVC
 */
final class Inflector
{
/**
 *  Return an CamelizeSyntaxed (LikeThisDearReader) from something like_this_dear_reader.
 *
 * @param string $string Word to camelize
 * @return string Camelized word. LikeThis.
 */
    public static function camelize($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * Return an underscore_syntaxed (like_this_dear_reader) from something LikeThisDearReader.
     *
     * @param  string $string CamelCased word to be "underscorized"
     * @return string Underscored version of the $string
     */
    public static function underscore($string)
    {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string));
    }

    /**
     * Return a Humanized syntaxed (Like this dear reader) from something like_this_dear_reader.
     *
     * @param  string $string CamelCased word to be "underscorized"
     * @return string Underscored version of the $string
     */
    public static function humanize($string)
    {
        return ucfirst(str_replace('_', ' ', $string));
    }

    /**
     * Namespace model to db
     *
     * Convert namespaced names to underscored names
     *
     * @param string
     * @return string
     */
    public static function model2dbName($class)
    {
        return strtolower(str_replace('\\', '_', $class));
    }
}
/**
 * Wraps PHP session with an OO API
 *
 * <code>
 * $session = Session::getInstance();
 * $session->myVar = 'my value';
 * echo $session->myVar;
 * </code>
 */
final class Session
{
    private static $instance;
    private function __construct(){}

    /**
     * Get Session instance
     *
     * @param string $name session name (defaults to PHPSESSID)
     * @return Session
     */
    public static function getInstance($name = 'PHPSESSID')
    {
        if(empty(self::$instance))
        {
            self::startSession($name);
            self::$instance = new Session;
        }
        return self::$instance;
    }

    public function __get($key)
    {
        if(isset($_SESSION[$key]))
        {
            return $_SESSION[$key];
        }
    }

    public function __set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    private static function startSession($name)
    {
        if(!isset($_SESSION))
        {
            if(headers_sent())
            {
                throw new Exception('headers already sent by');
            }
            session_name($name);
            session_start();
        }
    }
}
/**
 * Dependency injection a la Twittee and bootstrap
 */
final class Shozu
{
    private static $instance;
    private $store = array();

    private function __construct(){}

    public function __set($k, $c)
    {
        $this->store[$k]=$c;
    }

    public function __get($k)
    {
        if (!isset($this->store[$k]))
        {
            throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $k));
        }
        return (!is_array($this->store[$k]) && is_callable($this->store[$k])) ? $this->store[$k]($this) : $this->store[$k]; // not php5.3 yet
    }

    /**
     * Generate url for dynamic content.
     *
     * Depending on the url_rewriting setting, you'll want either normal urls or
     * clean urls ("index.php?/app/controller/action" or "/app/controller/action").
     *
     * <code>
     * $url = Shozu::getInstance()->url('app/controller/action');
     * </code>
     *
     * @param string $target targetted path to action
     * @return string
     */
    public function url($target)
    {
        if(substr($target,0,1) == '/')
        {
            $target = substr($target, 1);
        }
        $shozu = Shozu::getInstance();
        if($shozu->url_rewriting)
        {
            return $shozu->base_url . $target;
        }
        else
        {
            return $shozu->base_url . 'index.php?/' .$target;
        }
    }

    /**
     * Bootstraps application, dispatch query.
     *
     * The Shozu instance acts like a minimalistic dependency injection controller
     * a la Twittee (http://twittee.org/), so you can add config values or even closures.
     *
     * <code>
     * Shozu::getInstance()->handle(array(
     *           'url_rewriting'         => false, // override shozu config
     *           'your_own_config_param' => 'some value', // add your own config
     *           'your_dependency'       => function(){ return new Foo;} // use closures
     *
     * ));
     * </code>
     *
     * List of Shozu config keys (see source for default values): document_root,
     * project_root, benchmark, url_rewriting, use_i18n, default_application,
     * default_controller, default_action, db_dsn, db_user, db_pass, base_url,
     * debug, routes, obstart, include_path, error_handler, timezone, session_name,
     * session.
     *
     * @param array $override configuration
     */
    public function handle(array $override = null)
    {
        $config = array(
            'document_root'       => function(){return Shozu::getInstance()->project_root . 'docroot/';},
            'project_root'         => __DIR__ . '/',
            'benchmark'           => false,
            'url_rewriting'       => function(){if(!defined('SHOZU_URL_REWRITING')){define('SHOZU_URL_REWRITING', in_array('mod_rewrite', apache_get_modules()));}return SHOZU_URL_REWRITING;},
            'use_i18n'            => false,
            'default_application' => 'welcome',
            'default_controller'  => 'index',
            'default_action'      => 'index',
            'db_dsn'              => 'mysql:host=localhost;dbname=test',
            'db_user'             => 'root',
            'db_pass'             => '',
            'base_url'            => 'http://' . $_SERVER['HTTP_HOST'] . (dirname($_SERVER['SCRIPT_NAME']) != '/' ? dirname($_SERVER["SCRIPT_NAME"]) . '/' : '/') ,
            'debug'               => false,
            'routes'              => array(),
            'obstart'             => true,
            'include_path'        => explode(PATH_SEPARATOR, get_include_path()),
            'error_handler'       => '',
            'timezone'            => 'Europe/Paris',
            'session_name'        => 'app_session',
            'session'             => function(){return Session::getInstance(Shozu::getInstance()->session_name);}
        );
        if(is_array($config))
        {
            $config = array_merge($config, $override);
        }
        foreach($config as $key => $val)
        {
            $this->__set($key, $val);
        }
        date_default_timezone_set($config['timezone']);
        spl_autoload_register(array('Shozu', 'autoload'));
        set_exception_handler(array('Shozu', 'handleError'));
        if ($this->debug)
        {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', true);
        }
        else
        {
            error_reporting(0);
            ini_set('display_errors', false);
        }
        if ($this->use_i18n)
        {
            $lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $lang = strtolower(substr(chop($lang[0]) , 0, 2));
            $this->lang = $lang;
            $langFile = $this->project_root . 'lang' . DIRECTORY_SEPARATOR . $this->lang . '.php';
            if (is_file($langFile))
            {
                include ($langFile);
            }
            else
            {
                $this->translations = array();
            }
        }

        set_include_path(implode(PATH_SEPARATOR,
                                 array_unique(array_merge(array('.',
                                                                $this->project_root . 'applications' . DIRECTORY_SEPARATOR,
                                                                $this->project_root . 'lib' . DIRECTORY_SEPARATOR),
                                                          Shozu::getInstance()->include_path
                                                                ))));
        Dispatcher::addRoute($this->routes);
        if($this->obstart)
        {
            if(!ob_start('ob_gzhandler'))
            {
                ob_start();
            }
        }
        Benchmark::start('dispatch');
        Observer::notify('shozu.dispatch');
        Dispatcher::dispatch();
    }

    public static function handleError(Exception $e)
    {
        if(Shozu::getInstance()->error_handler != '')
        {
            list($application, $controller, $action) = explode('/', Shozu::getInstance()->error_handler);
            die(Dispatcher::render($application, $controller, $action, array($e)));
        }
        if(Shozu::getInstance()->debug === true)
        {
            if(!headers_sent())
            {
                header('content-type: text/plain');
            }
            else
            {
                echo '<pre>';
            }
            die($e->getMessage() . "\n" .
                $e->getTraceAsString());
        }
        else
        {
            if($e->getCode() == '404')
            {
                header('content-type: text/plain');
                header("HTTP/1.0 404 Not Found");
                die('file not found.');
            }
            if($e->getCode() == '500')
            {
                header('content-type: text/plain');
                header("HTTP/1.0 500 Internal Error");
                die('internal error.');
            }
        }
    }

    /**
     * Get Shozu instance
     *
     * @return Shozu
     */
    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new Shozu;
        }
        return self::$instance;
    }

    /**
     * Get Shozu instance.
     *
     * Just a short alias for Shozu::getInstance()
     *
     * @return Shozu
     */
    public static function _()
    {
        return self::getInstance();
    }

    /**
     * Default autoloader
     *
     * Assumes namespaces map to file system: \myns\myClass => /myns/myClass.php
     *
     * @param string $class class fully qualified name
     */
    public static function autoload($class)
    {
        $classFile = str_replace(array('_', '\\'), array('/', '/'), $class) . '.php';
        $old = ini_set('error_reporting', 0);
        $result = include($classFile);
        ini_set('error_reporting', $old);
        return $result;
    }
}
/**
 * Cache class with unified API for APC/Array/Disk cache.
 *
 * Uses an API similar to the one exposed by APC, ie fetch/store/delete methods.
 *
 * Instanciation:
 *
 * <code>
 * // disk cache. Create directory if not available.
 * $diskCache = Cache::_('myDiskCache', array('type'   => 'disk'
 *                                               'path'   => '/my/cache/path/',
 *                                               'create' => true));
 * // APC cache. Will throw exception if Apc is not installed
 * $apcCache = Cache::_('myApcCache', array('type' => 'apc'));
 *
 * // Array cache. Destroyed when script ends but useful to keep object references
 * $arrayCache = Cache::_('myArrayCache', array('type' => 'array'));
 * </code>
 *
 * Usage:
 *
 * <code>
 * // store a value for an hour
 * $cache->store('someID', $someValue, 3600);
 *
 * // fetch a value
 * $cache->fetch('someID');
 *
 * // delete a value
 * $cache->delete('someID');
 *
 * // store a value ONLY if id doesn't exist
 * $cache->add('someID', $someValue);
 * </code>
 *
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 * @package Cache
 */
abstract class Cache
{
    private static $store = array();

    /**
     * create / get cache instance
     *
     * @param string $id Cache identifier
     * @options array options as key=>value pairs
     * @return Cache cache instance
     */
    public static function getInstance($id, array $options = null)
    {
        if(!isset(self::$store[$id]))
        {
            if(!is_array($options))
            {
                throw new Cache_Exception('Options must be passed as an array');
            }
            if(!isset($options['type']))
            {
                throw new Cache_Exception('Type option not set');
            }
            switch($options['type'])
            {
                case 'store':
                case 'array':
                    self::$store[$id] = new Cache_Array($options);
                    break;
                case 'ram':
                case 'apc':
                    self::$store[$id] = new Cache_Apc($options);
                    break;
                case 'file':
                case 'disk':
                    self::$store[$id] = new Cache_File($options);
                    break;
                default:
                    throw new Cache_Exception('Unsupported cache type');
                    break;
            }
        }
        return self::$store[$id];
    }

    /**
     * Convinience shortcut to Cache::getInstance()
     *
     * @param string $id Cache identifier
     * @options array options as key=>value pairs
     * @return Cache cache instance
     */
    public static function _($id, array $options = null)
    {
        return self::getInstance($id, $options);
    }

    abstract public function __construct(array $options = null);
    abstract public function store($id, $value, $ttl = 0);
    abstract public function add($id, $value, $ttl = 0);
    abstract public function fetch($id);
    abstract public function delete($id);
}
/**
 * Cache exception
 *
 * @package Cache
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 */
class Cache_Exception extends Exception
{

}
/**
 * File-based cache.
 *
 * @package Cache
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 */
class Cache_File extends Cache
{
    private $path;
    /**
     * New file cache
     *
     * @param Array Options are cache path ('path') and wether to check it ('check')
     */
    public function __construct(array $options = null)
    {
        if(!is_array($options))
        {
            throw new Cache_Exception('You must provide an options array');
        }
        if(!isset($options['path']))
        {
            throw new Cache_Exception('Missing path option');
        }
        if(!isset($options['create']))
        {
            $options['create'] = false;
        }

        $slash = substr($options['path'], -1);
        if($slash != '/' and $slash !='\\')
        {
            $options['path'] .= '/';
        }

        if($options['create'])
        {
            if(!is_dir($options['path']))
            {
                if(!mkdir($options['path'], 0755, true))
                {
                    throw new Cache_Exception('directory "' . $options['path'] . '" does ot exist and could not be created.');
                }
            }
        }

        $this->path = $options['path'];
    }

    /**
     * Store value
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function store($id, $value, $ttl = 0)
    {
        $file = $this->fileName($id);
        if($ttl == 0)
        {
            $expires = 0;
        }
        else
        {
            $expires = time() + (int)$ttl;
        }
        if(file_put_contents($file,$expires
            . "\n" . serialize($value)))
        {
            return true;
        }
    }

    /**
     * Add value. Same as store, only will not overwrite existing value
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function add($id, $value, $ttl = 0)
    {
        if(($val = $this->fetch($id)) === false)
        {
            return $this->store($id, $value, $ttl);
        }
        return false;
    }

    /**
     * Fetch value
     *
     * @param string $id Value identifier
     * @return mixed Returns value or false
     */
    public function fetch($id)
    {
        $old = ini_set('error_reporting', 0);
        if(($file = fopen($this->fileName($id), 'r')) === false)
        {
            ini_set('error_reporting', $old);
            return false;
        }
        ini_set('error_reporting', $old);
        $expires = (int)fgets($file);
        if($expires > time() or $expires === 0)
        {
            $data = '';
            while(($line = fgets($file)) !== false)
            {
                $data .= $line;
            }
            fclose($file);
            return unserialize($data);
        }
        fclose($file);
        return false;
    }

    /**
     * Delete value from cache
     *
     * @param string $id Value identifier
     * @return boolean
     */
    public function delete($id)
    {
        $file = $this->fileName($id);
        if(is_file($file))
        {
            return unlink($file);
        }
        return false;
    }

    private function fileName($id)
    {
        return $this->path . md5($id);
    }
}
/**
 *Array-based cache. Useful for unit testing or keeping object references
 *
 * @package Cache
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 */
class Cache_Array extends Cache
{
    private $cache = array();

    /**
     * No options here.
     *
     * @param Array
     */
    public function __construct(array $options = null)
    {
        // nothing to do here :-)
    }

    /**
     * Store value
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function store($id, $value, $ttl = 0)
    {
        if($ttl == 0)
        {
            $this->cache[$id] = array($value, 0);
        }
        else
        {
            $expires = time() + $ttl;
            $this->cache[$id] = array($value, $expires);
        }
        return true;
    }

    /**
     * Add value. Same as store, but will not overwrite an existing value.
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function add($id, $value, $ttl = 0)
    {
        if(($val = $this->fetch($id)) === false)
        {
            return $this->store($id, $value, $ttl);
        }
        return false;
    }

    /**
     * Fetch value
     *
     * @param string $id Value identifier
     * @return mixed Returns value or false
     */
    public function fetch($id)
    {
        if(!isset($this->cache[$id]))
        {
            return false;
        }
        if($this->cache[$id][1] < time() and $this->cache[$id][1] !== 0)
        {
            $this->cache[$id] = NULL;
            return false;
        }
        return $this->cache[$id][0];
    }

    /**
     * Delete value from cache
     *
     * @param string $id Value identifier
     * @return boolean
     */
    public function delete($id)
    {
        if(isset($this->cache[$id]))
        {
            $this->cache[$id] = NULL;
            return true;
        }
        return false;
    }
}
/**
 * APC-based cache. Will not work in CLI.
 *
 * @package Cache
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 */
class Cache_Apc extends Cache
{
/**
 * No options here.
 *
 * @param Array
 */
    public function __construct(array $options = null)
    {
        if(!function_exists('apc_store'))
        {
            throw new Cache_Exception('APC must be installed to use this backend');
        }
    }

    /**
     * Store value
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function store($id, $value, $ttl = 0)
    {
        return apc_store($id, $value, $ttl);
    }

    /**
     * Add value. Same as store, but will not overwrite an existing value.
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function add($id, $value, $ttl = 0)
    {
        if(($val = $this->fetch($id)) === false)
        {
            return $this->store($id, $value, $ttl);
        }
        return false;
    }

    /**
     * Fetch value
     *
     * @param string $id Value identifier
     * @return mixed Returns value or false
     */
    public function fetch($id)
    {
        return apc_fetch($id);
    }

    /**
     * Delete value from cache
     *
     * @param string $id Value identifier
     * @return boolean
     */
    public function delete($id)
    {
        return apc_delete($id);
    }
}
/**
 * Memcache-based cache.
 *
 * @package Cache
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 */
class Cache_Memcache extends Cache
{
    private $options;
    private $memcache;
    /**
     * No options here.
     *
     * @param Array
     */
    public function __construct(array $options = null)
    {
        $this->options = $options;
        if(!is_array($options))
        {
            throw new Cache_Exception('You must provide an options array');
        }
        $this->memcache = new Memcache;
        if(!$this->memcache->connect($options['server'], $options['port']))
        {
            throw new Cache_Exception('could not connect to host');
        }
    }

    /**
     * Store value
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function store($id, $value, $ttl = 0)
    {
        return $this->memcache->set($id, $value, MEMCACHE_COMPRESSED, $ttl);
    }

    /**
     * Add value. Same as store, but will not overwrite an existing value.
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function add($id, $value, $ttl = 0)
    {
        return $this->memcache->add($id, $value, MEMCACHE_COMPRESSED, $ttl);
    }

    /**
     * Fetch value
     *
     * @param string $id Value identifier
     * @return mixed Returns value or false
     */
    public function fetch($id)
    {
        return $this->memcache->get($id);
    }

    /**
     * Delete value from cache
     *
     * @param string $id Value identifier
     * @return boolean
     */
    public function delete($id)
    {
        return $this->memcache->delete($id);
    }
}