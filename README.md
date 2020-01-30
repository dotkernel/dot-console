# dot-console

DotKernel component to build console applications based on zf-console

### Requirements
- PHP >= 7.1
- laminas/laminas-servicemanager >= 3.3,
- zfcampus/zf-console >= 1.3


### Installation
Run the following command in your project root directory
```bash
$ composer require dotkernel/dot-console
```

Next, register the package's `ConfigProvider` to your application config. If can also manually register the package's dependencies in your container. There is only one dependency that need to be registered `Dot\Console\Factory\ApplicationFactory` that should be used to create an `ZF\Console\Application` object used to bootstrap the app.

### Configuration and Usage
You should create a bootstrap file in your project, similar to `index.php`, that will be called from the command line to start console commands. We advise you to create a `bin` folder in your project's root folder. Here you can create a `console.php` file with the following content.
```php
/**
 * Console application bootstrap file
 */
use ZF\Console\Application;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
call_user_func(function () {
    /** @var \Interop\Container\ContainerInterface $container */
    $container = require 'config/container.php';

    /** @var Application $app */
    $app = $container->get(Application::class);

    $exit = $app->run();
    exit($exit);
});
```

This assumes you are using one of our web starter applications or a Mezzio similarly structured application.
Next you can go to the command line and call console commands

```bash
$ php bin/console.php <command_name> <parameters>
```

You can try some of the provided out-of-the box commands
```bash
$ php bin/console.php help
```

### Creating commands

dot-console is mainly a wrapper around [zf-console](https://github.com/zfcampus/zf-console). You should check their full documentation before.
Why a wrapper?
* it allows us to extend the original package if we need
* we can simplify some things

The first thing this package offers, is the `ZF\Console\Application` factory that creates a package using the configuration array provided at `dot_console` key. An console application needs a name, version, route configuration, console instance and dispatcher.
You can provide a configuration file for the console application in the following format
```php
return [
    'dot_console' => [
        'name' => 'Console Name',
        'version' => '1.0.0',

        'commands' => [
            //...
        ]
    ]
];
```
The second thing is an abstract class that you commands should extend. This class forces the `__invoke` method with the proper parameter definition that defines console commands.
Commands must be invokable classes with the following signature:
```php
public function __invoke(Route $route, AdapterInterface $console)
```

Command classes are pulled from the container, so you might inject your commands with dependencies.

### 'Hello World!' command example
#### Create HelloCommand class

```php

use Dot\Console\Command\AbstractCommand;
use Laminas\Console\Adapter\AdapterInterface;
use ZF\Console\Route;

class HelloCommand extends AbstractCommand
{
    /**
     * @param Route $route
     * @param AdapterInterface $console
     * @return int
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $console->writeLine('Hello World Command');
        return 0;
    }
}
```

Next, register this class in your container as a dependency.

#### Add command to config 
Update the console configuration to include this command
```php
//...
'commands' => [
    [
        'name' => 'hello',
        'description' => 'Hello, World! command full description',
        'short_description' => 'Hello, World! command short description',
        'handler' => HelloCommand::class,
    ],
]
//...
```
Please note that the content of:
- `description` is displayed when the command is executed
- `short_description` is displayed when the list of available commands is executed or getting help for a specific command

#### Testing command 
In command line, go to your project's root directory and type the following command:
```bash
$ php ./bin/console.php hello
```

For a complete documentation you can follow  [zf-console](https://github.com/zfcampus/zf-console). Anything there related to commands are applicable to this package too.

## License
MIT
