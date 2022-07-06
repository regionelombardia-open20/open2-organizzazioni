# amos-organizzazioni

Plugin to make organizations.

## Installation

### 1. The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
composer require open20/amos-organizzazioni
```

or add this row

```
"open20/amos-organizzazioni": "~1.6.3"
```

to the require section of your `composer.json` file.


### 2. Add module to your main config in common:
	
```php
<?php
'modules' => [
    'organizzazioni' => [
        'class' => 'open20\amos\organizzazioni\Module'
    ],
],

If do you want to enable community creation for every single organization set this
parameter to true in your modules-amos.php config file. If this is true organization
module manager is able to create a reserved community.
These reserved communities can be made/managed by legal representative and 
operative referee.
'modules' => [
    'organizzazioni' => [
        'class' => 'open20\amos\organizzazioni\Module',
        'enableCommunityCreation' => true
    ],
],

```


### 3. Apply migrations

```bash
php yii migrate/up --migrationPath=@vendor/open20/amos-organizzazioni/src/migrations
```

or add this row to your migrations config in console:

```php
<?php
return [
    '@vendor/open20/amos-organizzazioni/src/migrations',
];
```


### 4. Add configuration to tag module. In backend/config/modules-amos.php add configuration like this:

```php
<?php

if (isset($modules['tag'])) {
    ...
    if (isset($modules['organizzazioni'])) {
        $modules['tag']['modelsEnabled'][] = 'open20\amos\organizzazioni\models\Profilo';
        $modules['tag']['modelsEnabled'][] = 'open20\amos\organizzazioni\models\ProfiloSedi';
    }
    ...
}
```

Then go in the tag manager and configure the roles for the trees you want for this model.

### Module configuration params

* **enableSocial** - boolean, default = true  
If true enable the social sections in form and view of an organization.

* **enableWorkflow** - boolean, default = false  
If true enable the validation workflow for the organizations.
