# Symfony for dummies

Only two important things to know about the Framework:

## Routing

When your application receives a request, it calls a controller action to generate the response. The routing
configuration defines which action to run for each incoming URL. It also provides other useful features, like generating
SEO-friendly URLs (e.g. /read/intro-to-symfony instead of index.php?article_id=57).

###  Creating routes

Routes can be configured in YAML, XML, PHP or using attributes. All formats provide the same features and performance, 
so choose your favorite. Symfony recommends attributes because it's convenient to put the route and controller in the 
same place.

#### Creating Routes as Attributes

PHP attributes allow to define routes next to the code of the controllers associated to those routes. Attributes are 
native in PHP 8 and higher versions, so you can use them right away.

You need to add a bit of configuration to your project before using them. 

`If your project uses Symfony Flex, this file is already created for you.`

Otherwise, create the following file manually:

```yaml
# config/routes/attributes.yaml
controllers:
    resource:
        path: ../../src/Controller/
        namespace: App\Controller
    type: attribute

kernel:
    resource: App\Kernel
    type: attribute
```
This configuration tells Symfony to look for routes defined as attributes on classes declared in the App\Controller
namespace and stored in the src/Controller/ directory which follows the PSR-4 standard. The kernel can act as a
controller too, which is especially useful for small applications that use Symfony as a microframework.

Suppose you want to define a route for the /blog URL in your application. To do so, create a controller class like the 
following:
```php
// src/Controller/BlogController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog_list')]
    public function list(): Response
    {
        // ...
    }
}
```

> The query string of a URL is not considered when matching routes. In this example, URLs like `/blog?foo=bar` and 
> `/blog?foo=bar&bar=foo` will also match the blog_list route.

### Matching Expressions

Use the condition option if you need some route to match based on some arbitrary matching logic:

```php
// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route(
        '/contact',
        name: 'contact',
        condition: "context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'",
        // expressions can also include config parameters:
        // condition: "request.headers.get('User-Agent') matches '%app.allowed_browsers%'"
    )]
    public function contact(): Response
    {
        // ...
    }

    #[Route(
        '/posts/{id}',
        name: 'post_show',
        // expressions can retrieve route parameter values using the "params" variable
        condition: "params['id'] < 1000"
    )]
    public function showPost(int $id): Response
    {
        // ... return a JSON response with the post
    }
}
```

### Debugging Routes
As your application grows, you'll eventually have a lot of routes. Symfony includes some commands to help you debug 
routing issues. First, the debug:router command lists all your application routes in the same order in which Symfony 
evaluates them:
```bash
symfony console debug:router

----------------  -------  -------  -----  --------------------------------------------
Name              Method   Scheme   Host   Path
----------------  -------  -------  -----  --------------------------------------------
homepage          ANY      ANY      ANY    /
contact           GET      ANY      ANY    /contact
contact_process   POST     ANY      ANY    /contact
article_show      ANY      ANY      ANY    /articles/{_locale}/{year}/{title}.{_format}
blog              ANY      ANY      ANY    /blog/{page}
blog_show         ANY      ANY      ANY    /blog/{slug}
----------------  -------  -------  -----  --------------------------------------------
```

Pass the name (or part of the name) of some route to this argument to print the route details:

```bash
symfony console debug:router app_lucky_number

+-------------+---------------------------------------------------------+
| Property    | Value                                                   |
+-------------+---------------------------------------------------------+
| Route Name  | app_lucky_number                                        |
| Path        | /lucky/number/{max}                                     |
| ...         | ...                                                     |
| Options     | compiler_class: Symfony\Component\Routing\RouteCompiler |
|             | utf8: true                                              |
+-------------+---------------------------------------------------------+
```

The other command is called router:match and it shows which route will match the given URL. It's useful to find out why 
some URL is not executing the controller action that you expect:

```bash
symfony console router:match /lucky/number/8

  [OK] Route "app_lucky_number" matches
```
`SensioFrameworkExtraBundle` automatically convert the parameter as integer value.


## Service Container

Your application is full of useful objects: a "Mailer" object might help you send emails while another object might help

you save things to the database. Almost everything that your app "does" is actually done by one of these objects. And 
each time you install a new bundle, you get access to 

even more!

In Symfony, these useful objects are called services and each service lives inside a very special object called the 

service container. The container allows you to centralize the way objects are constructed. It makes your life easier, 
promotes a strong architecture and is super-fast!

### Fetching and using Services

The moment you start a Symfony app, your container already contains many services. These are like tools: waiting for you
to take advantage of them. In your controller, you can "ask" for a service from the container by type-hinting an argument
with the service's class or interface name.
When your class is associated to a route you can pass them from thye container as type-hinting

```php
// src/Controller/ProductController.php
namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products')]
    public function list(LoggerInterface $logger): Response
    {
        $logger->info('Look, I just used a service!');

        // ...
    }
}
```
What other services are available? Find out by running:
```bash
symfony console debug:autowiring

Autowirable Types
  =================

   The following classes & interfaces can be used as type-hints when autowiring:

   Describes a logger instance.
   Psr\Log\LoggerInterface (logger)

   Request stack that controls the lifecycle of requests.
   Symfony\Component\HttpFoundation\RequestStack (request_stack)

   RouterInterface is the interface that all Router classes must implement.
   Symfony\Component\Routing\RouterInterface (router.default)

   [...]
```

This file details what are automatically passed/ignored to the service container :

```yaml
# config/services.yaml
services:
    # default configuration for services in *this* file
    _defaults:
        autowire: true      # Automatically injects dependencies in your services.
        autoconfigure: true # Automatically registers your services as commands, event subscribers, etc.

    # makes classes in src/ available to be used as services
    # this creates a service per class whose id is the fully-qualified class name
    App\:
        resource: '../src/'
        exclude:
            - '../src/DependencyInjection/'
            - '../src/Entity/'
            - '../src/Kernel.php'

    # order is important in this file because service definitions
    # always *replace* previous ones; add your own service configuration below

    # ...
```

The container will automatically know to pass the logger service when instantiating the MessageGenerator. How does it 
know to do this? Autowiring. The key is the LoggerInterface type-hint in your __construct() method and the autowire: 
true config in services.yaml. When you type-hint an argument, the container will automatically find the matching service. 
If it can't, you'll see a clear exception with a helpful suggestion.