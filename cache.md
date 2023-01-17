# Study and optimize the application

It is possible thanks to [Blackfire](https://blackfire.io/) to measure
precisely the performance of the application.

## First

In your `.env` file, set the environment to `prod`

```.dotenv
APP_ENV=prod
```

In console :
```bash
 composer dump-env dev
 # or
composer dump-env prod
```

## Save and study a request

In your browser, click on the icon of your `Blackfire` extension then
on profile all request.
Enter the target url or navigate by clicking on the buttons. Once your
url is reached, stop recording and name the generated report. Make your
optimizations and compare reports with each other (time and resources)

`Blackfire` brings you many recommendations that you can follow to gain
performance.
Anytime you profile your application, `Blackfire` can detect common
performance bottlenecks and configuration issues. Solving them most of
the time should have a significant impact on the performance of your app.

For exemple:

-   `Doctrine DQL statements should be cached in production`
-   `Less ORM entities should be created`
-   `PHP Preloading should be configured`
-   `Twig template cache should be enabled in production`

> If you need any help, you can click the How to Fix button, the details will be
> given to solve the problem

If you follow the basic recommendations you can first save precious 
milliseconds

[//]: # (Tasks list optimized :)
[//]: # (![tasks list]&#40;assets/img/tasks_result.png&#41;)

It would be possible to optimize the autoloader composer by running in production environment the command: `composer dump-autoload -o`


You can write assertions if you have a premium account, put in `.blackfire.yaml` :
```yaml
# name 
  'list tasks':
    path:
      - '/tasks'
    assertions:
      - metrics.symfony.kernel.debug.count == 0 # production mode
      - metrics.symfony.yaml.reads.count == 0 # cache yaml in production
      - main.wall_time < 300ms
      - main.memory < 2Mb
```

In dashboard, select a report, click on `Callgraph` column, and find your functions to optimize them
