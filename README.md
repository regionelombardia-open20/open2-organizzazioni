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



### Organizations importer

These are the configurations to enable the importer and set the various params to import the organizations from an axcel file.

The importer configuration key is **importOrganizationsConf**. Here is an example for a basic importer configuration.

```php
<?php
'organizzazioni' => [
    'class' => 'open20\amos\organizzazioni\Module',
    ...
    'importOrganizationsConf' => [
        'keyForImport' => 'key_field',
        'mapHeaderImport' => [
            'field_1',
            'field_2',
            'key_field',
            'field_4',
            'field_5',
            ...
        ],
        'requiredMapHeaderImport' => [
            'field_2',
            'key_field',
        ],
    ],
    ...
],
```
The following keys are required for the importer to work.

- keyForImport
- mapHeaderImport

Now there's a description of all allowed configurations fields.

**keyForImport** is the field that will be used as a key to find if the organization you are importing already exists. This field must be present in the "mapHeaderImport" array.

**mapHeaderImport** is the string array of all the fields that must be present in the excel header, mandatory or not.

**requiredMapHeaderImport** is the string array of the required fields. The fields in this array must be present in the "mapHeaderImport" array and cannot be empty in the excel file. Otherwise the importer skip the excel row.

**worksheetName** is the name of the worksheet in the downloadable excel template. Default "Foglio1".

The user can upload the excel file with the header according with the mapHeaderImport fields list. The file is imported into a support table. Then the real import is performed by a console script that you must set in CRON table.

```php
php yii organizzazioni/import/import-organizations
```

### Module configuration params

* **viewStatusEmployees** - boolean, default = true  
If true show the status column in employees section in form and view.

* **viewRoleEmployees** - boolean, default = false  
If true show the role column in employees section in form and view.

* **userNetworkWidgetSearchOrganization** - boolean, default = true  
If true enable the search bar on the organizations section in the own profile network tab.

* **userNetworkWidgetSearchHeadquarter** - boolean, default = true  
If true enable the search bar on the headquarters section in the own profile network tab.

* **enableSocial** - boolean, default = true  
If true enable the social sections in form and view of an organization.

* **enableWorkflow** - boolean, default = false  
If true enable the validation workflow for the organizations.

* **enableOrganizationAttachments** - boolean, default = true  
If set to false the form and the view attachments sections will be hidden.

* **enableUniqueCodeForInvitation** - boolean, default = false  
If true a unique code is generated for each organization and it's sent in the external user invitation emails to allow the external users to join manually to an organization'.

* **addRequired** - array, default = []  
In this property you can configure which fields you want to add to the required array of the principal model of the plugin.
Here is an example.

```php
'modules' => [
    'organizzazioni' => [
        'class' => 'open20\amos\organizzazioni\Module',
        ...
        'addRequired' => [
            'Profilo' => [
                'codice_fiscale',
                'tipologia_di_organizzazione'
            ],
            'ProfiloSediOperative' => [
                'email'
            ],
        ],
        ...
    ],
],
```

* **enableProfiloGroups** - boolean, default = false  
If true enable the organizations groups.

* **excludeRefereesFromEployeesLists** - boolean, default = false  
If true the organization referees will be hidden in the organization employees lists.
