# Installation

## 1. Configuration du composer.json

Ajouter dans le fichier `composer.json` :

```javascript
"require": {
    "lilweb/job-bundle": "dev-master"
}
```

Il est aussi possible de le faire en ligne de commande:

```sh
$> composer require lilweb/job-bundle
```


## 2. Activer le bundle

Dans app/AppKernel.php:

```php
<?php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Lilweb\JobBundle\LilwebJobBundle(),
        );

        // ...
    }
```


## 3. Configurer le bundle


Configurez le noeud `lilweb_job` dans le fichier `app/config/config.yml` comme suit:

```javascript
lilweb_job:
    job_file: %kernel.root_dir%/%job_file% # Fichier XML décrivant l'architecture des jobs et des taches
```

Etant donné que tout ce qui concerne l'exécution des tâches doit être loggé à part,
définissez un handler 'jobs' pour monolog comme suit:

```javascript
monolog:
    handlers:
        job_handler:
            type: stream
            path: %job_logs_file%
            level: debug
            channels: jobs
        swift_handler:
            type:       swift_mailer
            from_email: %swift_handler_from%
            to_email:   %swift_handler_from%
            subject:    "[MY APP] Une erreur est survenue lors de l'exécution des jobs"
            level:      err
            channels:   jobs
```

NB: Ici, l'application est configurée de manière à envoyer des emails à partir d'un certain degré d'erreurs.


## 4. Définition des jobs/tâches

La définition des jobs et des taches se fait dans un fichier XML.
Voici l'architecture de ce fichier:

```XML
<?xml version="1.0" encoding="UTF-8" ?>
<config>
    <tasks>
        <task name="import:csv" service-id="lilweb:import_csv">
            <max-parallel-execution value="2" />
        </task>
        <task name="calcul:besoin" service-id="lilweb:calcul_besoin">
        </task>
        <task name="export:csv" service-id="lilweb:export_csv">
        </task>
        <task name="export:ftp" service-id="lilweb:export_ftp">
        </task>
    </tasks>
    <jobs>
        <job name="besoin:all" schedulable="true">
            <task name="import:csv" />
            <task name="calcul:besoin" />
            <task name="export:csv" />
            <task name="export:ftp" />
        </job>
    </jobs>
</config>
```

### 4.1 Définition des taches

Chaque tache est définie dans le noeud ```<tasks>```.

```
## BALISE <task>
Attributs obligatoires:
    - service-id: Nom du service a exécuté lors du lancement de l'ordonnanceur
    - name:       Nom de la tache (également repris dans la configuration des jobs)
Balises optionnelles:
    - <max-parallel-execution>: Configure le maximum d'exécution en parallèle de la tache

## BALISE <max-parallel-execution>
Attributs obligatoires:
    - value: Un nombre (-1 pour illimité)
```

### 4.2 Définition des jobs

Chaque job est défini dans le noeud ```<jobs>```.

```
## BALISE <job>
Attributs obligatoires:
    - name:  Nom du job
Attributs optionnels:
    - schedulable: valeur par défaut false, passer a true si le scheduler doit traiter le job.
Balises obligatoires:
    - <task>: Au moins une tache doit être définie pour un job. Cette tache est en quelque sorte
              une sorte de pointer vers une des taches définie plus haut, de ce fait les attributs
              'name' doivent correspondrent.

```

### 4.3 Définition des services

Chaque tache (import csv, export ftp ...) possède un comportement qui lui est propre.
C'est pourquoi celui-ci se doit d'être défini dans une service héritant de `AbstractTaskService` (`lilweb.abstract_task`),
afin de rationnaliser l'exécution des taches.

Côté config:

```yaml
lilweb.import_csv:
    class: %lilweb.import_csv.class%
    parent: lilweb.abstract_task
```

Côté PHP:

```PHP

use Lilweb\JobBundle\Entity\TaskInfo;
use Lilweb\JobBundle\Services\AbstractTaskService;

class ImportCsv extends AbstractTaskService
{
    /**
     * {@inheritdoc}
     */
    public function execute(TaskInfo $info)
    {
        // Traitement de la tache
        // Logging de ce qui se passe
    }
}
```
