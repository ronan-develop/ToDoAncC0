# Audit

## Pourquoi

Afin de satisfaire vos utilisateurs, votre application doit être parfaitement optimisée.
Pour ce faire vous pouvez vous référer à l'outil en ligne [Blackfire](https://blackfire.io/)
Cela vous permettra d'étudier les fichiers et fonctions appelés à chaque fois qu'une reqûete
est exécutée. 

`Ce rapport à pour but de fournir des recommandations et des bonnes pratiques
pour permettre aux utilisateurs, développeurs, service marketing etc, de tirer le meilleur
parti de ToDoAndCo.`

## Le projet

La branche principale de l'application n'a pas été modifiée pour se rendre compte de l'évolution
de ToDoAndCo.
Il faut se mettre sur la branche dev pour constater les changements effectués. Utiliser la 
commande `git checkout dev`.

### Recommandations aux équipes de développement

vous lirez :

- `readme.md` pour démarrer le projet

Toutes les informations nécessaires y sont présentes pour l'installation, 
la contribution, les personnes membres de l'équipe ... etc.   

### Version initiale

La version de Symfony utilisée pour ce projet n'est plus mise à jour ni recommandée. 
La dernière en date est [Symfony 6.2](https://symfony.com/doc/current/index.html), 
celle que je vous préconise, pour être parfaitement à jour.
La version actuelle `3.1` pourrait posséder des failles de sécurité.


### PHP version

La version de php recommandée avec Symfony 6.2 est `PHP 8.1.0 ou plus`.

### Dépendances

Préférez `Composer` pour les installer afin d'être certain de leur origine et de
leur validité.

## Performances

Sur une version plus récente, vous profiterez d'une application qui utilise [OPcache](https://symfony.com/doc/current/performance.html#performance-use-opcache) plutôt que [Byte Code Cache](https://symfony.com/doc/3.1/performance.html#use-a-byte-code-cache-e-g-opcache), vous pourrez également profiter pleinement de l'[autowiring](https://symfony.com/doc/current/service_container/autowiring.html).
Côté sécurité, la configuration sera plus aisée avec [symfony/security-bundle](https://symfony.com/doc/current/security.html).

Au-delà des performances du cœur de Symfony, c'est l'ensemble des [Bundles](https://symfony.com/bundles) qui pourront
aussi bénéficier de meilleures performances et sécurité.

### Accélérer

Un site trop lent est inévitablement un site boudé. De plus, avec la progression de
la navigation mobile, les attentes en terme de rapidité et de performances sont
toujours plus hautes.

Il est conseillé de :

- Minifier les sources ;
- Optimiser les images ;
- Charger les scripts de manière asynchrone ;

...

### Expérience utilisateur

Il faut faire en sorte que l’internaute arrive le plus vite possible sur une page
adaptée à son besoin. Le menu est un atout clé à ne pas négliger.

Si l'application grandit il faudra mettre en place un moteur de recherche interne.

### La page d’accueil

Elle a un rôle très important dans la navigation, même s’il ne faut pas
oublier que la page d’accueil est rarement la première page visitée. Dans la majorité
des cas, le visiteur arrive sur un article ou un produit, puis il visite la page
d’accueil pour avoir plus d’informations sur le site web et/ou chercher une page mieux
adaptée à ses besoins. La mise en page de la page d’accueil est très importante, le 
visiteur doit comprendre très rapidement ce qu’on lui propose et ce qu’on attend de
lui. La page d’accueil a également un rôle clé dans la communication de marque.
L’univers et les valeurs de la marque doivent transparaitre clairement dans la page
d’accueil.

### Référencement

Il est conseillé pour améliorer le référencement de l'application, de la rendre SEO
friendly. Les deux critères principaux en SEO sont le contenu et la popularité du site
web, mais si les moteurs de recherche n’arrivent pas à crawler (=lire) les pages de
votre site web, la popularité ou la qualité du contenu de votre site web est inutile.

### Compatibilité multi devices

À l’heure où 38% du trafic web est issu des smartphones et tablettes, un site web se doit d’être accessible parfaitement quelle que soit le device utilisé. Il faut privilégier les responsive design. Vous allez dans ce sens, en utilisant Boostrap.


## Qualité de code 

L'application obtient le score `B` sur [Codacy](https://app.codacy.com/gh/ronan-develop/ToDoAncC0/dashboard?branch=dev). Ne pas oublier de se
positionner sur la branch dev. La branche master est là pour la comparison.
Le code reste `Legacy` et un effort est encore à faire sur le respect des
conventions de style avec `PHP_CodeSniffer`.

Il est cependant possible de régler codacy afin que, à l'avenir les 
`code patterns` soient au plus proche de vos conventions.

| Linter           | Version |
|------------------|:-------:|
| PHP_CodeSniffer  |  3.6.2  |
| ESLint           | 8.23.1  |
| CSSLint          |  1.0.5  |
| Jackson Linter   | 2.10.2  |
| PhpMess Detector |  2.10.  |
| PMD              | 6.51.0  |
| Stylelint        | 14.20.0 |

Le **rapport de performance** peut être obtenu [ici](cache.md)

|   Routes    |  Time  | Before |
|:-----------:|:------:|:------:|
|  Homepage   | 81.6ms | 154ms  |
|  user_list  |  60ms  | 71.4ms |
| user_create | 79.5ms | 1.04s  |
|  user_edit  | 15.8ms | 131ms  |
|    tasks    | 79.3ms | 99.6ms |
| tasks_edit  | 123ms  | 51.8ms |