# dot-console

DotKernel component to build console applications based on zf-console

### Requirements
- PHP >= 7.1
- zendframework/zend-servicemanager >= 3.3,
- zfcampus/zf-console >= 1.3


### Installation
Run the following command in your project root directory
```bash
$ composer require dotkernel/dot-console
$ composer install
```
### 'Hello Word!' command example
#### Create HelloCommand class
In frontend/src/Console/src/Command folder Create HelloCommand.php file which contain HelloCommand class and extends AbstractCommand class.
```bash
namespace Frontend\Console\Command;

use Dot\Console\Command\AbstractCommand;
use Zend\Console\Adapter\AdapterInterface;
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

#### Add command to config 
In frontend application folder go to config/autoload/console.global.php and add folowing command
```bash
...
'commands' => [
    [
        'name' => 'hello',
        'description' => 'Hello, World! command example',
        'handler' => HelloCommand::class,
    ],
]
...
```

#### Testing command 
In comand line, go to project root directory and type folowing command
```bash
$ php ./bin/console.php hello
```
## License
MIT
