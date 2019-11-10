# laravel-models-scaffold
Auto-generate your models (scaffold) based on your MySQL database definition 
Installation:
1.- Copy this content to packages/reddireccion/multiapps (create the folder structure if does not exists)
2.- Add the namespace to the composer file
	autoload
		psr-4
			"Reddireccion\\ModelsScaffold\\":"packages/reddireccion/modelsscaffold"
3.- Add the service provider Reddireccion\ModelsScaffold\ModelsScaffoldServiceProvider::class to your config/app.php

It only works with mysql, and requires a database user with permissions to query information_schema.tables and information_schema.columns.
run the artisan command make:model-scaffold to generate the scaffold models for all your tables in the database.
Once you have generated your scaffold models, you'll find them in app/Models/Scaffold folder, do not modify the code directly as it will be overwritten if you run the command again to update your models based on your database structure changes.
You should not use this scaffold models directly in your app neither customize the code ther, instead use the models you'll find in the app/Models folder, this files are not overwriten by the command and inherit the scaffold model.

You can use mysql table and field comments to modify some behaviour of the scaffold generator by defining properties in a json serialized object as specified below

Command Parameters
	- tableName: optional parameter string,specifies the name of an individual table to create it's model
	- skipTables: optional parameter string, comma separated list of tables to skip from creating model, by default table migrations is skiped
	- databaseConnectionName: optional parameter string, path to the connection in config file by default database.connections.mysql
	- startsWith: optional parameter string, only the tables starting with this prefix will be used, by default the prefix from the database connection is used
	- guardedFields: optional parameter string, comma separated list of fields that should be added to the model property guarded, by default 'id,created_by,updated_by,created_at,updated_at,deleted_at' 
	- scaffoldTemplatePath: optional parameter string, path to the template used for the scaffold defaults to packages/reddireccion/modelsscaffold/src/Template/ScaffoldModel.php
	- emptyModelTemplatePath optional parameter string, path to the template used for the empty models that will inherite the scaffold model defaults to packages/reddireccion/modelsscaffold/src/Template/ScaffoldModel.php
	- modelsPath: optional parameter string, path where the models will be stored, by default app/Models
	- modelsNamespace: optional parameter string, namespace for the models, by default App\Models
Table 
	- step: integer that defines the order in wich models will be created, this should define the order in relationships, for example if users and roles tables has no dependencies both can be step 1, while table role_users will be step 2 as it depends on tables of step 1, and role_user_permissions will be step 3 as it depends on the table from step 2. If not defined all models will be created alphabetically ex '{step:1}' 

Field
	Special fields
		- deleted_at:  if field exists with that name, SoftDeletes trait will be added
		- timestamp: any field with this type will be added to the $dates property of the model.
		- _id: any field with that postfix will be treated as a foreign key and a relationship will be added.
	- functionName: for foreign key fields overwrites the default function name in the base model, the default is based on the model name ex: '{functionName:"user"}'
	- relatedModelFunctionName: for foreign key fields overwited the default function name of the related model, the default is based on the base model name
	- modelName: for foreignt key fields overwrited the related model name, the default is based on the field name.
	- hasOne: for foreign key fields if set to true the related model will have a hasOne relationship instead of hasMany

	- hidden: boolean default to false that will be used to indicate that this field will not be serialized for json requests
	- guarded: TODO IMPLEMENT

Field modifiers for DryCrud
	- canEdit: boolean default to true, allows to modify this fiels in an existing record
	- canCreate: boolean default to true, allows to set this field when creating a record